# PRD — KGJ Academy LMS

## 1. Ringkasan

KGJ Academy adalah Learning Management System internal untuk onboarding, SOP, product knowledge, kompetensi jabatan, evaluasi, dan pelaporan pembelajaran Kotagede Jewellery. Sistem melayani seluruh cabang dan divisi melalui web responsif.

## 2. Tujuan dan ukuran keberhasilan

| Tujuan | Bukti tercapai pada MVP |
|---|---|
| Pembelajaran terarah | Employee otomatis/terarah menerima learning path sesuai divisi, jabatan, dan cabang. |
| Evaluasi terukur | Pre-Test, materi, Post-Test, attempt, skor, dan improvement tercatat. |
| Kelulusan konsisten | Sistem menerapkan prerequisite, passing grade, dan retake per course. |
| Monitoring aman | Trainer melihat course binaannya; Admin dapat memfilter dan ekspor laporan organisasi. |
| Operasional mandiri | Admin dapat mengelola organisasi, konten, path, dan assignment tanpa perubahan kode. |

## 3. Pengguna dan hak akses

| Role | Kebutuhan utama |
|---|---|
| Super Admin | Konfigurasi menyeluruh, user, materi, laporan, dan audit. |
| LMS Admin / HR | User, struktur organisasi, course, path, enrollment, dan laporan. |
| Trainer / Mentor | Course dan penilaian yang ditugaskan melalui area operasional; tidak mengakses alur learner. |
| Employee / Learner | Materi, Pre-Test, Post-Test, konfirmasi pengiriman, dan progres pribadi tanpa nilai atau detail evaluasi. |

## 4. Scope MVP

1. Login dengan password, verifikasi email kerja, reset password melalui email, session authentication, dan RBAC. Akun yang dibuat Admin maupun impor harus memverifikasi email kerja sebelum memasuki area terverifikasi. Autentikasi dua faktor serta passkey tidak termasuk scope internal saat ini.
2. User, divisi, jabatan, cabang, manager, dan status akun.
3. Course, module, lesson, resource, thumbnail, dan status Draft/Published/Archived.
4. Learning path, prerequisite, enrollment, dan assignment dasar.
5. Pre-Test baseline satu percobaan dan Post-Test dengan question, answer, timer opsional, 1â€“3 attempt, hasil, serta passing grade.
6. Resume lesson, lesson/course progress, completion, dan status Not Passed.
7. Learner dashboard, admin dashboard, dan laporan dasar dengan filter/ekspor.
8. Audit activity untuk perubahan administratif serta assessment attempts.
9. Desktop/mobile responsive dan akses file privat.

### Di luar MVP

Certificate, question bank, course versioning, notifikasi lanjutan, gamification, AI assistant/search, HRIS/SSO, dan native mobile app mengikuti fase yang tercantum pada brief.

## 5. Alur inti

```text
Admin membuat/memperbarui user
  → menetapkan divisi + jabatan + cabang
  → membuat assignment/enrollment learning path
  → employee login
  → Pre-Test bila mandatory
  → menyelesaikan lesson wajib
  → Post-Test
  → employee menerima konfirmasi pengiriman netral
  → Trainer/LMS Admin/Super Admin menilai hasil dan, bila perlu, membuka kesempatan ulang
  → LMS Admin/Super Admin melihat laporan sesuai otorisasi
```

Contoh path Customer Service: Brand Introduction → Product Knowledge → Material & Specification → Customer Consultation → Sales & Closing → CRM & Follow Up → Complaint Handling → Final Evaluation.

## 6. Aturan bisnis yang tidak boleh berubah tanpa persetujuan pemilik proses

- Pre-Test dan Post-Test adalah tipe assessment berbeda; record atau hasilnya tidak boleh saling menimpa.
- Admin dapat memilih modul mana yang memiliki Pre-Test atau Post-Test; test tidak wajib dibuat untuk setiap modul.
- Pre-Test modul yang dibuat mengunci materi modul sampai disubmit; skor rendah tetap tidak menggagalkan course.
- Post-Test modul yang dibuat baru terbuka setelah lesson mandatory modul selesai. Setelah dikirim, modul berikutnya dapat dibuka tanpa menampilkan nilai maupun status lulus kepada Employee.
- Setelah seluruh lesson wajib dan assessment yang digunakan dikirim, course berstatus menunggu evaluasi akhir. Trainer yang ditugaskan, LMS Admin, atau Super Admin berwenang menyetujui kelulusan atau membuka kesempatan ulang Post-Test; sertifikat hanya diterbitkan setelah persetujuan kelulusan.
- Pre-Test selalu satu attempt dan tidak memiliki passing grade; jumlah attempt Post-Test diatur per assessment dalam rentang 1â€“3. Nilai Post-Test menjadi data evaluasi internal dan tidak otomatis mengubah status kelulusan Employee.
- Nilai course internal menggunakan bobot sama untuk setiap modul yang memiliki Pre-Test dan Post-Test: `(nilai Pre-Test pertama + nilai terbaik Post-Test) / 2`. Nilai akhir course adalah rata-rata seluruh nilai modul tersebut dan baru final setelah semua modul evaluatif selesai; sebelum itu hanya tampil sebagai rata-rata berjalan bagi role internal.
- Setiap attempt Post-Test setelah attempt pertama hanya dapat dibuka satu kali oleh Trainer yang ditugaskan atau role internal yang memiliki wewenang laporan/enrollment saat course berstatus menunggu evaluasi akhir. Otorisasi tidak dapat melampaui batas attempt yang dikonfigurasi.
- Course wajib dalam learning path berjalan berurutan: course wajib sebelumnya harus selesai sebelum course wajib berikutnya dapat dibuka. Course baru ditambahkan di akhir path; Admin mengubah urutan dengan aksi Naik/Turun. Soal assessment juga ditambahkan otomatis di akhir dan dapat dipindahkan dengan aksi yang sama.
- Simpan seluruh attempt, response, score, waktu, dan status untuk audit.
- Employee tidak menerima skor, passing grade, status lulus/gagal, sisa kesempatan, riwayat jawaban, kunci jawaban, atau pembahasan pada Dashboard, My Learning, My Progress, maupun Learning Path. Halaman setelah submit hanya mengonfirmasi bahwa assessment telah dikirim. Trainer, LMS Admin, dan Super Admin melihat hasil sesuai cakupan aksesnya.
- Otorisasi retake yang belum digunakan tidak memulai attempt saat halaman dibuka: Employee harus menekan tindakan mulai secara eksplisit. Trainer, LMS Admin, atau Super Admin dapat membatalkan otorisasi yang belum dipakai; pembatalan tetap tersimpan untuk audit.
- Improvement ditampilkan dalam poin: `post_test_score - pre_test_score`, bukan persen.
- Laporan repeat Post-Test harus menyatakan kebijakan skor (pertama, terakhir, terbaik); default laporan menampilkan skor pertama dan terbaik.
- Artikel wajib memiliki isi. Lesson PDF/video wajib memiliki satu sumber: file privat yang sesuai atau URL. Course tidak dapat diterbitkan bila ada sumber materi yang belum lengkap, assessment aktif tanpa soal, atau soal yang tidak memiliki tepat satu jawaban benar. Course editor menampilkan checklist dan tautan perbaikan untuk setiap kegagalan publikasi.
- Modul, lesson, dan sumber materi hanya dapat diubah ketika course berstatus Draft; course harus dikembalikan ke Draft sebelum materi Published diubah.
- Sertifikat hanya diterbitkan bila fitur diaktifkan pada course/path terkait.
- Akun Trainer / Mentor adalah akun operasional: hanya mengakses Dashboard, Course binaan, Progres peserta, dan Notifikasi. Pengujian atau pembelajaran sebagai peserta menggunakan akun Employee terpisah.

## 7. Kebutuhan fungsional per area

### Learner

Login; dashboard; My Learning; Learning Path; Course Detail; lesson player video/article/PDF; resources; previous/next; resume progress; Pre-Test; Post-Test; konfirmasi pengiriman tanpa hasil evaluasi; profile; dan sertifikat course yang diaktifkan Admin.

### Admin

Kelola user dan impor CSV/Excel; struktur organisasi; course builder; module/lesson; artikel teks; PDF privat; video MP4/WebM privat atau URL video; assessment builder; learning path; enrollment; preview/publish; announcements/notifications saat fasenya aktif; dashboard; laporan; export CSV/Excel/PDF; dan audit.

### Course minimum

Course Code, title, description, category, division, position, duration, mandatory/optional, prerequisite, Pre-Test settings, Post-Test settings, passing grade, attempts, audience, dan publication status.

## 8. Kebutuhan kualitas dan performa

| Kebutuhan | Ketetapan implementasi |
|---|---|
| Caching | Cache Redis hanya untuk data baca yang sering; invalidasi pada perubahan sumber data. |
| Daftar data | Seluruh halaman daftar memakai pagination server-side; tidak ada `SELECT` massal ke browser. |
| Lazy loading | Asset gambar, modal, chart, bagian dashboard non-kritis, dan page bundle dimuat saat diperlukan. |
| Media | Thumbnail memiliki ukuran turunan; WebP/AVIF bila kompatibel; video tidak dikirim lewat database. |
| Database | Foreign key dan index mengikuti pola filter, join, ordering, dan laporan pada `DATABASE_SYSTEM.md`. |
| Query | Eager loading terarah, kolom eksplisit, query agregat, dan larangan N+1 pada dashboard/listing. |
| Payload | Controller/Inertia resource hanya mengirim field yang dirender halaman tersebut. |
| Request | Satu sumber data per halaman; cache/request-scoped deduplication untuk data yang dibagi komponen. |
| Asset | Asset berversi, cache header/CDN untuk asset publik; media privat memakai URL sementara. |
| JavaScript | Page-level code splitting; chart/library berat tidak masuk bundle awal. |
| Render | Memoization hanya untuk turunan data atau list berat yang sudah terbukti mahal. |
| Kerja berat | Import, export, email massal, generate certificate/report, dan pemrosesan file berjalan melalui queue. |
| Feedback pengguna | Validasi menggunakan bahasa Indonesia dan nama field yang manusiawi; form menampilkan ringkasan serta error inline, memfokuskan error pertama, mengunci double-submit, dan memperlihatkan progres unggah. Aturan bisnis yang menolak aksi menjelaskan penyebab serta tindakan lanjutan. |

## 9. Security, privacy, dan audit

HTTPS, password hashing, session hardening, CSRF protection, role/policy authorization, private object storage, audit log, error log, backup, dan recovery adalah wajib. Client tidak pernah menentukan akses ke data employee lain; akses selalu diperiksa server-side.

## 10. UAT dan definition of done

UAT mencakup akun/role, assignment, Pre-Test, materi dan resume, prerequisite, Post-Test lulus/gagal, retake, improvement, reporting/export, responsive mobile, dan audit. MVP diterima setelah alur inti berjalan di staging tanpa kendala kritis.

Handover: requirement final, sitemap, wireframe, prototype, ERD, API/integration documentation bila diperlukan, source code, panduan Admin, laporan QA/UAT, serta deployment staging/production.
