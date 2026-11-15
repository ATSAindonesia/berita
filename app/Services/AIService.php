<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $model;

    protected int $timeout;

    protected int $maxRetries;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('news.ai.base_url', 'https://tokenportal.id/v1'), '/');
        $this->apiKey = (string) config('news.ai.api_key', '');
        $this->model = config('news.ai.model', 'deepseek-v4.1-flash-unli');
        $this->timeout = (int) config('news.ai.timeout', 45);
        $this->maxRetries = (int) config('news.ai.max_retries', 2);
    }

    /**
     * Get the active model name.
     */
    public function getModelName(): string
    {
        return $this->model;
    }

    /**
     * Send text generation request to the OpenAI-compatible LLM endpoint.
     *
     * @throws RuntimeException
     */
    public function generateText(string $systemPrompt, string $userPrompt, array $options = []): string
    {
        if (empty($this->apiKey)) {
            Log::warning('AI_API_KEY is not configured in .env.');
            throw new RuntimeException('AI_API_KEY belum dikonfigurasi pada file .env.');
        }

        $endpoint = "{$this->baseUrl}/chat/completions";
        $model = $options['model'] ?? $this->model;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $options['temperature'] ?? 0.2,
            'max_tokens' => $options['max_tokens'] ?? 3000,
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->retry($this->maxRetries, 1000, function (Exception $exception) {
                    Log::warning('Retrying AI API call due to error: '.$exception->getMessage());

                    return true;
                }, throw: false)
                ->post($endpoint, $payload);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error("AI API request failed [HTTP {$response->status()}]: {$errorBody}");
                throw new RuntimeException("AI API error (Status {$response->status()}): ".mb_substr($errorBody, 0, 300));
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? null;

            if (empty($text)) {
                Log::error('AI API returned empty choices or response', ['response' => $data]);
                throw new RuntimeException('AI API tidak mengembalikan konten teks yang diharapkan.');
            }

            return trim($text);
        } catch (Exception $e) {
            Log::error('AIService error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate structured JSON output from LLM.
     *
     * @throws RuntimeException
     */
    public function generateJson(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        $rawText = $this->generateText($systemPrompt, $userPrompt, $options);
        $parsed = $this->extractJson($rawText);

        if ($parsed === null) {
            Log::error('AIService failed to parse JSON from AI response', [
                'model' => $this->model,
                'raw_response' => mb_substr($rawText, 0, 1000),
            ]);

            throw new RuntimeException('Respon AI tidak dapat diuraikan sebagai format JSON yang valid.');
        }

        return $parsed;
    }

    /**
     * Safely extract JSON data from raw text containing Markdown fences or extra prose.
     */
    public function extractJson(string $rawText): ?array
    {
        $trimmed = trim($rawText);

        // 1. Direct JSON decode attempt
        $direct = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($direct)) {
            return $direct;
        }

        // 2. Remove markdown code fences ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\}|\[[\s\S]*?\])\s*```/i', $trimmed, $matches)) {
            $parsed = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                return $parsed;
            }
        }

        // 3. Find outermost curly braces or square brackets
        $firstCurly = strpos($trimmed, '{');
        $lastCurly = strrpos($trimmed, '}');
        if ($firstCurly !== false && $lastCurly !== false && $lastCurly > $firstCurly) {
            $substring = substr($trimmed, $firstCurly, $lastCurly - $firstCurly + 1);
            $parsed = json_decode($substring, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                return $parsed;
            }
        }

        $firstSquare = strpos($trimmed, '[');
        $lastSquare = strrpos($trimmed, ']');
        if ($firstSquare !== false && $lastSquare !== false && $lastSquare > $firstSquare) {
            $substring = substr($trimmed, $firstSquare, $lastSquare - $firstSquare + 1);
            $parsed = json_decode($substring, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                return $parsed;
            }
        }

        return null;
    }
}
