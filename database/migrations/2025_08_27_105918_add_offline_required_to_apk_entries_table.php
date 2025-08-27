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
        Schema::table('apk_entries', function (Blueprint $table) {
            $table->boolean('offline_required')->default(false)->after('lib_install_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_entries', function (Blueprint $table) {
            $table->dropColumn('offline_required');
        });
    }
};
