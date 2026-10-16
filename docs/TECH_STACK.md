# Tech Stack dan Pedoman Optimasi

## Keputusan stack

| Area | Pilihan | Keputusan penggunaan |
|---|---|---|
| Application | Laravel 13, PHP 8.4+ | Monolith modular; auth, policy, validation, job, cache, dan data ada dalam satu deployable. |
| UI | Inertia.js 3, Vue 3, TypeScript, Vite | Interaktif tanpa memisahkan API publik dan SPA. |
| Styling | Tailwind CSS 4 | Mobile-first, design token, breakpoint, dan code splitting CSS. |
| Database | PostgreSQL 17 | Data transaksional dan relasional LMS. |
| Cache | Redis 7 + `predis/predis` | Cache shared dan rate-limit. |
| Queue MVP | Laravel database queue | Cukup untuk impor/ekspor/notifikasi awal; pindah queue ke Redis ketika throughput menuntut. |
| Storage | S3-compatible object storage | Phase 1 memakai filesystem lokal; resource course berpindah ke bucket privat pada Phase 4. |
| Runtime lokal | Docker Compose | Service parity untuk PHP, Nginx, PostgreSQL, Redis, dan Vite. |
| Tests | Laravel feature tests, Vue component tests, Playwright E2E | Business rule/RBAC, komponen, serta desktop-mobile flow. |

Laravel Policies menjadi sumber otorisasi resource. Tidak ada API gateway, microservice, native app, real-time socket, atau search engine pada MVP.

## Struktur Docker

| Service | Port lokal default | Tanggung jawab |
|---|---:|---|
| `web` | 8080 | Nginx, hanya mengekspos `public/`. |
| `app` | internal | PHP-FPM dan Laravel. |
| `node` | 5174 (host) | Vite dev server pada port internal 5173; memakai image aplikasi agar Wayfinder dapat menjalankan Artisan. |
| `db` | 5433 (host) | PostgreSQL memakai port internal 5432 untuk aplikasi. |
| `redis` | 6379 | Cache dan kebutuhan queue berikutnya. |

`vendor` dan runtime `storage` memakai Docker named volume. Source tetap bind mount untuk pengembangan, sedangkan dependency, cache Blade, session, dan log berjalan pada filesystem Linux agar PHP-FPM tidak terhambat permission atau I/O Windows.

Nginx menerima request hingga 128 MB dan PHP membatasi satu unggahan materi hingga 50 MB (`upload_max_filesize=50M`, `post_max_size=52M`), selaras dengan validasi lesson PDF/video.

Setelah skeleton Laravel dibuat, salin `docker/.env.example` menjadi `.env`, lengkapi `APP_KEY`, lalu jalankan `docker compose up --build`. Konfigurasi bucket privat beserta credential storage dibuat saat resource course mulai diimplementasikan pada Phase 4.

Production harus memakai object storage S3-compatible dengan endpoint, credential, dan artefak/image yang dipin ke digest/checksum yang disetujui.

## Kebijakan optimasi

### Cache dan deduplikasi

- Cache Redis: taxonomy/course catalog published, struktur organisasi, pilihan filter, dashboard summary, dan aggregate report terfilter dengan TTL pendek.
- Cache key menyertakan versi data/filter/role yang memengaruhi hasil, misalnya `learner-dashboard:{userId}:{progressVersion}`.
- Invalidasi terjadi pada publish course, perubahan enrollment, penyelesaian lesson, submit attempt, atau perubahan organisasi yang relevan.
- Jangan cache form submission, attempt aktif, atau hasil authorization sebagai kebenaran jangka panjang.
- Satu controller/page loader mengambil satu dataset; child component menerima props, bukan meminta data yang sama ulang.

### Database dan response

- Semua list memakai cursor atau offset pagination yang dibatasi; gunakan cursor untuk activity/audit yang besar.
- Query memilih kolom yang dipakai view; gunakan eager loading terarah dan aggregate (`withCount`, subquery/aggregate) untuk statistik.
- Periksa query count pada dashboard/listing dan buat test/regression check untuk alur yang sebelumnya N+1.
- Resource/DTO untuk Inertia menentukan payload; jangan serialisasi model/relasi penuh.
- Index hanya ditambahkan untuk access pattern yang tercatat dalam `DATABASE_SYSTEM.md`; ukur query plan sebelum menambah index spekulatif.

### Frontend, media, dan CDN

- Halaman dan library berat di-split per route; chart dimuat hanya pada dashboard/report yang menampilkannya.
- Gunakan native `loading="lazy"` untuk thumbnail di bawah fold serta dynamic import untuk modal dan chart.
- Simpan original media privat; hasilkan/unggah turunan thumbnail pada ukuran penggunaan. Sajikan WebP atau AVIF jika rantai asset mendukung, dengan fallback yang kompatibel.
- Asset build publik memakai cache immutable/versioning dan CDN. File materi privat memakai signed temporary URL, bukan cache CDN publik.
- Hindari memoization global. Terapkan hanya setelah pengukuran menunjukkan re-render/perhitungan berat.

### Background processing

Worker menjalankan import user, export CSV/Excel/PDF, email massal, generate certificate, report periodik, dan transformasi media. HTTP request hanya membuat job serta memberi status; retry, timeout, idempotency key, dan failed-job visibility wajib disiapkan.

## Target pengukuran sebelum scale-up

| Sinyal | Tindakan awal |
|---|---|
| P95 dashboard/list > target | Periksa query plan, kolom, index, payload, lalu cache aggregate yang tepat. |
| Queue menumpuk | Pisahkan worker/export queue; pindah driver queue ke Redis bila database queue menjadi bottleneck. |
| Asset/media lambat | Tambahkan CDN dan direct upload ke object storage; jangan menaikkan limit PHP tanpa alasan. |
| DB CPU atau I/O tinggi | Audit query/report, pagination, index, dan retensi audit sebelum menambah instance. |

## Batas keamanan

Secret tidak masuk repository atau Docker image. Production menggunakan HTTPS, secret manager/managed environment variables, encrypted backup, private bucket, least-privilege database credentials, health checks, structured application logs, dan alert failed jobs.
