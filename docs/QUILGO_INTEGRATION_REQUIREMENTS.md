# Kebutuhan Integrasi Quilgo untuk KGJ LMS Academy

_Diperiksa pada 19 Oktober 2026. Harga dan kemampuan vendor dapat berubah; konfirmasi kembali pada halaman harga Quilgo sebelum pembelian._

## Kesimpulan

Quilgo dapat menjadi mesin pengerjaan dan proctoring test, sementara KGJ LMS
tetap menjadi sumber data penugasan, status kelulusan, sertifikat, dan laporan.
Untuk LMS kustom ini, jalur integrasi resmi yang terdokumentasi publik adalah
**Quilgo ↔ Zapier ↔ webhook KGJ LMS**. Dokumentasi yang diperiksa menyebut
Zapier sebagai lapisan otomasi utama Quilgo dan secara eksplisit mencantumkan
sistem kustom melalui Zapier Webhooks; dokumentasi tersebut tidak menjelaskan
REST API publik, kredensial API, atau tanda tangan webhook langsung dari Quilgo.
Jangan mengasumsikan ada API langsung sebelum Quilgo mengonfirmasikannya secara
tertulis.

Sumber: [Quilgo Complete Guide](https://quilgo.com/hub/quilgo-platform-complete-guide),
[Quilgo + Zapier](https://quilgo.com/blog/quilgo-zapier-introducing-full-automation-for-smarter-assessments).

## Yang perlu disiapkan

### 1. Akun dan konfigurasi Quilgo

- Akun **Quilgo Platform** yang aktif, bukan sekadar URL test publik.
- Buat atau verifikasi setiap test yang akan dipakai, termasuk judul yang
  konsisten dengan course/modul LMS dan ambang kelulusan yang disepakati.
- Gunakan **Restricted mode** untuk test resmi internal. Mode ini membuat test
  ditugaskan secara individual; admin memasukkan nama dan email responden.
  Group link pada mode ini dipakai untuk autentikasi, bukan pendaftaran bebas.
- Gunakan alamat email kerja yang sama persis di Quilgo dan KGJ LMS agar hasil
  dapat dipetakan ke akun learner yang benar.
- Atur jadwal mulai, durasi, deadline, dan bila diperlukan auto-submit. Pilih
  camera, screen, atau force tracking hanya bila kebijakan perusahaan serta
  pemberitahuan privasi kepada karyawan sudah disetujui.
- Untuk pengiriman melalui Quilgo, admin dapat memilih responden dan mengirim
  invitation email langsung dari menu `Send tests`.

Sumber: [Restricted mode dan pengiriman email](https://quilgo.com/hub/quilgo-platform-complete-guide).

### 2. Akun Zapier dan alur otomasi

Siapkan akun Zapier terpisah; biaya Zapier **bukan** bagian dari harga Quilgo
dan harus diperiksa pada paket Zapier yang dipilih. Buat dua Zap:

1. **Penugasan dari LMS ke Quilgo (opsional pada fase pertama)**
   - Event LMS: learner mendapat assignment Pre/Post-Test.
   - Aksi: kirim nama, email kerja, key assessment, dan URL/callback yang
     diperlukan melalui webhook Zapier.
   - Aksi Quilgo: buat/assign invitation untuk test yang tepat.

2. **Hasil dari Quilgo ke LMS (wajib untuk kelulusan otomatis)**
   - Trigger Quilgo: test selesai/hasil tersedia.
   - Aksi: Zapier Webhooks mengirim hasil ke endpoint HTTPS KGJ LMS.
   - Data minimum: ID hasil eksternal, ID/key test, email responden, status
     selesai, skor/nilai maksimum atau persentase, waktu submit, dan Trust
     Score bila organisasi memang akan memakainya.

Dokumentasi Quilgo menyatakan integrasi Zapier dapat membuat/menugaskan test
dari sistem eksternal dan mengembalikan skor, Trust Score, serta detail
penyelesaian ke sistem yang terhubung. Sumber:
[Quilgo + Zapier](https://quilgo.com/blog/quilgo-zapier-introducing-full-automation-for-smarter-assessments).

### 3. Perubahan yang diperlukan di KGJ LMS

Implementasi aplikasi perlu memiliki:

- Konfigurasi assessment eksternal: provider `quilgo`, key/URL test, tipe
  `pre` atau `post`, passing grade, batas attempt, dan status aktif.
- Record assignment/attempt sebelum learner diarahkan ke Quilgo, dengan status
  seperti `assigned`, `in_progress`, `waiting_result`, `passed`, `failed`, dan
  `sync_failed`.
- Endpoint HTTPS khusus penerimaan hasil dari Zapier, dilindungi secret yang
  disimpan hanya di environment production. Endpoint harus memvalidasi payload,
  cocokkan test dan email, serta menolak hasil duplikat menggunakan ID hasil
  eksternal (idempotensi).
- Audit log, penyimpanan payload aman/minimal, retry, dan tombol rekonsiliasi
  Admin untuk hasil yang gagal disinkronkan.
- Pemetaan nilai Quilgo ke aturan LMS: attempt, kelulusan course, prerequisite,
  laporan, dan sertifikat. Jangan menerbitkan sertifikat sebelum hasil tervalidasi.
- Tampilan learner yang transparan: `Belum dimulai` → `Dikerjakan di Quilgo`
  → `Menunggu hasil` → `Lulus/Tidak lulus`.

LMS tidak sebaiknya meng-iframe halaman Quilgo: test proctored dapat meminta
izin camera/screen dan harus dibuka langsung pada domain Quilgo. Ini adalah
rekomendasi integrasi berdasarkan alur izin kamera/layar yang didokumentasikan
Quilgo, bukan batasan teknis yang dinyatakan sebagai larangan oleh Quilgo.

### 4. Keamanan dan privasi

- Jangan menyimpan password Quilgo atau kredensial Zapier di database atau
  frontend; gunakan secrets deployment.
- Uji dengan satu akun karyawan uji sebelum assignment massal.
- Dokumentasikan pihak pemroses data, tujuan pemrosesan camera/screen,
  retensi hasil/proctoring, akses Admin, dan prosedur koreksi hasil. Quilgo
  menyatakan data dienkripsi saat transit dan saat tersimpan serta menyediakan
  dokumen DPA dan Privacy Policy; organisasi tetap perlu mengevaluasi
  kesesuaian kebijakan internal dan hukum yang berlaku.

Sumber: [Quilgo FAQ](https://quilgo.com/hub/faq).

## Apakah gratis?

Tidak untuk operasional berkelanjutan. Paket `Free trial` tercantum $0/bulan
namun dibatasi **20 test trial**, sehingga tepat untuk proof of concept, bukan
rollout karyawan. Harga platform yang tampil saat pemeriksaan:

| Paket Quilgo Platform | Harga bayar tahunan | Harga bayar bulanan | Kapasitas peserta test bersamaan | Test |
| --- | ---: | ---: | ---: | --- |
| Free trial | $0 | $0 | tidak dijadikan kapasitas produksi | 20 test trial |
| Starter | $24/bulan | $30/bulan | 10 | Tidak terbatas |
| Pro | $60/bulan | $75/bulan | 50 | Tidak terbatas |
| Ultra | $116/bulan | $145/bulan | 500 | Tidak terbatas |
| Custom | hubungi Quilgo | hubungi Quilgo | sesuai kontrak | Tidak terbatas |

Kapasitas tersebut adalah *test taker seats*, yaitu jumlah orang yang dapat
mengerjakan test pada saat bersamaan—bukan jumlah seluruh akun employee di LMS.
Starter hanya masuk akal bila gelombang test dibatasi maksimal 10 orang;
untuk peluncuran bersama lebih dari 10 employee gunakan setidaknya Pro.

Sumber: [halaman harga resmi Quilgo](https://quilgo.com/pricing).

## Keputusan yang dibutuhkan sebelum pembangunan

1. Tentukan apakah evaluasi yang sudah ada adalah satu Pre dan satu Post untuk
   seluruh course CS, atau evaluasi per modul. Model per modul membutuhkan
   pasangan test dan aturan kelulusan yang jelas untuk setiap modul.
2. Perbaiki pemetaan daftar Quilgo: URL Modul 2 dan Modul 3 saat ini identik;
   konfirmasi apakah memang reuse yang disengaja atau URL Modul 3 keliru.
3. Tentukan jumlah maksimum peserta yang akan test serentak, agar paket Quilgo
   dapat dipilih berdasarkan kapasitas nyata.
4. Setujui kebijakan proctoring: apakah cukup timer dan score, atau juga
   camera, screen, Force tracking, serta Trust Score.
5. Setujui apakah hasil Quilgo menjadi satu-satunya dasar kelulusan otomatis
   atau tetap memerlukan review Admin untuk skor/Trust Score tertentu.

## Rekomendasi pelaksanaan

Mulai dengan pilot satu Pre-Test dan satu Post-Test course CS melalui paket
trial (maksimum 20 test) atau Starter bila pilot melewati batas itu. Gunakan
Restricted mode, email kerja yang sudah diverifikasi, dan Zapier webhook hasil
ke endpoint LMS. Setelah sinkronisasi, retry, idempotensi, dan pelaporan lulus
diuji pada data uji, baru perluas ke seluruh modul dan batch employee.
