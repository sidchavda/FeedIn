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

class FetchVtvGujaratiNews extends Command
{
    protected $signature = 'news:fetch-vtv-gujarati
        {--limit=50 : Max articles to insert per run}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Gujarati news';

    private array $feeds = [
        ['url' => 'https://khabarchhe.com/rss', 'source' => 'Khabarchhe'],
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
            $this->line("Fetching: {$feed['source']}");
            $articles = $this->parseFeed($feed['url'], $feed['source']);
            if (empty($articles)) {
                $this->warn('  No articles from this source.');
                continue;
            }
            $this->info('  Found '.count($articles).' articles.');

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

        $this->info('New articles to process: '.count($pending));

        $sourceFallbacks = ['Khabarchhe'];
        $needsFetch = [];
        foreach ($pending as $i => $art) {
            $desc = trim(strip_tags($art['description'] ?? ''));
            $needsDescription = strlen($desc) < 20;
            $needsAuthor = empty($art['author']) || in_array($art['author'], $sourceFallbacks);
            if ($needsDescription || $needsAuthor) {
                $needsFetch[$i] = $art['link'];
            }
        }

        if (! empty($needsFetch)) {
            $this->line('Fetching article pages...');
            $this->fetchPages($needsFetch, $pending);
        }

        $this->summarizeArticles($pending);

        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '', $art['title']);
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

    private function fetchPages(array $needsFetch, array &$pending): void
    {
        $client = new Client([
            'timeout' => 8,
            'connect_timeout' => 5,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
        ]);

        if ($this->option('no-verify')) {
            $client = new Client([
                'timeout' => 8,
                'connect_timeout' => 5,
                'verify' => false,
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
            ]);
        }

        $requests = function () use ($needsFetch) {
            foreach ($needsFetch as $i => $url) {
                yield $i => new Request('GET', $url);
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use (&$pending, $needsFetch) {
                $html = (string) $response->getBody();
                $paragraphs = [];

                if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $paragraphs[] = $m[1];
                }
                if (empty($paragraphs) && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m2)) {
                    $paragraphs[] = $m2[1];
                }

                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pTags);
                $wordCount = 0;
                $skipPhrases = ['skip to navigation', 'skip to main', 'skip to content', 'advertisement',
                    'read more', 'related:', 'sign up', 'newsletter', 'terms of service',
                    'privacy policy', 'all rights reserved', 'cookie', 'subscribe',
                    'trending now', 'recommended', 'also read', 'follow us'];
                foreach ($pTags[1] as $p) {
                    $clean = trim(strip_tags($p));
                    $clean = html_entity_decode($clean);
                    $clean = preg_replace('/\s+/', ' ', $clean);
                    $lower = strtolower($clean);
                    $isNoise = false;
                    foreach ($skipPhrases as $skip) {
                        if (str_contains($lower, $skip)) {
                            $isNoise = true;
                            break;
                        }
                    }
                    if (strlen($clean) > 80 && ! $isNoise) {
                        $paragraphs[] = $clean;
                        $wordCount += str_word_count($clean);
                        if ($wordCount > 120) {
                            break;
                        }
                    }
                }

                $originalIndex = array_search($needsFetch[$index], array_column($pending, 'link'));
                if ($originalIndex === false) {
                    return;
                }

                if (! empty($paragraphs)) {
                    $pending[$originalIndex]['description'] = implode(' ', $paragraphs);
                }

                if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $pending[$originalIndex]['image'] = $m[1];
                }

                if (empty($pending[$originalIndex]['author'])) {
                    $author = '';
                    if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $author = trim($m[1]);
                    }
                    if (! $author && preg_match('/<meta[^>]+property=["\']article:author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $author = trim($m[1]);
                    }
                    if (! $author && preg_match('/<span[^>]*class=["\'][^"\']*\bauthor\b[^"\']*["\'][^>]*>(.+?)<\/span>/is', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if (! $author && preg_match('/<a[^>]*rel=["\']author["\'][^>]*>(.+?)<\/a>/is', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if (! $author && preg_match('/<span[^>]*class=["\'][^"\']*\bbyline\b[^"\']*["\'][^>]*>(.+?)<\/span>/is', $html, $m)) {
                        $author = trim(strip_tags(preg_replace('/^By\s*/i', '', $m[1])));
                    }
                    if ($author) {
                        $pending[$originalIndex]['author'] = $author;
                    }
                }
            },
            'rejected' => function ($reason, $index) {},
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    private function cleanDescription(?string $raw, string $title = ''): ?string
    {
        if (! $raw && ! $title) {
            return null;
        }

        $text = html_entity_decode(strip_tags($raw ?? ''));
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $text = preg_replace('/^By\s+.+?[.!?]\s*/', '', $text);
        $text = preg_replace('/^Summary\s*/', '', $text);
        $text = trim($text);

        $words = preg_split('/\s+/', $text);
        $wordCount = count($words);

        if ($wordCount > 75) {
            $words = array_slice($words, 0, 70);
            $text = implode(' ', $words);
            $text = preg_replace('/[^a-zA-Z0-9\x{0900}-\x{097F})]*$/', '', $text);
            $text = rtrim($text, ',;:').'.';
        }

        return trim($text ?? '') ?: null;
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

    private function normalizeTitle(string $title): string
    {
        return preg_replace('/[^a-z0-9\s\x{0900}-\x{097F}]/u', '', mb_strtolower(trim($title)));
    }
}
