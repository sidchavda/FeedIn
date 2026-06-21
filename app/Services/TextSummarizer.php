<?php

namespace App\Services;

use PhpScience\TextRank\TextRankFacade;
use PhpScience\TextRank\Tool\StopWords\English;

class TextSummarizer
{
    public function summarize(string $text, int $maxWords = 65, string $language = 'english'): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $originalWordCount = count(preg_split('/\s+/u', $text));

        if ($originalWordCount <= $maxWords) {
            return $text;
        }

        $sentences = $this->splitSentences($text);
        if (empty($sentences)) {
            return null;
        }

        $keywordScores = $this->getTextRankKeywords($text, $language);

        $scores = $this->scoreSentences($sentences, $keywordScores, $language);

        arsort($scores);

        $selectedIndices = [];
        $currentWordCount = 0;

        foreach ($scores as $index => $score) {
            $sentence = $sentences[$index];
            $wordsInSentence = count(preg_split('/\s+/u', $sentence));

            if ($currentWordCount + $wordsInSentence <= ($maxWords + 10)) {
                $selectedIndices[] = $index;
                $currentWordCount += $wordsInSentence;
            }

            if ($currentWordCount >= $maxWords) {
                break;
            }
        }

        sort($selectedIndices);

        $result = [];
        foreach ($selectedIndices as $i) {
            $result[] = $sentences[$i];
        }

        $output = implode(' ', $result);

        return ! empty($output) ? $output : null;
    }

    private function getTextRankKeywords(string $text, string $language): array
    {
        try {
            $textRank = new TextRankFacade;

            if ($language === 'english') {
                $textRank->setStopWords(new English);
            }

            return $textRank->getOnlyKeyWords($text);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function scoreSentences(array $sentences, array $keywordScores, string $language): array
    {
        $scores = [];
        $stopWords = $this->getStopWords($language);

        if (empty($keywordScores)) {
            $wordFreq = [];
            foreach ($sentences as $s) {
                $words = $this->tokenize($s, $language);
                foreach ($words as $w) {
                    if (! in_array($w, $stopWords)) {
                        $wordFreq[$w] = ($wordFreq[$w] ?? 0) + 1;
                    }
                }
            }

            foreach ($sentences as $i => $s) {
                $words = $this->tokenize($s, $language);
                $sentenceScore = 0;
                foreach ($words as $w) {
                    if (isset($wordFreq[$w])) {
                        $sentenceScore += $wordFreq[$w];
                    }
                }
                $wc = count($words);
                $lengthModifier = ($wc >= 15 && $wc <= 30) ? 1.5 : 1.0;
                $positionBias = 1.0 + (1.0 / ($i + 1));
                $scores[$i] = $sentenceScore * $lengthModifier * $positionBias;
            }

            return $scores;
        }

        foreach ($sentences as $i => $sentence) {
            $words = $this->tokenize($sentence, $language);
            $sentenceScore = 0;

            foreach ($words as $w) {
                if (isset($keywordScores[$w])) {
                    $sentenceScore += $keywordScores[$w];
                }
            }

            $wc = count($words);
            $lengthModifier = ($wc >= 15 && $wc <= 30) ? 1.5 : 1.0;
            $positionBias = 1.0 + (1.0 / ($i + 1));

            $scores[$i] = $sentenceScore * $lengthModifier * $positionBias;
        }

        return $scores;
    }

    private function splitSentences(string $text): array
    {
        $raw = preg_split('/(?<=[.!?।॥])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_filter(array_map('trim', $raw), function ($s) {
            return mb_strlen($s) > 10;
        });
    }

    private function tokenize(string $text, string $language): array
    {
        $text = mb_strtolower(strip_tags($text));
        preg_match_all('/[\x{0A80}-\x{0AFF}\w]+/u', $text, $matches);

        return $matches[0] ?? [];
    }

    private function getStopWords(string $language): array
    {
        $common = ['is', 'the', 'and', 'a', 'in', 'it', 'to', 'of', 'for', 'with', 'on', 'at'];
        $gujarati = ['છે', 'અને', 'તે', 'આ', 'માં', 'ના', 'ની', 'નું', 'થી', 'પર'];

        return ($language === 'gujarati') ? array_merge($common, $gujarati) : $common;
    }
}
