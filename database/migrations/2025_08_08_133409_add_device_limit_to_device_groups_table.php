<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->unsignedInteger('device_limit')->default(10)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->dropColumn('device_limit');
        });
    }
};
