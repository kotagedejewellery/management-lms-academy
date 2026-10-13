# Graph Report - KGJ-lms-academy  (2026-10-09)

## Corpus Check
- 387 files · ~56,727 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 18, .example 2, .conf 1)

## Summary
- 1984 nodes · 4264 edges · 117 communities (86 shown, 31 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 22 edges (avg confidence: 0.9)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Model
- TestCase
- User
- Login.vue
- @lucide/vue
- Role
- reka-ui
- @vueuse/core
- UserNotification
- input/index.ts
- Enrollment
- Chisel
- vue
- Illuminate\Http\RedirectResponse
- dialog/index.ts
- bootstrap/app.php
- @inertiajs/vue3
- TwoFactorSetupModal.vue
- Course
- Illuminate\Http\Request
- SheetContent.vue
- Layout.vue
- Inertia\Response
- UserInfo.vue
- CourseContentController.php
- compilerOptions
- package.json
- AppSidebar.vue
- resources_js_routes_register_index
- resources_js_routes_register_index_store
- InputOTPSlot.vue
- PasswordValidationRules
- AppHeader.vue
- UserController.php
- aliases
- TwoFactorRecoveryCodes.vue
- Illuminate\Database\Schema\Blueprint
- SelectContent.vue
- PRD — KGJ Academy LMS
- dependencies
- lib/utils.ts
- FortifyServiceProvider.php
- useAppearance.ts
- Badge.vue
- scripts
- Product
- AppServiceProvider.php
- ActivityLog
- CourseLibraryController.php
- composer.json
- Design UI/UX — KGJ Academy
- OrganizationController.php
- System Architecture
- Tech Stack dan Pedoman Optimasi
- require-dev
- UserFactory.php
- devDependencies
- reports/Index.vue
- types/index.ts
- global.d.ts
- SidebarProvider.vue
- AssessmentController.php
- require
- SidebarMenuSkeleton.vue
- Illuminate\Support\Facades\Schema
- Illuminate\Database\Migrations\Migration
- PasskeyRegister.vue
- Collapsible.vue
- tooltip/index.ts
- useCurrentUrl.ts
- ref_routes_register
- resources_js_routes_index_register
- courses/Content.vue
- ProfileValidationRules
- AssessmentAttemptController.php
- ReportEnrollmentQuery.php
- Database System
- LessonResourceTest.php
- optionalDependencies
- AvatarFallback.vue
- learning-paths/Content.vue
- config
- 2026_10_09_000004_create_learning_catalog_tables.php
- scripts
- vite.config.ts
- team/Index.vue
- FortifyServiceProvider
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000002_create_jobs_table.php
- 2026_10_09_000001_create_organization_tables.php
- Implementation Roadmap — KGJ Academy LMS
- AnnouncementTest.php
- artisan
- ResetUserPassword.php
- psr-4
- laravel
- Illuminate\Support\Str
- logging.php
- 0001_01_01_000001_create_cache_table.php
- 2025_08_14_170933_add_two_factor_columns_to_users_table.php
- 2026_10_09_000003_add_organization_to_users_table.php
- 2026_10_09_000005_create_learning_path_tables.php
- 2026_10_09_000007_create_enrollment_progress_tables.php
- 2026_10_09_000009_add_reporting_indexes_to_enrollments_table.php
- 2026_10_09_000012_add_phone_to_users_table.php
- 2026_10_09_000013_create_announcements_and_user_notifications_tables.php
- TooltipContent.vue
- 2026_10_09_000014_create_activity_logs_table.php
- console.php

## God Nodes (most connected - your core abstractions)
1. `User` - 133 edges
2. `vue` - 127 edges
3. `cn()` - 93 edges
4. `reka-ui` - 77 edges
5. `Course` - 73 edges
6. `@inertiajs/vue3` - 63 edges
7. `TestCase` - 56 edges
8. `@lucide/vue` - 54 edges
9. `@vueuse/core` - 45 edges
10. `Controller` - 38 edges

## Surprising Connections (you probably didn't know these)
- `{closure#2}()` --references--> `GenerateReportExport`  [EXTRACTED]
  tests/Feature/ReportTest.php → app/Jobs/GenerateReportExport.php
- `{closure#1}()` --references--> `PublishAnnouncement`  [EXTRACTED]
  tests/Feature/AnnouncementTest.php → app/Jobs/PublishAnnouncement.php
- `{closure#1}()` --calls--> `Permission`  [EXTRACTED]
  database/seeders/RolePermissionSeeder.php → app/Models/Permission.php
- `{closure#2}()` --calls--> `Role`  [EXTRACTED]
  database/seeders/RolePermissionSeeder.php → app/Models/Role.php
- `{closure#1}()` --references--> `CourseCategory`  [EXTRACTED]
  app/Http/Controllers/Admin/CourseCategoryController.php → app/Models/CourseCategory.php

## Import Cycles
- None detected.

## Communities (117 total, 31 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.04
Nodes (19): {closure#1}(), Answer, Assessment, AssessmentAttempt, AssessmentResponse, Branch, Division, LearningPathAssignment (+11 more)

### Community 1 - "TestCase"
Cohesion: 0.05
Nodes (31): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Foundation\Http\Middleware\PreventRequestForgery, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification (+23 more)

### Community 2 - "User"
Cohesion: 0.05
Nodes (16): User, UserPolicy, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon (+8 more)

### Community 3 - "Login.vue"
Cohesion: 0.11
Nodes (18): ref_inertiajs_core, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, Props, { verify, isLoading, error, isSupported }, Props, resources_js_components_ui_checkbox_index_checkbox (+10 more)

### Community 4 - "@lucide/vue"
Cohesion: 0.05
Nodes (39): @lucide/vue, presentation, props, Status, resources_js_components_ui_badge_index_badge, resources_js_components_ui_button_index_button, Category, form (+31 more)

### Community 5 - "Role"
Cohesion: 0.07
Nodes (19): CourseCategory, Permission, Role, DatabaseSeeder, {closure#1}(), {closure#2}(), RolePermissionSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents (+11 more)

### Community 6 - "reka-ui"
Cohesion: 0.05
Nodes (40): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+32 more)

### Community 7 - "@vueuse/core"
Cohesion: 0.05
Nodes (37): @vueuse/core, delegatedProps, emits, forwarded, props, emits, modelValue, props (+29 more)

### Community 8 - "UserNotification"
Cohesion: 0.08
Nodes (19): {closure#1}(), {closure#1}(), {closure#2}(), GenerateReportExport, {closure#2}(), PublishAnnouncement, SendWhatsappNotification, Announcement (+11 more)

### Community 9 - "input/index.ts"
Cohesion: 0.05
Nodes (31): inputRef, props, showPassword, resources_js_components_ui_input_index_input, resources_js_components_ui_label_index_label, Announcement, form, Assessment (+23 more)

### Community 10 - "Enrollment"
Cohesion: 0.07
Nodes (14): {closure#1}(), {closure#1}(), {closure#1}(), {closure#4}(), {closure#3}(), {closure#1}(), ReportController, {closure#2}() (+6 more)

### Community 11 - "Chisel"
Cohesion: 0.09
Nodes (30): {closure#1}(), InstallFeaturesCommand, MakeSuperAdmin, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode(), {closure#1}() (+22 more)

### Community 12 - "vue"
Cohesion: 0.07
Nodes (18): vue, props, props, props, props, props, props, props (+10 more)

### Community 13 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.09
Nodes (10): AnnouncementController, CourseCategoryController, Controller, NotificationController, ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Foundation\Auth\Access\AuthorizesRequests (+2 more)

### Community 14 - "dialog/index.ts"
Cohesion: 0.06
Nodes (23): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+15 more)

### Community 15 - "bootstrap/app.php"
Cohesion: 0.10
Nodes (16): EnsureActiveAccount, HandleAppearance, HandleInertiaRequests, RecordAuditActivity, {closure#1}(), {closure#2}(), {closure#3}(), Closure (+8 more)

### Community 16 - "@inertiajs/vue3"
Cohesion: 0.08
Nodes (17): @inertiajs/vue3, page, Activity, labels, props, Course, Enrollment, Stat (+9 more)

### Community 17 - "TwoFactorSetupModal.vue"
Cohesion: 0.07
Nodes (35): passwordInput, emit, handleDelete(), isDeleting, props, code, { copy, copied }, isOpen (+27 more)

### Community 18 - "Course"
Cohesion: 0.16
Nodes (5): {closure#4}(), CourseController, AssessmentAttemptController, Course, Illuminate\Database\Eloquent\Relations\HasManyThrough

### Community 19 - "Illuminate\Http\Request"
Cohesion: 0.20
Nodes (6): LearningPathAssignmentController, {closure#2}(), LearningPathContentController, LearningPathController, LearningPath, Illuminate\Http\Request

### Community 20 - "SheetContent.vue"
Cohesion: 0.08
Nodes (17): emits, forwarded, props, props, delegatedProps, emits, forwarded, props (+9 more)

### Community 21 - "Layout.vue"
Cohesion: 0.06
Nodes (29): resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, { hasSetupData, clearTwoFactorAuthData }, Props (+21 more)

### Community 22 - "Inertia\Response"
Cohesion: 0.12
Nodes (7): EnrollmentController, AuditLogController, DashboardController, LearningController, LearningProgressController, MyLearningPathController, Inertia\Response

### Community 23 - "UserInfo.vue"
Cohesion: 0.19
Nodes (11): resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, { getInitials }, Props, showAvatar, getInitial(), getInitials() (+3 more)

### Community 24 - "CourseContentController.php"
Cohesion: 0.20
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), CourseContentController, {closure#7}(), CourseModule, Lesson, Symfony\Component\HttpFoundation\StreamedResponse

### Community 25 - "compilerOptions"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 26 - "package.json"
Cohesion: 0.10
Nodes (19): private, $schema, type, clsx, concurrently, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc (+11 more)

### Community 27 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (24): footerNavItems, mainNavItems, page, Props, { isCurrentUrl }, { isMobile, state }, page, user (+16 more)

### Community 30 - "InputOTPSlot.vue"
Cohesion: 0.11
Nodes (15): vue-input-otp, delegatedProps, emits, forwarded, props, delegatedProps, forwarded, props (+7 more)

### Community 31 - "PasswordValidationRules"
Cohesion: 0.17
Nodes (8): PasswordValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 32 - "AppHeader.vue"
Cohesion: 0.09
Nodes (22): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, Props, resources_js_components_ui_navigation_menu_index_navigationmenu (+14 more)

### Community 34 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 35 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.08
Nodes (25): Props, uniqueErrors, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_alert_index_alert, resources_js_components_ui_alert_index_alertdescription, resources_js_components_ui_alert_index_alerttitle (+17 more)

### Community 36 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.20
Nodes (10): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+2 more)

### Community 37 - "SelectContent.vue"
Cohesion: 0.05
Nodes (27): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, emits, forwarded, props, delegatedProps, emits, forwarded (+19 more)

### Community 38 - "PRD — KGJ Academy LMS"
Cohesion: 0.12
Nodes (15): 10. UAT dan definition of done, 1. Ringkasan, 2. Tujuan dan ukuran keberhasilan, 3. Pengguna dan hak akses, 4. Scope MVP, 5. Alur inti, 6. Aturan bisnis yang tidak boleh berubah tanpa persetujuan pemilik proses, 7. Kebutuhan fungsional per area (+7 more)

### Community 39 - "dependencies"
Cohesion: 0.12
Nodes (16): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+8 more)

### Community 40 - "lib/utils.ts"
Cohesion: 0.06
Nodes (35): props, delegatedProps, props, props, SidebarMenuButtonVariants, SidebarProps, { isMobile, state, openMobile, setOpenMobile }, props (+27 more)

### Community 41 - "FortifyServiceProvider.php"
Cohesion: 0.13
Nodes (11): {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#9}() (+3 more)

### Community 42 - "useAppearance.ts"
Cohesion: 0.12
Nodes (21): vue-sonner, { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery() (+13 more)

### Community 43 - "Badge.vue"
Cohesion: 0.16
Nodes (8): class-variance-authority, props, props, props, AlertVariants, delegatedProps, props, BadgeVariants

### Community 44 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 45 - "Product"
Cohesion: 0.15
Nodes (12): Accessibility & Inclusion, Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product (+4 more)

### Community 46 - "AppServiceProvider.php"
Cohesion: 0.21
Nodes (6): AppServiceProvider, {closure#1}(), Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 47 - "ActivityLog"
Cohesion: 0.18
Nodes (5): {closure#2}(), ActivityLog, self, AuditLogTest, {closure#1}()

### Community 49 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 50 - "Design UI/UX — KGJ Academy"
Cohesion: 0.17
Nodes (11): 1. Arah yang mengikat, 2. Information architecture, 3. Layar minimum dan responsif, 4. Sistem komponen awal, 5. Aksesibilitas dan interaksi, 6. Kinerja UI, 7. State yang wajib dirancang, 8. Validasi implementasi (+3 more)

### Community 52 - "System Architecture"
Cohesion: 0.18
Nodes (10): 1. Prinsip arsitektur, 2. Modul aplikasi, 3. Jalur request, 4. Topologi Docker lokal, 5. Keamanan dan ketahanan, 6. Scale path, Learner, Pekerjaan berat (+2 more)

### Community 53 - "Tech Stack dan Pedoman Optimasi"
Cohesion: 0.18
Nodes (10): Background processing, Batas keamanan, Cache dan deduplikasi, Database dan response, Frontend, media, dan CDN, Kebijakan optimasi, Keputusan stack, Struktur Docker (+2 more)

### Community 54 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 55 - "UserFactory.php"
Cohesion: 0.27
Nodes (4): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, static

### Community 56 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 57 - "reports/Index.vue"
Cohesion: 0.22
Nodes (7): applyFilters(), clearFilters(), Enrollment, Export, exporting, Filters, props

### Community 58 - "types/index.ts"
Cohesion: 0.11
Nodes (21): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+13 more)

### Community 59 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 60 - "SidebarProvider.vue"
Cohesion: 0.15
Nodes (17): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+9 more)

### Community 62 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, php (+1 more)

### Community 63 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 64 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 65 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 66 - "PasskeyRegister.vue"
Cohesion: 0.25
Nodes (5): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm

### Community 67 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 68 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 69 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 72 - "courses/Content.vue"
Cohesion: 0.22
Nodes (5): Lesson, lessonForm, Module, moduleForm, props

### Community 73 - "ProfileValidationRules"
Cohesion: 0.39
Nodes (3): CreateNewUser, ProfileValidationRules, Laravel\Fortify\Contracts\CreatesNewUsers

### Community 75 - "ReportEnrollmentQuery.php"
Cohesion: 0.46
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), Illuminate\Database\Eloquent\Builder

### Community 76 - "Database System"
Cohesion: 0.25
Nodes (7): 1. Prinsip data, 2. Entitas dan relasi utama, 3. Constraint penting, 5. Query dan pagination, 6. Cache data, 7. Backup, retention, dan privacy, Database System

### Community 77 - "LessonResourceTest.php"
Cohesion: 0.36
Nodes (3): Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Storage, LessonResourceTest

### Community 78 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 79 - "AvatarFallback.vue"
Cohesion: 0.25
Nodes (4): props, delegatedProps, props, props

### Community 80 - "learning-paths/Content.vue"
Cohesion: 0.25
Nodes (5): Course, form, PathCourse, props, sequences

### Community 81 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 82 - "2026_10_09_000004_create_learning_catalog_tables.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 83 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 84 - "vite.config.ts"
Cohesion: 0.29
Nodes (6): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite-plus, @vitejs/plugin-vue

### Community 85 - "team/Index.vue"
Cohesion: 0.33
Nodes (5): applyFilters(), Enrollment, Filters, props, resetFilters()

### Community 87 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 88 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 89 - "2026_10_09_000001_create_organization_tables.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 90 - "Implementation Roadmap — KGJ Academy LMS"
Cohesion: 0.33
Nodes (5): Confirmed Phase 1 decisions, Cross-cutting definition of done, Delivery sequence, Implementation Roadmap — KGJ Academy LMS, Performance implementation checkpoints

### Community 93 - "ResetUserPassword.php"
Cohesion: 0.50
Nodes (3): ResetUserPassword, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 94 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 95 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 97 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 106 - "TooltipContent.vue"
Cohesion: 0.40
Nodes (4): delegatedProps, emits, forwarded, props

## Knowledge Gaps
- **599 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+594 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 907 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **31 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Model`, `UserController.php`, `TestCase`, `Role`, `UserNotification`, `ProfileValidationRules`, `Enrollment`, `Chisel`, `FortifyServiceProvider.php`, `LessonResourceTest.php`, `ActivityLog`, `Illuminate\Http\Request`, `Inertia\Response`, `UserFactory.php`, `AnnouncementTest.php`, `ResetUserPassword.php`, `PasswordValidationRules`?**
  _High betweenness centrality (0.069) - this node is a cross-community bridge._
- **Why does `vue` connect `vue` to `@lucide/vue`, `reka-ui`, `@vueuse/core`, `input/index.ts`, `dialog/index.ts`, `TwoFactorSetupModal.vue`, `SheetContent.vue`, `Layout.vue`, `UserInfo.vue`, `package.json`, `AppSidebar.vue`, `InputOTPSlot.vue`, `AppHeader.vue`, `TwoFactorRecoveryCodes.vue`, `SelectContent.vue`, `lib/utils.ts`, `useAppearance.ts`, `Badge.vue`, `reports/Index.vue`, `types/index.ts`, `global.d.ts`, `SidebarProvider.vue`, `SidebarMenuSkeleton.vue`, `PasskeyRegister.vue`, `useCurrentUrl.ts`, `AvatarFallback.vue`, `learning-paths/Content.vue`, `TooltipContent.vue`?**
  _High betweenness centrality (0.063) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `AppHeader.vue`, `Login.vue`, `types/index.ts`, `@lucide/vue`, `TwoFactorRecoveryCodes.vue`, `useCurrentUrl.ts`, `lib/utils.ts`, `input/index.ts`, `useAppearance.ts`, `courses/Content.vue`, `learning-paths/Content.vue`, `TwoFactorSetupModal.vue`, `Layout.vue`, `team/Index.vue`, `reports/Index.vue`, `package.json`, `AppSidebar.vue`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _599 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Model` be split into smaller, more focused modules?**
  _Cohesion score 0.0438871473354232 - nodes in this community are weakly interconnected._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.05060882800608828 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.05367231638418079 - nodes in this community are weakly interconnected._