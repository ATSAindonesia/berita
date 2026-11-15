<?php

namespace Tests\Feature;

use App\Models\NewsReference;
use App\Models\NewsSummary;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_empty_state_when_no_data(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Rangkuman berita');
        $response->assertSee('Terakhir dibuat');
        $response->assertSee('0 RANGKUMAN');
        $response->assertSee('Belum ada rangkuman berita');
        $response->assertSee('Data masih kosong');
    }

    public function test_dashboard_displays_summaries_list_when_data_exists(): void
    {
        $summary = NewsSummary::create([
            'title' => 'Harga beras naik: poin penting hari ini',
            'summary' => 'Ringkasan singkat',
            'category' => 'harga beras naik',
            'ai_model' => 'gemini-1.5-pro',
            'published_at' => Carbon::create(2026, 3, 12),
        ]);

        NewsReference::create([
            'news_summary_id' => $summary->id,
            'source_name' => 'Kompas',
            'source_url' => 'https://kompas.com',
            'published_at' => Carbon::create(2026, 3, 12),
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('1 RANGKUMAN');
        $response->assertSee('Harga beras naik: poin penting hari ini');
        $response->assertSee('harga beras naik');
        $response->assertSee('12 Mar 2026');
        $response->assertSee('1 sumber');
        $response->assertDontSee('Belum ada rangkuman berita');
    }
}
