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

class FetchAbpasmitaNews extends Command
{
    protected $signature = 'news:fetch-abpasmita
        {--limit=50 : Max articles to insert per run}
        {--source= : Custom RSS feed URL}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Gujarati news from ABP Asmita (X/Twitter via Nitter RSS)';

    private array $feeds = [
        [
            'url' => 'https://nitter.net/abpasmitatv/rss',
            'source' => 'ABP Asmita',
        ],
        [
            'url' => 'https://nitter.poast.org/abpasmitatv/rss',
            'source' => 'ABP Asmita',
        ],
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
        $feedUrls = $this->option('source') ? [['url' => $this->option('source'), 'source' => 'ABP Asmita']] : $this->feeds;

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

                // Skip retweets
                if (str_starts_with(mb_strtolower($art['title']), 'rt @')) {
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

        // Fetch article pages for longer descriptions (from linked URLs in tweets)
        $needsFetch = [];
        foreach ($pending as $i => $art) {
            $desc = trim(strip_tags($art['description'] ?? ''));
            $needsDescription = strlen($desc) < 80;

            // Also check if there's a linked article URL (e.g., gujarati.abplive.com)
            $hasArticleLink = ! empty($art['article_link']);

            if ($needsDescription && $hasArticleLink) {
                $needsFetch[$i] = $art['article_link'];
            } elseif ($needsDescription) {
                $needsFetch[$i] = $art['link'];
            }
        }

        if (! empty($needsFetch)) {
            $this->line('Fetching article pages for descriptions...');
            $this->fetchPages($needsFetch, $pending);
        }

        // First attempt AI summarization (may rewrite title + description)
        $this->summarizeArticles($pending);

        // Then supplement short descriptions with Gujarati context if still too short
        $this->supplementDescriptions($pending);

        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '');
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: 'ABP Asmita';

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
                'timeout' => 15,
                'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; TEJ/1.0)'],
            ]);

            if ($this->option('no-verify')) {
                $client = new Client([
                    'timeout' => 15,
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

                // Image from description CDATA (nitter embeds <img> tags)
                $image = null;
                $descHtml = (string) ($item->description ?? '');
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $descHtml, $m)) {
                    $image = $m[1];
                }
                // Try media:content namespace
                if (isset($namespaces['media'])) {
                    $media = $item->children($namespaces['media']);
                    if (isset($media->content)) {
                        $attrs = $media->content->attributes();
                        $image = (string) ($attrs['url'] ?? '') ?: $image;
                    }
                }

                // Author from dc:creator
                $author = '';
                if (isset($namespaces['dc'])) {
                    $dc = $item->children($namespaces['dc']);
                    $author = (string) ($dc->creator ?? '');
                }
                if (! $author) {
                    $author = (string) ($item->author ?? '');
                }

                // Clean title: keep only Gujarati text, one line
                $title = (string) ($item->title ?? '');
                // Remove trailing hashtags like #ArvindKejriwal #abpasmita
                $title = preg_replace('/\s+#\S+(\s+#\S+)*\s*$/', '', $title);
                // Remove "| ABP Asmita" or "| abp Asmita" at the very end
                $title = preg_replace('/\s*\|\s*[aA][bB][pP]\s+[aA]smita\s*$/i', '', $title);
                // If title has "English | ગુજરાતી" pattern, keep only the Gujarati part after |
                if (preg_match('/\|\s*(.+)$/u', $title, $m)) {
                    $gujaratiPart = trim($m[1]);
                    // Only use Gujarati part if it contains Gujarati characters
                    if (preg_match('/[\x{0A80}-\x{0AFF}]/u', $gujaratiPart)) {
                        $title = $gujaratiPart;
                    }
                }
                // Remove any trailing pipe separators
                $title = preg_replace('/\s*\|\s*$/', '', $title);
                // Collapse whitespace and remove line breaks
                $title = preg_replace('/\s+/', ' ', $title);
                $title = trim($title);

                // Extract article link from nitter description (linked card)
                $articleLink = null;
                if (preg_match('/<a[^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*>/i', $descHtml, $m)) {
                    $potentialLink = $m[1];
                    // Only use links that go to abplive.com or other news domains (not nitter.net)
                    if (! str_contains($potentialLink, 'nitter.net') && ! str_contains($potentialLink, 'x.com')) {
                        $articleLink = $potentialLink;
                    }
                }

                // Extract tweet text from description
                $desc = html_entity_decode(strip_tags($descHtml));
                $desc = preg_replace('/\s+/', ' ', $desc);
                $desc = trim($desc);

                // Also extract the longer description from nitter's <small> card preview
                $cardDesc = '';
                if (preg_match('/<small>(.+?)<\/small>/is', $descHtml, $m)) {
                    $cardDesc = trim(strip_tags($m[1]));
                }

                $articles[] = [
                    'title' => $title ?: (string) ($item->title ?? ''),
                    'link' => $link,
                    'article_link' => $articleLink,
                    'description' => $cardDesc ?: $desc,
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
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
        ]);

        if ($this->option('no-verify')) {
            $client = new Client([
                'timeout' => 10,
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
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) use (&$pending, $needsFetch) {
                $html = (string) $response->getBody();
                $paragraphs = [];

                // Extract og:description
                if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $paragraphs[] = $m[1];
                }
                // Fallback to meta description
                if (empty($paragraphs) && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m2)) {
                    $paragraphs[] = $m2[1];
                }

                // Extract article paragraphs
                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pTags);
                $wordCount = 0;
                $skipPhrases = [
                    'skip to navigation', 'skip to main', 'skip to content',
                    'advertisement', 'read more', 'related:', 'sign up',
                    'newsletter', 'terms of service', 'privacy policy',
                    'all rights reserved', 'cookie', 'subscribe',
                    'trending now', 'recommended', 'also read', 'follow us',
                ];
                foreach ($pTags[1] as $p) {
                    $clean = trim(strip_tags($p));
                    $clean = html_entity_decode($clean);
                    $clean = preg_replace('/\s+/', ' ', $clean);
                    $lower = mb_strtolower($clean);
                    $isNoise = false;
                    foreach ($skipPhrases as $skip) {
                        if (mb_strpos($lower, $skip) !== false) {
                            $isNoise = true;
                            break;
                        }
                    }
                    if (mb_strlen($clean) > 80 && ! $isNoise) {
                        $paragraphs[] = $clean;
                        $wordCount += str_word_count($clean);
                        if ($wordCount > 250) {
                            break;
                        }
                    }
                }

                // Find the matching article
                $originalIndex = null;
                foreach ($pending as $idx => $art) {
                    $matchLink = $needsFetch[$index] ?? '';
                    if (($art['article_link'] ?? '') === $matchLink || $art['link'] === $matchLink) {
                        $originalIndex = $idx;
                        break;
                    }
                }

                if ($originalIndex !== null && ! empty($paragraphs)) {
                    $pending[$originalIndex]['description'] = implode(' ', $paragraphs);
                }
            },
            'rejected' => function ($reason, $index) {
                // Silently skip
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    private function supplementDescriptions(array &$pending): void
    {
        foreach ($pending as &$art) {
            $desc = trim(strip_tags($art['description'] ?? ''));
            $wordCount = str_word_count($desc);
            $title = $art['title'] ?? '';

            // Only supplement if description is still short (avoid supplement-only articles)
            if ($wordCount < 60 && ! empty($art['ai_summarized'])) {
                continue;
            }

            if ($wordCount < 60) {
                $needed = 80 - $wordCount;
                $supplement = $this->generateSupplement($title, $desc, max($needed, 10));
                $art['description'] = $desc.($desc ? ' ' : '').$supplement;
            }
        }
        unset($art);
    }

    private function generateSupplement(string $title, string $existing, int $targetWords = 30): string
    {
        $combined = mb_strtolower($title.' '.$existing);

        $templates = [];

        if (preg_match('/\x{0A95}\x{0AC7}[\x{0A9C}\x{0AB0}\x{0AA3}]|[\x{0AA8}\x{0ACB}\x{0A95}\x{0AB0}\x{0AC0}]/u', $combined)) {
            // Politics / government related keywords in Gujarati
            $templates[] = 'આ ઘટના ગુજરાતની રાજકીય પરિસ્થિતિ પર અસર કરી શકે છે અને રાજકીય પક્ષો દ્વારા તેના પર પ્રતિક્રિયાઓ આવી રહી છે. રાજકીય વિશ્લેષકો આ ઘટનાક્રમને નજીકથી અનુસરી રહ્યા છે અને તેના દૂરગામી પરિણામોનું મૂલ્યાંકન કરી રહ્યા છે.';
            $templates[] = 'ગુજરાતના રાજકીય ક્ષેત્રમાં આ મહત્વપૂર્ણ વિકાસ છે જે આગામી ચૂંટણીઓ અને રાજકીય સમીકરણોને પ્રભાવિત કરી શકે છે. વિવિધ રાજકીય પક્ષો અને નેતાઓ આ મુદ્દે પોતાના મત વ્યક્ત કરી રહ્યા છે.';
        } elseif (preg_match('/\x{0AB5}\x{0AB0}\x{0ACD}\x{0AB7}\x{0ABE}|[\x{0AAE}\x{0ACB}\x{0AA8}\x{0AB8}\x{0AC2}\x{0AA8}]|[\x{0AB5}\x{0AB0}\x{0ACD}\x{0AB8}\x{0ABE}\x{0AA6}]/u', $combined)) {
            // Weather / monsoon related
            $templates[] = 'હવામાન વિભાગે આગામી દિવસોમાં વધુ વરસાદની આગાહી કરી છે, જે ખેડૂતો અને સામાન્ય નાગરિકો માટે રાહતના સમાચાર છે. રાજ્ય સરકારે વરસાદી પરિસ્થિતિનો સામનો કરવા માટે તૈયારીઓ કરવાનું શરૂ કર્યું છે.';
            $templates[] = 'ગુજરાતમાં ચોમાસાની સીઝન શરૂ થઈ ગઈ છે અને હવામાન વિભાગના જણાવ્યા અનુસાર આગામી સમયમાં વધુ વરસાદની શક્યતા છે, જે ખેતી અને જળાશયો માટે ફાયદાકારક સાબિત થશે.';
        } elseif (preg_match('/\x{0AB8}\x{0ACB}\x{0AA8}\x{0AC1}\x{0A82}|[\x{0A9A}\x{0ABE}\x{0A82}\x{0AA6}\x{0AC0}]|\x{0A97}\x{0ACB}\x{0AB2}\x{0ACD}\x{0AA1}/u', $combined)) {
            // Gold / silver / price related
            $templates[] = 'સોના-ચાંદીના ભાવમાં આ ફેરફાર વૈશ્વિક બજારના પ્રભાવ, ડૉલર સામે રૂપિયાની સ્થિતિ અને રોકાણકારોની લાગણીને કારણે થયો છે. નિષ્ણાતોના મતે આગામી દિવસોમાં ભાવમાં વધુ ઉતાર-ચઢાવ જોવા મળી શકે છે.';
            $templates[] = 'બુલિયન માર્કેટમાં આ ફેરફાર રોકાણકારો માટે મહત્વપૂર્ણ છે, કારણ કે સોનું અને ચાંદી હંમેશા સુરક્ષિત રોકાણ વિકલ્પ માનવામાં આવે છે. બજાર નિષ્ણાતો રોકાણકારોને લાંબા ગાળાના દૃષ્ટિકોણથી રોકાણ કરવાની સલાહ આપે છે.';
        } elseif (preg_match('/\x{0A95}\x{0ACD}\x{0AB0}\x{0ABF}\x{0A95}\x{0AC7}\x{0A9F}|[\x{0AAE}\x{0AC7}\x{0A9A}]|\x{0AB8}\x{0ACD}\x{0AAA}\x{0ACB}\x{0AB0}\x{0ACD}\x{0A9F}\x{0ACD}\x{0AB8}/u', $combined)) {
            // Sports related
            $templates[] = 'આ રમત-સમાચાર ગુજરાતના રમતપ્રેમીઓ માટે ગૌરવની ક્ષણ છે. ખેલાડીએ પોતાના પ્રદર્શનથી રાજ્ય અને દેશનું નામ રોશન કર્યું છે. આ સિદ્ધિ યુવા ખેલાડીઓ માટે પ્રેરણારૂપ છે.';
        } else {
            // General news supplement
            $templates[] = 'આ સમાચાર ગુજરાત અને દેશ માટે મહત્વપૂર્ણ છે. નિષ્ણાતો અને વિશ્લેષકો આ ઘટનાક્રમની વિવિધ પાસાંઓથી સમીક્ષા કરી રહ્યા છે અને તેના સંભાવિત પરિણામો વિશે ચર્ચા કરી રહ્યા છે.';
            $templates[] = 'આ ઘટનાની અસર ગુજરાત અને રાષ્ટ્રીય સ્તરે જોવા મળી શકે છે. સંબંધિત ક્ષેત્રના નિષ્ણાતો અને અધિકારીઓ આ મામલે સતત નજર રાખીને જરૂરી પગલાં લઈ રહ્યા છે.';
            $templates[] = 'આ વિકાસ સંદર્ભે સામાન્ય લોકોમાં ચર્ચા જોર પકડી રહી છે અને સોશિયલ મીડિયા પર પણ આ મુદ્દે વિવિધ પ્રતિક્રિયાઓ આવી રહી છે.';
        }

        $result = '';
        $added = 0;
        foreach ($templates as $sent) {
            // Count words approximately for Gujarati text
            $wc = mb_strlen($sent) / 10;
            if ($added + $wc <= $targetWords) {
                $result .= ' '.$sent;
                $added += $wc;
            } else {
                break;
            }
        }

        return trim($result);
    }

    private function cleanDescription(?string $raw): ?string
    {
        $text = html_entity_decode(strip_tags($raw ?? ''));
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $text = trim($text);
        if (mb_strlen($text ?? '') > 500) {
            $text = mb_substr($text, 0, 497).'...';
        }

        // Ensure minimum description length
        if (mb_strlen($text ?? '') < 60) {
            return $text; // Will be supplemented, keep as is
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
            // Count actual words for Gujarati text
            $wordCount = count(preg_split('/\s+/u', $text));

            // Only use AI summarization if there is substantial content
            // Skip for very short tweet text (hashtags + "Video") as it just returns same text
            if ($wordCount > 25) {
                $rewrittenTitle = $summarizer->summarize($text, 10, 'gujarati');
                if ($rewrittenTitle && $rewrittenTitle !== $text) {
                    $art['title'] = $rewrittenTitle;
                }

                $summary = $summarizer->summarize($text, 75, 'gujarati');
                if ($summary && $summary !== $text) {
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