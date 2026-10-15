# Graph Report - KGJ-lms-academy  (2026-10-14)

## Corpus Check
- 428 files · ~88,362 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 18, .example 2, .conf 1)

## Summary
- 2515 nodes · 5604 edges · 148 communities (108 shown, 40 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 27 edges (avg confidence: 0.88)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4d5f8ff3`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- vue
- TestCase
- User
- resources_js_components_ui_input_index_input
- button/index.ts
- ProcessUserImport.php
- Role
- reka-ui
- Course
- @vueuse/core
- TwoFactorSetupModal.vue
- Chisel
- dialog/index.ts
- Show.vue
- Illuminate\Database\Eloquent\Model
- LearningPathContentController
- learning-path-assignments/Index.vue
- bootstrap/app.php
- AppSidebar.vue
- Assessment.vue
- web.php
- CourseModule
- SidebarProvider.vue
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Inertia\Response
- Import.vue
- NavUser.vue
- resources_js_routes_register_index
- resources_js_routes_register_index_store
- Illuminate\Database\Eloquent\Relations\HasMany
- ReportController.php
- AppHeader.vue
- Illuminate\Database\Schema\Blueprint
- Enrollment
- @inertiajs/vue3
- package.json
- SelectContent.vue
- compilerOptions
- InputOTPSlot.vue
- PasswordValidationRules
- UserController.php
- useAppearance.ts
- Sidebar.vue
- Assessments.vue
- AssessmentAttemptController.php
- @lucide/vue
- ManagePasskeys.vue
- .seed
- aliases
- label/index.ts
- PRD — KGJ Academy LMS
- dependencies
- AssessmentAttempt
- Illuminate\Http\Request
- scripts
- Illuminate\Database\Migrations\Migration
- Illuminate\Support\Facades\Schema
- types/index.ts
- Product
- input/index.ts
- UX Feedback & Interaction Audit
- LearningController.php
- courses/Content.vue
- CourseController
- LearningPathAssignmentController.php
- composer.json
- Design UI/UX — KGJ Academy
- learning-paths/Content.vue
- reports/Index.vue
- ref_routes_register
- resources_js_routes_index_register
- OrganizationController.php
- System Architecture
- Tech Stack dan Pedoman Optimasi
- AssessmentResult.vue
- CourseManagementTest
- CourseLibraryController.php
- Course.php
- require-dev
- UserFactory.php
- devDependencies
- Badge.vue
- KGJ Academy
- LearningPathController
- Inertia\Testing\AssertableInertia
- SidebarMenuSkeleton.vue
- Illuminate\Http\RedirectResponse
- require
- SheetContent.vue
- PasskeyRegister.vue
- notifications/Index.vue
- artisan
- LessonResourceTest
- TooltipContent.vue
- LocalTestingSeeder.php
- audit-logs/Index.vue
- DatabaseSeeder.php
- 2026_10_13_000019_add_completion_rules_to_courses_and_assessments.php
- AssigneePicker.vue
- optionalDependencies
- vite.config.ts
- DashboardResolver.php
- DashboardTest
- config
- 2026_10_09_000004_create_learning_catalog_tables.php
- 2026_10_12_000016_add_timing_to_assessments.php
- Collapsible.vue
- Production Deployment Runbook
- scripts
- UserMenuContent.vue
- LearningFlowTest
- LearningPathCourse
- 2026_10_13_000020_create_course_trainers_table.php
- DashboardResolver
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- 2026_10_09_000001_create_organization_tables.php
- useCurrentUrl.ts
- Breadcrumbs.vue
- AssessmentResponse
- psr-4
- laravel
- logging.php
- patch-wayfinder.mjs
- 2026_10_14_000030_add_certificates_to_courses_and_enrollments.php
- 2026_10_09_000003_add_organization_to_users_table.php
- 2026_10_09_000005_create_learning_path_tables.php
- 2026_10_09_000007_create_enrollment_progress_tables.php
- 2026_10_09_000009_add_reporting_indexes_to_enrollments_table.php
- 2026_10_09_000012_add_phone_to_users_table.php
- 2026_10_09_000013_create_announcements_and_user_notifications_tables.php
- Performance Baseline
- 2026_10_09_000010_create_report_exports_table.php
- 2026_10_09_000015_create_user_imports_table.php
- console.php
- Data Uji Lokal
- GLOSSARY.md

## God Nodes (most connected - your core abstractions)
1. `User` - 191 edges
2. `vue` - 140 edges
3. `Course` - 131 edges
4. `cn()` - 93 edges
5. `reka-ui` - 77 edges
6. `TestCase` - 67 edges
7. `@inertiajs/vue3` - 65 edges
8. `@lucide/vue` - 61 edges
9. `CourseCategory` - 60 edges
10. `Role` - 55 edges

## Surprising Connections (you probably didn't know these)
- `{closure#3}()` --calls--> `Division`  [EXTRACTED]
  database/seeders/LocalTestingSeeder.php → app/Models/Division.php
- `{closure#2}()` --references--> `GenerateReportExport`  [EXTRACTED]
  tests/Feature/ReportTest.php → app/Jobs/GenerateReportExport.php
- `{closure#1}()` --references--> `ProcessUserImport`  [EXTRACTED]
  tests/Feature/UserImportTest.php → app/Jobs/ProcessUserImport.php
- `{closure#1}()` --references--> `PublishAnnouncement`  [EXTRACTED]
  tests/Feature/AnnouncementTest.php → app/Jobs/PublishAnnouncement.php
- `{closure#1}()` --references--> `SyncLearningPath`  [EXTRACTED]
  tests/Feature/Admin/LearningPathManagementTest.php → app/Jobs/SyncLearningPath.php

## Import Cycles
- None detected.

## Communities (148 total, 40 thin omitted)

### Community 0 - "vue"
Cohesion: 0.04
Nodes (55): vue, props, props, props, props, props, props, props (+47 more)

### Community 1 - "TestCase"
Cohesion: 0.05
Nodes (27): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Foundation\Http\Middleware\PreventRequestForgery, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification (+19 more)

### Community 2 - "User"
Cohesion: 0.05
Nodes (18): MakeSuperAdmin, User, UserPolicy, Illuminate\Console\Command, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory (+10 more)

### Community 3 - "resources_js_components_ui_input_index_input"
Cohesion: 0.09
Nodes (25): ref_inertiajs_core, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, Props, { verify, isLoading, error, isSupported }, inputRef, props (+17 more)

### Community 4 - "button/index.ts"
Cohesion: 0.06
Nodes (31): presentation, props, Status, resources_js_components_ui_badge_index_badge, resources_js_components_ui_button_index_button, Course, props, search (+23 more)

### Community 5 - "ProcessUserImport.php"
Cohesion: 0.06
Nodes (33): {closure#1}(), {closure#1}(), {closure#3}(), GenerateReportExport, IssueCertificate, {closure#6}(), {closure#7}(), {closure#9}() (+25 more)

### Community 6 - "Role"
Cohesion: 0.08
Nodes (15): LearningPath, Permission, Role, {closure#5}(), {closure#1}(), {closure#2}(), Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Http\UploadedFile (+7 more)

### Community 7 - "reka-ui"
Cohesion: 0.05
Nodes (36): reka-ui, props, delegatedProps, props, props, emits, forwarded, props (+28 more)

### Community 8 - "Course"
Cohesion: 0.09
Nodes (10): {closure#6}(), Assessment, Course, CourseCategory, Division, AssessmentManagementTest, LearningPathManagementTest, AssessmentAttemptTest (+2 more)

### Community 9 - "@vueuse/core"
Cohesion: 0.06
Nodes (30): @vueuse/core, delegatedProps, props, navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props (+22 more)

### Community 10 - "TwoFactorSetupModal.vue"
Cohesion: 0.07
Nodes (24): Props, uniqueErrors, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props (+16 more)

### Community 11 - "Chisel"
Cohesion: 0.10
Nodes (28): {closure#1}(), InstallFeaturesCommand, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode(), {closure#1}(), {closure#10}() (+20 more)

### Community 13 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 14 - "Show.vue"
Cohesion: 0.06
Nodes (33): Lesson, Module, resources_js_components_ui_sheet_index_sheetcontent, activeLesson, activeLessonIndex, allLessons, Assessment, assessmentAction() (+25 more)

### Community 15 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.09
Nodes (8): {closure#1}(), {closure#2}(), Answer, ReportExport, UserImport, Illuminate\Database\Eloquent\Model, Illuminate\Support\Carbon, self

### Community 17 - "learning-path-assignments/Index.vue"
Cohesion: 0.06
Nodes (39): emit, passwordInput, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent, resources_js_components_ui_dialog_index_dialogdescription, resources_js_components_ui_dialog_index_dialogfooter, resources_js_components_ui_dialog_index_dialogheader (+31 more)

### Community 18 - "bootstrap/app.php"
Cohesion: 0.10
Nodes (16): EnsureActiveAccount, HandleAppearance, HandleInertiaRequests, RecordAuditActivity, {closure#1}(), {closure#2}(), {closure#3}(), Closure (+8 more)

### Community 19 - "AppSidebar.vue"
Cohesion: 0.20
Nodes (7): Props, mainNavGroups, page, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebarheader

### Community 20 - "Assessment.vue"
Cohesion: 0.07
Nodes (27): answeredCount, answerError, assessmentDescription, assessmentTitle, completionPercentage, csrfToken(), currentQuestion, currentQuestionIndex (+19 more)

### Community 21 - "web.php"
Cohesion: 0.13
Nodes (4): EnrollmentController, LearningController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route

### Community 22 - "CourseModule"
Cohesion: 0.20
Nodes (7): {closure#1}(), {closure#3}(), CourseContentController, CourseModule, LessonResource, Illuminate\Support\Arr, Symfony\Component\HttpFoundation\StreamedResponse

### Community 23 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 24 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.07
Nodes (7): {closure#1}(), {closure#2}(), {closure#2}(), Certificate, LearningPathAssignment, LearningPathRule, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 25 - "Inertia\Response"
Cohesion: 0.07
Nodes (17): AnnouncementController, {closure#1}(), UserImportController, Controller, CourseLibraryController, DashboardController, LearningProgressController, MyLearningPathController (+9 more)

### Community 26 - "Import.vue"
Cohesion: 0.25
Nodes (3): form, Import, importDialogOpen

### Community 27 - "NavUser.vue"
Cohesion: 0.11
Nodes (20): Props, { isCurrentOrParentUrl }, NavGroup, { isMobile, state }, loggingOut, logoutDialogOpen, page, user (+12 more)

### Community 30 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.07
Nodes (11): Question, 1. Prinsip data, 2. Entitas dan relasi utama, 3. Constraint penting, 4. Index awal berdasarkan access pattern, 5. Query dan pagination, 6. Cache data, 7. Backup, retention, dan privacy (+3 more)

### Community 31 - "ReportController.php"
Cohesion: 0.20
Nodes (4): AssigneeSearchController, Illuminate\Http\JsonResponse, Illuminate\Support\Str, Pdo\Mysql

### Community 32 - "AppHeader.vue"
Cohesion: 0.09
Nodes (24): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback (+16 more)

### Community 33 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.13
Nodes (15): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+7 more)

### Community 34 - "Enrollment"
Cohesion: 0.11
Nodes (8): {closure#1}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#2}(), Enrollment, {closure#1}(), {closure#3}()

### Community 35 - "@inertiajs/vue3"
Cohesion: 0.06
Nodes (36): @inertiajs/vue3, disableDialogOpen, disabling, { hasSetupData, clearTwoFactorAuthData }, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors } (+28 more)

### Community 36 - "package.json"
Cohesion: 0.10
Nodes (19): private, $schema, type, clsx, concurrently, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc (+11 more)

### Community 37 - "SelectContent.vue"
Cohesion: 0.06
Nodes (21): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 38 - "compilerOptions"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 39 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 40 - "PasswordValidationRules"
Cohesion: 0.06
Nodes (22): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+14 more)

### Community 41 - "UserController.php"
Cohesion: 0.15
Nodes (5): {closure#4}(), {closure#5}(), {closure#6}(), UserController, LearningPathAssignmentService

### Community 42 - "useAppearance.ts"
Cohesion: 0.14
Nodes (19): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+11 more)

### Community 43 - "Sidebar.vue"
Cohesion: 0.09
Nodes (15): emits, forwarded, props, props, delegatedProps, props, props, props (+7 more)

### Community 44 - "Assessments.vue"
Cohesion: 0.13
Nodes (8): Assessment, createForm, deletingQuestion, leaveGuard, props, questions, questionToDelete, settings

### Community 45 - "AssessmentAttemptController.php"
Cohesion: 0.16
Nodes (5): AssessmentAttemptController, {closure#2}(), {closure#6}(), AssessmentDraft, Illuminate\Http\Response

### Community 46 - "@lucide/vue"
Cohesion: 0.07
Nodes (21): @lucide/vue, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+13 more)

### Community 47 - "ManagePasskeys.vue"
Cohesion: 0.10
Nodes (20): resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, confirmOpen, emit, handleDelete(), isDeleting, props (+12 more)

### Community 48 - ".seed"
Cohesion: 0.27
Nodes (3): Carbon\CarbonInterface, LocalTestingSeeder, Illuminate\Support\Collection

### Community 49 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 50 - "label/index.ts"
Cohesion: 0.09
Nodes (20): resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, Props, Props, resources_js_components_ui_label_index_label, resources_js_components_ui_separator_index_separator, { isCurrentOrParentUrl }, sidebarNavItems (+12 more)

### Community 51 - "PRD — KGJ Academy LMS"
Cohesion: 0.12
Nodes (15): 10. UAT dan definition of done, 1. Ringkasan, 2. Tujuan dan ukuran keberhasilan, 3. Pengguna dan hak akses, 4. Scope MVP, 5. Alur inti, 6. Aturan bisnis yang tidak boleh berubah tanpa persetujuan pemilik proses, 7. Kebutuhan fungsional per area (+7 more)

### Community 52 - "dependencies"
Cohesion: 0.12
Nodes (16): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+8 more)

### Community 53 - "AssessmentAttempt"
Cohesion: 0.22
Nodes (10): AssessmentAttempt, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}() (+2 more)

### Community 54 - "Illuminate\Http\Request"
Cohesion: 0.14
Nodes (14): CourseCategoryController, ReportController, {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}() (+6 more)

### Community 55 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 56 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 57 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.14
Nodes (5): {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 58 - "types/index.ts"
Cohesion: 0.14
Nodes (15): className, Props, Props, resources_js_components_ui_sidebar_index_sidebarinset, resources_js_components_ui_sidebar_index_sidebarprovider, resources_js_components_ui_sidebar_index_sidebartrigger, resources_js_components_ui_sonner_index_toaster, Props (+7 more)

### Community 59 - "Product"
Cohesion: 0.15
Nodes (12): Accessibility & Inclusion, Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product (+4 more)

### Community 60 - "input/index.ts"
Cohesion: 0.05
Nodes (30): emits, modelValue, props, useUnsavedChangesGuard(), leaveWithoutSaving(), reset(), Option, props (+22 more)

### Community 61 - "UX Feedback & Interaction Audit"
Cohesion: 0.29
Nodes (6): Design Health, Persona risks, Priority findings, Run notes, Strengths, UX Feedback & Interaction Audit

### Community 62 - "LearningController.php"
Cohesion: 0.09
Nodes (8): {closure#2}(), {closure#10}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#4}(), {closure#9}(), Lesson

### Community 63 - "courses/Content.vue"
Cohesion: 0.14
Nodes (9): deleting, deletion, leaveGuard, Lesson, lessonForm, Module, moduleForm, props (+1 more)

### Community 65 - "LearningPathAssignmentController.php"
Cohesion: 0.15
Nodes (5): LearningPathAssignmentController, AuditLogController, {closure#2}(), CertificateController, ActivityLog

### Community 66 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 67 - "Design UI/UX — KGJ Academy"
Cohesion: 0.17
Nodes (11): 1. Arah yang mengikat, 2. Information architecture, 3. Layar minimum dan responsif, 4. Sistem komponen awal, 5. Aksesibilitas dan interaksi, 6. Kinerja UI, 7. State yang wajib dirancang, 8. Validasi implementasi (+3 more)

### Community 68 - "learning-paths/Content.vue"
Cohesion: 0.15
Nodes (9): Course, deletingPathCourse, form, leaveGuard, PathCourse, pathCourseToDelete, props, sequences (+1 more)

### Community 69 - "reports/Index.vue"
Cohesion: 0.10
Nodes (16): applyFilters(), clearFilters(), Course, courseOptions, courseQuery, courseSearchError, courseSearching, Division (+8 more)

### Community 73 - "System Architecture"
Cohesion: 0.18
Nodes (10): 1. Prinsip arsitektur, 2. Modul aplikasi, 3. Jalur request, 4. Topologi Docker lokal, 5. Keamanan dan ketahanan, 6. Scale path, Learner, Pekerjaan berat (+2 more)

### Community 74 - "Tech Stack dan Pedoman Optimasi"
Cohesion: 0.18
Nodes (10): Background processing, Batas keamanan, Cache dan deduplikasi, Database dan response, Frontend, media, dan CDN, Kebijakan optimasi, Keputusan stack, Struktur Docker (+2 more)

### Community 75 - "AssessmentResult.vue"
Cohesion: 0.18
Nodes (10): canRetry, courseStatus, description, duration, isPostTest, passed, props, remainingAttempts (+2 more)

### Community 79 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 80 - "UserFactory.php"
Cohesion: 0.27
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, static

### Community 81 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 82 - "Badge.vue"
Cohesion: 0.24
Nodes (6): class-variance-authority, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 83 - "KGJ Academy"
Cohesion: 0.40
Nodes (4): KGJ Academy, Menjalankan secara lokal, Operasional, Validasi

### Community 85 - "Inertia\Testing\AssertableInertia"
Cohesion: 0.14
Nodes (17): Inertia\Testing\AssertableInertia, {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+9 more)

### Community 86 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 87 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.21
Nodes (4): AssessmentController, {closure#1}(), ProfileController, Illuminate\Http\RedirectResponse

### Community 88 - "require"
Cohesion: 0.20
Nodes (10): require, dompdf/dompdf, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder (+2 more)

### Community 89 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 90 - "PasskeyRegister.vue"
Cohesion: 0.25
Nodes (5): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm

### Community 91 - "notifications/Index.vue"
Cohesion: 0.29
Nodes (4): vue-sonner, markingId, Notification, props

### Community 94 - "TooltipContent.vue"
Cohesion: 0.14
Nodes (9): emits, forwarded, props, delegatedProps, emits, forwarded, props, props (+1 more)

### Community 95 - "LocalTestingSeeder.php"
Cohesion: 0.10
Nodes (9): {closure#8}(), Branch, LessonProgress, Position, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+1 more)

### Community 96 - "audit-logs/Index.vue"
Cohesion: 0.33
Nodes (3): Activity, labels, props

### Community 97 - "DatabaseSeeder.php"
Cohesion: 0.38
Nodes (4): DatabaseSeeder, RolePermissionSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 98 - "2026_10_13_000019_add_completion_rules_to_courses_and_assessments.php"
Cohesion: 0.25
Nodes (4): {closure#1}(), {closure#2}(), {closure#4}(), {closure#5}()

### Community 99 - "AssigneePicker.vue"
Cohesion: 0.14
Nodes (12): activeIndex, Assignee, error, handleEnter(), listboxId, loading, props, query (+4 more)

### Community 100 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 101 - "vite.config.ts"
Cohesion: 0.25
Nodes (7): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue, vitePort

### Community 102 - "DashboardResolver.php"
Cohesion: 0.31
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#5}(), {closure#6}()

### Community 104 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 105 - "2026_10_09_000004_create_learning_catalog_tables.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 106 - "2026_10_12_000016_add_timing_to_assessments.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 107 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 108 - "Production Deployment Runbook"
Cohesion: 0.29
Nodes (6): 1. Prasyarat, 2. Deploy aplikasi, 3. Backup dan restore database, 4. Monitoring dan respons insiden, 5. Checklist rilis, Production Deployment Runbook

### Community 109 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 110 - "UserMenuContent.vue"
Cohesion: 0.22
Nodes (8): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, emit, Props, resources_js_routes_profile_index, resources_js_types_index_user

### Community 112 - "LearningPathCourse"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#3}(), {closure#5}(), {closure#6}(), LearningPathCourse

### Community 116 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 117 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 118 - "2026_10_09_000001_create_organization_tables.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 119 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 120 - "Breadcrumbs.vue"
Cohesion: 0.25
Nodes (7): Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist, resources_js_components_ui_breadcrumb_index_breadcrumbpage, resources_js_components_ui_breadcrumb_index_breadcrumbseparator

### Community 122 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 123 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 125 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 126 - "patch-wayfinder.mjs"
Cohesion: 0.50
Nodes (3): ref_node_fs, patched, path

### Community 128 - "2026_10_14_000030_add_certificates_to_courses_and_enrollments.php"
Cohesion: 0.16
Nodes (5): {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 135 - "Performance Baseline"
Cohesion: 0.40
Nodes (4): Database, Frontend, Performance Baseline, Queue dan cache

## Knowledge Gaps
- **730 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+725 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1142 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **40 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `resources_js_components_ui_input_index_input`, `button/index.ts`, `reka-ui`, `@vueuse/core`, `TwoFactorSetupModal.vue`, `dialog/index.ts`, `Show.vue`, `learning-path-assignments/Index.vue`, `AppSidebar.vue`, `Assessment.vue`, `SidebarProvider.vue`, `Import.vue`, `NavUser.vue`, `AppHeader.vue`, `@inertiajs/vue3`, `package.json`, `SelectContent.vue`, `InputOTPSlot.vue`, `useAppearance.ts`, `Sidebar.vue`, `Assessments.vue`, `@lucide/vue`, `ManagePasskeys.vue`, `label/index.ts`, `types/index.ts`, `input/index.ts`, `courses/Content.vue`, `learning-paths/Content.vue`, `reports/Index.vue`, `AssessmentResult.vue`, `Badge.vue`, `SidebarMenuSkeleton.vue`, `SheetContent.vue`, `PasskeyRegister.vue`, `notifications/Index.vue`, `TooltipContent.vue`, `AssigneePicker.vue`, `useCurrentUrl.ts`?**
  _High betweenness centrality (0.083) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `TestCase`, `ProcessUserImport.php`, `Role`, `Course`, `CourseController.php`, `Illuminate\Database\Eloquent\Model`, `web.php`, `CourseModule`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Inertia\Response`, `Illuminate\Database\Eloquent\Relations\HasMany`, `ReportController.php`, `Enrollment`, `PasswordValidationRules`, `UserController.php`, `.seed`, `Illuminate\Http\Request`, `CourseController`, `LearningPathAssignmentController.php`, `CourseManagementTest`, `UserFactory.php`, `Inertia\Testing\AssertableInertia`, `LessonResourceTest`, `LocalTestingSeeder.php`, `DatabaseSeeder.php`, `DashboardResolver.php`, `DashboardTest`, `LearningFlowTest`, `DashboardResolver`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `vue`, `resources_js_components_ui_input_index_input`, `button/index.ts`, `TwoFactorSetupModal.vue`, `Show.vue`, `learning-path-assignments/Index.vue`, `AppSidebar.vue`, `Assessment.vue`, `Import.vue`, `NavUser.vue`, `AppHeader.vue`, `package.json`, `useAppearance.ts`, `Assessments.vue`, `ManagePasskeys.vue`, `label/index.ts`, `types/index.ts`, `input/index.ts`, `courses/Content.vue`, `learning-paths/Content.vue`, `reports/Index.vue`, `AssessmentResult.vue`, `notifications/Index.vue`, `audit-logs/Index.vue`, `UserMenuContent.vue`, `useCurrentUrl.ts`, `Breadcrumbs.vue`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _730 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `vue` be split into smaller, more focused modules?**
  _Cohesion score 0.042321879246748205 - nodes in this community are weakly interconnected._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.04688832054560955 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.046462063086104004 - nodes in this community are weakly interconnected._