<?php

namespace App\Services;

use InvalidArgumentException;

class PromptService
{
    /**
     * Validate the user prompt.
     *
     * @throws InvalidArgumentException
     */
    public function validatePrompt(string $prompt): string
    {
        $trimmed = trim($prompt);

        if (mb_strlen($trimmed) < 2) {
            throw new InvalidArgumentException('Prompt minimal terdiri dari 2 karakter.');
        }

        if (mb_strlen($trimmed) > 500) {
            throw new InvalidArgumentException('Prompt maksimal 500 karakter.');
        }

        return $trimmed;
    }

    /**
     * Base System Prompt for News Research Analyst.
     * Enforces objectivity, accuracy, rigorous cross-referencing, and anti-hallucination.
     */
    public function getBaseSystemPrompt(): string
    {
        return <<<'PROMPT'
Anda adalah seorang "News Research Analyst" profesional tingkat tinggi.
Tugas utama Anda adalah mencari, mengumpulkan, memverifikasi, menganalisis, dan merangkum berita peristiwa nasional maupun internasional secara mendalam, objektif, dan faktual.

Prinsip dan karakteristik mutlak yang wajib dipatuhi:
1. AKURASI & OBJEKTIVITAS: Utamakan kebenaran fakta dan kenetralan sudut pandang. Hindari bias politik, ideologi, maupun sensasionalisme.
2. LARANGAN HALUSINASI: Jangan pernah mengarang fakta, data statistik, nama tokoh, kutipan, tanggal, maupun sumber. Jika suatu informasi tidak tertera dalam sumber, jangan asumsikan atau reka cipta.
3. PEMISAHAN FAKTA & OPINI: Bedakan secara tegas antara fakta keras (kejadian empiris terverifikasi), opini/analisis narasumber, dan spekulasi/rumor yang beredar.
4. KRONOLOGI & WAKTU: Perhatikan secara teliti tanggal publikasi tiap artikel serta urutan linimasa kejadian sebenarnya.
5. PERBANDINGAN MULTI-SUMBER: Selalu bandingkan informasi dari berbagai media. Identifikasi titik temu (konsensus), sudut pandang yang berbeda, serta inkonsistensi atau pertentangan fakta antar berita.
6. SKEPTISISME AGREGATOR: Jangan menyimpulkan bahwa banyaknya artikel berarti banyak sumber independen; seringkali banyak media hanya mereplikasi siaran pers atau berita kantor berita yang sama.
7. PRIORITAS SUMBER PRIMER: Utamakan pernyataan resmi pihak berwenang, dokumen resmi, dan media massa terakreditasi dan kredibel.
8. KREDIBILITAS REFERENSI: Pastikan setiap klaim penting dalam analisis dan rangkuman merujuk pada sumber berita yang bersangkutan.
PROMPT;
    }

    /**
     * Enhance user prompt with clear separation between system instructions and original prompt.
     * Does not alter the user's core intent.
     */
    public function enhancePrompt(string $userPrompt): array
    {
        $validated = $this->validatePrompt($userPrompt);
        $systemPrompt = $this->getBaseSystemPrompt();

        $enhancedInstruction = <<<USER_ENHANCEMENT
[PERMINTAAN PENGGUNA]:
"{$validated}"

[PETUNJUK ANALISIS]:
Lakukan penelusuran dan analisis berita mendalam berdasarkan topik permintaan pengguna di atas.
Tetap patuhi batasan topik pengguna secara presisi tanpa memperluas ke hal di luar konteks kecuali jika berkaitan langsung dengan akar penyebab atau dampak krusialnya.
USER_ENHANCEMENT;

        return [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $enhancedInstruction,
            'original_prompt' => $validated,
        ];
    }

    /**
     * Build prompt for Step 3: Search Query Generation.
     * AI generates structured JSON search queries.
     */
    public function buildQueryGenerationPrompt(string $userPrompt): array
    {
        $systemPrompt = $this->getBaseSystemPrompt()."\n\n".<<<'INSTRUCTION'
Tugas spesifik saat ini:
Ubah topik atau permintaan pengguna menjadi 3 hingga 5 search query berita yang spesifik, relevan, dan optimal untuk mesin pencari (SearXNG).
Karakteristik query:
1. Pertimbangkan konteks peristiwa, topik inti, lokasi geografis (jika relevan), dan periode waktu terkini.
2. Gunakan variasi kata kunci (misal: sinonim, istilah resmi pemerintah, nama lembaga terkait, atau kata kunci bahasa Indonesia/Inggris sesuai konteks).
3. Buat query dalam bentuk frase pencarian mesin telusur yang efektif (hindari kalimat tanya panjang yang tidak lazim di search engine).

Output WAJIB berupa JSON murni dengan format berikut:
{
    "topic": "nama topik singkat",
    "language": "id",
    "queries": [
        "kata kunci query 1",
        "kata kunci query 2",
        "kata kunci query 3"
    ]
}
INSTRUCTION;

        $userInstruction = <<<USER
Topik berita yang diminta:
"{$userPrompt}"

Hasilkan JSON daftar search queries yang optimal sekarang.
USER;

        return [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userInstruction,
        ];
    }

    /**
     * Build prompt for Step 6: AI News Analysis.
     */
    public function buildAnalysisPrompt(string $userPrompt, array $articles): array
    {
        $systemPrompt = $this->getBaseSystemPrompt()."\n\n".<<<'INSTRUCTION'
Tugas spesifik saat ini:
Analisis secara komprehensif kumpulan artikel berita yang disediakan.
Identifikasi:
1. Fakta utama yang terverifikasi.
2. Informasi penting dan konteks latar belakang peristiwa.
3. Kronologi urutan kejadian (jika terdapat linimasa waktu).
4. Pihak atau entitas yang terlibat (tokoh, lembaga, kementerian, masyarakat, saksi).
5. Perbandingan informasi antar sumber berita (kesamaan, perbedaan fokus, dan kontradiksi jika ada).
6. Fakta yang belum terkonfirmasi, masih dalam penyelidikan, atau diperdebatkan.

Output WAJIB berupa JSON murni dengan format:
{
    "main_facts": [
        "fakta 1",
        "fakta 2"
    ],
    "context": "konteks latar belakang dan signifikansi peristiwa",
    "chronology": [
        {
            "time": "waktu/tanggal",
            "event": "penjelasan peristiwa"
        }
    ],
    "entities_involved": [
        "entitas 1",
        "entitas 2"
    ],
    "source_comparison": {
        "agreements": "hal-hal yang disepakati oleh seluruh sumber",
        "discrepancies": "perbedaan data, angka, atau sudut pandang antar sumber (atau 'Tidak ada pertentangan signifikan' jika konsisten)",
        "contradictions": "informasi yang saling bertentangan antar berita jika ada"
    },
    "unconfirmed_or_disputed": [
        "poin spekulasi atau klaim yang belum diverifikasi independen"
    ]
}
INSTRUCTION;

        $articlesText = '';
        foreach ($articles as $index => $art) {
            $num = $index + 1;
            $title = $art['title'] ?? 'Tanpa Judul';
            $source = $art['source_name'] ?? ($art['domain'] ?? 'Media');
            $url = $art['url'] ?? '';
            $date = ! empty($art['published_at']) ? $art['published_at'] : 'Tanggal tidak diketahui';
            $content = $art['clean_content'] ?? ($art['snippet'] ?? '');

            // Truncate per article to 2500 chars to fit context window comfortably
            $contentSnippet = mb_substr($content, 0, 2500);

            $articlesText .= <<<ITEM
---
[SUMBER #{$num}]: {$source}
JUDUL: {$title}
TANGGAL: {$date}
URL: {$url}
ISI BERITA:
{$contentSnippet}

ITEM;
        }

        $userInstruction = <<<USER
Topik Permintaan Pengguna: "{$userPrompt}"

Berikut adalah kumpulan artikel berita yang berhasil dikumpulkan:
{$articlesText}

Analisis seluruh sumber di atas secara objektif dan hasilkan analisis dalam format JSON sesuai spesifikasi.
USER;

        return [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userInstruction,
        ];
    }

    /**
     * Build prompt for Step 7: AI News Summarization.
     * Enforces the 8 required parts.
     */
    public function buildSummarizationPrompt(string $userPrompt, array $analysisData, array $articles): array
    {
        $systemPrompt = $this->getBaseSystemPrompt()."\n\n".<<<'INSTRUCTION'
Tugas spesifik saat ini:
Buat rangkuman berita akhir yang lengkap, berimbang, dan mudah dipahami berdasarkan hasil analisis berita yang telah dilakukan.

Rangkuman WAJIB mengikuti struktur 8 bagian berikut:
1. Judul Rangkuman (padat, informatif, menarik, dan objektif).
2. Ringkasan Utama (ikhtisar eksekutif 2-4 paragraf yang menjawab 5W+1H).
3. Poin-Poin Penting (daftar bullet points hal-hal terpenting).
4. Kronologi Peristiwa (linimasa peristiwa berurutan jika ada).
5. Perbandingan Informasi Antar Sumber (komparasi perspektif atau angka antar media).
6. Fakta yang Belum Terkonfirmasi atau Masih Diperdebatkan (aspek yang belum final atau butuh klarifikasi).
7. Kesimpulan (kesimpulan sintesis dan implikasi ke depan).
8. Referensi Sumber Berita (daftar media dan judul yang digunakan).

Output WAJIB berupa JSON murni dengan format:
{
    "title": "Judul Rangkuman Berita",
    "summary": "Teks ringkasan utama komprehensif...",
    "key_points": [
        "Poin penting 1",
        "Poin penting 2",
        "Poin penting 3"
    ],
    "chronology": "Teks kronologi peristiwa yang berurutan (atau penjelasan linimasa singkat)",
    "source_comparison": "Uraian perbandingan informasi antar berbagai sumber berita...",
    "unconfirmed_facts": "Fakta atau klaim yang belum terkonfirmasi / masih diperdebatkan...",
    "conclusion": "Kesimpulan akhir dan dampak situasi...",
    "category": "topik kategori singkat"
}
INSTRUCTION;

        $analysisJson = json_encode($analysisData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $sourceList = '';
        foreach ($articles as $index => $art) {
            $num = $index + 1;
            $source = $art['source_name'] ?? ($art['domain'] ?? 'Media');
            $title = $art['title'] ?? 'Tanpa Judul';
            $url = $art['url'] ?? '';
            $sourceList .= "{$num}. [{$source}] {$title} ({$url})\n";
        }

        $userInstruction = <<<USER
Topik Pengguna: "{$userPrompt}"

Hasil Analisis Berita:
{$analysisJson}

Daftar Sumber Berita yang Digunakan:
{$sourceList}

Hasilkan rangkuman akhir dalam format JSON sesuai struktur 8 bagian yang telah ditentukan.
USER;

        return [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userInstruction,
        ];
    }
}
