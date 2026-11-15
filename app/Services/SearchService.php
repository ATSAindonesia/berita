<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SearchService
{
    protected string $searxngUrl;

    protected int $timeout;

    protected int $retryTimes;

    protected string $categories;

    protected string $language;

    protected int $maxResultsPerQuery;

    public function __construct()
    {
        $this->searxngUrl = rtrim(config('news.searxng.url', 'http://127.0.0.1:8080'), '/');
        $this->timeout = config('news.searxng.timeout', 15);
        $this->retryTimes = config('news.searxng.retry_times', 2);
        $this->categories = config('news.searxng.categories', 'news,general');
        $this->language = config('news.searxng.language', 'id-ID');
        $this->maxResultsPerQuery = config('news.searxng.max_results_per_query', 10);
    }

    /**
     * Send all search queries to SearXNG and aggregate unique results.
     *
     * @param  array<string>  $queries
     * @return array<array{url: string, title: string, snippet: string, source_name: string, domain: string, published_at: ?string, score: ?float}>
     *
     * @throws RuntimeException
     */
    public function searchMultipleQueries(array $queries): array
    {
        if (empty($queries)) {
            Log::warning('SearchService received empty search queries.');

            return [];
        }

        $allResults = [];
        $seenUrls = [];
        $failedQueriesCount = 0;

        foreach ($queries as $query) {
            $trimmed = trim($query);
            if (empty($trimmed)) {
                continue;
            }

            try {
                $results = $this->querySearxng($trimmed);
                foreach ($results as $item) {
                    $normalizedUrl = $this->normalizeUrl($item['url']);
                    if (! isset($seenUrls[$normalizedUrl])) {
                        $seenUrls[$normalizedUrl] = true;
                        $allResults[] = $item;
                    }
                }
            } catch (Exception $e) {
                $failedQueriesCount++;
                Log::warning("SearXNG query failed for query '{$trimmed}': ".$e->getMessage());

                // If host is unreachable or connection refused, abort querying remaining queries
                if (str_contains(strtolower($e->getMessage()), 'connection refused') ||
                    str_contains(strtolower($e->getMessage()), 'failed to connect')) {
                    break;
                }
            }
        }

        if (empty($allResults) && $failedQueriesCount >= count($queries)) {
            Log::error("All queries to SearXNG failed. SearXNG may be unreachable at {$this->searxngUrl}");
            throw new RuntimeException("SearXNG tidak dapat diakses pada {$this->searxngUrl}. Pastikan service SearXNG aktif.");
        }

        Log::info('SearXNG aggregated search results', [
            'queries_count' => count($queries),
            'unique_results_count' => count($allResults),
        ]);

        return $allResults;
    }

    /**
     * Query SearXNG for a single search string.
     */
    public function querySearxng(string $query): array
    {
        $endpoint = "{$this->searxngUrl}/search";

        $params = [
            'q' => $query,
            'format' => 'json',
            'categories' => $this->categories,
            'language' => $this->language,
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(3)
                ->retry($this->retryTimes, 300, function ($exception) {
                    if (str_contains(strtolower($exception->getMessage()), 'connection refused') ||
                        str_contains(strtolower($exception->getMessage()), 'failed to connect')) {
                        return false;
                    }

                    return true;
                }, throw: false)
                ->get($endpoint, $params);

            if ($response->failed()) {
                Log::error("SearXNG HTTP Error [Status {$response->status()}]: ".mb_substr($response->body(), 0, 200));
                throw new RuntimeException("SearXNG mengembalikan status HTTP {$response->status()}");
            }

            $json = $response->json();
            if (! is_array($json) || ! isset($json['results'])) {
                Log::warning('SearXNG response is not valid JSON or missing results key', ['response' => $response->body()]);

                return [];
            }

            $items = [];
            $count = 0;
            foreach ($json['results'] as $raw) {
                if ($count >= $this->maxResultsPerQuery) {
                    break;
                }

                $url = $raw['url'] ?? '';
                $title = trim(strip_tags($raw['title'] ?? ''));
                $snippet = trim(strip_tags($raw['content'] ?? ''));
                $engine = $raw['engine'] ?? 'SearXNG';

                if (empty($url) || empty($title)) {
                    continue;
                }

                $domain = $this->extractDomain($url);
                $publishedAt = $this->parsePublishedDate($raw['publishedDate'] ?? null);

                $items[] = [
                    'url' => $url,
                    'title' => $title,
                    'snippet' => $snippet,
                    'source_name' => $engine,
                    'domain' => $domain,
                    'published_at' => $publishedAt,
                    'score' => isset($raw['score']) ? (float) $raw['score'] : null,
                ];

                $count++;
            }

            return $items;
        } catch (Exception $e) {
            Log::error("SearchService exception when connecting to SearXNG at {$endpoint}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract human-readable domain name from URL.
     */
    public function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return 'media';
        }

        return preg_replace('/^www\./i', '', strtolower($host));
    }

    /**
     * Basic URL normalization to eliminate duplicates from query tracking parameters.
     */
    public function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (! $parsed || ! isset($parsed['host'])) {
            return strtolower(rtrim($url, '/'));
        }

        $scheme = isset($parsed['scheme']) ? strtolower($parsed['scheme']) : 'https';
        $host = preg_replace('/^www\./i', '', strtolower($parsed['host']));
        $path = isset($parsed['path']) ? rtrim($parsed['path'], '/') : '';

        // Keep essential query params, drop utm_* and fbclid
        $cleanQuery = '';
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            $filteredParams = [];
            foreach ($queryParams as $k => $v) {
                if (! str_starts_with(strtolower($k), 'utm_') && ! in_array(strtolower($k), ['fbclid', 'gclid', 'ref', 'source'])) {
                    $filteredParams[$k] = $v;
                }
            }
            if (! empty($filteredParams)) {
                ksort($filteredParams);
                $cleanQuery = '?'.http_build_query($filteredParams);
            }
        }

        return "{$scheme}://{$host}{$path}{$cleanQuery}";
    }

    /**
     * Parse date string into Carbon or ISO format if possible.
     */
    protected function parsePublishedDate(?string $rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        try {
            return Carbon::parse($rawDate)->toDateTimeString();
        } catch (Exception) {
            return null;
        }
    }
}
