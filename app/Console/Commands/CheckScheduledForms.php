<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FormTarget;
use App\Services\FormCheckService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckScheduledForms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:check-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and run scheduled form targets';

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
        $this->info('Checking scheduled forms...');
        
        $dueForms = FormTarget::where('schedule_enabled', true)
            ->where(function ($query) {
                $query->where('schedule_next_run_at', '<=', now())
                      ->orWhereNull('schedule_next_run_at');
            })
            ->get();
        
        if ($dueForms->isEmpty()) {
            $this->info('No forms due for execution.');
            return 0;
        }
        
        $this->info("Found {$dueForms->count()} forms due for execution.");
        
        foreach ($dueForms as $form) {
            try {
                $this->info("Processing form {$form->id} for target {$form->target->url}");
                
                // Dispatch the check job
                $this->dispatchCheckJob($form);
                
                // Advance the schedule
                $form->advanceSchedule();
                
                $this->info("Form {$form->id} scheduled for next run at {$form->schedule_next_run_at}");
                
            } catch (\Exception $e) {
                $this->error("Error processing form {$form->id}: " . $e->getMessage());
                Log::error('Scheduler error', [
                    'form_target_id' => $form->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info('Scheduled forms check completed.');
        return 0;
    }
    
    private function dispatchCheckJob(FormTarget $form): void
    {
        // Dispatch the job to the queue
        \App\Jobs\CheckFormJob::dispatch($form);
        
        $this->info("Dispatched check job for form {$form->id}");
    }
}
