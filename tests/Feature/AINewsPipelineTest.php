<?php

namespace Tests\Feature;

use App\Services\ContentExtractionService;
use App\Services\NewsFilteringService;
use App\Services\NewsStorageService;
use App\Services\PromptService;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AINewsPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_service_enhancement_and_separation(): void
    {
        $promptService = app(PromptService::class);
        $userPrompt = 'Kenaikan PPN 12 persen tahun 2025';

        $enhanced = $promptService->enhancePrompt($userPrompt);

        $this->assertArrayHasKey('system_prompt', $enhanced);
        $this->assertArrayHasKey('user_prompt', $enhanced);
        $this->assertArrayHasKey('original_prompt', $enhanced);

        // System prompt contains news research analyst identity
        $this->assertStringContainsString('News Research Analyst', $enhanced['system_prompt']);
        $this->assertStringContainsString('AKURASI & OBJEKTIVITAS', $enhanced['system_prompt']);

        // User prompt preserves exact original intent
        $this->assertStringContainsString($userPrompt, $enhanced['user_prompt']);
        $this->assertEquals($userPrompt, $enhanced['original_prompt']);
    }

    public function test_query_generation_prompt_structure(): void
    {
        $promptService = app(PromptService::class);
        $bundle = $promptService->buildQueryGenerationPrompt('harga cabai melonjak');

        $this->assertStringContainsString('JSON', $bundle['system_prompt']);
        $this->assertStringContainsString('SearXNG', $bundle['system_prompt']);
        $this->assertStringContainsString('harga cabai melonjak', $bundle['user_prompt']);
    }

    public function test_searxng_search_service_aggregation(): void
    {
        Http::fake([
            '*/search*' => Http::response([
                'results' => [
                    [
                        'url' => 'https://news.detik.com/berita/harga-beras-naik',
                        'title' => 'Harga Beras Naik Signifikan',
                        'content' => 'Pemerintah memantau lonjakan harga beras di pasar induk.',
                        'engine' => 'google',
                        'publishedDate' => '2026-03-12T10:00:00Z',
                        'score' => 1.5,
                    ],
                    [
                        'url' => 'https://news.detik.com/berita/harga-beras-naik?utm_source=twitter',
                        'title' => 'Harga Beras Naik Signifikan',
                        'content' => 'Pemerintah memantau lonjakan harga beras di pasar induk.',
                        'engine' => 'bing',
                        'publishedDate' => '2026-03-12T10:00:00Z',
                        'score' => 1.2,
                    ],
                ],
            ], 200),
        ]);

        $searchService = app(SearchService::class);
        $results = $searchService->searchMultipleQueries(['harga beras naik']);

        $this->assertCount(1, $results, 'Normalized duplicate URLs must be deduplicated during aggregation');
        $this->assertEquals('news.detik.com', $results[0]['domain']);
        $this->assertEquals('Harga Beras Naik Signifikan', $results[0]['title']);
    }

    public function test_content_extraction_cleans_html_and_extracts_metadata(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Kenaikan Harga Pangan - Kompas</title>
    <meta name="author" content="Redaksi Kompas">
    <meta property="article:published_time" content="2026-03-14T08:30:00+07:00">
    <link rel="canonical" href="https://kompas.com/read/kenaikan-harga-pangan">
</head>
<body>
    <header><nav><a href="/">Home</a></nav></header>
    <div class="advertisement">Pasang iklan di sini</div>
    <script>console.log('tracker');</script>
    <article>
        <p>Harga beras dan komoditas pangan pokok kembali mengalami kenaikan di berbagai pasar tradisional di Jawa dan Sumatera.</p>
        <p>Kementerian Pertanian menyatakan pasokan beras aman dan operasi pasar akan segera dilancarkan dalam pekan ini.</p>
    </article>
    <footer><p>Hak Cipta Dilindungi</p></footer>
</body>
</html>
HTML;

        Http::fake([
            'https://kompas.com/*' => Http::response($html, 200),
        ]);

        $extractor = app(ContentExtractionService::class);
        $extracted = $extractor->extractArticle('https://kompas.com/read/kenaikan-harga-pangan');

        $this->assertNotNull($extracted);
        $this->assertStringContainsString('Harga beras dan komoditas pangan pokok', $extracted['clean_content']);
        $this->assertStringNotContainsString('Pasang iklan', $extracted['clean_content']);
        $this->assertStringNotContainsString('tracker', $extracted['clean_content']);
        $this->assertEquals('Redaksi Kompas', $extracted['author']);
        $this->assertEquals('https://kompas.com/read/kenaikan-harga-pangan', $extracted['canonical_url']);
    }

    public function test_news_filtering_deduplicates_and_scores_relevance(): void
    {
        $articles = [
            [
                'url' => 'https://media-a.com/berita-1',
                'canonical_url' => 'https://media-a.com/berita-1',
                'title' => 'Harga Beras dan Cabai Naik Tajam',
                'clean_content' => 'Harga beras dan cabai mengalami kenaikan tajam di sejumlah daerah.',
            ],
            [
                'url' => 'https://media-b.com/berita-1-mirror',
                'canonical_url' => 'https://media-a.com/berita-1', // Same canonical
                'title' => 'Harga Beras dan Cabai Naik Tajam',
                'clean_content' => 'Harga beras dan cabai mengalami kenaikan tajam.',
            ],
            [
                'url' => 'https://media-c.com/tips-memasak',
                'canonical_url' => 'https://media-c.com/tips-memasak',
                'title' => 'Tips Resep Kue Lebaran Enak dan Renyah',
                'clean_content' => 'Cara membuat adonan kue kering yang renyah dan gurih untuk keluarga di rumah.',
            ],
        ];

        $filter = app(NewsFilteringService::class);
        $filtered = $filter->filterAndDeduplicate($articles, 'harga beras naik');

        $this->assertCount(1, $filtered['articles'], 'Only relevant non-duplicate article should be kept');
        $this->assertEquals('Harga Beras dan Cabai Naik Tajam', $filtered['articles'][0]['title']);
        $this->assertGreaterThan(0, $filtered['duplicates_count']);
    }

    public function test_complete_storage_service_persists_all_tables(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('harga beras naik');

        $this->assertDatabaseHas('search_sessions', [
            'id' => $session->id,
            'status' => 'pending',
            'prompt' => 'harga beras naik',
        ]);

        $prompt = $storage->recordAiPrompt(
            $session,
            'analysis',
            'System instruction',
            'User instruction',
            '{"status":"ok"}',
            'deepseek-v4.1-flash-unli'
        );

        $this->assertDatabaseHas('ai_prompts', [
            'id' => $prompt->id,
            'search_session_id' => $session->id,
            'prompt_type' => 'analysis',
        ]);

        $sources = $storage->storeNewsSources($session, [
            [
                'title' => 'Kompas - Beras Naik',
                'url' => 'https://kompas.com/beras-naik',
                'source_name' => 'Kompas',
                'clean_content' => 'Konten lengkap berita kompas.',
                'status' => 'extracted',
            ],
        ]);

        $this->assertDatabaseHas('news_sources', [
            'search_session_id' => $session->id,
            'url' => 'https://kompas.com/beras-naik',
        ]);

        $summaryData = [
            'title' => 'Rangkuman Kenaikan Harga Beras',
            'summary' => 'Ringkasan komprehensif mengenai pergerakan harga beras nasional.',
            'key_points' => ['Poin 1: Pasokan menipis', 'Poin 2: Harga naik 10%'],
            'chronology' => 'Pergerakan terjadi sejak awal bulan.',
            'source_comparison' => 'Seluruh sumber sepakat terjadi lonjakan.',
            'unconfirmed_facts' => 'Data impor belum dipublikasikan.',
            'conclusion' => 'Dibutuhkan operasi pasar segera.',
            'category' => 'harga beras naik',
            'content' => 'Isi lengkap laporan berita.',
        ];

        $summary = $storage->saveNewsSummary($session, $summaryData, $sources, 'deepseek-v4.1-flash-unli');

        $this->assertDatabaseHas('news_summaries', [
            'id' => $summary->id,
            'search_session_id' => $session->id,
            'title' => 'Rangkuman Kenaikan Harga Beras',
        ]);

        $this->assertDatabaseHas('summary_sources', [
            'news_summary_id' => $summary->id,
            'news_source_id' => $sources[0]->id,
        ]);

        $this->assertDatabaseHas('news_references', [
            'news_summary_id' => $summary->id,
            'source_url' => 'https://kompas.com/beras-naik',
        ]);

        $this->assertDatabaseHas('search_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_summary_show_route_displays_all_8_parts_and_references(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('perubahan iklim');

        $summary = $storage->saveNewsSummary($session, [
            'title' => 'Laporan Analisis Perubahan Iklim 2026',
            'summary' => 'Ringkasan komprehensif dampak perubahan iklim global.',
            'key_points' => ['Suhu global naik 1.5C', 'Curah hujan ekstrem meningkat'],
            'chronology' => 'Linimasa perundingan COP29 hingga sekarang.',
            'source_comparison' => 'Media barat dan media nasional memiliki fokus yang sejalan.',
            'unconfirmed_facts' => 'Target emisi tahun 2030 masih diperdebatkan.',
            'conclusion' => 'Transisi energi bersih harus dipercepat.',
            'category' => 'perubahan iklim',
            'references' => [
                [
                    'source_name' => 'Antara',
                    'source_title' => 'Laporan Khusus Iklim',
                    'source_url' => 'https://antaranews.com/iklim-2026',
                ],
            ],
        ]);

        $response = $this->get(route('summaries.show', $summary));

        $response->assertStatus(200);
        $response->assertSee('Laporan Analisis Perubahan Iklim 2026');
        $response->assertSee('Ringkasan Utama');
        $response->assertSee('Poin-Poin Penting');
        $response->assertSee('Suhu global naik 1.5C');
        $response->assertSee('Kronologi Peristiwa');
        $response->assertSee('Perbandingan Informasi Antar Sumber');
        $response->assertSee('Fakta Belum Terkonfirmasi');
        $response->assertSee('Kesimpulan');
        $response->assertSee('Daftar Referensi Berita');
        $response->assertSee('https://antaranews.com/iklim-2026');
    }

    public function test_summary_status_api_endpoint(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('subsidi energi');

        $response = $this->getJson(route('summaries.status', $session->id));

        $response->assertStatus(200);
        $response->assertJson([
            'session_id' => $session->id,
            'status' => 'pending',
        ]);
    }
}
