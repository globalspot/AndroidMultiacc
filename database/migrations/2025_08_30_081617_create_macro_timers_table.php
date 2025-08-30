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
        Schema::create('macro_timers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('macro_id')->constrained('automation_macros')->onDelete('cascade');
            $table->string('name');
            $table->integer('delay');
            $table->enum('unit', ['seconds', 'minutes', 'hours', 'days']);
            $table->text('description')->nullable();
            $table->boolean('is_repeating')->default(false);
            $table->integer('repeat_count')->nullable();
            $table->timestamps();
            
            $table->index(['macro_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macro_timers');
    }
};
