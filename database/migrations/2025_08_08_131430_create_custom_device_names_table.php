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
        Schema::create('custom_device_names', function (Blueprint $table) {
            $table->id();
            $table->string('device_id'); // Reference to goProfiles table
            $table->unsignedBigInteger('user_id');
            $table->string('custom_name');
            $table->timestamps();
            
            // Unique constraint to ensure one custom name per device per user
            $table->unique(['device_id', 'user_id']);
            
            // Foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_device_names');
    }
};
