# KGJ Academy

LMS internal Kotagede Jewellery untuk assignment learning path, pembelajaran, Pre-Test/Post-Test, pelaporan, dan sertifikat.

## Menjalankan secara lokal

1. Salin `.env.example` menjadi `.env`, lalu sesuaikan kredensial database dan layanan lokal.
2. Jalankan `docker compose up -d`.
3. Jalankan migrasi: `docker compose exec app php artisan migrate`.
4. Isi data demonstrasi secara aman: `docker compose exec app php artisan db:seed --class=LocalTestingSeeder`.
5. Buka `http://localhost:8080`.

Seeder lokal bersifat idempoten dan tidak melakukan truncate. Jangan gunakan `migrate:fresh`, `db:wipe`, atau `docker compose down -v` pada database lokal yang ingin dipertahankan.

## Validasi

```bash
docker compose exec app composer test
docker compose exec node npm run check
docker compose exec node npm run build
```

## Operasional

- Worker queue: `docker compose exec app php artisan queue:work`
- Konfigurasi notifikasi WhatsApp Fonnte dan layanan eksternal lain tersedia melalui `.env`; jangan commit token ke repository.
- Panduan data uji lokal tersedia pada `docs/LOCAL_TEST_DATA.md`.
