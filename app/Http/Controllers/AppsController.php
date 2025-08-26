<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ApkEntry;
use App\Models\AppInstallTask;
use App\Models\CustomDeviceName;
use App\Services\DeviceService;

class AppsController extends Controller
{
    public function __construct(private DeviceService $deviceService)
    {
    }

    public function index(Request $request)
    {
        // Load enabled APK entries grouped by app name
        $entries = ApkEntry::all()->groupBy('app_name');

        return view('apps.index', [
            'groups' => $entries,
        ]);
    }

    public function devices(Request $request)
    {
        $user = $request->user();
        $devices = $this->deviceService->getAccessibleDevicesForUser($user);

        // Ensure latest custom name per device is applied (global latest)
        $deviceIds = collect($devices)->pluck('id')->filter()->unique()->values();
        if ($deviceIds->isNotEmpty()) {
            $latestNames = CustomDeviceName::whereIn('device_id', $deviceIds)
                ->orderBy('updated_at', 'desc')
                ->get(['device_id', 'custom_name'])
                ->unique('device_id')
                ->keyBy('device_id');

            $devices = collect($devices)->map(function ($d) use ($latestNames) {
                $id = is_array($d) ? ($d['id'] ?? null) : ($d->id ?? null);
                $custom = $id !== null && isset($latestNames[$id]) ? $latestNames[$id]->custom_name : null;
                if (is_array($d)) {
                    $d['custom_name'] = $custom ?? ($d['custom_name'] ?? null);
                    return $d;
                }
                $d->custom_name = $custom ?? ($d->custom_name ?? null);
                return $d;
            })->values();
        }

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ]);
    }

    public function createTasks(Request $request)
    {
        $data = $request->validate([
            'device_ids' => ['required', 'array'],
            'device_ids.*' => ['integer'],
            'app_title' => ['required', 'string'],
            'app_filename' => ['required', 'string'],
            'app_url' => ['required', 'url'],
            'lib_url' => ['nullable', 'url'],
            'install_order' => ['nullable', 'in:before,after'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $now = now();
        // Normalize permissions to JSON string for query builder insert
        $permsArray = $data['permissions'] ?? [];
        if (!is_array($permsArray)) {
            $permsArray = [];
        }
        $permsArray = array_values(array_unique(array_filter($permsArray, function ($p) { return is_string($p) && $p !== ''; })));
        $permissionsJson = !empty($permsArray) ? json_encode($permsArray) : null;
        $tasks = [];
        foreach ($data['device_ids'] as $deviceId) {
            $tasks[] = [
                'device_id' => $deviceId,
                'app_title' => $data['app_title'],
                'app_filename' => $data['app_filename'],
                'app_url' => $data['app_url'],
                'lib_url' => $data['lib_url'] ?? null,
                'install_order' => $data['install_order'] ?? null,
                'add_date' => $now,
                'update_date' => null,
                'comlete_date' => null,
                'install_status' => 'queued',
                'permissions' => $permissionsJson,
            ];
        }

        DB::connection('mysql_second')->table('app_install_tasks')->insert($tasks);

        return response()->json(['success' => true, 'count' => count($tasks)]);
    }
}


