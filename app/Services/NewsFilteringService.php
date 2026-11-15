<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NewsFilteringService
{
    protected int $maxArticles;

    protected int $minContentLength;

    public function __construct()
    {
        $this->maxArticles = config('news.extraction.max_articles_to_extract', 8);
        $this->minContentLength = config('news.extraction.min_content_length', 150);
    }

    /**
     * Deduplicate and filter articles by canonical URL, content similarity, and relevance to the user prompt.
     *
     * @param  array<array>  $articles
     */
    public function filterAndDeduplicate(array $articles, string $userPrompt): array
    {
        if (empty($articles)) {
            return [
                'articles' => [],
                'duplicates_count' => 0,
                'filtered_count' => 0,
            ];
        }

        // 1. Canonical & Normalized URL Deduplication
        $uniqueByUrl = [];
        $duplicatesCount = 0;

        foreach ($articles as $art) {
            $canonical = $this->normalizeUrl($art['canonical_url'] ?? ($art['url'] ?? ''));

            if (isset($uniqueByUrl[$canonical])) {
                $duplicatesCount++;
                // If existing article had less content, swap with the more complete one
                $existingLen = mb_strlen($uniqueByUrl[$canonical]['clean_content'] ?? '');
                $currentLen = mb_strlen($art['clean_content'] ?? '');
                if ($currentLen > $existingLen) {
                    $uniqueByUrl[$canonical] = $art;
                }
            } else {
                $uniqueByUrl[$canonical] = $art;
            }
        }

        // 2. Title & Content Similarity Deduplication
        $uniqueBySimilarity = [];
        foreach ($uniqueByUrl as $art) {
            $isDuplicate = false;
            $currentTitle = $art['title'] ?? '';

            foreach ($uniqueBySimilarity as $existing) {
                $sim = $this->calculateSimilarity($currentTitle, $existing['title'] ?? '');
                if ($sim >= 0.85) {
                    $isDuplicate = true;
                    $duplicatesCount++;
                    break;
                }
            }

            if (! $isDuplicate) {
                $uniqueBySimilarity[] = $art;
            }
        }

        // 3. Relevance Scoring against User Prompt
        $scoredArticles = [];
        $keywords = $this->extractKeywords($userPrompt);
        $filteredCount = 0;

        foreach ($uniqueBySimilarity as $art) {
            $content = $art['clean_content'] ?? ($art['snippet'] ?? '');
            $title = $art['title'] ?? '';

            // Check minimum substance
            if (mb_strlen($content) < 50 && mb_strlen($title) < 15) {
                $filteredCount++;

                continue;
            }

            $score = $this->calculateRelevanceScore($title, $content, $keywords);
            $art['relevance_score'] = $score;

            // Keep if score is non-zero or fallback if keyword list is small
            if ($score > 0 || empty($keywords)) {
                $scoredArticles[] = $art;
            } else {
                $filteredCount++;
            }
        }

        // Sort descending by relevance score, then by content length
        usort($scoredArticles, function ($a, $b) {
            if ($a['relevance_score'] == $b['relevance_score']) {
                return mb_strlen($b['clean_content'] ?? '') <=> mb_strlen($a['clean_content'] ?? '');
            }

            return ($b['relevance_score'] > $a['relevance_score']) ? 1 : -1;
        });

        // Limit to max configured articles for AI context optimization
        $finalArticles = array_slice($scoredArticles, 0, $this->maxArticles);

        Log::info('NewsFilteringService completed filtering', [
            'initial_count' => count($articles),
            'duplicates_removed' => $duplicatesCount,
            'irrelevant_removed' => $filteredCount,
            'final_selected' => count($finalArticles),
        ]);

        return [
            'articles' => $finalArticles,
            'duplicates_count' => $duplicatesCount,
            'filtered_count' => $filteredCount,
        ];
    }

    /**
     * Compute string similarity between two titles.
     */
    protected function calculateSimilarity(string $str1, string $str2): float
    {
        $s1 = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str1)));
        $s2 = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str2)));

        if ($s1 === $s2) {
            return 1.0;
        }

        similar_text($s1, $s2, $percent);

        return $percent / 100.0;
    }

    /**
     * Extract essential keywords from prompt, filtering out Indonesian/English stop words.
     */
    public function extractKeywords(string $prompt): array
    {
        $stopWords = [
            'dan', 'atau', 'yang', 'di', 'ke', 'dari', 'ini', 'itu', 'pada', 'untuk',
            'dengan', 'oleh', 'adalah', 'sebagai', 'dalam', 'tentang', 'atas', 'bisa',
            'akan', 'sudah', 'telah', 'ada', 'hari', 'saat', 'serta', 'tersebut', 'juga',
            'karena', 'jika', 'apabila', 'terhadap', 'menurut', 'antara', 'the', 'and',
            'in', 'on', 'at', 'to', 'for', 'of', 'with', 'about', 'is', 'are', 'was',
        ];

        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($prompt));
        $tokens = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);

        $keywords = [];
        foreach ($tokens as $t) {
            if (mb_strlen($t) >= 3 && ! in_array($t, $stopWords)) {
                $keywords[] = $t;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Calculate relevance score based on keyword presence in title and content.
     */
    protected function calculateRelevanceScore(string $title, string $content, array $keywords): float
    {
        if (empty($keywords)) {
            return 1.0;
        }

        $titleLower = mb_strtolower($title);
        $contentLower = mb_strtolower($content);

        $score = 0.0;
        foreach ($keywords as $kw) {
            // Title matches have 3x weight
            if (str_contains($titleLower, $kw)) {
                $score += 3.0;
            }
            // Content matches
            if (str_contains($contentLower, $kw)) {
                $score += 1.0;
            }
        }

        return $score;
    }

    /**
     * Normalize URL for deduplication.
     */
    protected function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (! $parsed || ! isset($parsed['host'])) {
            return strtolower(rtrim($url, '/'));
        }

        $scheme = isset($parsed['scheme']) ? strtolower($parsed['scheme']) : 'https';
        $host = preg_replace('/^www\./i', '', strtolower($parsed['host']));
        $path = isset($parsed['path']) ? rtrim($parsed['path'], '/') : '';

        return "{$scheme}://{$host}{$path}";
    }
}
