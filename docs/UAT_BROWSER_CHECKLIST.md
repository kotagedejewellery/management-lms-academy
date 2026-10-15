# Checklist UAT Browser

Dokumen ini adalah bukti kerja UAT manual sebelum rilis. Pada 15 Oktober 2026, penguji mengonfirmasi seluruh skenario di bawah telah diuji dan lulus. Artefak screenshot/video belum dilampirkan ke repository.

## Informasi sesi

| Item | Isi penguji |
|---|---|
| Tanggal dan waktu | 15 Oktober 2026; waktu tidak dicatat |
| Penguji | Pengguna/proses owner KGJ Academy |
| URL/build yang diuji | Lingkungan lokal; detail build tidak dicatat |
| Browser dan versi | Tidak dicatat |
| Akun/role yang digunakan | Seluruh role sesuai skenario UAT |
| Bukti screenshot/video | Dikonfirmasi penguji; artefak belum dilampirkan |

## Matriks perangkat

Lakukan alur inti pada Chrome desktop 1440 px dan 1024 px, lalu browser mobile/emulation 390 px dan tablet 768 px. Catat bug, URL, langkah reproduksi, dan bukti pada kolom catatan.

| Area | Skenario penerimaan | Desktop | Mobile | Catatan/bukti |
|---|---|---|---|---|
| Login | Email/Employee ID, password, lupa password, pesan gagal, dan logout | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Role | Employee, Manager, Trainer, LMS Admin, dan Super Admin hanya melihat menu serta data yang diizinkan | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Navigasi | Sidebar/drawer, menu aktif, focus keyboard, dan tombol kembali bekerja | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Dashboard | Statistik, CTA, course terkunci, empty/loading/error state tidak menyesatkan | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Learning | Pre-Test, player video/PDF/article, resource privat, previous/next, resume, dan Post-Test | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Assessment | Draft, refresh, timer jika aktif, submit, attempt/retake, hasil, dan akses review | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Admin | User, organisasi, course/lesson/assessment, path, assignment, laporan, ekspor, dan audit | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Audit | Perubahan field aman menampilkan nilai sebelum/sesudah tanpa password/token | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Sertifikat | Akses pemilik/Admin dan unduh PDF privat | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |
| Aksesibilitas | Tab/Shift+Tab, Enter/Space, focus terlihat, label form, dialog Escape, kontras, dan target sentuh | [x] Lulus | [x] Lulus | Dikonfirmasi penguji |

## Integrasi dan gangguan jaringan

| Skenario | Hasil yang diharapkan | Status | Catatan/bukti |
|---|---|---|---|
| Upload media nyata | Format/ukuran valid diterima; file tidak valid mendapat pesan jelas; resource tetap privat | [x] Lulus | Dikonfirmasi penguji |
| WhatsApp Fonnte | Dengan `FONNTE_TOKEN` sandbox yang valid, notifikasi assignment/deadline dikirim sekali; inbox in-app tetap ada | [x] Lulus | Dikonfirmasi penguji |
| Koneksi terputus | Simpan progres/draft dan submit menampilkan kegagalan yang jelas serta aksi coba lagi; tidak ada duplikasi attempt | [x] Lulus | Dikonfirmasi penguji |
| Export/report | Status queued/ready/failed jelas; unduhan hanya tersedia untuk pemilik yang berwenang | [x] Lulus | Dikonfirmasi penguji |

## Keputusan rilis

**Status UAT: LULUS** berdasarkan konfirmasi penguji. Tidak ada kegagalan P0/P1 yang dilaporkan. Tambahkan artefak screenshot/video bila diperlukan untuk kebutuhan audit formal di kemudian hari.
