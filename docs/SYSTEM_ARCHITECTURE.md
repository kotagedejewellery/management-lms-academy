# System Architecture

## 1. Prinsip arsitektur

KGJ Academy adalah modular monolith. Satu aplikasi Laravel menangani domain, authorization, validation, transaction, Inertia responses, jobs, dan audit. Batas modul menjaga kode tetap terarah tanpa biaya operasional microservices.

```text
Browser desktop/mobile
  │ HTTPS / session cookie
  ▼
Nginx ────────────── static Vite assets (CDN di production)
  │ FastCGI
  ▼
Laravel + Inertia + Vue
  ├─ Policies/RBAC
  ├─ Domain modules: Organization, Learning, Assessment, Reporting
  ├─ Redis cache / rate limit
  ├─ PostgreSQL
  ├─ Private object storage (PDF, video, thumbnail, certificate)
  └─ Queue worker (imports, exports, mail, heavy generation)
```

## 2. Modul aplikasi

| Modul | Tanggung jawab | Batas penting |
|---|---|---|
| Identity & Access | Login, reset password, role, permission, policy | Semua resource check di server. |
| Organization | User, division, position, branch, manager | Menyediakan audience dan scope reporting. |
| Learning Catalog | Course, module, lesson, resource, publication | Hanya published course dapat dipelajari. |
| Learning Path & Enrollment | Path, course order, prerequisite, assignment | Menentukan course yang tersedia pada learner. |
| Assessment | Pre/Post-Test, question, attempt, response, scoring, retake authorization | Attempt immutable setelah submitted; setiap retake Post-Test membutuhkan otorisasi internal yang tercatat. |
| Progress & Completion | Lesson progress, course progress, completion state | Menghitung state dari data terotorisasi. |
| Reporting | Dashboard, filter, aggregate, export | Query minimal; export berjalan sebagai job. |
| Activity & Operations | Audit log, notification job, failed job | Tidak mengubah alur belajar utama. |

## 3. Jalur request

### Learner

1. Browser meminta page Inertia.
2. Middleware memverifikasi session, role, dan policy.
3. Action mengambil data ber-paginasi, relasi yang dibutuhkan, serta cache aggregate yang valid.
4. Server mengirim props minimum; Vue merender route yang di-split.
5. Media privat diberikan sebagai signed URL berumur pendek setelah policy check.

### Submit assessment

1. Server memvalidasi enrollment, prerequisite, timer, attempt limit, ownership, dan otorisasi retake bila Post-Test sudah pernah dikirim.
2. Dalam transaksi: simpan responses, hitung score, kunci attempt sebagai submitted, dan perbarui course progress.
3. Buat audit activity dan invalidasi cache progress/report yang relevan.
4. Kembalikan konfirmasi pengiriman netral kepada learner. Score, outcome, jawaban, dan kunci jawaban hanya tersedia pada jalur laporan internal yang terotorisasi.

### Otorisasi retake Post-Test

1. Setelah Post-Test belum memenuhi standar, Employee tidak dapat memulai attempt berikutnya sendiri.
2. Trainer yang ditugaskan pada course, atau role internal dengan izin enrollment/laporan, membuat satu otorisasi retake untuk enrollment dan assessment tersebut.
3. Employee melihat halaman persiapan netral dan harus menekan tindakan mulai sebelum otorisasi dikonsumsi atomik serta attempt berikutnya dibuat. Otorisasi yang belum dipakai dapat dibatalkan secara soft-cancel oleh pihak berwenang dan tidak dapat melampaui attempt limit assessment.

### Pekerjaan berat

Request membuat job dan segera memberi status. Worker mengeksekusi export/import/generation, menyimpan artefak di storage privat, mencatat hasil, lalu mengirim notifikasi bila fitur aktif.

## 4. Topologi Docker lokal

```text
web:8080 → app:9000 → db:5432
                  └→ redis:6379
node:5173 ────────→ mounted source / Vite HMR
```

Docker Compose adalah lingkungan developer. Phase 1 menggunakan filesystem lokal karena belum ada resource course. Production menggunakan image terbangun, reverse proxy/TLS, database terkelola atau ter-backup, Redis terkelola bila diperlukan, dan S3-compatible object storage; bind mount lokal tidak digunakan di production. Reset data operasional tidak pernah berjalan saat deploy: reset production hanya dapat dipanggil manual dengan konfirmasi satu kali, mempertahankan user, role, permission, relasi user-role, dan struktur organisasi.

## 5. Keamanan dan ketahanan

- Authorization melalui Laravel Policies di setiap action/resource; UI hide bukan kontrol keamanan.
- Password di-hash; HTTPS, CSRF, session security, rate limit login/reset, dan input validation aktif.
- Bucket private; object access menggunakan signed URL setelah authorization.
- Audit log append-only secara aplikasi untuk admin change dan assessment activity.
- Database backup terjadwal dan restore drill sebelum go-live; object storage memiliki lifecycle/backup terpisah.
- Error log, failed job, health endpoint, dan metrik request/queue/database dipantau.

## 6. Scale path

MVP berjalan dengan satu app instance dan database queue. Ketika pengukuran membuktikan kebutuhan: tambah worker terpisah, pindahkan queue ke Redis, tambah CDN untuk public build assets, cache aggregate report, lalu scale app horizontally. Jangan memecah service sebelum batas modul atau metrik menunjukkan alasan nyata.
