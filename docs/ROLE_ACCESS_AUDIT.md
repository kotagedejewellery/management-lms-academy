# Audit akses role

Tanggal audit: 16 Oktober 2026  
Ruang lingkup: role, permission, route server, dashboard, sidebar, dan batas data yang dirender. Dokumen ini menggambarkan implementasi saat ini, bukan target baru.

## Kesimpulan singkat

Enam role aktif sudah memiliki pemisahan akses yang kuat pada server, bukan hanya pada sidebar. Seluruh halaman aplikasi utama mensyaratkan akun yang aktif dan email terverifikasi; endpoint pembelajaran juga memeriksa enrollment, status course, serta prerequisite di server.

| Status | Arti |
| --- | --- |
| Sesuai | Sesuai dengan mandat role dan dibatasi di server. |
| Terbatas dengan benar | Tidak tersedia bagi role karena memang bukan tanggung jawabnya. |
| Perlu keputusan | Sistem aman, tetapi kebijakan produk belum dinyatakan eksplisit. |
| Gap | Perilaku saat ini terlalu luas atau belum memenuhi ruang lingkup role yang disepakati. |

## Fondasi akses yang berlaku untuk semua role

| Kontrol | Status | Implementasi saat ini |
| --- | --- | --- |
| Login, akun aktif, dan email terverifikasi | Sesuai | Halaman aplikasi utama berada di middleware `auth`, `verified`, `active`, dan `audit`. |
| Profil, keamanan akun, dan tampilan | Sesuai | Semua pengguna aktif dapat mengelola profilnya sendiri; pengaturan keamanan tetap memerlukan verifikasi email. |
| Notifikasi | Sesuai | Hanya notifikasi milik pengguna yang sedang masuk yang dapat dibaca. |
| Sertifikat | Sesuai | Learner hanya melihat sertifikatnya sendiri; pengelola penugasan dapat melihat sertifikat organisasi. Unduhan tetap memakai otorisasi server. |
| Resource course | Sesuai | Resource privat hanya dapat diakses pengelola course atau learner dengan enrollment yang sah. |
| Katalog course | Sesuai | Hanya course terbit yang generik atau cocok dengan divisi/jabatan pengguna yang muncul. Course tanpa penugasan tidak dapat dipelajari. |

## Matriks menu dan modul

Legenda: ✓ tersedia; — tidak tersedia karena bukan mandat; `✓*` tersedia dengan batas khusus.

Matriks ini menggambarkan menu yang dirender di sidebar. Endpoint Pembelajaran saya, Jalur belajar saya, Katalog course, dan Progres saya tetap aman digunakan lewat URL langsung oleh semua akun aktif; endpoint tersebut hanya mengembalikan data akun sendiri. Role operasional tidak melihat menu tersebut agar navigasi tetap fokus pada pekerjaan administrasi.

| Menu / modul | Super Admin | LMS Admin | Trainer | Manager | Employee | Management |
| --- | --- | --- | --- | --- | --- | --- |
| Dashboard sesuai peran | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Pembelajaran saya | — | — | ✓ | ✓ | ✓ | ✓ |
| Jalur belajar saya | — | — | ✓ | ✓ | ✓ | ✓ |
| Katalog course | — | — | ✓ | ✓ | ✓ | ✓ |
| Progres saya | — | — | ✓ | ✓ | ✓ | ✓ |
| Kelola course dan materi | ✓ | ✓ | ✓* | — | — | — |
| Kelola jalur belajar | ✓ | ✓ | — | — | — | — |
| Pengguna | ✓ | ✓* | — | — | — | — |
| Organisasi | ✓ | ✓ | — | — | — | — |
| Penugasan course / jalur | ✓ | ✓ | — | — | — | — |
| Pengumuman | ✓ | ✓ | — | — | — | — |
| Laporan | ✓ | ✓ | — | — | — | ✓ |
| Progres tim | ✓ | — | — | ✓* | — | — |
| Aktivitas audit | ✓ | — | — | — | — | — |
| Sertifikat | ✓* | ✓* | ✓ | ✓ | ✓ | ✓ |
| Notifikasi dan pengaturan akun | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

`✓*` pada Trainer berarti hanya course yang ditugaskan kepadanya.  
`✓*` pada LMS Admin untuk Pengguna berarti tidak dapat membuat atau menetapkan role Super Admin.  
`✓*` pada Manager berarti hanya bawahan langsung berdasarkan `manager_id`.  
`✓*` pada Sertifikat untuk Super Admin/LMS Admin berarti dapat melihat semua sertifikat; role lain hanya miliknya sendiri.

## Pemetaan per role

### Super Admin

| Area | Status | Perilaku aktual |
| --- | --- | --- |
| Dashboard operasional | Sesuai | Melihat ringkasan penugasan dan operasi, sama seperti LMS Admin. |
| Pengelolaan organisasi, pengguna, course, path, assignment, pengumuman, laporan | Sesuai | Memiliki seluruh permission melalui bypass `super-admin`. |
| Audit activity | Sesuai | Satu-satunya role yang dapat membuka jejak audit. |
| Progres tim | Sesuai | Dapat melihat laporan bawahan langsung bila data manager tersedia. |
| Pembelajaran pribadi dan katalog | Sesuai | Disembunyikan agar navigasi fokus pada operasi, tetapi endpoint self-service tetap aman bila dibuka langsung dan hanya memuat data akun sendiri. |
| Dashboard keamanan khusus | Perlu keputusan | Belum ada dashboard khusus health, queue, atau konfigurasi keamanan. Ini bukan penghalang operasi MVP; tambahkan hanya bila Super Admin perlu memantau infrastruktur dari aplikasi. |

### LMS Admin

| Area | Status | Perilaku aktual |
| --- | --- | --- |
| Operasi LMS | Sesuai | Mengelola pengguna, struktur organisasi, course, jalur belajar, penugasan, pengumuman, dan laporan. |
| Manajemen user | Sesuai | Dapat membuat/mengubah user, tetapi tidak dapat menetapkan Super Admin. |
| Audit activity | Terbatas dengan benar | Audit dibatasi untuk Super Admin untuk menjaga pemisahan kontrol. |
| Progres tim | Terbatas dengan benar | Tidak memperoleh `view-team`; laporan organisasi tersedia melalui modul Laporan. |
| Pembelajaran pribadi | Sesuai | Tidak ditampilkan untuk role operasional, tetapi endpoint self-service tetap dapat dibuka dan hanya memuat data akun sendiri. |

### Trainer / Mentor

| Area | Status | Perilaku aktual |
| --- | --- | --- |
| Course yang ditugaskan | Sesuai | Daftar course, materi, resource, thumbnail, dan assessment dibatasi oleh relasi `course_trainers`. Trainer tidak dapat mengubah trainer course. |
| Course baru | Sesuai | Hanya LMS Admin/Super Admin (`manage-enrollments`) yang dapat membuka dan mengirim pembuatan course. Trainer mengelola course yang telah ditugaskan. |
| Kategori course global | Sesuai | Hanya LMS Admin/Super Admin (`manage-enrollments`) yang dapat membuka, membuat, dan mengubah taxonomy kategori global. |
| Learner pribadi | Sesuai | Tetap dapat memakai Pembelajaran saya, Jalur belajar saya, katalog, progres, sertifikat, dan notifikasi. |
| User, organisasi, penugasan, pengumuman, laporan, audit | Terbatas dengan benar | Tidak ditampilkan dan ditolak oleh otorisasi server. |

### Manager

| Area | Status | Perilaku aktual |
| --- | --- | --- |
| Dashboard tim | Sesuai | Dashboard Manager menampilkan konteks bawahan langsung. |
| Progres tim | Sesuai dengan kebijakan saat ini | Query dibatasi ke `users.manager_id = pengguna masuk`; manager tidak dapat melihat user di luar rantai pelaporannya. |
| Learner pribadi | Sesuai | Mendapat seluruh pengalaman belajar pribadi yang sama dengan Employee. |
| Laporan organisasi | Terbatas dengan benar | Tidak memiliki `view-reports`. |
| Cakupan divisi / cabang | Perlu keputusan | Bila “tim yang diizinkan” berarti divisi atau cabang penuh, fitur agregasi itu belum ada. Implementasi saat ini konsisten pada definisi yang lebih aman: bawahan langsung saja. |

### Employee

| Area | Status | Perilaku aktual |
| --- | --- | --- |
| Dashboard learner | Sesuai | Menampilkan pembelajaran aktif, assessment, deadline, progres, dan course yang terkunci prerequisite. |
| My Learning, path, katalog, detail course, lesson, assessment | Sesuai | Hanya enrollment dan materi yang memenuhi aturan pembelajaran yang dapat diakses. |
| Assessment | Sesuai | Pre-Test/Post-Test mengikuti mandatory rule, prerequisite lesson, batas attempt, dan passing grade di server. |
| Sertifikat, progres, notifikasi, profil | Sesuai | Data selalu milik sendiri. |
| Administrasi, laporan, team progress, audit | Terbatas dengan benar | Tidak tersedia di sidebar dan terproteksi server-side. |

### Management / Owner

| Area | Status | Perilaku aktual |
| --- | --- | --- |
| Dashboard eksekutif | Sesuai | Melihat metrik organisasi dan ringkasan pembelajaran. |
| Laporan | Sesuai | Melihat laporan organisasi dan dapat membuat export privat miliknya. Pencarian dan pagination dijalankan di server. |
| Learner pribadi | Sesuai | Tetap memiliki menu pembelajaran pribadi, sertifikat, progres, dan notifikasi. |
| Operasi LMS | Terbatas dengan benar | Tidak dapat mengubah user, course, organisasi, assignment, atau audit. |
| Sertifikat organisasi | Terbatas dengan benar | Hanya sertifikat miliknya yang ditampilkan; bila Owner harus mengaudit sertifikat organisasi, ini perlu permission baru yang eksplisit. |

## Perbaikan yang dituntaskan

| Prioritas awal | Perbaikan | Hasil |
| --- | --- | --- |
| P1 | Trainer dapat membuat course sendiri | Ditutup. `courses.create` dan `courses.store` kini memerlukan `manage-enrollments`; CTA pembuatan juga tidak dirender untuk Trainer. |
| P1 | Trainer dapat mengubah kategori course global | Ditutup. Seluruh endpoint kategori kini memerlukan `manage-enrollments`; tautan pengelolaan kategori disembunyikan dari Trainer. |

## Gap dan keputusan yang masih terbuka

| Prioritas | Temuan | Dampak | Rekomendasi |
| --- | --- | --- | --- |
| P2 | Role dan permission bersifat fixed | Tidak ada UI untuk membuat role/permission/custom role; perubahan harus melalui seeder/deployment. | Tegaskan kebijakan satu set role tetap, atau bangun modul RBAC jika HR membutuhkan role kustom. |
| P2 | Data model mengizinkan multi-role, UI user hanya menetapkan satu role | Jika multi-role dibuat langsung di database, sidebar dapat gabungan dan dashboard memakai prioritas operasional > trainer > manager > management > learner. | Rekomendasi: tetapkan satu role operasional per akun sampai ada desain role switcher dan aturan dashboard multi-role. |
| P2 | Cakupan Manager hanya direct report | Tidak ada roll-up divisi/cabang. | Pertahankan bila manager memang mengelola bawahan langsung; tambahkan scope organisasi eksplisit bila diperlukan. |
| P3 | Super Admin memakai dashboard operasi | Tidak ada dashboard administratif/infrastruktur terpisah. | Tidak perlu untuk MVP; pertimbangkan bila operasi aplikasi membutuhkan pemantauan queue, health, atau konfigurasi. |
| P3 | Management tidak melihat sertifikat semua orang | Owner hanya dapat melihat sertifikat sendiri meskipun dapat melihat laporan. | Tambahkan permission `view-certificates` hanya jika ada kebutuhan audit sertifikat. |

## Bukti pemeriksaan

- Permission dan role seeded: `database/seeders/RolePermissionSeeder.php`.
- Bypass Super Admin dan batas trainer: `app/Models/User.php`.
- Route dan middleware: `routes/web.php`.
- Navigasi yang benar-benar dirender per permission: `resources/js/components/AppSidebar.vue`.
- Pemilihan dashboard per role: `app/Support/DashboardResolver.php`.
- Proteksi course dan konten: `app/Http/Controllers/Admin/CourseController.php`, `CourseContentController.php`, dan `AssessmentController.php`.
- Validasi regresi akses dilakukan pada 16 Oktober 2026: 33 test lulus, 264 assertion (`DashboardTest`, `TeamProgressTest`, `CourseManagementTest`, `UserManagementTest`, `ReportTest`, dan `CourseLibraryTest`).
- Validasi perbaikan P1: 16 test lulus, 83 assertion pada `CourseManagementTest`, termasuk penolakan Trainer dan izin LMS Admin untuk kategori.
- Kontrak akses role: 2 test lulus, 144 assertion pada `RoleAccessContractTest`. Test ini mengunci mode dashboard serta akses route kritis untuk Super Admin, LMS Admin, Trainer, Manager, Employee, dan Management.
- Kontrak menu sidebar: 6 test frontend lulus pada `resources/js/navigation/sidebar.test.ts`. Test menjalankan sumber navigasi yang dipakai `AppSidebar` dan mengunci item yang terlihat untuk keenam role.

## Batas audit

Audit ini memeriksa kode dan automated test, bukan konfigurasi akun produksi atau data aktual pada VPS. Keputusan authoring Trainer dan ownership kategori telah diterapkan berdasarkan rekomendasi audit; route server, dashboard, dan menu sidebar kini memiliki regression contract. Gap tersisa merupakan keputusan kebijakan produk, bukan bypass akses yang diketahui.
