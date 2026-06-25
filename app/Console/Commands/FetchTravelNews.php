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

class FetchTravelNews extends Command
{
    protected $signature = 'news:fetch-travel
        {--limit=50 : Max articles to insert per run}
        {--source= : Custom URL to scrape (overrides default)}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch travel & tourism news from TravelMedia.in';

    private string $defaultUrl = 'https://travelmedia.in/news.html';

    public function handle(): int
    {
        $language = $this->ensureLanguage();
        $category = $this->ensureTravelCategory($language->id);
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $existingTitles = News::pluck('title')->map(fn ($t) => $this->normalizeTitle($t))->flip();

        $sourceUrl = $this->option('source') ?: $this->defaultUrl;

        $this->line("Fetching listing page: {$sourceUrl}");
        $articles = $this->scrapeListingPage($sourceUrl);

        if (empty($articles)) {
            $this->warn('No articles found on the listing page.');

            return Command::SUCCESS;
        }

        $this->info('Found ' . count($articles) . ' articles on listing page.');

        // Deduplicate against existing data
        $pending = [];
        $seenLinks = [];
        $seenTitles = [];

        foreach ($articles as $art) {
            if (count($pending) >= $limit) {
                break;
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

        if (empty($pending)) {
            $this->warn('No new articles to insert.');

            return Command::SUCCESS;
        }

        $this->info('New articles to process: ' . count($pending));

        // Fetch individual article pages for description, image, and author
        $this->line('Fetching article pages...');
        $this->fetchArticlePages($pending);

        // Drop articles that still have no image after page fetch
        $pending = array_values(array_filter($pending, fn ($art) => ! empty($art['image'])));

        if (empty($pending)) {
            $this->warn('No articles with images found after fetching pages.');

            return Command::SUCCESS;
        }

        $this->info('Articles with images: ' . count($pending));

        $this->summarizeArticles($pending);

        // Insert into database
        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '', $art['title']);
            $author = $art['author'] ?: 'TravelMedia.in';

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

    /**
     * Scrape the TravelMedia.in listing page and extract article links + titles.
     */
    private function scrapeListingPage(string $url): array
    {
        try {
            $client = $this->makeClient(10);
            $response = $client->get($url);
            $html = (string) $response->getBody();

            $articles = [];
            $seen = [];

            // Match article links: href="https://travelmedia.in/YYYY/MM/DD/slug.html">Title</a>
            preg_match_all(
                '/<a[^>]+href=["\']((https?:\/\/travelmedia\.in\/\d{4}\/\d{2}\/\d{2}\/[^"\']+\.html))["\'][^>]*>([^<]+)<\/a>/i',
                $html,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $m) {
                $link = trim($m[1]);
                $title = trim(html_entity_decode($m[3]));

                // Skip navigation / non-article links
                if (empty($title) || mb_strlen($title) < 15) {
                    continue;
                }

                // Skip privacy, profile, and other non-news pages
                if (preg_match('/\b(privacy|profile|contact|submit|advertise)\b/i', $link)) {
                    continue;
                }

                // Deduplicate by link within this scrape
                if (isset($seen[$link])) {
                    continue;
                }
                $seen[$link] = true;

                $articles[] = [
                    'title' => $title,
                    'link' => $link,
                    'description' => '',
                    'image' => null,
                    'author' => '',
                    'source' => 'TravelMedia.in',
                ];
            }

            return $articles;
        } catch (\Exception $e) {
            $this->warn("  Error scraping listing: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Fetch individual article pages concurrently to extract description, image, and author.
     */
    private function fetchArticlePages(array &$pending): void
    {
        $client = $this->makeClient(8, 5);

        $requests = function () use ($pending) {
            foreach ($pending as $i => $art) {
                yield $i => new Request('GET', $art['link']);
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use (&$pending) {
                $html = (string) $response->getBody();

                // --- Image ---
                $image = null;
                // og:image
                if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $image = $m[1];
                }
                if (! $image && preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $m)) {
                    $image = $m[1];
                }
                // Fallback: first large image in article body
                if (! $image) {
                    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $imgMatches);
                    foreach ($imgMatches[1] as $imgUrl) {
                        if (str_contains($imgUrl, 'travelmedia.in') && preg_match('/\.(jpg|jpeg|png|avif|webp)/i', $imgUrl)) {
                            // Skip tiny icons and logos
                            if (! preg_match('/(favicon|logo|icon|sprite|badge)/i', $imgUrl)) {
                                $image = $imgUrl;
                                break;
                            }
                        }
                    }
                }
                $pending[$index]['image'] = $image;

                // --- Description ---
                $paragraphs = [];

                // og:description
                if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $paragraphs[] = html_entity_decode($m[1]);
                }
                if (empty($paragraphs) && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $paragraphs[] = html_entity_decode($m[1]);
                }

                // Body paragraphs
                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pTags);
                $wordCount = 0;
                $skipPhrases = ['skip to navigation', 'skip to main', 'skip to content', 'advertisement',
                    'read more', 'related:', 'sign up', 'newsletter', 'terms of service',
                    'privacy policy', 'all rights reserved', 'cookie', 'subscribe',
                    'trending now', 'recommended', 'also read', 'follow us', 'get in touch',
                    'about us', 'news categories', 'copyright'];
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

                if (! empty($paragraphs)) {
                    $pending[$index]['description'] = implode(' ', $paragraphs);
                }

                // --- Author ---
                if (empty($pending[$index]['author'])) {
                    $author = '';
                    if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $author = trim($m[1]);
                    }
                    if (! $author && preg_match('/<meta[^>]+property=["\']article:author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $author = trim($m[1]);
                    }
                    if (! $author && preg_match('/<a[^>]+href=["\'][^"\']*profile[^"\']*["\'][^>]*>([^<]+)<\/a>/i', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if (! $author && preg_match('/<span[^>]*class=["\'][^"\']*\bauthor\b[^"\']*["\'][^>]*>(.+?)<\/span>/is', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if (! $author && preg_match('/<a[^>]*rel=["\']author["\'][^>]*>(.+?)<\/a>/is', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if ($author) {
                        $pending[$index]['author'] = $author;
                    }
                }
            },
            'rejected' => function ($reason, $index) {
                // Silently skip failed page fetches
            },
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
            $text = rtrim($text, ',;:') . '.';
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
                $rewrittenTitle = $summarizer->summarize($text, 10, 'english');
                if ($rewrittenTitle) {
                    $art['title'] = $rewrittenTitle;
                }

                $summary = $summarizer->summarize($text, 75, 'english');
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
            ['code' => 'en'],
            ['name' => 'English', 'is_active' => 1]
        );
    }

    private function ensureTravelCategory(int $languageId): Category
    {
        return Category::firstOrCreate(
            ['name' => 'Travel', 'language_id' => $languageId],
            ['is_active' => 1]
        );
    }

    private function makeClient(int $timeout = 10, int $connectTimeout = null): Client
    {
        $options = [
            'timeout' => $timeout,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
        ];

        if ($connectTimeout) {
            $options['connect_timeout'] = $connectTimeout;
        }

        if ($this->option('no-verify')) {
            $options['verify'] = false;
        }

        return new Client($options);
    }
}
