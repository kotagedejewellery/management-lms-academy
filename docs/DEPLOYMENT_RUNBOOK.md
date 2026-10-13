# Production Deployment Runbook

Dokumen ini adalah prosedur operasional untuk deploy KGJ Academy LMS. Nilai secret hanya disimpan di secret manager atau environment host, tidak di repository maupun image.

## 1. Prasyarat

- Domain sudah mengarah ke load balancer/reverse proxy HTTPS.
- PostgreSQL, Redis, dan bucket object storage privat sudah tersedia, terenkripsi, dan hanya dapat diakses dari aplikasi/worker.
- Service aplikasi, worker queue, dan scheduler (bila nanti dipakai) berjalan terpisah serta memiliki restart policy.
- Image aplikasi dibangun dari commit yang disetujui; jangan deploy `docker-compose.yml` lokal karena file tersebut memakai bind mount, `APP_ENV=local`, dan `APP_DEBUG=true`.

Environment minimum production:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lms.example.com
APP_KEY=<generated-secret>
DB_CONNECTION=pgsql
CACHE_STORE=redis
QUEUE_CONNECTION=database
FILESYSTEM_DISK=s3
FONNTE_TOKEN=<secret>
IMPORT_INITIAL_PASSWORD=<secret>
```

Isi juga kredensial PostgreSQL, Redis, S3-compatible storage, serta mailer sesuai layanan yang disetujui. Batasi kredensial database hanya pada database aplikasi dan kredensial storage hanya pada bucket LMS.

## 2. Deploy aplikasi

1. Jalankan suite test dan build asset dari commit yang akan dirilis.
2. Bangun/pull image artefak yang sudah dipin versinya, lalu jalankan release dengan environment production.
3. Pada container aplikasi, jalankan:

   ```sh
   php artisan migrate --force
   php artisan optimize
   ```

4. Jalankan worker dengan supervisor/container terpisah:

   ```sh
   php artisan queue:work database --sleep=3 --tries=3 --timeout=120
   ```

5. Verifikasi `GET /up` menghasilkan HTTP 200, login administrator berhasil, dan buat satu export laporan untuk memastikan worker memproses job.

Rollback aplikasi memakai image rilis sebelumnya. Jangan melakukan rollback migration destruktif; jika migration sudah dijalankan, pulihkan data melalui prosedur restore atau buat migration korektif yang reviewable.

## 3. Backup dan restore database

Backup terjadwal dijalankan oleh platform database atau host yang memiliki akses least-privilege. Simpan hasilnya terenkripsi di lokasi berbeda dari database utama.

```sh
pg_dump --format=custom --no-owner --file=kgj-lms-YYYYMMDD.dump "$DATABASE_URL"
```

Minimal: backup harian, retensi mengikuti kebijakan HR/legal, dan satu salinan off-site. Catat waktu backup terakhir serta hasilnya di catatan operasi.

Uji restore dilakukan pada database terisolasi, bukan database production:

```sh
createdb kgj_lms_restore_check
pg_restore --clean --if-exists --no-owner --dbname=kgj_lms_restore_check kgj-lms-YYYYMMDD.dump
```

Setelah restore, verifikasi migration, jumlah pengguna/course/enrollment, dan satu laporan CSV pada lingkungan tersebut. Catat tanggal, operator, backup yang dipakai, durasi, dan hasil. Kegagalan restore adalah blocker go-live.

## 4. Monitoring dan respons insiden

- Pantau `/up`, HTTP 5xx, log aplikasi, penggunaan database/Redis/storage, serta failed jobs dan waktu tunggu queue.
- Alert segera bila health check gagal, failed jobs bertambah, backup gagal, atau queue tidak bergerak.
- Untuk data dashboard yang terlambat, periksa worker dan Redis terlebih dahulu; cache dashboard manajemen aman dibangun ulang dari PostgreSQL dan dapat dihapus dengan `php artisan cache:forget dashboard:management`.
- Jangan memasukkan token Fonnte, password impor, atau URL database ke tiket/log.

## 5. Checklist rilis

- [ ] Semua test backend lulus dan build frontend berhasil.
- [ ] `APP_DEBUG=false`, HTTPS aktif, dan secret production tidak memakai nilai contoh.
- [ ] Migration berhasil; worker aktif dan memproses export/notifikasi.
- [ ] `/up` HTTP 200 dari luar jaringan aplikasi.
- [ ] Backup baru tersedia dan restore test memiliki bukti terbaru.
- [ ] Admin memverifikasi login, assignment, lesson, assessment, report export, dan notifikasi pada data non-produksi/akun uji.

Konfigurasi VPS, DNS, secret manager, dan layanan backup berada di luar repository; setiap perubahan di sana harus dicatat bersama rilisnya.
