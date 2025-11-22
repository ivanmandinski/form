<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Target;
use App\Models\FormTarget;
use App\Models\FieldMapping;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo targets
        $target1 = Target::create([
            'url' => 'https://httpbin.org/forms/post',
            'notes' => 'Demo form for testing HTTP submissions',
        ]);

        $target2 = Target::create([
            'url' => 'https://example.com/contact',
            'notes' => 'Example contact form',
        ]);

        // Create demo forms
        $form1 = FormTarget::create([
            'target_id' => $target1->id,
            'selector_type' => 'css',
            'selector_value' => 'form',
            'method_override' => 'POST',
            'action_override' => 'https://httpbin.org/post',
            'uses_js' => false,
            'recaptcha_expected' => false,
            'success_selector' => '.success',
            'error_selector' => '.error',
            'schedule_enabled' => true,
            'schedule_frequency' => 'hourly',
            'schedule_timezone' => 'UTC',
        ]);

        $form2 = FormTarget::create([
            'target_id' => $target2->id,
            'selector_type' => 'id',
            'selector_value' => 'contact-form',
            'uses_js' => true,
            'recaptcha_expected' => false,
            'schedule_enabled' => false,
            'schedule_frequency' => 'manual',
        ]);

        // Create demo field mappings
        FieldMapping::create([
            'form_target_id' => $form1->id,
            'name' => 'custname',
            'value' => 'Demo User',
        ]);

        FieldMapping::create([
            'form_target_id' => $form1->id,
            'name' => 'custtel',
            'value' => '+1234567890',
        ]);

        FieldMapping::create([
            'form_target_id' => $form1->id,
            'name' => 'custemail',
            'value' => 'demo@example.com',
        ]);

        FieldMapping::create([
            'form_target_id' => $form2->id,
            'name' => 'name',
            'value' => 'Test User',
        ]);

        FieldMapping::create([
            'form_target_id' => $form2->id,
            'name' => 'email',
            'value' => 'test@example.com',
        ]);

        FieldMapping::create([
            'form_target_id' => $form2->id,
            'name' => 'message',
            'value' => 'This is a test message from the form monitor.',
        ]);
    }
}
