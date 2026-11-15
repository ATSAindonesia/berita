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
    public function create()
    {
        return view('summaries.create');
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

            return response()->json([
                'message' => 'Proses pencarian dan analisis berita sedang berjalan.',
                'search_session_id' => $session->id,
                'status' => $session->status,
                'summary_id' => $session->newsSummary?->id,
            ], 202);
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

        return response()->json([
            'session_id' => $session->id,
            'status' => $session->status,
            'error_message' => $session->error_message,
            'summary_id' => $session->newsSummary?->id,
        ]);
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
