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

class FetchSportsNews extends Command
{
    protected $signature = 'news:fetch-sports
        {--limit=50 : Max articles to insert per run}
        {--source= : Custom RSS feed URL}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch sports news from ESPNcricinfo and The Bridge';

    private array $feeds = [
        [
            'url' => 'https://www.espncricinfo.com/rss/content/story/feeds/0.xml',
            'source' => 'ESPNcricinfo',
        ],
        [
            'url' => 'https://thebridge.in/feed',
            'source' => 'The Bridge',
        ],
    ];

    public function handle(): int
    {
        $language = $this->ensureLanguage();
        $category = $this->ensureSportsCategory($language->id);
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $existingTitles = News::pluck('title')->map(fn ($t) => $this->normalizeTitle($t))->flip();
        $feedUrls = $this->option('source') ? [['url' => $this->option('source'), 'source' => 'Custom']] : $this->feeds;

        $pending = [];
        $seenLinks = [];
        $seenTitles = [];

        foreach ($feedUrls as $feed) {
            if (count($pending) >= $limit) {
                break;
            }
            $feedUrl = $feed['url'];
            $source = $feed['source'];
            $this->line("Fetching: {$source}");
            $articles = $this->parseFeed($feedUrl, $source);
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

        // Fetch article pages for descriptions and author where needed
        $sourceFallbacks = ['ESPNcricinfo', 'The Bridge', 'Custom'];
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

        // Insert into database
        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '', $art['title']);
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: ($art['source'] ?? 'Sports News');

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

                // Image
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

                // Author
                $author = '';
                if (isset($namespaces['dc'])) {
                    $dc = $item->children($namespaces['dc']);
                    $author = (string) ($dc->creator ?? '');
                }
                if (! $author) {
                    $author = (string) ($item->author ?? '');
                }

                // Description
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

                // og:description
                if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $paragraphs[] = $m[1];
                }
                if (empty($paragraphs) && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m2)) {
                    $paragraphs[] = $m2[1];
                }

                // Body paragraphs
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
                if ($originalIndex !== false && ! empty($paragraphs)) {
                    $pending[$originalIndex]['description'] = implode(' ', $paragraphs);
                }

                // Author from page
                if ($originalIndex !== false && empty($pending[$originalIndex]['author'])) {
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
            $text = preg_replace('/[^a-zA-Z0-9)]*$/', '', $text);
            $text = rtrim($text, ',;:').'.';
        } elseif ($wordCount < 68) {
            $needed = 70 - $wordCount;
            $text .= ' '.$this->supplementSummary($text, $title, max($needed, 5));
            $words = preg_split('/\s+/', trim($text ?? ''));
            if (count($words) > 75) {
                $words = array_slice($words, 0, 70);
                $text = implode(' ', $words);
                $text = preg_replace('/[^a-zA-Z0-9)]*$/', '', $text);
                $text = rtrim($text, ',;:').'.';
            }
        }

        return trim($text ?? '') ?: null;
    }

    private function supplementSummary(string $existing, string $title, int $targetWords = 25): string
    {
        $combined = strtolower($title.' '.$existing);

        $templates = [];

        if (preg_match('/match|score|win|lose|defeat|victory|series|tour|championship|final|semi|quarter/i', $combined)) {
            $templates[] = 'This match result adds another chapter to the ongoing competition, with implications for team standings and future fixtures in the tournament.';
            $templates[] = 'Fans and analysts will be closely examining this performance as the tournament progresses toward its decisive stages.';
        } elseif (preg_match('/transfer|signing|contract|trade|deal|join|move|buy|sell/i', $combined)) {
            $templates[] = 'This transfer marks a significant move in the sports world, with potential implications for team dynamics and future performance.';
            $templates[] = 'Industry experts are already analysing how this acquisition will impact the team\'s strategy and competitive standing.';
        } elseif (preg_match('/injury|recovery|return|comeback|surgery|fitness/i', $combined)) {
            $templates[] = 'The athlete\'s health and fitness journey is being closely monitored by fans and team management alike as they work toward full recovery.';
            $templates[] = 'Medical staff and coaching teams are taking all necessary precautions to ensure a safe and timely return to competitive action.';
        } elseif (preg_match('/record|milestone|century|hat-trick|landmark|personal best|achievement/i', $combined)) {
            $templates[] = 'This remarkable achievement adds to the athlete\'s illustrious career and will be remembered as a milestone in the sport\'s history.';
            $templates[] = 'The achievement underscores the athlete\'s exceptional talent, dedication, and contribution to their sport over the years.';
        } elseif (preg_match('/olymp|asian game|commonwealth|world cup|world championship/i', $combined)) {
            $templates[] = 'Preparations for this major sporting event are underway with athletes and teams working tirelessly to represent their nations at the highest level.';
            $templates[] = 'The event promises intense competition as the world\'s best athletes gather to showcase their skills and compete for glory.';
        }

        if (empty($templates)) {
            $templates[] = 'This sports news has generated considerable interest among fans and analysts who are closely following developments in the sporting world.';
            $templates[] = 'The sporting landscape continues to evolve with exciting developments that capture the attention of audiences worldwide.';
        }

        $result = '';
        $added = 0;
        foreach ($templates as $sent) {
            $wc = str_word_count($sent);
            if ($added + $wc <= $targetWords) {
                $result .= ' '.$sent;
                $added += $wc;
            } else {
                break;
            }
        }

        return $result;
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
                $rewrittenTitle = $summarizer->summarize($text, 20, 'english');
                if ($rewrittenTitle) {
                    $art['title'] = $rewrittenTitle;
                }

                $summary = $summarizer->summarize($text, 55, 'english');
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
        return preg_replace('/[^a-z0-9\s]/', '', strtolower(trim($title)));
    }

    private function ensureLanguage(): Language
    {
        return Language::firstOrCreate(
            ['code' => 'EN'],
            ['name' => 'English', 'is_active' => 1]
        );
    }

    private function ensureSportsCategory(int $languageId): Category
    {
        return Category::firstOrCreate(
            ['name' => 'Sports', 'language_id' => $languageId],
            ['is_active' => 1]
        );
    }
}
