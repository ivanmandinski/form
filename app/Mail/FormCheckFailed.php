<?php

namespace App\Mail;

use App\Models\CheckRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormCheckFailed extends Mailable
{
    use Queueable, SerializesModels;

    public $checkRun;

    public function __construct(CheckRun $checkRun)
    {
        $this->checkRun = $checkRun;
    }

    public function build()
    {
        return $this->subject('Form Check Failed: ' . $this->checkRun->formTarget->target->url)
                    ->view('emails.form_check_failed');
    }
}
