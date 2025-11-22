<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER TABLE with ENUM, so we need to recreate the table
        // First, backup existing data
        $existingData = DB::table('check_runs')->get();
        
        // Drop foreign key constraint from check_artifacts if it exists
        if (Schema::hasTable('check_artifacts')) {
            Schema::table('check_artifacts', function (Blueprint $table) {
                $table->dropForeign(['check_run_id']);
            });
        }
        
        // Drop the existing table
        Schema::dropIfExists('check_runs');
        
        // Recreate the table with the new enum values
        Schema::create('check_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_target_id')->constrained()->onDelete('cascade');
            $table->enum('driver', ['http', 'dusk', 'puppeteer']);
            $table->enum('status', ['success', 'failure', 'blocked', 'error', 'pending']);
            $table->integer('http_status')->nullable();
            $table->string('final_url')->nullable();
            $table->text('message_excerpt')->nullable();
            $table->json('error_detail')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        
        // Restore the data
        foreach ($existingData as $row) {
            DB::table('check_runs')->insert([
                'id' => $row->id,
                'form_target_id' => $row->form_target_id,
                'driver' => $row->driver,
                'status' => $row->status,
                'http_status' => $row->http_status,
                'final_url' => $row->final_url,
                'message_excerpt' => $row->message_excerpt,
                'error_detail' => $row->error_detail,
                'started_at' => $row->started_at,
                'finished_at' => $row->finished_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
        
        // Recreate foreign key constraint if check_artifacts table exists
        if (Schema::hasTable('check_artifacts')) {
            Schema::table('check_artifacts', function (Blueprint $table) {
                $table->foreign('check_run_id')->references('id')->on('check_runs')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data
        $existingData = DB::table('check_runs')->get();
        
        // Drop foreign key constraint from check_artifacts if it exists
        if (Schema::hasTable('check_artifacts')) {
            Schema::table('check_artifacts', function (Blueprint $table) {
                $table->dropForeign(['check_run_id']);
            });
        }
        
        // Drop the table
        Schema::dropIfExists('check_runs');
        
        // Recreate with old enum values
        Schema::create('check_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_target_id')->constrained()->onDelete('cascade');
            $table->enum('driver', ['http', 'dusk']);
            $table->enum('status', ['success', 'failure', 'blocked', 'error', 'pending']);
            $table->integer('http_status')->nullable();
            $table->string('final_url')->nullable();
            $table->text('message_excerpt')->nullable();
            $table->json('error_detail')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        
        // Restore data (only http and dusk drivers)
        foreach ($existingData as $row) {
            if (in_array($row->driver, ['http', 'dusk'])) {
                DB::table('check_runs')->insert([
                    'id' => $row->id,
                    'form_target_id' => $row->form_target_id,
                    'driver' => $row->driver,
                    'status' => $row->status,
                    'http_status' => $row->http_status,
                    'final_url' => $row->final_url,
                    'message_excerpt' => $row->message_excerpt,
                    'error_detail' => $row->error_detail,
                    'started_at' => $row->started_at,
                    'finished_at' => $row->finished_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
        
        // Recreate foreign key constraint if check_artifacts table exists
        if (Schema::hasTable('check_artifacts')) {
            Schema::table('check_artifacts', function (Blueprint $table) {
                $table->foreign('check_run_id')->references('id')->on('check_runs')->onDelete('cascade');
            });
        }
    }
};
