---
target: UX feedback and interaction across application pages
total_score: 24
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 3
target_identity: "file:E:\\AI Engineer\\PT KotaGede Jewellery\\New System\\KGJ-lms-academy\\resources\\js\\pages"
timestamp: 2026-10-14T04-20-16Z
slug: resources-js-pages
---
## UX Feedback & Interaction Audit

### Design Health

Nielsen score: 24/40. The learning journey is product-specific and mature, while operational feedback in administrative and reporting flows remains inconsistent.

### Strengths

- Shared confirmation dialog and global flash toast provide a solid interaction foundation.
- Assessment includes timer, progress, draft handling, retry, submission confirmation, and understandable outcomes.
- Lesson player exposes pending, failure, and retry states; reusable empty states and accessible pagination are broadly in place.

### Priority findings

1. **P0 — Assessment draft can be lost during immediate exit.** Draft saving is debounced; leaving immediately after answering can cancel the newest pending save although the confirmation says it was saved. Make exit await the final save and offer retry or explicit exit-without-saving on failure.
2. **P1 — Pending destructive actions are not consistently locked.** Lesson/module/resource/question/path-content deletion flows do not pass a pending state to the shared confirmation dialog. Keep the dialog locked until the request completes.
3. **P1 — Inline form validation is inconsistent and weakly associated with controls.** Standardize a field-error contract: `aria-invalid`, `aria-describedby`, alert semantics, first-invalid focus, and local recovery in assignment modals.
4. **P1 — Long forms lack unsaved-change protection.** Add a shared dirty-form leave guard to course, lesson/content, assessment builder, user, and learning-path forms only; never apply it to filters/search.
5. **P2 — Async outcomes remain ambiguous.** Report course search conflates failure and no results; export completion requires manual reload; notifications lack local acknowledgement. Add local loading/failed/retry/ready states, with modest polling only while a pending export page is open.
6. **P2 — Assessment navigation lacks a question map.** Add an accessible jump-to-question drawer showing current, answered, and unanswered state while preserving a single-question mobile focus.
7. **P2 — Keyboard and touch hardening is incomplete.** Turn AssigneePicker into a proper keyboard combobox and raise mobile-critical controls toward 44px; retain compact controls for dense desktop tables.
8. **P2 — Role scope is insufficiently explicit.** Dashboards and reports should state whether data is an authorized team scope or organization-wide.
9. **P3 — Interaction copy mixes Indonesian and English.** Localize common actions, error defaults, and assistive labels while preserving Pre-Test and Post-Test as product terms.
10. **P3 — Skeleton exists but is not used on principal data pages.** Apply only where loading duration is visible; do not add decorative skeletons globally.

### Persona risks

- **First-time learner:** mixed terms such as My Learning, course, assignment, and Indonesian alternatives dilute the mental model.
- **Keyboard/screen-reader user:** AssigneePicker lacks complete combobox semantics; validation errors are not reliably connected to fields.
- **Mobile learner:** rapid assessment exit can lose the latest response; unanswered questions are hard to find.
- **Admin power user:** long forms can be lost and repeated assignments/reporting require more explicit async status.

### Run notes

Two independent assessments were completed. The deterministic detector returned no findings for `resources/js`. Browser, screen-reader, physical mobile, offline, and E2E evidence were unavailable and therefore not claimed.
