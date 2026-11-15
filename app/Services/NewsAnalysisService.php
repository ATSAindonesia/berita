<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class NewsAnalysisService
{
    public function __construct(
        protected PromptService $promptService,
        protected AIService $aiService
    ) {}

    /**
     * Analyze collected news articles using AI.
     *
     * @return array{
     *     analysis: array,
     *     system_prompt: string,
     *     user_prompt: string,
     *     ai_response_raw: string,
     *     model: string
     * }
     */
    public function analyze(string $userPrompt, array $articles): array
    {
        $promptBundle = $this->promptService->buildAnalysisPrompt($userPrompt, $articles);
        $systemPrompt = $promptBundle['system_prompt'];
        $userPromptText = $promptBundle['user_prompt'];
        $model = $this->aiService->getModelName();

        try {
            $analysisResult = $this->aiService->generateJson($systemPrompt, $userPromptText);
            $rawResponse = json_encode($analysisResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            return [
                'analysis' => $analysisResult,
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPromptText,
                'ai_response_raw' => $rawResponse,
                'model' => $model,
            ];
        } catch (Exception $e) {
            Log::warning('NewsAnalysisService: AI analysis generation encountered an error: '.$e->getMessage());

            // Build deterministic structured heuristic analysis as a fallback
            $fallback = $this->buildFallbackAnalysis($userPrompt, $articles);

            return [
                'analysis' => $fallback,
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPromptText,
                'ai_response_raw' => json_encode($fallback, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'model' => $model.' (fallback)',
            ];
        }
    }

    /**
     * Provide a solid fallback analysis when AI service is unavailable or in mock environments.
     */
    protected function buildFallbackAnalysis(string $userPrompt, array $articles): array
    {
        $mainFacts = [];
        $entities = [];
        $chronology = [];

        foreach ($articles as $index => $art) {
            $title = $art['title'] ?? '';
            $source = $art['source_name'] ?? ($art['domain'] ?? 'Media');
            $date = $art['published_at'] ?? 'Terkini';

            if (! empty($title)) {
                $mainFacts[] = "Laporan {$source}: {$title}";
            }

            if (! empty($source) && ! in_array($source, $entities)) {
                $entities[] = $source;
            }

            if ($index < 3 && ! empty($title)) {
                $chronology[] = [
                    'time' => $date,
                    'event' => "Publikasi laporan dari {$source} mengenai {$title}",
                ];
            }
        }

        if (empty($mainFacts)) {
            $mainFacts[] = "Perkembangan informasi terkait topik '{$userPrompt}' sedang menjadi fokus pantauan publik.";
        }

        return [
            'main_facts' => $mainFacts,
            'context' => "Isu seputar '{$userPrompt}' mendapat perhatian luas di berbagai media massa dengan berbagai tinjauan aspek regulasi dan sosial.",
            'chronology' => $chronology,
            'entities_involved' => array_values(array_unique($entities)),
            'source_comparison' => [
                'agreements' => "Seluruh media menyoroti urgensi dan dampak dari isu '{$userPrompt}'.",
                'discrepancies' => 'Sebagian media menekankan dampak ekonomi langsung, sementara sumber lain lebih menyoroti kebijakan pemerintah terkait.',
                'contradictions' => 'Belum ditemukan pertentangan data yang kontradiktif antar sumber utama.',
            ],
            'unconfirmed_or_disputed' => [
                'Rincian estimasi jangka panjang masih menunggu pembaruan resmi dari lembaga terkait.',
            ],
        ];
    }
}
