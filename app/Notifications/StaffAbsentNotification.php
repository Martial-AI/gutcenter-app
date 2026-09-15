<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class StaffAbsentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User   $absentUser,
        public readonly string $type,        // 'permanent' | 'non_permanent'
        public readonly string $slotLabel,   // e.g. "08:00 – 11:30" or "Mathématiques (09:00)"
        public readonly Carbon $date,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $name = trim(($this->absentUser->first_name ?? '') . ' ' . ($this->absentUser->last_name ?? $this->absentUser->name));

        return [
            'icon'    => 'alert',
            'color'   => 'rose',
            'title'   => __('Absence Alert'),
            'message' => $this->type === 'permanent'
                ? __(':name was absent during work slot :slot on :date.', [
                    'name' => $name,
                    'slot' => $this->slotLabel,
                    'date' => $this->date->translatedFormat('l d/m/Y'),
                  ])
                : __(':name did not attend their class (:slot) on :date.', [
                    'name' => $name,
                    'slot' => $this->slotLabel,
                    'date' => $this->date->translatedFormat('l d/m/Y'),
                  ]),
            'user_id' => $this->absentUser->id,
            'user_name' => $name,
            'slot'    => $this->slotLabel,
            'date'    => $this->date->toDateString(),
            'type'    => $this->type,
        ];
    }
}
