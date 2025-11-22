<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckRun extends Model
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_ERROR = 'error';

    const DRIVER_HTTP = 'http';
    const DRIVER_DUSK = 'dusk';
    const DRIVER_PUPPETEER = 'puppeteer';

    protected $fillable = [
        'form_target_id',
        'driver',
        'status',
        'http_status',
        'final_url',
        'message_excerpt',
        'error_detail',
        'debug_info',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'http_status' => 'integer',
        'error_detail' => 'array',
        'debug_info' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function formTarget(): BelongsTo
    {
        return $this->belongsTo(FormTarget::class);
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(CheckArtifact::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }
}
