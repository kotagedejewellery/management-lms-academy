# Database System

## 1. Prinsip data

PostgreSQL menyimpan data operasional dan histori evaluasi. Object storage menyimpan binary lesson resource (video, PDF, dan gambar); tabel hanya menyimpan metadata/path/ukuran/mime type. Sertifikat belum menjadi fitur aktif dan belum memiliki storage maupun tabel. Foreign key menjaga referential integrity. Assessment attempt dan audit log tidak ditimpa untuk mempertahankan auditability.

## 2. Entitas dan relasi utama

```text
User ──< Enrollment >── Course ──< Module ──< Lesson ──< LessonResource
  │                         │                         │
  ├─ Division / Position / Branch / Manager            └─< LessonProgress >── Enrollment
  ├─< UserRole >── Role ──< RolePermission >── Permission
  └─< ActivityLog

LearningPath ──< LearningPathCourse >── Course
Course ──< Assessment (PRE_TEST | POST_TEST) ──< Question ──< Answer
Assessment ──< AssessmentAttempt ──< AssessmentResponse

Progress course dihitung dari `lesson_progress` milik sebuah `enrollment`; status dan waktu selesai course disimpan pada `enrollments`. Status enrollment adalah `active`, `completed`, atau `not_passed`.
```

### Tabel domain minimum

| Area | Tabel |
|---|---|
| Identity | `users`, `roles`, `permissions`, `role_permissions`, `user_roles` |
| Organization | `divisions`, `positions`, `branches` |
| Catalog | `course_categories`, `courses`, `modules`, `lessons`, `lesson_resources` |
| Paths | `learning_paths`, `learning_path_courses`, `learning_path_rules`, `learning_path_assignments`, `enrollments` |
| Progress | `lesson_progress` (progress course merupakan nilai turunan) |
| Assessment | `assessments`, `questions`, `answers`, `assessment_attempts`, `assessment_responses`, `assessment_drafts` |
| Operations | `announcements`, `user_notifications`, `activity_logs`, `report_exports`, `user_imports` |

`assessments.type` dibatasi ke `PRE_TEST` atau `POST_TEST`. Satu course dapat memiliki keduanya sebagai record berbeda. `assessments.is_required` berlaku untuk Pre-Test; ketika aktif, materi hanya dapat dibuka setelah Pre-Test disubmit. `attempt_limit` dikonfigurasi 1â€“3 per assessment. `assessment_attempts` menyimpan score, waktu mulai/kirim, pass status, attempt number, dan snapshot nilai yang diperlukan untuk audit.

## 3. Constraint penting

- `users.employee_id` dan `users.email` unique sesuai kebijakan akun.
- `enrollments`: unique `(user_id, course_id)`; jangan mengizinkan enrollment course ganda.
- `lesson_progress`: unique `(enrollment_id, lesson_id)`.
- Progress course tidak memiliki tabel terpisah: agregasi `lesson_progress` menunjukkan penyelesaian lesson, sedangkan `enrollments.status` dan `enrollments.completed_at` mencatat penyelesaian course.
- `assessment_attempts`: unique `(assessment_id, enrollment_id, attempt_number)`.
- `learning_path_courses`: unique `(learning_path_id, course_id)` dan memiliki `sequence` positif.
- `learning_path_assignments`: unique `(user_id, learning_path_id)`.
- `learning_path_rules`: unique `(learning_path_id, division_id, position_id, branch_id)`; ketiga atribut organisasi harus cocok sebelum assignment otomatis dibuat.
- Rule baru disinkronkan melalui queue ke employee aktif yang telah cocok; proses ini idempoten dan tidak menimpa enrollment/progress yang ada.
- Foreign key memakai aturan delete yang eksplisit; riwayat assessment/audit tidak cascade delete.
- Attempt submit, score, dan transition completion dijalankan dalam transaksi; unique constraint menangani race condition retake bersamaan.
- Course dengan Post-Test wajib selesai setelah Post-Test lulus; tanpa Post-Test course selesai ketika seluruh lesson wajib selesai. Attempt Post-Test terakhir yang gagal mengubah enrollment menjadi `not_passed`.

## 4. Index awal berdasarkan access pattern

| Tabel | Index | Dipakai oleh |
|---|---|---|
| `users` | unique `employee_id`, unique `email`; `(division_id, position_id, branch_id, status)` | Admin user filter dan assignment audience. |
| `enrollments` | `(user_id, status, due_at)`; `(course_id, status)` | My Learning, deadline, reporting course. |
| `lesson_progress` | `(enrollment_id, lesson_id)` unique; `(status, updated_at)` | Resume/progress course. |
| `courses` | `(status, division_id, position_id)`; `(category_id, status)` | Course library dan admin catalog. |
| `learning_path_rules` | `(division_id, position_id, branch_id)` | Pencocokan otomatis path saat employee aktif dibuat, diperbarui, atau diimpor. |
| `modules` | `(course_id, sequence)` | Course detail/player. |
| `lessons` | `(module_id, sequence)` | Course player. |
| `assessments` | `(course_id, type)` | Membuka Pre/Post-Test. |
| `assessment_attempts` | `(assessment_id, enrollment_id, attempt_number)` unique; `(enrollment_id, submitted_at)` | Limit attempt dan report user. |
| `activity_logs` | `(subject_type, subject_id, created_at)`; `(actor_id, created_at)` | Audit trail. |
| `user_notifications` | `(user_id, announcement_id)` unique; `(user_id, read_at, created_at)` | In-app notification dan status pengiriman WhatsApp. |
| `report_exports` | `(user_id, status, created_at)` | Riwayat dan antrean export report. |
| `user_imports` | `(user_id, created_at)` | Riwayat import user. |

Tambahkan index laporan gabungan hanya setelah `EXPLAIN (ANALYZE, BUFFERS)` menunjukkan kebutuhan. Index berlebih memperlambat insert progress/attempt.

## 5. Query dan pagination

- Listing user/course/enrollment memakai pagination server-side dengan sort field yang diizinkan dan stabil.
- Audit log dan activity feed menggunakan cursor pagination berdasarkan `(created_at, id)`.
- Dashboard menghitung aggregate melalui query terarah atau cache, bukan menarik semua enrollment ke PHP/Vue.
- Eager load relasi yang dirender; gunakan count/exists/aggregate untuk badge dan statistik. Larang loop query per user/course/lesson.
- Select field eksplisit pada endpoint/page props; jangan memuat answer correctness ke client sebelum submit atau ke role tanpa izin.

## 6. Cache data

| Data | Lokasi | Invalidation |
|---|---|---|
| Course catalog published/filter taxonomy | Redis | Publish/archive/edit course/category. |
| Learner dashboard summary | Redis, key per user + version | Lesson progress, enrollment, attempt submit. |
| Manager/Admin aggregate report | Redis, key per permitted scope + filter + period | Progress/attempt/enrollment yang berada dalam scope. |
| Authorization/resource access | Tidak di-cache lintas request sebagai source of truth | Periksa Policy pada request. |

Cache harus selalu dapat dibangun ulang dari PostgreSQL; tidak ada state kelulusan yang hanya hidup di Redis.

## 7. Backup, retention, dan privacy

Backup database terenkripsi serta diuji restore-nya sebelum production. Audit dan assessment attempts memiliki retention sesuai kebijakan HR/legal yang harus ditetapkan. Soft delete hanya dipakai pada entitas yang memang perlu dipulihkan; historical result tetap tidak boleh hilang saat course/user dinonaktifkan. Export mengikuti permission dan dicatat sebagai activity log.
