<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Berita apa yang ingin kamu pahami hari ini?</title>

    <script>
      tailwind = { config: { corePlugins: { preflight: false } } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@300;400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, ::before, ::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #F6F4EF;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #14140F;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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

        @keyframes subtlePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.88); }
        }

        .animate-pulse-dot {
            animation: subtlePulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body style="background-color: #F6F4EF">

    <!-- ======================================================== -->
    <!-- STAGE A: SEARCH FORM INPUT (Default State)               -->
    <!-- ======================================================== -->
    <div id="searchStageSection" class="{{ $initialSession ? 'hidden' : 'flex' }} flex-col items-center justify-center w-full min-h-screen px-4 py-6 relative">
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
    </div>

    <!-- ======================================================== -->
    <!-- STAGE B: 03 · MENCARI (Real-time Loading Screen)          -->
    <!-- ======================================================== -->
    <div
      id="loadingStageSection"
      data-pencil-name="03 · Mencari"
      class="{{ $initialSession ? 'flex' : 'hidden' }} box-border w-full max-w-[1440px] min-h-[900px] flex-col gap-0 justify-between items-center bg-[#F6F4EF] overflow-hidden"
    >
      <div
        data-pencil-name="Top Bar"
        class="box-border w-full h-[88px] shrink-0 flex flex-row gap-0 p-[0px_64px] justify-between items-center"
      >
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-[13px] text-[#787774] hover:text-[#14140F] transition-colors text-decoration-none">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
            </svg>
            <span>Kembali ke dashboard</span>
        </a>
      </div>

      <div
        data-pencil-name="Loading Stage"
        class="box-border w-full [flex:1_1_0] flex flex-col gap-0 justify-center items-center"
      >
        <div
          data-pencil-name="Loading Stack"
          class="box-border w-[760px] h-[434px] shrink-0 relative"
        >
          <!-- Status Row (AI SEDANG MENCARI) -->
          <div
            data-pencil-name="Status Row"
            class="box-border w-fit h-fit absolute left-0 top-0 flex flex-row gap-[10px] justify-start items-center [z-index:0]"
          >
            <div
              id="statusPulseDot"
              data-pencil-name="Pulse"
              class="box-border w-[8px] shrink-0 h-[8px] flex flex-row gap-0 justify-start items-start bg-[#2C6B52] rounded-[999px] animate-pulse-dot"
            ></div>
            <div
              id="statusLabel"
              data-pencil-name="Status Label"
              class="text-[10px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.8px] text-left [white-space:nowrap] uppercase"
            >
              AI SEDANG MENCARI
            </div>
          </div>

          <div
            data-pencil-name="Gap 1"
            class="box-border w-[1px] h-[20px] absolute left-0 top-[13px] flex flex-row gap-0 justify-start items-start [z-index:1]"
          ></div>

          <!-- Query Line -->
          <div
            id="queryLine"
            data-pencil-name="Query Line"
            class="text-[52px]/[58px] box-border w-[760px] absolute left-0 top-[33px] text-[#14140F] font-['Newsreader',Georgia,serif] font-normal text-left [z-index:2] truncate"
          >
            {{ $initialSession['prompt'] ?? 'harga beras naik' }}
          </div>

          <div
            data-pencil-name="Gap 2"
            class="box-border w-[1px] h-[10px] absolute left-0 top-[91px] flex flex-row gap-0 justify-start items-start [z-index:3]"
          ></div>

          <!-- Query Meta (e.g. 12 detik berjalan · 42 dari 128 penerbit dibaca) -->
          <div
            id="queryMeta"
            data-pencil-name="Query Meta"
            class="text-[14px]/[normal] box-border absolute left-0 top-[101px] text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap] [z-index:4]"
          >
            0 detik berjalan · Mempersiapkan pencarian berita
          </div>

          <div
            data-pencil-name="Gap 3"
            class="box-border w-[1px] h-[28px] absolute left-0 top-[118px] flex flex-row gap-0 justify-start items-start [z-index:5]"
          ></div>

          <!-- Progress Track -->
          <div
            data-pencil-name="Progress Track"
            class="box-border w-[760px] h-[6px] absolute left-0 top-[146px] flex flex-row gap-0 justify-start items-start bg-[#EDEAE0] rounded-[999px] [z-index:6] overflow-hidden"
          >
            <div
              id="progressFill"
              data-pencil-name="Progress Fill"
              class="box-border h-[6px] absolute left-0 top-0 flex flex-row gap-0 justify-start items-start bg-[#1D4B3C] rounded-[999px] [z-index:0] transition-all duration-500 ease-out"
              style="width: 5%;"
            ></div>
          </div>

          <div
            data-pencil-name="Gap 4"
            class="box-border w-[1px] h-[30px] absolute left-0 top-[152px] flex flex-row gap-0 justify-start items-start [z-index:7]"
          ></div>

          <!-- Steps -->
          <div
            data-pencil-name="Steps"
            class="box-border w-[760px] h-fit absolute left-0 top-[182px] flex flex-col gap-0 justify-start items-start [z-index:8]"
          >
            <!-- Step 1: Menelusuri penerbit -->
            <div
              id="stepRow1"
              data-pencil-name="Step Menelusuri penerbit"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-[14px] p-[13px_0px] justify-start items-center"
            >
              <div
                id="stepDot1"
                data-pencil-name="Dot Menelusuri penerbit"
                class="box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#E5EFE7] rounded-[999px] transition-colors"
              >
                <div data-pencil-name="Active Dot" class="box-border w-[7px] shrink-0 h-[7px] bg-[#1D4B3C] rounded-[50%] animate-pulse-dot"></div>
              </div>
              <div
                data-pencil-name="Step Text"
                class="box-border [flex:1_1_0] h-fit flex flex-col gap-[2px] justify-start items-start"
              >
                <div
                  id="stepLabel1"
                  data-pencil-name="Step Label"
                  class="text-[14px]/[normal] box-border text-[#14140F] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
                >
                  Menelusuri penerbit
                </div>
                <div
                  id="stepMeta1"
                  data-pencil-name="Step Meta"
                  class="text-[12px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]"
                >
                  Menghubungkan ke penerbit berita...
                </div>
              </div>
              <div
                id="stepBadge1"
                data-pencil-name="Live Tag"
                class="box-border w-fit shrink-0 h-fit flex flex-row gap-0 p-[4px_9px] justify-start items-start bg-[#E5EFE7] rounded-[999px]"
              >
                <div
                  id="stepBadgeText1"
                  data-pencil-name="Live Tag Text"
                  class="text-[9px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.2px] text-left [white-space:nowrap]"
                >
                  BERJALAN
                </div>
              </div>
            </div>

            <!-- Step 2: Membaca artikel -->
            <div
              id="stepRow2"
              data-pencil-name="Step Membaca artikel"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-[14px] p-[13px_0px] justify-start items-center"
            >
              <div
                id="stepDot2"
                data-pencil-name="Dot Membaca artikel"
                class="box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#EDEAE0] rounded-[999px] transition-colors"
              ></div>
              <div
                data-pencil-name="Step Text"
                class="box-border [flex:1_1_0] h-fit flex flex-col gap-[2px] justify-start items-start"
              >
                <div
                  id="stepLabel2"
                  data-pencil-name="Step Label"
                  class="text-[14px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
                >
                  Membaca artikel
                </div>
                <div
                  id="stepMeta2"
                  data-pencil-name="Step Meta"
                  class="text-[12px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]"
                >
                  Menunggu penelusuran selesai
                </div>
              </div>
              <div
                id="stepBadge2"
                data-pencil-name="Live Tag"
                class="hidden box-border w-fit shrink-0 h-fit flex flex-row gap-0 p-[4px_9px] justify-start items-start bg-[#E5EFE7] rounded-[999px]"
              >
                <div
                  id="stepBadgeText2"
                  data-pencil-name="Live Tag Text"
                  class="text-[9px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.2px] text-left [white-space:nowrap]"
                >
                  BERJALAN
                </div>
              </div>
            </div>

            <!-- Step 3: Menyusun ringkasan -->
            <div
              id="stepRow3"
              data-pencil-name="Step Menyusun ringkasan"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-[14px] p-[13px_0px] justify-start items-center"
            >
              <div
                id="stepDot3"
                data-pencil-name="Dot Menyusun ringkasan"
                class="box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#EDEAE0] rounded-[999px] transition-colors"
              ></div>
              <div
                data-pencil-name="Step Text"
                class="box-border [flex:1_1_0] h-fit flex flex-col gap-[2px] justify-start items-start"
              >
                <div
                  id="stepLabel3"
                  data-pencil-name="Step Label"
                  class="text-[14px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
                >
                  Menyusun ringkasan
                </div>
                <div
                  id="stepMeta3"
                  data-pencil-name="Step Meta"
                  class="text-[12px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]"
                >
                  Menunggu artikel selesai
                </div>
              </div>
              <div
                id="stepBadge3"
                data-pencil-name="Live Tag"
                class="hidden box-border w-fit shrink-0 h-fit flex flex-row gap-0 p-[4px_9px] justify-start items-start bg-[#E5EFE7] rounded-[999px]"
              >
                <div
                  id="stepBadgeText3"
                  data-pencil-name="Live Tag Text"
                  class="text-[9px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.2px] text-left [white-space:nowrap]"
                >
                  BERJALAN
                </div>
              </div>
            </div>
          </div>

          <div
            data-pencil-name="Gap 5"
            class="box-border w-[1px] h-[26px] absolute left-0 top-[362px] flex flex-row gap-0 justify-start items-start [z-index:9]"
          ></div>

          <!-- Cancel & Retry Row -->
          <div
            data-pencil-name="Cancel Row"
            class="box-border w-fit h-fit absolute left-0 top-[388px] flex flex-row gap-[14px] justify-start items-center [z-index:10]"
          >
            <!-- Cancel Button -->
            <button
              type="button"
              id="cancelSearchBtn"
              data-pencil-name="Cancel Button"
              class="box-border w-fit shrink-0 h-[46px] flex flex-row gap-[9px] p-[0px_18px] justify-start items-center bg-[#FFFFFF] [outline:1.5px_solid_#E1DCD0] [outline-offset:-0.75px] rounded-[12px] cursor-pointer hover:bg-[#FAF9F5] active:scale-[0.98] transition-all"
            >
              <svg
                data-pencil-name="Cancel Icon"
                viewBox="0 0 16 16"
                preserveAspectRatio="xMidYMid meet"
                data-icon-name="x"
                data-icon-set="lucide"
                xmlns="http://www.w3.org/2000/svg"
                class="box-border w-[16px] shrink-0 h-[16px] overflow-visible"
              >
                <path
                  d="M3.824 3.359q-.289 .098-.433 .352-.047 .098-.047 .289 0 .191 .047 .289 .05 .094 1.855 1.902l1.809 1.809-1.809 1.809q-1.805 1.808-1.855 1.906-.047 .094-.047 .269 0 .176 .039 .274 .043 .094 .152 .207 .113 .109 .207 .152 .098 .039 .274 .039 .175 0 .269-.047 .098-.05 1.906-1.855l1.809-1.809 1.809 1.809q1.808 1.805 1.902 1.855 .098 .047 .273 .047 .176 0 .27-.039 .098-.043 .207-.152 .113-.113 .152-.207 .043-.098 .043-.274 0-.175-.05-.269-.047-.098-1.852-1.906l-1.809-1.809 1.809-1.809q1.805-1.808 1.852-1.902 .05-.098 .05-.289 0-.191-.047-.289-.113-.191-.32-.305-.066-.031-.113-.047-.047-.015-.176-.015-.129 0-.176 .015-.047 .016-.129 .047-.113 .067-1.902 1.86l-1.793 1.789-3.23-3.215q-.356-.336-.481-.434-.098-.062-.226-.062h-.032q-.16 0-.207 .015z"
                  fill="#a8502a"
                />
              </svg>
              <div
                id="cancelButtonLabel"
                data-pencil-name="Cancel Label"
                class="text-[13.5px]/[normal] box-border text-[#14140F] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
              >
                Batalkan pencarian
              </div>
            </button>

            <!-- Retry Button (Shown only when failed) -->
            <button
              type="button"
              id="retrySearchBtn"
              class="hidden box-border w-fit shrink-0 h-[46px] flex flex-row gap-[9px] p-[0px_18px] justify-start items-center bg-[#2C6B52] text-white rounded-[12px] cursor-pointer hover:bg-[#1D4B3C] active:scale-[0.98] transition-all font-['Inter',system-ui,sans-serif] text-[13.5px] font-medium"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 2v6h-6"></path>
                <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                <path d="M3 22v-6h6"></path>
                <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
              </svg>
              <span>Coba lagi</span>
            </button>
          </div>
        </div>
      </div>

      <div
        data-pencil-name="Footer"
        class="box-border w-full h-[72px] shrink-0 flex flex-row gap-0 p-[0px_64px] justify-between items-center"
      ></div>
    </div>

    <!-- Checkmark SVG Template for Completed Steps -->
    <template id="checkIconTemplate">
      <svg
        data-pencil-name="Check"
        viewBox="0 0 12 12"
        preserveAspectRatio="xMidYMid meet"
        data-icon-name="check"
        data-icon-set="lucide"
        xmlns="http://www.w3.org/2000/svg"
        class="box-border w-[12px] shrink-0 h-[12px] overflow-visible"
      >
        <path
          d="M9.841 2.531q-.062 .012-.111 .05-.047 .035-2.637 2.628l-2.593 2.578-1.104-1.104q-1.105-1.102-1.184-1.137-.076-.038-.191-.038-.114 0-.199 .032-.082 .029-.161 .097-.076 .064-.114 .146-.024 .062-.029 .097-.006 .035-.006 .12v.035q-.012 .097 .035 .17 .061 .094 .313 .346 .167 .181 .829 .841l1.284 1.271q.24 .229 .334 .29 .061 .047 .158 .035l.082 .012q.061 0 .12-.023 .073-.062 .275-.247 .205-.187 .686-.653l1.957-1.957q1.787-1.799 2.314-2.326 .528-.53 .554-.589 .035-.073 .035-.205 0-.085-.006-.12-.005-.035-.029-.097-.038-.07-.117-.14-.076-.074-.155-.103-.076-.032-.179-.032-.103 0-.161 .023z"
          fill="#f6f4ef"
        />
      </svg>
    </template>

    <!-- Error Icon Template for Failed Steps -->
    <template id="errorIconTemplate">
      <svg class="box-border w-[10px] shrink-0 h-[10px] text-[#B91C1C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </template>

    <!-- Active Dot Template -->
    <template id="activeDotTemplate">
      <div data-pencil-name="Active Dot" class="box-border w-[7px] shrink-0 h-[7px] bg-[#1D4B3C] rounded-[50%] animate-pulse-dot"></div>
    </template>

    <script>
        window.__INITIAL_SESSION__ = @json($initialSession);

        document.addEventListener('DOMContentLoaded', function () {
            const searchStageSection = document.getElementById('searchStageSection');
            const loadingStageSection = document.getElementById('loadingStageSection');
            const form = document.getElementById('summarizeForm');
            const queryInput = document.getElementById('queryInput');
            const submitBtn = document.getElementById('submitBtn');
            const arrowIcon = document.getElementById('arrowIcon');
            const loadingSpinner = document.getElementById('loadingSpinner');

            const queryLine = document.getElementById('queryLine');
            const queryMeta = document.getElementById('queryMeta');
            const statusLabel = document.getElementById('statusLabel');
            const statusPulseDot = document.getElementById('statusPulseDot');
            const progressFill = document.getElementById('progressFill');

            const cancelSearchBtn = document.getElementById('cancelSearchBtn');
            const cancelButtonLabel = document.getElementById('cancelButtonLabel');
            const retrySearchBtn = document.getElementById('retrySearchBtn');

            const checkIconTemplate = document.getElementById('checkIconTemplate');
            const errorIconTemplate = document.getElementById('errorIconTemplate');
            const activeDotTemplate = document.getElementById('activeDotTemplate');

            let currentSessionId = null;
            let currentQueryText = '';
            let timerInterval = null;
            let secondsElapsed = 0;
            let eventSource = null;
            let pollTimer = null;
            let latestMetaSummary = 'Mempersiapkan pencarian...';

            function formatTimeMeta() {
                queryMeta.textContent = `${secondsElapsed} detik berjalan · ${latestMetaSummary}`;
            }

            function startTimer() {
                stopTimer();
                secondsElapsed = 0;
                formatTimeMeta();
                timerInterval = setInterval(function () {
                    secondsElapsed++;
                    formatTimeMeta();
                }, 1000);
            }

            function stopTimer() {
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
            }

            function stopRealtime() {
                if (eventSource) {
                    eventSource.close();
                    eventSource = null;
                }
                if (pollTimer) {
                    clearTimeout(pollTimer);
                    pollTimer = null;
                }
            }

            // Update individual UI step state
            function updateStepElement(stepIndex, state, metaText, badgeText = null) {
                const dot = document.getElementById(`stepDot${stepIndex}`);
                const label = document.getElementById(`stepLabel${stepIndex}`);
                const meta = document.getElementById(`stepMeta${stepIndex}`);
                const badge = document.getElementById(`stepBadge${stepIndex}`);
                const badgeTextEl = document.getElementById(`stepBadgeText${stepIndex}`);

                meta.textContent = metaText;

                // Reset dot content
                dot.innerHTML = '';

                if (state === 'completed') {
                    dot.className = 'box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#1D4B3C] rounded-[999px] transition-colors';
                    dot.appendChild(checkIconTemplate.content.cloneNode(true));
                    label.className = "text-[14px]/[normal] box-border text-[#14140F] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]";
                    meta.className = "text-[12px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]";
                    badge.classList.add('hidden');
                } else if (state === 'active') {
                    dot.className = 'box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#E5EFE7] rounded-[999px] transition-colors';
                    dot.appendChild(activeDotTemplate.content.cloneNode(true));
                    label.className = "text-[14px]/[normal] box-border text-[#14140F] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]";
                    meta.className = "text-[12px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]";
                    badge.className = 'box-border w-fit shrink-0 h-fit flex flex-row gap-0 p-[4px_9px] justify-start items-start bg-[#E5EFE7] rounded-[999px]';
                    badgeTextEl.className = "text-[9px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.2px] text-left [white-space:nowrap]";
                    badgeTextEl.textContent = badgeText || 'BERJALAN';
                    badge.classList.remove('hidden');
                } else if (state === 'failed') {
                    dot.className = 'box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#FEE2E2] rounded-[999px] transition-colors';
                    dot.appendChild(errorIconTemplate.content.cloneNode(true));
                    label.className = "text-[14px]/[normal] box-border text-[#B91C1C] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]";
                    meta.className = "text-[12px]/[normal] box-border text-[#B91C1C] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]";
                    badge.className = 'box-border w-fit shrink-0 h-fit flex flex-row gap-0 p-[4px_9px] justify-start items-start bg-[#FEE2E2] rounded-[999px]';
                    badgeTextEl.className = "text-[9px]/[normal] box-border text-[#B91C1C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.2px] text-left [white-space:nowrap]";
                    badgeTextEl.textContent = 'GAGAL';
                    badge.classList.remove('hidden');
                } else {
                    // Pending
                    dot.className = 'box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#EDEAE0] rounded-[999px] transition-colors';
                    label.className = "text-[14px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-medium text-left [white-space:nowrap]";
                    meta.className = "text-[12px]/[normal] box-border text-[#8C887B] font-['Inter',system-ui,sans-serif] font-normal text-left [white-space:nowrap]";
                    badge.classList.add('hidden');
                }
            }

            // Apply payload data to the UI according to real backend stages
            function applySessionPayload(data) {
                if (!data) return;

                const stage = data.current_stage || 'initializing';
                const status = data.status || 'processing';
                const progress = data.progress !== null && data.progress !== undefined ? data.progress : 10;
                const meta = data.meta || {};
                const error = data.error || data.error_message;

                // Update Progress Bar
                const clampedProgress = Math.min(100, Math.max(5, progress));
                progressFill.style.width = clampedProgress + '%';

                // Categorize backend stage into 3 visual steps
                const isStep1Stage = ['initializing', 'prompt_enhancement', 'query_generation', 'news_searching'].includes(stage);
                const isStep2Stage = ['content_extraction', 'news_filtering'].includes(stage);
                const isStep3Stage = ['news_analysis', 'summarization', 'saving_results', 'completed'].includes(stage);

                // ==========================================
                // 1. Process Step 1: Menelusuri penerbit
                // ==========================================
                if (status === 'completed' || isStep2Stage || isStep3Stage) {
                    const count = meta.sources_count || meta.articles_count || 5;
                    updateStepElement(1, 'completed', `Selesai · ${count} sumber`);
                } else if (status === 'failed' && isStep1Stage) {
                    updateStepElement(1, 'failed', error || 'Gagal mencari berita');
                } else if (isStep1Stage) {
                    let text = data.description || 'Menelusuri penerbit...';
                    if (stage === 'prompt_enhancement') text = 'Mengoptimalkan instruksi pencarian...';
                    if (stage === 'query_generation') text = 'Menghasilkan kata kunci pencarian...';
                    if (stage === 'news_searching') text = 'Mencari dari berbagai penerbit...';
                    updateStepElement(1, 'active', text, 'BERJALAN');
                } else {
                    updateStepElement(1, 'pending', 'Menunggu');
                }

                // ==========================================
                // 2. Process Step 2: Membaca artikel
                // ==========================================
                if (status === 'completed' || isStep3Stage) {
                    const artCount = meta.articles_count || meta.extracted_count || 3;
                    if (meta.failed_count && meta.failed_count > 0) {
                        updateStepElement(2, 'completed', `Selesai · ${artCount} artikel (${meta.failed_count} sumber dilewati)`);
                    } else {
                        updateStepElement(2, 'completed', `Selesai · ${artCount} artikel`);
                    }
                } else if (status === 'failed' && isStep2Stage) {
                    updateStepElement(2, 'failed', error || 'Gagal membaca artikel');
                } else if (isStep2Stage) {
                    let text = data.description || 'Membaca artikel...';
                    if (meta.sources_count) {
                        const ext = meta.extracted_count || 0;
                        text = `${ext} dari ${meta.sources_count} artikel`;
                    }
                    updateStepElement(2, 'active', text, 'BERJALAN');
                } else {
                    updateStepElement(2, 'pending', 'Menunggu artikel selesai');
                }

                // ==========================================
                // 3. Process Step 3: Menyusun ringkasan
                // ==========================================
                if (status === 'completed') {
                    updateStepElement(3, 'completed', 'Selesai · Rangkuman siap');
                } else if (status === 'failed' && isStep3Stage) {
                    updateStepElement(3, 'failed', error || 'Gagal menyusun ringkasan');
                } else if (isStep3Stage && stage !== 'completed') {
                    let text = data.description || 'AI sedang menyusun ringkasan...';
                    if (stage === 'news_analysis') text = 'AI menganalisis & membandingkan berita...';
                    if (stage === 'summarization') text = 'AI menyusun rangkuman utama...';
                    if (stage === 'saving_results') text = 'Menyimpan hasil ke database...';
                    updateStepElement(3, 'active', text, 'BERJALAN');
                } else {
                    updateStepElement(3, 'pending', 'Menunggu artikel selesai');
                }

                // ==========================================
                // Overall Status Row & Query Meta
                // ==========================================
                if (status === 'completed') {
                    statusPulseDot.className = 'box-border w-[8px] shrink-0 h-[8px] flex flex-row gap-0 justify-start items-start bg-[#2C6B52] rounded-[999px]';
                    statusLabel.className = "text-[10px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.8px] text-left [white-space:nowrap] uppercase";
                    statusLabel.textContent = 'RANGKUMAN SELESAI';
                    latestMetaSummary = 'Rangkuman berita berhasil dibuat';
                    formatTimeMeta();

                    stopRealtime();
                    stopTimer();

                    // Smooth redirect to summary result
                    setTimeout(function () {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else if (data.summary_id) {
                            window.location.href = `/summaries/${data.summary_id}`;
                        }
                    }, 800);

                } else if (status === 'failed') {
                    statusPulseDot.className = 'box-border w-[8px] shrink-0 h-[8px] flex flex-row gap-0 justify-start items-start bg-[#B91C1C] rounded-[999px]';
                    statusLabel.className = "text-[10px]/[normal] box-border text-[#B91C1C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.8px] text-left [white-space:nowrap] uppercase";
                    statusLabel.textContent = 'PROSES TERHENTI';
                    latestMetaSummary = error || 'Terjadi kendala pada proses pemrosesan';
                    formatTimeMeta();

                    stopRealtime();
                    stopTimer();

                    cancelButtonLabel.textContent = 'Kembali ke pencarian';
                    retrySearchBtn.classList.remove('hidden');

                } else {
                    // Processing / Active
                    statusPulseDot.className = 'box-border w-[8px] shrink-0 h-[8px] flex flex-row gap-0 justify-start items-start bg-[#2C6B52] rounded-[999px] animate-pulse-dot';
                    statusLabel.className = "text-[10px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.8px] text-left [white-space:nowrap] uppercase";

                    if (isStep1Stage) {
                        statusLabel.textContent = 'AI SEDANG MENCARI';
                        latestMetaSummary = meta.sources_count ? `${meta.sources_count} penerbit ditemukan` : 'Menelusuri penerbit berita...';
                    } else if (isStep2Stage) {
                        statusLabel.textContent = 'AI MEMBACA ARTIKEL';
                        const ext = meta.extracted_count || 0;
                        const tot = meta.sources_count || 0;
                        latestMetaSummary = tot > 0 ? `${ext} dari ${tot} penerbit dibaca` : 'Mengekstrak isi berita...';
                    } else {
                        statusLabel.textContent = 'AI MENYUSUN RINGKASAN';
                        latestMetaSummary = data.description || 'Menganalisis & merangkum berita...';
                    }

                    formatTimeMeta();
                    retrySearchBtn.classList.add('hidden');
                    cancelButtonLabel.textContent = 'Batalkan pencarian';
                }
            }

            // Connect to real-time status updates via SSE + fallback polling
            function connectToStatusStream(sessionId) {
                stopRealtime();

                const streamUrl = `/summaries/status/${sessionId}/stream`;

                if (window.EventSource) {
                    try {
                        eventSource = new EventSource(streamUrl);

                        eventSource.onmessage = function (event) {
                            try {
                                const data = JSON.parse(event.data);
                                applySessionPayload(data);
                            } catch (e) {
                                console.error('Error parsing SSE event:', e);
                            }
                        };

                        eventSource.onerror = function () {
                            // On SSE error or disconnection, close it and switch to fallback polling
                            if (eventSource) {
                                eventSource.close();
                                eventSource = null;
                            }
                            scheduleFallbackPoll(sessionId, 1200);
                        };
                    } catch (e) {
                        scheduleFallbackPoll(sessionId, 1000);
                    }
                } else {
                    scheduleFallbackPoll(sessionId, 1000);
                }
            }

            function scheduleFallbackPoll(sessionId, delayMs = 1500) {
                if (pollTimer) clearTimeout(pollTimer);

                pollTimer = setTimeout(function () {
                    fetch(`/summaries/status/${sessionId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        applySessionPayload(data);
                        if (data.status === 'processing' || data.status === 'pending') {
                            scheduleFallbackPoll(sessionId, 1500);
                        }
                    })
                    .catch(err => {
                        console.warn('Status poll warning:', err);
                        scheduleFallbackPoll(sessionId, 2500);
                    });
                }, delayMs);
            }

            // Switch view from Search Form to Loading Stage
            function showLoadingStage(queryText, sessionId = null) {
                currentQueryText = queryText;
                currentSessionId = sessionId;

                queryLine.textContent = queryText;
                searchStageSection.classList.add('hidden');
                searchStageSection.classList.remove('flex');
                loadingStageSection.classList.remove('hidden');
                loadingStageSection.classList.add('flex');

                retrySearchBtn.classList.add('hidden');
                cancelButtonLabel.textContent = 'Batalkan pencarian';

                startTimer();

                if (sessionId) {
                    connectToStatusStream(sessionId);
                }
            }

            // Switch view from Loading Stage back to Search Form
            function showSearchStage() {
                stopRealtime();
                stopTimer();

                currentSessionId = null;

                loadingStageSection.classList.add('hidden');
                loadingStageSection.classList.remove('flex');
                searchStageSection.classList.remove('hidden');
                searchStageSection.classList.add('flex');

                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                arrowIcon.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');

                // Clear session query param in URL without reload
                const url = new URL(window.location.href);
                url.searchParams.delete('session_id');
                window.history.replaceState({}, '', url.pathname);

                queryInput.focus();
            }

            // Submit form handler
            form.addEventListener('submit', function (e) {
                const queryVal = queryInput.value.trim();
                if (!queryVal) return;

                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.85';
                arrowIcon.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');

                // Switch to loading UI immediately
                showLoadingStage(queryVal);

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ query: queryVal })
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok && response.status !== 202) {
                        throw new Error(data.message || 'Gagal memulai proses pencarian');
                    }
                    return data;
                })
                .then(data => {
                    const sessionId = data.search_session_id || data.session_id;
                    currentSessionId = sessionId;

                    // Update URL with session_id
                    const url = new URL(window.location.href);
                    url.searchParams.set('session_id', sessionId);
                    window.history.replaceState({}, '', url.toString());

                    applySessionPayload(data);
                    connectToStatusStream(sessionId);
                })
                .catch(err => {
                    console.error('Submission error:', err);
                    applySessionPayload({
                        status: 'failed',
                        current_stage: 'initializing',
                        error: err.message || 'Terjadi kegagalan koneksi ke server.'
                    });
                });
            });

            // Cancel Button Handler
            cancelSearchBtn.addEventListener('click', function () {
                if (currentSessionId) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(`/summaries/status/${currentSessionId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    }).catch(() => {});
                }

                showSearchStage();
            });

            // Retry Button Handler
            retrySearchBtn.addEventListener('click', function () {
                if (currentQueryText) {
                    queryInput.value = currentQueryText;
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                } else {
                    showSearchStage();
                }
            });

            // If initial session was supplied via server rendering
            if (window.__INITIAL_SESSION__) {
                showLoadingStage(window.__INITIAL_SESSION__.prompt || 'Pencarian Berita', window.__INITIAL_SESSION__.session_id);
                applySessionPayload(window.__INITIAL_SESSION__);
            }
        });
    </script>
</body>
</html>
