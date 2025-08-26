<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_second')->table('app_install_tasks', function (Blueprint $table) {
            if (!Schema::connection('mysql_second')->hasColumn('app_install_tasks', 'permissions')) {
                $table->json('permissions')->nullable()->after('install_status');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_second')->table('app_install_tasks', function (Blueprint $table) {
            if (Schema::connection('mysql_second')->hasColumn('app_install_tasks', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });
    }
};


