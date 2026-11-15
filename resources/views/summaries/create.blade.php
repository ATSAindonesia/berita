<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita apa yang ingin kamu pahami hari ini?</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .font-serif-custom {
            font-family: 'Newsreader', Georgia, Cambria, 'Times New Roman', serif;
        }

        .search-pill-container {
            width: 100%;
            max-width: 500px;
            height: 48px;
            background-color: #ffffff;
            border: 1px solid #ebe7df;
            border-radius: 14px;
            display: flex;
            align-items: center;
            padding-left: 16px;
            padding-right: 6px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .search-pill-container:focus-within {
            border-color: #224c3e;
            box-shadow: 0 4px 24px -2px rgba(34, 76, 62, 0.12), 0 0 0 1px #224c3e;
        }

        .search-input {
            flex: 1;
            height: 100%;
            border: none;
            background: transparent;
            outline: none;
            padding-left: 10px;
            padding-right: 8px;
            font-size: 13.5px;
            color: #1e1e21;
            font-family: inherit;
        }

        .search-input::placeholder {
            color: #9c9b98;
            font-size: 13px;
        }

        .btn-submit-arrow {
            width: 36px;
            height: 36px;
            background-color: #224c3e;
            border: none;
            border-radius: 10px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background-color 0.15s ease, transform 0.08s ease;
        }

        .btn-submit-arrow:hover {
            background-color: #1a3c31;
        }

        .btn-submit-arrow:active {
            transform: scale(0.96);
        }

        .nav-back-link {
            position: absolute;
            top: 28px;
            left: 28px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #787774;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .nav-back-link:hover {
            color: #1e1e21;
        }
    </style>
</head>
<body>

    <!-- Back to Dashboard Navigation -->
    <a href="{{ route('dashboard') }}" class="nav-back-link">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
        </svg>
        <span>Kembali ke dashboard</span>
    </a>

    <!-- Main Content Container -->
    <main class="w-full max-w-[560px] flex flex-col items-center">
        <!-- Heading -->
        <h1 class="font-serif-custom text-[33px] font-normal leading-[1.28] text-center text-[#1e1e21] mb-11 select-none">
            Berita apa yang ingin kamu<br>
            pahami hari ini?
        </h1>

        <!-- Search / Summarize Form -->
        <form id="summarizeForm" method="POST" action="{{ route('summaries.store') }}" class="w-full flex flex-col items-center">
            @csrf

            <div class="search-pill-container">
                <!-- Search Magnifier Icon -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9c9b98" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>

                <!-- Query Input -->
                <input
                    type="text"
                    name="query"
                    id="queryInput"
                    placeholder='Cari berita, misalnya "harga beras naik"'
                    required
                    autofocus
                    autocomplete="off"
                    class="search-input"
                    value="{{ old('query') }}"
                />

                <!-- Submit Button with Up-Right Arrow Icon -->
                <button type="submit" id="submitBtn" class="btn-submit-arrow" aria-label="Mulai rangkum berita">
                    <svg id="arrowIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="7" y1="17" x2="17" y2="7" />
                        <polyline points="7 7 17 7 17 17" />
                    </svg>

                    <!-- Loading Spinner (hidden by default) -->
                    <svg id="loadingSpinner" class="hidden animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25" stroke="currentColor"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor"/>
                    </svg>
                </button>
            </div>

            @error('query')
                <div class="text-[12px] text-[#b91c1c] mt-2.5 text-center">
                    {{ $message }}
                </div>
            @enderror
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('summarizeForm');
            const submitBtn = document.getElementById('submitBtn');
            const arrowIcon = document.getElementById('arrowIcon');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const queryInput = document.getElementById('queryInput');

            form.addEventListener('submit', function () {
                if (queryInput.value.trim().length > 0) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.85';
                    arrowIcon.classList.add('hidden');
                    loadingSpinner.classList.remove('hidden');
                }
            });
        });
    </script>
</body>
</html>

