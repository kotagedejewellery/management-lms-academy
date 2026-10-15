# Graph Report - KGJ-lms-academy  (2026-10-15)

## Corpus Check
- 438 files · ~94,615 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 27 file(s) not represented in the graph (top: (none) 18, .example 3, .conf 1)

## Summary
- 2643 nodes · 5901 edges · 156 communities (109 shown, 47 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 28 edges (avg confidence: 0.88)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2846f6df`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- SidebarMenuButton.vue
- vue
- Course.php
- input/index.ts
- @lucide/vue
- User
- TestCase
- reka-ui
- CourseContentController.php
- Inertia\Response
- TwoFactorSetupModal.vue
- Chisel
- Course
- PasswordValidationRules
- Role
- @vueuse/core
- Show.vue
- learning-path-assignments/Index.vue
- Illuminate\Http\Request
- Illuminate\Database\Eloquent\Relations\HasMany
- dialog/index.ts
- Assessment.vue
- Enrollment
- SidebarProvider.vue
- UserImportController.php
- bootstrap/app.php
- IssueCertificate
- NavUser.vue
- resources_js_routes_register_index
- resources_js_routes_register_index_store
- LearningPath
- AssessmentAttemptController.php
- AppHeader.vue
- Illuminate\Database\Schema\Blueprint
- Inertia\Testing\AssertableInertia
- ManageTwoFactor.vue
- Illuminate\Http\RedirectResponse
- SelectContent.vue
- Sidebar.vue
- LocalTestingSeeder.php
- reports/Index.vue
- CourseController.php
- useAppearance.ts
- settings.php
- compilerOptions
- package.json
- InputOTPSlot.vue
- UserController.php
- UserNotification
- CourseController
- Security.vue
- Layout.vue
- courses/Form.vue
- .seed
- aliases
- PRD — KGJ Academy LMS
- dependencies
- AssigneePicker.vue
- types/index.ts
- ReportEnrollmentQuery.php
- Assessments.vue
- LearningPathAssignmentService.php
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
- devDependencies
- users/Form.vue
- SidebarMenuSkeleton.vue
- LearningPathController
- OrganizationController.php
- LearningPathAssignmentService
- @inertiajs/vue3
- SheetContent.vue
- artisan
- DashboardTest
- LessonResourceTest
- LearningFlowTest
- 2026_10_09_000008_create_assessment_tables.php
- optionalDependencies
- vite.config.ts
- AvatarFallback.vue
- Import.vue
- config
- 2026_10_09_000002_create_access_control_tables.php
- 2026_10_09_000004_create_learning_catalog_tables.php
- 2026_10_12_000016_add_timing_to_assessments.php
- Production Deployment Runbook
- UX Feedback & Interaction Audit
- scripts
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- 2026_10_09_000001_create_organization_tables.php
- 2026_10_14_000030_add_certificates_to_courses_and_enrollments.php
- ActivityLog
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
- LearningPathCourse
- CourseCategoryController
- Data Uji Lokal
- GLOSSARY.md
- LearningPathAssignment
- PublishAnnouncement
- Throwable
- global.d.ts
- LearningLessonNavigator.vue
- audit-logs/Index.vue
- AssessmentAttempt
- Checklist UAT Browser
- DashboardResolver
- production-entrypoint.sh
- SpreadsheetChunkReadFilter

## God Nodes (most connected - your core abstractions)
1. `User` - 202 edges
2. `vue` - 142 edges
3. `Course` - 140 edges
4. `cn()` - 93 edges
5. `reka-ui` - 77 edges
6. `TestCase` - 69 edges
7. `@inertiajs/vue3` - 66 edges
8. `CourseCategory` - 63 edges
9. `Enrollment` - 62 edges
10. `@lucide/vue` - 62 edges

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

## Communities (156 total, 47 thin omitted)

### Community 0 - "SidebarMenuButton.vue"
Cohesion: 0.09
Nodes (18): SidebarMenuButtonVariants, delegatedProps, { isMobile, state }, props, props, SidebarMenuButtonProps, resources_js_components_ui_tooltip_index_tooltip, resources_js_components_ui_tooltip_index_tooltipcontent (+10 more)

### Community 1 - "vue"
Cohesion: 0.05
Nodes (46): vue, props, props, props, props, props, props, props (+38 more)

### Community 3 - "input/index.ts"
Cohesion: 0.06
Nodes (38): ref_inertiajs_core, @laravel/passkeys, resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, emit, name (+30 more)

### Community 4 - "@lucide/vue"
Cohesion: 0.06
Nodes (37): @lucide/vue, presentation, props, Status, resources_js_components_ui_badge_index_badge, resources_js_components_ui_button_index_button, Course, props (+29 more)

### Community 5 - "User"
Cohesion: 0.04
Nodes (19): {closure#4}(), {closure#5}(), User, UserPolicy, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory (+11 more)

### Community 6 - "TestCase"
Cohesion: 0.04
Nodes (30): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Foundation\Http\Middleware\PreventRequestForgery, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification (+22 more)

### Community 7 - "reka-ui"
Cohesion: 0.04
Nodes (45): reka-ui, emits, forwarded, props, props, props, emits, forwarded (+37 more)

### Community 8 - "CourseContentController.php"
Cohesion: 0.17
Nodes (8): {closure#1}(), {closure#3}(), {closure#4}(), {closure#5}(), CourseContentController, CourseModule, LessonResource, Illuminate\Support\Arr

### Community 9 - "Inertia\Response"
Cohesion: 0.07
Nodes (17): AnnouncementController, {closure#1}(), EnrollmentController, AuditLogController, Controller, CourseLibraryController, DashboardController, LearningProgressController (+9 more)

### Community 10 - "TwoFactorSetupModal.vue"
Cohesion: 0.07
Nodes (24): Props, uniqueErrors, code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props (+16 more)

### Community 11 - "Chisel"
Cohesion: 0.08
Nodes (31): {closure#1}(), InstallFeaturesCommand, MakeSuperAdmin, SendDeadlineReminders, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode() (+23 more)

### Community 12 - "Course"
Cohesion: 0.09
Nodes (10): Assessment, Course, CourseCategory, {closure#1}(), Illuminate\Support\Facades\Storage, AssessmentManagementTest, LearningPathManagementTest, AssessmentAttemptTest (+2 more)

### Community 13 - "PasswordValidationRules"
Cohesion: 0.06
Nodes (21): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+13 more)

### Community 14 - "Role"
Cohesion: 0.07
Nodes (18): Division, Permission, Role, DatabaseSeeder, {closure#1}(), {closure#2}(), RolePermissionSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents (+10 more)

### Community 15 - "@vueuse/core"
Cohesion: 0.05
Nodes (37): @vueuse/core, delegatedProps, emits, forwarded, props, emits, modelValue, props (+29 more)

### Community 16 - "Show.vue"
Cohesion: 0.06
Nodes (37): activeLesson, activeLessonIndex, allLessons, Assessment, assessmentAction(), assessmentHref(), assessmentTitle(), completingLessonId (+29 more)

### Community 17 - "learning-path-assignments/Index.vue"
Cohesion: 0.06
Nodes (39): emit, passwordInput, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent, resources_js_components_ui_dialog_index_dialogdescription, resources_js_components_ui_dialog_index_dialogfooter, resources_js_components_ui_dialog_index_dialogheader (+31 more)

### Community 18 - "Illuminate\Http\Request"
Cohesion: 0.12
Nodes (14): LearningController, ReportController, {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#3}(), {closure#4}() (+6 more)

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
Cohesion: 0.12
Nodes (13): {closure#1}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#2}(), Enrollment, {closure#2}(), {closure#3}() (+5 more)

### Community 23 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 24 - "UserImportController.php"
Cohesion: 0.20
Nodes (3): UserImportController, CertificateController, Symfony\Component\HttpFoundation\StreamedResponse

### Community 25 - "bootstrap/app.php"
Cohesion: 0.10
Nodes (16): EnsureActiveAccount, HandleAppearance, HandleInertiaRequests, RecordAuditActivity, {closure#1}(), {closure#2}(), {closure#3}(), Closure (+8 more)

### Community 26 - "IssueCertificate"
Cohesion: 0.14
Nodes (18): {closure#3}(), GenerateReportExport, IssueCertificate, {closure#2}(), SyncLearningPath, {closure#2}(), SyncLearningPathRule, Dompdf\Dompdf (+10 more)

### Community 27 - "NavUser.vue"
Cohesion: 0.08
Nodes (27): mainNavGroups, page, Props, { isCurrentOrParentUrl }, NavGroup, { isMobile, state }, loggingOut, logoutDialogOpen (+19 more)

### Community 30 - "LearningPath"
Cohesion: 0.20
Nodes (5): LearningPathAssignmentController, {closure#4}(), LearningPathContentController, LearningPath, {closure#5}()

### Community 31 - "AssessmentAttemptController.php"
Cohesion: 0.16
Nodes (5): AssessmentAttemptController, {closure#2}(), {closure#6}(), AssessmentDraft, Illuminate\Http\Response

### Community 32 - "AppHeader.vue"
Cohesion: 0.08
Nodes (27): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, Props, resources_js_components_ui_avatar_index_avatar (+19 more)

### Community 33 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.12
Nodes (16): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}(), {closure#2}(), {closure#4}() (+8 more)

### Community 34 - "Inertia\Testing\AssertableInertia"
Cohesion: 0.12
Nodes (21): Inertia\Testing\AssertableInertia, {closure#1}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}() (+13 more)

### Community 35 - "ManageTwoFactor.vue"
Cohesion: 0.08
Nodes (27): disableDialogOpen, disabling, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors } (+19 more)

### Community 36 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.21
Nodes (4): AssessmentController, {closure#1}(), ProfileController, Illuminate\Http\RedirectResponse

### Community 37 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 38 - "Sidebar.vue"
Cohesion: 0.09
Nodes (15): emits, forwarded, props, props, delegatedProps, props, props, props (+7 more)

### Community 39 - "LocalTestingSeeder.php"
Cohesion: 0.09
Nodes (11): {closure#11}(), {closure#12}(), AssessmentResponse, Branch, Position, {closure#1}(), {closure#2}(), {closure#3}() (+3 more)

### Community 40 - "reports/Index.vue"
Cohesion: 0.10
Nodes (16): applyFilters(), clearFilters(), Course, courseOptions, courseQuery, courseSearchError, courseSearching, Division (+8 more)

### Community 41 - "CourseController.php"
Cohesion: 0.10
Nodes (5): AssigneeSearchController, {closure#6}(), Illuminate\Http\JsonResponse, Illuminate\Support\Str, Pdo\Mysql

### Community 42 - "useAppearance.ts"
Cohesion: 0.12
Nodes (21): vue-sonner, { appearance, updateAppearance }, tabs, props, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme() (+13 more)

### Community 43 - "settings.php"
Cohesion: 0.20
Nodes (3): SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route

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
Cohesion: 0.20
Nodes (3): {closure#6}(), UserController, Illuminate\Support\Facades\DB

### Community 48 - "UserNotification"
Cohesion: 0.14
Nodes (6): {closure#1}(), {closure#1}(), {closure#2}(), SendWhatsappNotification, Announcement, UserNotification

### Community 50 - "Security.vue"
Cohesion: 0.12
Nodes (14): resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, confirmOpen, emit, handleDelete() (+6 more)

### Community 51 - "Layout.vue"
Cohesion: 0.10
Nodes (23): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, resources_js_components_ui_separator_index_separator, emit, Props, currentUrlReactive (+15 more)

### Community 52 - "courses/Form.vue"
Cohesion: 0.12
Nodes (12): Option, props, Course, Option, props, Course, displayedThumbnail, form (+4 more)

### Community 53 - ".seed"
Cohesion: 0.27
Nodes (3): Carbon\CarbonInterface, LocalTestingSeeder, Illuminate\Support\Collection

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
Cohesion: 0.10
Nodes (22): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+14 more)

### Community 59 - "ReportEnrollmentQuery.php"
Cohesion: 0.38
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+1 more)

### Community 60 - "Assessments.vue"
Cohesion: 0.05
Nodes (24): useUnsavedChangesGuard(), leaveWithoutSaving(), reset(), Assessment, createForm, deletingQuestion, leaveGuard, props (+16 more)

### Community 62 - "ProcessUserImport"
Cohesion: 0.25
Nodes (4): {closure#1}(), ProcessUserImport, UserImport, {closure#1}()

### Community 63 - "ProcessUserImport.php"
Cohesion: 0.14
Nodes (5): {closure#10}(), {closure#8}(), Carbon\Carbon, Generator, PhpOffice\PhpSpreadsheet\IOFactory

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
Nodes (11): {closure#2}(), {closure#2}(), {closure#2}(), Answer, Certificate, LearningPathRule, LessonProgress, ReportExport (+3 more)

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
Cohesion: 0.14
Nodes (9): deleting, deletion, leaveGuard, Lesson, lessonForm, Module, moduleForm, props (+1 more)

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
Cohesion: 0.08
Nodes (8): {closure#2}(), {closure#12}(), {closure#13}(), {closure#18}(), {closure#19}(), {closure#20}(), {closure#7}(), Lesson

### Community 80 - "AssessmentResult.vue"
Cohesion: 0.18
Nodes (10): canRetry, courseStatus, description, duration, isPostTest, passed, props, remainingAttempts (+2 more)

### Community 82 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 83 - "UserFactory.php"
Cohesion: 0.27
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, static

### Community 84 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 85 - "users/Form.vue"
Cohesion: 0.14
Nodes (10): Option, props, ManagedUser, Option, props, form, leaveGuard, ManagedUser (+2 more)

### Community 86 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 90 - "@inertiajs/vue3"
Cohesion: 0.12
Nodes (14): @inertiajs/vue3, Action, context, DashboardItem, LearningSummary, props, Stat, markingId (+6 more)

### Community 91 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 96 - "2026_10_09_000008_create_assessment_tables.php"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 97 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 98 - "vite.config.ts"
Cohesion: 0.25
Nodes (7): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue, vitePort

### Community 99 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 100 - "Import.vue"
Cohesion: 0.25
Nodes (3): form, Import, importDialogOpen

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

### Community 107 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

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

### Community 112 - "ActivityLog"
Cohesion: 0.22
Nodes (3): {closure#2}(), ActivityLog, self

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

### Community 131 - "LearningPathCourse"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#3}(), {closure#5}(), {closure#6}(), LearningPathCourse

### Community 145 - "LearningPathAssignment"
Cohesion: 0.29
Nodes (3): {closure#1}(), LearningPathAssignment, LearningAvailability

### Community 148 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 150 - "audit-logs/Index.vue"
Cohesion: 0.22
Nodes (4): Activity, fieldLabels, labels, props

### Community 152 - "Checklist UAT Browser"
Cohesion: 0.33
Nodes (5): Checklist UAT Browser, Informasi sesi, Integrasi dan gangguan jaringan, Keputusan rilis, Matriks perangkat

## Knowledge Gaps
- **751 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+746 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1196 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **47 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `TestCase`, `CourseContentController.php`, `Inertia\Response`, `Chisel`, `Course`, `PasswordValidationRules`, `Role`, `LearningPathAssignment`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Enrollment`, `DashboardResolver`, `IssueCertificate`, `LearningPath`, `Inertia\Testing\AssertableInertia`, `LocalTestingSeeder.php`, `CourseController.php`, `UserController.php`, `UserNotification`, `CourseController`, `.seed`, `LearningPathAssignmentService.php`, `ProcessUserImport.php`, `CourseManagementTest`, `Illuminate\Database\Eloquent\Model`, `UserFactory.php`, `LearningPathAssignmentService`, `DashboardTest`, `LessonResourceTest`, `LearningFlowTest`, `ActivityLog`?**
  _High betweenness centrality (0.082) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `SidebarMenuButton.vue`, `input/index.ts`, `@lucide/vue`, `reka-ui`, `TwoFactorSetupModal.vue`, `@vueuse/core`, `Show.vue`, `learning-path-assignments/Index.vue`, `dialog/index.ts`, `Assessment.vue`, `global.d.ts`, `SidebarProvider.vue`, `NavUser.vue`, `AppHeader.vue`, `ManageTwoFactor.vue`, `SelectContent.vue`, `Sidebar.vue`, `reports/Index.vue`, `useAppearance.ts`, `package.json`, `InputOTPSlot.vue`, `Security.vue`, `Layout.vue`, `courses/Form.vue`, `AssigneePicker.vue`, `types/index.ts`, `Assessments.vue`, `Badge.vue`, `courses/Content.vue`, `AssessmentResult.vue`, `SidebarMenuSkeleton.vue`, `@inertiajs/vue3`, `SheetContent.vue`, `AvatarFallback.vue`, `Import.vue`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `vue`, `input/index.ts`, `@lucide/vue`, `TwoFactorSetupModal.vue`, `Show.vue`, `learning-path-assignments/Index.vue`, `Assessment.vue`, `audit-logs/Index.vue`, `NavUser.vue`, `AppHeader.vue`, `ManageTwoFactor.vue`, `reports/Index.vue`, `useAppearance.ts`, `package.json`, `Security.vue`, `Layout.vue`, `courses/Form.vue`, `types/index.ts`, `Assessments.vue`, `courses/Content.vue`, `AssessmentResult.vue`, `users/Form.vue`, `Import.vue`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _751 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `SidebarMenuButton.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.08666666666666667 - nodes in this community are weakly interconnected._
- **Should `vue` be split into smaller, more focused modules?**
  _Cohesion score 0.04835164835164835 - nodes in this community are weakly interconnected._
- **Should `input/index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.058445353594389245 - nodes in this community are weakly interconnected._