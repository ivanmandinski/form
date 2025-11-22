<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Cron\CronExpression;

class FormTarget extends Model
{
    protected $fillable = [
        'target_id',
        'selector_type',
        'selector_value',
        'method_override',
        'action_override',
        'driver_type',
        'uses_js',
        'recaptcha_expected',
        'success_selector',
        'error_selector',
        'schedule_enabled',
        'schedule_frequency',
        'schedule_cron',
        'schedule_timezone',
        'schedule_next_run_at',
        'last_scheduled_run_at',
        'execute_javascript',
        'wait_for_elements',
        'custom_actions',
    ];

    protected $casts = [
        'uses_js' => 'boolean',
        'recaptcha_expected' => 'boolean',
        'schedule_enabled' => 'boolean',
        'schedule_next_run_at' => 'datetime',
        'last_scheduled_run_at' => 'datetime',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function fieldMappings(): HasMany
    {
        return $this->hasMany(FieldMapping::class);
    }

    public function checkRuns(): HasMany
    {
        return $this->hasMany(CheckRun::class);
    }

    public function getNextRunTimeAttribute(): ?Carbon
    {
        if (!$this->schedule_enabled || $this->schedule_frequency === 'manual') {
            return null;
        }

        if ($this->schedule_frequency === 'cron' && $this->schedule_cron) {
            $cron = new CronExpression($this->schedule_cron);
            return $cron->getNextRunDate();
        }

        $now = Carbon::now($this->schedule_timezone);
        
        switch ($this->schedule_frequency) {
            case 'hourly':
                return $now->addHour()->startOfHour();
            case 'daily':
                return $now->addDay()->startOfDay();
            case 'weekly':
                return $now->addWeek()->startOfWeek();
            default:
                return null;
        }
    }

    public function advanceSchedule(): void
    {
        if (!$this->schedule_enabled || $this->schedule_frequency === 'manual') {
            return;
        }

        $this->last_scheduled_run_at = Carbon::now($this->schedule_timezone);
        
        if ($this->schedule_frequency === 'cron' && $this->schedule_cron) {
            $cron = new CronExpression($this->schedule_cron);
            $this->schedule_next_run_at = $cron->getNextRunDate();
        } else {
            $this->schedule_next_run_at = $this->getNextRunTimeAttribute();
        }
        
        $this->save();
    }
}
