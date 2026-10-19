# Data Uji Lokal

Gunakan satu perintah aman berikut untuk mengisi atau memperbarui data demo lokal:

```powershell
docker compose exec -T app php artisan db:seed --class=Database\Seeders\LocalTestingSeeder --force
```

Seeder ini hanya berjalan pada environment `local` atau `testing`. Semua data demo memakai `firstOrCreate`, `updateOrCreate`, atau `updateOrInsert`, sehingga dapat dijalankan berulang kali tanpa menghapus data lokal yang tidak terkait.

Jangan gunakan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau penghapusan volume Docker untuk pembaruan data demo. Perintah tersebut memang menghapus database atau volume lokal.

Volume PostgreSQL lokal bernama `kgj-lms-academy_postgres_data` dan dideklarasikan sebagai volume eksternal. Karena itu, `docker compose down -v` tidak akan menghapus data database ini; gunakan `docker compose down` biasa untuk menghentikan layanan. Jangan menjalankan `docker volume rm kgj-lms-academy_postgres_data` kecuali memang bermaksud menghapus seluruh database lokal.

Test suite dipaksa memakai SQLite in-memory melalui `phpunit.xml`, sehingga `php artisan test` tidak boleh menulis ke PostgreSQL lokal.

Data demo mencakup organisasi, role/permission, sembilan akun, lima course dengan status berbeda, modul/lesson/resource, learning path dan aturan otomatis, assignment/enrollment/progress, Pre-Test/Post-Test, notifikasi, audit activity, ekspor laporan, dan riwayat import.

Seeder juga menerbitkan dua sertifikat PDF contoh untuk course onboarding yang telah selesai. Sertifikat tersedia melalui menu **Sertifikat** bagi pemiliknya dan LMS Admin.

## Materi Customer Service Dealmaker Mastery

Setelah data demo dasar tersedia, jalankan perintah berikut untuk memasukkan satu course tambahan yang dapat langsung diuji pada learner:

```powershell
docker compose exec -T app php artisan db:seed --class=Database\Seeders\LocalCustomerServiceDealmakerSeeder --force
```

Seeder ini hanya membuat atau memperbarui course `CS-DEAL-001` beserta 10 modul dan 20 lesson (presentasi Canva serta video YouTube). Course diterbitkan, dikelola oleh `trainer@kgj.test`, dan ditugaskan ke `anisa@kgj.test`. Tautan Canva Modul 1–5 disimpan sebagai tautan baca (`/view`); pemilik Canva tetap harus mengatur izin tautan tersebut ke **view** agar dapat dibuka learner. Seeder tidak menghapus atau mengubah course lain.

## Environment lokal yang perlu diisi

Salin `.env.example` menjadi `.env` bila belum ada. Untuk pengujian WhatsApp, isi `FONNTE_TOKEN` dengan token sandbox/akun uji Anda. Untuk pengujian impor user, ganti `IMPORT_INITIAL_PASSWORD` dengan password awal yang aman untuk data lokal. Jangan masukkan kedua nilai tersebut ke Git.
