<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroTimer extends Model
{
    use HasFactory;

    protected $fillable = [
        'macro_id',
        'name',
        'delay',
        'unit',
        'description',
        'is_repeating',
        'repeat_count',
    ];

    protected $casts = [
        'is_repeating' => 'boolean',
        'repeat_count' => 'integer',
    ];

    public function macro(): BelongsTo
    {
        return $this->belongsTo(AutomationMacro::class);
    }

    public function getDelayInSecondsAttribute()
    {
        $multipliers = [
            'seconds' => 1,
            'minutes' => 60,
            'hours' => 3600,
            'days' => 86400,
        ];

        return $this->delay * ($multipliers[$this->unit] ?? 1);
    }

    public function getFormattedDelayAttribute()
    {
        return $this->delay . ' ' . $this->unit;
    }
}

