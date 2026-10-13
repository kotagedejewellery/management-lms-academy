# Graph Report - KGJ-lms-academy  (2026-10-12)

## Corpus Check
- 399 files · ~60,736 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 18, .example 2, .conf 1)

## Summary
- 2096 nodes · 4518 edges · 137 communities (102 shown, 35 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 23 edges (avg confidence: 0.9)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- User
- reka-ui
- @vueuse/core
- Login.vue
- @lucide/vue
- Role
- Laravel\Fortify\Features
- UserNotification
- Illuminate\Database\Eloquent\Relations\BelongsTo
- label/index.ts
- Inertia\Response
- Chisel
- dialog/index.ts
- CourseCategory
- @inertiajs/vue3
- Illuminate\Database\Eloquent\Model
- Illuminate\Database\Eloquent\Relations\HasMany
- TwoFactorSetupModal.vue
- bootstrap/app.php
- package.json
- Course
- Layout.vue
- Illuminate\Http\Request
- lib/utils.ts
- LearningPath
- Show.vue
- SheetContent.vue
- AppSidebar.vue
- resources_js_routes_register_index
- resources_js_routes_register_index_store
- CourseContentController.php
- ProcessUserImport.php
- AppHeader.vue
- dependencies
- learning-paths/Content.vue
- TwoFactorRecoveryCodes.vue
- ProcessUserImport
- SelectContent.vue
- compilerOptions
- vue
- InputOTPSlot.vue
- Controller
- useAppearance.ts
- ProfileValidationRules
- CourseController.php
- aliases
- Illuminate\Database\Schema\Blueprint
- PRD — KGJ Academy LMS
- settings.php
- UserFactory.php
- UserInfo.vue
- input/index.ts
- ActivityLog
- global.d.ts
- TestCase
- scripts
- Illuminate\Support\Facades\Schema
- Product
- types/index.ts
- courses/Form.vue
- SidebarProvider.vue
- users/Form.vue
- PasswordValidationRules
- CourseLibraryController.php
- FortifyServiceProvider.php
- composer.json
- Design UI/UX — KGJ Academy
- FortifyServiceProvider
- System Architecture
- Tech Stack dan Pedoman Optimasi
- ref_routes_register
- resources_js_routes_index_register
- Announcement
- UserController.php
- Production Deployment Runbook
- require-dev
- devDependencies
- Badge.vue
- reports/Index.vue
- ManageTwoFactor.vue
- ReportEnrollmentQuery.php
- require
- Illuminate\Database\Migrations\Migration
- optionalDependencies
- Collapsible.vue
- tooltip/index.ts
- SidebarMenuSkeleton.vue
- useCurrentUrl.ts
- Assessment.vue
- OrganizationController.php
- Course.php
- DashboardTest
- artisan
- AvatarFallback.vue
- AppServiceProvider.php
- scripts
- vite.config.ts
- LearningController.php
- config
- 2026_10_09_000004_create_learning_catalog_tables.php
- 2026_10_12_000016_add_timing_to_assessments.php
- PasskeyRegister.vue
- team/Index.vue
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- 2026_10_09_000001_create_organization_tables.php
- Implementation Roadmap — KGJ Academy LMS
- Illuminate\Support\Str
- Performance Baseline
- Import.vue
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
- 2026_10_09_000013_create_announcements_and_user_notifications_tables.php
- LearningPathManagementTest.php
- TooltipContent.vue
- ReportTest.php
- 2026_10_09_000015_create_user_imports_table.php
- console.php
- patch-wayfinder.mjs
- OrganizationManagementTest.php

## God Nodes (most connected - your core abstractions)
1. `User` - 144 edges
2. `vue` - 128 edges
3. `cn()` - 93 edges
4. `Course` - 83 edges
5. `reka-ui` - 77 edges
6. `@inertiajs/vue3` - 64 edges
7. `TestCase` - 60 edges
8. `@lucide/vue` - 55 edges
9. `Role` - 46 edges
10. `@vueuse/core` - 45 edges

## Surprising Connections (you probably didn't know these)
- `{closure#2}()` --references--> `GenerateReportExport`  [EXTRACTED]
  tests/Feature/ReportTest.php → app/Jobs/GenerateReportExport.php
- `{closure#1}()` --references--> `ProcessUserImport`  [EXTRACTED]
  tests/Feature/UserImportTest.php → app/Jobs/ProcessUserImport.php
- `{closure#1}()` --calls--> `Permission`  [EXTRACTED]
  database/seeders/RolePermissionSeeder.php → app/Models/Permission.php
- `{closure#1}()` --references--> `PublishAnnouncement`  [EXTRACTED]
  tests/Feature/AnnouncementTest.php → app/Jobs/PublishAnnouncement.php
- `{closure#2}()` --calls--> `Role`  [EXTRACTED]
  database/seeders/RolePermissionSeeder.php → app/Models/Role.php

## Import Cycles
- None detected.

## Communities (137 total, 35 thin omitted)

### Community 0 - "User"
Cohesion: 0.06
Nodes (15): User, UserPolicy, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon, Illuminate\Support\Facades\RateLimiter (+7 more)

### Community 1 - "reka-ui"
Cohesion: 0.05
Nodes (40): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+32 more)

### Community 2 - "@vueuse/core"
Cohesion: 0.05
Nodes (37): @vueuse/core, delegatedProps, emits, forwarded, props, emits, modelValue, props (+29 more)

### Community 3 - "Login.vue"
Cohesion: 0.11
Nodes (18): ref_inertiajs_core, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, Props, { verify, isLoading, error, isSupported }, Props, resources_js_components_ui_checkbox_index_checkbox (+10 more)

### Community 4 - "@lucide/vue"
Cohesion: 0.06
Nodes (35): @lucide/vue, presentation, props, Status, resources_js_components_ui_badge_index_badge, resources_js_components_ui_button_index_button, Category, form (+27 more)

### Community 5 - "Role"
Cohesion: 0.13
Nodes (11): MakeSuperAdmin, Role, DatabaseSeeder, {closure#1}(), {closure#2}(), RolePermissionSeeder, Illuminate\Console\Command, Illuminate\Database\Console\Seeds\WithoutModelEvents (+3 more)

### Community 6 - "Laravel\Fortify\Features"
Cohesion: 0.09
Nodes (9): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Laravel\Fortify\Features, PasswordResetTest (+1 more)

### Community 7 - "UserNotification"
Cohesion: 0.12
Nodes (14): {closure#1}(), GenerateReportExport, {closure#2}(), PublishAnnouncement, SendWhatsappNotification, UserNotification, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue (+6 more)

### Community 8 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.08
Nodes (5): Answer, AssessmentAttempt, LearningPathAssignment, ReportExport, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 9 - "label/index.ts"
Cohesion: 0.08
Nodes (14): resources_js_components_ui_label_index_label, Announcement, form, Lesson, lessonForm, Module, moduleForm, props (+6 more)

### Community 10 - "Inertia\Response"
Cohesion: 0.08
Nodes (18): {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}(), DashboardController, {closure#3}(), LearningProgressController, MyLearningPathController (+10 more)

### Community 11 - "Chisel"
Cohesion: 0.10
Nodes (28): {closure#1}(), InstallFeaturesCommand, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode(), {closure#1}(), {closure#10}() (+20 more)

### Community 12 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 13 - "CourseCategory"
Cohesion: 0.13
Nodes (6): CourseCategory, CourseManagementTest, AssessmentAttemptTest, {closure#1}(), CourseLibraryTest, LearningFlowTest

### Community 14 - "@inertiajs/vue3"
Cohesion: 0.10
Nodes (14): @inertiajs/vue3, page, Activity, labels, props, Course, Enrollment, Stat (+6 more)

### Community 15 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.11
Nodes (9): {closure#1}(), AssessmentResponse, Branch, LearningPathCourse, LessonProgress, Permission, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model (+1 more)

### Community 16 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.08
Nodes (11): Question, 1. Prinsip data, 2. Entitas dan relasi utama, 3. Constraint penting, 4. Index awal berdasarkan access pattern, 5. Query dan pagination, 6. Cache data, 7. Backup, retention, dan privacy (+3 more)

### Community 17 - "TwoFactorSetupModal.vue"
Cohesion: 0.06
Nodes (38): resources_js_actions_app_http_controllers_settings_profilecontroller, passwordInput, emit, handleDelete(), isDeleting, props, code, { copy, copied } (+30 more)

### Community 18 - "bootstrap/app.php"
Cohesion: 0.10
Nodes (16): EnsureActiveAccount, HandleAppearance, HandleInertiaRequests, RecordAuditActivity, {closure#1}(), {closure#2}(), {closure#3}(), Closure (+8 more)

### Community 19 - "package.json"
Cohesion: 0.10
Nodes (19): private, $schema, type, clsx, concurrently, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc (+11 more)

### Community 20 - "Course"
Cohesion: 0.17
Nodes (8): AssessmentController, {closure#1}(), AssessmentAttemptController, {closure#2}(), {closure#3}(), Assessment, Course, Illuminate\Http\RedirectResponse

### Community 21 - "Layout.vue"
Cohesion: 0.12
Nodes (15): resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator, resources_js_components_ui_separator_index_separator, Props, { isCurrentOrParentUrl }, sidebarNavItems (+7 more)

### Community 22 - "Illuminate\Http\Request"
Cohesion: 0.19
Nodes (5): AnnouncementController, CourseCategoryController, LearningPathController, ReportController, Illuminate\Http\Request

### Community 23 - "lib/utils.ts"
Cohesion: 0.06
Nodes (35): props, delegatedProps, props, props, SidebarMenuButtonVariants, SidebarProps, { isMobile, state, openMobile, setOpenMobile }, props (+27 more)

### Community 24 - "LearningPath"
Cohesion: 0.18
Nodes (5): LearningPathAssignmentController, {closure#2}(), LearningPathContentController, LearningPath, Illuminate\Validation\ValidationException

### Community 25 - "Show.vue"
Cohesion: 0.33
Nodes (4): Assessment, Lesson, Module, props

### Community 26 - "SheetContent.vue"
Cohesion: 0.08
Nodes (17): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+9 more)

### Community 27 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (24): footerNavItems, mainNavItems, page, Props, { isCurrentUrl }, { isMobile, state }, page, user (+16 more)

### Community 30 - "CourseContentController.php"
Cohesion: 0.18
Nodes (9): {closure#1}(), {closure#2}(), {closure#3}(), CourseContentController, {closure#7}(), CourseModule, Lesson, LessonResource (+1 more)

### Community 31 - "ProcessUserImport.php"
Cohesion: 0.12
Nodes (6): {closure#6}(), {closure#7}(), {closure#8}(), {closure#9}(), Division, Position

### Community 32 - "AppHeader.vue"
Cohesion: 0.09
Nodes (22): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, Props, resources_js_components_ui_navigation_menu_index_navigationmenu (+14 more)

### Community 33 - "dependencies"
Cohesion: 0.12
Nodes (16): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+8 more)

### Community 34 - "learning-paths/Content.vue"
Cohesion: 0.25
Nodes (5): Course, form, PathCourse, props, sequences

### Community 35 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.08
Nodes (25): Props, uniqueErrors, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle (+17 more)

### Community 36 - "ProcessUserImport"
Cohesion: 0.24
Nodes (3): {closure#1}(), ProcessUserImport, UserImport

### Community 37 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 38 - "compilerOptions"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 39 - "vue"
Cohesion: 0.07
Nodes (18): vue, props, props, props, props, props, props, props (+10 more)

### Community 40 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 41 - "Controller"
Cohesion: 0.12
Nodes (8): EnrollmentController, UserImportController, AuditLogController, Controller, NotificationController, ProfileController, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Foundation\Auth\Access\AuthorizesRequests

### Community 42 - "useAppearance.ts"
Cohesion: 0.12
Nodes (21): vue-sonner, { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+13 more)

### Community 43 - "ProfileValidationRules"
Cohesion: 0.20
Nodes (6): CreateNewUser, ResetUserPassword, ProfileValidationRules, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 45 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 46 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.20
Nodes (10): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+2 more)

### Community 47 - "PRD — KGJ Academy LMS"
Cohesion: 0.12
Nodes (15): 10. UAT dan definition of done, 1. Ringkasan, 2. Tujuan dan ukuran keberhasilan, 3. Pengguna dan hak akses, 4. Scope MVP, 5. Alur inti, 6. Aturan bisnis yang tidak boleh berubah tanpa persetujuan pemilik proses, 7. Kebutuhan fungsional per area (+7 more)

### Community 48 - "settings.php"
Cohesion: 0.22
Nodes (3): SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route

### Community 49 - "UserFactory.php"
Cohesion: 0.27
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, static

### Community 50 - "UserInfo.vue"
Cohesion: 0.19
Nodes (11): resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, { getInitials }, Props, showAvatar, getInitial(), getInitials() (+3 more)

### Community 51 - "input/index.ts"
Cohesion: 0.10
Nodes (15): inputRef, props, showPassword, resources_js_components_ui_input_index_input, Assessment, createForm, props, questions (+7 more)

### Community 52 - "ActivityLog"
Cohesion: 0.25
Nodes (3): {closure#2}(), ActivityLog, self

### Community 53 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 54 - "TestCase"
Cohesion: 0.06
Nodes (26): Illuminate\Foundation\Http\Middleware\PreventRequestForgery, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Inertia\Testing\AssertableInertia, AssessmentManagementTest, {closure#1}(), AuditLogTest, {closure#1}() (+18 more)

### Community 55 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 56 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 57 - "Product"
Cohesion: 0.15
Nodes (12): Accessibility & Inclusion, Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product (+4 more)

### Community 58 - "types/index.ts"
Cohesion: 0.11
Nodes (21): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+13 more)

### Community 59 - "courses/Form.vue"
Cohesion: 0.15
Nodes (9): Option, props, Course, Option, props, Course, form, Option (+1 more)

### Community 60 - "SidebarProvider.vue"
Cohesion: 0.15
Nodes (17): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+9 more)

### Community 61 - "users/Form.vue"
Cohesion: 0.15
Nodes (9): Option, props, ManagedUser, Option, props, form, ManagedUser, Option (+1 more)

### Community 62 - "PasswordValidationRules"
Cohesion: 0.16
Nodes (8): PasswordValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 64 - "FortifyServiceProvider.php"
Cohesion: 0.14
Nodes (10): {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#9}() (+2 more)

### Community 65 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 66 - "Design UI/UX — KGJ Academy"
Cohesion: 0.17
Nodes (11): 1. Arah yang mengikat, 2. Information architecture, 3. Layar minimum dan responsif, 4. Sistem komponen awal, 5. Aksesibilitas dan interaksi, 6. Kinerja UI, 7. State yang wajib dirancang, 8. Validasi implementasi (+3 more)

### Community 67 - "FortifyServiceProvider"
Cohesion: 0.24
Nodes (3): AppServiceProvider, FortifyServiceProvider, Illuminate\Support\ServiceProvider

### Community 68 - "System Architecture"
Cohesion: 0.18
Nodes (10): 1. Prinsip arsitektur, 2. Modul aplikasi, 3. Jalur request, 4. Topologi Docker lokal, 5. Keamanan dan ketahanan, 6. Scale path, Learner, Pekerjaan berat (+2 more)

### Community 69 - "Tech Stack dan Pedoman Optimasi"
Cohesion: 0.18
Nodes (10): Background processing, Batas keamanan, Cache dan deduplikasi, Database dan response, Frontend, media, dan CDN, Kebijakan optimasi, Keputusan stack, Struktur Docker (+2 more)

### Community 72 - "Announcement"
Cohesion: 0.23
Nodes (3): {closure#1}(), Announcement, AnnouncementTest

### Community 74 - "Production Deployment Runbook"
Cohesion: 0.29
Nodes (6): 1. Prasyarat, 2. Deploy aplikasi, 3. Backup dan restore database, 4. Monitoring dan respons insiden, 5. Checklist rilis, Production Deployment Runbook

### Community 75 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 76 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 77 - "Badge.vue"
Cohesion: 0.16
Nodes (8): class-variance-authority, props, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 78 - "reports/Index.vue"
Cohesion: 0.22
Nodes (7): applyFilters(), clearFilters(), Enrollment, Export, exporting, Filters, props

### Community 79 - "ManageTwoFactor.vue"
Cohesion: 0.15
Nodes (11): resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, { hasSetupData, clearTwoFactorAuthData }, Props, showSetupModal (+3 more)

### Community 80 - "ReportEnrollmentQuery.php"
Cohesion: 0.46
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), Illuminate\Database\Eloquent\Builder

### Community 81 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, php (+1 more)

### Community 82 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 83 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 84 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 85 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 86 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 87 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 88 - "Assessment.vue"
Cohesion: 0.25
Nodes (8): form, formattedTime, props, Question, remainingSeconds, submit(), timeExpired, updateTimer()

### Community 93 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 94 - "AppServiceProvider.php"
Cohesion: 0.40
Nodes (5): {closure#1}(), Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\Facades\DB, Illuminate\Validation\Rules\Password

### Community 95 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 96 - "vite.config.ts"
Cohesion: 0.25
Nodes (7): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue, vitePort

### Community 98 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 99 - "2026_10_09_000004_create_learning_catalog_tables.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 100 - "2026_10_12_000016_add_timing_to_assessments.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 101 - "PasskeyRegister.vue"
Cohesion: 0.25
Nodes (5): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm

### Community 102 - "team/Index.vue"
Cohesion: 0.33
Nodes (5): applyFilters(), Enrollment, Filters, props, resetFilters()

### Community 103 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 104 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 105 - "2026_10_09_000001_create_organization_tables.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 106 - "Implementation Roadmap — KGJ Academy LMS"
Cohesion: 0.33
Nodes (5): Confirmed Phase 1 decisions, Cross-cutting definition of done, Delivery sequence, Implementation Roadmap — KGJ Academy LMS, Performance implementation checkpoints

### Community 108 - "Performance Baseline"
Cohesion: 0.40
Nodes (4): Database, Frontend, Performance Baseline, Queue dan cache

### Community 110 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 111 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 112 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 122 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

### Community 123 - "ReportTest.php"
Cohesion: 0.16
Nodes (8): Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Queue, Illuminate\Support\Facades\Storage, LessonResourceTest, {closure#1}(), {closure#2}(), ReportTest, {closure#1}()

### Community 126 - "patch-wayfinder.mjs"
Cohesion: 0.50
Nodes (3): ref_node_fs, patched, path

## Knowledge Gaps
- **626 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+621 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 957 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **35 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `reka-ui`, `@vueuse/core`, `@lucide/vue`, `dialog/index.ts`, `TwoFactorSetupModal.vue`, `package.json`, `lib/utils.ts`, `SheetContent.vue`, `AppSidebar.vue`, `AppHeader.vue`, `learning-paths/Content.vue`, `TwoFactorRecoveryCodes.vue`, `SelectContent.vue`, `InputOTPSlot.vue`, `useAppearance.ts`, `UserInfo.vue`, `input/index.ts`, `global.d.ts`, `types/index.ts`, `SidebarProvider.vue`, `Badge.vue`, `reports/Index.vue`, `ManageTwoFactor.vue`, `SidebarMenuSkeleton.vue`, `useCurrentUrl.ts`, `Assessment.vue`, `AvatarFallback.vue`, `PasskeyRegister.vue`, `TooltipContent.vue`?**
  _High betweenness centrality (0.071) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Role`, `Laravel\Fortify\Features`, `UserNotification`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `OrganizationManagementTest.php`, `Inertia\Response`, `CourseCategory`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Course`, `LearningPath`, `ProcessUserImport.php`, `Controller`, `ProfileValidationRules`, `UserFactory.php`, `ActivityLog`, `TestCase`, `FortifyServiceProvider.php`, `Announcement`, `UserController.php`, `DashboardTest`, `LearningPathManagementTest.php`, `ReportTest.php`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `Login.vue`, `@lucide/vue`, `label/index.ts`, `TwoFactorSetupModal.vue`, `package.json`, `Layout.vue`, `lib/utils.ts`, `Show.vue`, `AppSidebar.vue`, `AppHeader.vue`, `learning-paths/Content.vue`, `TwoFactorRecoveryCodes.vue`, `useAppearance.ts`, `input/index.ts`, `types/index.ts`, `courses/Form.vue`, `users/Form.vue`, `reports/Index.vue`, `ManageTwoFactor.vue`, `useCurrentUrl.ts`, `Assessment.vue`, `team/Index.vue`, `Import.vue`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _626 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.05576441102756892 - nodes in this community are weakly interconnected._
- **Should `reka-ui` be split into smaller, more focused modules?**
  _Cohesion score 0.04612159329140461 - nodes in this community are weakly interconnected._
- **Should `@vueuse/core` be split into smaller, more focused modules?**
  _Cohesion score 0.0473469387755102 - nodes in this community are weakly interconnected._