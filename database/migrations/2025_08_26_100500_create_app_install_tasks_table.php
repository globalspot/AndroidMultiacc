<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_second')->create('app_install_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('device_id');
            $table->string('app_title');
            $table->string('app_filename');
            $table->string('app_url', 2048);
            $table->string('lib_url', 2048)->nullable();
            $table->string('install_order', 16)->nullable();
            $table->dateTime('add_date');
            $table->dateTime('update_date')->nullable();
            // Note: spelling per request
            $table->dateTime('comlete_date')->nullable();
            $table->string('install_status', 64)->default('queued');

            $table->index('device_id');
            $table->index('install_status');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_second')->dropIfExists('app_install_tasks');
    }
};


