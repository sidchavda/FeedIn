<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\Category;
use App\Models\Language;
use App\Services\HuggingFaceService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchGujaratiNews extends Command
{
    protected $signature = 'news:fetch-gujarati
        {--limit=50 : Max articles to insert per run}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Gujarati news from Divya Bhaskar and News18 Gujarati';

    private array $feeds = [
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-1035.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-1037.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-1038.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-969.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-970.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-12042.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://gujarati.news18.com/commonfeeds/v1/guj/rss/latest.xml', 'source' => 'News18 Gujarati'],
    ];

    public function handle(): int
    {
        $language = Language::firstOrCreate(
            ['code' => 'GJ'],
            ['name' => 'Gujarati', 'is_active' => 1]
        );
        $category = Category::firstOrCreate(
            ['name' => 'ગુજરાતી', 'language_id' => $language->id],
            ['is_active' => 1]
        );
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $existingTitles = News::pluck('title')->map(fn ($t) => $this->normalizeTitle($t))->flip();

        $pending = [];
        $seenLinks = [];
        $seenTitles = [];

        foreach ($this->feeds as $feed) {
            if (count($pending) >= $limit) {
                break;
            }
            $feedUrl = $feed['url'];
            $source = $feed['source'];
            $this->line("Fetching: {$source}");
            $articles = $this->parseFeed($feedUrl, $source);
            if (empty($articles)) {
                continue;
            }
            $this->info("  Found " . count($articles) . " articles.");

            foreach ($articles as $art) {
                if (count($pending) >= $limit) {
                    break 2;
                }
                $link = $art['link'] ?? null;
                if (!$link || isset($existingLinks[$link]) || isset($seenLinks[$link])) {
                    continue;
                }
                if (empty($art['title'])) {
                    continue;
                }
                if (empty($art['image'])) {
                    continue;
                }
                $normalized = $this->normalizeTitle($art['title']);
                if (isset($existingTitles[$normalized]) || isset($seenTitles[$normalized])) {
                    continue;
                }
                $pending[] = $art;
                $seenLinks[$link] = true;
                $seenTitles[$normalized] = true;
            }
        }

        if (empty($pending)) {
            $this->warn('No new articles to insert.');
            return Command::SUCCESS;
        }

        $this->info('New articles to process: ' . count($pending));

        $this->summarizeWithGemini($pending, 'gujarati');
        $this->rewriteTitles($pending, 'gujarati');

        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $title = html_entity_decode(strip_tags($art['title']));
            $title = trim(preg_replace('/\s+/', ' ', $title));

            $desc = !empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '');
            if (!empty($art['ai_summarized'])) {
                // Log::info('HF: stored', ['title' => mb_substr($title, 0, 60)]);
            }
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: $art['source'];

            try {
                News::create([
                    'title' => mb_substr($title, 0, 160),
                    'link' => $art['link'],
                    'language_id' => $language->id,
                    'category_id' => $category->id,
                    'description' => $desc,
                    'image' => $image,
                    'author' => $author,
                    'status' => 1,
                    'push_notification' => 0,
                ]);
                $inserted++;
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), '23000')) {
                    $this->warn('  Skipped duplicate: ' . mb_substr($title, 0, 60));
                } else {
                    throw $e;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Inserted: {$inserted}");
        return Command::SUCCESS;
    }

    private function parseFeed(string $url, string $source): array
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; TEJ/1.0)'],
            ]);

            if ($this->option('no-verify')) {
                $client = new Client([
                    'timeout' => 10,
                    'verify' => false,
                    'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; TEJ/1.0)'],
                ]);
            }

            $response = $client->get($url);
            $xml = simplexml_load_string((string) $response->getBody());
            if (!$xml || !isset($xml->channel)) {
                return [];
            }

            $items = $xml->channel->item ?? [];
            $articles = [];

            foreach ($items as $item) {
                $link = trim((string) ($item->link ?? ''));
                if (!$link) {
                    continue;
                }

                $namespaces = $item->getNamespaces(true);

                $image = null;
                if (isset($namespaces['media'])) {
                    $media = $item->children($namespaces['media']);
                    if (isset($media->content)) {
                        $attrs = $media->content->attributes();
                        $image = (string) ($attrs['url'] ?? '');
                    }
                    if (!$image && isset($media->thumbnail)) {
                        $attrs = $media->thumbnail->attributes();
                        $image = (string) ($attrs['url'] ?? '');
                    }
                }
                if (!$image && isset($item->enclosure)) {
                    $attrs = $item->enclosure->attributes();
                    if (str_starts_with((string) ($attrs['type'] ?? ''), 'image/')) {
                        $image = (string) ($attrs['url'] ?? '');
                    }
                }
                if (!$image) {
                    $descHtml = (string) ($item->description ?? '');
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $descHtml, $m)) {
                        $image = $m[1];
                    }
                }

                $author = '';
                if (isset($namespaces['dc'])) {
                    $dc = $item->children($namespaces['dc']);
                    $author = (string) ($dc->creator ?? '');
                }
                if (!$author) {
                    $author = (string) ($item->author ?? '');
                }

                $desc = (string) ($item->description ?? '');
                if (empty(trim(strip_tags($desc))) && isset($namespaces['content'])) {
                    $content = $item->children($namespaces['content']);
                    $desc = (string) ($content->encoded ?? '');
                }

                $articles[] = [
                    'title' => (string) ($item->title ?? ''),
                    'link' => $link,
                    'description' => $desc,
                    'image' => $image,
                    'author' => $author,
                    'source' => $source,
                ];
            }

            return $articles;
        } catch (\Exception $e) {
            $this->warn("  Error: {$e->getMessage()}");
            return [];
        }
    }

    private function cleanDescription(?string $raw): ?string
    {
        $text = html_entity_decode(strip_tags($raw ?? ''));
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $text = trim($text);
        if (mb_strlen($text ?? '') > 500) {
            $text = mb_substr($text, 0, 497) . '...';
        }
        return $text ?: null;
    }

    private function rewriteTitles(array &$pending, string $language): void
    {
        $gemini = new HuggingFaceService();
        if (!config('services.huggingface.api_key')) {
            return;
        }

        $this->line('Rewriting titles with AI...');
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $i => &$art) {
            $articleText = !empty($art['ai_summarized'])
                ? $art['description']
                : trim(strip_tags($art['description'] ?? ''));
            if (strlen($articleText) > 50 && !empty($art['title'])) {
                $newTitle = $gemini->rewriteTitle($art['title'], $articleText, $language);
                if ($newTitle) {
                    Log::info('HF: title rewritten', ['old' => mb_substr($art['title'], 0, 40), 'new' => mb_substr($newTitle, 0, 40)]);
                    $art['title'] = $newTitle;
                } else {
                    Log::info('HF: title rewrite failed', ['title' => mb_substr($art['title'] ?? '', 0, 40)]);
                }
            }
            $bar->advance();
        }
        unset($art);

        $bar->finish();
        $this->newLine();
    }

    private function normalizeTitle(string $title): string
    {
        return preg_replace('/[^a-z0-9\s\x{0900}-\x{097F}]/u', '', mb_strtolower(trim($title)));
    }

    private function summarizeWithGemini(array &$pending, string $language): void
    {
        $gemini = new HuggingFaceService();
        if (!config('services.huggingface.api_key')) {
            return;
        }

        $this->line('Summarizing with Gemini AI...');
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $i => &$art) {
            if (!empty($art['link'])) {
                $summary = $gemini->summarizeUrl($art['link'], $language, 70, $art['title'] ?? '');
                if ($summary) {
                    $art['description'] = $summary;
                    $art['ai_summarized'] = true;
                    // Log::info('HF: summarized', ['title' => mb_substr($art['title'] ?? '', 0, 60)]);
                } else {
                    // Log::info('HF: failed (null)', ['title' => mb_substr($art['title'] ?? '', 0, 60)]);
                }
            }
            $bar->advance();
        }
        unset($art);

        $bar->finish();
        $this->newLine();
    }
}
