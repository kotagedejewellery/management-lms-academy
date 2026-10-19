# Graph Report - KGJ-lms-academy  (2026-10-19)

## Corpus Check
- 449 files · ~103,944 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 28 file(s) not represented in the graph (top: (none) 18, .example 3, .conf 1)

## Summary
- 2798 nodes · 6207 edges · 163 communities (118 shown, 45 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 35 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5771581a`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- SidebarMenuButton.vue
- vue
- label/index.ts
- UserMenuContent.vue
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
- Panduan Pengguna KGJ Academy
- Illuminate\Database\Eloquent\Relations\HasMany
- Badge.vue
- Assessment.vue
- Enrollment
- SidebarProvider.vue
- courses/Form.vue
- bootstrap/app.php
- IssueCertificate
- NavUser.vue
- resources_js_routes_register_index
- resources_js_routes_register_index_store
- LearningPath
- Assessments.vue
- useCurrentUrl.ts
- Illuminate\Database\Schema\Blueprint
- Inertia\Testing\AssertableInertia
- @inertiajs/vue3
- Illuminate\Http\RedirectResponse
- LearningPathController
- Sidebar.vue
- LocalTestingSeeder.php
- reports/Index.vue
- devDependencies
- useAppearance.ts
- settings.php
- compilerOptions
- package.json
- InputError.vue
- Illuminate\Database\Seeder
- Audit akses role
- CourseController
- Layout.vue
- @lucide/vue
- AppHeader.vue
- .seed
- aliases
- PRD — KGJ Academy LMS
- dependencies
- AssigneePicker.vue
- types/index.ts
- ReportController.php
- SelectContent.vue
- ActivityLog
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
- CourseController.php
- courses/Content.vue
- require
- System Architecture
- Tech Stack dan Pedoman Optimasi
- LearningController.php
- AssessmentResult.vue
- optionalDependencies
- require-dev
- UserFactory.php
- AppSidebar.vue
- users/Form.vue
- LearningPathAssignmentController.php
- dialog/index.ts
- CourseContentController
- UserNotification
- FortifyServiceProvider
- app.ts
- artisan
- Assessment
- LessonResourceTest
- LearningFlowTest
- 2026_10_09_000008_create_assessment_tables.php
- InputOTPSlot.vue
- ProfileValidationRules
- AvatarFallback.vue
- SecurityController.php
- config
- 2026_10_09_000002_create_access_control_tables.php
- 2026_10_09_000004_create_learning_catalog_tables.php
- 2026_10_12_000016_add_timing_to_assessments.php
- Production Deployment Runbook
- UX Feedback & Interaction Audit
- OrganizationController
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- 2026_10_09_000001_create_organization_tables.php
- 2026_10_14_000030_add_certificates_to_courses_and_enrollments.php
- Course.php
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
- SpreadsheetChunkReadFilter
- DashboardTest
- Data Uji Lokal
- GLOSSARY.md
- global.d.ts
- SidebarMenuSkeleton.vue
- CourseLibraryController.php
- scripts
- Checkbox.vue
- SheetContent.vue
- ReportEnrollmentQuery.php
- Checklist UAT Browser
- production-entrypoint.sh
- DashboardResolver
- Throwable
- Illuminate\Support\Facades\DB
- resources_js_components_ui_dialog_index_dialogtrigger
- Symfony\Component\HttpFoundation\StreamedResponse
- notifications/Index.vue
- LearningLessonNavigator.vue
- Illuminate\Support\Facades\Storage

## God Nodes (most connected - your core abstractions)
1. `User` - 216 edges
2. `Course` - 153 edges
3. `vue` - 144 edges
4. `cn()` - 93 edges
5. `TestCase` - 79 edges
6. `reka-ui` - 77 edges
7. `Enrollment` - 68 edges
8. `CourseCategory` - 66 edges
9. `@inertiajs/vue3` - 65 edges
10. `Role` - 64 edges

## Surprising Connections (you probably didn't know these)
- `Bukti pemeriksaan` --references--> `CourseManagementTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/Admin/CourseManagementTest.php
- `Bukti pemeriksaan` --references--> `UserManagementTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/Admin/UserManagementTest.php
- `Bukti pemeriksaan` --references--> `DashboardTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/DashboardTest.php
- `Bukti pemeriksaan` --references--> `ReportTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/ReportTest.php
- `Bukti pemeriksaan` --references--> `RoleAccessContractTest`  [INFERRED]
  docs/ROLE_ACCESS_AUDIT.md → tests/Feature/RoleAccessContractTest.php

## Import Cycles
- None detected.

## Communities (163 total, 45 thin omitted)

### Community 0 - "SidebarMenuButton.vue"
Cohesion: 0.09
Nodes (18): SidebarMenuButtonVariants, delegatedProps, { isMobile, state }, props, props, SidebarMenuButtonProps, resources_js_components_ui_tooltip_index_tooltip, resources_js_components_ui_tooltip_index_tooltipcontent (+10 more)

### Community 1 - "vue"
Cohesion: 0.05
Nodes (49): vue, props, props, props, props, props, props, props (+41 more)

### Community 2 - "label/index.ts"
Cohesion: 0.10
Nodes (14): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm, resources_js_components_ui_label_index_label, Announcement, form (+6 more)

### Community 3 - "UserMenuContent.vue"
Cohesion: 0.12
Nodes (18): resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, { getInitials } (+10 more)

### Community 4 - "button/index.ts"
Cohesion: 0.05
Nodes (37): presentation, props, Status, resources_js_components_ui_badge_index_badge, resources_js_components_ui_button_index_button, Course, props, search (+29 more)

### Community 5 - "User"
Cohesion: 0.04
Nodes (18): {closure#4}(), {closure#5}(), User, UserPolicy, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory (+10 more)

### Community 6 - "TestCase"
Cohesion: 0.05
Nodes (26): Bukti pemeriksaan, Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Foundation\Http\Middleware\PreventRequestForgery, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Event (+18 more)

### Community 7 - "reka-ui"
Cohesion: 0.04
Nodes (45): reka-ui, emits, forwarded, props, props, props, emits, forwarded (+37 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (17): AnnouncementController, CourseCategoryController, EnrollmentController, LearningController, {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}() (+9 more)

### Community 9 - "Inertia\Response"
Cohesion: 0.05
Nodes (20): {closure#1}(), {closure#2}(), {closure#6}(), UserController, AuditLogController, Controller, CourseLibraryController, DashboardController (+12 more)

### Community 10 - "TwoFactorSetupModal.vue"
Cohesion: 0.09
Nodes (19): code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors }, { resolvedAppearance } (+11 more)

### Community 11 - "Chisel"
Cohesion: 0.10
Nodes (28): {closure#1}(), InstallFeaturesCommand, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode(), {closure#1}(), {closure#10}() (+20 more)

### Community 12 - "Course"
Cohesion: 0.07
Nodes (13): Course, CourseCategory, {closure#1}(), {closure#1}(), CustomerServiceDealmakerSeeder, Illuminate\Testing\PendingCommand, AssessmentManagementTest, LearningPathManagementTest (+5 more)

### Community 13 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (6): CreateNewUser, ResetUserPassword, PasswordValidationRules, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 14 - "Role"
Cohesion: 0.08
Nodes (13): Division, Permission, Position, Role, {closure#1}(), {closure#2}(), Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Support\Facades\Queue (+5 more)

### Community 15 - "@vueuse/core"
Cohesion: 0.06
Nodes (30): @vueuse/core, delegatedProps, props, navigationMenuTriggerStyle, delegatedProps, emits, forwarded, props (+22 more)

### Community 16 - "Show.vue"
Cohesion: 0.06
Nodes (39): activeLesson, activeLessonIndex, allLessons, Assessment, assessmentAction(), assessmentHref(), assessmentTitle(), completingLessonId (+31 more)

### Community 17 - "learning-path-assignments/Index.vue"
Cohesion: 0.06
Nodes (37): emit, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent, resources_js_components_ui_dialog_index_dialogdescription, resources_js_components_ui_dialog_index_dialogfooter, resources_js_components_ui_dialog_index_dialogheader, resources_js_components_ui_dialog_index_dialogtitle (+29 more)

### Community 18 - "Panduan Pengguna KGJ Academy"
Cohesion: 0.09
Nodes (21): 10. Bantuan cepat, 11. Batas akses penting, 1. Memulai, 2. Alur pembelajaran karyawan, 3. Akses menurut role, 4. Panduan Employee, 5. Panduan Trainer / Mentor, 6. Panduan Manager (+13 more)

### Community 19 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.05
Nodes (11): Question, 1. Prinsip data, 2. Entitas dan relasi utama, 3. Constraint penting, 4. Index awal berdasarkan access pattern, 5. Query dan pagination, 6. Cache data, 7. Backup, retention, dan privacy (+3 more)

### Community 20 - "Badge.vue"
Cohesion: 0.16
Nodes (8): class-variance-authority, props, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 21 - "Assessment.vue"
Cohesion: 0.07
Nodes (27): answeredCount, answerError, assessmentDescription, assessmentTitle, completionPercentage, csrfToken(), currentQuestion, currentQuestionIndex (+19 more)

### Community 22 - "Enrollment"
Cohesion: 0.09
Nodes (14): MakeSuperAdmin, SendDeadlineReminders, {closure#1}(), {closure#2}(), Enrollment, UserNotificationService, {closure#2}(), {closure#3}() (+6 more)

### Community 23 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 24 - "courses/Form.vue"
Cohesion: 0.10
Nodes (12): Option, props, Course, Option, props, Course, displayedThumbnail, form (+4 more)

### Community 25 - "bootstrap/app.php"
Cohesion: 0.09
Nodes (19): EnsureActiveAccount, HandleAppearance, HandleInertiaRequests, RecordAuditActivity, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+11 more)

### Community 26 - "IssueCertificate"
Cohesion: 0.14
Nodes (18): {closure#3}(), GenerateReportExport, IssueCertificate, {closure#2}(), SyncLearningPath, {closure#2}(), SyncLearningPathRule, Dompdf\Dompdf (+10 more)

### Community 27 - "NavUser.vue"
Cohesion: 0.11
Nodes (19): Props, { isCurrentOrParentUrl }, NavGroup, { isMobile, state }, loggingOut, logoutDialogOpen, page, user (+11 more)

### Community 30 - "LearningPath"
Cohesion: 0.15
Nodes (6): LearningPathAssignmentController, {closure#1}(), {closure#2}(), LearningPathContentController, LearningPath, LearningPathCourse

### Community 31 - "Assessments.vue"
Cohesion: 0.05
Nodes (26): messages, props, useUnsavedChangesGuard(), leaveWithoutSaving(), reset(), Assessment, createForm, deletingQuestion (+18 more)

### Community 32 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 33 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.12
Nodes (16): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}(), {closure#2}(), {closure#4}() (+8 more)

### Community 34 - "Inertia\Testing\AssertableInertia"
Cohesion: 0.10
Nodes (26): Inertia\Testing\AssertableInertia, {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}() (+18 more)

### Community 35 - "@inertiajs/vue3"
Cohesion: 0.07
Nodes (31): @inertiajs/vue3, disableDialogOpen, disabling, { hasSetupData, clearTwoFactorAuthData }, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors } (+23 more)

### Community 36 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.19
Nodes (4): AssessmentController, {closure#1}(), {closure#4}(), Illuminate\Http\RedirectResponse

### Community 38 - "Sidebar.vue"
Cohesion: 0.09
Nodes (15): emits, forwarded, props, props, delegatedProps, props, props, props (+7 more)

### Community 39 - "LocalTestingSeeder.php"
Cohesion: 0.13
Nodes (8): {closure#11}(), AssessmentResponse, Branch, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#7}()

### Community 40 - "reports/Index.vue"
Cohesion: 0.10
Nodes (16): applyFilters(), clearFilters(), Course, courseOptions, courseQuery, courseSearchError, courseSearching, Division (+8 more)

### Community 41 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 42 - "useAppearance.ts"
Cohesion: 0.19
Nodes (15): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+7 more)

### Community 43 - "settings.php"
Cohesion: 0.18
Nodes (3): Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route, ActionableHttpErrorTest

### Community 44 - "compilerOptions"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 45 - "package.json"
Cohesion: 0.09
Nodes (25): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, @laravel/vite-plugin-wayfinder (+17 more)

### Community 46 - "InputError.vue"
Cohesion: 0.06
Nodes (31): ref_inertiajs_core, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, errorElement, props, Props, { verify, isLoading, error, isSupported } (+23 more)

### Community 47 - "Illuminate\Database\Seeder"
Cohesion: 0.12
Nodes (9): DatabaseSeeder, {closure#1}(), LocalCustomerServiceDealmakerSeeder, RolePermissionSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, LocalCustomerServiceDealmakerSeederTest, {closure#1}() (+1 more)

### Community 48 - "Audit akses role"
Cohesion: 0.12
Nodes (15): Audit akses role, Batas audit, Employee, Fondasi akses yang berlaku untuk semua role, Gap dan keputusan yang masih terbuka, Kesimpulan singkat, LMS Admin, Management / Owner (+7 more)

### Community 50 - "Layout.vue"
Cohesion: 0.07
Nodes (25): resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, Props, confirmOpen, emit (+17 more)

### Community 51 - "@lucide/vue"
Cohesion: 0.09
Nodes (17): @lucide/vue, resources_js_actions_app_http_controllers_settings_profilecontroller, Props, uniqueErrors, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle, Activity (+9 more)

### Community 52 - "AppHeader.vue"
Cohesion: 0.12
Nodes (15): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, resources_js_components_ui_navigation_menu_index_navigationmenu, resources_js_components_ui_navigation_menu_index_navigationmenuitem (+7 more)

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

### Community 59 - "ReportController.php"
Cohesion: 0.14
Nodes (7): AssigneeSearchController, {closure#1}(), {closure#2}(), ReportController, ReportExport, ReportEnrollmentQuery, Illuminate\Http\JsonResponse

### Community 60 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 61 - "ActivityLog"
Cohesion: 0.25
Nodes (3): {closure#2}(), ActivityLog, self

### Community 62 - "ProcessUserImport"
Cohesion: 0.25
Nodes (4): {closure#1}(), ProcessUserImport, UserImport, {closure#1}()

### Community 63 - "ProcessUserImport.php"
Cohesion: 0.11
Nodes (7): {closure#10}(), {closure#12}(), {closure#8}(), LearningPathAssignmentService, Carbon\Carbon, Generator, PhpOffice\PhpSpreadsheet\IOFactory

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
Nodes (10): {closure#3}(), Answer, Certificate, LearningPathRule, LessonProgress, LessonResource, {closure#6}(), Illuminate\Database\Eloquent\Model (+2 more)

### Community 72 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 73 - "Design UI/UX — KGJ Academy"
Cohesion: 0.17
Nodes (11): 1. Arah yang mengikat, 2. Information architecture, 3. Layar minimum dan responsif, 4. Sistem komponen awal, 5. Aksesibilitas dan interaksi, 6. Kinerja UI, 7. State yang wajib dirancang, 8. Validasi implementasi (+3 more)

### Community 74 - "CourseController.php"
Cohesion: 0.11
Nodes (4): {closure#6}(), Illuminate\Support\Arr, Illuminate\Support\Str, Pdo\Mysql

### Community 75 - "courses/Content.vue"
Cohesion: 0.09
Nodes (15): addLesson(), deleting, deletion, editingLesson, leaveGuard, Lesson, lessonForm, Module (+7 more)

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
Cohesion: 0.05
Nodes (16): {closure#1}(), {closure#12}(), {closure#2}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#9}(), {closure#12}() (+8 more)

### Community 80 - "AssessmentResult.vue"
Cohesion: 0.18
Nodes (10): canRetry, courseStatus, description, duration, isPostTest, passed, props, remainingAttempts (+2 more)

### Community 81 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 82 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 83 - "UserFactory.php"
Cohesion: 0.27
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, static

### Community 84 - "AppSidebar.vue"
Cohesion: 0.12
Nodes (15): ref_vitest, Props, mainNavGroups, page, resources_js_components_ui_sidebar_index_sidebar, resources_js_components_ui_sidebar_index_sidebarcontent, resources_js_components_ui_sidebar_index_sidebarfooter, resources_js_components_ui_sidebar_index_sidebarheader (+7 more)

### Community 85 - "users/Form.vue"
Cohesion: 0.14
Nodes (10): Option, props, ManagedUser, Option, props, form, leaveGuard, ManagedUser (+2 more)

### Community 86 - "LearningPathAssignmentController.php"
Cohesion: 0.16
Nodes (6): {closure#1}(), {closure#2}(), LearningPathAssignment, {closure#1}(), {closure#3}(), {closure#5}()

### Community 87 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 89 - "UserNotification"
Cohesion: 0.12
Nodes (8): {closure#1}(), {closure#1}(), {closure#2}(), PublishAnnouncement, SendWhatsappNotification, Announcement, UserNotification, {closure#1}()

### Community 90 - "FortifyServiceProvider"
Cohesion: 0.24
Nodes (3): AppServiceProvider, FortifyServiceProvider, Illuminate\Support\ServiceProvider

### Community 91 - "app.ts"
Cohesion: 0.31
Nodes (6): laravel-vite-plugin, initializeFlashToast(), successMessage(), Appearance, FlashToast, ResolvedAppearance

### Community 93 - "Assessment"
Cohesion: 0.14
Nodes (8): AssessmentAttemptController, {closure#2}(), {closure#6}(), Assessment, AssessmentAttempt, AssessmentDraft, {closure#1}(), Illuminate\Http\Response

### Community 96 - "2026_10_09_000008_create_assessment_tables.php"
Cohesion: 0.25
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 97 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 98 - "ProfileValidationRules"
Cohesion: 0.29
Nodes (4): ProfileValidationRules, ProfileUpdateRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest

### Community 99 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 100 - "SecurityController.php"
Cohesion: 0.24
Nodes (4): SecurityController, PasswordUpdateRequest, TwoFactorAuthenticationRequest, Laravel\Fortify\InteractsWithTwoFactorState

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
Cohesion: 0.22
Nodes (8): 1. Prasyarat, 2. Konfigurasi awal VPS, 3. Auto-deploy GitHub Actions, 3. Backup dan restore database, 4. Monitoring dan respons insiden, 5. Checklist rilis, Data katalog awal, Production Deployment Runbook

### Community 106 - "UX Feedback & Interaction Audit"
Cohesion: 0.29
Nodes (6): Design Health, Persona risks, Priority findings, Run notes, Strengths, UX Feedback & Interaction Audit

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

### Community 133 - "Data Uji Lokal"
Cohesion: 0.50
Nodes (3): Data Uji Lokal, Environment lokal yang perlu diisi, Materi Customer Service Dealmaker Mastery

### Community 145 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 146 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 148 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 149 - "Checkbox.vue"
Cohesion: 0.33
Nodes (4): delegatedProps, emits, forwarded, props

### Community 150 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 151 - "ReportEnrollmentQuery.php"
Cohesion: 0.38
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+1 more)

### Community 152 - "Checklist UAT Browser"
Cohesion: 0.33
Nodes (5): Checklist UAT Browser, Informasi sesi, Integrasi dan gangguan jaringan, Keputusan rilis, Matriks perangkat

### Community 157 - "Illuminate\Support\Facades\DB"
Cohesion: 0.33
Nodes (5): {closure#1}(), Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\Facades\DB, Illuminate\Validation\Rules\Password

### Community 167 - "Symfony\Component\HttpFoundation\StreamedResponse"
Cohesion: 0.21
Nodes (4): UserImportController, CertificateController, {closure#2}(), Symfony\Component\HttpFoundation\StreamedResponse

### Community 170 - "notifications/Index.vue"
Cohesion: 0.29
Nodes (4): vue-sonner, markingId, Notification, props

### Community 201 - "Illuminate\Support\Facades\Storage"
Cohesion: 0.13
Nodes (7): Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Storage, PhpOffice\PhpSpreadsheet\Spreadsheet, PhpOffice\PhpSpreadsheet\Writer\Xlsx, {closure#1}(), LocalTestingSeederTest, UserImportTest

## Knowledge Gaps
- **794 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+789 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1269 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **45 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Audit akses role` connect `Audit akses role` to `TestCase`?**
  _High betweenness centrality (0.426) - this node is a cross-community bridge._
- **Why does `Bukti pemeriksaan` connect `TestCase` to `DashboardTest`, `CourseManagementTest`, `Course`, `Role`, `Illuminate\Database\Seeder`, `Audit akses role`?**
  _High betweenness centrality (0.420) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _794 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `SidebarMenuButton.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.08666666666666667 - nodes in this community are weakly interconnected._
- **Should `vue` be split into smaller, more focused modules?**
  _Cohesion score 0.045464725643896975 - nodes in this community are weakly interconnected._
- **Should `label/index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.09782608695652174 - nodes in this community are weakly interconnected._
- **Should `UserMenuContent.vue` be split into smaller, more focused modules?**
  _Cohesion score 0.11904761904761904 - nodes in this community are weakly interconnected._