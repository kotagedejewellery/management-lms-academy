# Implementation Roadmap — KGJ Academy LMS

## Confirmed Phase 1 decisions

- Employee data can be entered manually by Admin. Manual entry is the Phase 1 baseline; CSV/Excel bulk import follows after the core user-management flow is accepted.
- Admin can configure the passing grade per course/assessment. The applied passing grade is used when evaluating the related Post-Test.

## Delivery sequence

| Phase | Scope | Dependency | Completion evidence |
|---|---|---|---|
| 0. Business gate | Confirm attempt limit, retake rule, repeated Post-Test reporting policy, initial course material, and production hosting. | None | Signed decisions recorded before affected implementation starts. |
| 1. Application bootstrap | Initialize Laravel/Inertia/Vue/Tailwind; make Docker services runnable; configure PostgreSQL, Redis, MinIO, environment, test runner, and health endpoint. | Stack and Docker documents | Application boots locally; migrations and baseline tests run in Docker. |
| 2. Identity and organization | Session auth, reset password, roles/permissions/policies, manual user CRUD, division, position, branch, and manager relation. | Phase 1 | Admin manages employees; authorization tests prove role and manager scope. |
| 3. Shared responsive UI | App shell, desktop sidebar/mobile drawer, tokens, status badges, pagination, loading/empty/error states, and accessibility baseline. | Phase 1 | Screens are usable at 390px, 768px, 1024px, and 1440px. |
| 4. Learning catalog | Course/category, module, lesson, private resources, publish status, path builder, course order, and prerequisite. | Phases 2–3 | Admin creates and publishes a complete learning path without code. |
| 5. Enrollment and learner flow | Assignment/enrollment, My Learning, course detail, lesson player, resume progress, course progress, and completion prerequisite. | Phase 4 | Employee can access only assigned published content and resume learning. |
| 6. Assessment and completion | Pre-Test/Post-Test, questions, answers, timer, attempt lifecycle, scoring, improvement, retake, atomic completion, and audit. | Phase 5 plus Phase 0 rules | Core flow Pre-Test → lesson → Post-Test → Passed/Not Passed is covered by feature tests. |
| 7. Reporting and background work | Learner/manager/admin dashboards, permitted filters, exports, activity logs, Redis cache, and queued export/report jobs. | Phases 2, 5–6 | Reports respect scope; heavy exports do not block HTTP requests. |
| 8. Hardening and UAT | Query/index review, N+1 guardrails, payload review, code splitting, media optimization, backup/restore test, staging UAT, and deployment runbook. | All prior phases | UAT checklist passes with no critical issue. |

## Status implementasi saat ini

| Phase | Status | Catatan verifikasi |
|---|---|---|
| 0. Business gate | Selesai | Maksimum 3 attempt, retake langsung, skor Post-Test terbaik untuk laporan, Pre-Test dapat wajib, dan aturan completion tercatat di PRD. |
| 1. Application bootstrap | Selesai | Laravel/Inertia, Docker, PostgreSQL, Redis, MinIO, queue worker, health check, serta test runner tersedia. |
| 2. Identity and organization | Selesai | RBAC, user manual, organisasi, cabang, jabatan, dan scope manager tersedia. |
| 3. Shared responsive UI | Selesai secara implementasi | Shell, state aksi/error, modal/konfirmasi, indikator sidebar, serta layout desktop/mobile tersedia. UAT perangkat nyata tetap diperlukan. |
| 4. Learning catalog | Selesai | Builder course, module, lesson, private resource, publish guard, path, prerequisite, dan urutan course wajib tersedia. |
| 5. Enrollment and learner flow | Selesai | Assignment, rule divisi+jabatan+cabang, sinkronisasi user yang sudah ada melalui queue, detail course, resume, dan progres tersedia. |
| 6. Assessment and completion | Selesai | Pre-Test wajib/opsional, batas 1–3 attempt, timer, draft, Post-Test, skor terbaik, improvement, Passed/Not Passed, serta audit tersedia dan diuji. |
| 7. Reporting and background work | Selesai | Dashboard, report scoped, status Not Passed, baseline Pre-Test, Post-Test terbaik, improvement, CSV queue, dan audit tersedia. |
| 8. Hardening and UAT | Implementasi selesai; penerimaan operasional tertunda | Validasi, indeks, pagination, cache/queue, payload minimum, resource privat, dan test otomatis diterapkan. Backup/restore, staging UAT, metrik P95/bundle/cache/queue, serta deployment runbook perlu dijalankan di lingkungan staging/produksi. |

## Performance implementation checkpoints

- **Phases 2–5:** server-side pagination, explicit query columns, eager loading, minimal Inertia props, request deduplication, lazy image/component loading.
- **Phase 6:** transactional assessment submission, indexes for attempts/progress, targeted cache invalidation after progress changes.
- **Phase 7:** Redis cache for dashboard/report aggregates, queue for exports, route-level code splitting, lazy chart loading, and private signed file access.
- **Phase 8:** measure query plans, P95 request time, bundle weight, cache hit rate, queue wait time, and responsive usability before adding infrastructure.

## Cross-cutting definition of done

Every implementation increment must include: migration/factory when data changes; policy/validation at trust boundaries; proportional automated tests; mobile and desktop verification for UI; pagination for lists; minimal payload/query review; error/empty/loading state; and Graphify index update after the change.
