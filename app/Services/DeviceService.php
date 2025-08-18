<?php

namespace App\Services;

use App\Models\CustomDeviceName;
use App\Models\DeviceAssignment;
use App\Models\DeviceGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeviceService
{
    /**
     * Normalize strings for robust Unicode search
     */
    private function normalizeForSearch(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        $value = urldecode($value);
        // Ensure UTF-8 encoding with best-effort conversion for Cyrillic
        $isUtf8 = mb_detect_encoding($value, 'UTF-8', true);
        if (!$isUtf8 || preg_match('//u', $value) !== 1) {
            $encodings = ['UTF-8', 'Windows-1251', 'ISO-8859-5', 'KOI8-R', 'ISO-8859-1', 'ASCII'];
            $from = mb_detect_encoding($value, $encodings, true) ?: null;
            if ($from && $from !== 'UTF-8') {
                $value = @mb_convert_encoding($value, 'UTF-8', $from);
            }
            // If still invalid UTF-8, try iconv fallbacks
            if (preg_match('//u', $value) !== 1) {
                foreach (['Windows-1251', 'ISO-8859-5', 'KOI8-R'] as $fallback) {
                    $converted = @iconv($fallback, 'UTF-8//IGNORE', $value);
                    if ($converted !== false && preg_match('//u', $converted) === 1) {
                        $value = $converted;
                        break;
                    }
                }
            }
        }
        // Normalize case using multibyte
        $value = mb_strtolower($value, 'UTF-8');
        // Trim and collapse whitespace
        $value = preg_replace('/\s+/u', ' ', trim($value));
        return $value ?? '';
    }
    /**
     * Get all devices from organic database
     */
    public function getAllDevices()
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'screenView', 'deviceAddress', 'valid', 'createDate', 'updateDate')
            ->where('valid', 1)
            ->get();
    }

    /**
     * Get device by ID from organic database
     */
    public function getDeviceById($deviceId)
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'screenView', 'deviceAddress', 'valid', 'createDate', 'updateDate', 'gateUrl')
            ->where('id', $deviceId)
            ->first();
    }

    /**
     * Get devices by IDs (preserving input order) and decorate with user-scoped fields
     */
    public function getDevicesByIdsForUser(User $user, array $deviceIds)
    {
        if (empty($deviceIds)) {
            return collect();
        }

        $rows = DB::connection('mysql_second')
            ->table('goProfiles')
            ->whereIn('id', $deviceIds)
            ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'screenView', 'deviceAddress', 'valid', 'createDate', 'updateDate', 'gateUrl')
            ->get();

        // Index by id for quick lookup
        $byId = $rows->keyBy(function ($r) { return (string) $r->id; });

        // Fetch assignments for access-level and group decoration
        $assignments = $user->getAccessibleDevices()->whereIn('device_id', $deviceIds)->values();
        $assignById = $assignments->keyBy('device_id');

        // Optional custom names (user-specific for non-admin/manager)
        $customNames = [];
        if ($user->isAdmin() || $user->isManager()) {
            $customNameRows = \App\Models\CustomDeviceName::whereIn('device_id', $deviceIds)
                ->orderBy('updated_at', 'desc')
                ->get(['device_id', 'custom_name']);
            foreach ($customNameRows as $row) {
                if (!isset($customNames[$row->device_id])) {
                    $customNames[$row->device_id] = $row->custom_name;
                }
            }
        }

        $result = collect();
        foreach ($deviceIds as $id) {
            $row = $byId->get((string) $id);
            if (!$row) continue;
            $assignment = $assignById->get((string) $id);
            // Decorate similar to getAccessibleDevicesForUser
            $row->assignment = $assignment;
            $row->access_level = $assignment->access_level ?? null;
            $row->group = $assignment->deviceGroup ?? null;
            $row->port_number = $this->extractPortFromAddress($row->deviceAddress ?? '');
            $row->display_name = ($user->isAdmin() || $user->isManager())
                ? ($customNames[(string)$row->id] ?? $row->deviceName)
                : ($user->getCustomDeviceName($row->id) ?? $row->deviceName);
            $row->has_custom_name = ($row->display_name && $row->display_name !== $row->deviceName);
            $result->push($row);
        }

        return $result;
    }

    /**
     * Get devices accessible to a specific user
     */
    public function getAccessibleDevicesForUser(User $user, $filterGroupId = null, $filterUserId = null)
    {
        $assignments = $user->getAccessibleDevices();

        // Apply optional assignment-level filters BEFORE device de-duplication
        if ($filterGroupId) {
            $assignments = $assignments->filter(function ($assignment) use ($filterGroupId) {
                return (string)$assignment->device_group_id === (string)$filterGroupId;
            })->values();
        }
        if ($filterUserId) {
            $assignments = $assignments->filter(function ($assignment) use ($filterUserId) {
                return (string)$assignment->user_id === (string)$filterUserId;
            })->values();
        }

        $devices = collect();

        // If user is admin or manager, prepare a map of global custom names (latest per device)
        $globalCustomNames = [];
        if ($user->isAdmin() || $user->isManager()) {
            $deviceIds = $assignments->pluck('device_id')->map(function ($id) { return (string) $id; });
            if ($deviceIds->isNotEmpty()) {
                $customNameRows = CustomDeviceName::whereIn('device_id', $deviceIds)
                    ->orderBy('updated_at', 'desc')
                    ->get(['device_id', 'custom_name']);
                foreach ($customNameRows as $row) {
                    if (!isset($globalCustomNames[$row->device_id])) {
                        $globalCustomNames[$row->device_id] = $row->custom_name;
                    }
                }
            }
        }

        foreach ($assignments as $assignment) {
            $deviceInfo = $this->getDeviceById($assignment->device_id);
            if ($deviceInfo) {
                $deviceInfo->assignment = $assignment;
                $deviceInfo->access_level = $assignment->access_level;
                $deviceInfo->group = $assignment->deviceGroup;
                
                // Add custom name if exists
                if ($user->isAdmin() || $user->isManager()) {
                    $customName = $globalCustomNames[(string) $deviceInfo->id] ?? null;
                } else {
                    $customName = $user->getCustomDeviceName($deviceInfo->id);
                }
                $deviceInfo->display_name = $customName ?: $deviceInfo->deviceName;
                $deviceInfo->has_custom_name = !empty($customName);
                
                // Extract port number from deviceAddress
                $deviceInfo->port_number = $this->extractPortFromAddress($deviceInfo->deviceAddress ?? '');
                
                $devices->push($deviceInfo);
            }
        }
        
        // For admin/manager views, the same device can be assigned to multiple users/groups.
        // Deduplicate by device ID AFTER optional filters are applied.
        if ($user->isAdmin() || $user->isManager()) {
            $devices = $devices->unique('id')->values();
        }
        
        // Sort devices: custom-named first, then alphabetically by display name, then by id
        $devices = $devices->sort(function ($a, $b) {
            $aHas = !empty($a->has_custom_name);
            $bHas = !empty($b->has_custom_name);
            if ($aHas !== $bHas) {
                return $aHas ? -1 : 1;
            }
            $aName = strtolower($a->display_name ?? $a->deviceName ?? '');
            $bName = strtolower($b->display_name ?? $b->deviceName ?? '');
            if ($aName !== $bName) {
                return $aName <=> $bName;
            }
            return ($a->id <=> $b->id);
        })->values();

        return $devices;
    }

    /**
     * Assign device to user
     */
    public function assignDeviceToUser($deviceId, $userId, $groupId = null, $accessLevel = 'user')
    {
        return DeviceAssignment::create([
            'user_id' => $userId,
            'device_group_id' => $groupId,
            'device_id' => $deviceId,
            'access_level' => $accessLevel,
            'is_active' => true,
        ]);
    }

    /**
     * Remove device assignment
     */
    public function removeDeviceAssignment($deviceId, $userId)
    {
        return DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Create device group
     */
    public function createDeviceGroup($name, $description = null, $gateUrl = null)
    {
        return DeviceGroup::create([
            'name' => $name,
            'description' => $description,
            'gate_url' => $gateUrl,
        ]);
    }

    /**
     * Get all device groups
     */
    public function getAllDeviceGroups()
    {
        return DeviceGroup::with('deviceAssignments')->get();
    }

    /**
     * Get device statistics
     */
    public function getDeviceStatistics(User $user)
    {
        $accessibleDevices = $this->getAccessibleDevicesForUser($user);
        
        return [
            'total_devices' => $accessibleDevices->count(),
            'active_devices' => $accessibleDevices->where('deviceStatus', 'online')->count(),
            'groups_count' => $user->isAdmin() ? DeviceGroup::count() : $user->managedGroups()->count(),
            'users_count' => $user->isAdmin() ? User::count() : 0,
        ];
    }

    /**
     * Search devices by name or platform
     */
    public function searchDevices($query)
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('deviceName', 'like', "%{$query}%")
            ->orWhere('devicePlatform', 'like', "%{$query}%")
            ->orWhere('deviceOs', 'like', "%{$query}%")
            ->where('valid', 1)
            ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'screenView', 'deviceAddress', 'valid', 'createDate', 'updateDate')
            ->get();
    }

    /**
     * Search devices accessible to the given user (Unicode-safe, includes custom names)
     */
    public function searchAccessibleDevices(User $user, string $query)
    {
        $accessible = $this->getAccessibleDevicesForUser($user);
        $needle = $this->normalizeForSearch($query);

        $filtered = $accessible->filter(function ($device) use ($needle) {
            $displayName = $this->normalizeForSearch($device->display_name ?? $device->deviceName ?? '');
            $originalName = $this->normalizeForSearch($device->deviceName ?? '');
            $platform = $this->normalizeForSearch($device->devicePlatform ?? '');
            $os = $this->normalizeForSearch($device->deviceOs ?? '');
            $status = $this->normalizeForSearch($device->deviceStatus ?? '');
            $groupName = $this->normalizeForSearch(optional($device->group)->name ?? '');
            $port = (string)($device->port_number ?? '');

            $matches = false;
            foreach ([$displayName, $originalName, $platform, $os, $status, $groupName] as $haystack) {
                if ($needle === '' || mb_stripos($haystack, $needle, 0, 'UTF-8') !== false) {
                    $matches = true;
                    break;
                }
            }
            if (!$matches && $port !== '') {
                if (stripos($port, $needle) !== false) {
                    $matches = true;
                }
            }
            return $matches;
        })->values();

        return $filtered->map(function ($device) {
            // Return a consistent subset for API responses
            return [
                'id' => $device->id,
                'deviceName' => $device->deviceName,
                'display_name' => $device->display_name ?? $device->deviceName,
                'devicePlatform' => $device->devicePlatform,
                'deviceOs' => $device->deviceOs,
                'deviceStatus' => $device->deviceStatus,
                'screenView' => $device->screenView,
                'deviceAddress' => $device->deviceAddress,
                'valid' => $device->valid ?? 1,
                'createDate' => $device->createDate ?? null,
                'updateDate' => $device->updateDate ?? null,
                'group' => $device->group ?? null,
                'port_number' => $device->port_number ?? null,
            ];
        });
    }

    /**
     * Start device automation
     */
    public function startDevice($deviceId, $userId = null)
    {
        // Check device limit if user is provided
        if ($userId) {
            $assignment = DeviceAssignment::where('device_id', $deviceId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();

            if ($assignment && $assignment->deviceGroup) {
                $group = $assignment->deviceGroup;
                
                // Check if group has reached its limit
                if ($group->hasReachedLimit()) {
                    throw new \Exception('Device limit reached for this group. Cannot start more devices.');
                }
            }
        }

        // Set statusDate to 11:59 PM of current day
        $statusDate = strtotime('today 23:59:59');

        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('id', $deviceId)
            ->update([
                'deviceStatus' => 'starting',
                'sessionStatus' => 'starting',
                'updateDate' => time(),
                'statusDate' => $statusDate,
            ]);
    }

    /**
     * Stop device automation
     */
    public function stopDevice($deviceId)
    {
        // Set statusDate to 11:59 PM of current day
        $statusDate = strtotime('today 23:59:59');

        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('id', $deviceId)
            ->update([
                'sessionStatus' => 'finished',
                'updateDate' => time(),
                'statusDate' => $statusDate,
            ]);
    }

    /**
     * Get device status
     */
    public function getDeviceStatus($deviceId)
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('id', $deviceId)
            ->select('deviceStatus', 'sessionStatus')
            ->first();
    }

    /**
     * Check if device can be started
     */
    public function canStartDevice($deviceId)
    {
        $device = $this->getDeviceById($deviceId);
        return $device && $device->deviceStatus === 'stopped';
    }

    /**
     * Check if device can be stopped
     */
    public function canStopDevice($deviceId)
    {
        $device = $this->getDeviceById($deviceId);
        return $device && $device->deviceStatus === 'online';
    }

    /**
     * Save custom device name
     */
    public function saveCustomDeviceName($deviceId, $userId, $customName)
    {
        return CustomDeviceName::updateOrCreate(
            ['device_id' => $deviceId, 'user_id' => $userId],
            ['custom_name' => $customName]
        );
    }

    /**
     * Delete custom device name for a device, regardless of author
     */
    public function deleteCustomDeviceName($deviceId)
    {
        return CustomDeviceName::where('device_id', $deviceId)->delete();
    }

    /**
     * Update device group limit
     */
    public function updateDeviceGroupLimit($groupId, $newLimit)
    {
        $group = DeviceGroup::find($groupId);
        if (!$group) {
            throw new \Exception('Device group not found.');
        }

        // Check if new limit is less than currently running devices
        $runningCount = $group->getRunningDevicesCount();
        if ($newLimit < $runningCount) {
            throw new \Exception("Cannot set limit below currently running devices ({$runningCount}).");
        }

        $group->device_limit = $newLimit;
        return $group->save();
    }

    /**
     * Get device group limit info
     */
    public function getDeviceGroupLimitInfo($groupId)
    {
        $group = DeviceGroup::find($groupId);
        if (!$group) {
            return null;
        }

        return [
            'id' => $group->id,
            'name' => $group->name,
            'device_limit' => $group->device_limit,
            'running_devices' => $group->getRunningDevicesCount(),
            'remaining_slots' => $group->getRemainingSlots(),
            'has_reached_limit' => $group->hasReachedLimit(),
        ];
    }

    /**
     * Get all available gate URLs from goProfiles
     */
    public function getAvailableGateUrls()
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->whereNotNull('gateUrl')
            ->where('gateUrl', '!=', '')
            ->distinct()
            ->pluck('gateUrl')
            ->filter()
            ->values();
    }

    /**
     * Get devices by gate URL
     */
    public function getDevicesByGateUrl($gateUrl)
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('gateUrl', $gateUrl)
            ->where('valid', 1)
            ->where('createDate', '>', mktime(0, 0, 0, 8, 9, 2025))
            ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'deviceAddress', 'gateUrl')
            ->get();
    }

    /**
     * Request screenshot for device
     */
    public function requestScreenshot($deviceId)
    {
        return DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('id', $deviceId)
            ->update([
                'makeScreenshot' => 1,
                'updateDate' => time(),
            ]);
    }

    /**
     * Check and update status dates for all devices
     */
    public function checkAndUpdateStatusDates()
    {
        $currentTime = time();
        $fiveMinutesAgo = $currentTime - (5 * 60);
        $nextDayStatusDate = strtotime('tomorrow 23:59:59');

        // Get devices that need status date update
        // Do not touch freshly created devices explicitly set to 0
        $devicesToUpdate = DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('statusDate', '>', 0)
            ->where('statusDate', '<', $fiveMinutesAgo)
            ->where('valid', 1)
            ->get();

        $updatedCount = 0;
        foreach ($devicesToUpdate as $device) {
            DB::connection('mysql_second')
                ->table('goProfiles')
                ->where('id', $device->id)
                ->update([
                    'statusDate' => $nextDayStatusDate,
                    'updateDate' => $currentTime,
                ]);
            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * Extract port number from deviceAddress field
     */
    public function extractPortFromAddress($deviceAddress)
    {
        if (empty($deviceAddress)) {
            return null;
        }

        // Extract port number after the colon
        $parts = explode(':', $deviceAddress);
        if (count($parts) >= 2) {
            return trim($parts[1]);
        }

        return null;
    }
}
