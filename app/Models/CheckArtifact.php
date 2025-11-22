<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CheckArtifact extends Model
{
    const TYPE_HTML = 'html';
    const TYPE_SCREENSHOT = 'screenshot';
    const TYPE_DEBUG_INFO = 'debug_info';

    protected $fillable = [
        'check_run_id',
        'type',
        'path',
    ];

    protected $casts = [
        'type' => 'string',
        'path' => 'string',
    ];

    public function checkRun(): BelongsTo
    {
        return $this->belongsTo(CheckRun::class);
    }

    public function isHtml(): bool
    {
        return $this->type === self::TYPE_HTML;
    }

    public function isScreenshot(): bool
    {
        return $this->type === self::TYPE_SCREENSHOT;
    }

    public function isDebugInfo(): bool
    {
        return $this->type === self::TYPE_DEBUG_INFO;
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getContentAttribute(): ?string
    {
        if (!$this->path || !Storage::disk('public')->exists($this->path)) {
            return null;
        }

        return Storage::disk('public')->get($this->path);
    }
}
