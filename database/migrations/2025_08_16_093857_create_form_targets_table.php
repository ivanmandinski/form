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
        Schema::create('form_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained()->onDelete('cascade');
            $table->enum('selector_type', ['id', 'class', 'css']);
            $table->string('selector_value');
            $table->string('method_override')->nullable();
            $table->string('action_override')->nullable();
            $table->boolean('uses_js')->default(false);
            $table->boolean('recaptcha_expected')->default(false);
            $table->string('success_selector')->nullable();
            $table->string('error_selector')->nullable();
            
            // Scheduling fields
            $table->boolean('schedule_enabled')->default(false);
            $table->enum('schedule_frequency', ['manual', 'hourly', 'daily', 'weekly', 'cron'])->default('manual');
            $table->string('schedule_cron')->nullable();
            $table->string('schedule_timezone')->default('UTC');
            $table->timestamp('schedule_next_run_at')->nullable();
            $table->timestamp('last_scheduled_run_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_targets');
    }
};
