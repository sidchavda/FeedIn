<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Models\Category;
use App\Models\Language;
use App\Services\HuggingFaceService;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchPoliticsNews extends Command
{
    protected $signature = 'news:fetch-politics
        {--limit=50 : Max articles to insert per run}
        {--source= : Custom RSS feed URL}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch politics news from Frontline';

    private array $feeds = [
        [
            'url' => 'https://frontline.thehindu.com/politics/feeder/default.rss',
            'source' => 'Frontline',
        ],
    ];

    public function handle(): int
    {
        $language = $this->ensureLanguage();
        $category = $this->ensurePoliticsCategory($language->id);
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

        $sourceFallbacks = ['Frontline', 'Custom'];
        $needsFetch = [];
        foreach ($pending as $i => $art) {
            $desc = trim(strip_tags($art['description'] ?? ''));
            $needsDescription = strlen($desc) < 20;
            $needsAuthor = empty($art['author']) || in_array($art['author'], $sourceFallbacks);
            if ($needsDescription || $needsAuthor) {
                $needsFetch[$i] = $art['link'];
            }
        }

        if (!empty($needsFetch)) {
            $this->line('Fetching article pages...');
            $this->fetchPages($needsFetch, $pending);
        }

        $this->summarizeWithGemini($pending, 'english');
        $this->rewriteTitles($pending, 'english');

        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $title = html_entity_decode(strip_tags($art['title']));
            $title = trim(preg_replace('/\s+/', ' ', $title));

            $desc = !empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '', $title);
            if (!empty($art['ai_summarized'])) {
                Log::info('HF: stored', ['title' => mb_substr($title, 0, 60)]);
            }
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: ($art['source'] ?? 'Politics News');

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
                        if (str_contains($lower, $skip)) { $isNoise = true; break; }
                    }
                    if (strlen($clean) > 80 && !$isNoise) {
                        $paragraphs[] = $clean;
                        $wordCount += str_word_count($clean);
                        if ($wordCount > 120) {
                            break;
                        }
                    }
                }

                $originalIndex = array_search($needsFetch[$index], array_column($pending, 'link'));
                if ($originalIndex !== false && !empty($paragraphs)) {
                    $pending[$originalIndex]['description'] = implode(' ', $paragraphs);
                }

                if ($originalIndex !== false && empty($pending[$originalIndex]['author'])) {
                    $author = '';
                    if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $author = trim($m[1]);
                    }
                    if (!$author && preg_match('/<meta[^>]+property=["\']article:author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                        $author = trim($m[1]);
                    }
                    if (!$author && preg_match('/<span[^>]*class=["\'][^"\']*\bauthor\b[^"\']*["\'][^>]*>(.+?)<\/span>/is', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if (!$author && preg_match('/<a[^>]*rel=["\']author["\'][^>]*>(.+?)<\/a>/is', $html, $m)) {
                        $author = trim(strip_tags($m[1]));
                    }
                    if (!$author && preg_match('/<span[^>]*class=["\'][^"\']*\bbyline\b[^"\']*["\'][^>]*>(.+?)<\/span>/is', $html, $m)) {
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
        if (!$raw && !$title) {
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
        } elseif ($wordCount < 68) {
            $needed = 70 - $wordCount;
            $text .= ' ' . $this->supplementSummary($text, $title, max($needed, 5));
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

    private function supplementSummary(string $existing, string $title, int $targetWords = 25): string
    {
        $combined = strtolower($title . ' ' . $existing);

        $templates = [];

        if (preg_match('/election|vote|poll|constituency|candidate|party|campaign|rally/i', $combined)) {
            $templates[] = 'This political development comes at a crucial time, with significant implications for electoral dynamics and party strategies across the region.';
            $templates[] = 'Political analysts are closely watching how this will shape voter sentiment and influence the larger political landscape.';
        } elseif (preg_match('/bill|law|legislation|parliament|amendment|act|policy|scheme|ordinance/i', $combined)) {
            $templates[] = 'The legislative move is expected to spark debate among political parties and civil society groups regarding its broader implications.';
            $templates[] = 'Lawmakers and policy experts continue to examine the potential impact of this decision on governance and public welfare.';
        } elseif (preg_match('/protest|strike|agitation|demonstration|opposition|movement|rally|sit-in/i', $combined)) {
            $templates[] = 'The protest highlights growing public sentiment on the issue, with various political groups weighing in on the matter.';
            $templates[] = 'Authorities are closely monitoring the situation as civil society organisations and political parties mobilise support for their respective positions.';
        } elseif (preg_match('/appoint|resign|dismiss|cabinet reshuffle|portfolio|minister|secretary|commission/i', $combined)) {
            $templates[] = 'This administrative change is seen as a strategic move that could alter the balance of power within the government apparatus.';
            $templates[] = 'Observers are analysing how this reshuffle will impact policy implementation and governance efficiency in the coming months.';
        } elseif (preg_match('/supreme court|high court|judgment|verdict|bench|justice|judicial|constitution|petition/i', $combined)) {
            $templates[] = 'The judicial ruling has significant constitutional implications and is expected to influence future legal interpretations on the subject.';
            $templates[] = 'Legal experts and political commentators are assessing the far-reaching consequences of this decision for Indian democracy.';
        }

        if (empty($templates)) {
            $templates[] = 'This political development has generated significant discussion among analysts and commentators tracking the evolving political landscape.';
            $templates[] = 'The developments are being closely followed by political observers who see this as a significant moment in contemporary politics.';
        }

        $result = '';
        $added = 0;
        foreach ($templates as $sent) {
            $wc = str_word_count($sent);
            if ($added + $wc <= $targetWords) {
                $result .= ' ' . $sent;
                $added += $wc;
            } else {
                break;
            }
        }

        return $result;
    }

    private function summarizeWithGemini(array &$pending, string $language): void
    {
        $gemini = new HuggingFaceService();
        if (!config('services.huggingface.api_key')) {
            return;
        }

        $this->line('Summarizing with AI...');
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $i => &$art) {
            if (!empty($art['link'])) {
                $summary = $gemini->summarizeUrl($art['link'], $language, 70, $art['title'] ?? '');
                if (!$summary) {
                    $rssText = trim(strip_tags($art['description'] ?? ''));
                    $rssText = preg_replace('/\s+/', ' ', $rssText);
                    if (strlen($rssText) > 50) {
                        $summary = $gemini->summarizeText($rssText, $language, 70, $art['title'] ?? '');
                    }
                }
                if ($summary) {
                    $art['description'] = $summary;
                    $art['ai_summarized'] = true;
                    Log::info('HF: summarized', ['title' => mb_substr($art['title'] ?? '', 0, 60)]);
                } else {
                    Log::info('HF: failed (null)', ['title' => mb_substr($art['title'] ?? '', 0, 60)]);
                }
            }
            $bar->advance();
        }
        unset($art);

        $bar->finish();
        $this->newLine();
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
        return preg_replace('/[^a-z0-9\s]/', '', strtolower(trim($title)));
    }

    private function ensureLanguage(): Language
    {
        return Language::firstOrCreate(
            ['code' => 'EN'],
            ['name' => 'English', 'is_active' => 1]
        );
    }

    private function ensurePoliticsCategory(int $languageId): Category
    {
        return Category::firstOrCreate(
            ['name' => 'Politics', 'language_id' => $languageId],
            ['is_active' => 1]
        );
    }
}
