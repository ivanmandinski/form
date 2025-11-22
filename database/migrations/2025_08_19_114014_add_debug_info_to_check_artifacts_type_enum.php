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
        // SQLite doesn't support ALTER TABLE for ENUMs, so we need to recreate the table
        Schema::dropIfExists('check_artifacts');
        
        Schema::create('check_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_run_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['html', 'screenshot', 'debug_info']);
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_artifacts');
        
        Schema::create('check_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_run_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['html', 'screenshot']);
            $table->string('path');
            $table->timestamps();
        });
    }
};
