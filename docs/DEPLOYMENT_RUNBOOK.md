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

Ganti `APP_URL` dan `TRAEFIK_HOST` dengan subdomain final, isi password PostgreSQL yang kuat, `IMPORT_INITIAL_PASSWORD`, dan token Fonnte bila digunakan. Isi juga `MAIL_PASSWORD` dengan SMTP API key Resend serta gunakan alamat pengirim yang telah diverifikasi di Resend; reset password dan verifikasi akun baru bergantung pada SMTP ini. `APP_URL` wajib memakai `https://` (bukan `http://`) agar browser tidak memblokir asset Vite sebagai mixed content. Jangan set `ASSET_URL` ke URL HTTP. Pastikan network Traefik tersedia, lalu buat volume persistent satu kali. Perintah ini idempotent dan tidak menghapus data:

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

PHP-FPM (`app`), `worker`, dan `scheduler` juga terhubung ke network Docker `outbound` tanpa port publik. Network ini hanya menyediakan akses keluar yang diperlukan untuk layanan eksternal seperti SMTP; PostgreSQL dan Redis tetap berada hanya pada network `internal`.

Deploy selalu membangun dan membuat ulang `app` serta `web` dalam perintah yang sama agar asset frontend dan konfigurasi Nginx terbaru aktif. Nginx meresolusikan PHP-FPM lewat DNS Docker dinamis, sehingga perubahan IP `app` saat dibuat ulang tidak meninggalkan target FastCGI lama.

```sh
docker compose --env-file .env.production -f compose.production.yml up -d --build --force-recreate app web worker scheduler
```

### Konsolidasi role operasional

Release yang memensiunkan role `Manager Tim` dan `Management / Owner` memakai migration forward-only. Sebelum deploy, pastikan backup database terbaru tersedia dan tinjau akun terdampak: `Manager Tim` dipetakan ke `Employee / Learner`, sedangkan `Management / Owner` dipetakan ke `LMS Admin / HR` dengan hak operasional penuh.

Migration membatalkan deploy tanpa mengubah data bila role pengganti `employee` atau `lms-admin` belum tersedia. Bila itu terjadi, jalankan satu kali seeder role berikut pada image release yang sudah aktif, lalu jalankan ulang migration/deploy. Seeder ini tidak boleh dimasukkan ke auto-deploy rutin.

```sh
dc=(docker compose --env-file .env.production -f compose.production.yml)
"${dc[@]}" exec -T app php artisan db:seed --class='Database\Seeders\RolePermissionSeeder' --force
"${dc[@]}" exec -T app php artisan migrate --force
```

Jangan gunakan `php artisan migrate:rollback` untuk migration konsolidasi role. Bila pemulihan diperlukan, gunakan backup atau migration korektif yang baru.

### Data katalog Customer Service

Deploy otomatis tidak menjalankan seeder katalog agar setiap push tidak mengubah data bisnis. Untuk menyamakan katalog Customer Service production dengan katalog lokal setelah release berhasil, jalankan **satu kali secara eksplisit** di VPS:

```sh
cd /opt/apps/sistem-lms-academy/management-lms-academy
dc=(docker compose --env-file .env.production -f compose.production.yml)
"${dc[@]}" exec -T app php artisan db:seed --class='Database\Seeders\ProductionCustomerServiceDealmakerRefreshSeeder' --force
```

Perintah ini hanya mengganti materi dan assessment course `CS-DEAL-001`: 10 modul dengan satu video YouTube per modul, serta assessment native LMS pada Modul 1–5 dan 7–10. Setiap assessment memiliki lima soal Pre-Test (satu percobaan, tanpa nilai kelulusan) dan sepuluh soal Post-Test (tiga percobaan, nilai kelulusan 80). Modul 6 sengaja dilewati karena sumber soal belum tersedia.

Seeder tidak membuat, menghapus, atau mengubah akun pengguna, role, organisasi, enrollment, trainer course, maupun learning path. Ia akan **membatalkan seluruh operasi** apabila course sudah memiliki progres lesson, attempt/draf/otorisasi ulang assessment, sertifikat, atau enrollment yang pernah dimulai/diselesaikan. Dalam keadaan itu, jangan memaksakan refresh; backup dan lakukan migrasi konten terencana agar riwayat learner tetap utuh.

Seeder tetap dijalankan **secara eksplisit**, bukan pada setiap auto-deploy. Menjalankan perintah lebih dari sekali hanya relevan bila Anda memang ingin menyegarkan ulang katalog CS yang belum memiliki riwayat belajar peserta.

#### Force refresh katalog CS

Jika katalog perlu diganti sebelum go-live dan owner telah menyetujui penghapusan riwayat pembelajaran lama `CS-DEAL-001`, gunakan perintah berikut **satu kali**. Perintah ini menghapus progres lesson, draf/attempt/otorisasi ulang assessment, sertifikat, serta status mulai/selesai enrollment untuk course tersebut. Akun user, role, organisasi, enrollment, trainer course, learning path, dan data course lain tetap dipertahankan; enrollment `CS-DEAL-001` dikembalikan menjadi aktif.

```sh
cd /opt/apps/sistem-lms-academy/management-lms-academy
dc=(docker compose --env-file .env.production -f compose.production.yml)
"${dc[@]}" exec -T app php artisan db:seed --class='Database\Seeders\ProductionCustomerServiceDealmakerForceRefreshSeeder' --force
```

Jangan gunakan `migrate:fresh`, `docker compose down -v`, atau penghapusan volume untuk kebutuhan ini karena tindakan tersebut juga berisiko menghapus akun dan seluruh data aplikasi.

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
- Untuk data dashboard yang terlambat, periksa worker dan Redis terlebih dahulu; cache dashboard operasional aman dibangun ulang dari PostgreSQL dan dapat dihapus dengan `php artisan cache:forget dashboard:operations`.
- Jangan memasukkan token Fonnte, password impor, atau URL database ke tiket/log.

## 5. Checklist rilis

- [ ] Semua test backend lulus dan build frontend berhasil.
- [ ] `APP_DEBUG=false`, HTTPS aktif, dan secret production tidak memakai nilai contoh.
- [ ] Migration berhasil; worker aktif dan memproses export/notifikasi.
- [ ] `/up` HTTP 200 dari luar jaringan aplikasi.
- [ ] Backup baru tersedia dan restore test memiliki bukti terbaru.
- [ ] Admin memverifikasi login, assignment, lesson, assessment, report export, dan notifikasi pada data non-produksi/akun uji.

Konfigurasi VPS, DNS, secret manager, dan layanan backup berada di luar repository; setiap perubahan di sana harus dicatat bersama rilisnya.
