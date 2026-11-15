<?php

namespace Tests\Feature;

use App\Jobs\ProcessNewsSearchJob;
use App\Models\NewsSummary;
use App\Models\SearchSession;
use App\Services\NewsStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RealtimeLoadingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Analisis Berita Terkini',
                                'summary' => 'Ringkasan komprehensif berita.',
                                'queries' => ['berita terkini', 'kebijakan pemerintah'],
                                'key_points' => ['Poin penting 1', 'Poin penting 2'],
                                'chronology' => 'Linimasa peristiwa.',
                                'source_comparison' => 'Perbandingan sumber.',
                                'unconfirmed_facts' => 'Fakta belum konfirmasi.',
                                'conclusion' => 'Kesimpulan analisis.',
                                'category' => 'berita terkini',
                                'analysis' => 'Analisis mendalam.',
                                'references' => [
                                    [
                                        'source_name' => 'Antara',
                                        'source_title' => 'Laporan Berita',
                                        'source_url' => 'https://antaranews.com/berita-1',
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
            '*/search*' => Http::response([
                'results' => [
                    [
                        'url' => 'https://antaranews.com/berita-1',
                        'title' => 'Laporan Berita',
                        'content' => 'Isi ringkas berita.',
                    ],
                ],
            ], 200),
            '*' => Http::response('<html><title>Berita</title><article><p>Isi artikel berita lengkap.</p></article></html>', 200),
        ]);
    }

    public function test_loading_page_contains_exact_pencil_design_elements(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('harga beras naik');

        $response = $this->get('/summaries/create?session_id='.$session->id);

        $response->assertStatus(200);
        $response->assertSee('data-pencil-name="03 · Mencari"', false);
        $response->assertSee('data-pencil-name="Loading Stage"', false);
        $response->assertSee('data-pencil-name="Loading Stack"', false);
        $response->assertSee('data-pencil-name="Status Row"', false);
        $response->assertSee('data-pencil-name="Pulse"', false);
        $response->assertSee('data-pencil-name="Status Label"', false);
        $response->assertSee('AI SEDANG MENCARI');
        $response->assertSee('data-pencil-name="Query Line"', false);
        $response->assertSee('harga beras naik');
        $response->assertSee('data-pencil-name="Query Meta"', false);
        $response->assertSee('data-pencil-name="Progress Track"', false);
        $response->assertSee('data-pencil-name="Progress Fill"', false);
        $response->assertSee('data-pencil-name="Steps"', false);
        $response->assertSee('data-pencil-name="Step Menelusuri penerbit"', false);
        $response->assertSee('data-pencil-name="Step Membaca artikel"', false);
        $response->assertSee('data-pencil-name="Step Menyusun ringkasan"', false);
        $response->assertSee('data-pencil-name="Cancel Row"', false);
        $response->assertSee('data-pencil-name="Cancel Button"', false);
        $response->assertSee('Batalkan pencarian');
    }

    public function test_ajax_submission_returns_202_with_contract_payload(): void
    {
        $response = $this->postJson('/summaries', [
            'query' => 'kenaikan harga cabai',
        ]);

        $response->assertStatus(202);
        $response->assertJsonStructure([
            'session_id',
            'status',
            'current_stage',
            'stage_label',
            'description',
            'progress',
            'completed_stages',
            'meta',
            'error',
            'summary_id',
            'redirect_url',
        ]);

        $sessionId = $response->json('session_id');
        $this->assertNotNull($sessionId);
        $this->assertContains($response->json('current_stage'), ['initializing', 'completed']);
    }

    public function test_status_endpoint_contract_format(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('subsidi energi');

        $storage->updateStage(
            $session,
            'news_analysis',
            'Menganalisis Berita',
            'AI sedang membandingkan informasi dari berbagai sumber berita.',
            83,
            ['sources_count' => 5, 'articles_count' => 3]
        );

        $response = $this->getJson(route('summaries.status', $session->id));

        $response->assertStatus(200);
        $response->assertJson([
            'session_id' => $session->id,
            'status' => 'processing',
            'current_stage' => 'news_analysis',
            'stage_label' => 'Menganalisis Berita',
            'description' => 'AI sedang membandingkan informasi dari berbagai sumber berita.',
            'progress' => 83,
            'meta' => [
                'sources_count' => 5,
                'articles_count' => 3,
            ],
            'error' => null,
            'summary_id' => null,
        ]);

        $completedStages = $response->json('completed_stages');
        $this->assertContains('initializing', $completedStages);
    }

    public function test_completed_status_contract(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('ekonomi digital');

        $summary = $storage->saveNewsSummary($session, [
            'title' => 'Perkembangan Ekonomi Digital',
            'summary' => 'Rangkuman lengkap ekonomi digital.',
            'key_points' => ['Poin 1'],
            'chronology' => 'Linimasa.',
            'source_comparison' => 'Perbandingan.',
            'unconfirmed_facts' => 'Fakta.',
            'conclusion' => 'Kesimpulan.',
            'category' => 'ekonomi digital',
        ]);

        $response = $this->getJson(route('summaries.status', $session->id));

        $response->assertStatus(200);
        $response->assertJson([
            'session_id' => $session->id,
            'status' => 'completed',
            'current_stage' => 'completed',
            'stage_label' => 'Rangkuman Selesai',
            'progress' => 100,
            'summary_id' => $summary->id,
            'error' => null,
        ]);
        $this->assertStringContainsString('/summaries/'.$summary->id, $response->json('redirect_url'));
    }

    public function test_cancel_search_session_endpoint(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('topik dibatalkan');

        $response = $this->postJson(route('summaries.cancel', $session->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $session->refresh();
        $this->assertEquals('failed', $session->status);
        $this->assertStringContainsString('dibatalkan', $session->error_message);
    }

    public function test_full_pipeline_updates_all_stages_and_handles_partial_failure(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Analisis Kebijakan Beras 2026',
                                'summary' => 'Ringkasan komprehensif kebijakan beras.',
                                'queries' => ['berita beras terkini', 'regulasi pangan'],
                                'key_points' => ['Operasi pasar diluncurkan', 'Stok beras 2 juta ton'],
                                'chronology' => 'Januari hingga Maret 2026.',
                                'source_comparison' => 'Media mengapresiasi langkah Bulog.',
                                'unconfirmed_facts' => 'Kebutuhan impor masih dikaji.',
                                'conclusion' => 'Pasokan pangan terjaga.',
                                'category' => 'kebijakan beras',
                                'analysis' => 'Analisis mendalam.',
                                'references' => [
                                    [
                                        'source_name' => 'Antara',
                                        'source_title' => 'Bulog gelar operasi pasar',
                                        'source_url' => 'https://antaranews.com/bulog-operasi-pasar',
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
            '*/search*' => Http::response([
                'results' => [
                    [
                        'url' => 'https://antaranews.com/bulog-operasi-pasar',
                        'title' => 'Bulog gelar operasi pasar',
                        'content' => 'Bulog memastikan stok beras aman.',
                    ],
                    [
                        'url' => 'https://broken-source.com/berita-gagal',
                        'title' => 'Sumber Bermasalah',
                        'content' => 'Error 404',
                    ],
                ],
            ], 200),
            'https://broken-source.com/*' => Http::response('Not Found', 404),
            '*' => Http::response('<html><title>Berita Beras</title><article><p>Pemerintah menyalurkan beras cadangan.</p></article></html>', 200),
        ]);

        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('kebijakan beras 2026');

        // Execute Job synchronously
        ProcessNewsSearchJob::dispatchSync($session->id);

        $session->refresh();

        $this->assertEquals('completed', $session->status);
        $this->assertEquals(100, $session->progress);
        $this->assertEquals('completed', $session->current_stage);

        // Verify completed stages history contains all progression stages
        $completed = $session->completed_stages;
        $this->assertContains('initializing', $completed);
        $this->assertContains('prompt_enhancement', $completed);
        $this->assertContains('query_generation', $completed);
        $this->assertContains('news_searching', $completed);
        $this->assertContains('content_extraction', $completed);
        $this->assertContains('news_filtering', $completed);
        $this->assertContains('news_analysis', $completed);
        $this->assertContains('summarization', $completed);
        $this->assertContains('completed', $completed);

        // Verify summary was saved and linked to session
        $this->assertNotNull($session->newsSummary);
        $this->assertStringContainsString('Kebijakan beras 2026', $session->newsSummary->title);
    }
}
