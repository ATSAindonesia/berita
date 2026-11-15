<?php

namespace Tests\Feature;

use App\Services\NewsStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FollowUpPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_follow_up_prompt_and_gets_ai_response(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('harga beras naik');

        $summary = $storage->saveNewsSummary($session, [
            'title' => 'Harga beras naik: Poin Penting Hari Ini',
            'summary' => 'Rangkuman perkembangan harga beras nasional.',
            'key_points' => ['Harga naik 8%', 'Operasi pasar digencarkan'],
            'chronology' => 'Linimasa dalam 2 pekan terakhir.',
            'source_comparison' => 'Seluruh media mencatat kenaikan.',
            'unconfirmed_facts' => 'Data impor belum final.',
            'conclusion' => 'Perlu percepatan distribusi.',
            'category' => 'harga beras naik',
        ]);

        Http::fake([
            'https://tokenportal.id/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Pemerintah melalui Bulog telah menyiapkan 200 ribu ton cadangan beras untuk stabilisasi harga.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('summaries.follow_up', $summary), [
            'prompt' => 'Apa langkah konkret pemerintah selanjutnya?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'prompt' => 'Apa langkah konkret pemerintah selanjutnya?',
            'answer' => 'Pemerintah melalui Bulog telah menyiapkan 200 ribu ton cadangan beras untuk stabilisasi harga.',
        ]);
        $response->assertJsonStructure(['success', 'prompt', 'answer', 'created_at']);
    }

    public function test_follow_up_validation_fails_on_empty_prompt(): void
    {
        $storage = app(NewsStorageService::class);
        $session = $storage->createSearchSession('harga beras naik');

        $summary = $storage->saveNewsSummary($session, [
            'title' => 'Harga beras naik: Poin Penting Hari Ini',
            'summary' => 'Ringkasan beras.',
            'category' => 'harga beras naik',
        ]);

        $response = $this->postJson(route('summaries.follow_up', $summary), [
            'prompt' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('prompt');
    }
}
