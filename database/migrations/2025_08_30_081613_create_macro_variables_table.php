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
        Schema::create('macro_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('macro_id')->constrained('automation_macros')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // string, integer, boolean, array, object
            $table->text('default_value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();
            
            $table->index(['macro_id', 'name']);
            $table->unique(['macro_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macro_variables');
    }
};
