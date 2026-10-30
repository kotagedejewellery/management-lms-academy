# Audit akses role

Tanggal pembaruan: 30 Oktober 2026
Ruang lingkup: role operasional, dashboard, sidebar, route server, dan cakupan data.

## Keputusan akses

KGJ Academy mendukung empat role operasional. Akses selalu diperiksa server-side; menyembunyikan menu bukan pengamanan utama.

| Role | Dashboard | Cakupan utama |
| --- | --- | --- |
| Employee / Learner | Pembelajaran saya | Course, progres, jalur belajar, sertifikat, dan notifikasi pribadi tanpa nilai assessment. |
| Trainer / Mentor | Course yang saya kelola | Course binaan dan Progres peserta untuk course yang ditugaskan. |
| LMS Admin / HR | Ringkasan operasional | Pengguna, organisasi, course, jalur belajar, penugasan, progres peserta, laporan, serta evaluasi akhir. |
| Super Admin | Ringkasan operasional | Semua kemampuan LMS Admin, termasuk pengelolaan role Super Admin dan aktivitas audit. |

Role **Manager Tim** dan **Management / Owner** telah dipensiunkan. Relasi atasan organisasi (`manager_id`) tetap ada untuk data organisasi, tetapi tidak memberikan akses aplikasi maupun dashboard Progres tim.

## Batas akses utama

| Area | Employee | Trainer | LMS Admin | Super Admin |
| --- | --- | --- | --- | --- |
| Pembelajaran pribadi | Ya | Tidak | Tidak | Tidak |
| Course binaan | Tidak | Hanya course ditugaskan | Semua | Semua |
| Progres peserta dan evaluasi akhir | Tidak | Hanya course ditugaskan | Semua | Semua |
| Laporan dan ekspor | Tidak | Tidak | Semua | Semua |
| Pengguna, organisasi, dan penugasan | Tidak | Tidak | Ya | Ya |
| Aktivitas audit | Tidak | Tidak | Tidak | Ya |

## Bukti regresi

- `RoleAccessContractTest` mengunci role aktif, dashboard, dan route kritis.
- `RoleRetirementMigrationTest` mengunci pemindahan akun retired tanpa menghapus user, enrollment, atau relasi atasan.
- `TrainerProgressTest` mengunci scope Trainer dan akses organisasi LMS Admin.
- `sidebar.test.ts` mengunci item sidebar yang terlihat untuk keempat role.

## Batas audit

Audit ini memeriksa source code dan test otomatis. Konfigurasi role serta akun aktual di production harus diverifikasi setelah migrasi rilis dijalankan.
