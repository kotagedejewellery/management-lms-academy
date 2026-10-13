# Register Gap Teknis — KGJ Academy LMS

Status dokumen: aktif. Register ini menjadi acuan audit dan penyelesaian gap codebase.

## Cakupan audit

- Jalur utama: autentikasi, organisasi, course/path, enrollment, assessment, reporting/export, import, notifikasi, dan resource privat.
- Kualitas: aturan bisnis, otorisasi, integritas data, transaksi, pagination/payload, static analysis, serta UI responsif/a11y yang dapat diperiksa dari kode.
- Asumsi beban: LMS internal dengan ribuan employee; akses learner dan assignment lebih sering daripada perubahan admin.

## Gap terverifikasi

| ID | Prioritas | Gap dan dampak | Keputusan perbaikan | Status |
|---|---|---|---|---|
| G-001 | P1 | User/course dapat memakai jabatan dari divisi lain. | Validasi cross-division di server dan feature test. | Selesai |
| G-002 | P1 | Siklus prerequisite tidak langsung dapat mengunci learner. | Menolak prerequisite transitif yang kembali ke course awal. | Selesai |
| G-003 | P1 | Penyisipan course path dapat melanggar sequence; assignment lama tidak menerima course baru. | Perpindahan sequence transaksional dan job sinkronisasi idempoten. | Selesai |
| G-004 | P1 | Akun nonaktif masih dapat mengakses Settings. | Middleware `active` diterapkan ke semua settings terautentikasi. | Selesai |
| G-005 | P1 | Request langsung dapat menambah lesson progress pada enrollment terminal. | Mutasi lesson dibatasi untuk enrollment `active`. | Selesai |
| G-006 | P1 | Retry import dapat meninggalkan user tanpa assignment path. | User, role, dan assignment dibuat dalam satu transaksi. | Selesai |
| G-007 | P2 | Builder tidak menyediakan penghapusan aman. | Confirmation UI; hanya draft tanpa progress yang dapat dihapus. Soal ber-attempt dan path ber-assignment dilindungi. | Selesai |
| G-008 | P2 | Dialog assignment memuat seluruh employee aktif ke browser. | Pencarian server-side: query minimal 2 karakter, maksimum 15 hasil, payload minimal, request sebelumnya dibatalkan. | Selesai |
| G-009 | P2 | Format PHP tidak konsisten; PHPStan default kehabisan memori; frontend memakai Lucide release tanpa type declaration. | Pint membetulkan format, PHPStan memakai 512 MB, Lucide dipatok ke `1.17.0` yang memiliki deklarasi TypeScript. Debt PHPStan dianalisis tanpa ignore blanket. | Dalam verifikasi |
| G-010 | P2 | URL learner langsung masih dapat membuka assessment/resource course archived. | Learner hanya dapat mengakses course published; direct route archived ditolak. | Selesai |
| G-011 | P3 | Hapus profile mandiri berbenturan dengan audit FK. | Penghapusan mandiri ditolak; LMS Admin menonaktifkan akun. | Selesai |
| G-012 | P3 | Backup/restore, UAT perangkat nyata, load test, P95 cache/queue harus divalidasi di staging/produksi. | Tidak disimulasikan sebagai bukti lokal; dibawa ke checklist release. | Eksternal |

## Bukti verifikasi utama

- Feature test melindungi prerequisite, division/position, terminal enrollment, archived resource, user import, sequence path, job sinkronisasi, delete draft, dan pencarian employee.
- Pencarian employee hanya mengembalikan `id`, `name`, dan `employee_id` untuk akun aktif yang cocok.
- Detektor Impeccable telah memeriksa seluruh UI yang diubah pada increment ini tanpa temuan.

## Catatan static analysis

PHPStan level 7 sebelumnya menemukan debt type-level Eloquent yang luas ketika memori dibuat cukup. Konfigurasi tidak diturunkan dan tidak diberi ignore blanket demi menghasilkan status hijau semu. Temuan yang berdampak langsung pada runtime/keamanan ditangani dalam G-001 sampai G-011; sisa type annotation akan dibuktikan dan dituntaskan pada tahap verifikasi G-009.
