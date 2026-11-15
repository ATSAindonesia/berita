<?php

namespace App\Services;

use App\Models\AiPrompt;
use App\Models\NewsReference;
use App\Models\NewsSource;
use App\Models\NewsSummary;
use App\Models\SearchSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsStorageService
{
    /**
     * Create an initial search session.
     */
    public function createSearchSession(string $prompt): SearchSession
    {
        return SearchSession::create([
            'prompt' => $prompt,
            'status' => 'pending',
            'current_stage' => 'initializing',
            'stage_label' => 'Mempersiapkan proses pencarian berita',
            'description' => 'Mempersiapkan proses dan analisis untuk pencarian berita.',
            'progress' => 5,
            'completed_stages' => [],
            'meta' => [],
        ]);
    }

    /**
     * Update search session status, search queries, or error messages.
     */
    public function updateSearchSession(int|SearchSession $session, array $data): SearchSession
    {
        $model = $session instanceof SearchSession ? $session : SearchSession::findOrFail($session);
        $model->update($data);

        return $model;
    }

    /**
     * Update the real-time processing stage, label, progress, and metadata of a search session.
     */
    public function updateStage(
        int|SearchSession $session,
        string $stage,
        string $stageLabel,
        string $description,
        ?int $progress = null,
        array $meta = []
    ): SearchSession {
        $model = $session instanceof SearchSession ? $session : SearchSession::findOrFail($session);

        $completedStages = $model->completed_stages ?? [];
        if ($model->current_stage && ! in_array($model->current_stage, $completedStages, true) && $model->current_stage !== $stage) {
            $completedStages[] = $model->current_stage;
        }

        if ($stage === 'completed' && ! in_array('completed', $completedStages, true)) {
            $completedStages[] = 'completed';
        }

        $existingMeta = $model->meta ?? [];
        $mergedMeta = array_merge($existingMeta, $meta);

        $status = $stage === 'completed' ? 'completed' : ($model->status === 'failed' ? 'failed' : 'processing');

        $updateData = [
            'status' => $status,
            'current_stage' => $stage,
            'stage_label' => $stageLabel,
            'description' => $description,
            'completed_stages' => array_values(array_unique($completedStages)),
            'meta' => $mergedMeta,
        ];

        if ($progress !== null) {
            $updateData['progress'] = $progress;
        }

        $model->update($updateData);

        return $model;
    }

    /**
     * Record prompt and AI response in the ai_prompts table.
     */
    public function recordAiPrompt(
        int|SearchSession $session,
        string $promptType,
        string $systemPrompt,
        string $userPrompt,
        ?string $aiResponse,
        ?string $model = null,
        string $status = 'success',
        ?string $errorMessage = null
    ): AiPrompt {
        $sessionId = $session instanceof SearchSession ? $session->id : $session;

        return AiPrompt::create([
            'search_session_id' => $sessionId,
            'prompt_type' => $promptType,
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'ai_response' => $aiResponse,
            'model' => $model,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Store raw or extracted news sources retrieved from SearXNG.
     *
     * @return array<NewsSource>
     */
    public function storeNewsSources(int|SearchSession $session, array $articles): array
    {
        $sessionId = $session instanceof SearchSession ? $session->id : $session;
        $stored = [];

        foreach ($articles as $art) {
            $source = NewsSource::create([
                'search_session_id' => $sessionId,
                'source_name' => $art['source_name'] ?? ($art['domain'] ?? 'Media'),
                'title' => $art['title'] ?? 'Tanpa Judul',
                'url' => $art['url'] ?? '',
                'canonical_url' => $art['canonical_url'] ?? ($art['url'] ?? ''),
                'domain' => $art['domain'] ?? null,
                'snippet' => $art['snippet'] ?? null,
                'raw_content' => $art['raw_content'] ?? null,
                'clean_content' => $art['clean_content'] ?? null,
                'author' => $art['author'] ?? null,
                'published_at' => ! empty($art['published_at']) ? Carbon::parse($art['published_at']) : null,
                'relevance_score' => $art['relevance_score'] ?? null,
                'status' => $art['status'] ?? 'pending',
            ]);

            $stored[] = $source;
        }

        return $stored;
    }

    /**
     * Persist the final summary, associate news sources via summary_sources,
     * and populate news_references for backward compatibility.
     *
     * @param  array<NewsSource>  $newsSources
     */
    public function saveNewsSummary(
        int|SearchSession $session,
        array $summaryData,
        array $newsSources = [],
        string $aiModel = 'deepseek-v4.1-flash-unli'
    ): NewsSummary {
        $sessionId = $session instanceof SearchSession ? $session->id : $session;

        return DB::transaction(function () use ($sessionId, $summaryData, $newsSources, $aiModel) {
            // 1. Create the NewsSummary record
            $newsSummary = NewsSummary::create([
                'search_session_id' => $sessionId,
                'title' => $summaryData['title'],
                'summary' => $summaryData['summary'],
                'content' => $summaryData['content'] ?? null,
                'key_points' => $summaryData['key_points'] ?? [],
                'chronology' => $summaryData['chronology'] ?? null,
                'source_comparison' => $summaryData['source_comparison'] ?? null,
                'unconfirmed_facts' => $summaryData['unconfirmed_facts'] ?? null,
                'conclusion' => $summaryData['conclusion'] ?? null,
                'category' => $summaryData['category'] ?? 'berita',
                'ai_model' => $aiModel,
                'published_at' => Carbon::now(),
            ]);

            // 2. Attach news sources to summary_sources pivot table
            if (! empty($newsSources)) {
                $attachData = [];
                foreach ($newsSources as $source) {
                    $sourceId = $source instanceof NewsSource ? $source->id : $source;
                    $attachData[$sourceId] = [
                        'relevance_note' => 'Digunakan dalam analisis dan rangkuman berita',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                $newsSummary->sources()->attach($attachData);
            }

            // 3. Populate news_references table for backward compatibility
            $referencesData = [];
            if (! empty($newsSources)) {
                foreach ($newsSources as $source) {
                    $referencesData[] = [
                        'news_summary_id' => $newsSummary->id,
                        'source_name' => $source->source_name ?? 'Media Berita',
                        'source_title' => $source->title ?? 'Laporan Berita',
                        'source_url' => $source->url,
                        'author' => $source->author ?? null,
                        'published_at' => $source->published_at ?? Carbon::now(),
                        'created_at' => Carbon::now(),
                    ];
                }
            } elseif (! empty($summaryData['references'])) {
                foreach ($summaryData['references'] as $ref) {
                    $referencesData[] = [
                        'news_summary_id' => $newsSummary->id,
                        'source_name' => $ref['source_name'] ?? 'Media Berita',
                        'source_title' => $ref['source_title'] ?? 'Laporan Berita',
                        'source_url' => $ref['source_url'] ?? '',
                        'author' => $ref['author'] ?? null,
                        'published_at' => Carbon::now(),
                        'created_at' => Carbon::now(),
                    ];
                }
            }

            if (! empty($referencesData)) {
                NewsReference::insert($referencesData);
            }

            // 4. Mark search session as completed
            $this->updateStage(
                $sessionId,
                'completed',
                'Rangkuman Selesai',
                'Rangkuman berita berhasil dibuat.',
                100,
                ['summary_id' => $newsSummary->id]
            );

            Log::info("NewsSummary #{$newsSummary->id} stored successfully for session #{$sessionId}");

            return $newsSummary;
        });
    }
}
