# Design UI/UX — KGJ Academy

## 1. Arah yang mengikat

Mode produk adalah **Operate**: employee menyelesaikan pembelajaran, sedangkan Admin/Manager mengelola dan memantau. Rujukan visual yang disetujui adalah visual board dalam brief KGJ Academy, bukan template dashboard generik.

- Identitas: hitam, putih/off-white, gold sebagai aksen terukur; tidak memakai gold sebagai warna teks kecil berkontras rendah.
- Karakter: modern, tenang, presisi, card bersih, negative space cukup, citra jewellery hanya sebagai konten/hero yang bermakna.
- Desktop: sidebar permanen, search/topbar, area konten berhirarki.
- Mobile: shell ringkas; sidebar menjadi drawer/navigation yang mudah disentuh.
- Istilah UI: selalu gunakan Pre-Test dan Post-Test, bukan Quiz/Kuis.

Mock data pada board hanya contoh. Tidak boleh menyalin nama employee, skor, atau tanggal contoh ke production.

## 2. Information architecture

### Learner

Dashboard; My Learning; Learning Paths; Course Library; Pre-Test & Post-Test; My Progress; Certificates; Announcements; Profile.

### Trainer / Mentor

Dashboard; Course binaan; Progres peserta; Notifikasi. Trainer menggunakan akun operasional terpisah dari akun Employee/Learner.

### Management / Owner

Dashboard; Progres peserta; Laporan; Notifikasi. Management menggunakan area evaluasi internal dan tidak melihat navigasi learner.

### Admin/Manager

Dashboard; Users; Divisions; Positions; Branches; Courses; Modules & Lessons; Learning Path Builder; Pre-Test Builder; Post-Test Builder; Enrollment; Reports; Notifications; Settings.

Manager melihat subset menu/data sesuai policy, termasuk CTA pembelajaran pribadi pada Dashboard bila memiliki assignment aktif. Navigation tidak menggantikan authorization server-side.

## 3. Layar minimum dan responsif

| Layar | Desktop | Mobile |
|---|---|---|
| Login | Panel brand/image dan form berdampingan. | Form prioritas; artwork diperkecil/tidak menghambat login. |
| Learner dashboard | Sidebar; kartu metric; continue learning; path; deadline. | Drawer; kartu metric 2 kolom; path horizontal/stack yang mudah dipindai. |
| Course detail | Metadata, module, CTA lanjut, tab. | Konten satu kolom; CTA tetap jelas; tab dapat scroll. |
| Lesson player | Daftar module/lesson di samping player. | Daftar menjadi drawer/accordion; player aspect-ratio stabil. |
| Pre/Post-Test | Soal, timer, progress, navigasi jelas. | Satu soal fokus; tombol previous/next/submit selalu dapat dijangkau. |
| Konfirmasi assessment Employee | Konfirmasi pengiriman netral dan CTA kembali ke materi. Tidak menampilkan skor, status lulus/gagal, percobaan, atau kunci jawaban. | Pesan dan CTA jelas di atas; tanpa detail evaluasi. |
| Admin dashboard/report | Filter dan data table/card/graph. | Filter drawer; metric cards; tabel bergeser menjadi card summary atau horizontal scroll terkontrol. |

Target desain diuji pada lebar 390px, 768px, 1024px, dan 1440px. Layout bersifat mobile-first; gunakan breakpoint hanya ketika struktur benar-benar berubah.

## 4. Sistem komponen awal

`AppShell`, `Sidebar`, `MobileNav`, `Topbar`, `PageHeader`, `MetricCard`, `ProgressBar`, `StatusBadge`, `CourseCard`, `LearningPathStep`, `LessonList`, `AssessmentQuestion`, `TestProgress`, `ResultSummary`, `FilterBar`, `DataTable`, `Pagination`, `EmptyState`, `ErrorState`, `ConfirmDialog`, dan `Toast`.

Pada course builder, formulir lesson membedakan sumber `Unggah file` dan `Gunakan URL`; label wajib menyatakan tipe serta batas file. Modul, lesson, dan soal assessment baru ditempatkan otomatis di akhir daftar; ubah posisinya melalui aksi Naik/Turun yang berlabel dan nonaktif di batas daftar, bukan dengan input nomor manual. Presentasi Canva memakai tautan view-only atau embed dan menjelaskan bahwa tautan editor tidak dapat digunakan; learner mendapat preview serta tautan buka di Canva sebagai fallback. Thumbnail course dapat diganti atau dihapus sebelum disimpan; pesan validasinya menjelaskan tindakan pemulihan, bukan nama rule teknis. Pesan error form terhubung ke kontrol yang bermasalah, diumumkan kepada pembaca layar, dan memfokuskan error pertama. Pemilihan Trainer / Mentor menampilkan akun aktif ber-role tersebut sebagai control pilihan yang jelas; jika kosong, arahkan Admin ke pengelolaan pengguna. Lesson yang ada dapat diubah atau mengganti file dari aksi yang dapat diakses keyboard. Saat course bukan Draft, tampilkan status read-only beserta arahan untuk kembali ke Draft, bukan formulir yang tampak dapat disimpan.

Setiap status internal memiliki teks dan icon/shape pendamping: Completed, In Progress, Not Started, Passed, Not Passed, Overdue, Locked. Warna saja tidak cukup. Hasil assessment Employee selalu berupa konfirmasi netral; outcome evaluasi, nilai, percobaan, dan kunci jawaban tidak dirender atau dikirim ke browser Employee. Assessment builder menyatakan bahwa Pre-Test adalah baseline satu percobaan, sedangkan nilai kelulusan dan percobaan ulang hanya berlaku untuk Post-Test. Employee dapat mengikuti attempt Post-Test pertama; Trainer atau role internal berwenang hanya dapat membuka setiap kesempatan ulang berikutnya saat course berada dalam evaluasi akhir. Setiap soal menampilkan status kunci jawaban, dapat diperluas untuk detail, dan dapat diubah hanya selama assessment masih aman diedit. Form course menampilkan checklist kesiapan publikasi yang menaut langsung ke materi atau soal yang belum siap. Setiap form panjang menampilkan ringkasan error yang dapat ditindaklanjuti, error inline yang terhubung ke field, lalu memusatkan viewport dan fokus keyboard ke error pertama. Tombol submit mengunci double-submit dan menyatakan objek yang sedang diproses; upload menampilkan nama file serta progres. Pesan aturan bisnis tampil sebagai penjelasan tindakan yang bisa dilakukan Admin, bukan error HTTP atau key validasi teknis.

## 5. Aksesibilitas dan interaksi

- Kontras teks dan control memenuhi WCAG AA; fokus keyboard terlihat.
- Seluruh control mempunyai label, error yang dapat dibaca, dan target sentuh minimal nyaman.
- Timer assessment tidak hanya mengandalkan warna/audio; peringatan ditulis jelas.
- Modal/drawer mengelola focus trap dan dapat ditutup keyboard.
- Video memiliki caption/transcript bila tersedia; PDF memiliki judul dan akses download sesuai izin.
- Jangan memakai hover sebagai satu-satunya cara mengakses tindakan penting.

## 6. Kinerja UI

- Route code splitting untuk admin, reporting, player, dan chart.
- Lazy load image bawah fold, modal, resource preview, serta grafik yang belum terlihat.
- Thumbnail memakai ukuran turunan dan `width`/`height` untuk mencegah layout shift.
- Course list/report selalu paginated; loading menggunakan skeleton seperlunya, bukan menahan halaman kosong.
- Props Inertia minimum per halaman; child component tidak melakukan fetch duplikat.
- Memoization hanya setelah profiling mengidentifikasi list/perhitungan berat.

## 7. State yang wajib dirancang

Employee-facing Dashboard, My Learning, My Progress, and Learning Path must not expose score, pass/fail, retry count, or retry limit. After all course stages are submitted, use the neutral state “Menunggu evaluasi akhir Management”. A pending retake opens a preparation screen; it begins only when Employee chooses “Mulai Post-Test”, and an authorized internal user may cancel it before it begins.

Loading, empty, offline/error, unauthorized, no assigned learning, lesson locked, deadline overdue, assessment in progress, time expired, assessment submitted, menunggu evaluasi akhir Management, kesempatan ulang dibuka, attempt limit reached (internal), passed/not passed (internal), export queued/ready/failed, dan file access denied.

## 8. Validasi implementasi

Sebelum UI dikirim untuk UAT: cek keyboard, screen-reader label, kontras, overflow pada 390px, sidebar/drawer, assessment flow, resume player, data table filter/pagination, dan loading/error states. Ketika implementasi UI dimulai, jalankan detector Impeccable pada file UI yang berubah dan lakukan screenshot desktop/mobile sebagai bukti review.
