<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomDeviceName extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'user_id',
        'custom_name',
    ];

    /**
     * Get the user that owns the custom device name
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
