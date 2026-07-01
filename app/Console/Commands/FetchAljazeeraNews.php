<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use App\Services\TextSummarizer;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use SimpleXMLElement;

class FetchAljazeeraNews extends Command
{
    protected $signature = 'news:fetch-aljazeera
        {--limit=50 : Max articles to insert per run}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Asia news from Al Jazeera';

    private string $rssUrl = 'https://www.aljazeera.com/xml/rss/all.xml';

    public function handle(): int
    {
        $language = Language::firstOrCreate(
            ['code' => 'EN'],
            ['name' => 'English', 'is_active' => 1]
        );
        $category = Category::firstOrCreate(
            ['name' => 'Trending', 'language_id' => $language->id],
            ['is_active' => 1]
        );
        $limit = (int) $this->option('limit');

        $existingLinks = News::pluck('link')->flip();
        $existingTitles = News::pluck('title')->map(fn ($t) => $this->normalizeTitle($t))->flip();

        $this->line("Fetching: Al Jazeera RSS");
        $articles = $this->parseRss();

        if (empty($articles)) {
            $this->warn('No articles found in RSS feed.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . count($articles) . ' articles in RSS feed.');

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

        $this->info('Fetching article details for images & descriptions...');
        $this->fetchArticlePages($pending);

        $this->summarizeArticles($pending);

        $inserted = 0;
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $art) {
            $desc = ! empty($art['ai_summarized']) ? $art['description'] : $this->cleanDescription($art['description'] ?? '');
            
            // Skip articles with short descriptions
            if (mb_strlen($desc) < 50) {
                $this->warn('  Skipped short description: ' . mb_substr($art['title'], 0, 60));
                $bar->advance();
                continue;
            }
            
            $image = $art['image'] ?? null;
            $author = $art['author'] ?: 'Al Jazeera';

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

    private function parseRss(): array
    {
        $xml = $this->guzzleFetch($this->rssUrl);
        if ($xml === null) {
            $this->warn('  Error fetching RSS feed');
            return [];
        }

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        if ($feed === false) {
            $this->warn('  Error parsing RSS XML');
            return [];
        }

        $articles = [];
        foreach ($feed->channel->item as $item) {
            $link = (string) $item->link;
            $link = str_replace('?traffic_source=rss', '', $link);

            $title = trim((string) $item->title);
            if (empty($title)) {
                continue;
            }

            $description = '';
            if ($item->description) {
                $description = trim(strip_tags((string) $item->description));
            }

            $image = $this->extractRssImage($item);

            $articles[] = [
                'title' => html_entity_decode($title, ENT_QUOTES, 'UTF-8'),
                'link' => $link,
                'description' => html_entity_decode($description, ENT_QUOTES, 'UTF-8'),
                'image' => $image,
                'author' => '',
                'source' => 'Al Jazeera',
            ];
        }

        return $articles;
    }

    private function extractRssImage(SimpleXMLElement $item): ?string
    {
        $namespaces = $item->getNamespaces(true);
        $media = $namespaces['media'] ?? null;

        if ($media) {
            $item->registerXPathNamespace('media', $media);
            $thumbnails = $item->xpath('media:thumbnail');
            if (! empty($thumbnails)) {
                $url = (string) $thumbnails[0]['url'];
                if ($url) {
                    return $url;
                }
            }
            $contents = $item->xpath('media:content');
            if (! empty($contents)) {
                $url = (string) $contents[0]['url'];
                if ($url) {
                    return $url;
                }
            }
        }

        if ($item->enclosure && (string) $item->enclosure['type'] === 'image/jpeg') {
            $url = (string) $item->enclosure['url'];
            if ($url) {
                return $url;
            }
        }

        if ($item->description) {
            $desc = (string) $item->description;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $desc, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    private function fetchArticlePages(array &$pending): void
    {
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        foreach ($pending as $i => &$article) {
            $html = $this->guzzleFetch($article['link']);
            if ($html === null) {
                $bar->advance();
                continue;
            }

            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $article['image'] = $m[1];
            }

            if (preg_match('/<meta[^>]+name=["\']author["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $article['author'] = trim($m[1]);
            }

            // Extract body from the wysiwyg container
            $bodyHtml = '';
            if (preg_match('/<div[^>]*class="[^"]*wysiwyg[^"]*"[^>]*>.*?<\/main>/is', $html, $bodyMatch)) {
                $bodyHtml = $bodyMatch[0];
            }

            if (! empty($bodyHtml)) {
                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $bodyHtml, $pTags);
            } else {
                preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pTags);
            }

            $paragraphs = [];
            foreach ($pTags[1] as $p) {
                $clean = trim(strip_tags($p));
                $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
                $clean = preg_replace('/\s+/', ' ', $clean);
                if (strlen($clean) > 60 && ! str_contains(strtolower($clean), 'advertisement')) {
                    $paragraphs[] = $clean;
                    if (count($paragraphs) >= 10) {
                        break;
                    }
                }
            }

            if (count($paragraphs) >= 3) {
                $article['description'] = implode(' ', $paragraphs);
            } elseif (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $article['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }

            $bar->advance();
        }
        unset($article);

        $bar->finish();
        $this->newLine();
    }

    private function guzzleFetch(string $url): ?string
    {
        try {
            $client = new Client([
                'timeout' => 15,
                'verify' => ! $this->option('no-verify'),
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ],
            ]);

            $response = $client->get($url);
            $body = (string) $response->getBody();

            if (str_contains($body, 'Just a moment')) {
                return null;
            }

            return $body;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function cleanDescription(?string $raw): ?string
    {
        $text = html_entity_decode(strip_tags($raw ?? ''), ENT_QUOTES, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text ?? '') > 500) {
            $text = mb_substr($text, 0, 497) . '...';
        }
        return $text ?: null;
    }

    private function normalizeTitle(string $title): string
    {
        return preg_replace('/[^a-z0-9\s]/u', '', mb_strtolower(trim($title)));
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
}
