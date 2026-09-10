<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BiometricEvent;
use App\Models\FingerprintRegistration;
use App\Models\Student;
use App\Models\User;
use App\Services\ZKTecoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricController extends Controller
{
    /**
     * Return enrollment data for students and staff to populate the modal.
     */
    public function enrollData(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $enrolledIdentifiers = FingerprintRegistration::pluck('identifier')->toArray();

        // Students list
        $students = Student::with(['enrollments.schoolClass'])
            ->whereNotNull('student_number')
            ->orderBy('first_name')
            ->get()
            ->map(function ($s) use ($enrolledIdentifiers) {
                $className = $s->enrollments->first()?->schoolClass?->name ?? 'Non assigné';
                return [
                    'id' => $s->id,
                    'type' => 'student',
                    'name' => trim($s->first_name . ' ' . $s->last_name),
                    'identifier' => $s->student_number,
                    'sub' => "Élève · Classe: {$className}",
                    'photo' => $s->photo_path ? asset('storage/' . $s->photo_path) : null,
                    'is_enrolled' => in_array($s->student_number, $enrolledIdentifiers),
                ];
            });

        // Staff / Users list
        $users = User::where('is_active', true)
            ->whereNotNull('professional_number')
            ->orderBy('name')
            ->get()
            ->map(function ($u) use ($enrolledIdentifiers) {
                $role = $u->localizedRoleLabel();
                return [
                    'id' => $u->id,
                    'type' => 'user',
                    'name' => $u->name,
                    'identifier' => $u->professional_number,
                    'sub' => "Personnel · {$role}",
                    'photo' => $u->photo_path ? asset('storage/' . $u->photo_path) : null,
                    'is_enrolled' => in_array($u->professional_number, $enrolledIdentifiers),
                ];
            });

        $zk = new ZKTecoService();

        return response()->json([
            'device_ip' => $zk->getIp(),
            'device_port' => $zk->getPort(),
            'students' => $students,
            'users' => $users,
            'total_enrolled' => count($enrolledIdentifiers),
        ]);
    }

    /**
     * Start fingerprint enrollment for a student or staff member.
     */
    public function enroll(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $validated = $request->validate([
            'entity_type' => 'required|in:student,user',
            'entity_id' => 'required|integer',
            'finger_index' => 'nullable|integer|between:0,9',
        ]);

        $entityType = $validated['entity_type'];
        $entityId = (int) $validated['entity_id'];
        $fingerIndex = (int) ($validated['finger_index'] ?? 0);

        if ($entityType === 'student') {
            $person = Student::findOrFail($entityId);
            $identifier = $person->student_number;
            $name = trim($person->first_name . ' ' . $person->last_name);
        } else {
            $person = User::findOrFail($entityId);
            $identifier = $person->professional_number;
            $name = $person->name;
        }

        if (blank($identifier)) {
            return response()->json([
                'success' => false,
                'message' => "Cette personne ne possède pas d'identifiant valide (numéro d'étudiant ou professionnel manquant).",
            ], 422);
        }

        // Check if already registered
        $existing = FingerprintRegistration::where('identifier', $identifier)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'is_already_enrolled' => true,
                'message' => "L'identifiant {$identifier} ({$name}) est déjà enregistré dans le système biométrique.",
            ], 422);
        }

        $zk = new ZKTecoService();
        $deviceResult = $zk->enrollFingerprint($identifier, $fingerIndex);

        // Record registration in DB
        $registration = FingerprintRegistration::updateOrCreate(
            ['identifier' => $identifier],
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'name' => $name,
                'finger_index' => $fingerIndex,
                'device_ip' => $zk->getIp(),
                'enrolled_by' => auth()->id(),
                'enrolled_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'identifier' => $identifier,
            'name' => $name,
            'device_ip' => $zk->getIp(),
            'device_message' => $deviceResult['message'] ?? '',
            'message' => "Empreinte biométrique associée avec succès à {$name} ({$identifier}). Le capteur a été activé pour l'enregistrement.",
        ]);
    }

    /**
     * Delete an enrolled fingerprint.
     */
    public function destroy(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $identifier = $request->input('identifier');
        if (blank($identifier)) {
            return response()->json(['success' => false, 'message' => 'Identifiant requis.'], 422);
        }

        $reg = FingerprintRegistration::where('identifier', $identifier)->first();

        // Attempt device deletion
        $zk = new ZKTecoService();
        $deviceResult = $zk->deleteUser($identifier);

        if ($reg) {
            $reg->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "L'empreinte pour l'identifiant {$identifier} a été retirée du système et du pointeur.",
            'device_message' => $deviceResult['message'] ?? null,
        ]);
    }

    /**
     * Fetch all pointages grouped by date and sorted by exact timestamp descending.
     */
    public function pointages(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('attendance.view') || auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $dateFilter = $request->input('date'); // optional YYYY-MM-DD
        $search = $request->input('search');

        // 1. Fetch from BiometricEvent
        $queryEvents = BiometricEvent::query();
        if ($dateFilter) {
            $queryEvents->whereDate('occurred_at', $dateFilter);
        }
        $events = $queryEvents->latest('occurred_at')->limit(300)->get();

        // 2. Fetch from Attendance with biometric or checked_at
        $queryAttendance = Attendance::with(['student.enrollments.schoolClass', 'user', 'session'])
            ->whereNotNull('checked_at');
        if ($dateFilter) {
            $queryAttendance->whereDate('checked_at', $dateFilter);
        }
        $attendances = $queryAttendance->latest('checked_at')->limit(300)->get();

        // Map students and users for quick lookup
        $students = Student::with('enrollments.schoolClass')->get()->keyBy('student_number');
        $users = User::all()->keyBy('professional_number');

        $pointagesList = collect();

        // Add biometric events
        foreach ($events as $event) {
            $identifier = $event->external_identifier;
            $student = $students->get($identifier);
            $user = ! $student ? $users->get($identifier) : null;

            $name = $student 
                ? trim($student->first_name . ' ' . $student->last_name) 
                : ($user ? $user->name : "ID Inconnu ({$identifier})");

            $typeLabel = $student 
                ? 'Élève (' . ($student->enrollments->first()?->schoolClass?->name ?? 'Classe') . ')'
                : ($user ? 'Personnel (' . $user->localizedRoleLabel() . ')' : 'Inconnu');

            $pointagesList->push([
                'id' => 'evt_' . $event->id,
                'identifier' => $identifier,
                'name' => $name,
                'type' => $student ? 'student' : ($user ? 'staff' : 'unknown'),
                'type_label' => $typeLabel,
                'occurred_at' => $event->occurred_at ? $event->occurred_at->format('Y-m-d H:i:s') : '—',
                'date' => $event->occurred_at ? $event->occurred_at->format('Y-m-d') : 'Date inconnue',
                'time' => $event->occurred_at ? $event->occurred_at->format('H:i:s') : '—',
                'date_human' => $event->occurred_at ? $event->occurred_at->translatedFormat('l d F Y') : '—',
                'device' => $event->device_identifier ?? 'Pointeuse ZKTeco',
                'status' => 'present',
                'status_label' => 'Pointé (Présent)',
                'method' => 'biometric',
            ]);
        }

        // Also add attendance records that have checked_at to catch all pointages
        foreach ($attendances as $att) {
            $student = $att->student;
            $user = $att->user;
            $identifier = $student?->student_number ?? ($user?->professional_number ?? ('ID-' . ($student?->id ?? $user?->id)));
            $occurredAt = $att->checked_at;
            if (! $occurredAt) continue;

            $key = ($student ? 'std_' . $student->id : 'usr_' . $user?->id) . '_' . $occurredAt->format('Y-m-d_H:i');
            // Avoid exact duplicate within same minute
            $alreadyExists = $pointagesList->contains(function ($item) use ($identifier, $occurredAt) {
                return $item['identifier'] === $identifier && substr($item['occurred_at'], 0, 16) === $occurredAt->format('Y-m-d H:i');
            });

            if (! $alreadyExists) {
                $name = $student 
                    ? trim($student->first_name . ' ' . $student->last_name) 
                    : ($user ? $user->name : 'Utilisateur');

                $typeLabel = $student 
                    ? 'Élève (' . ($student->enrollments->first()?->schoolClass?->name ?? 'Classe') . ')'
                    : ($user ? 'Personnel (' . $user->localizedRoleLabel() . ')' : 'Personnel');

                $pointagesList->push([
                    'id' => 'att_' . $att->id,
                    'identifier' => $identifier,
                    'name' => $name,
                    'type' => $student ? 'student' : 'staff',
                    'type_label' => $typeLabel,
                    'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
                    'date' => $occurredAt->format('Y-m-d'),
                    'time' => $occurredAt->format('H:i:s'),
                    'date_human' => $occurredAt->translatedFormat('l d F Y'),
                    'device' => $att->note ?: 'Pointeuse ZKTeco',
                    'status' => $att->status,
                    'status_label' => match($att->status) {
                        'present' => 'Présent',
                        'late' => 'En retard',
                        'absent' => 'Absent',
                        'excused' => 'Excusé',
                        default => $att->status,
                    },
                    'method' => $att->method === 'biometric' ? 'biometric' : 'manual',
                ]);
            }
        }

        // Apply search filter if provided
        if (! blank($search)) {
            $searchLower = mb_strtolower($search);
            $pointagesList = $pointagesList->filter(function ($item) use ($searchLower) {
                return str_contains(mb_strtolower($item['name']), $searchLower)
                    || str_contains(mb_strtolower($item['identifier']), $searchLower)
                    || str_contains(mb_strtolower($item['type_label']), $searchLower);
            });
        }

        // Sort by occurred_at descending
        $sorted = $pointagesList->sortByDesc('occurred_at')->values();

        // Group by date
        $grouped = $sorted->groupBy('date')->map(function ($items, $date) {
            $carbonDate = Carbon::parse($date);
            return [
                'date' => $date,
                'date_human' => $carbonDate->translatedFormat('l d F Y'),
                'total' => $items->count(),
                'records' => $items->values(),
            ];
        })->values();

        return response()->json([
            'total_count' => $sorted->count(),
            'grouped' => $grouped,
        ]);
    }

    /**
     * Test connection to ZKTeco terminal.
     */
    public function testConnection(): JsonResponse
    {
        abort_unless(auth()->user()?->can('attendance.manage') || auth()->user()?->can('roles.manage'), 403);

        $zk = new ZKTecoService();
        $isOnline = $zk->testConnection();

        return response()->json([
            'online' => $isOnline,
            'ip' => $zk->getIp(),
            'port' => $zk->getPort(),
            'message' => $isOnline 
                ? "Connexion réussie avec la pointeuse à {$zk->getIp()}:{$zk->getPort()} !"
                : "Impossible de joindre la pointeuse à {$zk->getIp()}:{$zk->getPort()}. Vérifiez le câble réseau ou l'adresse IP.",
        ]);
    }
}
