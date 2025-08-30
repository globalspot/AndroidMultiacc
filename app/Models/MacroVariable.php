<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'macro_id',
        'name',
        'type',
        'default_value',
        'description',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function macro(): BelongsTo
    {
        return $this->belongsTo(AutomationMacro::class);
    }

    public function getFormattedValueAttribute()
    {
        if ($this->type === 'boolean') {
            return $this->default_value ? 'true' : 'false';
        }
        
        if ($this->type === 'array' && is_string($this->default_value)) {
            return json_decode($this->default_value, true);
        }
        
        return $this->default_value;
    }
}

