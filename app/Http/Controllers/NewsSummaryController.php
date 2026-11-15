<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessNewsSearchJob;
use App\Models\NewsSummary;
use App\Models\SearchSession;
use App\Services\AIService;
use App\Services\NewsStorageService;
use App\Services\PromptService;
use Illuminate\Http\Request;

class NewsSummaryController extends Controller
{
    public function __construct(
        protected PromptService $promptService,
        protected NewsStorageService $newsStorageService
    ) {}

    /**
     * Show the minimalist search/create page.
     */
    public function create(Request $request)
    {
        $initialSession = null;
        if ($sessionId = $request->query('session_id')) {
            $sessionModel = SearchSession::with('newsSummary')->find($sessionId);
            if ($sessionModel) {
                $initialSession = $this->formatSessionPayload($sessionModel);
            }
        }

        return view('summaries.create', compact('initialSession'));
    }

    /**
     * Process and queue the news search and summary generation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:500'],
        ], [
            'query.required' => 'Silakan masukkan topik atau berita yang ingin dirangkum.',
            'query.min' => 'Topik minimal 2 karakter.',
            'query.max' => 'Topik maksimal 500 karakter.',
        ]);

        $query = $this->promptService->validatePrompt($validated['query']);

        // Create search session
        $session = $this->newsStorageService->createSearchSession($query);

        // Dispatch pipeline via Laravel Queue
        if (config('queue.default') === 'sync' || $request->boolean('sync')) {
            ProcessNewsSearchJob::dispatchSync($session->id);
        } else {
            ProcessNewsSearchJob::dispatch($session->id);
        }

        if ($request->wantsJson()) {
            $session->refresh();

            return response()->json(array_merge([
                'message' => 'Proses pencarian dan analisis berita sedang berjalan.',
                'search_session_id' => $session->id,
            ], $this->formatSessionPayload($session)), 202);
        }

        return redirect()->route('dashboard')->with('success', "Permintaan rangkuman untuk '{$query}' berhasil diproses.");
    }

    /**
     * Display a specific news summary and its references.
     */
    public function show(Request $request, NewsSummary $summary)
    {
        $summary->load(['references', 'sources', 'searchSession']);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $summary->id,
                'title' => $summary->title,
                'summary' => $summary->summary,
                'key_points' => $summary->key_points,
                'chronology' => $summary->chronology,
                'source_comparison' => $summary->source_comparison,
                'unconfirmed_facts' => $summary->unconfirmed_facts,
                'conclusion' => $summary->conclusion,
                'category' => $summary->category,
                'ai_model' => $summary->ai_model,
                'published_at' => $summary->published_at?->toIso8601String(),
                'references' => $summary->references->map(fn ($ref) => [
                    'source_name' => $ref->source_name,
                    'source_title' => $ref->source_title,
                    'source_url' => $ref->source_url,
                    'author' => $ref->author,
                    'published_at' => $ref->published_at?->toIso8601String(),
                ]),
            ]);
        }

        return view('summaries.show', compact('summary'));
    }

    /**
     * Check status of a search session (useful for async polling).
     */
    public function status(int $sessionId)
    {
        $session = SearchSession::with('newsSummary')->findOrFail($sessionId);

        return response()->json($this->formatSessionPayload($session));
    }

    /**
     * Real-time Server-Sent Events (SSE) stream for search session status.
     */
    public function stream(int $sessionId)
    {
        $session = SearchSession::with('newsSummary')->find($sessionId);
        if (! $session) {
            return response()->json(['error' => 'Sesi pencarian tidak ditemukan.'], 404);
        }

        return response()->stream(function () use ($sessionId) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            $startTime = time();
            $maxDuration = 180; // Safety timeout

            while (time() - $startTime < $maxDuration) {
                if (connection_aborted()) {
                    break;
                }

                $session = SearchSession::with('newsSummary')->find($sessionId);
                if (! $session) {
                    echo "event: error\n";
                    echo 'data: '.json_encode(['error' => 'Sesi tidak ditemukan'])."\n\n";
                    flush();
                    break;
                }

                $payload = $this->formatSessionPayload($session);

                echo 'data: '.json_encode($payload)."\n\n";
                flush();

                if (in_array($session->status, ['completed', 'failed'], true)) {
                    break;
                }

                usleep(700000); // 700ms stream interval
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Cancel an ongoing search session.
     */
    public function cancel(int $sessionId)
    {
        $session = SearchSession::findOrFail($sessionId);

        if ($session->status !== 'completed') {
            $session->update([
                'status' => 'failed',
                'error_message' => 'Pencarian dibatalkan oleh pengguna.',
                'description' => 'Pencarian dibatalkan oleh pengguna.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pencarian berhasil dibatalkan.',
        ]);
    }

    /**
     * Format search session according to the backend status contract.
     */
    public function formatSessionPayload(SearchSession $session): array
    {
        $summaryId = $session->newsSummary?->id;

        return [
            'session_id' => $session->id,
            'status' => $session->status,
            'current_stage' => $session->current_stage ?? ($session->status === 'completed' ? 'completed' : 'initializing'),
            'stage_label' => $session->stage_label ?? ($session->status === 'completed' ? 'Selesai' : 'Mempersiapkan pencarian'),
            'description' => $session->description ?? ($session->status === 'completed' ? 'Rangkuman berita berhasil dibuat.' : 'Sedang memproses pencarian...'),
            'progress' => $session->progress ?? ($session->status === 'completed' ? 100 : 5),
            'completed_stages' => $session->completed_stages ?? [],
            'meta' => $session->meta ?? [],
            'error' => $session->error_message,
            'error_message' => $session->error_message,
            'summary_id' => $summaryId,
            'redirect_url' => $summaryId ? route('summaries.show', $summaryId) : null,
        ];
    }

    /**
     * Handle follow-up prompt from user regarding this summary.
     */
    public function followUp(Request $request, NewsSummary $summary)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'prompt.required' => 'Silakan masukkan pertanyaan atau instruksi lanjutan.',
            'prompt.min' => 'Prompt minimal 3 karakter.',
            'prompt.max' => 'Prompt maksimal 500 karakter.',
        ]);

        $userPrompt = trim($validated['prompt']);

        $systemPrompt = <<<'SYS'
Anda adalah News Research Analyst profesional.
Tugas Anda adalah menjawab pertanyaan atau instruksi pengguna secara mendalam, objektif, dan faktual berdasarkan konteks berita yang telah dirangkum.
Patuhi prinsip:
1. Utamakan akurasi data dan objektivitas.
2. Jika ada informasi yang tidak tercantum dalam berita, nyatakan dengan jujur bahwa informasi tersebut belum tersedia di sumber.
3. Berikan jawaban dalam bahasa Indonesia yang ringkas, berbobot, dan mudah dipahami.
SYS;

        $keyPointsText = is_array($summary->key_points) ? implode("\n- ", $summary->key_points) : $summary->key_points;

        $contextText = "TOPIK: {$summary->category}\n"
            ."JUDUL: {$summary->title}\n"
            ."RINGKASAN UTAMA: {$summary->summary}\n"
            ."POIN PENTING:\n- {$keyPointsText}\n"
            ."KRONOLOGI: {$summary->chronology}\n"
            ."PERBANDINGAN SUMBER: {$summary->source_comparison}\n"
            ."FAKTA BELUM TERKONFIRMASI: {$summary->unconfirmed_facts}\n"
            ."KESIMPULAN: {$summary->conclusion}\n";

        $prompt = "KONTEKS RANGKUMAN BERITA:\n{$contextText}\n\nPERMINTAAN / PERTANYAAN LANJUTAN:\n\"{$userPrompt}\"\n\nJawab sekarang dengan jelas, faktual, dan terstruktur.";

        $aiService = app(AIService::class);

        try {
            $answer = $aiService->generateText($systemPrompt, $prompt);
        } catch (\Exception $e) {
            $answer = 'Maaf, terjadi kendala saat memproses pertanyaan lanjutan Anda: '.$e->getMessage();
        }

        return response()->json([
            'success' => true,
            'prompt' => $userPrompt,
            'answer' => $answer,
            'created_at' => now()->format('H:i'),
        ]);
    }
}
