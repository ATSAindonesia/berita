<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $summary->title }} — Rangkuman Berita</title>

    <script>
      tailwind = { config: { corePlugins: { preflight: false } } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono%3Awght%40400;500;600&family=Inter%3Awght%40100..900&family=Newsreader%3Awght%40200..800&display=swap"
      rel="stylesheet"
    />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
      *, ::before, ::after {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        background-color: #F6F4EF;
        color: #14140F;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }

      .font-mono-custom {
        font-family: 'IBM Plex Mono', monospace, system-ui, sans-serif;
      }

      .font-serif-custom {
        font-family: 'Newsreader', Georgia, Cambria, 'Times New Roman', serif;
      }

      .font-sans-custom {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      }

      /* References Modal */
      .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(20, 20, 15, 0.45);
        backdrop-filter: blur(3px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        z-index: 60;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
      }

      .modal-backdrop.active {
        opacity: 1;
        pointer-events: auto;
      }

      .modal-box {
        background: #ffffff;
        border: 1px solid #E1DCD0;
        border-radius: 20px;
        width: 100%;
        max-width: 620px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.12);
        transform: scale(0.96);
        transition: transform 0.2s ease;
      }

      .modal-backdrop.active .modal-box {
        transform: scale(1);
      }

      .ref-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border: 1px solid #EDE9DF;
        border-radius: 12px;
        margin-bottom: 8px;
        text-decoration: none;
        color: inherit;
        background-color: #F6F4EF;
        transition: all 0.15s ease;
      }

      .ref-item:hover {
        background-color: #E5EFE7;
        border-color: #1D4B3C;
        transform: translateY(-1px);
      }

      /* Chat exchange style */
      .chat-exchange {
        background-color: #F6F4EF;
        border: 1px solid #EDE9DF;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.6;
      }

      /* Custom scrollbars */
      ::-webkit-scrollbar {
        width: 6px;
      }
      ::-webkit-scrollbar-track {
        background: transparent;
      }
      ::-webkit-scrollbar-thumb {
        background: #E1DCD0;
        border-radius: 9999px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: #8C887B;
      }
    </style>
  </head>
  <body style="background-color: #F6F4EF">

    @php
        $items = [];

        // 1. Poin Utama / Ringkasan
        if (!empty($summary->summary)) {
            $items[] = [
                'tag' => 'POIN UTAMA',
                'title' => 'Ringkasan dan Perkembangan Terkini',
                'content' => $summary->summary,
                'is_list' => false,
            ];
        }

        // 2. Poin-Poin Penting
        if (!empty($summary->key_points) && is_array($summary->key_points)) {
            $items[] = [
                'tag' => 'POIN PENTING',
                'title' => 'Fakta Utama dan Temuan Lapangan',
                'content' => $summary->key_points,
                'is_list' => true,
            ];
        }

        // 3. Kronologi Peristiwa
        if (!empty($summary->chronology)) {
            $items[] = [
                'tag' => 'KRONOLOGI',
                'title' => 'Linimasa Perkembangan Kejadian',
                'content' => $summary->chronology,
                'is_list' => false,
            ];
        }

        // 4. Perbandingan Informasi Antar Sumber
        if (!empty($summary->source_comparison)) {
            $items[] = [
                'tag' => 'SUMBER',
                'title' => 'Respons Pemerintah dan Kebijakan',
                'content' => $summary->source_comparison,
                'is_list' => false,
            ];
        }

        // 5. Fakta Belum Terkonfirmasi / Dampak
        if (!empty($summary->unconfirmed_facts)) {
            $items[] = [
                'tag' => 'DAMPAK',
                'title' => 'Pengaruh ke Konsumen dan Isu Terkait',
                'content' => $summary->unconfirmed_facts,
                'is_list' => false,
            ];
        }

        // 6. Kesimpulan / Outlook
        if (!empty($summary->conclusion)) {
            $items[] = [
                'tag' => 'OUTLOOK',
                'title' => 'Proyeksi Pekan Depan dan Catatan Akhir',
                'content' => $summary->conclusion,
                'is_list' => false,
            ];
        }

        $totalPoints = count($items) > 0 ? count($items) : 4;
        $formattedDate = strtoupper(
            $summary->published_at
                ? ($summary->published_at->translatedFormat('d F Y') ?: $summary->published_at->format('d F Y'))
                : ($summary->created_at ? ($summary->created_at->translatedFormat('d F Y') ?: $summary->created_at->format('d F Y')) : date('d F Y'))
        );
    @endphp

    <!-- Top Navigation Bar -->
    <div class="box-border w-full max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-[64px] pt-6 flex flex-row justify-between items-center">
      <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-[11px] text-[#8C887B] hover:text-[#14140F] font-mono-custom tracking-[1.4px] uppercase transition no-underline">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        <span>Kembali ke dashboard</span>
      </a>

      <!-- Quick Toggle for Sidebar if collapsed -->
      <button
        type="button"
        id="quickOpenSidebarBtn"
        onclick="toggleSidebar(true)"
        class="hidden box-border h-[36px] flex-row gap-[8px] px-3.5 justify-center items-center bg-[#FFFFFF] hover:bg-[#E5EFE7] [outline:1px_solid_#E1DCD0] [outline-offset:-0.5px] rounded-[10px] text-[13px] text-[#1D4B3C] font-sans-custom font-semibold cursor-pointer transition"
      >
        <span>+ Buka Tambah Prompt</span>
      </button>
    </div>

    <!-- Main Container Matching read.md -->
    <div
      data-pencil-name="04 · Rangkuman"
      class="box-border w-full max-w-[1440px] mx-auto min-h-screen flex flex-col gap-0 justify-start items-start bg-[#F6F4EF]"
    >
      <!-- Title Area -->
      <div
        data-pencil-name="Title Area"
        class="box-border w-full h-fit shrink-0 flex flex-col gap-0 p-[28px_20px_20px_20px] sm:p-[40px_64px_24px_64px] justify-start items-center"
      >
        <div
          data-pencil-name="Meta"
          class="text-[10px]/[normal] box-border text-[#8C887B] font-mono-custom font-normal tracking-[1.6px] text-center [white-space:nowrap]"
        >
          RANGKUMAN SELESAI · {{ $formattedDate }}
        </div>
        <div
          data-pencil-name="Gap Title"
          class="box-border w-[1px] h-[12px] shrink-0 flex flex-row gap-0 justify-start items-start"
        ></div>
        <h1
          data-pencil-name="Judul Rangkuman"
          class="text-[32px]/[38px] sm:text-[42px]/[48px] box-border w-full max-w-[760px] text-[#14140F] font-serif-custom font-normal text-center m-0"
        >
          {{ $summary->title }}
        </h1>
        <div
          data-pencil-name="Gap Sub"
          class="box-border w-[1px] h-[10px] shrink-0 flex flex-row gap-0 justify-start items-start"
        ></div>
        <p
          data-pencil-name="Subjudul"
          class="text-[14px]/[21px] box-border w-full max-w-[620px] text-[#5B584E] font-sans-custom font-normal text-center m-0"
        >
          Ringkasan dari {{ $summary->references->count() }} artikel pilihan yang sudah diverifikasi dan disusun menjadi poin utama.
        </p>
      </div>

      <!-- Body Row -->
      <div
        data-pencil-name="Body"
        class="box-border w-full flex-1 flex flex-col lg:flex-row gap-[24px] px-4 sm:px-8 lg:p-[0px_0px_32px_64px] justify-start items-start"
      >
        <!-- Kertas Rangkuman -->
        <div
          data-pencil-name="Kertas Rangkuman"
          class="box-border flex-1 w-full min-h-[600px] flex flex-col gap-[18px] p-6 sm:p-[32px] justify-start items-start bg-[#FFFFFF] [outline:1px_solid_#E1DCD0] [outline-offset:-0.5px] rounded-[20px] overflow-hidden shadow-sm"
        >
          <!-- Paper Header -->
          <div
            data-pencil-name="Paper Header"
            class="box-border w-full h-fit shrink-0 flex flex-row gap-0 justify-between items-center"
          >
            <div
              data-pencil-name="Paper Label"
              class="text-[10px]/[normal] box-border text-[#8C887B] font-mono-custom font-normal tracking-[1.4px] text-left [white-space:nowrap]"
            >
              RANGKUMAN · {{ $totalPoints }} POIN
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                onclick="openReferencesModal()"
                data-pencil-name="Tombol Lihat Referensi"
                class="box-border w-fit shrink-0 h-[40px] flex flex-row gap-[8px] p-[0px_14px] justify-center items-center bg-[#E5EFE7] hover:bg-[#d8e6db] rounded-[10px] cursor-pointer border-0 transition"
                aria-label="Lihat referensi artikel"
              >
                <svg
                  data-pencil-name="Ikon Referensi"
                  viewBox="0 0 15 15"
                  preserveAspectRatio="xMidYMid meet"
                  data-icon-name="library"
                  data-icon-set="lucide"
                  xmlns="http://www.w3.org/2000/svg"
                  class="box-border w-[15px] shrink-0 h-[15px] overflow-visible"
                >
                  <path
                    d="M2.355 1.89q-.15 .044-.279 .164-.128 .121-.172 .271-.029 .107-.029 5.175 0 5.068 .029 5.175 .063 .227 .249 .347 .187 .118 .403 .096 .22-.022 .385-.187 .135-.136 .164-.315 .03-.121 .03-5.116 0-4.995-.03-5.116-.029-.179-.164-.315-.107-.121-.271-.165-.165-.044-.315-.014zM7.335 3.149q-.271 .092-.406 .33l-.044 .092v9.045l.029 .103q.092 .227 .315 .347 .106 .044 .271 .044 .165 0 .271-.044 .223-.12 .315-.347l.029-.103v-9.045l-.044-.092q-.106-.194-.3-.286-.092-.044-.242-.051-.15-.007-.194 .007zM9.782 3.164q-.107 .048-.206 .139-.095 .088-.135 .172-.037 .081-.044 .125-.007 .044-.007 .157 0 .114 .179 .766 .18 .652 1.08 3.772 1.26 4.409 1.29 4.486 .106 .194 .307 .285 .205 .088 .414 .03 .209-.063 .322-.202 .114-.143 .143-.355 0-.088-.172-.703-.172-.615-1.088-3.827-1.259-4.409-1.3-4.497-.036-.092-.121-.179-.08-.092-.179-.143-.095-.055-.238-.055-.143 0-.245 .029zM4.904 4.395q-.195 .014-.337 .157-.143 .143-.172 .337-.03 .121-.03 3.878 0 3.757 .03 3.849 .029 .194 .164 .315 .213 .209 .495 .187 .285-.022 .45-.264l.029-.029q.048-.059 .063-.209 .014-.179 .029-.857v-6.87l-.044-.103q-.091-.227-.271-.329-.179-.106-.406-.062z"
                    fill="#1d4b3c"
                  />
                </svg>
                <div
                  data-pencil-name="Label Referensi"
                  class="text-[13px]/[normal] box-border text-[#1D4B3C] font-sans-custom font-semibold text-left [white-space:nowrap]"
                >
                  Lihat Referensi · {{ $summary->references->count() }}
                </div>
              </button>
            </div>
          </div>

          <!-- Header Divider -->
          <div
            data-pencil-name="Header Divider"
            class="box-border w-full h-[1px] shrink-0 flex flex-row gap-0 justify-start items-start bg-[#EDE9DF]"
          ></div>

          <!-- Dynamic Berita Items matching read.md -->
          @forelse ($items as $item)
            <div
              data-pencil-name="Berita Item"
              class="box-border w-full h-fit shrink-0 flex flex-col gap-[8px] justify-start items-start"
            >
              <div
                data-pencil-name="Tag"
                class="text-[10px]/[normal] box-border text-[#1D4B3C] font-mono-custom font-normal tracking-[1.4px] text-left [white-space:nowrap]"
              >
                {{ $item['tag'] }}
              </div>
              <div
                data-pencil-name="Judul Item"
                class="text-[22px]/[28px] box-border w-full text-[#14140F] font-serif-custom font-normal text-left"
              >
                {{ $item['title'] }}
              </div>

              @if ($item['is_list'])
                <div class="box-border w-full flex flex-col gap-[8px] mt-1">
                  @foreach ($item['content'] as $point)
                    <div class="flex items-start gap-2.5 text-[14px]/[22px] text-[#5B584E] font-sans-custom font-normal">
                      <span class="w-1.5 h-1.5 rounded-full bg-[#1D4B3C] mt-2 shrink-0"></span>
                      <span>{{ $point }}</span>
                    </div>
                  @endforeach
                </div>
              @else
                <div
                  data-pencil-name="Isi Item"
                  class="text-[14px]/[22px] box-border w-full text-[#5B584E] font-sans-custom font-normal text-left whitespace-pre-line"
                >
                  {{ $item['content'] }}
                </div>
              @endif

              <div
                data-pencil-name="Divider"
                class="box-border w-full h-[1px] shrink-0 flex flex-row gap-0 justify-start items-start bg-[#EDE9DF] mt-2"
              ></div>
            </div>
          @empty
            <div
              data-pencil-name="Berita Item"
              class="box-border w-full h-fit shrink-0 flex flex-col gap-[8px] justify-start items-start"
            >
              <div
                data-pencil-name="Tag"
                class="text-[10px]/[normal] box-border text-[#1D4B3C] font-mono-custom font-normal tracking-[1.4px] text-left [white-space:nowrap]"
              >
                POIN UTAMA
              </div>
              <div
                data-pencil-name="Judul Item"
                class="text-[22px]/[28px] box-border w-full text-[#14140F] font-serif-custom font-normal text-left"
              >
                Ringkasan Berita
              </div>
              <div
                data-pencil-name="Isi Item"
                class="text-[14px]/[22px] box-border w-full text-[#5B584E] font-sans-custom font-normal text-left"
              >
                {{ $summary->summary }}
              </div>
              <div
                data-pencil-name="Divider"
                class="box-border w-full h-[1px] shrink-0 flex flex-row gap-0 justify-start items-start bg-[#EDE9DF]"
              ></div>
            </div>
          @endforelse
        </div>

        <!-- Sidebar Prompt matching read.md -->
        <div
          id="sidebarPrompt"
          data-pencil-name="Sidebar Prompt"
          class="box-border w-full lg:w-[359.5px] shrink-0 flex flex-col gap-[14px] p-[20px_24px_28px_24px] justify-start items-start bg-[#FFFFFF] border-0 lg:[border-width:0px_0px_0px_1px] [border-style:solid] [border-color:#E1DCD0] [margin:0px_0px_0px_-0.5px] rounded-[20px] lg:rounded-[20px_0px_0px_20px] shadow-sm transition-all duration-300"
        >
          <!-- Sidebar Header -->
          <div
            data-pencil-name="Sidebar Header"
            class="box-border w-full h-fit shrink-0 flex flex-row gap-0 justify-between items-center"
          >
            <div
              data-pencil-name="Sidebar Title"
              class="text-[22px]/[normal] box-border text-[#14140F] font-serif-custom font-normal text-left [white-space:nowrap]"
            >
              Tambah Prompt
            </div>
            <button
              type="button"
              onclick="toggleSidebar(false)"
              data-pencil-name="Tombol Hide"
              class="box-border w-[36px] shrink-0 h-[36px] flex flex-row gap-0 justify-center items-center bg-[#F6F4EF] hover:bg-[#edeae2] [outline:1px_solid_#E1DCD0] [outline-offset:-0.5px] rounded-[10px] cursor-pointer border-0 transition"
              title="Sembunyikan sidebar"
              aria-label="Sembunyikan sidebar"
            >
              <svg
                data-pencil-name="Icon Hide"
                viewBox="0 0 18 18"
                preserveAspectRatio="xMidYMid meet"
                data-icon-name="chevron-right"
                data-icon-set="lucide"
                xmlns="http://www.w3.org/2000/svg"
                class="box-border w-[18px] shrink-0 h-[18px] overflow-visible"
              >
                <path
                  d="M6.552 3.779q-.325 .11-.488 .396-.052 .11-.052 .325 0 .215 .052 .325 .058 .106 2.088 2.14l2.035 2.035-2.035 2.035q-2.03 2.034-2.088 2.144-.052 .106-.052 .303 0 .198 .044 .308 .048 .106 .171 .233 .128 .123 .233 .171 .11 .044 .308 .044 .197 0 .312-.061 .118-.066 2.447-2.395 2.334-2.334 2.395-2.448 .066-.119 .066-.334 0-.215-.057-.321-.053-.11-2.39-2.452l-1.855-1.837q-.431-.431-.593-.536-.128-.092-.255-.092h-.053q-.18 0-.233 .017z"
                  fill="#5b584e"
                />
              </svg>
            </button>
          </div>

          <!-- Sidebar Desc -->
          <div
            data-pencil-name="Sidebar Desc"
            class="text-[13px]/[20px] box-border w-full text-[#5B584E] font-sans-custom font-normal text-left"
          >
            Minta AI memperdalam, membandingkan, atau memfilter rangkuman ini sesuai kebutuhanmu.
          </div>

          <!-- Prompt Form inside Prompt Box -->
          <form id="followUpForm" class="w-full flex flex-col gap-0 m-0">
            @csrf
            <div
              data-pencil-name="Prompt Box"
              class="box-border w-full min-h-[180px] h-[220px] shrink-0 flex flex-col gap-0 p-[16px] justify-between items-start bg-[#F6F4EF] [outline:1px_solid_#EDE9DF] [outline-offset:-0.5px] rounded-[14px] focus-within:[outline:1px_solid_#1D4B3C] transition"
            >
              <textarea
                id="followUpInput"
                class="w-full h-full bg-transparent border-0 outline-none resize-none text-[13px] text-[#14140F] placeholder-[#8C887B] font-sans-custom leading-[20px]"
                placeholder="Misal: 'Bandingkan dampak bagi pedagang kecil vs swalayan', atau 'Apa opsi kebijakan alternatif yang disebut sumber?'"
                maxlength="500"
                required
              ></textarea>
              <div class="w-full flex items-center justify-between pt-2 border-t border-[#EDE9DF]/70 text-[11px] text-[#8C887B] font-mono-custom">
                <span id="charCounter">Maks. 500 karakter</span>
              </div>
            </div>

            <!-- Send Row -->
            <div
              data-pencil-name="Send Row"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-0 justify-end items-start mt-3"
            >
              <button
                type="submit"
                id="followUpBtn"
                data-pencil-name="Kirim Prompt"
                class="box-border w-fit shrink-0 h-[48px] flex flex-row gap-[8px] p-[0px_18px] justify-center items-center bg-[#1D4B3C] hover:bg-[#163b2f] text-[#FFFFFF] rounded-[12px] cursor-pointer transition active:scale-[0.98] border-0 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <!-- Send Icon -->
                <svg
                  id="sendIcon"
                  data-pencil-name="Send Icon"
                  viewBox="0 0 16 16"
                  preserveAspectRatio="xMidYMid meet"
                  data-icon-name="send"
                  data-icon-set="lucide"
                  xmlns="http://www.w3.org/2000/svg"
                  class="box-border w-[16px] shrink-0 h-[16px] overflow-visible"
                >
                  <path
                    d="M14.207 .672q-.016 .015-.031 .015-.047 0-.688 .208l-5.937 2.035q-6.176 2.109-6.321 2.175-.3 .141-.453 .438-.152 .297-.089 .602 .031 .222 .167 .425 .137 .2 .329 .309 .113 .051 2.742 1.105 2.633 1.055 2.746 1.121 .16 .079 .238 .254 .051 .067 1.106 2.7 1.054 2.629 1.105 2.757 .11 .192 .309 .329 .203 .136 .426 .183 .273 .031 .535-.078 .265-.113 .41-.34 .094-.109 .176-.351 .238-.657 2.285-6.664 2.051-6.008 2.066-6.086 .016-.114 0-.243-.047-.414-.375-.675-.328-.266-.746-.219zM9.777 5.281l-2.593 2.574-2.274-.91q-2.269-.914-2.254-.929l9.68-3.313q.016 0-2.559 2.578zM13.121 4.207q-.113 .305-1.664 4.832-1.457 4.289-1.473 4.305-.015 .015-.656-1.582l-1.183-2.946 5.152-5.152q0 0-.176 .543z"
                    fill="#ffffff"
                  />
                </svg>
                <!-- Spinner Icon -->
                <svg id="spinIcon" class="hidden animate-spin w-[16px] h-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <circle cx="12" cy="12" r="10" stroke-opacity="0.25" stroke="currentColor"/>
                  <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor"/>
                </svg>
                <div
                  data-pencil-name="Send Label"
                  class="text-[14px]/[normal] box-border text-[#FFFFFF] font-sans-custom font-semibold text-left [white-space:nowrap]"
                >
                  Kirim Prompt
                </div>
              </button>
            </div>
          </form>

          <!-- Conversation History / Follow-up Responses -->
          <div class="box-border w-full flex flex-col gap-2.5 mt-2 pt-3 border-t border-[#EDE9DF]">
            <div class="text-[10px] text-[#8C887B] font-mono-custom tracking-[1.4px] uppercase font-normal">
              Riwayat Tanya Jawab
            </div>
            <div id="followUpHistory" class="box-border w-full flex flex-col gap-3 max-h-[360px] overflow-y-auto pr-1"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- References Modal / Dialog -->
    <div id="referencesModal" class="modal-backdrop" onclick="closeReferencesModal(event)">
      <div class="modal-box" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="p-6 border-b border-[#EDE9DF] flex items-center justify-between bg-[#FFFFFF]">
          <div>
            <div class="text-[10px] text-[#8C887B] font-mono-custom tracking-[1.4px] uppercase mb-1">
              DAFTAR SUMBER BERITA
            </div>
            <h3 class="font-serif-custom text-[24px] text-[#14140F] font-normal m-0">
              Referensi Terverifikasi ({{ $summary->references->count() }})
            </h3>
          </div>
          <button
            type="button"
            onclick="closeReferencesModal()"
            class="w-9 h-9 rounded-[10px] bg-[#F6F4EF] hover:bg-[#edeae2] [outline:1px_solid_#E1DCD0] [outline-offset:-0.5px] flex items-center justify-center text-[#5B584E] hover:text-[#14140F] transition cursor-pointer border-0"
            aria-label="Tutup modal"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <!-- Filter Input -->
        <div class="px-6 py-3 bg-[#F6F4EF] border-b border-[#EDE9DF]">
          <input
            type="text"
            id="refSearchInput"
            placeholder="Cari sumber media atau judul artikel..."
            class="w-full h-10 px-3.5 text-[13px] bg-[#FFFFFF] [outline:1px_solid_#EDE9DF] [outline-offset:-0.5px] focus:[outline:1px_solid_#1D4B3C] rounded-[10px] text-[#14140F] placeholder-[#8C887B] font-sans-custom border-0"
            oninput="filterReferences(this.value)"
          >
        </div>

        <!-- List -->
        <div class="p-6 overflow-y-auto flex flex-col gap-2 max-h-[55vh]" id="referencesList">
          @forelse ($summary->references as $ref)
            <a
              href="{{ $ref->source_url }}"
              target="_blank"
              rel="noopener noreferrer"
              class="ref-item group"
              data-ref-text="{{ strtolower($ref->source_name . ' ' . $ref->source_title) }}"
            >
              <div class="pr-3">
                <div class="text-[13.5px] font-medium text-[#14140F] group-hover:text-[#1D4B3C] font-sans-custom mb-1 line-clamp-1">
                  {{ $ref->source_title ?: $ref->source_name }}
                </div>
                <div class="flex items-center text-[11px] text-[#8C887B] gap-2 font-mono-custom">
                  <span class="text-[#1D4B3C] font-semibold">{{ $ref->source_name }}</span>
                  @if ($ref->author)
                    <span>&middot;</span>
                    <span>{{ $ref->author }}</span>
                  @endif
                  @if ($ref->published_at)
                    <span>&middot;</span>
                    <span>{{ $ref->published_at->format('d M Y') }}</span>
                  @endif
                </div>
              </div>
              <div class="text-[#8C887B] group-hover:text-[#1D4B3C] shrink-0 transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="7" y1="17" x2="17" y2="7"></line>
                  <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
              </div>
            </a>
          @empty
            <p class="text-[13px] text-[#8C887B] text-center py-6 font-sans-custom m-0">
              Belum ada referensi berita.
            </p>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script>
      // References Modal Handlers
      function openReferencesModal() {
        const modal = document.getElementById('referencesModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('refSearchInput')?.focus(), 100);
      }

      function closeReferencesModal(e) {
        if (!e || e.target === document.getElementById('referencesModal') || !e.target.closest('.modal-box')) {
          const modal = document.getElementById('referencesModal');
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      }

      function filterReferences(query) {
        const q = query.toLowerCase().trim();
        const items = document.querySelectorAll('#referencesList .ref-item');
        items.forEach(el => {
          const text = el.getAttribute('data-ref-text') || '';
          el.style.display = text.includes(q) ? 'flex' : 'none';
        });
      }

      // Sidebar Toggle Logic
      function toggleSidebar(forceState) {
        const sidebar = document.getElementById('sidebarPrompt');
        const openBtn = document.getElementById('quickOpenSidebarBtn');
        if (!sidebar) return;

        const isCurrentlyHidden = sidebar.classList.contains('hidden');
        const shouldShow = typeof forceState === 'boolean' ? forceState : isCurrentlyHidden;

        if (shouldShow) {
          sidebar.classList.remove('hidden');
          if (openBtn) openBtn.classList.add('hidden');
        } else {
          sidebar.classList.add('hidden');
          if (openBtn) openBtn.classList.remove('hidden');
        }
      }

      // Keyboard escape to close modals or sidebar
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          closeReferencesModal();
        }
      });

      // Follow-up Prompt Logic
      const form = document.getElementById('followUpForm');
      const input = document.getElementById('followUpInput');
      const btn = document.getElementById('followUpBtn');
      const sendIcon = document.getElementById('sendIcon');
      const spinIcon = document.getElementById('spinIcon');
      const historyContainer = document.getElementById('followUpHistory');
      const charCounter = document.getElementById('charCounter');

      if (input && charCounter) {
        input.addEventListener('input', function () {
          const len = this.value.length;
          if (len > 0) {
            charCounter.textContent = `${len}/500 karakter`;
            if (len >= 450) {
              charCounter.style.color = '#c2410c';
            } else {
              charCounter.style.color = '#8C887B';
            }
          } else {
            charCounter.textContent = 'Maks. 500 karakter';
            charCounter.style.color = '#8C887B';
          }
        });
      }

      if (form) {
        form.addEventListener('submit', async function (e) {
          e.preventDefault();
          const promptText = input.value.trim();
          if (promptText.length < 3) return;

          btn.disabled = true;
          sendIcon.classList.add('hidden');
          spinIcon.classList.remove('hidden');

          try {
            const res = await fetch("{{ route('summaries.follow_up', $summary) }}", {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify({ prompt: promptText })
            });

            const data = await res.json();

            if (data.success) {
              const block = document.createElement('div');
              block.className = 'chat-exchange';
              block.innerHTML = `
                <div class="font-semibold text-[#1D4B3C] mb-1.5 flex items-center justify-between text-[12px] font-sans-custom">
                  <span>Q: "${escapeHtml(data.prompt)}"</span>
                  <span class="text-[10px] text-[#8C887B] font-mono-custom font-normal">${data.created_at}</span>
                </div>
                <div class="text-[#5B584E] whitespace-pre-line leading-[20px] text-[13px] font-sans-custom">${escapeHtml(data.answer)}</div>
              `;
              historyContainer.prepend(block);
              input.value = '';
              if (charCounter) {
                charCounter.textContent = 'Maks. 500 karakter';
                charCounter.style.color = '#8C887B';
              }
            } else {
              alert(data.message || 'Gagal memproses prompt.');
            }
          } catch (err) {
            alert('Terjadi kesalahan koneksi saat mengirim prompt.');
          } finally {
            btn.disabled = false;
            sendIcon.classList.remove('hidden');
            spinIcon.classList.add('hidden');
          }
        });
      }

      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }
    </script>
  </body>
</html>
