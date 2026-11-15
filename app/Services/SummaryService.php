<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SummaryService
{
    public function __construct(
        protected PromptService $promptService,
        protected AIService $aiService
    ) {}

    /**
     * Generate the final 8-part news summary based on analysis and collected articles.
     */
    public function summarize(string $userPrompt, array $analysisData, array $articles): array
    {
        $promptBundle = $this->promptService->buildSummarizationPrompt($userPrompt, $analysisData, $articles);
        $systemPrompt = $promptBundle['system_prompt'];
        $userPromptText = $promptBundle['user_prompt'];
        $model = $this->aiService->getModelName();

        try {
            $summaryJson = $this->aiService->generateJson($systemPrompt, $userPromptText);
            $structured = $this->normalizeSummaryOutput($summaryJson, $userPrompt, $articles);

            return [
                'data' => $structured,
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPromptText,
                'ai_response_raw' => json_encode($summaryJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'model' => $model,
            ];
        } catch (Exception $e) {
            Log::warning('SummaryService: AI summarization failed, falling back to structured synthesis: '.$e->getMessage());

            $fallback = $this->buildFallbackSummary($userPrompt, $analysisData, $articles);
            $structured = $this->normalizeSummaryOutput($fallback, $userPrompt, $articles);

            return [
                'data' => $structured,
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPromptText,
                'ai_response_raw' => json_encode($fallback, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'model' => $model.' (fallback)',
            ];
        }
    }

    /**
     * Normalize and construct the full formatted content matching the 8 required parts.
     */
    protected function normalizeSummaryOutput(array $data, string $userPrompt, array $articles): array
    {
        $rawTitle = ! empty($data['title']) ? trim($data['title']) : '';
        $prefix = Str::ucfirst($userPrompt);

        if (empty($rawTitle)) {
            $title = "{$prefix}: Poin Penting Hari Ini";
        } elseif (preg_match('/^'.preg_quote($userPrompt, '/').'/i', $rawTitle)) {
            $title = preg_replace('/^'.preg_quote($userPrompt, '/').'/i', $prefix, $rawTitle);
        } elseif (! str_contains(strtolower($rawTitle), strtolower($userPrompt))) {
            $title = "{$prefix}: {$rawTitle}";
        } else {
            $title = $rawTitle;
        }
        $summary = ! empty($data['summary']) ? trim($data['summary']) : "Rangkuman komprehensif mengenai {$userPrompt} berdasarkan analisis berbagai sumber berita.";

        $keyPoints = [];
        if (! empty($data['key_points']) && is_array($data['key_points'])) {
            $keyPoints = array_values(array_filter(array_map('trim', $data['key_points'])));
        }

        $chronology = ! empty($data['chronology']) ? trim((string) $data['chronology']) : 'Kronologi peristiwa sedang terus berkembang mengikuti laporan terbaru.';
        $sourceComparison = ! empty($data['source_comparison']) ? trim((string) $data['source_comparison']) : 'Informasi dari berbagai media mengonfirmasi inti peristiwa yang sama dengan penekanan pada dampak dan penanganan otoritas.';
        $unconfirmedFacts = ! empty($data['unconfirmed_facts']) ? trim((string) $data['unconfirmed_facts']) : 'Belum ada laporan yang saling bertentangan secara signifikan; proses verifikasi independen terus berjalan.';
        $conclusion = ! empty($data['conclusion']) ? trim((string) $data['conclusion']) : "Situasi seputar {$userPrompt} memerlukan transparansi dan langkah nyata dari pihak berwenang guna mitigasi dampak lebih lanjut.";
        $category = Str::lower(trim($userPrompt));

        // Build composite full content formatted with markdown sections
        $fullContent = "## 1. Judul Rangkuman\n{$title}\n\n".
            "## 2. Ringkasan Utama\n{$summary}\n\n".
            "## 3. Poin-Poin Penting\n";

        foreach ($keyPoints as $kp) {
            $fullContent .= "- {$kp}\n";
        }

        $fullContent .= "\n## 4. Kronologi Peristiwa\n{$chronology}\n\n".
            "## 5. Perbandingan Informasi Antar Sumber\n{$sourceComparison}\n\n".
            "## 6. Fakta yang Belum Terkonfirmasi atau Masih Diperdebatkan\n{$unconfirmedFacts}\n\n".
            "## 7. Kesimpulan\n{$conclusion}\n\n".
            "## 8. Referensi Sumber Berita\n";

        $references = [];
        foreach ($articles as $index => $art) {
            $num = $index + 1;
            $sourceName = $art['source_name'] ?? ($art['domain'] ?? 'Media Terkait');
            $sourceTitle = $art['title'] ?? "Laporan mengenai {$userPrompt}";
            $sourceUrl = $art['url'] ?? '';

            $references[] = [
                'source_name' => $sourceName,
                'source_title' => $sourceTitle,
                'source_url' => $sourceUrl,
                'author' => $art['author'] ?? null,
                'published_at' => $art['published_at'] ?? now(),
            ];

            $fullContent .= "{$num}. [{$sourceName}] {$sourceTitle} - {$sourceUrl}\n";
        }

        return [
            'title' => $title,
            'summary' => $summary,
            'key_points' => $keyPoints,
            'chronology' => $chronology,
            'source_comparison' => $sourceComparison,
            'unconfirmed_facts' => $unconfirmedFacts,
            'conclusion' => $conclusion,
            'category' => $category,
            'content' => $fullContent,
            'references' => $references,
        ];
    }

    /**
     * Construct heuristic fallback summary adhering to 8-part structure.
     */
    protected function buildFallbackSummary(string $userPrompt, array $analysisData, array $articles): array
    {
        $title = Str::ucfirst($userPrompt).': Poin Penting Hari Ini';
        $summary = "Rangkuman komprehensif mengenai {$userPrompt}. Berdasarkan analisis multi-sumber berita terpercaya, isu ini memicu sorotan luas terkait perkembangan terbaru serta respon aktif dari berbagai pemangku kepentingan.";

        $keyPoints = [];
        if (! empty($analysisData['main_facts']) && is_array($analysisData['main_facts'])) {
            $keyPoints = array_slice($analysisData['main_facts'], 0, 5);
        } else {
            $keyPoints = [
                "Isu '{$userPrompt}' menjadi topik bahasan utama di berbagai kanal berita.",
                'Pihak berwenang dan pemangku kepentingan tengah mengkaji langkah-langkah responsif.',
                'Masyarakat dan pemerhati publik mengharapkan kejelasan serta transparansi tindak lanjut.',
            ];
        }

        $chronologyText = '';
        if (! empty($analysisData['chronology']) && is_array($analysisData['chronology'])) {
            foreach ($analysisData['chronology'] as $c) {
                $time = $c['time'] ?? 'Terkini';
                $event = $c['event'] ?? '';
                $chronologyText .= "- [{$time}] {$event}\n";
            }
        } else {
            $chronologyText = 'Peristiwa ini berlangsung dalam rentang waktu terdekat seiring dengan munculnya laporan perdana di media nasional.';
        }

        $sourceComp = 'Sebagian besar media memiliki kesamaan dalam melaporkan fakta dasar peristiwa, dengan beberapa perbedaan pada fokus dampak sektoral.';
        if (! empty($analysisData['source_comparison']['agreements'])) {
            $sourceComp = $analysisData['source_comparison']['agreements'].' '.($analysisData['source_comparison']['discrepancies'] ?? '');
        }

        $unconfirmed = 'Data kuantitatif detail masih dalam proses sinkronisasi antar lembaga terkait.';
        if (! empty($analysisData['unconfirmed_or_disputed']) && is_array($analysisData['unconfirmed_or_disputed'])) {
            $unconfirmed = implode('. ', $analysisData['unconfirmed_or_disputed']);
        }

        $conclusion = "Perkembangan topik {$userPrompt} memerlukan pemantauan berkelanjutan demi memastikan keterbukaan informasi dan penanganan terukur.";

        return [
            'title' => $title,
            'summary' => $summary,
            'key_points' => $keyPoints,
            'chronology' => trim($chronologyText),
            'source_comparison' => $sourceComp,
            'unconfirmed_facts' => $unconfirmed,
            'conclusion' => $conclusion,
            'category' => Str::lower($userPrompt),
        ];
    }
}
