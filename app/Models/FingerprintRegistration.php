<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintRegistration extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'identifier',
        'name',
        'finger_index',
        'device_uid',
        'device_ip',
        'enrolled_by',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'finger_index' => 'integer',
            'device_uid' => 'integer',
            'enrolled_at' => 'datetime',
        ];
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'entity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entity_id');
    }

    public function getPersonAttribute()
    {
        if ($this->entity_type === 'student') {
            return $this->student;
        }

        return $this->user;
    }
}
