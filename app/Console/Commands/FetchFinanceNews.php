<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\Category;
use App\Models\Language;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;

class FetchFinanceNews extends Command
{
    protected $signature = 'news:fetch-finance
        {--limit=15 : Max articles to insert}
        {--source= : RSS feed URL (defaults to Yahoo Finance + fallbacks)}
        {--no-verify : Bypass SSL verification (use for local dev with cert issues)}';

    protected $description = 'Fetch real-time finance news from free RSS feeds';

    private array $feeds = [
        'https://finance.yahoo.com/news/rssindex',
        'https://feeds.marketwatch.com/marketwatch/topstories',
        'https://search.cnbc.com/rs/search/combinedcms/view.xml?partnerId=wrss01&id=100003114',
    ];

    public function handle(): int
    {
        $language = $this->ensureLanguage();
        $category = $this->ensureFinanceCategory($language->id);
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $feedUrls = $this->option('source') ? [$this->option('source')] : $this->feeds;

        // 1. Gather all unique new articles from RSS feeds
        $pending = [];

        foreach ($feedUrls as $feedUrl) {
            if (count($pending) >= $limit) {
                break;
            }
            $this->line("Fetching RSS: {$feedUrl}");
            $articles = $this->parseFeed($feedUrl);
            if (empty($articles)) {
                $this->warn("  No articles from this source.");
                continue;
            }
            $this->info("  Found " . count($articles) . " articles.");
            foreach ($articles as $art) {
                if (count($pending) >= $limit) {
                    break;
                }
                $link = $art['link'] ?? null;
                if (!$link || isset($existingLinks[$link])) {
                    continue;
                }
                if (empty($art['title'])) {
                    continue;
                }
                $pending[] = $art;
                $existingLinks[$link] = true;
            }
        }

        if (empty($pending)) {
            $this->warn('No new articles to insert.');
            return Command::SUCCESS;
        }

        $this->info('New articles to process: ' . count($pending));

        // 2. For articles missing descriptions, extract summary from their pages
        $needsFetch = [];
        foreach ($pending as $i => $art) {
            $desc = trim(strip_tags($art['description'] ?? ''));
            if (strlen($desc) < 20) {
                $needsFetch[$i] = $art['link'];
            }
        }

        if (!empty($needsFetch)) {
            $this->line('Fetching article pages for descriptions...');
            $this->fetchDescriptions($needsFetch, $pending);
        }

        // 3. Insert into database
        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $title = html_entity_decode(strip_tags($art['title']));
            $title = trim(preg_replace('/\s+/', ' ', $title));

            $desc = $this->cleanDescription($art['description'] ?? '');
            $image = $art['image'] ?? null;
            $author = $art['author'] ?? 'Financial News';

            News::create([
                'title' => mb_substr($title, 0, 255),
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
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Inserted: {$inserted}");
        return Command::SUCCESS;
    }

    private function cleanDescription(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $text = html_entity_decode(strip_tags($raw));
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $text = preg_replace('/^By\s+.+?[.!?]\s*/', '', $text);
        $text = preg_replace('/^Summary\s*/', '', $text);
        $text = trim($text);

        $words = preg_split('/\s+/', $text);
        $wordCount = count($words);

        if ($wordCount > 75) {
            $words = array_slice($words, 0, 70);
            $text = implode(' ', $words);
            $text = preg_replace('/[^a-zA-Z0-9)]*$/', '', $text);
            $text = rtrim($text, ',;:') . '.';
        } elseif ($wordCount < 55) {
            $text .= ' ' . $this->supplementSummary($text);
            $words = preg_split('/\s+/', trim($text ?? ''));
            if (count($words) > 75) {
                $words = array_slice($words, 0, 70);
                $text = implode(' ', $words);
                $text = preg_replace('/[^a-zA-Z0-9)]*$/', '', $text);
                $text = rtrim($text, ',;:') . '.';
            }
        }

        return trim($text ?? '') ?: null;
    }

    private function supplementSummary(string $existing): string
    {
        $existingLower = strtolower($existing);
        $templates = [
            'stock' => ' The company continues to navigate market conditions while focusing on growth opportunities and shareholder value.',
            'market' => ' Analysts are closely watching these developments as they could have broader implications for the sector.',
            'finance' => ' Industry experts suggest this move could influence broader market sentiment in the coming weeks.',
            'earnings' => ' The results come amid changing economic conditions that could impact future performance.',
        ];

        foreach ($templates as $keyword => $suffix) {
            if (str_contains($existingLower, $keyword)) {
                return $suffix;
            }
        }
        return ' Market participants are monitoring the situation for its potential impact on the industry outlook.';
    }

    private function fetchDescriptions(array $needsFetch, array &$pending): void
    {
        $client = new Client([
            'timeout' => 8,
            'connect_timeout' => 5,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);

        if ($this->option('no-verify')) {
            $client = new Client([
                'timeout' => 8,
                'connect_timeout' => 5,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ],
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

                // Extract og:description
                if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $paragraphs[] = $m[1];
                }
                // Fall back to meta description
                if (empty($paragraphs) && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m2)) {
                    $paragraphs[] = $m2[1];
                }

                // Extract body paragraphs for more content depth
                $bodyHtml = null;
                foreach (['caas-body', 'article-body', 'article__body', 'body-content'] as $cls) {
                    if (preg_match('/<div[^>]*class="[^"]*' . preg_quote($cls, '/') . '[^"]*"[^>]*>(.+?)<\/div>/is', $html, $bodyMatch)) {
                        $bodyHtml = $bodyMatch[1];
                        break;
                    }
                }
                if (!$bodyHtml && preg_match('/<div[^>]*itemprop="articleBody"[^>]*>(.+?)<\/div>/is', $html, $bodyMatch)) {
                    $bodyHtml = $bodyMatch[1];
                }

                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $bodyHtml ?: $html, $pTags);
                $wordCount = 0;
                $skipPhrases = ['skip to navigation', 'skip to main', 'skip to content', 'advertisement',
                    'read more', 'related:', 'sign up', 'newsletter', 'terms of service',
                    'privacy policy', 'all rights reserved', 'ai assistant'];
                foreach ($pTags[1] as $p) {
                    $clean = trim(strip_tags($p));
                    $clean = html_entity_decode($clean);
                    $clean = preg_replace('/\s+/', ' ', $clean);
                    $lower = strtolower($clean);
                    $isNoise = false;
                    foreach ($skipPhrases as $skip) {
                        if (str_contains($lower, $skip)) { $isNoise = true; break; }
                    }
                    if (strlen($clean) > 60 && !$isNoise) {
                        $paragraphs[] = $clean;
                        $wordCount += str_word_count($clean);
                        if ($wordCount > 120) {
                            break;
                        }
                    }
                }

                // Find the original article index
                $originalIndex = array_search($needsFetch[$index], array_column($pending, 'link'));
                if ($originalIndex !== false && !empty($paragraphs)) {
                    $pending[$originalIndex]['description'] = implode(' ', $paragraphs);
                }
            },
            'rejected' => function ($reason, $index) {
                // Silently skip — will fall back to RSS description or empty
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    private function parseFeed(string $url): array
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
            if (!$xml) {
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

                // Image from media:content
                $image = null;
                if (isset($namespaces['media'])) {
                    $media = $item->children($namespaces['media']);
                    if (isset($media->content)) {
                        $attrs = $media->content->attributes();
                        $image = (string) ($attrs['url'] ?? '');
                    }
                }
                if (!$image && isset($item->enclosure)) {
                    $attrs = $item->enclosure->attributes();
                    if (str_starts_with((string) ($attrs['type'] ?? ''), 'image/')) {
                        $image = (string) ($attrs['url'] ?? '');
                    }
                }

                // Author
                $author = '';
                if (isset($namespaces['dc'])) {
                    $dc = $item->children($namespaces['dc']);
                    $author = (string) ($dc->creator ?? '');
                }
                if (!$author) {
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
                ];
            }

            return $articles;
        } catch (\Exception $e) {
            $this->warn("  Error: {$e->getMessage()}");
            return [];
        }
    }

    private function ensureLanguage(): Language
    {
        return Language::firstOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'is_active' => 1]
        );
    }

    private function ensureFinanceCategory(int $languageId): Category
    {
        return Category::firstOrCreate(
            ['name' => 'Finance', 'language_id' => $languageId],
            ['is_active' => 1]
        );
    }
}
