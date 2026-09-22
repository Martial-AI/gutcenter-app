<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\BiometricEvent;
use App\Models\Expense;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentMonthlyFee;
use App\Models\Subject;
use App\Models\User;
use App\Services\SensitiveActivityNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('attendance.view'), 403);

        $user = auth()->user();
        $isTeacher = $user->hasRole('Prof');

        if ($isTeacher) {
            $teacherAssignments = $user->teacherAssignments()->with(['schoolClass', 'subject'])->get();
            $assignedClassIds = $teacherAssignments->pluck('school_class_id')->unique()->filter()->all();
            $assignedSubjectIds = $teacherAssignments->pluck('subject_id')->unique()->filter()->all();

            $classes = SchoolClass::whereIn('id', $assignedClassIds)->orderBy('name')->get();
            $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
            if ($subjects->isEmpty()) {
                $subjects = Subject::where('is_active', true)->orderBy('name')->get();
            }

            $selectedClassId = $request->input('class_id') ? (int) $request->input('class_id') : ($classes->first()?->id ?? null);
            if ($selectedClassId && ! in_array($selectedClassId, $assignedClassIds, true)) {
                $selectedClassId = $classes->first()?->id ?? null;
            }

            $selectedSubjectId = $request->input('subject_id') ? (int) $request->input('subject_id') : null;
            $attendanceDate = $request->input('attendance_date') ?: ($request->input('date') ?: now()->toDateString());

            $students = collect();
            if ($selectedClassId) {
                $students = Student::whereHas('enrollments', function ($q) use ($selectedClassId) {
                    $q->where('school_class_id', $selectedClassId)->where('status', 'active');
                })->orderBy('last_name')->orderBy('first_name')->get();
            }

            $sessionQuery = AttendanceSession::where('school_class_id', $selectedClassId)
                ->where('attendance_date', $attendanceDate);
            if ($selectedSubjectId) {
                $sessionQuery->where('subject_id', $selectedSubjectId);
            }
            $session = $sessionQuery->first();

            $attendancesMap = $session ? $session->attendances()->whereNotNull('student_id')->get()->keyBy('student_id') : collect();

            $teacherStudentsList = $students->map(function ($student) use ($attendancesMap) {
                $att = $attendancesMap->get($student->id);
                return [
                    'id' => $student->id,
                    'name' => $student->first_name.' '.$student->last_name,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'student_number' => $student->student_number,
                    'photo_url' => $student->photo_path ? route('students.photo', $student) : null,
                    'status' => $att ? $att->status : null,
                    'effective_status' => $att ? $att->status : 'unrecorded',
                    'note' => $att?->note,
                    'checked_at' => $att?->checked_at,
                ];
            });

            $presentCount = $teacherStudentsList->where('status', 'present')->count();
            $absentCount = $teacherStudentsList->where('status', 'absent')->count();
            $lateCount = $teacherStudentsList->where('status', 'late')->count();
            $unrecordedCount = $teacherStudentsList->whereNull('status')->count();
            $totalCount = $teacherStudentsList->count();

            return view('attendance.teacher', compact(
                'isTeacher',
                'classes',
                'subjects',
                'selectedClassId',
                'selectedSubjectId',
                'attendanceDate',
                'session',
                'teacherStudentsList',
                'presentCount',
                'absentCount',
                'lateCount',
                'unrecordedCount',
                'totalCount'
            ));
        }

        $selectedClassId = $request->input('class_id') ? (int) $request->input('class_id') : null;
        $dateFrom = $request->input('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
        $dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

        // Base attendance queries
        $studentAttendanceQuery = Attendance::whereNotNull('student_id')
            ->whereHas('session', function ($q) use ($selectedClassId, $dateFrom, $dateTo) {
                if ($selectedClassId) $q->where('school_class_id', $selectedClassId);
                if ($dateFrom) $q->where('attendance_date', '>=', $dateFrom->toDateString());
                if ($dateTo) $q->where('attendance_date', '<=', $dateTo->toDateString());
            });

        $staffAttendanceQuery = Attendance::whereNotNull('user_id')
            ->whereHas('session', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('attendance_date', '>=', $dateFrom->toDateString());
                if ($dateTo) $q->where('attendance_date', '<=', $dateTo->toDateString());
            });

        $totalStudentRecords = (clone $studentAttendanceQuery)->count();
        $studentAbsencesCount = (clone $studentAttendanceQuery)->where('status', 'absent')->count();
        $studentPresentsCount = (clone $studentAttendanceQuery)->whereIn('status', ['present', 'late'])->count();
        $studentAbsenceRate = $totalStudentRecords > 0 ? round(($studentAbsencesCount / $totalStudentRecords) * 100, 1) : 0.0;
        $studentPresenceRate = $totalStudentRecords > 0 ? round(($studentPresentsCount / $totalStudentRecords) * 100, 1) : 100.0;

        $totalStaffRecords = (clone $staffAttendanceQuery)->count();
        $staffAbsencesCount = (clone $staffAttendanceQuery)->where('status', 'absent')->count();
        $staffPresentsCount = (clone $staffAttendanceQuery)->whereIn('status', ['present', 'late'])->count();
        $staffAbsenceRate = $totalStaffRecords > 0 ? round(($staffAbsencesCount / $totalStaffRecords) * 100, 1) : 0.0;
        $staffPresenceRate = $totalStaffRecords > 0 ? round(($staffPresentsCount / $totalStaffRecords) * 100, 1) : 100.0;

        // Class breakdown stats
        $classes = SchoolClass::withCount(['enrollments' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('name')->get();

        $classStats = $classes->map(function ($class) use ($dateFrom, $dateTo) {
            $total = Attendance::whereNotNull('student_id')
                ->whereHas('session', fn ($q) => $q->where('school_class_id', $class->id)
                    ->when($dateFrom, fn ($q2) => $q2->where('attendance_date', '>=', $dateFrom->toDateString()))
                    ->when($dateTo, fn ($q2) => $q2->where('attendance_date', '<=', $dateTo->toDateString()))
                )->count();

            $absences = Attendance::whereNotNull('student_id')
                ->where('status', 'absent')
                ->whereHas('session', fn ($q) => $q->where('school_class_id', $class->id)
                    ->when($dateFrom, fn ($q2) => $q2->where('attendance_date', '>=', $dateFrom->toDateString()))
                    ->when($dateTo, fn ($q2) => $q2->where('attendance_date', '<=', $dateTo->toDateString()))
                )->count();

            $rate = $total > 0 ? round(($absences / $total) * 100, 1) : 0.0;

            return [
                'id' => $class->id,
                'name' => $class->name,
                'total' => $total,
                'absences' => $absences,
                'absence_rate' => $rate,
                'presence_rate' => $total > 0 ? 100 - $rate : 100.0,
            ];
        });

        // Flagged Students: 3 or more absences
        $flaggedStudentsRaw = Attendance::whereNotNull('student_id')
            ->where('status', 'absent')
            ->selectRaw('student_id, COUNT(*) as total_absences')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= 3')
            ->get();

        $flaggedStudentIds = $flaggedStudentsRaw->pluck('student_id')->all();
        $flaggedStudentsMap = $flaggedStudentsRaw->pluck('total_absences', 'student_id');

        $flaggedStudents = Student::with(['enrollments.schoolClass'])
            ->whereIn('id', $flaggedStudentIds)
            ->get()
            ->map(function ($student) use ($flaggedStudentsMap) {
                $absences = Attendance::where('student_id', $student->id)
                    ->where('status', 'absent')
                    ->with(['session.schoolClass', 'session.subject'])
                    ->latest()
                    ->get();

                return [
                    'student' => $student,
                    'total_absences' => $flaggedStudentsMap[$student->id] ?? 3,
                    'class_name' => $student->enrollments->first()?->schoolClass?->name ?? '—',
                    'recent_absences' => $absences,
                ];
            })
            ->sortByDesc('total_absences')
            ->values();

        // Flagged Staff/Profs: Absent staff & teachers
        $absentStaffRecords = Attendance::whereNotNull('user_id')
            ->where('status', 'absent')
            ->with(['user', 'session.subject'])
            ->latest()
            ->get();

        $flaggedStaff = $absentStaffRecords->groupBy('user_id')->map(function ($records) {
            $user = $records->first()->user;
            return [
                'user' => $user,
                'total_absences' => $records->count(),
                'recent_absences' => $records,
            ];
        })->values();

        // Student List with Absence Summaries
        $studentQuery = Student::with(['enrollments.schoolClass']);
        if ($selectedClassId) {
            $studentQuery->whereHas('enrollments', fn ($q) => $q->where('school_class_id', $selectedClassId)->where('status', 'active'));
        }
        $studentsList = $studentQuery->orderBy('last_name')->get()->map(function ($student) use ($dateFrom, $dateTo) {
            $totalSess = Attendance::where('student_id', $student->id)
                ->when($dateFrom, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '>=', $dateFrom->toDateString())))
                ->when($dateTo, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '<=', $dateTo->toDateString())))
                ->count();

            $absences = Attendance::where('student_id', $student->id)
                ->where('status', 'absent')
                ->when($dateFrom, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '>=', $dateFrom->toDateString())))
                ->when($dateTo, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '<=', $dateTo->toDateString())))
                ->count();

            return [
                'id' => $student->id,
                'name' => $student->first_name.' '.$student->last_name,
                'student_number' => $student->student_number,
                'photo_url' => $student->photo_path ? route('students.photo', $student) : null,
                'class_name' => $student->enrollments->first()?->schoolClass?->name ?? '—',
                'total_sessions' => $totalSess,
                'absences' => $absences,
                'absence_rate' => $totalSess > 0 ? round(($absences / $totalSess) * 100, 1) : 0.0,
                'is_flagged' => $absences >= 3,
            ];
        });

        // Staff List with Absence Summaries
        $staffList = User::role(['Prof', 'Secrétaire', 'Trésorier', 'Directeur General'])->orderBy('name')->get()->map(function ($user) use ($dateFrom, $dateTo) {
            $totalSess = Attendance::where('user_id', $user->id)
                ->when($dateFrom, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '>=', $dateFrom->toDateString())))
                ->when($dateTo, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '<=', $dateTo->toDateString())))
                ->count();

            $absences = Attendance::where('user_id', $user->id)
                ->where('status', 'absent')
                ->when($dateFrom, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '>=', $dateFrom->toDateString())))
                ->when($dateTo, fn ($q) => $q->whereHas('session', fn ($s) => $s->where('attendance_date', '<=', $dateTo->toDateString())))
                ->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'professional_number' => $user->professional_number ?? '—',
                'role' => $user->getRoleNames()->first() ?? 'Personnel',
                'total_sessions' => $totalSess,
                'absences' => $absences,
                'absence_rate' => $totalSess > 0 ? round(($absences / $totalSess) * 100, 1) : 0.0,
                'is_flagged' => $absences > 0,
            ];
        });

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $studentCount = Student::count();
        $teacherCount = User::role('Prof')->count();
        $biometricLogs = collect(); // Placeholder; populated via biometric terminal integration if available


        return view('attendance.index', compact(
            'isTeacher',
            'studentAbsenceRate',
            'studentPresenceRate',
            'staffAbsenceRate',
            'staffPresenceRate',
            'studentAbsencesCount',
            'staffAbsencesCount',
            'classStats',
            'flaggedStudents',
            'flaggedStaff',
            'studentsList',
            'staffList',
            'classes',
            'subjects',
            'selectedClassId',
            'biometricLogs',
            'studentCount',
            'teacherCount'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'attendance_time' => ['nullable', 'string'],
            'target_type' => ['required', 'in:student,staff'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'attendances' => ['required', 'array'],
            'attendances.*.id' => ['required', 'integer'],
            'attendances.*.status' => ['required', 'in:present,absent,late,excused'],
            'attendances.*.time' => ['nullable', 'string'],
            'attendances.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $defaultTime = !empty($data['attendance_time']) ? $data['attendance_time'] : now()->format('H:i');

        $session = AttendanceSession::updateOrCreate(
            [
                'school_class_id' => $data['school_class_id'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'attendance_date' => $data['attendance_date'],
                'target_type' => $data['target_type'],
            ],
            [
                'created_by' => auth()->id(),
            ]
        );

        foreach ($data['attendances'] as $item) {
            $isStudent = $data['target_type'] === 'student';
            
            $itemTime = !empty($item['time']) ? $item['time'] : $defaultTime;
            try {
                $checkedAt = Carbon::parse($data['attendance_date'].' '.$itemTime);
            } catch (\Throwable $e) {
                $checkedAt = now();
            }

            $attendance = Attendance::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $isStudent ? $item['id'] : null,
                    'user_id' => ! $isStudent ? $item['id'] : null,
                ],
                [
                    'status' => $item['status'],
                    'method' => 'manual',
                    'checked_at' => $checkedAt,
                    'recorded_by' => auth()->id(),
                    'note' => $item['note'] ?? null,
                ]
            );

            // Trigger alerts for staff absences
            if (! $isStudent && $item['status'] === 'absent') {
                $staff = User::find($item['id']);
                if ($staff) {
                    SensitiveActivityNotifier::send(
                        'Professeur/Personnel absent',
                        'Le membre du personnel '.$staff->name.' a été marqué absent le '.$data['attendance_date'].' à '.$itemTime.'.'
                    );
                }
            }

            // Trigger alerts for 3+ student absences
            if ($isStudent && $item['status'] === 'absent') {
                $student = Student::find($item['id']);
                if ($student) {
                    $totalAbsences = Attendance::where('student_id', $student->id)->where('status', 'absent')->count();
                    if ($totalAbsences >= 3) {
                        SensitiveActivityNotifier::send(
                            'Alerte : Élève à suivre',
                            'L’élève '.$student->first_name.' '.$student->last_name.' a désormais '.$totalAbsences.' absences.'
                        );
                    }
                }
            }
        }

        return back()->with('success', __('Attendance session saved successfully.'));
    }

    public function details(string $type, int $id): JsonResponse
    {
        abort_unless(auth()->user()?->can('statistics.view') || auth()->user()?->can('attendance.view'), 403);

        if ($type === 'student') {
            $person = Student::with('enrollments.schoolClass')->findOrFail($id);
            $name = $person->first_name.' '.$person->last_name;
            $subInfo = $person->enrollments->first()?->schoolClass?->name ?? 'Élève';
            $records = Attendance::where('student_id', $id)
                ->with(['session.schoolClass', 'session.subject', 'recordedBy'])
                ->latest()
                ->get();
        } else {
            $person = User::findOrFail($id);
            $name = $person->name;
            $subInfo = $person->getRoleNames()->first() ?? 'Personnel';
            $records = Attendance::where('user_id', $id)
                ->with(['session.schoolClass', 'session.subject', 'recordedBy'])
                ->latest()
                ->get();
        }

        $formatted = $records->map(function ($rec) {
            $dateFormatted = $rec->session?->attendance_date?->format('d/m/Y') ?? $rec->created_at->format('d/m/Y');
            $timeFormatted = $rec->checked_at ? $rec->checked_at->format('H:i') : ($rec->created_at ? $rec->created_at->format('H:i') : null);

            return [
                'id' => $rec->id,
                'date' => $dateFormatted,
                'time' => $timeFormatted ?: '—',
                'class_name' => $rec->session?->schoolClass?->name ?? '—',
                'subject_name' => $rec->session?->subject?->name ?? 'Absence Générale / Journée',
                'status' => $rec->status,
                'status_label' => match($rec->status) {
                    'present' => 'Présent',
                    'absent' => 'Absent',
                    'late' => 'En retard',
                    'excused' => 'Excusé',
                    default => $rec->status,
                },
                'method' => $rec->method === 'biometric' ? 'Biométrique' : 'Manuel',
                'note' => $rec->note ?? '—',
                'recorded_by' => $rec->recordedBy?->name ?? 'Système',
            ];
        });

        $totalAbsences = $records->where('status', 'absent')->count();
        $totalLate = $records->where('status', 'late')->count();
        $totalPresent = $records->where('status', 'present')->count();

        return response()->json([
            'person_name' => $name,
            'person_sub' => $subInfo,
            'total_absences' => $totalAbsences,
            'total_late' => $totalLate,
            'total_present' => $totalPresent,
            'records' => $formatted,
        ]);
    }

    public function syncDevice(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $ip = $request->input('ip', config('services.zkteco.ip', '192.168.0.201'));
        $zk = new \App\Services\ZKTecoService($ip);
        $result = $zk->syncToDatabase('ZKTeco-ZK3969');

        $isSuccess = ($result['processed'] ?? 0) > 0;
        $message = $isSuccess 
            ? "Synchronisation réussie : {$result['processed']} pointage(s) enregistré(s)."
            : "Synchronisation terminée avec {$ip}. Total pointages : " . ($result['total'] ?? 0) . ".";

        if ($request->wantsJson() || $request->ajax()) {
            session()->flash($isSuccess ? 'success' : 'info', $message);
            return response()->json([
                'success' => true,
                'is_success' => $isSuccess,
                'message' => $message,
                'processed' => $result['processed'] ?? 0,
                'total' => $result['total'] ?? 0,
            ]);
        }

        if ($isSuccess) {
            return back()->with('success', $message);
        }

        return back()->with('info', $message);
    }
}

