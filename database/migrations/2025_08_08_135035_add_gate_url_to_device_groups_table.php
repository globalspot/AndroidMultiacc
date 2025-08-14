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
            $table->string('gate_url')->nullable()->after('device_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->dropColumn('gate_url');
        });
    }
};
