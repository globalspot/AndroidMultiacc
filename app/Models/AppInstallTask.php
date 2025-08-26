<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppInstallTask extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'app_install_tasks';

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'app_title',
        'app_filename',
        'app_url',
        'lib_url',
        'install_order',
        'add_date',
        'update_date',
        'comlete_date',
        'install_status',
    ];
}


