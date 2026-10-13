# Performance Baseline

Tanggal pengukuran: 12 Oktober 2026. Lingkungan: Docker lokal PostgreSQL 17 dan build production Vite. Baseline ini adalah bukti struktur/index dan ukuran artefak, bukan simulasi beban production karena database lokal belum memiliki data operasional.

## Database

| Alur | Temuan | Keputusan |
|---|---|---|
| Dashboard learner: enrollment per user/status | `Index Only Scan` memakai `enrollments_user_id_status_due_at_index`; eksekusi 0,095 ms pada database kosong. | Index sudah sesuai; tidak menambah index baru. |
| Report status aktif, urut terbaru | `Bitmap Index Scan` memakai `enrollments_status_created_at_index`; eksekusi 0,119 ms pada database kosong. | Index sudah sesuai; ukur ulang dengan volume production-like. |
| Dashboard manajemen | Aggregate disimpan Redis selama lima menit dan diinvalidasi saat User, Course, atau Enrollment berubah. | Tidak perlu index baru tanpa bukti query plan. |

Jumlah record saat pengukuran: `users=0`, `courses=0`, `enrollments=0`, `assessment_attempts=0`. Karena itu hasil waktu tidak boleh digunakan sebagai target kapasitas atau dasar menambah index.

Saat data production-like tersedia, ulangi pada salinan anonim menggunakan:

```sql
EXPLAIN (ANALYZE, BUFFERS) <query-yang-lambat>;
```

Simpan query plan, jumlah record, buffer read/hit, dan waktu eksekusi sebelum mengubah index. Prioritaskan report berfilter, team progress, audit log, dan halaman course/enrollment berukuran besar.

## Frontend

`npm run build` lulus. Artefak utama:

| Artefak | Ukuran gzip |
|---|---:|
| App shell | 48,84 kB |
| Vendor shared | 78,73 kB |
| CSS aplikasi | 19,47 kB |
| Dashboard route | 1,54 kB |
| Assessment route | 1,56 kB |

Route page tetap menjadi chunk terpisah; tidak ada chart atau media besar pada bundle awal. Vite memberi peringatan opsional tentang `fontaine` untuk font fallback, namun dependency baru tidak ditambahkan karena tidak memengaruhi fungsi maupun hasil pengukuran saat ini.

## Queue dan cache

- Export, import, dan notifikasi telah berada pada worker queue; verifikasi production dilakukan melalui satu export uji sesudah deploy.
- Cache dashboard manajemen dibangun ulang dari PostgreSQL dan tidak menyimpan status kelulusan sebagai source of truth.
- Cache hit rate, queue wait time, P95 request, serta failed-job count memerlukan monitoring dari lingkungan production/staging karena tidak dapat diukur valid pada database lokal kosong.

Lihat `DEPLOYMENT_RUNBOOK.md` untuk checklist monitoring dan restore sebelum go-live.
