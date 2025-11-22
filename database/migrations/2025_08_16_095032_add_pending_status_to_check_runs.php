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
        Schema::table('check_runs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('check_runs', function (Blueprint $table) {
            $table->enum('status', ['pending', 'success', 'failure', 'blocked', 'error'])->after('driver');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_runs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('check_runs', function (Blueprint $table) {
            $table->enum('status', ['success', 'failure', 'blocked', 'error'])->after('driver');
        });
    }
};
