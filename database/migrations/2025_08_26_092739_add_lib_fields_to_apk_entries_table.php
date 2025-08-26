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
            $table->string('lib_filename')->nullable()->after('icon_url');
            $table->string('lib_url')->nullable()->after('lib_filename');
            $table->enum('lib_install_order', ['before', 'after'])->nullable()->after('lib_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_entries', function (Blueprint $table) {
            $table->dropColumn('lib_filename');
            $table->dropColumn('lib_url');
            $table->dropColumn('lib_install_order');
        });
    }
};
