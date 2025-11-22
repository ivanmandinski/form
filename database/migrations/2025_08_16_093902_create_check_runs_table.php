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
        Schema::create('check_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_target_id')->constrained()->onDelete('cascade');
            $table->enum('driver', ['http', 'dusk']);
            $table->enum('status', ['success', 'failure', 'blocked', 'error']);
            $table->integer('http_status')->nullable();
            $table->string('final_url')->nullable();
            $table->text('message_excerpt')->nullable();
            $table->json('error_detail')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_runs');
    }
};
