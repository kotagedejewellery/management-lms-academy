# Panduan Pengguna KGJ Academy

Panduan ini menjelaskan fitur inti KGJ Academy berdasarkan role akun Anda. Menu yang terlihat dapat berbeda karena akses ditentukan oleh tanggung jawab kerja.

## 1. Memulai

1. Buka alamat KGJ Academy yang diberikan LMS Admin.
2. Masuk menggunakan **email kerja atau Employee ID** dan kata sandi.
3. Jika sistem meminta verifikasi email, buka tautan yang dikirim ke alamat email terdaftar. Hubungi LMS Admin bila email tidak diterima atau akun tidak dapat diakses.
4. Gunakan menu profil untuk memperbarui data pribadi, kata sandi, dan pengaturan keamanan akun.

Semua pengguna dapat membuka **Notifikasi**. Sertifikat, progres, dan data pembelajaran pribadi hanya dapat dilihat oleh pemilik akun, kecuali role pengelola yang memang diberi akses organisasi.

## 2. Alur pembelajaran karyawan

Alur utama pembelajaran adalah:

```text
Ditugaskan → Pre-Test (jika diwajibkan) → Materi → Post-Test → Hasil → Sertifikat (jika diaktifkan)
```

1. Buka **Pembelajaran saya** untuk melihat course yang ditugaskan.
2. Pilih course, lalu baca ringkasan, progres, materi wajib, batas waktu, serta status assessment.
3. Kerjakan **Pre-Test** bila diminta. Nilainya menjadi baseline pengetahuan dan tidak menentukan kelulusan course.
4. Selesaikan seluruh lesson wajib. Materi dapat berupa artikel, video, PDF, atau resource unduhan sesuai course.
5. Klik **Tandai selesai** pada lesson setelah mempelajarinya. Sistem menyimpan progres dan posisi pembelajaran yang tersedia.
6. Kerjakan **Post-Test** setelah seluruh lesson wajib selesai. Kelulusan mengikuti passing grade dan batas percobaan yang ditetapkan pada course.
7. Buka hasil assessment untuk melihat skor, status lulus/tidak lulus, nilai terbaik, dan sisa percobaan bila tersedia.
8. Jika course mengaktifkan sertifikat dan Anda lulus, buka **Sertifikat** untuk mengunduh PDF.

### Status yang perlu dipahami

| Status | Arti |
| --- | --- |
| Belum dimulai | Course sudah ditugaskan tetapi belum ada progres. |
| Berlangsung | Ada lesson atau assessment yang sedang dikerjakan. |
| Terkunci | Ada prerequisite course atau lesson wajib yang belum selesai. |
| Selesai | Semua syarat course terpenuhi. |
| Tidak lulus | Post-Test belum mencapai passing grade; retake mengikuti batas percobaan course. |

## 3. Akses menurut role

| Role | Fokus utama | Modul inti |
| --- | --- | --- |
| Employee | Menyelesaikan pembelajaran pribadi | Dashboard, Pembelajaran saya, Jalur belajar saya, Katalog course, Progres saya, Sertifikat, Notifikasi |
| Trainer / Mentor | Mengelola materi course yang ditugaskan | Semua modul Employee, Kelola course yang ditugaskan |
| Manager | Memantau pembelajaran bawahan langsung | Semua modul Employee, Progres tim |
| Management | Memantau hasil organisasi | Semua modul Employee, Laporan |
| LMS Admin | Mengelola operasi pembelajaran | Pengguna, Organisasi, Course, Jalur belajar, Penugasan, Pengumuman, Laporan |
| Super Admin | Mengelola operasi dan audit | Semua modul LMS Admin, Aktivitas audit, Progres tim |

## 4. Panduan Employee

### Dashboard

Gunakan Dashboard untuk melihat course aktif, progres keseluruhan, deadline, assessment yang perlu dikerjakan, dan aksi **Lanjutkan pembelajaran**.

### Pembelajaran saya dan Jalur belajar saya

- Gunakan filter untuk membedakan course wajib, belum dimulai, berlangsung, atau selesai.
- Buka **Jalur belajar saya** untuk melihat urutan course dan prerequisite.
- Course lanjutan tidak dapat dibuka sebelum prerequisite yang diwajibkan selesai.

### Katalog course

Katalog menampilkan course terbit yang relevan dengan divisi atau jabatan Anda. Katalog bukan berarti course otomatis dapat dipelajari; course tetap harus ditugaskan terlebih dahulu.

### Assessment

- Simpan jawaban sebelum berpindah soal atau meninggalkan halaman.
- Timer hanya muncul jika assessment memiliki batas waktu.
- Jangan menutup halaman ketika submit sedang diproses.
- Bila Post-Test belum lulus, baca informasi sisa percobaan dan gunakan retake hanya ketika diizinkan sistem.

## 5. Panduan Trainer / Mentor

Trainer mengelola **hanya course yang ditugaskan** kepadanya. Trainer tidak dapat membuat course baru, mengubah trainer lain, mengatur kategori global, mengelola pengguna, atau membuat penugasan.

### Mengelola materi course

1. Buka **Kelola course**, lalu pilih course yang ditugaskan.
2. Pastikan course berstatus **Draft** sebelum mengubah struktur atau materi.
3. Tambahkan module untuk mengelompokkan materi.
4. Tambahkan lesson pada module dengan tipe yang sesuai:
   - **Artikel**: isi materi ditulis di editor lesson.
   - **PDF**: unggah dokumen atau gunakan sumber yang valid sesuai form.
   - **Video**: unggah file video atau gunakan tautan video yang valid sesuai form.
   - **Resource**: tambahkan file pendukung bila diperlukan.
5. Lesson baru ditempatkan otomatis di akhir modul. Gunakan tombol Naik atau Turun untuk mengubah urutannya, lalu tandai lesson wajib bila menjadi syarat kelulusan.
6. Buat atau perbarui Pre-Test/Post-Test serta pertanyaan sesuai kebutuhan course.
7. Gunakan preview untuk memeriksa materi sebelum dipublikasikan oleh pihak yang berwenang.

Materi course yang sudah **Published** bersifat read-only. Minta LMS Admin atau Super Admin mengembalikannya ke Draft bila perlu revisi.

## 6. Panduan Manager

Gunakan **Progres tim** untuk melihat perkembangan **bawahan langsung** yang tercatat pada struktur organisasi. Anda dapat memantau status course, progres, dan hasil pembelajaran tim yang berada dalam lingkup tersebut.

Manager tidak dapat melihat data employee lain di luar rantai pelaporannya, tidak dapat membuat penugasan, dan tidak memiliki akses laporan organisasi penuh.

## 7. Panduan Management

Gunakan **Laporan** untuk melihat ringkasan pembelajaran organisasi. Terapkan filter yang tersedia sebelum melakukan pencarian atau membuat export.

- Export dibuat di background; tunggu statusnya siap sebelum mengunduh file.
- File export bersifat privat dan hanya dapat diunduh oleh akun yang membuatnya.
- Management tetap dapat mengerjakan pembelajaran pribadi melalui menu learner.

Management tidak dapat mengubah user, organisasi, course, jalur belajar, atau penugasan.

## 8. Panduan LMS Admin

LMS Admin mengelola operasi pembelajaran sehari-hari.

### Pengguna dan organisasi

1. Siapkan struktur **Divisi**, **Jabatan**, dan **Cabang** pada menu Organisasi.
2. Tambahkan user satu per satu atau gunakan impor CSV/XLSX sesuai template resmi.
3. Lengkapi identitas employee, termasuk manager, divisi, jabatan, cabang, tanggal masuk, status akun, dan role.
4. LMS Admin tidak dapat membuat atau menetapkan role Super Admin.

### Course dan jalur belajar

1. Buat kategori course bila diperlukan.
2. Buat course dalam status Draft, isi informasi dasar, thumbnail, target audience, trainer, passing grade, dan pengaturan assessment.
3. Tambahkan module, lesson, resource, serta Pre-Test/Post-Test.
4. Preview lalu publish course ketika isi dan aturan kelulusan siap.
5. Buat jalur belajar; course baru ditempatkan otomatis di akhir path dan dapat dipindahkan dengan tombol Naik atau Turun. Atur prerequisite yang diperlukan.

### Penugasan dan komunikasi

- Gunakan **Penugasan** untuk menugaskan course kepada employee tertentu.
- Gunakan **Penugasan jalur** untuk penugasan path manual atau rule otomatis berdasarkan organisasi.
- Gunakan **Pengumuman** untuk menyampaikan informasi kepada pengguna aktif.
- Gunakan **Laporan** untuk memantau progres dan membuat export.

## 9. Panduan Super Admin

Super Admin memiliki seluruh kemampuan LMS Admin, termasuk pengelolaan role Super Admin. Gunakan **Aktivitas audit** untuk meninjau jejak perubahan dan aktivitas penting.

Aktivitas audit digunakan untuk penelusuran operasional; jangan membagikan informasi audit, export, atau data employee kepada pihak yang tidak berwenang.

## 10. Bantuan cepat

| Situasi | Tindakan |
| --- | --- |
| Tidak dapat login | Pastikan email/Employee ID dan kata sandi benar. Bila akun belum aktif atau email belum terverifikasi, hubungi LMS Admin. |
| Course tidak dapat dibuka | Periksa apakah course sudah ditugaskan dan prerequisite telah selesai. |
| Post-Test terkunci | Selesaikan seluruh lesson wajib terlebih dahulu. |
| Tidak dapat mengubah materi | Pastikan Anda Trainer yang ditugaskan dan course masih Draft. |
| File atau export belum tersedia | Tunggu proses background selesai, lalu refresh halaman terkait. |
| Akses ditolak | Role Anda tidak memiliki akses ke modul tersebut; hubungi LMS Admin bila akses diperlukan untuk tugas kerja. |

## 11. Batas akses penting

- Jangan berbagi akun atau kata sandi.
- Jangan mengunggah materi yang tidak memiliki izin penggunaan.
- Jangan menonaktifkan atau mengubah data employee tanpa kewenangan HR/LMS Admin.
- Hak akses diperiksa oleh sistem pada server; tampilan menu bukan satu-satunya kontrol akses.
