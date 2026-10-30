# Panduan Uji Pengguna KGJ Academy

Panduan ini dipakai saat uji pengguna nyata. Jalankan pengujian dengan akun sesuai role dan gunakan data/course uji yang telah ditetapkan oleh Super Admin.

## 1. Sebelum mulai

- Pastikan email kerja akun telah diverifikasi dan status akun **Aktif**.
- Gunakan satu akun terpisah untuk setiap role: Employee, Trainer/Mentor, dan Super Admin.
- Jangan mengubah course yang sedang dipakai peserta nyata. Perubahan materi hanya dilakukan pada course uji berstatus **Draf**.
- Jika menemukan masalah, catat: role, menu, langkah, hasil yang diharapkan, hasil yang terjadi, serta screenshot.

## 2. Employee

### Tujuan

Memastikan peserta dapat menyelesaikan pembelajaran yang ditugaskan tanpa melihat data penilaian internal.

### Alur uji

1. Masuk menggunakan akun Employee.
2. Buka **Pembelajaran saya**, lalu buka course yang berstatus aktif.
3. Jika tersedia, kerjakan **Pre-Test** modul dan kirim jawaban.
4. Buka materi/lesson wajib, pelajari, lalu tandai selesai.
5. Kerjakan **Post-Test** setelah seluruh lesson wajib pada modul selesai.
6. Pastikan modul berikutnya dapat dilanjutkan setelah Post-Test dikirim.
7. Setelah seluruh tahap course selesai, periksa status **Menunggu evaluasi akhir**.
8. Jika kelulusan disetujui oleh pihak internal, buka **Sertifikat** bila course menerbitkan sertifikat.

### Hasil yang harus terlihat

| Kondisi | Hasil yang diharapkan |
| --- | --- |
| Pre-Test dikirim | Materi modul terbuka bila Pre-Test digunakan. |
| Lesson wajib selesai | Post-Test modul terbuka bila Post-Test digunakan. |
| Post-Test dikirim | Modul berikutnya dapat dilanjutkan tanpa menunggu penilaian internal. |
| Semua tahap dikirim | Course menampilkan status menunggu evaluasi akhir. |
| Course disetujui | Status course selesai dan sertifikat tersedia bila diaktifkan. |

### Batas akses yang harus diuji

Employee **tidak boleh** melihat skor, nilai kelulusan, lulus/gagal, kunci jawaban, pembahasan, atau riwayat percobaan assessment. Konfirmasi setelah mengirim assessment harus bersifat netral.

## 3. Mentor / Trainer

### Tujuan

Memastikan Trainer hanya mengelola course binaan dan dapat meninjau progres serta hasil evaluasi peserta pada course tersebut.

### Alur uji

1. Masuk menggunakan akun Trainer/Mentor.
2. Dari Dashboard, buka **Course binaan** dan pastikan hanya course yang ditugaskan kepada akun tersebut yang tampil.
3. Buka course binaan:
   - Course **Draf** dapat dikelola sesuai kewenangan Trainer.
   - Course **Dipublikasikan** harus tampil read-only; ini adalah perilaku yang benar.
4. Buka **Progres peserta**.
5. Gunakan filter nama, status, atau course untuk menemukan peserta.
6. Buka detail evaluasi modul untuk meninjau nilai Pre-Test pertama, nilai Post-Test terbaik, dan jumlah percobaan.
7. Periksa ringkasan nilai course:
   - **Rata-rata berjalan** muncul bila belum semua modul evaluatif selesai.
   - **Nilai akhir course** muncul bila seluruh modul yang memiliki pasangan Pre-Test dan Post-Test telah dikirim.
8. Untuk peserta berstatus **Menunggu evaluasi akhir**, lakukan salah satu tindakan pada data uji:
   - setujui kelulusan; atau
   - buka/batalkan kesempatan ulang Post-Test sesuai kebutuhan evaluasi.

### Aturan nilai course

Nilai modul dihitung dengan rumus berikut:

```text
(nilai Pre-Test pertama + nilai Post-Test terbaik) / 2
```

Nilai akhir course adalah rata-rata seluruh modul yang memiliki kedua assessment tersebut. Modul tanpa pasangan Pre-Test dan Post-Test tidak dihitung.

### Batas akses yang harus diuji

Trainer tidak boleh melihat peserta atau mengelola materi pada course yang tidak ditugaskan kepadanya. Trainer menggunakan akun operasional, bukan alur pembelajaran Employee.

## 4. Super Admin

### Tujuan

Memastikan konfigurasi, penugasan, publikasi, pemantauan, dan audit dapat dilakukan secara menyeluruh.

### Alur uji inti

1. Buka **Pengguna** dan pastikan role, status akun, serta struktur organisasi user uji sudah benar.
2. Buka **Kelola course** dan periksa course uji.
3. Pada course Draf, lengkapi materi dan assessment bila diperlukan.
4. Gunakan **Kesiapan publikasi** dan perbaiki setiap item yang ditandai sebelum menerbitkan course.
5. Publikasikan course, lalu pastikan materi/assessment menjadi read-only.
6. Buat **Penugasan** course kepada akun Employee uji.
7. Periksa bahwa course muncul pada **Pembelajaran saya** akun Employee tersebut.
8. Buka **Progres peserta** untuk memantau status, nilai per modul, rata-rata berjalan, atau nilai akhir course.
9. Buka **Laporan** untuk memeriksa nilai course, jumlah modul evaluatif selesai, filter, dan ekspor CSV.
10. Buka **Aktivitas audit** untuk memastikan perubahan administratif penting tercatat.

### Hasil yang harus terlihat

| Tindakan | Hasil yang diharapkan |
| --- | --- |
| Menerbitkan course | Hanya dapat dilakukan bila kesiapan publikasi terpenuhi. |
| Menugaskan course | Course tampil pada akun Employee yang ditetapkan. |
| Memantau progres peserta | Nilai internal hanya tampil untuk role berwenang. |
| Seluruh modul evaluatif selesai | Ringkasan berubah dari rata-rata berjalan menjadi nilai akhir course. |
| Ekspor laporan | File CSV diproses dan hanya dapat diunduh oleh pembuat ekspor. |

## 5. Kriteria lulus UAT

UAT dinyatakan lulus bila:

- Employee dapat menyelesaikan alur pembelajaran tanpa melihat nilai internal.
- Trainer hanya melihat course binaan dan dapat meninjau serta menindaklanjuti evaluasi peserta yang berwenang.
- Super Admin dapat menerbitkan, menugaskan, memantau, mengekspor laporan, dan menelusuri audit.
- Nilai course konsisten antara **Progres peserta**, **Laporan**, dan **CSV**.
- Tidak ada akses lintas role melalui menu maupun URL langsung.
