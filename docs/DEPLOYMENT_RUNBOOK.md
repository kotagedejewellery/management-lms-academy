# Production Deployment Runbook

Dokumen ini adalah prosedur operasional untuk deploy KGJ Academy LMS pada satu VPS Docker yang sudah memiliki Traefik. Nilai secret hanya disimpan di GitHub Secrets atau `.env.production` di VPS—tidak pernah di repository maupun image.

## 1. Prasyarat

- DNS `subdomain.kotagedejewellery.tech` mengarah ke VPS dan Traefik telah menangani port 80/443.
- Network Docker Traefik sudah ada. Nama defaultnya `proxy`; gunakan nilai lain melalui `TRAEFIK_NETWORK` bila konfigurasi Traefik berbeda.
- VPS memiliki Docker Engine, Docker Compose plugin, Git, dan clone repository pada `/opt/apps/sistem-lms-academy/management-lms-academy` (atau GitHub variable `VPS_APP_PATH`).
- Jangan deploy `docker-compose.yml` lokal karena file tersebut memakai bind mount, `APP_ENV=local`, dan `APP_DEBUG=true`.

## 2. Konfigurasi awal VPS

```sh
cd /opt/apps/sistem-lms-academy/management-lms-academy
cp .env.production.example .env.production
nano .env.production
```

Ganti `APP_URL` dan `TRAEFIK_HOST` dengan subdomain final, isi password PostgreSQL yang kuat, `IMPORT_INITIAL_PASSWORD`, dan token Fonnte bila digunakan. `APP_URL` wajib memakai `https://` (bukan `http://`) agar browser tidak memblokir asset Vite sebagai mixed content. Jangan set `ASSET_URL` ke URL HTTP. Pastikan network Traefik tersedia, lalu buat volume persistent satu kali. Perintah ini idempotent dan tidak menghapus data:

```sh
docker network inspect proxy >/dev/null
docker volume create kgj-lms-academy_postgres_data
docker volume create kgj-lms-academy_redis_data
docker volume create kgj-lms-academy_storage_data
```

Bangun image dan hasilkan application key, lalu salin hasilnya ke `APP_KEY=` pada `.env.production`:

```sh
docker compose --env-file .env.production -f compose.production.yml build app
docker compose --env-file .env.production -f compose.production.yml run --rm --no-deps app php artisan key:generate --show
```

Untuk deploy pertama secara manual:

```sh
docker compose --env-file .env.production -f compose.production.yml up -d --build
docker compose --env-file .env.production -f compose.production.yml exec -T app php artisan migrate --force
docker compose --env-file .env.production -f compose.production.yml exec -T app php artisan optimize
docker compose --env-file .env.production -f compose.production.yml exec -T web wget -q -O /dev/null http://127.0.0.1/up
```

`compose.production.yml` hanya menempatkan service `web` pada network `proxy`; PostgreSQL, Redis, PHP-FPM, worker, dan scheduler tidak memiliki port publik.

### Data katalog awal

Deploy otomatis tidak menjalankan seeder katalog agar setiap push tidak mengubah data bisnis. Untuk memasukkan materi awal Customer Service satu kali setelah release berhasil, jalankan secara eksplisit di VPS:

```sh
cd /opt/apps/sistem-lms-academy/management-lms-academy
dc=(docker compose --env-file .env.production -f compose.production.yml)
"${dc[@]}" exec -T app php artisan db:seed --class='Database\Seeders\CustomerServiceDealmakerSeeder' --force
```

Seeder hanya membuat course `CS-DEAL-001`, kategori `Layanan Pelanggan`, 10 modul, dan 20 lesson. Ia tidak membuat akun, struktur organisasi, assignment, atau enrollment. Seluruh record menggunakan `firstOrCreate`, sehingga menjalankan ulang tidak menduplikasi maupun menimpa perubahan Admin pada course tersebut.

## 3. Auto-deploy GitHub Actions

Workflow CI akan men-deploy otomatis hanya ketika seluruh quality check lulus pada push ke `main`. Deploy pertama akan dilewati dengan aman sampai repository dan `.env.production` tersedia di VPS; setelah konfigurasi awal selesai, jalankan ulang workflow tersebut dari GitHub Actions atau lakukan push berikutnya. Tambahkan repository secrets berikut:

- `VPS_HOST`: IP atau hostname VPS.
- `VPS_USER`: user SSH dengan akses ke Docker dan repository deployment.
- `VPS_SSH_KEY`: private key Ed25519 tanpa passphrase.
- `VPS_SSH_KNOWN_HOSTS`: hasil `ssh-keyscan -H <VPS_HOST>` yang telah diverifikasi dari jalur tepercaya.

Opsional: buat GitHub repository variable `VPS_APP_PATH` bila path ini berubah dari `/opt/apps/sistem-lms-academy/management-lms-academy`.

Deploy melakukan `git fetch`, menyamakan source dengan `origin/main`, memastikan volume tersedia, membangun image, menjalankan migration, cache Laravel, restart worker, lalu mengecek `/up`. File `.env.production` tidak disentuh oleh proses tersebut. Jangan mengubah source di VPS secara manual karena perubahan tracked akan ditimpa pada deploy berikutnya.

Rollback source dapat dilakukan dengan revert commit di `main` lalu biarkan workflow deploy kembali. Jangan rollback migration secara destruktif; bila migration sudah berjalan, buat migration korektif atau pulihkan melalui backup.

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
