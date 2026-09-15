<?php

use App\Jobs\CheckStaffAbsences;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check for staff absences every 15 minutes, only between 6am and 8pm
Schedule::job(new CheckStaffAbsences)->everyFifteenMinutes()->between('06:00', '20:00');
