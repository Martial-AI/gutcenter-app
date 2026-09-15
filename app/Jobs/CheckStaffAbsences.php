<?php

namespace App\Jobs;

use App\Services\StaffAbsenceCheckerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckStaffAbsences implements ShouldQueue
{
    use Queueable;

    public function handle(StaffAbsenceCheckerService $checker): void
    {
        $checker->check();
    }
}
