<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_group_id',
        'device_id',
        'access_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the device group for this assignment
     */
    public function deviceGroup(): BelongsTo
    {
        return $this->belongsTo(DeviceGroup::class);
    }

    /**
     * Get device information from organic database
     */
    public function getDeviceInfo()
    {
        // Connect to the organic database to get device info
        $device = \DB::connection('mysql_second')
            ->table('goProfiles')
            ->where('id', $this->device_id)
            ->first();

        return $device;
    }

    /**
     * Check if user has specific access level
     */
    public function hasAccessLevel(string $level): bool
    {
        $levels = ['user' => 1, 'manager' => 2, 'owner' => 3];
        
        return $levels[$this->access_level] >= $levels[$level];
    }
}
