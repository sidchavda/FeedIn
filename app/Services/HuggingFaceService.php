<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    private Client $http;
    private ?string $apiKey;

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?? new Client(['timeout' => 60, 'verify' => false]);
        $this->apiKey = config('services.huggingface.api_key');
    }

    public function summarizeUrl(string $url, string $language = 'english', int $targetWords = 70, string $title = ''): ?string
    {
        $pageText = $this->fetchPageText($url);
        if (!$pageText) {
            return null;
        }

        return $this->summarizeText($pageText, $language, $targetWords, $title);
    }

    public function summarizeText(string $text, string $language = 'english', int $targetWords = 70, string $title = ''): ?string
    {
        if (!$this->apiKey) {
            Log::warning('HuggingFaceService: HF_API_KEY is not set');
            return null;
        }

        $titlePrefix = $title ? "Title: $title\n\n" : '';
        $input = mb_substr(trim($titlePrefix . $text), 0, 4000);

        if (empty($input)) {
            return null;
        }

        $lang = $language === 'gujarati' ? 'Gujarati' : 'English';

        $prompt = "Write a detailed summary of this article in {$lang} in 3 to 4 sentences. "
            . "Make the summary specific to this article's content and title. "
            . "Return only the summary text, no prefixes or labels:\n\n{$input}";

        return $this->callGroq($prompt);
    }

    private function fetchPageText(string $url): ?string
    {
        try {
            $resp = $this->http->get($url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'],
                'verify' => false,
                'timeout' => 15,
            ]);

            $html = (string) $resp->getBody();
            $parts = [];

            // 1. meta description / og:description
            foreach (['name="description"', 'property="og:description"', 'name="twitter:description"'] as $attr) {
                if (preg_match('/<meta[^>]+' . preg_quote($attr, '/') . '[^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                    $parts[] = html_entity_decode($m[1]);
                }
            }

            // 2. All <p> tags from the entire HTML
            preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pTags);
            $wordCount = 0;
            $skipPhrases = ['skip to navigation', 'skip to main', 'skip to content', 'advertisement',
                'read more', 'sign up', 'newsletter', 'terms of service',
                'privacy policy', 'all rights reserved', 'cookie', 'subscribe',
                'trending now', 'recommended', 'also read', 'follow us', 'related'];
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
                    $parts[] = $clean;
                    $wordCount += str_word_count($clean);
                    if ($wordCount > 200) {
                        break;
                    }
                }
            }

            // 3. Fallback: <article> or <body> content
            if (empty($parts)) {
                if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $m)) {
                    $parts[] = trim(strip_tags($m[1]));
                } elseif (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $m)) {
                    $parts[] = trim(strip_tags($m[1]));
                }
            }

            $text = trim(preg_replace('/\s+/', ' ', implode("\n\n", $parts)));

            return mb_strlen($text) > 100 ? $text : null;
        } catch (\Exception $e) {
            Log::warning("HuggingFaceService: Failed to fetch {$url}: {$e->getMessage()}");
            return null;
        }
    }

    private function callGroq(string $prompt): ?string
    {
        try {
            $model = 'qwen/qwen-2.5-72b-instruct';

            $resp = $this->http->post('https://openrouter.ai/api/v1/chat/completions', [
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    //                     'max_tokens' => 800,
                ],
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => 'http://localhost',
                    'X-Title' => 'TEJ News',
                ],
                'timeout' => 60,
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            $text = $body['choices'][0]['message']['content'] ?? null;

            if ($text) {
                $text = trim(preg_replace('/\s+/', ' ', $text));
                $text = trim($text, '"\'');
                // Reject if too short (AI returned a fragment, not a real summary)
                $wordCount = count(preg_split('/\s+/', $text));
                if ($wordCount < 40) {
                    Log::warning("HuggingFaceService: summary too short ({$wordCount} words), discarding");
                    return null;
                }
            }

            return $text;
        } catch (\Exception $e) {
            Log::error("HuggingFaceService API call failed: {$e->getMessage()}");
            return null;
        }
    }
}
