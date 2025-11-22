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
        // Add advanced Puppeteer fields to form_targets table
        Schema::table('form_targets', function (Blueprint $table) {
            $table->text('execute_javascript')->nullable()->after('error_selector');
            $table->json('wait_for_elements')->nullable()->after('execute_javascript');
            $table->json('custom_actions')->nullable()->after('wait_for_elements');
        });

        // Add advanced field mapping fields to field_mappings table
        Schema::table('field_mappings', function (Blueprint $table) {
            $table->string('selector')->nullable()->after('name');
            $table->string('type')->default('text')->after('value');
            $table->boolean('clear_first')->default(true)->after('type');
            $table->integer('delay')->default(100)->after('clear_first');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove advanced Puppeteer fields from form_targets table
        Schema::table('form_targets', function (Blueprint $table) {
            $table->dropColumn(['execute_javascript', 'wait_for_elements', 'custom_actions']);
        });

        // Remove advanced field mapping fields from field_mappings table
        Schema::table('field_mappings', function (Blueprint $table) {
            $table->dropColumn(['selector', 'type', 'clear_first', 'delay']);
        });
    }
};