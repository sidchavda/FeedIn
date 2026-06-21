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

class FetchFinanceNews extends Command
{
    protected $signature = 'news:fetch-finance
        {--limit=200 : Max articles to insert per run}
        {--source= : RSS feed URL (defaults to Yahoo Finance + fallbacks)}
        {--no-verify : Bypass SSL verification (use for local dev with cert issues)}';

    protected $description = 'Fetch real-time finance news from free RSS feeds';

    private array $feeds = [
        // 'https://www.moneycontrol.com/rss/business.xml',
        'https://economictimes.indiatimes.com/rssfeeds/1715249553.cms',
        'https://www.livemint.com/rss/money',
        'https://www.business-standard.com/rss/finance-101.rss',
        // 'https://www.ndtvprofit.com/rss/latest',
        'https://news.google.com/rss/search?q=finance+india+stock+market&hl=en-IN&gl=IN&ceid=IN:en',
    ];

    public function handle(): int
    {
        $language = $this->ensureLanguage();
        $category = $this->ensureFinanceCategory($language->id);
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $existingTitles = News::pluck('title')->map(fn ($t) => $this->normalizeTitle($t))->flip();
        $feedUrls = $this->option('source') ? [$this->option('source')] : $this->feeds;

        // 1. Gather all unique new articles from RSS feeds
        $pending = [];
        $seenLinks = [];
        $seenTitles = [];

        foreach ($feedUrls as $feedUrl) {
            $this->line("Fetching RSS: {$feedUrl}");
            $sourceName = $this->sourceNameFromUrl($feedUrl);
            $articles = $this->parseFeed($feedUrl, $sourceName);
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
                $titleCheck = html_entity_decode(strip_tags($art['title']));
                if (! $this->isFinanceRelated($titleCheck)) {
                    continue;
                }
                if (empty($art['image'])) {
                    continue;
                }
                $normalized = $this->normalizeTitle($titleCheck);
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

        // 2. Fetch article pages for descriptions and author where needed
        $sourceFallbacks = ['Moneycontrol News', 'Economic Times', 'Livemint', 'Business Standard', 'NDTV Profit', 'Google News', 'Financial News'];
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
            $this->fetchDescriptions($needsFetch, $pending);
        }

        $this->summarizeArticles($pending);

        // 3. Insert into database
        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '', $art['title']);
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: ($art['source'] ?? 'Financial News');

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
                if ($e->getCode() === '23000') {
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

    private function isFinanceRelated(string $title): bool
    {
        $lower = strtolower($title);

        $exclude = [
            'cricket', 'football', 'tennis', 'badminton', 't20', 'ipl', 'world cup',
            'match', 'score', 'goal', 'hat-trick', 'hattrick', 'tournament',
            'championship', 'semi-final', 'final', 'quarterfinal', 'league',
            'gold medal', 'silver medal', 'bronze medal', 'olympics', 'olympic',
            'quote of the day', 'proverb of the day', 'thought for the day',
            'life lesson', 'daily wisdom', 'word of the day',
            'movie', 'film review', 'actor', 'actress', 'singer', 'celebrity',
            'fashion', 'beauty', 'recipe', 'cooking', 'food',
            'horoscope', 'astrology', 'zodiac',
            'obituary', 'died at', 'passed away',
            'traffic', 'weather update', 'earthquake',
        ];
        foreach ($exclude as $keyword) {
            if (str_contains($lower, $keyword)) {
                return false;
            }
        }

        $financeKeywords = [
            'stock', 'market', 'share', 'nifty', 'sensex', 'bse', 'nse',
            'ipo', 'listing', 'public issue', 'offer for sale',
            'profit', 'revenue', 'earnings', 'net income', 'net profit', 'loss',
            'margin', 'quarter', 'q1', 'q2', 'q3', 'q4', 'fy', 'annual result',
            'dividend', 'buyback', 'bonus', 'split', 'corporate action',
            'rupee', 'dollar', 'forex', 'fdi', 'fii', 'dii', 'currency',
            'bank', 'rbi', 'sebi', 'regulat', 'policy', 'rate cut', 'repo',
            'monetary', 'inflation', 'gdp', 'economy', 'economic', 'fiscal',
            'invest', 'fund', 'mutual fund', 'debt', 'equity', 'capital',
            'merger', 'acquis', 'takeover', 'deal', 'partnership',
            'trade', 'tariff', 'export', 'import',
            'ceo', 'cfo', 'chairman', 'board', 'executive',
            'budget', 'tax', 'gst',
            'fundrai', 'startup', 'venture capital', 'funding',
            ' crore ', ' lakh ', ' million ', ' billion ', 'trillion',
            'price', 'rate', 'interest', 'loan', 'credit',
            'energy', 'oil', 'gold', 'commodity', 'crude',
            'steel', 'coal', 'metal', 'mining',
            'automotive', 'auto', 'car', ' ev ', 'electric vehicle',
            'pharma', 'healthcare', 'hospital',
            'realty', 'real estate', 'property',
            'technology', 'software', 'digital',
            'it services', 'it sector', 'it firm', 'it company',
            'telecom', 'telecommunication',
            'defence', 'defense', 'aerospace',
            'infrastructure', 'infra', 'power', 'renewable',
            'insurance', 'fintech',
            'dow jones', 'nasdaq', 's&p', 'wall street',
            'bull run', 'bull market', 'bear market', 'rally',
            'volatility', 'correction', 'crash', 'recovery',
        ];
        foreach ($financeKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
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

        if (preg_match('/q[234]|quarter|fy\d{2}|annual.*result/i', $combined)) {
            $templates[] = 'This quarterly performance comes amid a challenging economic environment where companies are navigating changing market dynamics, shifting consumer demand, and evolving regulatory landscapes.';
            $templates[] = 'The results reflect broader sector trends as businesses adapt their strategies to address competitive pressures while maintaining focus on operational efficiency and cost management.';
            $templates[] = 'Industry analysts will be closely examining these numbers for insights into sector health and comparing them against broader economic indicators to assess the overall business climate.';
        } elseif (preg_match('/dividend|buyback|corporate action|shareholder|bonus|split/i', $combined)) {
            $templates[] = 'Such corporate actions are closely watched by investors as signals of company health, management confidence in future prospects, and commitment to delivering shareholder value over the long term.';
            $templates[] = 'Companies with strong cash positions often use dividends and buybacks to reward shareholders, and these announcements can significantly influence market perception and stock valuation in the near term.';
            $templates[] = 'Market participants generally view such announcements positively as they indicate financial strength and a management team that prioritizes shareholder returns alongside business growth objectives.';
        } elseif (preg_match('/ipo|listing|public issue|offer for sale/i', $combined)) {
            $templates[] = 'Investor interest in new listings remains strong as the primary market continues to see healthy activity across multiple sectors, driven by positive sentiment and ample liquidity in the market.';
            $templates[] = 'Market analysts suggest that successful listings could pave the way for more companies to tap public markets, potentially leading to a robust pipeline of offerings in the coming months.';
            $templates[] = 'The response to these offerings provides valuable signals about investor appetite for specific sectors and the overall health of the capital markets at the current juncture.';
        } elseif (preg_match('/profit|revenue|earnings|net (income|profit)|loss|margin/i', $combined)) {
            $templates[] = 'The financial performance highlights the company\'s competitive position in the current market landscape and its ability to navigate sector-specific headwinds, input cost pressures, and demand fluctuations.';
            $templates[] = 'Industry watchers will be keenly observing how these numbers align with broader economic indicators and competitor performance, as they provide valuable benchmarks for sector evaluation.';
            $templates[] = 'The results also offer insights into management\'s strategic execution, operational efficiency, and ability to sustain growth momentum in an increasingly competitive business environment.';
        } elseif (preg_match('/rupee|dollar|forex|fii|dii|foreign|fdi|currency/i', $combined)) {
            $templates[] = 'Flows from foreign and domestic institutional investors continue to influence market direction amid global economic uncertainties, interest rate decisions by central banks, and geopolitical developments.';
            $templates[] = 'Currency movements and global capital flows remain important factors for emerging markets like India, impacting everything from trade competitiveness to inflation dynamics and investment patterns.';
            $templates[] = 'Analysts are closely tracking these flows as they often serve as leading indicators for market direction and investor sentiment toward Indian assets in the global context.';
        } elseif (preg_match('/sensex|nifty|benchmark|index|bse|nse|market.*rally|bull.*run/i', $combined)) {
            $templates[] = 'Indian equity benchmarks have been navigating a period of volatility influenced by global cues, domestic economic data releases, corporate earnings trajectories, and shifts in monetary policy expectations.';
            $templates[] = 'Market participants are adopting a stock-specific approach given the mixed signals from global markets and the varying performance across sectors in the domestic economy.';
            $templates[] = 'Analysts recommend maintaining a diversified portfolio with a focus on quality stocks that have strong fundamentals and reasonable valuations in the current market environment.';
        } elseif (preg_match('/regulat|rbi|sebi|policy|rate cut|repo|monetary/i', $combined)) {
            $templates[] = 'Regulatory developments and policy decisions play a crucial role in shaping market dynamics, influencing everything from liquidity conditions to sector-specific growth prospects and compliance requirements.';
            $templates[] = 'Market participants are closely analyzing the implications of these policy moves for different sectors and asset classes, adjusting their strategies accordingly.';
            $templates[] = 'Such regulatory actions often have far-reaching consequences for financial markets and require businesses to adapt their operations and compliance frameworks to the new guidelines.';
        } elseif (preg_match('/fundrai|invest|capital|equity|debt|acquis|merger/i', $combined)) {
            $templates[] = 'Capital raising and investment activities provide important signals about corporate confidence and growth plans, with companies leveraging various instruments to fund their expansion strategies.';
            $templates[] = 'These transactions are closely watched by market participants as they often reflect management\'s outlook on business prospects and the prevailing conditions in capital markets.';
            $templates[] = 'The terms and structure of such deals also offer insights into how investors and lenders are evaluating risk and return across different sectors of the economy.';
        }

        if (empty($templates)) {
            $templates[] = 'This development is part of ongoing changes in the financial and economic landscape that could influence market sentiment, investment strategies, and sectoral trends in the coming period.';
            $templates[] = 'Market participants and industry experts are closely tracking such news for its potential implications on broader economic outlook and specific sectors.';
            $templates[] = 'The evolving situation underscores the importance of staying informed about key developments that could shape market direction and create opportunities for informed investors.';
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

                // Extract body paragraphs — scan entire page <p> tags
                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pTags);
                $wordCount = 0;
                $skipPhrases = ['skip to navigation', 'skip to main', 'skip to content', 'advertisement',
                    'read more', 'related:', 'sign up', 'newsletter', 'terms of service',
                    'privacy policy', 'all rights reserved', 'ai assistant', 'cookie', 'subscribe',
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

                // Find the original article index
                $originalIndex = array_search($needsFetch[$index], array_column($pending, 'link'));
                if ($originalIndex !== false && ! empty($paragraphs)) {
                    $pending[$originalIndex]['description'] = implode(' ', $paragraphs);
                }

                // Extract author from page
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
            'rejected' => function ($reason, $index) {
                // Silently skip — will fall back to RSS description or empty
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
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

    private function sourceNameFromUrl(string $url): string
    {
        if (str_contains($url, 'moneycontrol.com')) {
            return 'Moneycontrol News';
        }
        if (str_contains($url, 'economictimes.indiatimes.com')) {
            return 'Economic Times';
        }
        if (str_contains($url, 'livemint.com')) {
            return 'Livemint';
        }
        if (str_contains($url, 'business-standard.com')) {
            return 'Business Standard';
        }
        if (str_contains($url, 'ndtvprofit.com')) {
            return 'NDTV Profit';
        }
        if (str_contains($url, 'news.google.com')) {
            return 'Google News';
        }

        return 'Financial News';
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
                $rewrittenTitle = $summarizer->summarize($text, 14, 'english');
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
}
