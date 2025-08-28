# Project File-by-File Summary (for LLM agents)

This document maps each significant file to its purpose, notable functions/classes, and how it interacts with other parts of the system. Use this as a quick-reference index when navigating or modifying the codebase.

## Root
- artisan: Laravel CLI entry.
- composer.json: PHP deps; scripts for dev/test; PSR-4 autoloads.
- composer.lock: Locked PHP deps.
- package.json: Frontend deps; Vite/Tailwind scripts.
- phpunit.xml: PHPUnit config.
- postcss.config.js: PostCSS config.
- tailwind.config.js: Tailwind config.
- vite.config.js: Vite config.
- README.md: Laravel scaffold docs.
- stagewise.json: Local dev config (ports/plugins).
- assign_devices.php: Standalone bulk assignment script; scans `mysql_second.goProfiles` and upserts `DeviceAssignment` for a target user.
- public/index.php: HTTP front controller.
- public/favicon.ico, robots.txt, scrcpy.zip: Static assets.
- PROJECT_SUMMARY.md: High-level description (keep for overview).
- PROJECT_FILES_SUMMARY.md: This file.

## app/Providers
- AppServiceProvider.php: Placeholder for app bootstrapping/service bindings (currently empty hooks).

## app/Http/Controllers (Core MVC controllers)
- Controller.php: Base controller.
- DashboardController.php
  - index(Request): Renders `dashboard` with user role flags and active group assignments (`UserGroupAssignment`).
  - admin(), manager(), user(): Role-specific dashboards (views expected under `resources/views/dashboard.*`).
- ProfileController.php
  - edit(): Renders `profile.edit` with current user.
  - update(ProfileUpdateRequest): Updates user; resets email verification on change.
  - destroy(Request): Validates password; deletes account and logs out.
- LanguageController.php
  - switchLanguage(Request, $locale): Persist `en|ru` to session and redirect back.
  - static getCurrentLocale(): Session locale or null.
  - static getAvailableLocales(): Map of locales.
- DeviceController.php
  - index(Request): Main devices page; loads accessible devices via `DeviceService`; sorts; builds stats (incl. created-device stats); prepares creation lists; caches ordered IDs; returns `devices.index`.
  - show(Request, $deviceId): AuthZ on assignment/admin; returns `devices.show` (referenced).
  - assign/unassign/cancelAssignment: Admin and manager flows for device assignments.
  - createGroup(): Admin-only create `DeviceGroup`.
  - search(): Unicode-safe search via `DeviceService::searchAccessibleDevices`.
  - startDevice/stopDevice/getDeviceStatus: AuthZ + start/stop/status via `DeviceService` with group limit enforcement.
  - refreshAllDevices()/refreshScreenshots()/getScreenshot(): Background updates and screenshot endpoints.
  - updateCustomName/deleteCustomName: Custom device names.
  - getGroupLimit/updateGroupLimit: Running device limits (admin).
  - getCreatedGroupLimit/updateCreatedGroupLimit: Created-device limits (admin) — returns `DeviceService::getDeviceGroupLimitInfo` and updates via `DeviceService::updateCreatedDeviceGroupLimit`.
  - getDevicesByGateUrl(): Admin-only list by gate URL.
  - chunk(Request): Infinite scroll; returns HTML for `cards` or `table` view.
  - createDevice()/batchCreate(): Insert into `mysql_second.goProfiles` and auto-assign creator as owner.
- UserAssignmentController.php (Admin + Manager flows)
  - Admin: index, assignUserToGroup, removeUserFromGroup (role sync).
  - Manager: deviceAssignmentInterface; assignDeviceToUser/removeDeviceAssignment; getGroupUsers/getGateUrlDevices; getMyAssignments/getManagedAssignments; getFreeDeviceStats/assignFreeDevicesToUser.
- GroupInviteController.php
  - generate/show/accept: Invite lifecycle for group membership.
- AdminApkController.php (Admin only)
  - index(): Scans `/public/apks` by app group; resolves icons; aggregates available library APKs; displays enabled entries from `ApkEntry`.
  - enable(): Upsert `ApkEntry` with URL, version, icon, library settings, offline flag.
  - disable(): Delete `ApkEntry` for app/filename.
  - extractVersionFromFilename(): Best-effort version inference from filename.
- AppsController.php
  - index(): Lists enabled APK entries grouped by `app_name` for the catalog.
  - devices(): Returns accessible devices with latest custom names for selection.
  - createTasks(): Validates payload; enforces offline-only constraint if configured in `ApkEntry`; inserts rows into `mysql_second.app_install_tasks` with optional permissions JSON.

## app/Http/Middleware
- CheckRole.php: Role gate `admin|manager|user|admin_or_manager` against `User` helpers.
- SetLocale.php: Resolves locale from session or `Accept-Language`; sets app locale and persists to session.

## app/Http/Requests
- Auth/LoginRequest.php: Validates and rate-limits login; authenticates via `Auth::attempt`.
- ProfileUpdateRequest.php: Validates name/email uniqueness (ignores current user).

## app/Services
- DeviceService.php (business logic around devices)
  - normalizeForSearch(string?): Unicode normalization and lowercasing.
  - getAllDevices(), getDeviceById().
  - getDevicesByIdsForUser(User, array): Ordered fetch + decorate with assignment/group/custom-name/port.
  - getAccessibleDevicesForUser(User, filterGroupId?, filterUserId?): Compose devices; dedupe for admin/manager; sort.
  - getDeviceStatistics(User): Adds created-device stats when group filter present.
  - searchDevices(query), searchAccessibleDevices(User, query).
  - startDevice($deviceId, $userId?), stopDevice($deviceId), getDeviceStatus(), canStartDevice(), canStopDevice().
  - saveCustomDeviceName(), deleteCustomDeviceName().
  - updateDeviceGroupLimit(), getDeviceGroupLimitInfo() including created-device metrics; updateCreatedDeviceGroupLimit(); getOrCreateGroupByGateUrl(); getAvailableGateUrls(); getDevicesByGateUrl(); requestScreenshot(); checkAndUpdateStatusDates(); extractPortFromAddress(); getDeviceScreenshot().

## app/Models (Primary unless noted)
- User.php: Role helpers; relationships; access helpers; custom name getter; manageable users/devices via gate URLs.
- DeviceAssignment.php: Fillables; belongsTo user/group; getDeviceInfo(); hasAccessLevel().
- DeviceGroup.php: Fillables include `created_device_limit`; relationships; running/created device counters; remaining slots; created-limit helpers.
- UserGroupAssignment.php: Fillables; belongsTo user/group; isManager()/isMember().
- GroupInvite.php: Fillables; belongsTo device group/manager; isValid().
- CustomDeviceName.php: Fillables; belongsTo user.
- HardwareProfile.php, OsImage.php: String primary keys; simple fillables.
- ApkEntry.php: Enabled APK registry (app/filename/version/urls/icon/lib/offline/add_date).
- AppInstallTask.php (mysql_second): App install tasks table mapping; JSON cast for permissions.

## app/View/Components (Blade components)
- AppLayout.php: Renders `layouts.app`.
- GuestLayout.php: Renders `layouts.guest`.
- LanguageSwitcher.php: Provides locales to `components/language-switcher`.
- LanguageSwitcherForm.php: Component class present; pairs with `components/language-switcher-form`.
- components/user-menu.blade.php: Dropdown with profile link and logout.

## routes
- web.php
  - `/` → login redirect; `/dashboard` protected.
  - Role groups: `admin`, `manager`, `user` dashboards.
  - Devices: index/chunk/create/batchCreate/assign/unassign/cancelAssignment/createGroup/search; start/stop/status; refresh/screenshot/custom-name; admin limits (running + created), gate devices; manager assignment suite; group invites generate/accept/show; request-screenshot.
  - Admin APK: `/admin/apks` index, POST enable/disable.
  - Apps: `/apps` (catalog), `/apps/devices` (JSON), POST `/apps/tasks` (create install tasks).
  - Language switching route.
- auth.php: Breeze auth routes.
- console.php: Example `inspire` command.

## resources/views (selected)
- devices/index.blade.php: Main UI incl. created-device limit block and stats.
- devices/partials/card-list.blade.php, table-rows.blade.php: Card/table renderers.
- admin/apks/index.blade.php: APK groups, enable/disable/update with library selection and offline flag.
- apps/index.blade.php: Apps catalog with version selector, device picker modal, permissions chooser, and task submission.
- user-assignments/*.blade.php: Admin/manager UIs; device-assignment interface (large view).
- invites/*.blade.php: Invite flow pages.
- components/user-menu.blade.php, language-switcher*.blade.php, and UI primitives.
- layouts/*.blade.php, dashboard.blade.php, welcome.blade.php.

## config
- database.php: sqlite default; `mysql` app DB; `mysql_second` external; Redis.
- app.php, auth.php, cache.php, filesystems.php, logging.php, mail.php, queue.php, services.php, session.php.

## database
- migrations: users/cache/jobs; domain tables including user_group_assignments, device_assignments, custom_device_names, device_groups (with `device_limit` and `created_device_limit`), group_invites, hardware_profiles, os_images; plus created-device limit migration.
- seeders: DatabaseSeeder, HardwareProfilesSeeder, OsImagesSeeder.
- factories: UserFactory.
- database.sqlite: Local DB.

## tests
- Feature/LanguageDetectionTest.php: Locale middleware + controller helpers.
- Feature/ProfileTest.php: Profile CRUD.
- Feature/ExampleTest.php; Unit/ExampleTest.php; Tests/TestCase.php.

## Notable Flows and Cross-Cutting Concerns
- Dual DB: primary Laravel DB for users/groups/assignments/APK entries; external `mysql_second` for devices and app install tasks.
- Role enforcement: `role:*` middleware + explicit checks in controllers.
- Group limits: running and created-device limits with safety checks against current counts.
- Custom device names: global latest for admin/manager; per-user for regular users.
- Infinite scroll: session-cached order token → precise slicing and server-side filtering windows.
- APK admin: filesystem scan synchronized to `ApkEntry` registry; optional additional library with installation order.
- Apps tasks: optional offline-only enforcement per APK entry; permissions are optional array saved as JSON.

## Suggested Entry Points for Agents
- Extend APK admin: update `AdminApkController` and `resources/views/admin/apks/index.blade.php`.
- Add new app install flags: extend `AppInstallTask` columns and `AppsController::createTasks`.
- Modify created-device limit logic: `DeviceGroup`, `DeviceService`, devices view.
- Evolve device search/sort: `DeviceService::searchAccessibleDevices` and devices views.

End of file.
