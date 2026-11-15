<?php

namespace Tests\Feature;

use App\Models\NewsSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_summary_page_renders_correctly(): void
    {
        $response = $this->get('/summaries/create');

        $response->assertStatus(200);
        $response->assertSee('Berita apa yang ingin kamu');
        $response->assertSee('pahami hari ini?');
        $response->assertSee('harga beras naik');
    }

    public function test_user_can_submit_news_query_and_creates_summary_with_references(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Harga beras naik: Laporan Analisis Terkini',
                                'summary' => 'Ringkasan komprehensif harga beras naik di berbagai pasar.',
                                'queries' => ['harga beras naik terbaru'],
                                'key_points' => ['Pasokan berkurang', 'Harga naik 10%'],
                                'chronology' => 'Kronologi kenaikan harga sejak awal bulan.',
                                'source_comparison' => 'Media mengonfirmasi adanya lonjakan harga.',
                                'unconfirmed_facts' => 'Data impor belum dirilis.',
                                'conclusion' => 'Pemerintah perlu operasi pasar.',
                                'category' => 'harga beras naik',
                                'analysis' => 'Analisis mendalam pergerakan harga.',
                                'references' => [
                                    [
                                        'source_name' => 'Antara',
                                        'source_title' => 'Harga beras naik di pasar induk',
                                        'source_url' => 'https://antaranews.com/beras-naik',
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
                        'url' => 'https://news.detik.com/harga-beras-naik',
                        'title' => 'Harga Beras Naik',
                        'content' => 'Laporan harga beras naik.',
                    ],
                ],
            ], 200),
            '*' => Http::response('<html><title>Harga beras naik</title><article><p>Harga beras naik di pasar.</p></article></html>', 200),
        ]);

        $response = $this->post('/summaries', [
            'query' => 'harga beras naik',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('news_summaries', [
            'category' => 'harga beras naik',
        ]);

        $summary = NewsSummary::first();
        $this->assertNotNull($summary);
        $this->assertStringContainsString('Harga beras naik', $summary->title);
        $this->assertGreaterThan(0, $summary->references()->count());

        // Verify it appears on the dashboard
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($summary->title);
        $dashboardResponse->assertSee('harga beras naik');
    }

    public function test_validation_fails_on_empty_query(): void
    {
        $response = $this->post('/summaries', [
            'query' => '',
        ]);

        $response->assertSessionHasErrors('query');
        $this->assertDatabaseCount('news_summaries', 0);
    }
}
