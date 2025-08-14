<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_group_id',
        'manager_id',
        'token',
        'expires_at',
        'max_uses',
        'uses',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function deviceGroup()
    {
        return $this->belongsTo(DeviceGroup::class, 'device_group_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if (!empty($this->expires_at) && now()->greaterThan($this->expires_at)) {
            return false;
        }
        if (!empty($this->max_uses) && $this->uses >= $this->max_uses) {
            return false;
        }
        return true;
    }
}


