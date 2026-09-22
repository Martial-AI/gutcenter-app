<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Services\StaffAbsenceCheckerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->can('roles.manage'), 403);
    }

    public function index(): View|RedirectResponse
    {
        if (! auth()->user()?->can('roles.manage')) {
            return redirect()->back()->with(
                'permission_denied',
                __('You do not have permission to access schedules.')
            );
        }

        $schedules = WorkSchedule::orderBy('day_of_week')->orderBy('starts_at')->get();

        // Group by day_of_week for display (null = every day)
        $grouped = $schedules->groupBy(fn ($s) => $s->day_of_week ?? 'all');

        return view('work-schedules.index', compact('schedules', 'grouped'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:60'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'starts_at'   => ['required', 'date_format:H:i'],
            'ends_at'     => ['required', 'date_format:H:i', 'after:starts_at'],
            'is_active'   => ['boolean'],
        ]);

        $data['starts_at'] .= ':00';
        $data['ends_at']   .= ':00';
        $data['is_active']  = $request->boolean('is_active', true);

        WorkSchedule::create($data);

        return back()->with('success', __('Work schedule slot created.'));
    }

    public function update(Request $request, WorkSchedule $workSchedule): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:60'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'starts_at'   => ['required', 'date_format:H:i'],
            'ends_at'     => ['required', 'date_format:H:i', 'after:starts_at'],
            'is_active'   => ['boolean'],
        ]);

        $data['starts_at'] .= ':00';
        $data['ends_at']   .= ':00';
        $data['is_active']  = $request->boolean('is_active', true);

        $workSchedule->update($data);

        return back()->with('success', __('Work schedule slot updated.'));
    }

    public function toggle(WorkSchedule $workSchedule): JsonResponse
    {
        $this->authorizeAdmin();
        $workSchedule->update(['is_active' => ! $workSchedule->is_active]);
        return response()->json(['is_active' => $workSchedule->is_active]);
    }

    public function destroy(WorkSchedule $workSchedule): RedirectResponse
    {
        $this->authorizeAdmin();
        $workSchedule->delete();
        return back()->with('success', __('Work schedule slot deleted.'));
    }

    /**
     * Manually trigger the absence check and return a JSON summary.
     */
    public function runCheck(Request $request, StaffAbsenceCheckerService $checker): JsonResponse
    {
        $this->authorizeAdmin();
        $result = $checker->check(now(), $request->boolean('force', true));
        return response()->json([
            'message' => __('Absence check completed.'),
            'alerts'  => $result,
        ]);
    }
}
