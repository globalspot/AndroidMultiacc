# Project Summary: Device Management System

## Overview
This Laravel application manages device assignments, user permissions, device groups, and now adds APK management and bulk app installation tasks. It integrates with an external MySQL database ("organic" via `mysql_second`) for device metadata, and supports role-based access control (admin, manager, user). Multi-language (en/ru) and locale auto-detection are provided.

## Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Databases**: SQLite (default), MySQL (primary), MySQL `mysql_second` (external devices/tasks)
- **Build**: Vite, PostCSS
- **Testing**: PHPUnit

## Key Modules
- **Device Management**: Assign/unassign devices, view status and screenshots, start/stop automation, infinite scrolling, custom device names.
- **Group Management**: Create groups; control device limits; gate URL association; user and manager membership; created-devices limit.
- **Assignments**: Admin assigns users to groups; managers assign devices within managed groups; bulk free-device allocation.
- **Invites**: Group invite link generation and acceptance.
- **Localization**: Session-based locale with Accept-Language detection; language switcher UI component.
- **APK Management (Admin)**: Scan `/public/apks` and enable/disable APKs per app group; optional additional library APKs and install order; icons.
- **Apps Installation (All Authenticated)**: UI to select app/version and devices, pick runtime permissions, and create app install tasks against `mysql_second.app_install_tasks`. Optionally requires devices to be offline based on APK entry configuration.

## Routes Highlights
- Dashboard by role: `/admin`, `/manager`, `/user`.
- Devices: list, chunk, create, batch create, assign/unassign/cancel, search, start/stop/status, screenshots, background refresh, custom names.
- Device limits (admin): get/update running device limit; get/update created-device limit.
- User assignments (admin): index, assign/remove user-group links.
- Manager assignments: device assignment CRUD; group users/devices; my/managed assignments; bulk free-device stats and allocation; group invites generate.
- Group invites public: invite landing and accept.
- APK admin: `/admin/apks` index, enable, disable.
- Apps: `/apps` catalog, `/apps/devices` list accessible devices with latest custom names, `/apps/tasks` create install tasks.
- Language: `GET /language/{locale}`.

## Data Model (Primary DB)
- `users`: with `role` and auth data.
- `device_groups`: name, description, `device_limit`, `gate_url`, `created_device_limit` (new).
- `device_assignments`: user-device mapping with `device_group_id`, `access_level`, `is_active`.
- `user_group_assignments`: user-group membership and role (member/manager), `is_active`.
- `custom_device_names`: per-user overrides of device display names.
- `apk_entries`: enabled APK records (app_name, filename, version, urls, lib settings, icon, offline_required).

## Data Model (mysql_second)
- `goProfiles`: external devices with status, OS, platform, screenView, gateUrl, etc.
- `app_install_tasks`: queued install tasks (device_id, app fields, permissions JSON, status fields).

## Notable Services and Logic
- `DeviceService`
  - Fetch/compose accessible devices with assignments, custom names, ports, and group info.
  - Unicode-safe device search.
  - Start/stop device with limit enforcement.
  - Group limits info and updates, including created-device limits.
  - Gate URL helpers and screenshot requests.
- `AdminApkController`: scans `/public/apks` groups, resolves icons, reads/writes `ApkEntry` records; enable/disable/update.
- `AppsController`: serves catalog view, exposes accessible devices JSON (with latest custom names), and creates install tasks with optional offline-only enforcement.

## Views
- Devices pages and partials: card/table rendering, controls, statistics, created-limit UI.
- User assignments (admin/manager) and invites pages.
- APK admin (`resources/views/admin/apks/index.blade.php`): grouped APKs, per-file enable/disable, library selection, offline flag.
- Apps catalog (`resources/views/apps/index.blade.php`): app cards with version selector, device picker modal, permissions picker, task creation.
- Components: `language-switcher`, `user-menu`, UI primitives, layouts.

## Security & Middleware
- `CheckRole` middleware for role-based route protection.
- `SetLocale` middleware for language detection and persistence.
- CSRF on forms and API endpoints; validation on controllers/requests.

## i18n
- `resources/lang/en|ru/app.php`: extensive strings including APK management, apps install flow, permissions dictionary, devices, groups, assignments, invites.

## Testing
- Feature tests for locale detection and profile CRUD; base example tests.

## Deployment & Dev Notes
- Configure DB connections in `config/database.php` (including `mysql_second`).
- Ensure `/public/apks` folder structure for APK admin usage.
- Vite/Tailwind build for assets.
