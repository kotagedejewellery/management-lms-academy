# Audit tautan Quilgo untuk evaluasi Customer Service

Tanggal audit: 19 Oktober 2026  
Ruang lingkup: pemetaan sepuluh tautan Quilgo yang diberikan untuk sepuluh modul Customer Service dan kesesuaiannya dengan assessment native KGJ LMS Academy. Audit ini tidak mengubah test maupun data pada Quilgo.

## Kesimpulan

Tautan tersebut **belum dapat langsung menjadi Pre-Test/Post-Test native LMS**. Assessment LMS saat ini adalah assessment internal berbasis `Assessment` → `Question` → `Answer`; hasilnya dicatat sebagai `AssessmentAttempt` dan `AssessmentResponse`. Model maupun form builder tidak mempunyai kolom URL assessment eksternal. Lihat [model Assessment](../app/Models/Assessment.php), [migration assessment](../database/migrations/2026_10_09_000008_create_assessment_tables.php), dan [AssessmentController](../app/Http/Controllers/Admin/AssessmentController.php).

Karena itu, membuka tautan Quilgo sebagai resource eksternal tidak akan mengisi skor, attempt, status lulus, penguncian materi, improvement, laporan, atau sertifikat pada LMS. Ia hanya tepat sebagai tautan evaluasi tambahan sampai ada integrasi hasil yang dirancang dan diuji.

Selain itu, terdapat **10 entri tetapi hanya 9 URL unik**: entri 2 dan 3 identik. Satu URL per modul juga belum cukup untuk skenario dua assessment per modul (Pre-Test dan Post-Test): skenario tersebut membutuhkan 20 assessment berbeda atau keputusan bahwa hanya salah satu tahap yang memakai Quilgo.

## Pemetaan materi dan tautan

| Modul | Materi | Tautan yang diterima | Status pemetaan |
| --- | --- | --- | --- |
| 1 | CS Dealmaker Mastery | `https://app.quilgo.com/t/vxikcUqtwrzTRijh` | Kandidat untuk Modul 1; jenis Pre/Post belum disebutkan. |
| 2 | Product Knowledge | `https://app.quilgo.com/t/DjaNNqsam83zhCrw` | Kandidat untuk Modul 2; jenis Pre/Post belum disebutkan. |
| 3 | Work Environment & Customer Character Analysis | `https://app.quilgo.com/t/DjaNNqsam83zhCrw` | **Duplikat Modul 2**; perlu URL test Modul 3 yang berbeda atau konfirmasi bahwa satu test memang sengaja dipakai bersama. |
| 4 | High-End Product Sales Strategy | `https://app.quilgo.com/t/ieSNIfNdjGZrHF2z` | Kandidat untuk Modul 4; jenis Pre/Post belum disebutkan. |
| 5 | High Convertion Strategy | `https://app.quilgo.com/t/stUanfidHi9NlAaD` | Kandidat untuk Modul 5; jenis Pre/Post belum disebutkan. |
| 6 | Customer Service Career Path | `https://app.quilgo.com/t/SfIgbozFrnPFVj9E` | Kandidat untuk Modul 6; jenis Pre/Post belum disebutkan. |
| 7 | Mastering Detailed Oriented & Budget Conscious | `https://app.quilgo.com/t/HfMnOle9m94D18nO` | Kandidat untuk Modul 7; jenis Pre/Post belum disebutkan. |
| 8 | Excellent Problem Solving & Complaint Handling | `https://app.quilgo.com/t/InReiKgAna54QzRc` | Kandidat untuk Modul 8; jenis Pre/Post belum disebutkan. |
| 9 | The Jewellery Warrior | `https://app.quilgo.com/t/gT2fm4ScQ86kowv6` | Kandidat untuk Modul 9; jenis Pre/Post belum disebutkan. |
| 10 | Sales Excellent & Winning Spirit | `https://app.quilgo.com/t/9N62YD2Vs4qPy5tX` | Kandidat untuk Modul 10; jenis Pre/Post belum disebutkan. |

Pemeriksaan otomatis tidak dapat membuka endpoint `app.quilgo.com/t/...`, sehingga judul, jumlah soal, kunci jawaban, batas waktu, deadline, dan mode akses setiap test tidak dapat diverifikasi tanpa akses pemilik pada dashboard Quilgo. Status tersebut tidak berarti tautan rusak.

## Dampak produk dan keamanan

- Quilgo menjelaskan bahwa group link dapat berjalan dalam mode **Public** (peserta mendaftar sendiri) atau **Restricted** (autentikasi); undangan email juga tersedia. Untuk data karyawan, gunakan Restricted/invitation dan pastikan email peserta sama dengan email kerja di LMS. [Panduan Quilgo](https://quilgo.com/hub/quilgo-platform-complete-guide)
- Quilgo memiliki timer, deadline, auto-submit, tampilan skor, serta data proctoring. Semua opsi ini harus ditetapkan pada setiap test, bukan diasumsikan dari tautan. [Panduan Quilgo](https://quilgo.com/hub/quilgo-platform-complete-guide)
- Quilgo menyediakan hasil/ekspor di platformnya. Integrasi Zapier mereka dapat mengirim informasi hasil, termasuk skor, tetapi mekanismenya adalah integrasi eksternal dan dokumentasi mereka menyebut polling sekitar 15 menit. Ini belum ada pada LMS dan tidak cukup aman untuk langsung dijadikan dasar kelulusan tanpa kontrak data, autentikasi webhook, pemetaan user, serta idempotensi. [Quilgo + Zapier](https://quilgo.com/blog/zapier)
- Jangan menanamkan test dengan `iframe` sebelum membuktikan bahwa Quilgo mengizinkannya. Buka sebagai tab baru untuk jalur sementara agar kebijakan frame/CSP pihak ketiga tidak memutus proses test.

## Rekomendasi

### Opsi yang direkomendasikan: assessment native sebagai sumber kebenaran

1. Minta pemilik Quilgo menetapkan untuk setiap modul: mana Pre-Test, mana Post-Test, dan mengirimkan URL unik yang benar untuk Modul 3.
2. Ekspor/berikan bank soal, opsi jawaban, jawaban benar, bobot, dan penjelasan dari setiap test yang akan dipakai.
3. Masukkan bank soal itu ke Assessment Builder LMS sebagai Pre-Test dan/atau Post-Test. Dengan cara ini, semua aturan produk yang sudah ada—gating materi, attempt, passing grade, improvement, report, dan sertifikat—tetap akurat.
4. Gunakan Quilgo hanya untuk kebutuhan proctoring tambahan yang memang disetujui; hasilnya direkonsiliasi oleh Admin, bukan otomatis mengubah kelulusan.

### Opsi integrasi (fase terpisah)

Jika Quilgo wajib menjadi mesin test utama, bangun adapter integrasi khusus setelah keputusan kebijakan. Minimalnya: identity mapping berdasarkan email kerja, URL/invitation individual, endpoint terautentikasi untuk menerima skor, validasi signature, penyimpanan ID test/attempt eksternal, idempotensi, state gagal/retry, audit, serta uji data nyata. Setelah itu barulah skor eksternal dapat dipetakan ke aturan completion LMS.

## Checklist yang harus dikonfirmasi pemilik Quilgo

Untuk setiap URL yang akan diaktifkan, catat di dashboard Quilgo:

1. Judul test dan modul yang dinilai.
2. Status Pre-Test atau Post-Test.
3. Mode akses Restricted/invitation, daftar email peserta, dan masa berlaku/deadline.
4. Jumlah soal, randomisasi, jawaban benar, bobot, passing grade, serta durasi.
5. Apakah kamera/screen proctoring diaktifkan dan dasar persetujuan/pemberitahuan karyawan.
6. Cara skor/hasil disimpan: tetap di Quilgo, ekspor manual, atau integrasi fase berikutnya.

Tanpa checklist ini dan URL unik Modul 3, data belum siap dihubungkan sebagai assessment resmi Customer Service.
