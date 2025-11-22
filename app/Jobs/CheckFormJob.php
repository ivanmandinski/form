<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckFormJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public \App\Models\FormTarget $formTarget
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\FormCheckService $formCheckService): void
    {
        $formCheckService->checkForm($this->formTarget);
    }
}
