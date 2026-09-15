<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\StaffAbsentNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StaffAbsenceCheckerService
{
    /**
     * Run all absence checks for the given date (defaults to today).
     * Returns a summary array for logging or controller response.
     */
    public function check(Carbon $date = null, bool $force = false): array
    {
        $date = ($date ?? now())->startOfDay();
        $summary = ['permanent' => 0, 'non_permanent' => 0, 'skipped' => 0];

        // Only run on working days (Mon–Sat by default) unless forced
        if (! $force && $date->isSunday()) {
            return $summary;
        }

        // Fetch admin recipients
        $admins = User::permission('roles.manage')->where('is_active', true)->get();
        if ($admins->isEmpty()) {
            // Fallback to any active user with Admin role
            $admins = User::role('Admin')->where('is_active', true)->get();
        }

        if ($admins->isEmpty()) {
            return $summary;
        }

        $summary['permanent']     = $this->checkPermanentStaff($date, $admins, $force);
        $summary['non_permanent'] = $this->checkNonPermanentStaff($date, $admins, $force);

        return $summary;
    }

    // -----------------------------------------------------------------------
    // Permanent staff — checked against WorkSchedule slots
    // -----------------------------------------------------------------------

    private function checkPermanentStaff(Carbon $date, Collection $admins, bool $force = false): int
    {
        $slots = WorkSchedule::active()->forDay($date)->get();
        if ($slots->isEmpty()) {
            return 0;
        }

        $permanentStaff = User::where('employment_type', 'permanent')
            ->where('is_active', true)
            ->get();

        if ($permanentStaff->isEmpty()) {
            return 0;
        }

        // Fetch punches and attendances for the day
        $punches = $this->getDayPunches($date);
        $attendances = $this->getDayStaffAttendances($date);

        $alertCount = 0;
        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($slot->starts_at, $date->timezone)->setDateFrom($date);
            $slotEnd   = Carbon::parse($slot->ends_at, $date->timezone)->setDateFrom($date);

            // Alert once grace period (30 min) after slot start has elapsed, or if forced
            $triggerTime = $slotStart->copy()->addMinutes(30);

            if (! $force && now()->lessThan($triggerTime)) {
                continue; // too early to flag absence
            }

            $slotLabel = substr((string)$slot->starts_at, 0, 5) . ' – ' . substr((string)$slot->ends_at, 0, 5);

            foreach ($permanentStaff as $staff) {
                // Check if staff is on leave
                if ($this->isStaffOnLeave($staff, $date)) {
                    continue;
                }

                $cacheKey = "absence_notified.permanent.{$staff->id}.{$date->toDateString()}.slot_{$slot->id}";

                if (! $force && Cache::has($cacheKey)) {
                    continue; // already notified today
                }

                $hasPunch = $this->staffHasPunchInWindow(
                    $staff,
                    $punches,
                    $attendances,
                    $slotStart,
                    $slotEnd
                );

                if (! $hasPunch) {
                    $notification = new StaffAbsentNotification($staff, 'permanent', $slotLabel, $date);
                    $admins->each(fn ($admin) => $admin->notify($notification));

                    activity('absences')
                        ->performedOn($staff)
                        ->log("Absence constatée : {$staff->name} ({$slotLabel})");

                    Cache::put($cacheKey, true, now()->addHours(24));
                    $alertCount++;
                }
            }
        }

        return $alertCount;
    }

    // -----------------------------------------------------------------------
    // Non-permanent staff (teachers) — checked against ClassSchedule
    // -----------------------------------------------------------------------

    private function checkNonPermanentStaff(Carbon $date, Collection $admins, bool $force = false): int
    {
        $dow = $date->dayOfWeekIso - 1; // 0=Mon … 6=Sun

        $lessons = ClassSchedule::with('teacher', 'subject', 'schoolClass')
            ->where('day_of_week', $dow)
            ->whereHas('teacher', fn ($q) => $q->where('is_active', true)
                ->where('employment_type', 'non_permanent'))
            ->get();

        if ($lessons->isEmpty()) {
            return 0;
        }

        $punches = $this->getDayPunches($date);
        $attendances = $this->getDayStaffAttendances($date);
        $alertCount = 0;

        foreach ($lessons as $lesson) {
            $teacher = $lesson->teacher;
            if (! $teacher) {
                continue;
            }

            // Check if teacher is on leave
            if ($this->isStaffOnLeave($teacher, $date)) {
                continue;
            }

            $lessonStart = Carbon::parse($lesson->starts_at, $date->timezone)->setDateFrom($date);
            $lessonEnd   = Carbon::parse($lesson->ends_at, $date->timezone)->setDateFrom($date);

            // Alert 15 min after lesson starts if no punch recorded
            $triggerTime = $lessonStart->copy()->addMinutes(15);

            if (! $force && now()->lessThan($triggerTime)) {
                continue;
            }

            $cacheKey = "absence_notified.non_permanent.{$teacher->id}.{$date->toDateString()}.lesson_{$lesson->id}";

            if (! $force && Cache::has($cacheKey)) {
                continue;
            }

            $hasPunch = $this->staffHasPunchInWindow(
                $teacher,
                $punches,
                $attendances,
                $lessonStart,
                $lessonEnd
            );

            if (! $hasPunch) {
                $subjectName = $lesson->subject?->name ?? __('Course');
                $className   = $lesson->schoolClass?->name ? ' (' . $lesson->schoolClass->name . ')' : '';
                $slotLabel   = "{$subjectName}{$className} [" . substr((string)$lesson->starts_at, 0, 5) . ' – ' . substr((string)$lesson->ends_at, 0, 5) . ']';

                $notification = new StaffAbsentNotification($teacher, 'non_permanent', $slotLabel, $date);
                $admins->each(fn ($admin) => $admin->notify($notification));

                activity('absences')
                    ->performedOn($teacher)
                    ->log("Absence au cours : {$teacher->name} ({$slotLabel})");

                Cache::put($cacheKey, true, now()->addHours(24));
                $alertCount++;
            }
        }

        return $alertCount;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Check if the staff member is currently on approved leave on the given date.
     */
    private function isStaffOnLeave(User $user, Carbon $date): bool
    {
        if ($user->leave_start_date && $user->leave_end_date) {
            $start = Carbon::parse($user->leave_start_date)->startOfDay();
            $end   = Carbon::parse($user->leave_end_date)->endOfDay();
            if ($date->between($start, $end)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load all biometric punches for the given day from biometric_events.
     */
    private function getDayPunches(Carbon $date): Collection
    {
        return DB::table('biometric_events')
            ->whereDate('occurred_at', $date->toDateString())
            ->whereIn('status', ['processed', 'ok', null])
            ->select('external_identifier', 'occurred_at')
            ->get();
    }

    /**
     * Load all staff attendances recorded for today in attendances table.
     */
    private function getDayStaffAttendances(Carbon $date): Collection
    {
        return Attendance::whereHas('session', function ($q) use ($date) {
                $q->whereDate('attendance_date', $date->toDateString());
            })
            ->whereNotNull('user_id')
            ->whereIn('status', ['present', 'late'])
            ->get();
    }

    /**
     * Check whether a given user has at least one punch/attendance within the time window.
     */
    private function staffHasPunchInWindow(
        User       $user,
        Collection $punches,
        Collection $attendances,
        Carbon     $slotStart,
        Carbon     $slotEnd
    ): bool {
        // 1. Check Attendance records for this user
        $hasAttendance = $attendances->where('user_id', $user->id)->first(function ($att) use ($slotStart, $slotEnd): bool {
            if (! $att->checked_at) {
                return true; // Mark as present if recorded today
            }
            $time = Carbon::parse($att->checked_at);
            return $time->between($slotStart->copy()->subMinutes(30), $slotEnd);
        });

        if ($hasAttendance) {
            return true;
        }

        // 2. Check BiometricEvent records matching user identifiers
        $identifiers = array_filter([
            $user->professional_number,
            (string) $user->id,
            $user->cin,
        ]);

        if (empty($identifiers) || $punches->isEmpty()) {
            return false;
        }

        $windowStart = $slotStart->copy()->subMinutes(30);
        $windowEnd   = $slotEnd->copy();

        return $punches->contains(function ($punch) use ($identifiers, $windowStart, $windowEnd): bool {
            if (! in_array((string) $punch->external_identifier, $identifiers, true)) {
                return false;
            }
            $punchTime = Carbon::parse($punch->occurred_at);
            return $punchTime->between($windowStart, $windowEnd);
        });
    }
}
