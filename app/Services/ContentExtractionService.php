<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentExtractionService
{
    protected int $timeout;

    protected string $userAgent;

    protected int $minContentLength;

    public function __construct()
    {
        $this->timeout = config('news.extraction.timeout', 10);
        $this->userAgent = config(
            'news.extraction.user_agent',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
        );
        $this->minContentLength = config('news.extraction.min_content_length', 200);
    }

    /**
     * Batch extract content for a collection of search result items.
     * Partial failures do not interrupt the overall extraction process.
     */
    public function extractAll(array $items): array
    {
        $extractedItems = [];

        foreach ($items as $item) {
            $url = $item['url'] ?? '';
            if (empty($url)) {
                continue;
            }

            try {
                $extracted = $this->extractArticle($url);

                if ($extracted !== null && ! empty($extracted['clean_content'])) {
                    $item['clean_content'] = $extracted['clean_content'];
                    $item['raw_content'] = $extracted['raw_content'];
                    $item['canonical_url'] = $extracted['canonical_url'] ?? $item['url'];
                    $item['author'] = $extracted['author'] ?? ($item['author'] ?? null);
                    if (! empty($extracted['published_at'])) {
                        $item['published_at'] = $extracted['published_at'];
                    }
                    if (! empty($extracted['title']) && (empty($item['title']) || mb_strlen($item['title']) < 10)) {
                        $item['title'] = $extracted['title'];
                    }
                    $item['status'] = 'extracted';
                } else {
                    // Fallback to snippet if extraction returned empty or failed
                    $item['clean_content'] = $item['snippet'] ?? '';
                    $item['raw_content'] = null;
                    $item['canonical_url'] = $item['url'];
                    $item['status'] = 'failed';
                }
            } catch (Exception $e) {
                Log::info("Content extraction skipped for {$url}: ".$e->getMessage());
                $item['clean_content'] = $item['snippet'] ?? '';
                $item['raw_content'] = null;
                $item['canonical_url'] = $item['url'];
                $item['status'] = 'failed';
            }

            $extractedItems[] = $item;
        }

        return $extractedItems;
    }

    /**
     * Extract and clean article content from a specific URL.
     */
    public function extractArticle(string $url): ?array
    {
        try {
            // Batasi download ke 2MB per URL agar tidak OOM di mesin RAM kecil.
            $maxBytes = 2 * 1024 * 1024;
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->withoutVerifying()
                ->withOptions(['stream' => true])
                ->get($url);

            if ($response->failed()) {
                Log::info("HTTP error {$response->status()} fetching article from: {$url}");

                return null;
            }

            $body = $response->toPsrResponse()->getBody();
            $html = '';
            while (! $body->eof() && strlen($html) < $maxBytes) {
                $html .= $body->read(65536);
            }
            $body->close();

            if (empty($html) || mb_strlen($html) < 200) {
                return null;
            }

            return $this->parseHtml($html, $url);
        } catch (Exception $e) {
            Log::info("Failed to retrieve URL {$url}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Parse HTML and extract metadata & cleaned body text using DOMDocument.
     */
    public function parseHtml(string $html, string $sourceUrl): array
    {
        // Suppress libxml warnings on malformed HTML
        $previousEntityLoader = libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        // Force UTF-8 encoding hint
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        $xpath = new DOMXPath($dom);

        // Extract metadata
        $title = $this->queryMeta($xpath, ['//meta[@property="og:title"]/@content', '//meta[@name="twitter:title"]/@content', '//title']);
        $author = $this->queryMeta($xpath, ['//meta[@name="author"]/@content', '//meta[@property="article:author"]/@content', '//meta[@name="twitter:creator"]/@content']);
        $canonical = $this->queryMeta($xpath, ['//link[@rel="canonical"]/@href', '//meta[@property="og:url"]/@content']);
        $rawPublishedAt = $this->queryMeta($xpath, ['//meta[@property="article:published_time"]/@content', '//meta[@name="pubdate"]/@content', '//time/@datetime']);

        $publishedAt = null;
        if (! empty($rawPublishedAt)) {
            try {
                $publishedAt = Carbon::parse($rawPublishedAt)->toDateTimeString();
            } catch (Exception) {
                $publishedAt = null;
            }
        }

        // Remove irrelevant elements
        $unwantedTags = [
            'script', 'style', 'nav', 'header', 'footer', 'aside', 'form',
            'noscript', 'svg', 'iframe', 'button', 'input', 'select', 'figure',
            'figcaption', 'canvas', 'video', 'audio',
        ];

        foreach ($unwantedTags as $tag) {
            $elements = $dom->getElementsByTagName($tag);
            $toRemove = [];
            foreach ($elements as $el) {
                $toRemove[] = $el;
            }
            foreach ($toRemove as $el) {
                if ($el->parentNode) {
                    $el->parentNode->removeChild($el);
                }
            }
        }

        // Remove unwanted classes/ids (advertisements, comments, social shares)
        $junkQueries = [
            '//*[contains(@class, "ad-")]',
            '//*[contains(@class, "advertisement")]',
            '//*[contains(@class, "comment")]',
            '//*[contains(@class, "share-")]',
            '//*[contains(@class, "social-")]',
            '//*[contains(@class, "sidebar")]',
            '//*[contains(@class, "cookie")]',
            '//*[contains(@id, "banner")]',
        ];

        foreach ($junkQueries as $jq) {
            $elements = $xpath->query($jq);
            if ($elements) {
                $toRemove = [];
                foreach ($elements as $el) {
                    $toRemove[] = $el;
                }
                foreach ($toRemove as $el) {
                    if ($el->parentNode) {
                        $el->parentNode->removeChild($el);
                    }
                }
            }
        }

        // Identify main content area
        $contentCandidates = [
            '//article',
            '//main',
            '//*[@itemprop="articleBody"]',
            '//*[contains(@class, "detail__body-text")]', // Detik
            '//*[contains(@class, "read__content")]', // Kompas
            '//*[contains(@class, "entry-content")]',
            '//*[contains(@class, "post-content")]',
            '//*[contains(@class, "article-body")]',
        ];

        $bodyText = '';
        foreach ($contentCandidates as $query) {
            $nodes = $xpath->query($query);
            if ($nodes && $nodes->length > 0) {
                $nodeText = $this->extractCleanTextFromNode($nodes->item(0));
                if (mb_strlen($nodeText) >= $this->minContentLength) {
                    $bodyText = $nodeText;
                    break;
                }
            }
        }

        // Fallback: extract all paragraphs if specific containers were not found or too short
        if (mb_strlen($bodyText) < $this->minContentLength) {
            $pNodes = $xpath->query('//p');
            $paragraphs = [];
            if ($pNodes) {
                foreach ($pNodes as $p) {
                    $pt = trim($p->textContent);
                    if (mb_strlen($pt) > 30) {
                        $paragraphs[] = $pt;
                    }
                }
            }
            $bodyText = implode("\n\n", $paragraphs);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousEntityLoader);

        // Truncate clean_content: max 15K chars per artikel, cukup untuk summarisasi.
        $cleanContent = trim($bodyText);
        if (mb_strlen($cleanContent) > 15000) {
            $cleanContent = mb_substr($cleanContent, 0, 15000).'...';
        }

        return [
            'title' => trim($title ?? ''),
            'author' => trim($author ?? ''),
            'published_at' => $publishedAt,
            'canonical_url' => $canonical ?: $sourceUrl,
            'clean_content' => $cleanContent,
            'raw_content' => mb_substr(strip_tags($html), 0, 2000),
        ];
    }

    /**
     * Helper to query XPath metadata.
     */
    protected function queryMeta(DOMXPath $xpath, array $queries): ?string
    {
        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes && $nodes->length > 0) {
                $val = trim($nodes->item(0)->nodeValue ?? '');
                if (! empty($val)) {
                    return $val;
                }
            }
        }

        return null;
    }

    /**
     * Extract clean text with preserved paragraph breaks from a DOMNode.
     */
    protected function extractCleanTextFromNode(\DOMNode $node): string
    {
        $paragraphs = [];
        $pNodes = $node->getElementsByTagName('p');

        if ($pNodes->length > 0) {
            foreach ($pNodes as $p) {
                $text = trim(preg_replace('/\s+/', ' ', $p->textContent));
                if (mb_strlen($text) > 20) {
                    $paragraphs[] = $text;
                }
            }

            return implode("\n\n", $paragraphs);
        }

        // If no <p> tags, get textContent cleaned
        $raw = preg_replace('/\s+/', ' ', $node->textContent);

        return trim($raw);
    }
}
