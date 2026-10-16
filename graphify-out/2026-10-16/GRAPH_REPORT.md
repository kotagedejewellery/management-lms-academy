# Graph Report - KGJ-lms-academy  (2026-10-16)

## Corpus Check
- 442 files · ~99,026 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 28 file(s) not represented in the graph (top: (none) 18, .example 3, .conf 1)

## Summary
- 2700 nodes · 6016 edges · 158 communities (117 shown, 41 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 37 edges (avg confidence: 0.9)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `dd4999f4`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- SidebarMenuButton.vue
- vue
- @inertiajs/vue3
- input/index.ts
- button/index.ts
- User
- TestCase
- reka-ui
- Illuminate\Http\Request
- Inertia\Response
- TwoFactorSetupModal.vue
- Chisel
- Course
- PasswordValidationRules
- Role
- @vueuse/core
- Show.vue
- learning-path-assignments/Index.vue
- LearningPathController
- Illuminate\Database\Eloquent\Relations\HasMany
- dialog/index.ts
- Assessment.vue
- Enrollment
- SidebarProvider.vue
- courses/Form.vue
- ActivityLog
- IssueCertificate
- AppSidebar.vue
- resources_js_routes_register_index
- resources_js_routes_register_index_store
- LearningPath
- Assessment
- GenerateReportExport.php
- Illuminate\Database\Schema\Blueprint
- Inertia\Testing\AssertableInertia
- TwoFactorRecoveryCodes.vue
- Illuminate\Http\RedirectResponse
- SelectContent.vue
- Sidebar.vue
- LocalTestingSeeder.php
- reports/Index.vue
- CourseController.php
- useAppearance.ts
- CourseContentController
- compilerOptions
- package.json
- InputOTPSlot.vue
- UserController.php
- Audit akses role
- CourseController
- Layout.vue
- AppHeader.vue
- label/index.ts
- .seed
- aliases
- PRD — KGJ Academy LMS
- dependencies
- AssigneePicker.vue
- types/index.ts
- AssessmentAttempt
- Assessments.vue
- learning-paths/Content.vue
- ProcessUserImport
- ProcessUserImport.php
- Illuminate\Database\Migrations\Migration
- scripts
- Illuminate\Support\Facades\Schema
- Product
- CourseManagementTest
- Illuminate\Database\Eloquent\Model
- ref_routes_register
- resources_js_routes_index_register
- composer.json
- Design UI/UX — KGJ Academy
- Badge.vue
- courses/Content.vue
- require
- System Architecture
- Tech Stack dan Pedoman Optimasi
- LearningController.php
- AssessmentResult.vue
- CourseLibraryController.php
- require-dev
- UserFactory.php
- NavUser.vue
- users/Form.vue
- LearningPathAssignment
- @lucide/vue
- OrganizationController
- UserNotification
- CourseManagementTest.php
- SheetContent.vue
- artisan
- ReportController
- LessonResourceTest
- LearningFlowTest
- 2026_10_09_000008_create_assessment_tables.php
- TwoFactorChallenge.vue
- devDependencies
- AvatarFallback.vue
- LearningPathAssignmentService
- config
- 2026_10_09_000002_create_access_control_tables.php
- 2026_10_09_000004_create_learning_catalog_tables.php
- 2026_10_12_000016_add_timing_to_assessments.php
- Production Deployment Runbook
- UX Feedback & Interaction Audit
- useTwoFactorAuth.ts
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- 2026_10_09_000001_create_organization_tables.php
- 2026_10_14_000030_add_certificates_to_courses_and_enrollments.php
- ManageTwoFactor.vue
- psr-4
- laravel
- logging.php
- 0001_01_01_000001_create_cache_table.php
- 2025_08_14_170933_add_two_factor_columns_to_users_table.php
- 2026_10_09_000003_add_organization_to_users_table.php
- 2026_10_09_000005_create_learning_path_tables.php
- 2026_10_09_000007_create_enrollment_progress_tables.php
- 2026_10_09_000009_add_reporting_indexes_to_enrollments_table.php
- 2026_10_09_000012_add_phone_to_users_table.php
- 2026_10_15_000001_add_generic_delivery_to_user_notifications.php
- Performance Baseline
- console.php
- patch-wayfinder.mjs
- KGJ Academy
- 2026_10_09_000010_create_report_exports_table.php
- 2026_10_13_000018_create_learning_path_rules_table.php
- 2026_10_13_000020_create_course_trainers_table.php
- optionalDependencies
- PasskeyItem.vue
- Data Uji Lokal
- GLOSSARY.md
- vite.config.ts
- SidebarMenuSkeleton.vue
- Import.vue
- DatabaseSeeder.php
- scripts
- audit-logs/Index.vue
- AlertError.vue
- Checklist UAT Browser
- SpreadsheetChunkReadFilter
- production-entrypoint.sh
- Course.php
- useCurrentUrl.ts
- LearningLessonNavigator.vue

## God Nodes (most connected - your core abstractions)
1. `User` - 205 edges
2. `Course` - 142 edges
3. `vue` - 142 edges
4. `cn()` - 93 edges
5. `reka-ui` - 77 edges
6. `TestCase` - 71 edges
7. `@inertiajs/vue3` - 66 edges
8. `Role` - 64 edges
9. `CourseCategory` - 63 edges
10. `Enrollment` - 62 edges

## Surprising Connections (you probably didn't know these)
- `Bukti pemeriksaan` --references--> `CourseManagementTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/Admin/CourseManagementTest.php
- `Bukti pemeriksaan` --references--> `UserManagementTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/Admin/UserManagementTest.php
- `Bukti pemeriksaan` --references--> `DashboardTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/DashboardTest.php
- `Bukti pemeriksaan` --references--> `ReportTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/ReportTest.php
- `Perbaikan yang dituntaskan` --references--> `create()`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → resources/js/pages/admin/courses/Assessments.vue

## Import Cycles
- None detected.

## Communities (158 total, 41 thin omitted)

### Community 0 - "SidebarMenuButton.vue"
Cohesion: 0.09
Nodes (18): SidebarMenuButtonVariants, delegatedProps, { isMobile, state }, props, props, SidebarMenuButtonProps, resources_js_components_ui_tooltip_index_tooltip, resources_js_components_ui_tooltip_index_tooltipcontent (+10 more)

### Community 1 - "vue"
Cohesion: 0.05
Nodes (46): vue, props, props, props, props, props, props, props (+38 more)

### Community 2 - "@inertiajs/vue3"
Cohesion: 0.11
Nodes (16): @inertiajs/vue3, vue-sonner, initializeFlashToast(), Action, context, DashboardItem, LearningSummary, props (+8 more)

### Community 3 - "input/index.ts"
Cohesion: 0.06
Nodes (39): ref_inertiajs_core, @laravel/passkeys, resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, emit, name (+31 more)

### Community 4 - "button/index.ts"
Cohesion: 0.06
Nodes (31): presentation, props, Status, resources_js_components_ui_badge_index_badge, resources_js_components_ui_button_index_button, Course, props, search (+23 more)

### Community 5 - "User"
Cohesion: 0.05
Nodes (16): User, UserPolicy, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable (+8 more)

### Community 6 - "TestCase"
Cohesion: 0.05
Nodes (27): Bukti pemeriksaan, Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Foundation\Http\Middleware\PreventRequestForgery, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Event (+19 more)

### Community 7 - "reka-ui"
Cohesion: 0.05
Nodes (37): reka-ui, emits, forwarded, props, props, props, emits, forwarded (+29 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (16): AnnouncementController, CourseCategoryController, EnrollmentController, UserImportController, {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}() (+8 more)

### Community 9 - "Inertia\Response"
Cohesion: 0.06
Nodes (17): {closure#1}(), AuditLogController, CertificateController, Controller, CourseLibraryController, DashboardController, LearningProgressController, MyLearningPathController (+9 more)

### Community 10 - "TwoFactorSetupModal.vue"
Cohesion: 0.15
Nodes (10): code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors }, { resolvedAppearance } (+2 more)

### Community 11 - "Chisel"
Cohesion: 0.08
Nodes (31): {closure#1}(), InstallFeaturesCommand, MakeSuperAdmin, SendDeadlineReminders, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode() (+23 more)

### Community 12 - "Course"
Cohesion: 0.07
Nodes (12): Course, CourseCategory, {closure#1}(), Illuminate\Support\Facades\Queue, Illuminate\Testing\PendingCommand, LearningPathManagementTest, AssessmentAttemptTest, CertificateTest (+4 more)

### Community 13 - "PasswordValidationRules"
Cohesion: 0.06
Nodes (22): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+14 more)

### Community 14 - "Role"
Cohesion: 0.08
Nodes (12): Division, Permission, Role, {closure#1}(), {closure#2}(), Illuminate\Database\Eloquent\Relations\BelongsToMany, OrganizationManagementTest, UserManagementTest (+4 more)

### Community 15 - "@vueuse/core"
Cohesion: 0.06
Nodes (30): @vueuse/core, delegatedProps, props, navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props (+22 more)

### Community 16 - "Show.vue"
Cohesion: 0.06
Nodes (39): activeLesson, activeLessonIndex, allLessons, Assessment, assessmentAction(), assessmentHref(), assessmentTitle(), completingLessonId (+31 more)

### Community 17 - "learning-path-assignments/Index.vue"
Cohesion: 0.06
Nodes (39): emit, passwordInput, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent, resources_js_components_ui_dialog_index_dialogdescription, resources_js_components_ui_dialog_index_dialogfooter, resources_js_components_ui_dialog_index_dialogheader (+31 more)

### Community 19 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.06
Nodes (11): Question, 1. Prinsip data, 2. Entitas dan relasi utama, 3. Constraint penting, 4. Index awal berdasarkan access pattern, 5. Query dan pagination, 6. Cache data, 7. Backup, retention, dan privacy (+3 more)

### Community 20 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 21 - "Assessment.vue"
Cohesion: 0.07
Nodes (27): answeredCount, answerError, assessmentDescription, assessmentTitle, completionPercentage, csrfToken(), currentQuestion, currentQuestionIndex (+19 more)

### Community 22 - "Enrollment"
Cohesion: 0.10
Nodes (14): {closure#1}(), {closure#3}(), {closure#1}(), {closure#2}(), Enrollment, {closure#1}(), {closure#2}(), {closure#3}() (+6 more)

### Community 23 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 24 - "courses/Form.vue"
Cohesion: 0.10
Nodes (12): Option, props, Course, Option, props, Course, displayedThumbnail, form (+4 more)

### Community 25 - "ActivityLog"
Cohesion: 0.07
Nodes (19): {closure#2}(), EnsureActiveAccount, HandleAppearance, HandleInertiaRequests, RecordAuditActivity, ActivityLog, {closure#1}(), {closure#2}() (+11 more)

### Community 26 - "IssueCertificate"
Cohesion: 0.15
Nodes (16): IssueCertificate, PublishAnnouncement, {closure#2}(), SyncLearningPath, {closure#2}(), SyncLearningPathRule, Dompdf\Dompdf, Dompdf\Options (+8 more)

### Community 27 - "AppSidebar.vue"
Cohesion: 0.12
Nodes (15): ref_vitest, Props, mainNavGroups, page, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebarheader (+7 more)

### Community 30 - "LearningPath"
Cohesion: 0.16
Nodes (9): LearningPathAssignmentController, {closure#1}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), LearningPathContentController, LearningPath (+1 more)

### Community 31 - "Assessment"
Cohesion: 0.17
Nodes (6): AssessmentAttemptController, {closure#2}(), {closure#6}(), Assessment, AssessmentDraft, Illuminate\Http\Response

### Community 32 - "GenerateReportExport.php"
Cohesion: 0.16
Nodes (6): {closure#2}(), {closure#3}(), GenerateReportExport, RuntimeException, {closure#2}(), Throwable

### Community 33 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.12
Nodes (16): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}(), {closure#2}(), {closure#4}() (+8 more)

### Community 34 - "Inertia\Testing\AssertableInertia"
Cohesion: 0.12
Nodes (22): Inertia\Testing\AssertableInertia, {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}() (+14 more)

### Community 35 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.21
Nodes (10): isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_card_index_card, resources_js_components_ui_card_index_cardcontent, resources_js_components_ui_card_index_carddescription, resources_js_components_ui_card_index_cardheader, resources_js_components_ui_card_index_cardtitle (+2 more)

### Community 36 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.12
Nodes (7): AssessmentController, {closure#1}(), ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Route

### Community 37 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 38 - "Sidebar.vue"
Cohesion: 0.09
Nodes (15): emits, forwarded, props, props, delegatedProps, props, props, props (+7 more)

### Community 39 - "LocalTestingSeeder.php"
Cohesion: 0.11
Nodes (9): {closure#2}(), {closure#12}(), Branch, Position, ReportExport, {closure#1}(), {closure#2}(), {closure#3}() (+1 more)

### Community 40 - "reports/Index.vue"
Cohesion: 0.10
Nodes (16): applyFilters(), clearFilters(), Course, courseOptions, courseQuery, courseSearchError, courseSearching, Division (+8 more)

### Community 41 - "CourseController.php"
Cohesion: 0.11
Nodes (4): {closure#6}(), Illuminate\Support\Arr, Illuminate\Support\Str, Pdo\Mysql

### Community 42 - "useAppearance.ts"
Cohesion: 0.19
Nodes (15): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+7 more)

### Community 43 - "CourseContentController"
Cohesion: 0.28
Nodes (3): CourseContentController, CourseModule, LessonResource

### Community 44 - "compilerOptions"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 45 - "package.json"
Cohesion: 0.10
Nodes (19): private, $schema, type, clsx, concurrently, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc (+11 more)

### Community 46 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 47 - "UserController.php"
Cohesion: 0.16
Nodes (4): {closure#4}(), {closure#5}(), {closure#6}(), UserController

### Community 48 - "Audit akses role"
Cohesion: 0.12
Nodes (15): Audit akses role, Batas audit, Employee, Fondasi akses yang berlaku untuk semua role, Gap dan keputusan yang masih terbuka, Kesimpulan singkat, LMS Admin, Management / Owner (+7 more)

### Community 50 - "Layout.vue"
Cohesion: 0.12
Nodes (15): resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, Props, resources_js_components_ui_separator_index_separator, { isCurrentOrParentUrl } (+7 more)

### Community 51 - "AppHeader.vue"
Cohesion: 0.07
Nodes (33): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback (+25 more)

### Community 52 - "label/index.ts"
Cohesion: 0.15
Nodes (8): Announcement, form, sendDialogOpen, applySearch(), clearSearch(), Course, filters, props

### Community 54 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 55 - "PRD — KGJ Academy LMS"
Cohesion: 0.12
Nodes (15): 10. UAT dan definition of done, 1. Ringkasan, 2. Tujuan dan ukuran keberhasilan, 3. Pengguna dan hak akses, 4. Scope MVP, 5. Alur inti, 6. Aturan bisnis yang tidak boleh berubah tanpa persetujuan pemilik proses, 7. Kebutuhan fungsional per area (+7 more)

### Community 56 - "dependencies"
Cohesion: 0.12
Nodes (16): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+8 more)

### Community 57 - "AssigneePicker.vue"
Cohesion: 0.14
Nodes (12): activeIndex, Assignee, error, handleEnter(), listboxId, loading, props, query (+4 more)

### Community 58 - "types/index.ts"
Cohesion: 0.09
Nodes (25): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+17 more)

### Community 59 - "AssessmentAttempt"
Cohesion: 0.22
Nodes (10): AssessmentAttempt, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+2 more)

### Community 60 - "Assessments.vue"
Cohesion: 0.14
Nodes (8): Assessment, createForm, deletingQuestion, leaveGuard, props, questions, questionToDelete, settings

### Community 61 - "learning-paths/Content.vue"
Cohesion: 0.15
Nodes (9): Course, deletingPathCourse, form, leaveGuard, PathCourse, pathCourseToDelete, props, sequences (+1 more)

### Community 62 - "ProcessUserImport"
Cohesion: 0.25
Nodes (4): {closure#1}(), ProcessUserImport, UserImport, {closure#1}()

### Community 63 - "ProcessUserImport.php"
Cohesion: 0.13
Nodes (6): {closure#10}(), {closure#11}(), {closure#8}(), Carbon\Carbon, Generator, PhpOffice\PhpSpreadsheet\IOFactory

### Community 64 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.14
Nodes (5): {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}(), Illuminate\Database\Migrations\Migration

### Community 65 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 66 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 67 - "Product"
Cohesion: 0.15
Nodes (12): Accessibility & Inclusion, Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product (+4 more)

### Community 69 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (12): {closure#2}(), {closure#2}(), Answer, AssessmentResponse, Certificate, LearningPathRule, LessonProgress, {closure#6}() (+4 more)

### Community 72 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 73 - "Design UI/UX — KGJ Academy"
Cohesion: 0.17
Nodes (11): 1. Arah yang mengikat, 2. Information architecture, 3. Layar minimum dan responsif, 4. Sistem komponen awal, 5. Aksesibilitas dan interaksi, 6. Kinerja UI, 7. State yang wajib dirancang, 8. Validasi implementasi (+3 more)

### Community 74 - "Badge.vue"
Cohesion: 0.20
Nodes (7): class-variance-authority, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 75 - "courses/Content.vue"
Cohesion: 0.11
Nodes (14): addLesson(), deleting, deletion, editingLesson, leaveGuard, Lesson, lessonForm, Module (+6 more)

### Community 76 - "require"
Cohesion: 0.18
Nodes (11): require, dompdf/dompdf, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+3 more)

### Community 77 - "System Architecture"
Cohesion: 0.18
Nodes (10): 1. Prinsip arsitektur, 2. Modul aplikasi, 3. Jalur request, 4. Topologi Docker lokal, 5. Keamanan dan ketahanan, 6. Scale path, Learner, Pekerjaan berat (+2 more)

### Community 78 - "Tech Stack dan Pedoman Optimasi"
Cohesion: 0.18
Nodes (10): Background processing, Batas keamanan, Cache dan deduplikasi, Database dan response, Frontend, media, dan CDN, Kebijakan optimasi, Keputusan stack, Struktur Docker (+2 more)

### Community 79 - "LearningController.php"
Cohesion: 0.06
Nodes (15): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#12}(), {closure#13}(), {closure#18}() (+7 more)

### Community 80 - "AssessmentResult.vue"
Cohesion: 0.18
Nodes (10): canRetry, courseStatus, description, duration, isPostTest, passed, props, remainingAttempts (+2 more)

### Community 82 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 83 - "UserFactory.php"
Cohesion: 0.27
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, static

### Community 84 - "NavUser.vue"
Cohesion: 0.11
Nodes (18): Props, { isCurrentOrParentUrl }, NavGroup, { isMobile, state }, loggingOut, logoutDialogOpen, page, user (+10 more)

### Community 85 - "users/Form.vue"
Cohesion: 0.08
Nodes (17): useUnsavedChangesGuard(), leaveWithoutSaving(), reset(), form, LearningPath, leaveGuard, props, Option (+9 more)

### Community 86 - "LearningPathAssignment"
Cohesion: 0.18
Nodes (5): {closure#1}(), LearningPathAssignment, {closure#1}(), {closure#3}(), {closure#5}()

### Community 87 - "@lucide/vue"
Cohesion: 0.07
Nodes (20): @lucide/vue, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+12 more)

### Community 89 - "UserNotification"
Cohesion: 0.15
Nodes (6): {closure#1}(), {closure#1}(), {closure#2}(), SendWhatsappNotification, Announcement, UserNotification

### Community 90 - "CourseManagementTest.php"
Cohesion: 0.15
Nodes (6): Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Storage, PhpOffice\PhpSpreadsheet\Spreadsheet, PhpOffice\PhpSpreadsheet\Writer\Xlsx, {closure#1}(), LocalTestingSeederTest

### Community 91 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 93 - "ReportController"
Cohesion: 0.26
Nodes (3): AssigneeSearchController, ReportController, Illuminate\Http\JsonResponse

### Community 96 - "2026_10_09_000008_create_assessment_tables.php"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 97 - "TwoFactorChallenge.vue"
Cohesion: 0.18
Nodes (9): resources_js_components_ui_input_otp_index_inputotp, resources_js_components_ui_input_otp_index_inputotpgroup, resources_js_components_ui_input_otp_index_inputotpslot, authConfigContent, code, showRecoveryInput, resources_js_routes_two_factor_login_index, resources_js_routes_two_factor_login_index_store (+1 more)

### Community 98 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 99 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 101 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 102 - "2026_10_09_000002_create_access_control_tables.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 103 - "2026_10_09_000004_create_learning_catalog_tables.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 104 - "2026_10_12_000016_add_timing_to_assessments.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 105 - "Production Deployment Runbook"
Cohesion: 0.25
Nodes (7): 1. Prasyarat, 2. Konfigurasi awal VPS, 3. Auto-deploy GitHub Actions, 3. Backup dan restore database, 4. Monitoring dan respons insiden, 5. Checklist rilis, Production Deployment Runbook

### Community 106 - "UX Feedback & Interaction Audit"
Cohesion: 0.29
Nodes (6): Design Health, Persona risks, Priority findings, Run notes, Strengths, UX Feedback & Interaction Audit

### Community 107 - "useTwoFactorAuth.ts"
Cohesion: 0.20
Nodes (9): errors, hasSetupData, manualSetupKey, qrCodeSvg, recoveryCodesList, UseTwoFactorAuthReturn, resources_js_routes_two_factor_index_qrcode, resources_js_routes_two_factor_index_recoverycodes (+1 more)

### Community 108 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 109 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 110 - "2026_10_09_000001_create_organization_tables.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 111 - "2026_10_14_000030_add_certificates_to_courses_and_enrollments.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 112 - "ManageTwoFactor.vue"
Cohesion: 0.22
Nodes (7): disableDialogOpen, disabling, { hasSetupData, clearTwoFactorAuthData }, showSetupModal, resources_js_routes_two_factor_index, resources_js_routes_two_factor_index_disable, resources_js_routes_two_factor_index_enable

### Community 113 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 114 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 115 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 124 - "Performance Baseline"
Cohesion: 0.40
Nodes (4): Database, Frontend, Performance Baseline, Queue dan cache

### Community 125 - "console.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 126 - "patch-wayfinder.mjs"
Cohesion: 0.50
Nodes (3): ref_node_fs, patched, path

### Community 127 - "KGJ Academy"
Cohesion: 0.40
Nodes (4): KGJ Academy, Menjalankan secara lokal, Operasional, Validasi

### Community 131 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 132 - "PasskeyItem.vue"
Cohesion: 0.12
Nodes (17): confirmOpen, emit, handleDelete(), isDeleting, props, Auth, Passkey, TwoFactorConfigContent (+9 more)

### Community 145 - "vite.config.ts"
Cohesion: 0.25
Nodes (7): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue, vitePort

### Community 146 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 147 - "Import.vue"
Cohesion: 0.25
Nodes (3): form, Import, importDialogOpen

### Community 148 - "DatabaseSeeder.php"
Cohesion: 0.38
Nodes (4): DatabaseSeeder, RolePermissionSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 149 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 150 - "audit-logs/Index.vue"
Cohesion: 0.22
Nodes (4): Activity, fieldLabels, labels, props

### Community 151 - "AlertError.vue"
Cohesion: 0.33
Nodes (5): Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle

### Community 152 - "Checklist UAT Browser"
Cohesion: 0.33
Nodes (5): Checklist UAT Browser, Informasi sesi, Integrasi dan gangguan jaringan, Keputusan rilis, Matriks perangkat

### Community 156 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

## Knowledge Gaps
- **767 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+762 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1220 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **41 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Audit akses role` connect `Audit akses role` to `TestCase`?**
  _High betweenness centrality (0.443) - this node is a cross-community bridge._
- **Why does `Bukti pemeriksaan` connect `TestCase` to `Audit akses role`, `CourseManagementTest`, `Course`, `Role`?**
  _High betweenness centrality (0.436) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _767 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `SidebarMenuButton.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.08666666666666667 - nodes in this community are weakly interconnected._
- **Should `vue` be split into smaller, more focused modules?**
  _Cohesion score 0.04810744810744811 - nodes in this community are weakly interconnected._
- **Should `@inertiajs/vue3` be split into smaller, more focused modules?**
  _Cohesion score 0.10666666666666667 - nodes in this community are weakly interconnected._
- **Should `input/index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.05909090909090909 - nodes in this community are weakly interconnected._