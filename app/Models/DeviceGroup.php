<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'device_limit',
        'created_device_limit',
        'gate_url',
    ];

    /**
     * Get device assignments for this group
     */
    public function deviceAssignments(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class);
    }

    /**
     * Get users assigned to this group
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'device_assignments', 'device_group_id', 'user_id');
    }

    /**
     * Get devices in this group
     */
    public function devices()
    {
        return $this->hasMany(DeviceAssignment::class)->whereNotNull('device_id');
    }

    /**
     * Get count of currently running devices in this group
     */
    public function getRunningDevicesCount()
    {
        $deviceIds = $this->deviceAssignments()->pluck('device_id');
        
        if ($deviceIds->isEmpty()) {
            return 0;
        }

        return \DB::connection('mysql_second')
            ->table('goProfiles')
            ->whereIn('id', $deviceIds)
            ->where('deviceStatus', 'online')
            ->count();
    }

    /**
     * Check if group has reached device limit
     */
    public function hasReachedLimit()
    {
        return $this->getRunningDevicesCount() >= $this->device_limit;
    }

    /**
     * Get remaining device slots
     */
    public function getRemainingSlots()
    {
        return max(0, $this->device_limit - $this->getRunningDevicesCount());
    }

    /**
     * Get count of devices created under this group's gate
     */
    public function getCreatedDevicesCount(): int
    {
        if (!$this->gate_url) {
            return 0;
        }
        return (int) \DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('gateUrl', $this->gate_url)
            ->where('valid', 1)
            ->count();
    }

    /**
     * Check if group has reached created devices limit
     */
    public function hasReachedCreatedLimit(): bool
    {
        return $this->getCreatedDevicesCount() >= (int) ($this->created_device_limit ?? 0);
    }

    /**
     * Get remaining created device slots
     */
    public function getRemainingCreatedSlots(): int
    {
        return max(0, ((int) ($this->created_device_limit ?? 0)) - $this->getCreatedDevicesCount());
    }

    /**
     * Get user group assignments for this group
     */
    public function userGroupAssignments()
    {
        return $this->hasMany(UserGroupAssignment::class);
    }

    /**
     * Get users assigned to this group
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_group_assignments', 'device_group_id', 'user_id')
            ->withPivot('role', 'is_active')
            ->wherePivot('is_active', true);
    }

    /**
     * Get managers of this group
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'user_group_assignments', 'device_group_id', 'user_id')
            ->withPivot('role', 'is_active')
            ->wherePivot('role', 'manager')
            ->wherePivot('is_active', true);
    }

    /**
     * Get members of this group
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'user_group_assignments', 'device_group_id', 'user_id')
            ->withPivot('role', 'is_active')
            ->wherePivot('role', 'member')
            ->wherePivot('is_active', true);
    }

    /**
     * Check if user is assigned to this group
     */
    public function hasUser($userId): bool
    {
        return $this->userGroupAssignments()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user is manager of this group
     */
    public function hasManager($userId): bool
    {
        return $this->userGroupAssignments()
            ->where('user_id', $userId)
            ->where('role', 'manager')
            ->where('is_active', true)
            ->exists();
    }
}
