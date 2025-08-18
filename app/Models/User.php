<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has manager role
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user has user role
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has admin or manager role
     */
    public function isAdminOrManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Get device assignments for this user
     */
    public function deviceAssignments()
    {
        return $this->hasMany(DeviceAssignment::class);
    }

    /**
     * Get devices assigned to this user
     */
    public function assignedDevices()
    {
        return $this->deviceAssignments()->where('is_active', true);
    }

    /**
     * Get device groups this user manages
     */
    public function managedGroups()
    {
        // Managers are determined via UserGroupAssignment with role 'manager'
        return $this->userGroupAssignments()
            ->where('role', 'manager')
            ->where('is_active', true)
            ->with('deviceGroup');
    }

    /**
     * Get all devices user has access to based on role
     */
    public function getAccessibleDevices()
    {
        if ($this->isAdmin()) {
            // Admin sees all devices
            return DeviceAssignment::where('is_active', true)->get();
        } elseif ($this->isManager()) {
            // Manager sees devices in groups they manage (from UserGroupAssignment)
            $groupIds = $this->userGroupAssignments()
                ->where('role', 'manager')
                ->where('is_active', true)
                ->pluck('device_group_id')
                ->filter();
            
            return DeviceAssignment::whereIn('device_group_id', $groupIds)
                ->where('is_active', true)
                ->get();
        } else {
            // User sees devices assigned directly to them, including owner level
            return $this->assignedDevices()->orWhere(function($q){
                $q->where('user_id', $this->id)->where('access_level', 'owner');
            })->get();
        }
    }

    /**
     * Get custom device names for this user
     */
    public function customDeviceNames()
    {
        return $this->hasMany(CustomDeviceName::class);
    }

    /**
     * Get custom name for a specific device
     */
    public function getCustomDeviceName($deviceId)
    {
        return $this->customDeviceNames()
            ->where('device_id', $deviceId)
            ->value('custom_name');
    }

    /**
     * Get user group assignments
     */
    public function userGroupAssignments()
    {
        return $this->hasMany(UserGroupAssignment::class);
    }

    /**
     * Get groups where user is a member
     */
    public function memberGroups()
    {
        return $this->userGroupAssignments()
            ->where('role', 'member')
            ->where('is_active', true)
            ->with('deviceGroup');
    }

    /**
     * Get groups where user is a manager
     */
    public function managerGroups()
    {
        return $this->userGroupAssignments()
            ->where('role', 'manager')
            ->where('is_active', true)
            ->with('deviceGroup');
    }

    /**
     * Get all groups user belongs to (as member or manager)
     */
    public function allGroups()
    {
        return $this->userGroupAssignments()
            ->where('is_active', true)
            ->with('deviceGroup');
    }

    /**
     * Check if user is member of specific group
     */
    public function isMemberOfGroup($groupId): bool
    {
        return $this->userGroupAssignments()
            ->where('device_group_id', $groupId)
            ->where('role', 'member')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user is manager of specific group
     */
    public function isManagerOfGroup($groupId): bool
    {
        return $this->userGroupAssignments()
            ->where('device_group_id', $groupId)
            ->where('role', 'manager')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get users that this user can manage (in groups where user is manager)
     */
    public function getManageableUsers()
    {
        if (!$this->isManager()) {
            return collect();
        }

        $groupIds = $this->managerGroups()->pluck('device_group_id');
        
        return User::whereHas('userGroupAssignments', function ($query) use ($groupIds) {
            $query->whereIn('device_group_id', $groupIds)
                  ->where('role', 'member')
                  ->where('is_active', true);
        })->get();
    }

    /**
     * Get devices from gate URLs assigned to groups where user is manager
     */
    public function getManageableDevices()
    {
        if (!$this->isManager()) {
            return collect();
        }

        $groupIds = $this->managerGroups()->pluck('device_group_id');
        $gateUrls = DeviceGroup::whereIn('id', $groupIds)
            ->whereNotNull('gate_url')
            ->pluck('gate_url');

        if ($gateUrls->isEmpty()) {
            return collect();
        }

        // Get devices from organic database based on gate URLs
        $devices = collect();
        foreach ($gateUrls as $gateUrl) {
            $devicesFromGate = \DB::connection('mysql_second')
                ->table('goProfiles')
                ->where('gateUrl', $gateUrl)
                ->get();
            
            $devices = $devices->merge($devicesFromGate);
        }

        return $devices;
    }
}
