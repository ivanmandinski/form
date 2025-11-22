<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FormTarget;
use App\Models\CheckRun;
use App\Services\FormCheckService;
use Illuminate\Support\Facades\Log;

class RunFormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:test {form : The ID of the form target to run} {--json : Output machine-readable summary}';

    /**
     * @var array<int, string>
     */
    protected $aliases = ['forms:run'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a specific form target by ID';

    public function __construct(
        private FormCheckService $formCheckService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $formId = $this->argument('form');
        
        try {
            $form = FormTarget::with('target')->findOrFail($formId);
            
            $this->info("Running form check for Form ID: {$form->id}");
            $this->info("Target URL: {$form->target->url}");
            $this->info("Driver: {$form->driver_type}");
            $this->info("Selector: {$form->selector_type}#{$form->selector_value}");
            $this->line('');
            
            $this->info('Starting form check...');
            
            $checkRun = $this->formCheckService->checkForm($form);
            
            $this->line('');
            $this->info('Form check completed!');
            $this->line('');
            
            // Display results
            $this->displayResults($checkRun);

            if ($this->option('json')) {
                $this->line(json_encode($this->buildSummary($checkRun), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            
            return 0;
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("Form target with ID {$formId} not found.");
            return 1;
        } catch (\Exception $e) {
            $this->error("Error running form check: " . $e->getMessage());
            Log::error('Manual form run error', [
                'form_target_id' => $formId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
    
    private function displayResults(CheckRun $checkRun): void
    {
        $this->info('=== RESULTS ===');
        
        // Status
        if ($checkRun->status === CheckRun::STATUS_SUCCESS) {
            $this->info('✅ Status: SUCCESS');
        } elseif ($checkRun->status === CheckRun::STATUS_ERROR) {
            $this->error('❌ Status: ERROR');
        } elseif ($checkRun->status === CheckRun::STATUS_FAILURE) {
            $this->error('❌ Status: FAILURE');
        } else {
            $this->warn("⏳ Status: " . strtoupper($checkRun->status));
        }
        
        // Driver used
        $this->info("🚗 Driver: " . strtoupper($checkRun->driver));
        
        // Message
        if ($checkRun->message) {
            $this->info("💬 Message: {$checkRun->message}");
        }
        
        // Error details
        if ($checkRun->error_details) {
            $this->error("⚠️ Error: {$checkRun->error_details}");
        }
        
        // Response time
        if ($checkRun->response_time_ms) {
            $this->info("⏱️ Response Time: {$checkRun->response_time_ms}ms");
        }
        
        // Final URL
        if ($checkRun->final_url) {
            $this->info("🔗 Final URL: {$checkRun->final_url}");
        }
        
        // Execution time
        if ($checkRun->started_at && $checkRun->completed_at) {
            $duration = $checkRun->started_at->diffInSeconds($checkRun->completed_at);
            $this->info("⏱️ Execution Time: {$duration}s");
        }
        
        // Artifacts
        $artifacts = $checkRun->artifacts;
        if ($artifacts->count() > 0) {
            $this->info("📎 Artifacts: {$artifacts->count()}");
            foreach ($artifacts as $artifact) {
                $this->info("   • {$artifact->type}: " . $this->artifactUrl($artifact->path));
            }
        }
        
        // Check run details
        $this->info("📊 Check Run ID: {$checkRun->id}");
        if ($runUrl = $this->adminRunUrl($checkRun->id)) {
            $this->info("🔍 View details: {$runUrl}");
        }
    }

    private function buildSummary(CheckRun $checkRun): array
    {
        return [
            'run_id' => $checkRun->id,
            'status' => $checkRun->status,
            'driver' => $checkRun->driver,
            'final_url' => $checkRun->final_url,
            'message' => $checkRun->message_excerpt,
            'error_detail' => $checkRun->error_detail,
            'finished_at' => optional($checkRun->finished_at)->toDateTimeString(),
            'artifacts' => $checkRun->artifacts->map(function ($artifact) {
                return [
                    'type' => $artifact->type,
                    'path' => $artifact->path,
                    'url' => $this->artifactUrl($artifact->path),
                ];
            })->all(),
        ];
    }

    private function artifactUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $base = rtrim(config('app.url') ?? '', '/');

        return $base ? "{$base}/storage/{$path}" : "/storage/{$path}";
    }

    private function adminRunUrl(int $runId): ?string
    {
        $base = rtrim(config('app.url') ?? '', '/');

        return $base ? "{$base}/admin/runs/{$runId}" : null;
    }
}
