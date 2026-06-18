<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    private Client $http;
    private ?string $apiKey;

    private array $models = [
        'english' => 'facebook/bart-large-cnn',
        'gujarati' => 'facebook/mbart-large-50-summarization',
    ];

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
        $input = mb_substr(trim($titlePrefix . $text), 0, 3000);

        if (empty($input)) {
            return null;
        }

        $model = $this->models[$language] ?? $this->models['english'];

        return $this->callHuggingFace($input, $model, $targetWords, $language);
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

    private function callHuggingFace(string $input, string $model, int $targetWords, string $language): ?string
    {
        try {
            $url = "https://api-inference.huggingface.co/models/{$model}";

            $maxLength = $targetWords + 30;
            $minLength = max(20, $targetWords - 20);

            $resp = $this->http->post($url, [
                'json' => [
                    'inputs' => $input,
                    'parameters' => [
                        'max_length' => $maxLength,
                        'min_length' => $minLength,
                        'do_sample' => false,
                    ],
                ],
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 60,
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            $text = $body[0]['summary_text'] ?? null;

            if ($text) {
                $text = trim(preg_replace('/\s+/', ' ', $text));
            }

            return $text;
        } catch (\Exception $e) {
            Log::error("HuggingFaceService API call failed: {$e->getMessage()}");
            return null;
        }
    }
}
