<?php

namespace Tests\Feature;

use App\Models\NewsSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
