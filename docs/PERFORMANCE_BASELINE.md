# Performance Baseline

Tanggal pengukuran: 14 Oktober 2026. Lingkungan: Docker lokal PostgreSQL 17, data demo aman, dan build production Vite. Baseline ini adalah bukti struktur/index dan ukuran artefak, bukan simulasi beban production.

## Database

| Alur | Temuan | Keputusan |
|---|---|---|
| Dashboard learner: enrollment per user/status | `Seq Scan` dengan 7 enrollment; eksekusi 0,143 ms dan 5 shared-buffer hit. | Dataset terlalu kecil untuk memakai index; tidak menambah index tanpa bukti beban nyata. |
| Report status aktif, urut terbaru | `Seq Scan` dengan 7 enrollment; eksekusi 0,074 ms dan 1 shared-buffer hit. | Dataset terlalu kecil untuk memakai index; ukur ulang pada salinan anonim bervolume production-like. |
| Dashboard manajemen | Aggregate disimpan Redis selama lima menit dan diinvalidasi saat User, Course, atau Enrollment berubah. | Tidak perlu index baru tanpa bukti query plan. |

Jumlah record saat pengukuran: `users=9`, `courses=5`, `enrollments=7`, `assessment_attempts=7`. Hasil ini tidak boleh digunakan sebagai target kapasitas atau dasar menambah index karena masih berupa data demo.

Saat data production-like tersedia, ulangi pada salinan anonim menggunakan:

```sql
EXPLAIN (ANALYZE, BUFFERS) <query-yang-lambat>;
```

Simpan query plan, jumlah record, buffer read/hit, dan waktu eksekusi sebelum mengubah index. Prioritaskan report berfilter, team progress, audit log, dan halaman course/enrollment berukuran besar.

## Frontend

`npm run build` lulus. Artefak utama:

| Artefak | Ukuran gzip |
|---|---:|
| App shell | 44,68 kB |
| Vendor shared | 78,73 kB |
| CSS aplikasi | 20,57 kB |
| Dashboard route | 1,25 kB |
| Assessment route | 3,76 kB |

Route page tetap menjadi chunk terpisah; tidak ada chart atau media besar pada bundle awal. Vite memberi peringatan opsional tentang `fontaine` untuk font fallback, namun dependency baru tidak ditambahkan karena tidak memengaruhi fungsi maupun hasil pengukuran saat ini.

## Queue dan cache

- Export, import, dan notifikasi telah berada pada worker queue; verifikasi production dilakukan melalui satu export uji sesudah deploy.
- Cache dashboard manajemen dibangun ulang dari PostgreSQL dan tidak menyimpan status kelulusan sebagai source of truth.
- Cache hit rate, queue wait time, P95 request, serta failed-job count memerlukan monitoring dari lingkungan production/staging karena tidak dapat diukur valid pada database lokal kosong.

Lihat `DEPLOYMENT_RUNBOOK.md` untuk checklist monitoring dan restore sebelum go-live.
