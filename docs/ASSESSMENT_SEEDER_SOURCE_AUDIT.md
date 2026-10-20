# Audit Sumber Seeder Pre-Test dan Post-Test CS

Tanggal audit: 20 Oktober 2026.

## Keputusan rancangan

Setiap modul Customer Service yang memiliki assessment akan dibuat sebagai dua
assessment native LMS yang berbeda:

| Tahap | Soal | Aturan LMS |
| --- | ---: | --- |
| Pre-Test | 5 soal diagnosis yang tidak mengulang soal Post-Test | Wajib bila dibuat, 1 percobaan, 10 menit, tanpa nilai kelulusan |
| Post-Test | 10 soal penerapan materi | Wajib bila dibuat, 3 percobaan, 15 menit, nilai kelulusan 80 |

Pre-Test dan Post-Test **tidak boleh memakai soal atau opsi yang sama**. Hasil
Pre-Test saat ini dapat dilihat learner setelah dikirim, sehingga penggunaan
ulang akan membuat Post-Test tidak lagi mengukur penguasaan materi secara
mandiri.

Setiap soal akan bernilai satu poin, memiliki empat opsi, tepat satu
`is_correct = true`, dan pembahasan singkat yang merujuk langsung ke materi.
Seeder harus idempoten serta hanya menargetkan course dan modul CS yang telah
diverifikasi; tidak boleh menghapus assessment, attempt, atau data pembelajaran
lain. Assessment yang sudah ada selalu dilewati agar konfigurasi atau riwayat
yang sudah dibuat Admin tidak tertimpa.

## Kesiapan sumber

| Modul | Sumber | Bank soal tersedia | Status seed | Catatan |
| --- | --- | ---: | --- | --- |
| 1. CS Dealmaker Mastery | [Google Doc](https://docs.google.com/document/d/11aswY7nr_WESqEIPq4gzKt__-WEoSmXoqITwA8vHTwc/edit) | 10 | Siap setelah kunci direview | Konsep, tugas, follow-up, dan etika komunikasi lengkap. |
| 2. Product Knowledge | [Google Doc](https://docs.google.com/document/d/1iCXDZpECfl0jSempEF4UFwKFwA2SeIwuzB9JH3-8spA/edit) | 10 | Siap setelah kunci dan data komersial direview | Harga paket, bonus, dan material adalah data yang berpotensi berubah. |
| 3. Lingkungan kerja & karakter customer | [Google Doc](https://docs.google.com/document/d/1VTxpp3SkWEMTs3pfAkCzQqumNFPfrLAX7kU_QBC_rtU/edit) | 6 | Belum lengkap | Perlu empat soal Post-Test tambahan. Profil cabang perlu dikonfirmasi masih berlaku. |
| 4. Strategi penjualan produk high-end | [Google Doc](https://docs.google.com/document/d/1vdqUqHoM8pn_m5tIIm5t-jShrAkZEnsoM4kVvMZh88g/edit) | 10 | Siap setelah review kebijakan | Pernyataan investasi, harga, dan buyback harus diposisikan sebagai kebijakan/modul internal, bukan janji finansial universal. |
| 5. Strategi High Conversion | [Google Doc](https://docs.google.com/document/d/1ell3coiVPDkqpuk53LKmDpRnssu7v1juzby-76M_fxc/edit) | 10 | Siap setelah review kunci | Gunakan angka 5 menit, 10x, dan H+2 sebagai standar pelatihan internal. |
| 6. Career Path CS | [Google Doc](https://docs.google.com/document/d/1OI2G79yAIjJiuWpxZsFh12DfeIWt5wuB9E76LBmWw0Q/edit) | Tidak dapat dibaca | Sengaja dilewati | Atas keputusan pemilik sistem, Modul 6 tidak memiliki Pre-Test atau Post-Test pada seed ini. |
| 7. Detail-Oriented, Budget-Conscious Customer | [Google Doc](https://docs.google.com/document/d/1HJMatilqejtWfkZqdkBOuiZQlQRSJL3b5_3bBcyjOMQ/edit) | 9 | Belum lengkap | Perlu satu soal Post-Test tambahan. |
| 8. Problem Solving & Complaint Handling | [Google Doc](https://docs.google.com/document/d/1VP2jCZKtiddEcHvDuZ-OVif_Xp6LjA14zQHB8y7le-8/edit) | 10 | Siap setelah review kebijakan | Kewenangan kompensasi, modifikasi, dan refund perlu disetujui pemilik kebijakan. |
| 9. The Jewellery Warrior | [Google Doc](https://docs.google.com/document/d/1g1Pk41HVjtIQ-qk_uj6OLgBHYnQPdBc8Ae7nDeniaSA/edit) | 10 | Siap setelah review kunci | Klaim 70% follow-up adalah materi internal, bukan statistik eksternal yang dijanjikan. |
| 10. Sales Excellence & Winning Spirit | [Google Doc](https://docs.google.com/document/d/1ATBrsJZrrNNwUV2ltUY54gHSLHowYKOa75E2TtlD4K4/edit) | 10 | Siap setelah review kunci | Materi dan pilihan skenario konsisten. |

Semua dokumen yang dapat dibaca memisahkan materi dan bagian bertajuk
`SOAL PRETEST POSTEST`, tetapi **tidak ada yang menandai kunci jawaban atau
pembahasan secara eksplisit**. Karena LMS menolak soal tanpa tepat satu kunci,
kunci tidak boleh diasumsikan hanya dari posisi opsi; kunci dan pembahasan akan
ditetapkan dalam data seed berdasarkan isi materi lalu direview pemilik materi.

## Bank soal yang akan diseed

Untuk modul yang sumbernya lengkap, bank data akhir berisi 15 soal per modul:

1. Lima soal Pre-Test baru, berbasis tujuan dan istilah dasar materi. Soal ini
   bersifat diagnostik dan tidak memakai kalimat, opsi, atau skenario yang sama
   dengan Post-Test.
2. Sepuluh soal Post-Test. Gunakan bank soal sumber sebagai dasar; tambahkan
   empat soal untuk Modul 3 dan satu soal untuk Modul 7 agar mencapai sepuluh.
3. Satu pembahasan ringkas per soal yang menjelaskan alasan jawaban benar tanpa
   menambah kebijakan, harga, garansi, atau komitmen yang tidak ada pada materi.

Dengan rancangan tersebut, sembilan modul yang dapat dibaca memerlukan 135
soal. Dari dokumen yang ada, 85 soal Post-Test dapat dipetakan langsung, lima
soal Post-Test perlu ditambahkan, dan 45 soal Pre-Test perlu ditulis sebagai
soal diagnostik baru. Modul 6 sengaja tidak disertakan dalam seed ini.

## Pemeriksaan sebelum implementasi seed

1. Pemilik materi menyetujui kunci jawaban, terutama klaim harga/paket,
   garansi, kebijakan refund, dan karakter cabang.
2. Seeder menemukan course CS dan masing-masing modul berdasarkan kode/judul
   yang stabil, lalu berhenti dengan pesan jelas jika salah satu tidak ada.
3. Seeder hanya membuat assessment yang belum ada. Assessment yang ada—dengan
   atau tanpa riwayat attempt—dilewati agar tidak ada perubahan data saat
   deployment berikutnya.
4. Tambahkan test seeder untuk memastikan setiap assessment berisi jumlah soal
   yang benar dan setiap soal memiliki tepat satu jawaban benar.
