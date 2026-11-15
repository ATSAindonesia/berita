<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script>
      tailwind = { config: { corePlugins: { preflight: false } } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono%3Awght%40400&family=Inter%3Awght%40100..900&family=Newsreader%3Awght%40200..800&display=swap"
      rel="stylesheet"
    />
    <style>
      *, ::before, ::after {
        box-sizing: border-box;
      }

      body {
        margin: 0;
      }
    </style>

    <style>
      body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    </style>
  </head>
  <body style="background-color: #F6F4EF">
    <div
      data-pencil-name="03 · Mencari"
      class="box-border w-[1440px] h-[900px] flex flex-col gap-0 justify-start items-start bg-[#F6F4EF] overflow-hidden"
    >
      <div
        data-pencil-name="Top Bar"
        class="box-border w-full h-[88px] shrink-0 flex flex-row gap-0 p-[0px_64px] justify-between items-center"
      ></div>
      <div
        data-pencil-name="Loading Stage"
        class="box-border w-full [flex:1_1_0] flex flex-col gap-0 justify-center items-center"
      >
        <div
          data-pencil-name="Loading Stack"
          class="box-border w-[760px] h-[434px] shrink-0 relative"
        >
          <div
            data-pencil-name="Status Row"
            class="box-border w-fit h-fit absolute left-0 top-0 flex flex-row gap-[10px] justify-start items-center [z-index:0]"
          >
            <div
              data-pencil-name="Pulse"
              class="box-border w-[8px] shrink-0 h-[8px] flex flex-row gap-0 justify-start items-start bg-[#2C6B52] rounded-[999px]"
            ></div>
            <div
              data-pencil-name="Status Label"
              class="text-[10px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.8px] text-left [white-space:nowrap]"
            >
              AI SEDANG MENCARI
            </div>
          </div>
          <div
            data-pencil-name="Gap 1"
            class="box-border w-[1px] h-[20px] absolute left-0 top-[13px] flex flex-row gap-0 justify-start items-start [z-index:1]"
          ></div>
          <div
            data-pencil-name="Query Line"
            class="text-[52px]/[58px] box-border w-[760px] absolute left-0 top-[33px] text-[#14140F] font-[Newsreader,system-ui,sans-serif] font-normal text-left [z-index:2]"
          >
            harga beras naik
          </div>
          <div
            data-pencil-name="Gap 2"
            class="box-border w-[1px] h-[10px] absolute left-0 top-[91px] flex flex-row gap-0 justify-start items-start [z-index:3]"
          ></div>
          <div
            data-pencil-name="Query Meta"
            class="text-[14px]/[normal] box-border absolute left-0 top-[101px] text-[#8C887B] font-[Inter,system-ui,sans-serif] font-normal text-left [white-space:nowrap] [z-index:4]"
          >
            12 detik berjalan · 42 dari 128 penerbit dibaca
          </div>
          <div
            data-pencil-name="Gap 3"
            class="box-border w-[1px] h-[28px] absolute left-0 top-[118px] flex flex-row gap-0 justify-start items-start [z-index:5]"
          ></div>
          <div
            data-pencil-name="Progress Track"
            class="box-border w-[760px] h-[6px] absolute left-0 top-[146px] flex flex-row gap-0 justify-start items-start bg-[#EDEAE0] rounded-[999px] [z-index:6]"
          >
            <div
              data-pencil-name="Progress Fill"
              class="box-border w-[471px] h-[6px] absolute left-0 top-0 flex flex-row gap-0 justify-start items-start bg-[#1D4B3C] rounded-[999px] [z-index:0]"
            ></div>
          </div>
          <div
            data-pencil-name="Gap 4"
            class="box-border w-[1px] h-[30px] absolute left-0 top-[152px] flex flex-row gap-0 justify-start items-start [z-index:7]"
          ></div>
          <div
            data-pencil-name="Steps"
            class="box-border w-[760px] h-fit absolute left-0 top-[182px] flex flex-col gap-0 justify-start items-start [z-index:8]"
          >
            <div
              data-pencil-name="Step Menelusuri penerbit"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-[14px] p-[13px_0px] justify-start items-center"
            >
              <div
                data-pencil-name="Dot Menelusuri penerbit"
                class="box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#1D4B3C] rounded-[999px]"
              >
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
              </div>
              <div
                data-pencil-name="Step Text"
                class="box-border [flex:1_1_0] h-fit flex flex-col gap-[2px] justify-start items-start"
              >
                <div
                  data-pencil-name="Step Label"
                  class="text-[14px]/[normal] box-border text-[#14140F] font-[Inter,system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
                >
                  Menelusuri penerbit
                </div>
                <div
                  data-pencil-name="Step Meta"
                  class="text-[12px]/[normal] box-border text-[#8C887B] font-[Inter,system-ui,sans-serif] font-normal text-left [white-space:nowrap]"
                >
                  Selesai · 128 sumber
                </div>
              </div>
            </div>
            <div
              data-pencil-name="Step Membaca artikel"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-[14px] p-[13px_0px] justify-start items-center"
            >
              <div
                data-pencil-name="Dot Membaca artikel"
                class="box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#E5EFE7] rounded-[999px]"
              >
                <div
                  data-pencil-name="Active Dot"
                  class="box-border w-[7px] shrink-0 h-[7px] bg-[#1D4B3C] rounded-[50%]"
                ></div>
              </div>
              <div
                data-pencil-name="Step Text"
                class="box-border [flex:1_1_0] h-fit flex flex-col gap-[2px] justify-start items-start"
              >
                <div
                  data-pencil-name="Step Label"
                  class="text-[14px]/[normal] box-border text-[#14140F] font-[Inter,system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
                >
                  Membaca artikel
                </div>
                <div
                  data-pencil-name="Step Meta"
                  class="text-[12px]/[normal] box-border text-[#8C887B] font-[Inter,system-ui,sans-serif] font-normal text-left [white-space:nowrap]"
                >
                  42 dari 96 artikel
                </div>
              </div>
              <div
                data-pencil-name="Live Tag"
                class="box-border w-fit shrink-0 h-fit flex flex-row gap-0 p-[4px_9px] justify-start items-start bg-[#E5EFE7] rounded-[999px]"
              >
                <div
                  data-pencil-name="Live Tag Text"
                  class="text-[9px]/[normal] box-border text-[#1D4B3C] font-['IBM_Plex_Mono',system-ui,sans-serif] font-normal tracking-[1.2px] text-left [white-space:nowrap]"
                >
                  BERJALAN
                </div>
              </div>
            </div>
            <div
              data-pencil-name="Step Menyusun ringkasan"
              class="box-border w-full h-fit shrink-0 flex flex-row gap-[14px] p-[13px_0px] justify-start items-center"
            >
              <div
                data-pencil-name="Dot Menyusun ringkasan"
                class="box-border w-[20px] shrink-0 h-[20px] flex flex-row gap-0 justify-center items-center bg-[#EDEAE0] rounded-[999px]"
              ></div>
              <div
                data-pencil-name="Step Text"
                class="box-border [flex:1_1_0] h-fit flex flex-col gap-[2px] justify-start items-start"
              >
                <div
                  data-pencil-name="Step Label"
                  class="text-[14px]/[normal] box-border text-[#8C887B] font-[Inter,system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
                >
                  Menyusun ringkasan
                </div>
                <div
                  data-pencil-name="Step Meta"
                  class="text-[12px]/[normal] box-border text-[#8C887B] font-[Inter,system-ui,sans-serif] font-normal text-left [white-space:nowrap]"
                >
                  Menunggu artikel selesai
                </div>
              </div>
            </div>
          </div>
          <div
            data-pencil-name="Gap 5"
            class="box-border w-[1px] h-[26px] absolute left-0 top-[362px] flex flex-row gap-0 justify-start items-start [z-index:9]"
          ></div>
          <div
            data-pencil-name="Cancel Row"
            class="box-border w-fit h-fit absolute left-0 top-[388px] flex flex-row gap-[14px] justify-start items-center [z-index:10]"
          >
            <div
              data-pencil-name="Cancel Button"
              class="box-border w-fit shrink-0 h-[46px] flex flex-row gap-[9px] p-[0px_18px] justify-start items-center bg-[#FFFFFF] [outline:1.5px_solid_#E1DCD0] [outline-offset:-0.75px] rounded-[12px]"
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
                data-pencil-name="Cancel Label"
                class="text-[13.5px]/[normal] box-border text-[#14140F] font-[Inter,system-ui,sans-serif] font-medium text-left [white-space:nowrap]"
              >
                Batalkan pencarian
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        data-pencil-name="Footer"
        class="box-border w-full h-[72px] shrink-0 flex flex-row gap-0 p-[0px_64px] justify-between items-center"
      ></div>
    </div>
  </body>
</html>


# IMPLEMENTATION INSTRUCTIONS — REAL-TIME AI NEWS PROCESSING LOADING

## 1. Objective

Implementasikan sistem loading frontend yang sudah tersedia agar terintegrasi dengan proses backend AI News Aggregation & Summarization.

Frontend saat ini sudah memiliki desain loading menggunakan dummy data. Tugas utama adalah menghubungkan desain tersebut dengan status dan aktivitas pemrosesan backend yang sebenarnya.

**PENTING:** Jangan membuat desain loading baru. Gunakan desain, komponen, animasi, struktur, dan layout yang sudah tersedia.

## 2. Implementation Workflow

Ikuti urutan pekerjaan berikut:

1. Periksa seluruh kode frontend yang diberikan.
2. Identifikasi komponen loading, animasi, state, dan dummy data yang digunakan.
3. Pahami bagaimana setiap tahapan loading ditampilkan kepada pengguna.
4. Periksa implementasi backend dan identifikasi proses yang benar-benar tersedia.
5. Identifikasi API endpoint dan mekanisme komunikasi frontend-backend.
6. Hubungkan setiap tahapan loading dengan status pemrosesan backend.
7. Ganti dummy data dengan status aktual dari backend.
8. Pastikan seluruh transisi loading berjalan sesuai proses yang sebenarnya.

Jangan langsung melakukan perubahan sebelum memahami struktur implementasi yang sudah tersedia.

## 3. Loading Process Stages

Gunakan tahapan berikut sebagai acuan pemetaan status loading:

| Stage | Activity | Description |
|---|---|---|
| 1 | Initializing | Mempersiapkan proses pencarian berita |
| 2 | Prompt Enhancement | AI mengoptimalkan instruksi pencarian berdasarkan prompt pengguna |
| 3 | Query Generation | AI menghasilkan kata kunci pencarian yang relevan |
| 4 | News Searching | SearXNG mencari berita dari berbagai sumber |
| 5 | Content Extraction | Mengambil konten artikel dari URL hasil pencarian |
| 6 | News Filtering | Menyaring berita relevan dan menghapus duplikasi |
| 7 | News Analysis | AI menganalisis dan membandingkan informasi dari berbagai artikel |
| 8 | Summarization | AI menyusun rangkuman berita |
| 9 | Saving Results | Menyimpan hasil rangkuman dan referensi ke database |
| 10 | Completed | Seluruh proses berhasil diselesaikan |

Tahapan tersebut merupakan acuan pemetaan UI, bukan asumsi bahwa seluruh proses backend sudah tersedia.

Sesuaikan dengan implementasi backend yang benar-benar ada.

## 4. Backend Integration

Integrasikan frontend dengan backend menggunakan mekanisme komunikasi yang sesuai dengan arsitektur proyek.

Prioritaskan penggunaan:

- Server-Sent Events (SSE) untuk mengirimkan update status proses secara real-time.
- Laravel Queue untuk menjalankan proses AI secara asynchronous.
- Database untuk menyimpan status dan riwayat proses.

Jika proyek sudah memiliki mekanisme polling atau WebSocket, evaluasi terlebih dahulu sebelum menambahkan teknologi baru.

Frontend harus menerima informasi seperti:

- Current processing stage.
- Processing status.
- Progress percentage jika tersedia dan dapat dihitung secara valid.
- Activity description.
- Error message apabila terjadi kegagalan.
- Final result ketika proses selesai.

Jangan membuat progress percentage palsu hanya berdasarkan timer atau animasi.

## 5. Frontend Behavior

Pastikan desain loading yang sudah tersedia dapat menangani kondisi berikut:

**A. Processing**
- Menampilkan tahapan yang sedang berjalan.
- Menandai tahapan sebelumnya sebagai selesai.
- Menampilkan indikator aktivitas pada tahapan aktif.
- Memperbarui deskripsi berdasarkan aktivitas backend.

**B. Completed**
- Menandai seluruh tahapan sebagai selesai.
- Menghentikan indikator loading.
- Mengarahkan atau menampilkan hasil rangkuman berita.

**C. Failed**
- Menampilkan informasi kegagalan.
- Menunjukkan tahapan tempat proses mengalami error.
- Menyediakan mekanisme retry jika memungkinkan.

**D. Partial Failure**
- Jika sebagian artikel gagal diambil tetapi proses masih dapat dilanjutkan, tampilkan status yang sesuai.
- Jangan menganggap seluruh proses gagal hanya karena satu sumber berita bermasalah.

## 6. Backend Status Contract

Pastikan backend menyediakan struktur status yang konsisten.

Contoh format payload:

```json
{
  "session_id": "session-123",
  "status": "processing",
  "current_stage": "news_analysis",
  "stage_label": "Analyzing News",
  "description": "AI sedang membandingkan informasi dari berbagai sumber berita.",
  "progress": null,
  "completed_stages": [
    "initializing",
    "prompt_enhancement",
    "query_generation",
    "news_searching",
    "content_extraction",
    "news_filtering"
  ],
  "error": null
}
```

Contoh status akhir:

```json
{
  "session_id": "session-123",
  "status": "completed",
  "current_stage": "completed",
  "stage_label": "Completed",
  "description": "Rangkuman berita berhasil dibuat.",
  "progress": 100,
  "error": null
}
```

Format tersebut merupakan contoh kontrak komunikasi. Sesuaikan implementasinya dengan struktur backend yang ada.

## 7. Important Implementation Constraints

- Jangan mengubah desain visual yang sudah ada.
- Jangan mengubah CSS, layout, spacing, typography, atau animasi tanpa kebutuhan teknis yang jelas.
- Jangan membangun ulang komponen loading.
- Jangan menghapus komponen yang masih digunakan.
- Jangan mengubah struktur frontend secara besar-besaran.
- Jangan menggunakan dummy data sebagai sumber status proses setelah integrasi selesai.
- Jangan membuat status proses yang tidak benar-benar terjadi di backend.
- Jangan mengarang persentase progress.
- Hindari polling yang terlalu sering.
- Pastikan koneksi status dapat dihentikan ketika proses selesai atau komponen dilepas.
- Tangani reconnect dan kegagalan koneksi dengan benar.

## 8. Expected Result

Hasil akhir yang diharapkan:

1. Desain loading tetap sama dengan implementasi frontend awal.
2. Dummy data berhasil digantikan oleh data status backend.
3. Setiap tahapan loading mencerminkan aktivitas backend yang sebenarnya.
4. Perubahan status dapat ditampilkan secara real-time atau melalui mekanisme update yang tersedia.
5. Loading menangani kondisi processing, completed, failed, dan partial failure.
6. Setelah proses selesai, pengguna dapat melihat hasil rangkuman berita beserta referensinya.

## 9. Final Instruction

Sebelum melakukan implementasi, lakukan pemeriksaan terhadap frontend dan backend yang tersedia.

Identifikasi terlebih dahulu:
- File komponen loading.
- File yang mengatur dummy data.
- API endpoint yang tersedia.
- Backend processing stages.
- Mekanisme komunikasi status.

Setelah itu, lakukan implementasi dengan perubahan seminimal mungkin.

Jangan melakukan rebuild atau refactoring besar. Fokus hanya pada integrasi loading frontend dengan proses backend aktual.

Setelah implementasi, berikan laporan singkat berisi:
- File yang diubah.
- Perubahan yang dilakukan.
- API endpoint yang digunakan.
- Tahapan loading yang berhasil diintegrasikan.
- Tahapan yang belum tersedia di backend.
- Hasil validasi dan pengujian.