<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SearXNG Search Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the local or remote SearXNG instance.
    |
    */
    'searxng' => [
        'url' => env('SEARXNG_URL', 'http://127.0.0.1:8080'),
        'timeout' => (int) env('SEARXNG_TIMEOUT', 15),
        'retry_times' => (int) env('SEARXNG_RETRY_TIMES', 2),
        'categories' => env('SEARXNG_CATEGORIES', 'news,general'),
        'language' => env('SEARXNG_LANGUAGE', 'id-ID'),
        'max_results_per_query' => (int) env('SEARXNG_MAX_RESULTS_PER_QUERY', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | OpenAI-compatible API endpoint configuration.
    |
    */
    'ai' => [
        'base_url' => env('AI_BASE_URL', 'https://tokenportal.id/v1'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL', 'deepseek-v4.1-flash-unli'),
        'timeout' => (int) env('AI_TIMEOUT', 45),
        'max_retries' => (int) env('AI_MAX_RETRIES', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Extraction & Filtering
    |--------------------------------------------------------------------------
    */
    'extraction' => [
        'timeout' => (int) env('ARTICLE_EXTRACT_TIMEOUT', 10),
        'max_articles_to_extract' => (int) env('ARTICLE_MAX_COUNT', 8),
        'min_content_length' => (int) env('ARTICLE_MIN_CONTENT_LENGTH', 200),
        'user_agent' => env(
            'ARTICLE_USER_AGENT',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
        ),
    ],

];
