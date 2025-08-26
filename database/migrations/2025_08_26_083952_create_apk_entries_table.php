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
        Schema::create('apk_entries', function (Blueprint $table) {
            $table->id();
            $table->string('app_name'); // folder name under public/apks
            $table->string('filename'); // apk file name
            $table->string('url'); // absolute URL to the file
            $table->timestamp('add_date'); // when enabled/added to DB
            $table->timestamps();

            $table->unique(['app_name', 'filename']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apk_entries');
    }
};
