<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private Client $http;
    private ?string $apiKey;

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?? new Client(['timeout' => 30]);
        $this->apiKey = config('services.gemini.api_key');
    }

    public function summarizeUrl(string $url, string $language = 'english', int $targetWords = 70): ?string
    {
        $pageText = $this->fetchPageText($url);
        if (!$pageText) {
            return null;
        }

        return $this->summarizeText($pageText, $language, $targetWords);
    }

    public function summarizeText(string $text, string $language = 'english', int $targetWords = 70): ?string
    {
        if (!$this->apiKey) {
            Log::warning('GeminiService: GEMINI_API_KEY is not set');
            return null;
        }

        $text = mb_substr(trim($text), 0, 4000);

        if (empty($text)) {
            return null;
        }

        $prompt = "Summarize the following article in $language in about $targetWords words. "
            . "Return only the summary text, no prefixes or labels:\n\n$text";

        return $this->callGemini($prompt);
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
            $text = '';

            if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/is', $html, $m)) {
                $text .= html_entity_decode($m[1]) . "\n\n";
            }

            if (preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $m)) {
                $article = strip_tags($m[1]);
                $text .= html_entity_decode(trim($article));
            } else {
                if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $m)) {
                    $body = strip_tags($m[1]);
                    $text .= html_entity_decode(trim($body));
                }
            }

            $text = trim(preg_replace('/\s+/', ' ', $text));

            return mb_strlen($text) > 100 ? $text : null;
        } catch (\Exception $e) {
            Log::warning("GeminiService: Failed to fetch {$url}: {$e->getMessage()}");
            return null;
        }
    }

    private function callGemini(string $prompt): ?string
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->apiKey}";

            $resp = $this->http->post($url, [
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 300,
                    ],
                ],
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 30,
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($text) {
                $text = trim(preg_replace('/\s+/', ' ', $text));
                $text = trim($text, '"\'');
            }

            return $text;
        } catch (\Exception $e) {
            Log::error("GeminiService API call failed: {$e->getMessage()}");
            return null;
        }
    }
}
