<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rangkuman berita</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f6f4ee;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e1e21;
            min-height: 100vh;
            padding: 32px 24px 64px 24px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .dashboard-container {
            width: 100%;
            max-width: 864px;
            margin: 0 auto;
        }

        .font-serif-custom {
            font-family: 'Newsreader', Georgia, Cambria, 'Times New Roman', serif;
        }

        .main-card {
            background-color: #ffffff;
            border: 1px solid #ebe7df;
            border-radius: 18px;
            min-height: 512px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 28px;
            border-bottom: 1px solid #f2efe9;
            text-decoration: none;
            color: inherit;
            transition: background-color 0.12s ease;
        }

        .summary-row:hover {
            background-color: #faf8f5;
        }

        .btn-primary {
            background-color: #224c3e;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            height: 38px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.15s ease, opacity 0.15s ease;
        }

        .btn-primary:hover {
            background-color: #1a3c31;
        }

        .btn-primary:active {
            opacity: 0.95;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Top Header -->
        <header class="flex items-start justify-between mb-7">
            <div>
                <h1 class="font-serif-custom text-[30px] font-normal leading-tight text-[#1e1e21] mb-1.5 select-none">
                    Rangkuman berita
                </h1>
                <p class="text-[13px] text-[#787774] font-normal">
                    Semua berita yang sudah kamu rangkum, tersimpan di satu tempat.
                </p>
            </div>

            <!-- Action Button -->
            <a href="{{ route('summaries.create') }}" class="btn-primary select-none">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <span>Buat rangkuman</span>
            </a>
        </header>

        @if (session('success'))
            <div class="mb-5 px-4 py-3 rounded-xl bg-[#eef4f0] border border-[#d2e2d8] text-[#224c3e] text-[13px] flex items-center justify-between shadow-2xs">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-[#224c3e]/60 hover:text-[#224c3e] text-lg font-bold leading-none cursor-pointer">&times;</button>
            </div>
        @endif

        <!-- Main Card -->
        <main class="main-card">
            <!-- Card Header -->
            <div class="flex items-center justify-between px-7 pt-6 pb-4">
                <h2 class="font-serif-custom text-[19px] font-normal text-[#1e1e21]">
                    Terakhir dibuat
                </h2>
                <span class="text-[11px] font-medium tracking-wider text-[#a1a19d] uppercase">
                    {{ $totalCount }} RANGKUMAN
                </span>
            </div>

            <!-- Summaries List / Empty State -->
            @if ($summaries->isNotEmpty())
                <div class="divide-y divide-[#f2efe9]">
                    @foreach ($summaries as $summary)
                        <a href="{{ route('summaries.show', $summary) }}" class="summary-row">
                            <!-- Left: Title & Topic -->
                            <div class="pr-4">
                                <h3 class="font-serif-custom text-[16.5px] font-normal text-[#1e1e21] leading-snug mb-1">
                                    {{ $summary->title }}
                                </h3>
                                <div class="flex items-center text-[12px] text-[#8e8d8a]">
                                    <span class="uppercase tracking-widest text-[10.5px] font-medium text-[#9c9b98]">TOPIK</span>
                                    <span class="text-[#c4c2bc] mx-2 font-bold">·</span>
                                    <span class="lowercase font-mono text-[11.5px] text-[#8e8d8a]">{{ $summary->category }}</span>
                                </div>
                            </div>

                            <!-- Right: Date, Source count, and Arrow icon -->
                            <div class="flex items-center gap-6 shrink-0 text-[12.5px]">
                                <span class="text-[#8e8d8a]">
                                    {{ $summary->published_at ? $summary->published_at->format('d M Y') : $summary->created_at->format('d M Y') }}
                                </span>
                                <span class="font-medium text-[#224c3e] min-w-[70px] text-right">
                                    {{ $summary->references_count ?? 0 }} sumber
                                </span>
                                <span class="text-[#8e8d8a]">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7" />
                                        <polyline points="7 7 17 7 17 17" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- Empty State Element -->
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#f6f4ee] border border-[#e8e4db] flex items-center justify-center text-[#224c3e] mb-4 shadow-sm">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                            <path d="M18 14h-8" />
                            <path d="M15 18h-5" />
                            <path d="M10 6h8v4h-8V6Z" />
                        </svg>
                    </div>

                    <h3 class="font-serif-custom text-[21px] font-normal text-[#1e1e21] mb-2">
                        Belum ada rangkuman berita
                    </h3>
                    
                    <p class="text-[13px] text-[#787774] max-w-sm mb-6 leading-relaxed">
                        Data masih kosong. Mulai rangkum berita pertama Anda dan semua poin penting serta referensi akan tertata rapi di sini.
                    </p>

                    <a href="{{ route('summaries.create') }}" class="btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        <span>Buat rangkuman sekarang</span>
                    </a>
                </div>
            @endif
        </main>
    </div>

</body>
</html>

