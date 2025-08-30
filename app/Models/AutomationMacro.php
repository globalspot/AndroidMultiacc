<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationMacro extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'nodes',
        'connections',
        'is_active',
        'last_executed_at',
    ];

    protected $casts = [
        'nodes' => 'array',
        'connections' => 'array',
        'is_active' => 'boolean',
        'last_executed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(MacroVariable::class);
    }

    public function timers(): HasMany
    {
        return $this->hasMany(MacroTimer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

