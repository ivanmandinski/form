<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldMapping extends Model
{
    protected $fillable = [
        'form_target_id',
        'name',
        'selector',
        'value',
        'type',
        'clear_first',
        'delay',
    ];

    protected $casts = [
        'name' => 'string',
        'selector' => 'string',
        'value' => 'string',
        'type' => 'string',
        'clear_first' => 'boolean',
        'delay' => 'integer',
    ];

    public function formTarget(): BelongsTo
    {
        return $this->belongsTo(FormTarget::class);
    }
}
