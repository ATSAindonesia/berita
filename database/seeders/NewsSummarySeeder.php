<?php

namespace Database\Seeders;

use App\Models\NewsReference;
use App\Models\NewsSummary;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewsSummarySeeder extends Seeder
{
    /**
     * Run the database seeds with the exact sample data from design.
     */
    public function run(): void
    {
        $samples = [
            [
                'title' => 'Harga beras naik: poin penting hari ini',
                'category' => 'harga beras naik',
                'published_at' => Carbon::create(2026, 3, 12),
                'sources_count' => 128,
            ],
            [
                'title' => 'Pemilu 2029: peta koalisi terbaru',
                'category' => 'pemilu 2029',
                'published_at' => Carbon::create(2026, 3, 11),
                'sources_count' => 86,
            ],
            [
                'title' => 'Perkembangan AI di Indonesia',
                'category' => 'AI di Indonesia',
                'published_at' => Carbon::create(2026, 3, 10),
                'sources_count' => 74,
            ],
            [
                'title' => 'Peluang dan tantangan transisi energi',
                'category' => 'transisi energi',
                'published_at' => Carbon::create(2026, 3, 8),
                'sources_count' => 92,
            ],
            [
                'title' => 'Performa timnas menuju kualifikasi',
                'category' => 'timnas Indonesia',
                'published_at' => Carbon::create(2026, 3, 6),
                'sources_count' => 51,
            ],
        ];

        foreach ($samples as $data) {
            $summary = NewsSummary::create([
                'title' => $data['title'],
                'summary' => 'Ringkasan mengenai '.$data['title'],
                'content' => 'Konten lengkap rangkuman berita...',
                'category' => $data['category'],
                'ai_model' => 'gemini-1.5-pro',
                'published_at' => $data['published_at'],
            ]);

            // Buat referensi dummy sesuai jumlah sumber
            for ($i = 1; $i <= $data['sources_count']; $i++) {
                NewsReference::create([
                    'news_summary_id' => $summary->id,
                    'source_name' => 'Sumber '.$i,
                    'source_title' => 'Artikel Berita '.$i,
                    'source_url' => 'https://example.com/news/'.$i,
                    'published_at' => $data['published_at'],
                ]);
            }
        }
    }
}
