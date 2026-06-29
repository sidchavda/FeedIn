<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Language;
use App\Models\News;
use App\Services\TextSummarizer;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Symfony\Component\Process\Process;

class FetchVtvGujaratiNews extends Command
{
    protected $signature = 'news:fetch-vtv-gujarati
        {--limit=50 : Max articles to insert per run}
        {--no-verify : Bypass SSL verification}';

    protected $description = 'Fetch Gujarati news from VTV Gujarati';

    private array $sources = [
        ['url' => 'https://www.vtvgujarati.com/', 'source' => 'VTV Gujarati'],
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

        $allArticles = [];
        foreach ($this->sources as $src) {
            $this->line("Fetching: {$src['source']}");
            $articles = $this->parseHomepage($src['url'], $src['source']);
            if (empty($articles)) {
                continue;
            }
            $this->info('  Found '.count($articles).' articles on homepage.');
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
                $allArticles = array_merge($allArticles, array_values($unique));
            }
        }

        if (empty($allArticles)) {
            $this->warn('No new articles to insert.');
            return Command::SUCCESS;
        }

        $allArticles = array_slice($allArticles, 0, $limit);

        $this->info('Fetching article details...');
        $this->fetchArticleDetails($allArticles);

        $pending = [];
        foreach ($allArticles as $art) {
            $desc = trim(strip_tags($art['description'] ?? ''));
            if (mb_strlen($desc) < 50) {
                continue;
            }
            $normalized = $this->normalizeTitle($art['title']);
            $pending[$normalized] = $art;
        }
        $pending = array_values($pending);

        $this->info('Articles with full content: '.count($pending));
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

    private function parseHomepage(string $url, string $source): array
    {
        $html = $this->curlFetch($url);
        if ($html === null) {
            $this->warn("  Error fetching homepage");
            return [];
        }

        $articles = [];
        $seen = [];

        preg_match_all('/<a[^>]+href="(\/news-details\/[^"]+)"[^>]*>/i', $html, $linkMatches, PREG_SET_ORDER);
        if (empty($linkMatches)) {
            return [];
        }

        foreach ($linkMatches as $m) {
            $slug = $m[1];
            $fullUrl = 'https://www.vtvgujarati.com' . $slug;

            if (isset($seen[$fullUrl])) {
                continue;
            }
            $seen[$fullUrl] = true;

            $title = '';
            $image = '';

            if (preg_match('/<a[^>]*href="' . preg_quote($slug, '/') . '"[^>]*>\s*([^<]+?)\s*<\/a>/si', $html, $tMatch)) {
                $title = trim(strip_tags(html_entity_decode($tMatch[1])));
            }

            $imgPattern = '/<a[^>]*href="' . preg_quote($slug, '/') . '"[^>]*>.*?<img[^>]+src="([^"]+)"[^>]*>/si';
            if (preg_match($imgPattern, $html, $imgMatch)) {
                $image = $imgMatch[1];
            }

            if (! $image) {
                $altPattern = '/<img[^>]+class="[^"]*aspect-\[1\.78\][^"]*"[^>]+src="([^"]+)"[^>]*>/i';
                preg_match_all($altPattern, $html, $altMatches);
                $idx = count($articles);
                if (isset($altMatches[1][$idx])) {
                    $image = $altMatches[1][$idx];
                }
            }

            if (empty($title) && preg_match('/og:title"\s*content="([^"]+)"/i', $html, $ot)) {
                $title = html_entity_decode($ot[1], ENT_QUOTES, 'UTF-8');
            }
            if (empty($image) && preg_match('/og:image"\s*content="([^"]+)"/i', $html, $oi)) {
                $image = $oi[1];
            }

            if (! $title) {
                continue;
            }

            $articles[] = [
                'title' => $title,
                'link' => $fullUrl,
                'description' => '',
                'image' => $image,
                'author' => '',
                'source' => $source,
            ];
        }

        return $articles;
    }

    private function fetchArticleDetails(array &$articles): void
    {
        $bar = $this->output->createProgressBar(count($articles));
        $bar->start();

        foreach ($articles as $i => &$article) {
            $html = $this->curlFetch($article['link']);
            if ($html === null) {
                $bar->advance();
                continue;
            }

            $title = '';
            if (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/i', $html, $m)) {
                $title = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            } elseif (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
                $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
            }
            if ($title) {
                $article['title'] = $title;
            }

            if (preg_match('/<meta\s+property="og:description"\s+content="([^"]+)"/i', $html, $m)) {
                $article['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            } elseif (preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $html, $m)) {
                $article['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }

            if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $m)) {
                $article['image'] = $m[1];
            }

            if (preg_match('/"articleBody"\s*:\s*"([^"]+)"/si', $html, $m)) {
                $body = json_decode('"' . $m[1] . '"') ?? '';
                if (mb_strlen(trim(strip_tags($body))) > mb_strlen(trim(strip_tags($article['description'] ?? '')))) {
                    $article['description'] = $body;
                }
            }

            if (preg_match('/"author"\s*:\s*\{[^}]*"name"\s*:\s*"([^"]+)"/si', $html, $m)) {
                $article['author'] = $m[1];
            }

            $bar->advance();
        }
        unset($article);

        $bar->finish();
        $this->newLine();
    }

    private function curlFetch(string $url): ?string
    {
        $args = [
            'C:\WINDOWS\system32\curl.exe',
            '-s',
            '-L',
            '--max-time', '15',
            '-H', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            '-H', 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            '-H', 'Accept-Language: en-US,en;q=0.9,gu;q=0.8',
        ];

        $args[] = '--insecure';

        $args[] = $url;

        try {
            $process = new Process($args, null, null, null, 20);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            $body = $process->getOutput();
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
