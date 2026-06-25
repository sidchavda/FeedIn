<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use App\Services\TextSummarizer;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class FetchAkilaNews extends Command
{
    protected $signature = 'news:fetch-akila
        {--limit=50 : Max articles to insert per run}
        {--source= : Custom URL to scrape (overrides default)}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Gujarati news from Sandesh';

    private array $feeds = [
        ['url' => 'https://sandesh.com/rss/gujarat.xml', 'source' => 'Sandesh'],
        ['url' => 'https://sandesh.com/rss/india.xml', 'source' => 'Sandesh'],
        ['url' => 'https://sandesh.com/rss/entertainment.xml', 'source' => 'Sandesh'],
        ['url' => 'https://sandesh.com/rss/sports.xml', 'source' => 'Sandesh'],
    ];

    public function handle(): int
    {
        $language = $this->ensureLanguage();
        $category = $this->ensureAkilaCategory($language->id);
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $existingTitles = News::pluck('title')->map(fn ($t) => $this->normalizeTitle($t))->flip();
        $feedList = $this->option('source') ? [['url' => $this->option('source'), 'source' => 'Sandesh']] : $this->feeds;

        // Gather all unique new articles from RSS feeds
        $pending = [];
        $seenLinks = [];
        $seenTitles = [];

        foreach ($feedList as $feed) {
            $feedUrl = $feed['url'];
            $sourceName = $feed['source'];
            $this->line("Fetching: {$sourceName} - {$feedUrl}");
            $articles = $this->parseFeed($feedUrl, $sourceName);
            if (empty($articles)) {
                $this->warn('  No articles from this source.');
                continue;
            }
            $this->info('  Found ' . count($articles) . ' articles.');
            foreach ($articles as $art) {
                if (count($pending) >= $limit) {
                    break 2;
                }
                $link = $art['link'] ?? null;
                if (! $link || isset($existingLinks[$link]) || isset($seenLinks[$link])) {
                    continue;
                }
                if (empty($art['title'])) {
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

        $this->summarizeArticles($pending);

        // Insert into database
        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '', $art['title']);
            $author = $art['author'] ?: $art['source'];

            try {
                News::create([
                    'title' => mb_substr($art['title'], 0, 160),
                    'link' => $art['link'],
                    'language_id' => $language->id,
                    'category_id' => $category->id,
                    'description' => $desc,
                    'image' => $art['image'] ?? null,
                    'author' => $author,
                    'status' => 1,
                    'push_notification' => 0,
                ]);
                $inserted++;
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), '23000')) {
                    $this->warn('  Skipped duplicate: ' . mb_substr($art['title'], 0, 60));
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

    private function parseFeed(string $url, string $sourceName = ''): array
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
            if (! $xml) {
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

                // Image from media:content, media:thumbnail
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
                // Fallback: extract first <img> from description HTML
                if (! $image) {
                    $descHtml = (string) ($item->description ?? '');
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $descHtml, $m)) {
                        $image = $m[1];
                    }
                }

                // Author
                $author = '';
                if (isset($namespaces['dc'])) {
                    $dc = $item->children($namespaces['dc']);
                    $author = (string) ($dc->creator ?? '');
                }
                if (! $author) {
                    $author = (string) ($item->author ?? '');
                }

                // Description from content:encoded if description is empty
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
                    'source' => $sourceName,
                ];
            }

            return $articles;
        } catch (\Exception $e) {
            $this->warn("  Error parsing feed: {$e->getMessage()}");

            return [];
        }
    }

    private function cleanDescription(?string $raw, string $title = ''): ?string
    {
        if (! $raw && ! $title) {
            return null;
        }

        $text = html_entity_decode(strip_tags($raw ?? ''));
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $text = trim($text);

        if (mb_strlen($text ?? '') > 500) {
            $text = mb_substr($text, 0, 497).'...';
        }

        return $text ?: null;
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
                // Title: 15-20 words for 1-2 lines
                $rewrittenTitle = $summarizer->summarize($text, 12, 'gujarati');
                if ($rewrittenTitle) {
                    $art['title'] = $rewrittenTitle;
                }

                // Description: 80-100 words for 4-5 lines
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

    private function normalizeTitle(string $title): string
    {
        return preg_replace('/[^a-z0-9\s\x{0900}-\x{097F}]/u', '', mb_strtolower(trim($title)));
    }

    private function ensureLanguage(): Language
    {
        return Language::firstOrCreate(
            ['code' => 'GJ'],
            ['name' => 'Gujarati', 'is_active' => 1]
        );
    }

    private function ensureAkilaCategory(int $languageId): Category
    {
        return Category::firstOrCreate(
            ['name' => 'ગુજરાતી', 'language_id' => $languageId],
            ['is_active' => 1]
        );
    }
}
