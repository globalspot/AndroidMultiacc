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
  - index(Request): Main devices page: resolves default group; loads accessible devices via `DeviceService`; sorts; builds stats; prepares creation lists; caches ordered IDs in session; returns `devices.index`.
  - show(Request, $deviceId): AuthZ on assignment/admin; returns `devices.show` (not present in repo, referenced).
  - assign(Request): Admin-only; uses `DeviceService::assignDeviceToUser`.
  - unassign(Request, $deviceId, $userId): Admin-only; removes assignment.
  - createGroup(Request): Admin-only; creates `DeviceGroup`.
  - search(Request): Unicode-safe search via `DeviceService::searchAccessibleDevices`.
  - startDevice/stopDevice/getDeviceStatus: AuthZ + start/stop/status via `DeviceService`; enforce group limit on start.
  - refreshAllDevices(): Batch normalize status, attach capabilities and stats for background refresh.
  - refreshScreenshots(): Returns hashes for online devices only.
  - getScreenshot(): Returns base64 screenshot.
  - updateCustomName/deleteCustomName: Manage custom device names.
  - getGroupLimit/updateGroupLimit/getDevicesByGateUrl: Admin-only group limit and gate device queries.
  - chunk(Request): Infinite scroll; slices session-cached order with optional filtering; renders partials to HTML.
  - createDevice(Request): Inserts new row into `mysql_second.goProfiles`; auto-assigns creator as `owner`.
  - batchCreate(Request): Creates N devices with selections/jittered coordinates; auto-assigns as `owner`.
- UserAssignmentController.php (Admin + Manager flows)
  - index(): Admin UI for assigning users to groups.
  - assignUserToGroup(), removeUserFromGroup(): Admin APIs to manage `UserGroupAssignment`; auto-downgrade role if no manager assignments remain.
  - deviceAssignmentInterface(): Manager UI with manageable users/devices, latest active invites.
  - assignDeviceToUser(): Manager API to assign device if device belongs to group gate.
  - removeDeviceAssignment(): Manager API; deactivates.
  - getGroupUsers(): Manager API; lists users in a managed group.
  - getGateUrlDevices(): Manager API; lists devices from the group's gate, merged with latest custom names.
  - getMyAssignments(), getManagedAssignments(): Manager APIs returning decorated assignments with device info and custom names, sorted by custom-name, then name.
  - getFreeDeviceStats(), assignFreeDevicesToUser(): Manager tools for bulk free-device allocation.
- GroupInviteController.php
  - generate(): Manager generates or returns latest valid invite; returns URL to `group-invites.show`.
  - show(): Shows invite landing (login-required or confirm view) with validity info.
  - accept(): Authenticated user accepts invite; activates/creates `UserGroupAssignment` as member; increments uses and auto-deactivates when max reached.

## app/Http/Middleware
- CheckRole.php: Role gate `admin|manager|user|admin_or_manager` against `User` helpers.
- SetLocale.php: Resolves locale from session or `Accept-Language`; sets app locale and persists to session.

## app/Http/Requests
- Auth/LoginRequest.php: Validates and rate-limits login; authenticates via `Auth::attempt`.
- ProfileUpdateRequest.php: Validates name/email uniqueness (ignores current user).

## app/Services
- DeviceService.php (business logic around devices)
  - normalizeForSearch(string?): Robust Unicode normalization and lowercasing.
  - getAllDevices(), getDeviceById(): Read from `mysql_second.goProfiles`.
  - getDevicesByIdsForUser(User, array): Ordered fetch + decorate with assignment/group/custom-name/port.
  - getAccessibleDevicesForUser(User, filterGroupId?, filterUserId?): Build from `User::getAccessibleDevices()`; fetch device rows; decorate; dedupe for admin/manager; sort by custom-name, then name, then id.
  - assignDeviceToUser(), removeDeviceAssignment(), createDeviceGroup(), getAllDeviceGroups().
  - getDeviceStatistics(User): Counts of devices online/total, groups and users (admin only).
  - searchDevices(query): Simple DB like; superseded by searchAccessibleDevices.
  - searchAccessibleDevices(User, query): Unicode-safe in-memory filter; returns normalized subset.
  - startDevice($deviceId, $userId?): Enforce group device_limit if known; set statusDate to end-of-day; mark starting.
  - stopDevice($deviceId): Set sessionStatus finished; statusDate end-of-day.
  - getDeviceStatus(), canStartDevice(), canStopDevice().
  - saveCustomDeviceName(), deleteCustomDeviceName().
  - updateDeviceGroupLimit(), getDeviceGroupLimitInfo().
  - getAvailableGateUrls(), getDevicesByGateUrl().
  - requestScreenshot(), checkAndUpdateStatusDates(), extractPortFromAddress(), getDeviceScreenshot().

## app/Models (Laravel DB)
- User.php
  - Role helpers: isAdmin(), isManager(), isUser(), isAdminOrManager().
  - Relationships: deviceAssignments(), customDeviceNames(), userGroupAssignments(), memberGroups(), managerGroups(), allGroups().
  - Access helpers: getAccessibleDevices() returns `DeviceAssignment` collection filtered by role; isMemberOfGroup(), isManagerOfGroup(); getManageableUsers(), getManageableDevices() via gate URLs.
  - getCustomDeviceName($deviceId): Per-user override.
- DeviceAssignment.php
  - Fillable: user_id, device_group_id, device_id, access_level, is_active; belongsTo user, deviceGroup.
  - getDeviceInfo(): Lookup from `mysql_second.goProfiles`.
  - hasAccessLevel(level): user < manager < owner.
- DeviceGroup.php
  - Fillable: name, description, device_limit, gate_url.
  - Relationships: deviceAssignments(), users(), devices(), userGroupAssignments(), assignedUsers(), managers(), members().
  - Group limit helpers: getRunningDevicesCount() (online in `goProfiles`), hasReachedLimit(), getRemainingSlots().
  - Membership checks: hasUser($userId), hasManager($userId).
- UserGroupAssignment.php
  - Fillable: user_id, device_group_id, role, is_active; belongsTo user, deviceGroup; isManager(), isMember().
- GroupInvite.php
  - Fillable: device_group_id, manager_id, token, expires_at, max_uses, uses, is_active; relations to DeviceGroup/User; isValid().
- CustomDeviceName.php
  - Fillable: device_id, user_id, custom_name; belongsTo user.
- HardwareProfile.php (string id), OsImage.php (string id): Simple fillables for device creation metadata.

## app/View/Components (Blade components)
- AppLayout.php: Renders `layouts.app`.
- GuestLayout.php: Renders `layouts.guest`.
- LanguageSwitcher.php: Provides current/available locales and renders `components/language-switcher`.
- LanguageSwitcherForm.php: Blade component class exists (not read here); pairs with view in `resources/views/components`.

## routes
- web.php
  - `/` redirects to `login`.
  - `/dashboard` protected by `auth,verified`.
  - Role groups: `role:admin|manager|user` map to `DashboardController` methods.
  - Auth routes for profile CRUD; device routes: index/chunk/create/batchCreate/assign/unassign/cancelAssignment/createGroup/search;
    automation: start/stop/status; background refresh; screenshots; custom-name; admin group limit and gate devices;
    manager device assignment suite; group invite generate/accept/show; request-screenshot.
  - `language.switch` route.
  - Includes `auth.php`.
- auth.php: Breeze auth routes for login/register/password/email verification/logout.
- console.php: Example `inspire` command.

## resources/views (selected)
- devices/index.blade.php: Main devices UI, filters, and controls (large; supports infinite scroll via `chunk` and partials).
- devices/partials/card-list.blade.php: Per-device card; shows status, port badge, group limit info; inline JS hooks for start/stop/refresh and custom name editing.
- devices/partials/table-rows.blade.php: Tabular rendering (used when `view=table`).
- dashboard.blade.php and `layouts/*`: App shell and dashboard.
- auth/*.blade.php: Breeze views.
- profile/edit.blade.php + partials: Profile UI.
- invites/*.blade.php: Invite flow pages (login-required, confirm, accepted, invalid).
- user-assignments/*.blade.php: Admin and manager assignment UIs.
- components/*.blade.php: UI primitives and language switcher.

## config
- database.php: Default sqlite; `mysql` for app; `mysql_second` external organic DB; Redis config.
- app.php, auth.php, cache.php, filesystems.php, logging.php, mail.php, queue.php, services.php, session.php: Standard Laravel configs.

## database
- migrations: Users/cache/jobs; domain tables: user_group_assignments, device_assignments, custom_device_names, device_groups device_limit/gate_url, group_invites, hardware_profiles, os_images.
- seeders: DatabaseSeeder, HardwareProfilesSeeder, OsImagesSeeder.
- factories: UserFactory.
- database.sqlite: Default local DB file.

## tests
- Feature/LanguageDetectionTest.php: Ensures `SetLocale` behavior and controller helpers.
- Feature/ProfileTest.php: Profile CRUD and auth assertions.
- Feature/ExampleTest.php: Root route returns 200.
- Unit/ExampleTest.php: Sanity.
- Tests/TestCase.php: Base (inherits Laravel Harness in app bootstrap).

## Notable Flows and Cross-Cutting Concerns
- Dual DB pattern: Laravel DB for users/groups/assignments; external `mysql_second.goProfiles` for devices. All device metadata read through `DeviceService` or raw queries in controllers.
- Role enforcement: Route middleware `role:*` → `CheckRole`; per-action checks inside controllers as well.
- Device group limits: Checked on start; group exposes helpers for counts and limits.
- Custom device names: Global for admin/manager previews (latest wins), per-user for regular users.
- Infinite scroll: Session-cached order token; server slices IDs and returns partial HTML.

## Suggested Entry Points for Agents
- Add a new device action: touch `routes/web.php`, add method in `DeviceController`, business rule in `DeviceService`.
- Change search behavior: edit `DeviceService::searchAccessibleDevices` and front-end filter usage in views.
- Modify group limits: `DeviceService::updateDeviceGroupLimit` and `DeviceGroup` helpers.
- Manager assignment UX: `UserAssignmentController` + `resources/views/user-assignments/*`.

End of file.
