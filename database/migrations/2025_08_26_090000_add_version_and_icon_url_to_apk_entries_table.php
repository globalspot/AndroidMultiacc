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
            $table->string('version')->nullable()->after('filename');
            $table->string('icon_url')->nullable()->after('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_entries', function (Blueprint $table) {
            $table->dropColumn('version');
            $table->dropColumn('icon_url');
        });
    }
};
