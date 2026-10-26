# Audit Sumber Seeder Pre-Test dan Post-Test CS

Tanggal audit: 26 Oktober 2026.

## Kesimpulan

Setiap Google Doc yang dapat diakses memiliki **satu** heading `SOAL PRETEST
POSTEST` diikuti satu bank soal. Tidak ada bagian Pre-Test dan Post-Test yang
terpisah. Karena itu, dokumen sumber tidak mendukung pemisahan dua bank soal
berbeda secara otomatis.

Seeder menerapkan target 10 soal pada setiap Pre-Test dan Post-Test untuk
modul 1–5 serta 7–10. Pre-Test menggunakan prompt yang berbeda dari
Post-Test agar tetap berfungsi sebagai baseline. Karena dokumen tidak memuat
dua bank terpisah maupun kunci jawaban, tambahan Pre-Test dan kunci jawaban
harus ditinjau pemilik materi sebelum ditetapkan sebagai standar final.

Modul 6 tetap tidak disemai karena sumbernya tidak tersedia bagi akun yang
melakukan audit.

## Inventaris sumber primer

| Modul | Sumber | Heading assessment pada sumber | Bank soal tunggal | Kelayakan 10 soal per assessment | Temuan |
| --- | --- | --- | ---: | --- | --- |
| 1. CS Dealmaker Mastery | [Google Doc](https://docs.google.com/document/d/11aswY7nr_WESqEIPq4gzKt__-WEoSmXoqITwA8vHTwc/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Nomor 1-10, setiap soal memiliki opsi A-D. |
| 2. Product Knowledge | [Google Doc](https://docs.google.com/document/d/1iCXDZpECfl0jSempEF4UFwKFwA2SeIwuzB9JH3-8spA/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Urutan 1-10 lengkap; nomor 3 tertulis `3 Seorang ...` tanpa titik. |
| 3. Lingkungan kerja & Analisis Karakter Customer | [Google Doc](https://docs.google.com/document/d/1VTxpp3SkWEMTs3pfAkCzQqumNFPfrLAX7kU_QBC_rtU/edit) | `SOAL PRETEST POSTEST` | 6 | Dilengkapi di seeder | Sumber hanya nomor 1-6; soal pelengkap perlu validasi pemilik materi. |
| 4. Strategi Penjualan Produk High-End | [Google Doc](https://docs.google.com/document/d/1vdqUqHoM8pn_m5tIIm5t-jShrAkZEnsoM4kVvMZh88g/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Nomor 1-10, setiap soal memiliki opsi A-D. |
| 5. Strategi High Konversi | [Google Doc](https://docs.google.com/document/d/1ell3coiVPDkqpuk53LKmDpRnssu7v1juzby-76M_fxc/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Nomor 1-10, setiap soal memiliki opsi A-D. |
| 6. Karir Path CS | [Google Doc](https://docs.google.com/document/d/1OI2G79yAIjJiuWpxZsFh12DfeIWt5wuB9E76LBmWw0Q/edit) | Tidak dapat dibaca | — | Lewati | Google Drive mengembalikan 404; tidak ada sumber yang dapat diaudit. |
| 7. Detail-Oriented, Budget-Conscious Customer | [Google Doc](https://docs.google.com/document/d/1HJMatilqejtWfkZqdkBOuiZQlQRSJL3b5_3bBcyjOMQ/edit) | `SOAL PRETEST POSTEST` | 9 | Dilengkapi di seeder | Sumber hanya nomor 1-9; soal pelengkap perlu validasi pemilik materi. |
| 8. Excellent Problem Solving & Complaint Handling | [Google Doc](https://docs.google.com/document/d/1VP2jCZKtiddEcHvDuZ-OVif_Xp6LjA14zQHB8y7le-8/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Nomor 1-10, setiap soal memiliki opsi A-D. |
| 9. The Jewellery Warrior | [Google Doc](https://docs.google.com/document/d/1g1Pk41HVjtIQ-qk_uj6OLgBHYnQPdBc8Ae7nDeniaSA/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Nomor 1-10, setiap soal memiliki opsi A-D. |
| 10. Sales Excellence & Winning Spirit | [Google Doc](https://docs.google.com/document/d/1ATBrsJZrrNNwUV2ltUY54gHSLHowYKOa75E2TtlD4K4/edit) | `SOAL PRETEST POSTEST` | 10 | Siap disalin ke Pre dan Post | Nomor 1-10, setiap soal memiliki opsi A-D. |

## Kunci jawaban

Tidak satu pun dari sembilan dokumen yang dapat dibaca menyertakan penanda
eksplisit `kunci jawaban`, `jawaban benar`, atau pembahasan. Seeder LMS tetap
memerlukan tepat satu jawaban benar per soal. Kunci yang sudah ada di seeder
harus ditinjau terhadap materi atau dikonfirmasi pemilik materi; audit ini
tidak dapat menjadikan posisi opsi sebagai kunci.

## Status implementasi

1. Modul 1–5 serta 7–10 memiliki 10 Pre-Test dan 10 Post-Test.
2. Setiap soal memiliki empat pilihan dan tepat satu kunci jawaban.
3. Modul 6 tetap dilewati hingga sumber materi atau bank soal tersedia.
4. Test regresi memeriksa jumlah soal, kunci jawaban tunggal, dan prompt
   Pre-Test/Post-Test yang tidak sama dalam modul yang sama.
