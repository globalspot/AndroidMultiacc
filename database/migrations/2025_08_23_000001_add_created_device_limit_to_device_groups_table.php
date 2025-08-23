<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->unsignedInteger('created_device_limit')->default(100)->after('device_limit');
        });
    }

    public function down(): void
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->dropColumn('created_device_limit');
        });
    }
};


