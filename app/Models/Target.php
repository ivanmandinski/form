<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Target extends Model
{
    protected $fillable = [
        'url',
        'notes',
    ];

    protected $casts = [
        'url' => 'string',
        'notes' => 'string',
    ];

    public function formTargets(): HasMany
    {
        return $this->hasMany(FormTarget::class);
    }
}
