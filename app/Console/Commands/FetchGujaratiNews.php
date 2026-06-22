<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use App\Services\TextSummarizer;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class FetchGujaratiNews extends Command
{
    protected $signature = 'news:fetch-gujarati
        {--limit=50 : Max articles to insert per run}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Gujarati news from Divya Bhaskar, News18 Gujarati & Gujarat Samachar';

    private array $feeds = [
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-1035.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-1037.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-1038.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-969.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-970.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://divyabhaskar.co.in/rss-v1--category-12042.xml', 'source' => 'Divya Bhaskar'],
        ['url' => 'https://gujarati.news18.com/commonfeeds/v1/guj/rss/latest.xml', 'source' => 'News18 Gujarati'],
        ['url' => 'https://www.gujaratsamachar.com/rss/top-stories', 'source' => 'Gujarat Samachar'],
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

        // Parse all feeds into per-source buckets
        $buckets = [];
        foreach ($this->feeds as $feed) {
            $source = $feed['source'];
            $this->line("Fetching: {$source}");
            $articles = $this->parseFeed($feed['url'], $source);
            if (empty($articles)) {
                continue;
            }
            $this->info('  Found '.count($articles).' articles.');
            $unique = [];
            foreach ($articles as $art) {
                $link = $art['link'] ?? null;
                if (! $link || isset($existingLinks[$link])) {
                    continue;
                }
                if (empty($art['title']) || empty($art['image'])) {
                    continue;
                }
                $key = $this->normalizeTitle($art['title']);
                if (isset($existingTitles[$key])) {
                    continue;
                }
                $unique[$key] = $art;
            }
            if (! empty($unique)) {
                $buckets[$source] = array_values($unique);
            }
        }

        if (empty($buckets)) {
            $this->warn('No new articles to insert.');

            return Command::SUCCESS;
        }

        // Round-robin interleave across sources
        $pending = [];
        $seenLinks = [];
        $seenTitles = [];
        $sourceNames = array_keys($buckets);
        $indexes = array_fill_keys($sourceNames, 0);

        while (count($pending) < $limit) {
            $anyRemaining = false;
            foreach ($sourceNames as $src) {
                if (count($pending) >= $limit) {
                    break 2;
                }
                $idx = $indexes[$src];
                if (! isset($buckets[$src][$idx])) {
                    continue;
                }
                $anyRemaining = true;
                $art = $buckets[$src][$idx];
                $indexes[$src]++;

                $link = $art['link'];
                $normalized = $this->normalizeTitle($art['title']);
                if (isset($seenLinks[$link]) || isset($seenTitles[$normalized])) {
                    continue;
                }
                $pending[] = $art;
                $seenLinks[$link] = true;
                $seenTitles[$normalized] = true;
            }
            if (! $anyRemaining) {
                break;
            }
        }

        $this->info('New articles to process: '.count($pending));
        if (empty($pending)) {
            return Command::SUCCESS;
        }

        $this->summarizeArticles($pending);

        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '');
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: $art['source'];

            try {
                News::create([
                    'title' => mb_substr($art['title'], 0, 160),
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
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), '23000')) {
                    $this->warn('  Skipped duplicate: '.mb_substr($art['title'], 0, 60));
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
            if (! $xml || ! isset($xml->channel)) {
                return [];
            }

            $items = $xml->channel->item ?? [];
            $articles = [];

            foreach ($items as $item) {
                $link = trim((string) ($item->link ?? ''));
                if (! $link) {
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
                    if (! $image && isset($media->thumbnail)) {
                        $attrs = $media->thumbnail->attributes();
                        $image = (string) ($attrs['url'] ?? '');
                    }
                }
                if (! $image && isset($item->enclosure)) {
                    $attrs = $item->enclosure->attributes();
                    if (str_starts_with((string) ($attrs['type'] ?? ''), 'image/')) {
                        $image = (string) ($attrs['url'] ?? '');
                    }
                }
                if (! $image) {
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
                if (! $author) {
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
            $text = mb_substr($text, 0, 497).'...';
        }

        return $text ?: null;
    }

    private function normalizeTitle(string $title): string
    {
        return preg_replace('/[^a-z0-9\s\x{0900}-\x{097F}]/u', '', mb_strtolower(trim($title)));
    }

    private function summarizeArticles(array &$pending): void
    {
        $summarizer = new TextSummarizer;

        $this->line('Rewriting titles & summarizing articles...');
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $i => &$art) {
            $text = trim(strip_tags($art['description'] ?? ''));
            if (mb_strlen($text) > 40) {
                $rewrittenTitle = $summarizer->summarize($text, 10, 'gujarati');
                if ($rewrittenTitle) {
                    $art['title'] = $rewrittenTitle;
                }

                $summary = $summarizer->summarize($text, 75, 'gujarati');
                if ($summary) {
                    $art['description'] = $summary;
                    $art['ai_summarized'] = true;
                }
            }
            $bar->advance();
        }
        unset($art);

        $bar->finish();
        $this->newLine();
    }
}
