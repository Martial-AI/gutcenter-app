<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricEvent extends Model
{
    protected $fillable = [
        'device_identifier',
        'external_identifier',
        'occurred_at',
        'payload',
        'status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'processed_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
