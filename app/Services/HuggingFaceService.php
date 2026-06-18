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

        $prompt = "Summarize the following article in {$lang} in about {$targetWords} words. "
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
            Log::warning("HuggingFaceService: Failed to fetch {$url}: {$e->getMessage()}");
            return null;
        }
    }

    private function callGroq(string $prompt): ?string
    {
        try {
            $model = 'llama-3.1-8b-instant';

            $resp = $this->http->post('https://api.groq.com/openai/v1/chat/completions', [
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 300,
                ],
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 60,
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            $text = $body['choices'][0]['message']['content'] ?? null;

            if ($text) {
                $text = trim(preg_replace('/\s+/', ' ', $text));
                $text = trim($text, '"\'');
            }

            return $text;
        } catch (\Exception $e) {
            Log::error("HuggingFaceService API call failed: {$e->getMessage()}");
            return null;
        }
    }
}
