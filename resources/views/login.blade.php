<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selamat datang kembali</title>

    <!-- Google Fonts: Newsreader for elegant serif & Plus Jakarta Sans -->
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
            align-items: center;
            justify-content: center;
            padding: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .login-wrapper {
            width: 340px;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-title {
            font-family: 'Newsreader', Georgia, Cambria, 'Times New Roman', serif;
            font-size: 29px;
            font-weight: 400;
            line-height: 1.2;
            color: #1e1e21;
            text-align: center;
            margin-bottom: 48px;
            user-select: none;
            letter-spacing: -0.01em;
        }

        .login-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            position: relative;
            width: 100%;
        }

        .login-input {
            width: 100%;
            height: 44px;
            background-color: #ffffff;
            border: 1px solid #e8e4db;
            border-radius: 10px;
            padding-left: 16px;
            padding-right: 44px;
            font-size: 14px;
            color: #1e1e21;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .login-input::placeholder {
            color: #8e8d8a;
            letter-spacing: 2px;
            font-size: 14px;
        }

        .login-input:focus {
            border-color: #224c3e;
            box-shadow: 0 0 0 1px #224c3e;
        }

        .eye-toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8e8d8a;
            transition: color 0.15s ease;
        }

        .eye-toggle-btn:hover {
            color: #4a4946;
        }

        .login-btn {
            width: 100%;
            height: 40px;
            background-color: #224c3e;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            transition: background-color 0.15s ease, opacity 0.15s ease;
        }

        .login-btn:hover {
            background-color: #1a3c31;
        }

        .login-btn:active {
            opacity: 0.95;
        }

        .error-feedback {
            font-size: 12px;
            color: #b91c1c;
            margin-top: 6px;
            text-align: left;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>

    <main class="login-wrapper">
        <!-- Title -->
        <h1 class="login-title">
            Selamat datang kembali
        </h1>

        <!-- Form -->
        <form method="POST" action="{{ route('login.post') }}" class="login-form">
            @csrf

            <!-- Password Input -->
            <div class="input-container">
                <div class="input-group">
                    <input
                        id="passwordInput"
                        type="password"
                        name="password"
                        placeholder="••••••••••"
                        required
                        autofocus
                        autocomplete="current-password"
                        class="login-input"
                    />

                    <!-- Eye toggle button -->
                    <button
                        type="button"
                        id="togglePassword"
                        class="eye-toggle-btn"
                        aria-label="Tampilkan / Sembunyikan password"
                        tabindex="-1"
                    >
                        <!-- Eye Icon -->
                        <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        <!-- Eye Off Icon -->
                        <svg id="eyeOffIcon" class="hidden" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                            <line x1="2" x2="22" y1="2" y2="22" />
                        </svg>
                    </button>
                </div>

                @error('password')
                    <div class="error-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-btn">
                <span>Masuk</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="m12 5 7 7-7 7" />
                </svg>
            </button>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                    if (isPassword) {
                        eyeIcon.classList.add('hidden');
                        eyeOffIcon.classList.remove('hidden');
                    } else {
                        eyeIcon.classList.remove('hidden');
                        eyeOffIcon.classList.add('hidden');
                    }
                    passwordInput.focus();
                });
            }
        });
    </script>
</body>
</html>
