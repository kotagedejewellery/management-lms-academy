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

### Admin/Manager

Dashboard; Users; Divisions; Positions; Branches; Courses; Modules & Lessons; Learning Path Builder; Pre-Test Builder; Post-Test Builder; Enrollment; Reports; Notifications; Settings.

Manager melihat subset menu/data sesuai policy. Navigation tidak menggantikan authorization server-side.

## 3. Layar minimum dan responsif

| Layar | Desktop | Mobile |
|---|---|---|
| Login | Panel brand/image dan form berdampingan. | Form prioritas; artwork diperkecil/tidak menghambat login. |
| Learner dashboard | Sidebar; kartu metric; continue learning; path; deadline. | Drawer; kartu metric 2 kolom; path horizontal/stack yang mudah dipindai. |
| Course detail | Metadata, module, CTA lanjut, tab. | Konten satu kolom; CTA tetap jelas; tab dapat scroll. |
| Lesson player | Daftar module/lesson di samping player. | Daftar menjadi drawer/accordion; player aspect-ratio stabil. |
| Pre/Post-Test | Soal, timer, progress, navigasi jelas. | Satu soal fokus; tombol previous/next/submit selalu dapat dijangkau. |
| Result | Score, status, attempt, improvement, tindakan berikutnya. | Status dan tindakan di atas; detail di bawah. |
| Admin dashboard/report | Filter dan data table/card/graph. | Filter drawer; metric cards; tabel bergeser menjadi card summary atau horizontal scroll terkontrol. |

Target desain diuji pada lebar 390px, 768px, 1024px, dan 1440px. Layout bersifat mobile-first; gunakan breakpoint hanya ketika struktur benar-benar berubah.

## 4. Sistem komponen awal

`AppShell`, `Sidebar`, `MobileNav`, `Topbar`, `PageHeader`, `MetricCard`, `ProgressBar`, `StatusBadge`, `CourseCard`, `LearningPathStep`, `LessonList`, `AssessmentQuestion`, `TestProgress`, `ResultSummary`, `FilterBar`, `DataTable`, `Pagination`, `EmptyState`, `ErrorState`, `ConfirmDialog`, dan `Toast`.

Setiap status memiliki teks dan icon/shape pendamping: Completed, In Progress, Not Started, Passed, Not Passed, Overdue, Locked. Warna saja tidak cukup.

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

Loading, empty, offline/error, unauthorized, no assigned learning, lesson locked, deadline overdue, assessment in progress, time expired, attempt limit reached, passed, not passed/retake, export queued/ready/failed, dan file access denied.

## 8. Validasi implementasi

Sebelum UI dikirim untuk UAT: cek keyboard, screen-reader label, kontras, overflow pada 390px, sidebar/drawer, assessment flow, resume player, data table filter/pagination, dan loading/error states. Ketika implementasi UI dimulai, jalankan detector Impeccable pada file UI yang berubah dan lakukan screenshot desktop/mobile sebagai bukti review.
