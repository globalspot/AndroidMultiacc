<?php

namespace App\Http\Controllers;

use App\Models\DeviceAssignment;
use App\Models\DeviceGroup;
use App\Models\User;
use App\Services\DeviceService;
use App\Models\HardwareProfile;
use App\Models\OsImage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Display device dashboard
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        
        // Get selected filters
        $selectedGroupId = $request->query('group_id');
        $selectedUserId = $request->query('user_id');
        $orderToken = $request->query('token');

        // Enforce default group when none is provided (no "All groups" view)
        if (!$selectedGroupId) {
            $defaultGroup = null;
            if ($user->isAdmin()) {
                $defaultGroup = DeviceGroup::orderBy('created_at', 'desc')->first();
            } else {
                // First group the user belongs to via assignments
                $firstAssignment = $user->allGroups()->with('deviceGroup')->first();
                $defaultGroup = $firstAssignment?->deviceGroup;
            }

            if ($defaultGroup) {
                // Redirect to same route with default group applied; drop user_id to avoid mismatches
                $params = $request->query();
                $params['group_id'] = $defaultGroup->id;
                unset($params['user_id']);
                return redirect()->route('devices.index', $params);
            }
        }
        
        // Get devices based on group filter
        // Get accessible devices with optional assignment-level filters
        $devices = $this->deviceService->getAccessibleDevicesForUser($user, $selectedGroupId, $selectedUserId);

        // Apply consistent sorting: custom-named devices first, then alphabetically by display name, then by ID
        // This ensures that devices with custom names (display_name !== deviceName) appear at the top
        $devices = $devices->sort(function ($a, $b) {
            // Check if device has custom name (display_name different from deviceName)
            $aHas = !empty($a->display_name) && $a->display_name !== $a->deviceName;
            $bHas = !empty($b->display_name) && $b->display_name !== $b->deviceName;
            
            if ($aHas !== $bHas) {
                return $aHas ? -1 : 1; // Custom names first
            }
            
            // Then sort alphabetically by display name
            $aName = strtolower($a->display_name ?? $a->deviceName ?? '');
            $bName = strtolower($b->display_name ?? $b->deviceName ?? '');
            if ($aName !== $bName) {
                return $aName <=> $bName;
            }
            
            // Finally by device ID
            return ($a->id <=> $b->id);
        })->values();

        // Optional filter: show only online devices via query params
        // Supports: ?online=1 or ?status=online or ?only_online=1
        $showOnlyOnline = $request->boolean('online')
            || $request->query('status') === 'online'
            || (string) $request->query('only_online') === '1';

        // Keep server always returning full set; client-side filters will narrow
        $statistics = $this->deviceService->getDeviceStatistics($user);
        
        // Get available devices for assignment (admin only)
        $availableDevices = collect();
        if ($user->isAdmin()) {
            $availableDevices = $this->deviceService->getAllDevices();
        }
        
        // Get groups for assignment
        $groups = collect();
        $gateUrls = collect();
        if ($user->isAdmin()) {
            $groups = $this->deviceService->getAllDeviceGroups();
            $gateUrls = $this->deviceService->getAvailableGateUrls();
        } elseif ($user->isManager()) {
            $groups = $user->managedGroups()->with('deviceGroup')->get()->pluck('deviceGroup');
        }
        
        // Get user's accessible groups for filtering (admins see all)
        if ($user->isAdmin()) {
            $userGroups = DeviceGroup::orderBy('name')->get();
        } else {
            $userGroups = $user->allGroups()->get()->pluck('deviceGroup')->filter();
        }
        
        // Cache the ordered device IDs for chunk loading
        $devicesOrderToken = (string) Str::uuid();
        session(["devices_order.$devicesOrderToken" => $devices->pluck('id')->values()->all()]);

        // Data for Create Device modal
        $hardwareProfiles = HardwareProfile::orderBy('title')->get(['id','title','dimension']);
        $osImages = OsImage::orderBy('skdVersion')->get(['id','android','version','skdVersion','arch']);
        // Gate URLs available to current user for creation
        $createDeviceGateUrls = collect();
        if ($user->isAdmin()) {
            $createDeviceGateUrls = $this->deviceService->getAvailableGateUrls();
        } elseif ($user->isManager()) {
            $groupIds = $user->managerGroups()->pluck('device_group_id');
            $createDeviceGateUrls = \App\Models\DeviceGroup::whereIn('id', $groupIds)
                ->whereNotNull('gate_url')
                ->pluck('gate_url')
                ->filter()
                ->unique()
                ->values();
        }

        return view('devices.index', [
            'devices' => $devices,
            'statistics' => $statistics,
            'availableDevices' => $availableDevices,
            'groups' => $groups,
            'gateUrls' => $gateUrls,
            'user' => $user,
            'onlineFilter' => $showOnlyOnline,
            'userGroups' => $userGroups,
            'selectedGroupId' => $selectedGroupId,
            // For completeness; view can still use request('user_id') directly
            'selectedUserId' => $selectedUserId,
            'devicesOrderToken' => $devicesOrderToken,
            'hardwareProfiles' => $hardwareProfiles,
            'osImages' => $osImages,
            'createDeviceGateUrls' => $createDeviceGateUrls,
        ]);
    }

    /**
     * Show device details
     */
    public function show(Request $request, $deviceId): View
    {
        $user = $request->user();
        $device = $this->deviceService->getDeviceById($deviceId);
        
        if (!$device) {
            abort(404);
        }
        
        // Check if user has access to this device
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
            
        if (!$assignment && !$user->isAdmin()) {
            abort(403);
        }
        
        return view('devices.show', [
            'device' => $device,
            'assignment' => $assignment,
            'user' => $user,
        ]);
    }

    /**
     * Assign device to user (admin only)
     */
    public function assign(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'group_id' => 'nullable|exists:device_groups,id',
        ]);

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        try {
            $this->deviceService->assignDeviceToUser(
                $request->device_id,
                $request->user_id,
                $request->group_id,
                'user' // Default access level
            );

            return redirect()->back()->with('success', __('app.device_assigned_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('app.device_assignment_failed'));
        }
    }

    /**
     * Remove device assignment (admin only)
     */
    public function unassign(Request $request, $deviceId, $userId)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        try {
            $this->deviceService->removeDeviceAssignment($deviceId, $userId);
            return redirect()->back()->with('success', __('app.device_unassigned_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('app.device_unassignment_failed'));
        }
    }

    /**
     * Create device group (admin only)
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'gate_url' => 'nullable|string',
        ]);

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        try {
            $this->deviceService->createDeviceGroup($request->name, $request->description, $request->gate_url);
            return redirect()->back()->with('success', __('app.group_created_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('app.group_creation_failed'));
        }
    }

    /**
     * Search devices
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $user = $request->user();
        $q = $request->input('query', '');
        // Use Unicode-safe, access-scoped search including custom names
        $devices = $this->deviceService->searchAccessibleDevices($user, $q);
        
        return response()->json($devices);
    }

    /**
     * Start device automation
     */
    public function startDevice(Request $request, $deviceId)
    {
        $user = $request->user();
        
        // Check if user has access to this device
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
            
        if (!$assignment && !$user->isAdmin()) {
            abort(403, 'Access denied');
        }

        // Check if device can be started
        if (!$this->deviceService->canStartDevice($deviceId)) {
            return response()->json([
                'success' => false,
                'message' => __('app.device_cannot_be_started')
            ], 400);
        }

        try {
            $this->deviceService->startDevice($deviceId, $user->id);
            
            return response()->json([
                'success' => true,
                'message' => __('app.device_started_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.start_failed')
            ], 500);
        }
    }

    /**
     * Stop device automation
     */
    public function stopDevice(Request $request, $deviceId)
    {
        $user = $request->user();
        
        // Check if user has access to this device
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
            
        if (!$assignment && !$user->isAdmin()) {
            abort(403, 'Access denied');
        }

        // Check if device can be stopped
        if (!$this->deviceService->canStopDevice($deviceId)) {
            return response()->json([
                'success' => false,
                'message' => __('app.device_cannot_be_stopped')
            ], 400);
        }

        try {
            $this->deviceService->stopDevice($deviceId);
            
            return response()->json([
                'success' => true,
                'message' => __('app.device_stopped_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.device_stop_failed')
            ], 500);
        }
    }

    /**
     * Get device status
     */
    public function getDeviceStatus(Request $request, $deviceId)
    {
        $user = $request->user();
        
        // Check if user has access to this device
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
            
        if (!$assignment && !$user->isAdmin()) {
            abort(403, 'Access denied');
        }

        $status = $this->deviceService->getDeviceStatus($deviceId);
        
        return response()->json($status);
    }

    /**
     * Refresh all devices data for background updates
     */
    public function refreshAllDevices(Request $request)
    {
        try {
            // Check and update status dates first
            $updatedStatusDates = $this->deviceService->checkAndUpdateStatusDates();
            
            $user = $request->user();
            $devices = $this->deviceService->getAccessibleDevicesForUser($user);
            
            $deviceData = [];
            foreach ($devices as $device) {
                $deviceData[] = [
                    'id' => $device->id,
                    'deviceStatus' => $device->deviceStatus,
                    'screenView' => $device->screenView,
                    'screenViewHash' => md5($device->screenView ?? ''),
                    'canStart' => $this->deviceService->canStartDevice($device->id),
                    'canStop' => $this->deviceService->canStopDevice($device->id),
                    'access_level' => $device->access_level ?? null,
                    'group' => $device->group ?? null,
                    'user_id' => $user->id,
                    'port_number' => $device->port_number ?? null
                ];
            }

            // Get updated statistics
            $statistics = $this->deviceService->getDeviceStatistics($user);
            
            return response()->json([
                'success' => true,
                'devices' => $deviceData,
                'statistics' => $statistics,
                'statusDatesUpdated' => $updatedStatusDates,
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => __('app.refresh_devices_failed')], 500);
        }
    }

    /**
     * Refresh only screenshots for devices
     */
    public function refreshScreenshots(Request $request)
    {
        try {
            $user = $request->user();
            $devices = $this->deviceService->getAccessibleDevicesForUser($user);
            
            $screenshotData = [];
            foreach ($devices as $device) {
                if ($device->deviceStatus === 'online' && !empty($device->screenView)) {
                    $screenshotData[] = [
                        'id' => $device->id,
                        'screenView' => $device->screenView,
                        'screenViewHash' => md5($device->screenView ?? ''),
                        'deviceStatus' => $device->deviceStatus
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'screenshots' => $screenshotData,
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => __('app.refresh_screenshots_failed')], 500);
        }
    }

    /**
     * Update custom device name
     */
    public function updateCustomName(Request $request, $deviceId)
    {
        $request->validate([
            'custom_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        
        // Check if user has access to this device
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
            
        if (!$assignment && !$user->isAdmin()) {
            abort(403, 'Access denied');
        }

        try {
            $this->deviceService->saveCustomDeviceName($deviceId, $user->id, $request->custom_name);
            
            return response()->json([
                'success' => true,
                'message' => __('app.custom_name_saved_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.custom_name_save_failed')
            ], 500);
        }
    }

    /**
     * Delete custom device name
     */
    public function deleteCustomName(Request $request, $deviceId)
    {
        $user = $request->user();

        try {
            // Allow deletion by any authorized user (admin/assigned user/manager)
            $this->deviceService->deleteCustomDeviceName($deviceId);
            
            return response()->json([
                'success' => true,
                'message' => __('app.custom_name_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.custom_name_delete_failed')
            ], 500);
        }
    }

    /**
     * Get device group limit info (admin only)
     */
    public function getGroupLimit(Request $request, $groupId)
    {
        try {
            $limitInfo = $this->deviceService->getDeviceGroupLimitInfo($groupId);
            
            if (!$limitInfo) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.group_not_found')
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $limitInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update device group limit (admin only)
     */
    public function updateGroupLimit(Request $request, $groupId)
    {
        $request->validate([
            'device_limit' => 'required|integer|min:1|max:100',
        ]);

        try {
            $this->deviceService->updateDeviceGroupLimit($groupId, $request->device_limit);
            
            return response()->json([
                'success' => true,
                'message' => __('app.group_limit_updated_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get devices by gate URL (admin only)
     */
    public function getDevicesByGateUrl(Request $request, $gateUrl)
    {
        try {
            $devices = $this->deviceService->getDevicesByGateUrl($gateUrl);
            
            return response()->json([
                'success' => true,
                'devices' => $devices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load devices chunk for infinite scrolling
     */
    public function chunk(Request $request)
    {
        $user = $request->user();
        $offset = max(0, (int) $request->query('offset', 0));
        $limit = max(1, min(100, (int) $request->query('limit', 20)));
        $view = $request->query('view', 'cards'); // 'cards' or 'table'

        $selectedGroupId = $request->query('group_id');
        $selectedUserId = $request->query('user_id');
        $orderToken = $request->query('token');

        // SQL debug removed

        // If we have an order token, use session-cached ordering for precise ID slicing
        $chunk = collect();
        $total = 0;
        if ($orderToken) {
            $orderedIds = (array) session("devices_order.$orderToken", []);
            $total = count($orderedIds);

            // Read filters
            $search = mb_strtolower(trim((string) $request->query('search', '')));
            $onlyOnline = (string) $request->query('only_online', '') === '1';
            $isFiltered = ($search !== '' || $onlyOnline);

            if (!$isFiltered) {
                // Fast path: no filters → simple slice, contiguous paging
                $sliceIds = array_slice($orderedIds, $offset, $limit);
                if (!empty($sliceIds)) {
                    $chunk = $this->deviceService->getDevicesByIdsForUser($user, $sliceIds);
                }
                $hasMore = ($offset + $limit) < $total;
                $nextOffset = $offset + $limit;
            } else {
                // Filtered path: scan windows forward to collect matching items
                $windowSize = max($limit * 10, 200); // evaluate up to 200 IDs per request
                $scanIndex = $offset;
                $matches = collect();

                while ($scanIndex < $total && $matches->count() < $limit) {
                    $end = min($scanIndex + $windowSize, $total);
                    $windowIds = array_slice($orderedIds, $scanIndex, $end - $scanIndex);
                    if (empty($windowIds)) break;

                    $devicesWindow = $this->deviceService->getDevicesByIdsForUser($user, $windowIds);

                    // Filter by online status and search
                    $filtered = $devicesWindow->filter(function ($d) use ($onlyOnline, $search) {
                        if ($onlyOnline && (($d->deviceStatus ?? '') !== 'online')) return false;
                        if ($search === '') return true;
                        $haystacks = [
                            mb_strtolower((string)($d->display_name ?? '')),
                            mb_strtolower((string)($d->deviceName ?? '')),
                            mb_strtolower((string)($d->devicePlatform ?? '')),
                            mb_strtolower((string)($d->deviceOs ?? '')),
                            mb_strtolower((string)($d->deviceStatus ?? '')),
                            mb_strtolower((string)optional($d->group)->name ?? ''),
                            (string)($d->port_number ?? ''),
                        ];
                        foreach ($haystacks as $h) {
                            if ($h !== '' && str_contains($h, $search)) return true;
                        }
                        return false;
                    })->values();

                    $needed = $limit - $matches->count();
                    if ($filtered->isNotEmpty()) {
                        $matches = $matches->merge($filtered->slice(0, $needed));
                    }

                    $scanIndex = $end;
                }

                $chunk = $matches->values();
                $hasMore = $scanIndex < $total;
                $nextOffset = $scanIndex; // advance to where we scanned up to
            }
        } else {
            // Fallback: full list slice
            $devices = $this->deviceService->getAccessibleDevicesForUser($user, $selectedGroupId, $selectedUserId);
            $total = $devices->count();
            $chunk = $devices->slice($offset, $limit)->values();
            $hasMore = ($offset + $limit) < $total;
            $nextOffset = $offset + $limit;
        }
        if (!isset($hasMore)) {
            $hasMore = ($offset + $limit) < $total;
        }

        if ($view === 'table') {
            $html = view('devices.partials.table-rows', [
                'devices' => $chunk,
                'user' => $user,
            ])->render();
        } else {
            $html = view('devices.partials.card-list', [
                'devices' => $chunk,
                'user' => $user,
            ])->render();
        }

        $response = [
            'success' => true,
            'html' => $html,
            'hasMore' => $hasMore,
            'nextOffset' => $nextOffset ?? ($offset + $limit),
            'total' => $total,
        ];

        // SQL debug removed

        return response()->json($response);
    }

    /**
     * Request screenshot for device
     */
    public function requestScreenshot(Request $request, $deviceId)
    {
        $user = $request->user();
        
        // Check if user has access to this device
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
            
        if (!$assignment && !$user->isAdmin()) {
            abort(403, 'Access denied');
        }

        try {
            $this->deviceService->requestScreenshot($deviceId);
            
            return response()->json([
                'success' => true,
                'message' => __('app.screenshot_requested_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.screenshot_request_failed')
            ], 500);
        }
    }

    /**
     * Create a new device record in goProfiles (mysql_second)
     */
    public function createDevice(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'hardware_profile_id' => 'nullable|string|max:64',
            'os_image_id' => 'nullable|string|max:64',
            'proxy' => 'required|string|max:255',
            'proxy_login' => 'nullable|string|max:255',
            'proxy_pass' => 'nullable|string|max:255',
            'gate_url' => 'required|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $now = time();
            // Hardware profile title => devicePlatform
            $hwTitle = null;
            if ($request->filled('hardware_profile_id')) {
                $hw = \App\Models\HardwareProfile::find($request->input('hardware_profile_id'));
                $hwTitle = $hw?->title;
            }
            // If hardware profile not selected, leave platform empty per requirement
            $platform = $hwTitle ? substr($hwTitle, 0, 250) : '';

            // Default device name: hw title + '.' + 10 random alnum; if no HW selected, leave blank
            $name = trim((string) $request->input('name', ''));
            if ($name === '') {
                if ($hwTitle) {
                    $name = $hwTitle . '.' . \Illuminate\Support\Str::random(10);
                } else {
                    $name = '';
                }
            }

            // OS version string
            // If OS image not selected, deviceOs should be empty
            $deviceOsValue = '';
            if ($request->filled('os_image_id')) {
                $os = \App\Models\OsImage::find($request->input('os_image_id'));
                if ($os) {
                    $deviceOsValue = (string) $os->version;
                }
            }

            // Proxy
            $proxyHostPort = substr(trim($request->input('proxy')), 0, 50);
            $proxyLogin = $request->input('proxy_login') ? substr($request->input('proxy_login'), 0, 100) : null;
            $proxyPass = $request->input('proxy_pass') ? substr($request->input('proxy_pass'), 0, 100) : null;

            $data = [
                'createDate' => $now,
                'deviceName' => substr($name, 0, 100),
                'devicePlatform' => $platform,
                'deviceOs' => substr($deviceOsValue, 0, 250),
                'proxy' => $proxyHostPort,
                'proxyLogin' => $proxyLogin,
                'proxyPass' => $proxyPass,
                // Leave newProxy, deviceAddress, deviceStatus, sessionStatus, updateDate to table defaults
                'gateUrl' => substr((string) $request->input('gate_url'), 0, 50),
                // Explicitly set statusDate default to 0
                'statusDate' => 0,
            ];

            if ($request->filled('latitude')) {
                $data['lat'] = substr((string) $request->input('latitude'), 0, 150);
            }
            if ($request->filled('longitude')) {
                $data['lon'] = substr((string) $request->input('longitude'), 0, 150);
            }

            // Insert to mysql_second.goProfiles
            $id = \DB::connection('mysql_second')->table('goProfiles')->insertGetId($data);

            // Auto-assign the new device to the current user in Laravel DB
            $user = $request->user();
            $groupId = null;
            if ($request->filled('gate_url')) {
                $groupId = \App\Models\DeviceGroup::where('gate_url', $request->input('gate_url'))
                    ->value('id');
            }
            // Access level 'owner' for creator
            try {
                $this->deviceService->assignDeviceToUser((string) $id, $user->id, $groupId, 'owner');
            } catch (\Throwable $e) {
                // Swallow assignment errors but report in response
                // so device creation still succeeds
                \Log::warning('Auto-assign failed for new device', [
                    'device_id' => $id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'id' => $id,
                'message' => __('app.create')
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch create devices
     */
    public function batchCreate(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:500',
            'hardware_profile_ids' => 'nullable|array',
            'hardware_profile_ids.*' => 'string|max:64',
            'os_image_ids' => 'nullable|array',
            'os_image_ids.*' => 'string|max:64',
            'proxy_list' => 'nullable|string',
            'proxy_login' => 'nullable|string|max:255',
            'proxy_pass' => 'nullable|string|max:255',
            'gate_url' => 'required|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'max_deviation' => 'nullable|numeric|min:0',
        ]);

        try {
            $now = time();
            $user = $request->user();

            // Resolve group ID by gate URL for auto-assignment
            $groupId = null;
            if ($request->filled('gate_url')) {
                $groupId = \App\Models\DeviceGroup::where('gate_url', $request->input('gate_url'))->value('id');
            }

            // Prepare selections
            $hwIds = array_values(array_filter((array) $request->input('hardware_profile_ids', [])));
            $osIds = array_values(array_filter((array) $request->input('os_image_ids', [])));

            // Parse proxy list: supports lines with host:port, and port ranges 1000-2000 or comma lists
            $rawProxyLines = preg_split('/\r?\n/', (string) $request->input('proxy_list', '')) ?: [];
            $proxies = [];
            foreach ($rawProxyLines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                // IP range + port or port-range: 192.168.0.1-192.168.0.10:1000-1002
                if (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3})-(\d{1,3}(?:\.\d{1,3}){3}):(\d+)(?:-(\d+))?$/', $line, $m)) {
                    $startIp = $m[1];
                    $endIp = $m[2];
                    $portStart = (int) $m[3];
                    $portEnd = isset($m[4]) ? (int) $m[4] : $portStart;
                    $startOctets = explode('.', $startIp);
                    $endOctets = explode('.', $endIp);
                    // Support ranges within the same /24 for safety
                    if ($startOctets[0] === $endOctets[0] && $startOctets[1] === $endOctets[1] && $startOctets[2] === $endOctets[2]) {
                        $hostPrefix = $startOctets[0] . '.' . $startOctets[1] . '.' . $startOctets[2] . '.';
                        $a = (int) $startOctets[3];
                        $b = (int) $endOctets[3];
                        if ($b >= $a) {
                            for ($oct = $a; $oct <= $b; $oct++) {
                                for ($pp = $portStart; $pp <= $portEnd; $pp++) {
                                    $proxies[] = $hostPrefix . $oct . ':' . $pp;
                                }
                            }
                            continue;
                        }
                    }
                    // Fallback: if not same /24 or invalid, treat as is
                    $proxies[] = $startIp . ':' . $portStart;
                    continue;
                }
                // Handle patterns like host:1000-1002 or host:1000,1001
                if (preg_match('/^([^:\s]+):(\d+)-(\d+)$/', $line, $m)) {
                    $host = $m[1];
                    $start = (int) $m[2];
                    $end = (int) $m[3];
                    if ($end >= $start) {
                        for ($p = $start; $p <= $end; $p++) {
                            $proxies[] = $host . ':' . $p;
                        }
                    }
                } elseif (preg_match('/^([^:\s]+):(\d+(?:,\d+)*)$/', $line, $m)) {
                    $host = $m[1];
                    $ports = explode(',', $m[2]);
                    foreach ($ports as $pp) {
                        $pp = trim($pp);
                        if ($pp !== '' && ctype_digit($pp)) {
                            $proxies[] = $host . ':' . (int) $pp;
                        }
                    }
                } else {
                    // Plain host:port
                    $proxies[] = $line;
                }
            }

            $count = (int) $request->input('count');
            $created = [];

            for ($i = 0; $i < $count; $i++) {
                // Pick hardware profile
                $hwId = null;
                $platform = '';
                if (!empty($hwIds)) {
                    $hwId = $hwIds[$i % count($hwIds)];
                    $hw = \App\Models\HardwareProfile::find($hwId);
                    $platform = $hw ? substr((string) $hw->title, 0, 250) : '';
                }

                // Pick OS image
                $deviceOsValue = '';
                if (!empty($osIds)) {
                    $osId = $osIds[$i % count($osIds)];
                    $os = \App\Models\OsImage::find($osId);
                    if ($os) {
                        $deviceOsValue = (string) $os->version;
                    }
                }

                // Pick proxy if provided
                $proxyHostPort = '';
                if (!empty($proxies)) {
                    $proxyHostPort = substr((string) $proxies[$i % count($proxies)], 0, 50);
                }

                // Derive name based on HW if available
                $name = '';
                if ($platform !== '') {
                    $name = substr($platform . '.' . \Illuminate\Support\Str::random(10), 0, 100);
                }

                // Coordinates with deviation
                $lat = $request->input('latitude');
                $lon = $request->input('longitude');
                $maxDev = (float) ($request->input('max_deviation') ?? 0);
                if ($lat !== null && $lon !== null && $maxDev > 0) {
                    // Simple jitter within +/- maxDev for both lat and lon
                    $jitterLat = ((mt_rand() / mt_getrandmax()) * 2 - 1) * $maxDev;
                    $jitterLon = ((mt_rand() / mt_getrandmax()) * 2 - 1) * $maxDev;
                    $lat = (float) $lat + $jitterLat;
                    $lon = (float) $lon + $jitterLon;
                }

                $data = [
                    'createDate' => $now,
                    'deviceName' => $name,
                    'devicePlatform' => $platform,
                    'deviceOs' => substr($deviceOsValue, 0, 250),
                    'proxy' => $proxyHostPort,
                    'proxyLogin' => $request->input('proxy_login') ? substr((string) $request->input('proxy_login'), 0, 100) : null,
                    'proxyPass' => $request->input('proxy_pass') ? substr((string) $request->input('proxy_pass'), 0, 100) : null,
                    'gateUrl' => substr((string) $request->input('gate_url'), 0, 50),
                    'statusDate' => 0,
                ];
                if ($lat !== null) {
                    $data['lat'] = substr((string) $lat, 0, 150);
                }
                if ($lon !== null) {
                    $data['lon'] = substr((string) $lon, 0, 150);
                }

                $id = \DB::connection('mysql_second')->table('goProfiles')->insertGetId($data);

                // Auto-assign to creator as owner
                try {
                    $this->deviceService->assignDeviceToUser((string) $id, $user->id, $groupId, 'owner');
                } catch (\Throwable $e) {
                    \Log::warning('Auto-assign failed for new batch device', [
                        'device_id' => $id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $created[] = $id;
            }

            return response()->json([
                'success' => true,
                'created_ids' => $created,
                'count' => count($created),
                'message' => __('app.devices_created_successfully'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel device assignment (admin and manager only)
     */
    public function cancelAssignment(Request $request, $deviceId)
    {
        $user = $request->user();
        
        // Check if user has permission to cancel assignments
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Access denied');
        }

        // Find the device assignment
        $assignment = DeviceAssignment::where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => __('app.assignment_not_found')
            ], 404);
        }

        // For managers, check if they can manage this device's group
        if ($user->isManager() && !$user->isAdmin()) {
            if (!$assignment->device_group_id || !$user->isManagerOfGroup($assignment->device_group_id)) {
                abort(403, __('app.cannot_cancel_assignment_not_your_group'));
            }
        }

        try {
            // Deactivate the assignment instead of deleting it
            $assignment->update(['is_active' => false]);
            
            return response()->json([
                'success' => true,
                'message' => __('app.assignment_cancelled_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('app.assignment_cancel_failed')
            ], 500);
        }
    }
}
