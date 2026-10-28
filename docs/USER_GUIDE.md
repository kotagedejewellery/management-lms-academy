# Panduan Pengguna KGJ Academy

> Panduan MVP ini menjelaskan alur dan menu yang tersedia pada versi sistem saat ini. Menu serta data yang dapat dibuka mengikuti role akun Anda.

## Daftar isi

1. [Memulai](#1-memulai)
2. [Matriks akses role](#2-matriks-akses-role)
3. [Alur pembelajaran Employee](#3-alur-pembelajaran-employee)
4. [Panduan per role](#4-panduan-per-role)
5. [Bantuan cepat](#5-bantuan-cepat)

## 1. Memulai

### Persyaratan akses

Sebelum menggunakan KGJ Academy, pastikan akun Anda:

- berstatus aktif;
- menggunakan email kerja yang telah diverifikasi; dan
- memiliki role yang ditetapkan oleh LMS Admin.

### Langkah masuk

1. Buka alamat KGJ Academy yang diberikan LMS Admin.
2. Masuk menggunakan **email kerja atau Employee ID** dan kata sandi.
3. Selesaikan verifikasi email apabila sistem meminta verifikasi.
4. Gunakan menu profil untuk memperbarui data pribadi atau kata sandi.
5. Gunakan **Lupa kata sandi** pada halaman masuk apabila tidak dapat mengakses akun.

Semua pengguna dapat membuka **Notifikasi**. Jangan membagikan akun atau kata sandi kepada orang lain.

## 2. Matriks akses role

| Role | Fokus utama | Menu inti MVP |
| --- | --- | --- |
| Employee / Learner | Menyelesaikan pembelajaran pribadi | Dashboard, Pembelajaran saya, Jalur belajar saya, Katalog course, Progres saya, Sertifikat, Notifikasi |
| Manager Tim | Memantau bawahan langsung serta belajar sebagai peserta | Semua menu Employee, Progres tim |
| Trainer / Mentor | Mengelola course binaan dan evaluasi pesertanya | Dashboard, Course binaan, Progres peserta, Notifikasi |
| Management / Owner | Meninjau hasil organisasi dan evaluasi akhir | Dashboard, Progres peserta, Laporan, Notifikasi |
| LMS Admin / HR | Menjalankan operasi pembelajaran | Dashboard, Kelola course, Kelola jalur belajar, Sertifikat, Pengguna, Organisasi, Penugasan, Penugasan jalur, Pengumuman, Progres peserta, Laporan, Notifikasi |
| Super Admin | Mengelola operasi penuh dan audit | Semua area LMS Admin, Progres tim, Aktivitas audit |

> Akses selalu diperiksa oleh server. Membuka URL secara langsung tidak memberi akses di luar role atau cakupan data akun Anda.

## 3. Alur pembelajaran Employee

Pre-Test dan Post-Test hanya muncul pada modul yang memang dikonfigurasi memiliki assessment.

```text
Course ditugaskan
  → Pre-Test modul (bila ada)
  → materi wajib modul
  → Post-Test modul (bila ada)
  → modul berikutnya
  → evaluasi akhir internal
  → disetujui / kesempatan ulang bila diperlukan
  → sertifikat (bila diaktifkan)
```

### Langkah pembelajaran

1. Buka **Pembelajaran saya**, lalu pilih course yang berstatus aktif.
2. Kerjakan **Pre-Test** jika tersedia. Pre-Test menjadi baseline, hanya satu kali percobaan, dan tidak menentukan kelulusan.
3. Pelajari semua lesson wajib pada modul. Tandai lesson selesai setelah dipelajari.
4. Kerjakan **Post-Test** jika tersedia setelah lesson wajib modul selesai.
5. Setelah Post-Test dikirim, modul berikutnya dapat dibuka tanpa menunggu penilaian internal.
6. Setelah semua materi dan assessment wajib selesai, course berstatus **Menunggu evaluasi akhir**.

### Kerahasiaan hasil penilaian

Employee hanya menerima konfirmasi bahwa assessment telah dikirim. Employee tidak dapat melihat:

- skor atau nilai kelulusan;
- status lulus/gagal;
- sisa atau riwayat percobaan;
- kunci jawaban; dan
- pembahasan jawaban.

Hasil dikelola secara internal. Sertifikat menjadi pemberitahuan positif apabila course disetujui dan penerbitan sertifikat diaktifkan.

### Arti status course

| Status | Arti | Tindakan Employee |
| --- | --- | --- |
| Aktif | Course masih dapat dikerjakan. | Lanjutkan tahap yang terbuka. |
| Terkunci | Ada prerequisite atau tahap wajib sebelumnya. | Selesaikan syarat yang disebutkan sistem. |
| Menunggu evaluasi akhir | Seluruh tahap wajib telah dikirim. | Tunggu arahan pihak internal. |
| Selesai | Course telah disetujui. | Buka Sertifikat bila tersedia. |
| Perlu diulang | Evaluasi internal memerlukan tindak lanjut. | Ikuti arahan Trainer, Management, atau LMS Admin. |

### Menu pembelajaran pribadi

| Menu | Kegunaan |
| --- | --- |
| Pembelajaran saya | Membuka dan melanjutkan course yang ditugaskan. |
| Jalur belajar saya | Melihat urutan course dan prerequisite. |
| Katalog course | Melihat course terbit yang relevan; course tetap harus ditugaskan agar dapat dipelajari. |
| Progres saya | Melihat progres pribadi tanpa nilai assessment. |
| Sertifikat | Mengunduh sertifikat pribadi yang telah diterbitkan. |

## 4. Panduan per role

### 4.1 Employee / Learner

Gunakan alur pada [bagian 3](#3-alur-pembelajaran-employee) untuk menyelesaikan course.

- Gunakan filter pada **Pembelajaran saya** untuk menemukan course aktif, belum dimulai, atau selesai.
- Ikuti urutan pada **Jalur belajar saya** untuk course wajib.
- Jangan menutup halaman ketika assessment sedang dikirim.
- Hubungi pihak internal bila course menunggu evaluasi akhir atau perlu kesempatan ulang Post-Test.

Employee tidak dapat mengelola user, organisasi, materi, penugasan, laporan organisasi, maupun aktivitas audit.

### 4.2 Manager Tim

Manager memiliki seluruh fitur Employee untuk dirinya sendiri, serta akses ke **Progres tim**.

1. Buka **Progres tim**.
2. Gunakan pencarian atau filter status untuk menemukan assignment bawahan langsung.
3. Pantau progres, status course, dan tenggat yang memerlukan perhatian.
4. Berikan arahan operasional kepada anggota tim bila diperlukan.

Manager hanya dapat melihat bawahan langsung yang tercatat pada struktur organisasi. Manager tidak melihat skor angka atau jawaban assessment, serta tidak dapat mengubah materi atau membuat penugasan.

### 4.3 Trainer / Mentor

Trainer adalah akun operasional dan hanya dapat mengakses course yang ditugaskan sebagai **Course binaan**.

Dashboard Trainer menampilkan jumlah peserta pada course binaan yang **Menunggu evaluasi akhir**. Gunakan tombol **Evaluasi peserta** untuk langsung meninjau mereka; nilai detail tetap berada di halaman **Progres peserta**.

#### Mengelola materi

1. Buka **Course binaan**, lalu pilih course yang ditugaskan.
2. Tambahkan atau perbarui modul, lesson, resource, serta Pre-Test/Post-Test sesuai materi.
3. Pastikan setiap assessment yang digunakan memiliki soal.
4. Pastikan setiap soal memiliki **tepat satu jawaban benar**.
5. Gunakan pratinjau untuk memeriksa susunan materi.

Struktur materi dan assessment hanya dapat diubah saat course berstatus **Draf**. Trainer tidak membuat course baru, tidak mengelola kategori global, user, organisasi, jalur belajar, atau penugasan.

#### Meninjau peserta

1. Buka **Progres peserta**.
2. Cari course binaan atau peserta yang perlu ditinjau.
3. Tinjau nilai Pre-Test/Post-Test per modul, serta rata-rata berjalan atau nilai akhir course. Setiap modul evaluatif berbobot sama: nilai Pre-Test pertama dan nilai terbaik Post-Test masing-masing berkontribusi 50%.
4. Saat course berstatus **Menunggu evaluasi akhir**, pilih tindakan yang sesuai:
   - setujui kelulusan; atau
   - buka atau batalkan kesempatan ulang Post-Test.

Trainer hanya dapat meninjau peserta pada course binaannya. Pengujian alur Employee dilakukan dengan akun Employee terpisah.

### 4.4 Management / Owner

Management berfokus pada evaluasi internal organisasi dan tidak memiliki alur pembelajaran pribadi.

Dashboard Management menampilkan jumlah assignment yang **Menunggu evaluasi akhir** dan menaut langsung ke **Progres peserta**. Nilai detail tetap ditinjau per peserta dan per course.

#### Evaluasi akhir

1. Buka **Progres peserta**.
2. Gunakan filter course atau status untuk menemukan peserta yang memerlukan evaluasi.
3. Tinjau hasil assessment, peningkatan, dan riwayat percobaan sesuai kebutuhan evaluasi.
4. Untuk course berstatus **Menunggu evaluasi akhir**, setujui kelulusan atau kelola kesempatan ulang Post-Test.

#### Laporan

1. Buka **Laporan**.
2. Gunakan filter pencarian, status, course, dan divisi.
3. Tinjau **Nilai akhir** atau **Rata-rata berjalan** course beserta jumlah modul evaluatif yang telah selesai. Nilai ini memakai rata-rata setara dari Pre-Test pertama dan Post-Test terbaik pada setiap modul yang memiliki keduanya.
4. Pilih **Ekspor CSV** apabila diperlukan.
5. Tunggu status ekspor siap, lalu unduh file dari daftar ekspor Anda.

File ekspor bersifat privat dan hanya dapat diunduh oleh akun yang membuatnya. Management tidak mengelola user, organisasi, materi, penugasan, sertifikat, atau audit log.

### 4.5 LMS Admin / HR

LMS Admin menjalankan operasi pembelajaran sehari-hari.

Jika terdapat assignment **Menunggu evaluasi akhir**, Dashboard LMS Admin memprioritaskan tombol **Evaluasi peserta**; bila tidak ada, tombol utama tetap menuju **Kelola penugasan**.

#### Mengelola organisasi dan user

1. Pada **Organisasi**, kelola divisi, jabatan, dan cabang terlebih dahulu.
2. Pada **Pengguna**, tambahkan atau perbarui data employee: manager, struktur organisasi, status akun, dan role.
3. Gunakan impor user bila data sudah mengikuti template sistem.

LMS Admin tidak dapat membuat atau menetapkan role Super Admin.

#### Membuat dan menerbitkan course

1. Buat course sebagai **Draf** dan lengkapi informasi dasarnya.
2. Tentukan audience, trainer, durasi, dan aturan penyelesaian.
3. Tambahkan modul dan lesson. Artikel wajib memiliki isi; PDF/video wajib memiliki satu sumber yang valid.
4. Tambahkan assessment hanya bila diperlukan:
   - Pre-Test adalah baseline satu kali tanpa nilai kelulusan.
   - Post-Test memiliki nilai kelulusan dan batas percobaan.
5. Periksa **Kesiapan publikasi**, perbaiki semua item yang ditandai, lalu gunakan pratinjau.
6. Pilih **Publikasikan course** setelah seluruh persyaratan terpenuhi.

Setelah terbit, struktur materi dan assessment bersifat read-only. Kembalikan course ke status Draf sebelum merevisi struktur atau materi.

#### Penugasan dan pemantauan

- **Penugasan**: menugaskan course kepada employee tertentu.
- **Penugasan jalur**: menugaskan learning path secara manual atau berdasarkan aturan organisasi.
- **Pengumuman**: menyampaikan informasi kepada pengguna aktif.
- **Progres peserta** dan **Laporan**: memantau hasil, menyetujui evaluasi akhir, atau mengelola kesempatan ulang Post-Test.
- **Sertifikat**: melihat dan mengunduh sertifikat yang telah diterbitkan.

### 4.6 Super Admin

Super Admin memiliki semua kemampuan LMS Admin, termasuk pengelolaan role Super Admin.

- Gunakan **Aktivitas audit** untuk menelusuri perubahan administratif dan aktivitas penting.
- Gunakan **Progres tim** untuk memantau bawahan langsung apabila struktur pelaporan tersedia.
- Gunakan hak akses penuh hanya untuk kebutuhan operasional yang sah.

Audit log, ekspor, sertifikat, dan data employee adalah informasi internal. Jangan membagikannya kepada pihak yang tidak berwenang.

## 5. Bantuan cepat

| Situasi | Tindakan |
| --- | --- |
| Tidak dapat masuk | Periksa email/Employee ID dan kata sandi; gunakan Lupa kata sandi atau hubungi LMS Admin. |
| Email belum diverifikasi | Buka tautan pada email kerja atau minta LMS Admin memeriksa data akun. |
| Course tidak terlihat | Pastikan course telah ditugaskan, diterbitkan, dan prerequisite dipenuhi. |
| Materi atau Post-Test terkunci | Selesaikan tahap wajib sebelumnya pada modul atau course tersebut. |
| Assessment sudah dikirim | Tunggu evaluasi internal; skor tidak ditampilkan pada akun Employee. |
| Perlu mengulang Post-Test | Hubungi Trainer, Management, atau LMS Admin yang menangani evaluasi. |
| Tidak dapat mengubah materi | Pastikan Anda Trainer yang ditugaskan atau LMS Admin, dan course berstatus Draf. |
| Ekspor belum dapat diunduh | Tunggu pemrosesan background selesai, lalu buka kembali Laporan. |
| Menerima akses ditolak | Role atau cakupan data Anda tidak mengizinkan tindakan tersebut; hubungi LMS Admin bila akses diperlukan. |
