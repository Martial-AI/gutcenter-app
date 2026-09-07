<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\BiometricEvent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BiometricPunchController extends Controller
{
    public function punch(Request $request): JsonResponse
    {
        $identifier = trim((string) $request->input('identifier', $request->input('user_id', $request->input('student_id'))));
        $deviceIdentifier = trim((string) $request->input('device_identifier', $request->input('device_id', 'Biometric-Terminal')));
        $occurredAt = $request->input('occurred_at', $request->input('timestamp')) ? Carbon::parse($request->input('occurred_at', $request->input('timestamp'))) : now();
        $statusOverride = $request->input('status', 'present');

        if (blank($identifier)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing identifier parameter (student_number or professional_number).',
            ], 422);
        }

        // 1. Create Biometric Event Record
        $event = BiometricEvent::create([
            'device_identifier' => $deviceIdentifier,
            'external_identifier' => $identifier,
            'occurred_at' => $occurredAt,
            'payload' => $request->all(),
            'status' => 'pending',
        ]);

        // 2. Lookup matching student or staff member
        $student = Student::with('enrollments.schoolClass')
            ->where('student_number', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        $user = null;
        if (! $student) {
            $user = User::where('professional_number', $identifier)
                ->orWhere('id', $identifier)
                ->orWhere('cin', $identifier)
                ->first();
        }

        if (! $student && ! $user) {
            $event->update([
                'status' => 'failed',
                'error_message' => 'Aucun élève ni membre du personnel trouvé pour cet identifiant : '.$identifier,
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No student or staff member found for identifier: '.$identifier,
            ], 404);
        }

        // 3. Find or Create Attendance Session for Today
        $today = $occurredAt->toDateString();
        if ($student) {
            $activeClassId = $student->enrollments->first()?->school_class_id;
            $session = AttendanceSession::firstOrCreate([
                'school_class_id' => $activeClassId,
                'attendance_date' => $today,
                'target_type' => 'student',
            ], [
                'created_by' => 1,
            ]);

            Attendance::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => $statusOverride,
                    'method' => 'biometric',
                    'checked_at' => $occurredAt,
                    'note' => 'Pointage biométrique auto via '.$deviceIdentifier,
                ]
            );

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pointage enregistre pour l’élève '.$student->first_name.' '.$student->last_name,
                'entity_type' => 'student',
                'student_id' => $student->id,
                'name' => $student->first_name.' '.$student->last_name,
                'student_number' => $student->student_number,
                'time' => $occurredAt->toIso8601String(),
            ]);
        } else {
            $session = AttendanceSession::firstOrCreate([
                'school_class_id' => null,
                'attendance_date' => $today,
                'target_type' => 'staff',
            ], [
                'created_by' => 1,
            ]);

            Attendance::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => $statusOverride,
                    'method' => 'biometric',
                    'checked_at' => $occurredAt,
                    'note' => 'Pointage biométrique auto via '.$deviceIdentifier,
                ]
            );

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pointage enregistre pour le personnel '.$user->name,
                'entity_type' => 'staff',
                'user_id' => $user->id,
                'name' => $user->name,
                'professional_number' => $user->professional_number,
                'time' => $occurredAt->toIso8601String(),
            ]);
        }
    }
}
