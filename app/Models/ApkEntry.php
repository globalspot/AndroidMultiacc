<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApkEntry extends Model
{
    protected $fillable = [
        'app_name',
        'filename',
        'version',
        'url',
        'icon_url',
        'lib_filename',
        'lib_url',
        'lib_install_order',
        'add_date',
    ];
}
