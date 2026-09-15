<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'day_of_week',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active'   => 'boolean',
        ];
    }

    /** Only active schedules. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Schedules that apply on the given Carbon day.
     * day_of_week: 0=Mon … 6=Sun (ISO weekday - 1).
     * null means every working day.
     */
    public function scopeForDay(Builder $query, \Carbon\Carbon $date): Builder
    {
        $dow = $date->dayOfWeekIso - 1; // convert 1-7 → 0-6
        return $query->where(function ($q) use ($dow): void {
            $q->whereNull('day_of_week')->orWhere('day_of_week', $dow);
        });
    }

    /** Human-readable day name. */
    public function getDayNameAttribute(): string
    {
        if ($this->day_of_week === null) {
            return __('Every day');
        }
        $days = [
            0 => __('Monday'), 1 => __('Tuesday'), 2 => __('Wednesday'),
            3 => __('Thursday'), 4 => __('Friday'), 5 => __('Saturday'), 6 => __('Sunday'),
        ];
        return $days[$this->day_of_week] ?? __('Unknown');
    }
}
