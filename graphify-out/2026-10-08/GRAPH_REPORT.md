# Graph Report - KGJ-lms-academy  (2026-10-08)

## Corpus Check
- 272 files · ~37,085 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 18, .example 2, .conf 1)

## Summary
- 1283 nodes · 2337 edges · 90 communities (74 shown, 16 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 10 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- vue
- Product
- Design UI/UX — KGJ Academy
- PRD — KGJ Academy LMS
- System Architecture
- Tech Stack dan Pedoman Optimasi
- Database System
- Implementation Roadmap — KGJ Academy LMS
- types/index.ts
- Login.vue
- TwoFactorRecoveryCodes.vue
- Chisel
- @vueuse/core
- sidebar/index.ts
- navigation-menu/index.ts
- User
- Illuminate\Database\Schema\Blueprint
- TwoFactorSetupModal.vue
- Illuminate\Foundation\Http\FormRequest
- AppHeader.vue
- compilerOptions
- TestCase
- package.json
- Layout.vue
- DeleteUser.vue
- FortifyServiceProvider
- FortifyServiceProvider.php
- AppSidebar.vue
- SidebarProvider.vue
- ProfileController.php
- sheet/index.ts
- aliases
- dependencies
- InputOTPSlot.vue
- UserFactory.php
- UserMenuContent.vue
- useAppearance.ts
- select/index.ts
- tooltip/index.ts
- scripts
- Laravel\Fortify\Features
- PasskeyRegister.vue
- @inertiajs/vue3
- PasswordValidationRules
- composer.json
- PasswordResetTest.php
- require-dev
- devDependencies
- NavigationMenu.vue
- global.d.ts
- bootstrap/app.php
- AuthenticationTest
- Collapsible.vue
- reka-ui
- SheetContent.vue
- useCurrentUrl.ts
- require
- settings.php
- optionalDependencies
- ProfileValidationRules
- button/index.ts
- DialogContent.vue
- Sheet.vue
- SidebarMenuSkeleton.vue
- config
- VerificationNotificationTest.php
- scripts
- Sonner.vue
- dialog/index.ts
- SelectContent.vue
- Separator.vue
- HandleAppearance.php
- SecurityTest
- psr-4
- laravel
- logging.php
- DatabaseSeeder.php
- @lucide/vue
- EmailVerificationTest.php
- console.php
- Select.vue
- artisan

## God Nodes (most connected - your core abstractions)
1. `vue` - 117 edges
2. `cn()` - 93 edges
3. `reka-ui` - 77 edges
4. `User` - 58 edges
5. `@vueuse/core` - 45 edges
6. `@inertiajs/vue3` - 38 edges
7. `@lucide/vue` - 33 edges
8. `TestCase` - 27 edges
9. `compilerOptions` - 19 edges
10. `scripts` - 13 edges

## Surprising Connections (you probably didn't know these)
- `CreateNewUser` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Actions/Fortify/CreateNewUser.php → app/Concerns/ProfileValidationRules.php
- `PasswordUpdateRequest` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/PasswordUpdateRequest.php → app/Concerns/PasswordValidationRules.php
- `ProfileDeleteRequest` --mixes_in--> `PasswordValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileDeleteRequest.php → app/Concerns/PasswordValidationRules.php
- `ProfileUpdateRequest` --mixes_in--> `ProfileValidationRules`  [EXTRACTED]
  app/Http/Requests/Settings/ProfileUpdateRequest.php → app/Concerns/ProfileValidationRules.php
- `AuthenticationTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Auth/AuthenticationTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (90 total, 16 thin omitted)

### Community 0 - "vue"
Cohesion: 0.05
Nodes (39): vue, props, props, props, AlertVariants, props, props, props (+31 more)

### Community 1 - "Product"
Cohesion: 0.15
Nodes (12): Accessibility & Inclusion, Brand Commitments, Capabilities and Constraints, Evidence on Hand, Operating Context, Platform, Positioning, Product (+4 more)

### Community 2 - "Design UI/UX — KGJ Academy"
Cohesion: 0.17
Nodes (11): 1. Arah yang mengikat, 2. Information architecture, 3. Layar minimum dan responsif, 4. Sistem komponen awal, 5. Aksesibilitas dan interaksi, 6. Kinerja UI, 7. State yang wajib dirancang, 8. Validasi implementasi (+3 more)

### Community 3 - "PRD — KGJ Academy LMS"
Cohesion: 0.12
Nodes (15): 10. UAT dan definition of done, 1. Ringkasan, 2. Tujuan dan ukuran keberhasilan, 3. Pengguna dan hak akses, 4. Scope MVP, 5. Alur inti, 6. Aturan bisnis yang tidak boleh berubah tanpa persetujuan pemilik proses, 7. Kebutuhan fungsional per area (+7 more)

### Community 4 - "System Architecture"
Cohesion: 0.18
Nodes (10): 1. Prinsip arsitektur, 2. Modul aplikasi, 3. Jalur request, 4. Topologi Docker lokal, 5. Keamanan dan ketahanan, 6. Scale path, Learner, Pekerjaan berat (+2 more)

### Community 5 - "Tech Stack dan Pedoman Optimasi"
Cohesion: 0.18
Nodes (10): Background processing, Batas keamanan, Cache dan deduplikasi, Database dan response, Frontend, media, dan CDN, Kebijakan optimasi, Keputusan stack, Struktur Docker (+2 more)

### Community 6 - "Database System"
Cohesion: 0.20
Nodes (9): 1. Prinsip data, 2. Entitas dan relasi utama, 3. Constraint penting, 4. Index awal berdasarkan access pattern, 5. Query dan pagination, 6. Cache data, 7. Backup, retention, dan privacy, Database System (+1 more)

### Community 7 - "Implementation Roadmap — KGJ Academy LMS"
Cohesion: 0.33
Nodes (5): Confirmed Phase 1 decisions, Cross-cutting definition of done, Delivery sequence, Implementation Roadmap — KGJ Academy LMS, Performance implementation checkpoints

### Community 8 - "types/index.ts"
Cohesion: 0.10
Nodes (22): className, Props, Props, Props, resources_js_components_ui_breadcrumb_index_breadcrumb, resources_js_components_ui_breadcrumb_index_breadcrumbitem, resources_js_components_ui_breadcrumb_index_breadcrumblink, resources_js_components_ui_breadcrumb_index_breadcrumblist (+14 more)

### Community 9 - "Login.vue"
Cohesion: 0.09
Nodes (24): ref_inertiajs_core, inputRef, props, showPassword, Props, resources_js_components_ui_checkbox_index_checkbox, resources_js_components_ui_input_index_input, emits (+16 more)

### Community 10 - "TwoFactorRecoveryCodes.vue"
Cohesion: 0.07
Nodes (29): Props, uniqueErrors, { hasSetupData, clearTwoFactorAuthData }, showSetupModal, isRecoveryCodesVisible, recoveryCodeSectionRef, { recoveryCodesList, fetchRecoveryCodes, errors }, resources_js_components_ui_alert_index_alert (+21 more)

### Community 11 - "Chisel"
Cohesion: 0.09
Nodes (29): {closure#1}(), InstallFeaturesCommand, Chisel, chiselRemoveNpmPackages(), chiselRun(), chiselSkipsNode(), {closure#1}(), {closure#10}() (+21 more)

### Community 12 - "@vueuse/core"
Cohesion: 0.08
Nodes (18): @vueuse/core, delegatedProps, props, delegatedProps, forwardedProps, props, delegatedProps, forwardedProps (+10 more)

### Community 13 - "sidebar/index.ts"
Cohesion: 0.10
Nodes (20): resources_js_components_ui_sheet_index_sheet, resources_js_components_ui_sheet_index_sheetcontent, SidebarMenuButtonVariants, SidebarProps, { isMobile, state, openMobile, setOpenMobile }, props, delegatedProps, { isMobile, state } (+12 more)

### Community 14 - "navigation-menu/index.ts"
Cohesion: 0.07
Nodes (22): class-variance-authority, delegatedProps, props, BadgeVariants, navigationMenuTriggerStyle, delegatedProps, emits, forwarded (+14 more)

### Community 15 - "User"
Cohesion: 0.11
Nodes (13): User, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Illuminate\Support\Carbon (+5 more)

### Community 16 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.12
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+6 more)

### Community 17 - "TwoFactorSetupModal.vue"
Cohesion: 0.09
Nodes (19): code, { copy, copied }, isOpen, modalConfig, pinInputContainerRef, Props, { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors }, { resolvedAppearance } (+11 more)

### Community 18 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.24
Nodes (6): PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 19 - "AppHeader.vue"
Cohesion: 0.12
Nodes (14): auth, { isCurrentUrl, whenCurrentUrl }, mainNavItems, page, Props, rightNavItems, Props, resources_js_components_ui_navigation_menu_index_navigationmenu (+6 more)

### Community 20 - "compilerOptions"
Cohesion: 0.10
Nodes (20): compilerOptions, allowJs, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, jsxImportSource, lib (+12 more)

### Community 21 - "TestCase"
Cohesion: 0.13
Nodes (8): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, RegistrationTest, TwoFactorChallengeTest, DashboardTest, ExampleTest, TestCase, ExampleTest

### Community 22 - "package.json"
Cohesion: 0.09
Nodes (25): private, $schema, type, clsx, concurrently, @inertiajs/vite, @laravel/multiplex, laravel-vite-plugin (+17 more)

### Community 23 - "Layout.vue"
Cohesion: 0.10
Nodes (19): resources_js_actions_app_http_controllers_settings_profilecontroller, resources_js_actions_app_http_controllers_settings_securitycontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyregistrationcontroller_destroy, Props, Props, Props, { isCurrentOrParentUrl } (+11 more)

### Community 24 - "DeleteUser.vue"
Cohesion: 0.15
Nodes (16): passwordInput, emit, handleDelete(), isDeleting, props, resources_js_components_ui_dialog_index_dialog, resources_js_components_ui_dialog_index_dialogclose, resources_js_components_ui_dialog_index_dialogcontent (+8 more)

### Community 25 - "FortifyServiceProvider"
Cohesion: 0.15
Nodes (8): AppServiceProvider, {closure#1}(), FortifyServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\Facades\Date, Illuminate\Support\Facades\DB, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 26 - "FortifyServiceProvider.php"
Cohesion: 0.16
Nodes (12): HandleInertiaRequests, {closure#1}(), {closure#10}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#8}(), {closure#9}() (+4 more)

### Community 27 - "AppSidebar.vue"
Cohesion: 0.11
Nodes (22): footerNavItems, mainNavItems, Props, { isCurrentUrl }, { isMobile, state }, page, user, resources_js_components_ui_dropdown_menu_index_dropdownmenu (+14 more)

### Community 28 - "SidebarProvider.vue"
Cohesion: 0.16
Nodes (16): emits, isMobile, open, openMobile, props, setOpen(), setOpenMobile(), state (+8 more)

### Community 29 - "ProfileController.php"
Cohesion: 0.18
Nodes (8): Controller, ProfileController, SecurityController, ProfileUpdateRequest, Illuminate\Http\RedirectResponse, Illuminate\Support\Facades\Auth, Inertia\Inertia, Inertia\Response

### Community 30 - "sheet/index.ts"
Cohesion: 0.13
Nodes (8): props, delegatedProps, props, props, props, delegatedProps, props, props

### Community 31 - "aliases"
Cohesion: 0.12
Nodes (15): aliases, components, composables, lib, ui, utils, iconLibrary, $schema (+7 more)

### Community 32 - "dependencies"
Cohesion: 0.12
Nodes (16): dependencies, class-variance-authority, clsx, @inertiajs/vite, @inertiajs/vue3, @laravel/passkeys, laravel-vite-plugin, @lucide/vue (+8 more)

### Community 33 - "InputOTPSlot.vue"
Cohesion: 0.13
Nodes (12): vue-input-otp, delegatedProps, emits, forwarded, props, forwarded, props, context (+4 more)

### Community 34 - "UserFactory.php"
Cohesion: 0.16
Nodes (6): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql, static

### Community 35 - "UserMenuContent.vue"
Cohesion: 0.10
Nodes (17): props, resources_js_components_ui_avatar_index_avatar, resources_js_components_ui_avatar_index_avatarfallback, resources_js_components_ui_avatar_index_avatarimage, resources_js_components_ui_dropdown_menu_index_dropdownmenugroup, resources_js_components_ui_dropdown_menu_index_dropdownmenuitem, resources_js_components_ui_dropdown_menu_index_dropdownmenulabel, resources_js_components_ui_dropdown_menu_index_dropdownmenuseparator (+9 more)

### Community 36 - "useAppearance.ts"
Cohesion: 0.15
Nodes (18): { appearance, updateAppearance }, tabs, appearance, getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), mediaQuery(), prefersDark() (+10 more)

### Community 37 - "select/index.ts"
Cohesion: 0.07
Nodes (18): props, delegatedProps, forwardedProps, props, props, props, delegatedProps, forwardedProps (+10 more)

### Community 38 - "tooltip/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 39 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 40 - "Laravel\Fortify\Features"
Cohesion: 0.22
Nodes (7): Inertia\Testing\AssertableInertia, Laravel\Fortify\Features, {closure#1}(), PasswordConfirmationTest, {closure#1}(), {closure#1}(), {closure#2}()

### Community 41 - "PasskeyRegister.vue"
Cohesion: 0.25
Nodes (5): @laravel/passkeys, emit, name, { register, isLoading, error, isSupported }, showForm

### Community 42 - "@inertiajs/vue3"
Cohesion: 0.18
Nodes (9): @inertiajs/vue3, page, { title = '', description = '' }, initializeFlashToast(), resources_js_routes_index, resources_js_routes_index_dashboard, resources_js_routes_index_home, resources_js_routes_index_login (+1 more)

### Community 43 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (6): CreateNewUser, ResetUserPassword, PasswordValidationRules, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 44 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 46 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, larastan/larastan, laravel/pail, laravel/pao, laravel/pint, laravel/sail, mockery/mockery (+2 more)

### Community 47 - "devDependencies"
Cohesion: 0.20
Nodes (10): devDependencies, concurrently, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, @types/node, typescript, vite, vite-plus (+2 more)

### Community 48 - "NavigationMenu.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, delegatedProps, forwardedProps, props

### Community 49 - "global.d.ts"
Cohesion: 0.22
Nodes (9): Auth, ComponentCustomProperties, GlobalDirectives, ImportMeta, ImportMetaEnv, InertiaConfig, @inertiajs/core, vite/client (+1 more)

### Community 50 - "bootstrap/app.php"
Cohesion: 0.28
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets

### Community 52 - "Collapsible.vue"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 53 - "reka-ui"
Cohesion: 0.06
Nodes (32): reka-ui, emits, forwarded, props, delegatedProps, emits, forwarded, props (+24 more)

### Community 54 - "SheetContent.vue"
Cohesion: 0.22
Nodes (7): delegatedProps, emits, forwarded, props, SheetContentProps, delegatedProps, props

### Community 55 - "useCurrentUrl.ts"
Cohesion: 0.33
Nodes (8): currentUrlReactive, page, useCurrentUrl(), isCurrentOrParentUrl(), isCurrentUrl(), whenCurrentUrl(), UseCurrentUrlReturn, toUrl()

### Community 56 - "require"
Cohesion: 0.22
Nodes (9): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, php (+1 more)

### Community 58 - "optionalDependencies"
Cohesion: 0.25
Nodes (8): optionalDependencies, @laravel/multiplex, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 60 - "button/index.ts"
Cohesion: 0.14
Nodes (14): resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_index, resources_js_actions_laravel_passkeys_http_controllers_passkeyconfirmationcontroller_store, Props, { verify, isLoading, error, isSupported }, Props, resources_js_components_ui_button_index_button, ButtonVariants (+6 more)

### Community 61 - "DialogContent.vue"
Cohesion: 0.25
Nodes (6): delegatedProps, emits, forwarded, props, delegatedProps, props

### Community 62 - "Sheet.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

### Community 63 - "SidebarMenuSkeleton.vue"
Cohesion: 0.25
Nodes (5): props, width, resources_js_components_ui_skeleton_index_skeleton, props, SkeletonProps

### Community 64 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 65 - "VerificationNotificationTest.php"
Cohesion: 0.29
Nodes (3): Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, VerificationNotificationTest

### Community 66 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, build, build:ssr, check, check:fix, dev, types:check

### Community 68 - "dialog/index.ts"
Cohesion: 0.22
Nodes (5): emits, forwarded, props, props, props

### Community 69 - "SelectContent.vue"
Cohesion: 0.29
Nodes (6): resources_js_components_ui_select_index_selectscrolldownbutton, resources_js_components_ui_select_index_selectscrollupbutton, delegatedProps, emits, forwarded, props

### Community 70 - "Separator.vue"
Cohesion: 0.29
Nodes (4): resources_js_components_ui_separator_index_separator, delegatedProps, props, props

### Community 71 - "HandleAppearance.php"
Cohesion: 0.47
Nodes (4): HandleAppearance, Closure, Illuminate\Support\Facades\View, Symfony\Component\HttpFoundation\Response

### Community 73 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 74 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 75 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 76 - "DatabaseSeeder.php"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 77 - "@lucide/vue"
Cohesion: 0.08
Nodes (18): @lucide/vue, delegatedProps, emits, forwarded, props, delegatedProps, emits, forwarded (+10 more)

### Community 81 - "EmailVerificationTest.php"
Cohesion: 0.50
Nodes (3): Illuminate\Auth\Events\Verified, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\URL

### Community 89 - "Select.vue"
Cohesion: 0.50
Nodes (3): emits, forwarded, props

## Knowledge Gaps
- **514 isolated node(s):** `$schema`, `style`, `config`, `css`, `baseColor` (+509 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 687 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `vue` connect `vue` to `types/index.ts`, `Login.vue`, `TwoFactorRecoveryCodes.vue`, `@vueuse/core`, `sidebar/index.ts`, `navigation-menu/index.ts`, `TwoFactorSetupModal.vue`, `AppHeader.vue`, `package.json`, `Layout.vue`, `DeleteUser.vue`, `AppSidebar.vue`, `SidebarProvider.vue`, `sheet/index.ts`, `InputOTPSlot.vue`, `UserMenuContent.vue`, `useAppearance.ts`, `select/index.ts`, `PasskeyRegister.vue`, `@inertiajs/vue3`, `NavigationMenu.vue`, `global.d.ts`, `reka-ui`, `SheetContent.vue`, `useCurrentUrl.ts`, `button/index.ts`, `DialogContent.vue`, `SidebarMenuSkeleton.vue`, `SelectContent.vue`, `Separator.vue`, `@lucide/vue`?**
  _High betweenness centrality (0.153) - this node is a cross-community bridge._
- **Why does `reka-ui` connect `reka-ui` to `vue`, `Login.vue`, `@vueuse/core`, `sidebar/index.ts`, `navigation-menu/index.ts`, `package.json`, `SidebarProvider.vue`, `sheet/index.ts`, `InputOTPSlot.vue`, `UserMenuContent.vue`, `select/index.ts`, `tooltip/index.ts`, `NavigationMenu.vue`, `Collapsible.vue`, `SheetContent.vue`, `button/index.ts`, `DialogContent.vue`, `Sheet.vue`, `dialog/index.ts`, `SelectContent.vue`, `Separator.vue`, `@lucide/vue`, `Select.vue`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Why does `@inertiajs/vue3` connect `@inertiajs/vue3` to `vue`, `UserMenuContent.vue`, `types/index.ts`, `Login.vue`, `TwoFactorRecoveryCodes.vue`, `TwoFactorSetupModal.vue`, `AppHeader.vue`, `useCurrentUrl.ts`, `package.json`, `Layout.vue`, `DeleteUser.vue`, `AppSidebar.vue`, `button/index.ts`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **What connects `$schema`, `style`, `config` to the rest of the system?**
  _514 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `vue` be split into smaller, more focused modules?**
  _Cohesion score 0.05094905094905095 - nodes in this community are weakly interconnected._
- **Should `PRD — KGJ Academy LMS` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._
- **Should `types/index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.1028225806451613 - nodes in this community are weakly interconnected._