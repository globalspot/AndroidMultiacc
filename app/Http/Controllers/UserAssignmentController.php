<?php

namespace App\Http\Controllers;

use App\Models\DeviceAssignment;
use App\Models\DeviceGroup;
use App\Models\User;
use App\Models\UserGroupAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserAssignmentController extends Controller
{
    /**
     * Show user assignment interface (admin only)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        $users = User::where('role', '!=', 'admin')->get();
        $groups = DeviceGroup::all();
        $assignments = UserGroupAssignment::with(['user', 'deviceGroup'])->get();

        return view('user-assignments.index', [
            'users' => $users,
            'groups' => $groups,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Assign user to group (admin only)
     */
    public function assignUserToGroup(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:device_groups,id',
            'role' => 'required|in:member,manager',
        ]);

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        // Check if assignment already exists
        $existingAssignment = UserGroupAssignment::where('user_id', $request->user_id)
            ->where('device_group_id', $request->group_id)
            ->first();

        if ($existingAssignment) {
            // Update existing assignment
            $existingAssignment->update([
                'role' => $request->role,
                'is_active' => true,
            ]);
        } else {
            // Create new assignment
            UserGroupAssignment::create([
                'user_id' => $request->user_id,
                'device_group_id' => $request->group_id,
                'role' => $request->role,
                'is_active' => true,
            ]);
        }

        // Update user's main role in users table if assigned as manager
        $targetUser = User::find($request->user_id);
        if ($request->role === 'manager' && $targetUser->role !== 'admin') {
            $targetUser->update(['role' => 'manager']);
        }

        return response()->json([
            'success' => true,
            'message' => 'User assigned to group successfully'
        ]);
    }

    /**
     * Remove user from group (admin only)
     */
    public function removeUserFromGroup(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:device_groups,id',
        ]);

        $user = $request->user();
        
        if (!$user->isAdmin()) {
            abort(403);
        }

        UserGroupAssignment::where('user_id', $request->user_id)
            ->where('device_group_id', $request->group_id)
            ->update(['is_active' => false]);

        // Check if user has any remaining manager assignments
        $targetUser = User::find($request->user_id);
        $remainingManagerAssignments = UserGroupAssignment::where('user_id', $request->user_id)
            ->where('role', 'manager')
            ->where('is_active', true)
            ->count();

        // If no manager assignments left and user is not admin, demote to user role
        if ($remainingManagerAssignments === 0 && $targetUser->role !== 'admin') {
            $targetUser->update(['role' => 'user']);
        }

        return response()->json([
            'success' => true,
            'message' => 'User removed from group successfully'
        ]);
    }

    /**
     * Show device assignment interface for managers
     */
    public function deviceAssignmentInterface(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        $manageableUsers = $user->getManageableUsers();
        $manageableDevices = $user->getManageableDevices();
        $managerGroups = $user->managerGroups()->get();

        // Preload latest active invites for these groups
        $groupIds = $managerGroups->pluck('device_group_id')->filter()->values();
        $existingInvites = \App\Models\GroupInvite::whereIn('device_group_id', $groupIds)
            ->where('is_active', true)
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })
            ->where(function ($q) { $q->whereNull('max_uses')->orWhereColumn('uses', '<', 'max_uses'); })
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('device_group_id')
            ->map(function ($list) { return $list->first(); });

        return view('user-assignments.device-assignment', [
            'manageableUsers' => $manageableUsers,
            'manageableDevices' => $manageableDevices,
            'managerGroups' => $managerGroups,
            'user' => $user,
            'existingInvites' => $existingInvites,
        ]);
    }

    /**
     * Assign device to user (manager only)
     */
    public function assignDeviceToUser(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:device_groups,id',
        ]);

        $user = $request->user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        // Check if manager can manage this group
        if (!$user->isManagerOfGroup($request->group_id)) {
            abort(403, 'You can only assign devices in groups you manage');
        }

        // Check if target user belongs to this group (member or manager) OR if assigning to self
        $targetUser = User::find($request->user_id);
        $isSelfAssignment = $user->id === $targetUser->id;
        
        if (
            !$isSelfAssignment &&
            !($targetUser->isMemberOfGroup($request->group_id) || $targetUser->isManagerOfGroup($request->group_id))
        ) {
            abort(403, 'User must belong to the group (member or manager), or you can assign devices to yourself');
        }

        // Allow managers to assign any device from the group's gate URL

        // Check if device is from gate URL assigned to this group
        $group = DeviceGroup::find($request->group_id);
        if ($group->gate_url) {
            $deviceFromGate = \DB::connection('mysql_second')
                ->table('goProfiles')
                ->where('id', $request->device_id)
                ->where('gateUrl', $group->gate_url)
                ->first();

            if (!$deviceFromGate) {
                abort(403, 'Device must be from gate URL assigned to this group');
            }
        }

        // Create or update device assignment
        $existingAssignment = DeviceAssignment::where('user_id', $request->user_id)
            ->where('device_id', $request->device_id)
            ->first();

        if ($existingAssignment) {
            $existingAssignment->update([
                'device_group_id' => $request->group_id,
                'is_active' => true,
            ]);
        } else {
            DeviceAssignment::create([
                'user_id' => $request->user_id,
                'device_id' => $request->device_id,
                'device_group_id' => $request->group_id,
                'access_level' => 'user',
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device assigned to user successfully'
        ]);
    }

    /**
     * Remove device assignment (manager only)
     */
    public function removeDeviceAssignment(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        // Check if manager can manage this assignment
        $assignment = DeviceAssignment::where('device_id', $request->device_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        if (!$user->isManagerOfGroup($assignment->device_group_id)) {
            abort(403, 'You can only remove device assignments in groups you manage');
        }

        $assignment->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Device assignment removed successfully'
        ]);
    }

    /**
     * Get users in group for manager
     */
    public function getGroupUsers(Request $request, $groupId): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        // Check if manager can manage this group
        if (!$user->isManagerOfGroup($groupId)) {
            abort(403, 'You can only view users in groups you manage');
        }

        $group = DeviceGroup::find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found'], 404);
        }

        // Get both members and managers in the group
        $users = $group->assignedUsers()->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Get devices from gate URL for manager
     */
    public function getGateUrlDevices(Request $request, $groupId): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        // Check if manager can manage this group
        if (!$user->isManagerOfGroup($groupId)) {
            abort(403, 'You can only view devices in groups you manage');
        }

        $group = DeviceGroup::find($groupId);
        
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found'], 404);
        }
        
        if (!$group->gate_url) {
            return response()->json([
                'success' => false,
                'message' => 'No gate URL assigned to this group'
            ]);
        }

        // Get devices from organic database for the group's gate URL (no per-manager restriction)
        $devices = \DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('gateUrl', $group->gate_url)
            ->where('valid', 1)
            ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'deviceAddress', 'gateUrl')
            ->get();

        // Get custom names globally (user-independent). Prefer the most recently updated per device.
        // Normalize device IDs to strings to avoid type-mismatch issues
        $deviceIds = $devices->pluck('id')->map(function ($id) { return (string) $id; });
        $customNameRows = \App\Models\CustomDeviceName::whereIn('device_id', $deviceIds)
            ->orderBy('updated_at', 'desc')
            ->get(['device_id', 'custom_name']);
        $customNames = [];
        foreach ($customNameRows as $row) {
            if (!isset($customNames[$row->device_id])) {
                $customNames[$row->device_id] = $row->custom_name;
            }
        }

        // Add custom names to devices
        $devices->each(function ($device) use ($customNames) {
            $idStr = (string) $device->id;
            $device->custom_name = $customNames[$idStr] ?? null;
        });

        // Sort devices: those with custom names first, then by name (custom or default)
        $devices = $devices->sort(function ($a, $b) {
            $aHas = !empty($a->custom_name);
            $bHas = !empty($b->custom_name);
            if ($aHas !== $bHas) {
                return $aHas ? -1 : 1;
            }
            $aName = strtolower($a->custom_name ?? $a->deviceName ?? '');
            $bName = strtolower($b->custom_name ?? $b->deviceName ?? '');
            return $aName <=> $bName;
        })->values();

        return response()->json([
            'success' => true,
            'devices' => $devices
        ]);
    }

    /**
     * Get current user's device assignments (manager only)
     */
    public function getMyAssignments(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        // Get user's device assignments
        $assignments = DeviceAssignment::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('deviceGroup')
            ->get();

        // Get device information for each assignment
        $assignments->each(function ($assignment) use ($user) {
            if ($assignment->device_group_id && $assignment->deviceGroup && $assignment->deviceGroup->gate_url) {
                $deviceInfo = \DB::connection('mysql_second')
                    ->table('goProfiles')
                    ->where('id', $assignment->device_id)
                    ->where('gateUrl', $assignment->deviceGroup->gate_url)
                    ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'deviceAddress', 'gateUrl')
                    ->first();
                
                if ($deviceInfo) {
                    // Prefer user's own custom name; if absent, fall back to the latest global custom name
                    $customName = $user->customDeviceNames()
                        ->where('device_id', $assignment->device_id)
                        ->value('custom_name');

                    if (empty($customName)) {
                        $customName = \App\Models\CustomDeviceName::where('device_id', $assignment->device_id)
                            ->orderBy('updated_at', 'desc')
                            ->value('custom_name');
                    }

                    $deviceInfo->custom_name = $customName;
                    $assignment->device_info = $deviceInfo;
                }
            }
        });

        // Sort: custom named devices first, then by name (custom or default)
        $sortedAssignments = $assignments->sort(function ($left, $right) {
            $leftInfo = $left->device_info ?? null;
            $rightInfo = $right->device_info ?? null;

            $leftHasCustom = $leftInfo && !empty($leftInfo->custom_name);
            $rightHasCustom = $rightInfo && !empty($rightInfo->custom_name);
            if ($leftHasCustom !== $rightHasCustom) {
                return $leftHasCustom ? -1 : 1;
            }

            $leftName = strtolower(($leftInfo->custom_name ?? $leftInfo->deviceName ?? ''));
            $rightName = strtolower(($rightInfo->custom_name ?? $rightInfo->deviceName ?? ''));
            return $leftName <=> $rightName;
        })->values();

        return response()->json([
            'success' => true,
            'assignments' => $sortedAssignments
        ]);
    }

    /**
     * Get all device assignments for users in groups managed by current manager
     */
    public function getManagedAssignments(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isManager()) {
            abort(403);
        }

        // Get manager's group IDs
        $groupIds = $user->userGroupAssignments()
            ->where('role', 'manager')
            ->where('is_active', true)
            ->pluck('device_group_id')
            ->filter();

        if ($groupIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'assignments' => []
            ]);
        }

        // Fetch assignments for those groups
        $assignments = \App\Models\DeviceAssignment::whereIn('device_group_id', $groupIds)
            ->where('is_active', true)
            ->where('user_id', '!=', $user->id)
            ->with(['user', 'deviceGroup'])
            ->get();

        // Preload custom names for all involved device IDs (latest wins)
        $deviceIds = $assignments->pluck('device_id')->map(function ($id) { return (string) $id; });
        $customNameRows = \App\Models\CustomDeviceName::whereIn('device_id', $deviceIds)
            ->orderBy('updated_at', 'desc')
            ->get(['device_id', 'custom_name']);
        $customNames = [];
        foreach ($customNameRows as $row) {
            if (!isset($customNames[$row->device_id])) {
                $customNames[$row->device_id] = $row->custom_name;
            }
        }

        // Attach device info for each assignment (respect group's gate_url when available)
        $assignments->each(function ($assignment) use ($customNames) {
            if ($assignment->device_group_id && $assignment->deviceGroup) {
                $query = \DB::connection('mysql_second')
                    ->table('goProfiles')
                    ->where('id', $assignment->device_id)
                    ->select('id', 'deviceName', 'devicePlatform', 'deviceOs', 'deviceStatus', 'deviceAddress', 'gateUrl');

                if (!empty($assignment->deviceGroup->gate_url)) {
                    $query->where('gateUrl', $assignment->deviceGroup->gate_url);
                }

                $deviceInfo = $query->first();
                $assignment->device_info = $deviceInfo;

                // Attach custom name if exists
                if ($deviceInfo) {
                    $idStr = (string) $assignment->device_id;
                    $deviceInfo->custom_name = $customNames[$idStr] ?? null;
                }
            }
        });

        // Sort: custom named devices first, then by name (custom or default)
        $sortedAssignments = $assignments->sort(function ($left, $right) {
            $leftInfo = $left->device_info ?? null;
            $rightInfo = $right->device_info ?? null;

            $leftHasCustom = $leftInfo && !empty($leftInfo->custom_name);
            $rightHasCustom = $rightInfo && !empty($rightInfo->custom_name);
            if ($leftHasCustom !== $rightHasCustom) {
                return $leftHasCustom ? -1 : 1;
            }

            $leftName = strtolower(($leftInfo->custom_name ?? $leftInfo->deviceName ?? ''));
            $rightName = strtolower(($rightInfo->custom_name ?? $rightInfo->deviceName ?? ''));
            return $leftName <=> $rightName;
        })->values();

        return response()->json([
            'success' => true,
            'assignments' => $sortedAssignments,
        ]);
    }

    /**
     * Get statistics for free and assigned devices within a group for the current manager
     * A device is considered free if it is assigned to exactly one manager (globally) and to no other users in the managed groups.
     */
    public function getFreeDeviceStats(Request $request, $groupId): JsonResponse
    {
        $manager = $request->user();
        if (!$manager->isManager()) {
            abort(403);
        }

        if (!$manager->isManagerOfGroup($groupId)) {
            abort(403, 'You can only view devices in groups you manage');
        }

        $group = DeviceGroup::find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Group not found'], 404);
        }

        // All devices for this group's gate URL
        $devices = collect();
        if (!empty($group->gate_url)) {
            $devices = \DB::connection('mysql_second')
                ->table('goProfiles')
                ->where('gateUrl', $group->gate_url)
                ->where('valid', 1)
                ->pluck('id')
                ->map(fn ($id) => (string) $id);
        }

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => true,
                'free' => 0,
                'assigned' => 0,
                'total' => 0,
            ]);
        }

        // Determine manager-owned devices under this gate
        $managerDeviceIds = DeviceAssignment::where('user_id', $manager->id)
            ->where('is_active', true)
            ->whereIn('device_id', $devices)
            ->pluck('device_id')
            ->map(fn ($id) => (string) $id)
            ->unique();

        if ($managerDeviceIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'free' => 0,
                'assigned' => 0,
                'total' => 0,
            ]);
        }

        // Assigned to group members (exclude managers) among manager-owned devices
        $groupMemberIds = $group->members()->pluck('users.id');
        $assignedToMembersCount = 0;
        $assignedToMembersSet = collect();
        if ($groupMemberIds->isNotEmpty()) {
            $assignedToMembers = DeviceAssignment::whereIn('device_id', $managerDeviceIds)
                ->whereIn('user_id', $groupMemberIds)
                ->where('is_active', true)
                ->pluck('device_id')
                ->unique();
            $assignedToMembersSet = $assignedToMembers;
            $assignedToMembersCount = $assignedToMembers->count();
        }

        // Free devices are manager-owned devices not assigned to group members
        $freeDeviceIds = $managerDeviceIds->diff($assignedToMembersSet);

        return response()->json([
            'success' => true,
            'free' => $freeDeviceIds->count(),
            'assigned' => $assignedToMembersCount,
            'total' => $managerDeviceIds->count(),
        ]);
    }

    /**
     * Assign N free devices to a selected user within a group (manager only)
     */
    public function assignFreeDevicesToUser(Request $request): JsonResponse
    {
        $request->validate([
            'group_id' => 'required|exists:device_groups,id',
            'user_id' => 'required|exists:users,id',
            'count' => 'required|integer|min:1',
        ]);

        $manager = $request->user();
        if (!$manager->isManager()) {
            abort(403);
        }

        $groupId = (int) $request->group_id;
        if (!$manager->isManagerOfGroup($groupId)) {
            abort(403, 'You can only assign devices in groups you manage');
        }

        $group = DeviceGroup::find($groupId);
        if (!$group || empty($group->gate_url)) {
            return response()->json(['success' => false, 'message' => 'Group or gate URL not found'], 404);
        }

        $targetUser = User::find($request->user_id);
        if (!($targetUser->isMemberOfGroup($groupId) || $targetUser->isManagerOfGroup($groupId))) {
            return response()->json(['success' => false, 'message' => 'User must belong to the group'], 422);
        }

        // All valid devices from this gate
        $devices = \DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('gateUrl', $group->gate_url)
            ->where('valid', 1)
            ->pluck('id')
            ->map(fn ($id) => (string) $id);

        if ($devices->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No devices available for this gate'], 422);
        }

        // Determine manager-owned device IDs under this gate
        $managerDeviceIds = DeviceAssignment::where('user_id', $manager->id)
            ->where('is_active', true)
            ->whereIn('device_id', $devices)
            ->pluck('device_id')
            ->map(fn ($id) => (string) $id)
            ->unique();

        if ($managerDeviceIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No manager-owned devices available'], 422);
        }

        // Free devices for assignment are manager-owned devices not currently assigned to members of this group
        $groupMemberIds = $group->members()->pluck('users.id');
        $assignedToMembersSet = collect();
        if ($groupMemberIds->isNotEmpty()) {
            $assignedToMembersSet = DeviceAssignment::whereIn('device_id', $managerDeviceIds)
                ->whereIn('user_id', $groupMemberIds)
                ->where('is_active', true)
                ->pluck('device_id')
                ->unique();
        }
        $freeDeviceIds = $managerDeviceIds->diff($assignedToMembersSet);

        $availableCount = $freeDeviceIds->count();
        $requested = (int) $request->count;
        if ($requested > $availableCount) {
            return response()->json([
                'success' => false,
                'message' => 'Requested count exceeds available free devices',
                'available' => $availableCount,
            ], 422);
        }

        // Select the first N free device IDs deterministically (sorted by id)
        $selected = $freeDeviceIds->sort()->take($requested)->values();

        // Create assignments if not existing for this user
        foreach ($selected as $deviceId) {
            $existing = DeviceAssignment::where('user_id', $targetUser->id)
                ->where('device_id', (string) $deviceId)
                ->first();
            if ($existing) {
                if (!$existing->is_active || $existing->device_group_id !== $groupId) {
                    $existing->update([
                        'is_active' => true,
                        'device_group_id' => $groupId,
                    ]);
                }
                continue;
            }
            DeviceAssignment::create([
                'user_id' => $targetUser->id,
                'device_group_id' => $groupId,
                'device_id' => (string) $deviceId,
                'access_level' => 'user',
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Devices assigned successfully',
            'assigned' => $selected->count(),
        ]);
    }
}
