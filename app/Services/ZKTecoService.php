<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\BiometricEvent;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ZKTecoService
{
    private string $ip;
    private int $port;
    private int $timeout;

    public function __construct(?string $ip = null, ?int $port = null, ?int $timeout = null)
    {
        $this->ip = $ip
            ?: (\App\Models\Setting::get('zkteco_ip')
                ?: config('services.zkteco.ip', '192.168.0.201'));

        $this->port = $port
            ?: (int) (\App\Models\Setting::get('zkteco_port')
                ?: config('services.zkteco.port', 4370));

        $this->timeout = $timeout
            ?? (int) config('services.zkteco.timeout', 20);
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Retourne le Python utilisé pour communiquer avec ZKTeco.
     */
    private function getPythonBinary(): string
    {
        $configured = config(
            'services.zkteco.python_path',
            '/home/gutcenter/zkteco/.venv/bin/python3'
        );

        $candidates = [
            $configured,
            '/home/gutcenter/zkteco/.venv/bin/python3',
            '/home/gutcenter/zkteco/.venv/bin/python',
        ];

        foreach ($candidates as $binary) {
            if (!$binary) {
                continue;
            }

            if (!is_file($binary) || !is_executable($binary)) {
                continue;
            }

            $command = escapeshellarg($binary)
                . ' -c '
                . escapeshellarg('import zk; print("OK")')
                . ' 2>/dev/null';

            $output = trim((string) shell_exec($command));

            if ($output === 'OK') {
                return $binary;
            }
        }

        throw new RuntimeException(
            'Python avec pyzk introuvable. Vérifiez ZKTECO_PYTHON_PATH.'
        );
    }

    /**
     * Retourne le script Python ZKTeco.
     */
    private function getScriptPath(): string
    {
        $script = config(
            'services.zkteco.script_path',
            base_path('scripts/zk_manage.py')
        );

        if (!is_file($script)) {
            throw new RuntimeException(
                "Script ZKTeco introuvable : {$script}"
            );
        }

        if (!is_readable($script)) {
            throw new RuntimeException(
                "Script ZKTeco non lisible : {$script}"
            );
        }

        return $script;
    }

    /**
     * Exécute scripts/zk_manage.py et récupère son JSON.
     *
     * stdout = JSON
     * stderr = progression / diagnostic
     */
    private function runPythonCommand(
        array $arguments,
        ?int $timeoutSeconds = null
    ): array {
        $python = $this->getPythonBinary();
        $script = $this->getScriptPath();

        $timeoutSeconds ??= $this->timeout;

        $commandParts = [
            $python,
            $script,
        ];

        foreach ($arguments as $argument) {
            $commandParts[] = (string) $argument;
        }

        $command = implode(
            ' ',
            array_map(
                static fn ($value) => escapeshellarg((string) $value),
                $commandParts
            )
        );

        Log::debug('ZKTeco Python command', [
            'ip' => $this->ip,
            'port' => $this->port,
            'timeout' => $timeoutSeconds,
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            $command,
            $descriptors,
            $pipes,
            base_path()
        );

        if (!is_resource($process)) {
            throw new RuntimeException(
                'Impossible de démarrer le processus Python ZKTeco.'
            );
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';

        $startedAt = microtime(true);
        $timedOut = false;

        while (true) {
            $stdout .= (string) stream_get_contents($pipes[1]);
            $stderr .= (string) stream_get_contents($pipes[2]);

            $status = proc_get_status($process);

            if (!$status['running']) {
                break;
            }

            if ((microtime(true) - $startedAt) >= $timeoutSeconds) {
                $timedOut = true;

                proc_terminate($process, 15);
                usleep(500000);

                $status = proc_get_status($process);

                if ($status['running']) {
                    proc_terminate($process, 9);
                }

                break;
            }

            usleep(100000);
        }

        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($stderr !== '') {
            Log::debug('ZKTeco Python stderr', [
                'output' => trim($stderr),
            ]);
        }

        if ($timedOut) {
            Log::error('ZKTeco Python timeout', [
                'timeout' => $timeoutSeconds,
                'stderr' => trim($stderr),
            ]);

            return [
                'success' => false,
                'message' => "Timeout de communication avec le terminal ZKTeco après {$timeoutSeconds} secondes.",
                'stdout' => trim($stdout),
                'stderr' => trim($stderr),
                'exit_code' => $exitCode,
                'timeout' => true,
            ];
        }

        $json = json_decode(trim($stdout), true);

        if (!is_array($json)) {
            Log::error('Réponse JSON ZKTeco invalide', [
                'stdout' => trim($stdout),
                'stderr' => trim($stderr),
                'exit_code' => $exitCode,
            ]);

            return [
                'success' => false,
                'message' => 'Le script ZKTeco a retourné une réponse invalide.',
                'stdout' => trim($stdout),
                'stderr' => trim($stderr),
                'exit_code' => $exitCode,
            ];
        }

        $json['_exit_code'] = $exitCode;

        if ($stderr !== '') {
            $json['_stderr'] = trim($stderr);
        }

        return $json;
    }

    /**
     * Teste la connexion au terminal.
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->runPythonCommand([
                'test',
                '--ip',
                $this->ip,
                '--port',
                $this->port,
                '--timeout',
                $this->timeout,
            ], $this->timeout + 5);

            return ($result['success'] ?? false)
                && ($result['online'] ?? false);
        } catch (\Throwable $e) {
            Log::error('Erreur test connexion ZKTeco', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Test détaillé utilisé éventuellement par l'interface/API.
     */
    public function testConnectionDetailed(): array
    {
        try {
            return $this->runPythonCommand([
                'test',
                '--ip',
                $this->ip,
                '--port',
                $this->port,
                '--timeout',
                $this->timeout,
            ], $this->timeout + 5);
        } catch (\Throwable $e) {
            Log::error('Erreur test détaillé ZKTeco', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'online' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enregistre une empreinte.
     *
     * Le script Python gère le workaround nécessaire au firmware
     * du ZK3969 : UID temporaire -> transfert du template -> suppression.
     */
    public function enrollFingerprint(
        string $userId,
        string $name,
        int $fingerIndex = 0,
        int $timeout = 60
    ): array {
        try {
            $result = $this->runPythonCommand([
                'enroll',
                '--ip',
                $this->ip,
                '--port',
                $this->port,
                '--user-id',
                $userId,
                '--name',
                $name,
                '--finger-index',
                $fingerIndex,
                '--timeout',
                $timeout,
            ], $timeout + 40);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Erreur enrôlement empreinte ZKTeco', [
                'user_id' => $userId,
                'finger_index' => $fingerIndex,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'enrolled' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Supprime un utilisateur du terminal.
     *
     * Si l'utilisateur n'existe déjà plus sur le terminal,
     * le script Python retourne success=true, deleted=false.
     */
    public function deleteUser(string $userId): array
    {
        try {
            return $this->runPythonCommand([
                'delete',
                '--ip',
                $this->ip,
                '--port',
                $this->port,
                '--user-id',
                $userId,
                '--timeout',
                $this->timeout,
            ], $this->timeout + 10);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression utilisateur ZKTeco', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'deleted' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Récupère les pointages depuis le terminal.
     */
    public function getAttendanceLogs(): array
    {
        try {
            $result = $this->runPythonCommand([
                'attendance',
                '--ip',
                $this->ip,
                '--port',
                $this->port,
                '--timeout',
                $this->timeout,
            ], $this->timeout + 15);

            if (!($result['success'] ?? false)) {
                Log::error('Impossible de récupérer les pointages ZKTeco', [
                    'message' => $result['message'] ?? null,
                    'stderr' => $result['_stderr'] ?? null,
                ]);

                return [];
            }

            $records = $result['attendance'] ?? [];

            if (!is_array($records)) {
                return [];
            }

            return array_map(
                static function (array $record): array {
                    return [
                        'identifier' => (string) ($record['user_id'] ?? ''),
                        'timestamp' => $record['timestamp'] ?? null,
                        'status' => 'present',
                        'device_status' => $record['status'] ?? null,
                        'punch' => $record['punch'] ?? null,
                    ];
                },
                $records
            );
        } catch (\Throwable $e) {
            Log::error('Erreur récupération pointages ZKTeco', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Synchronise les pointages du terminal avec Laravel.
     *
     * Cette méthode est idempotente :
     * un même identifiant + timestamp ne crée pas plusieurs
     * BiometricEvent.
     */
    public function syncToDatabase(): array
    {
        $logs = $this->getAttendanceLogs();

        $result = [
            'success' => true,
            'total' => count($logs),
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($logs as $log) {
            try {
                $identifier = trim((string) ($log['identifier'] ?? ''));
                $timestamp = $log['timestamp'] ?? null;

                if ($identifier === '' || !$timestamp) {
                    $result['failed']++;

                    $result['errors'][] = [
                        'identifier' => $identifier,
                        'message' => 'Pointage incomplet.',
                    ];

                    continue;
                }

                $occurredAt = Carbon::parse($timestamp);

                /*
                 * Résolution Student.
                 */
                $student = Student::query()
                    ->where('student_number', $identifier)
                    ->first();

                /*
                 * Résolution User.
                 */
                $user = User::query()
                    ->where('professional_number', $identifier)
                    ->first();

                if (!$user && is_numeric($identifier)) {
                    $user = User::query()
                        ->where('id', (int) $identifier)
                        ->first();
                }

                if (!$student && !$user) {
                    $result['failed']++;

                    $result['errors'][] = [
                        'identifier' => $identifier,
                        'message' => 'Utilisateur/étudiant introuvable.',
                    ];

                    continue;
                }

                /*
                 * Vérification d'idempotence.
                 *
                 * On cherche un événement existant correspondant
                 * exactement au même identifiant et au même timestamp.
                 */
                $existingEvent = BiometricEvent::query()
                    ->where('external_identifier', $identifier)
                    ->where('occurred_at', $occurredAt)
                    ->first();

                if ($existingEvent) {
                    $result['skipped']++;
                    continue;
                }

                /*
                 * Création de l'événement biométrique.
                 */
                $event = BiometricEvent::create([
                    'device_identifier' => $this->ip,
                    'external_identifier' => $identifier,
                    'occurred_at' => $occurredAt,
                    'status' => 'pending',
                    'payload' => $log,
                ]);

                /*
                 * Type de pointage.
                 */
                $targetType = $student
                    ? Student::class
                    : User::class;

                $targetId = $student
                    ? $student->id
                    : $user->id;

                /*
                 * Session du jour.
                 *
                 * On reprend la logique générale existante :
                 * une session par date et type.
                 */
                $session = AttendanceSession::firstOrCreate(
                    [
                        'date' => $occurredAt->toDateString(),
                        'target_type' => $targetType,
                    ],
                    [
                        'created_by' => auth()->id()
                            ?: User::query()->value('id')
                            ?: 1,
                    ]
                );

                /*
                 * Création / mise à jour du pointage.
                 */
                $attendanceQuery = Attendance::query()
                    ->where('attendance_session_id', $session->id);

                if ($student) {
                    $attendanceQuery->where('student_id', $student->id);
                } else {
                    $attendanceQuery->where('user_id', $user->id);
                }

                $attendance = $attendanceQuery->first();

                if (!$attendance) {
                    $attendance = new Attendance();

                    $attendance->attendance_session_id = $session->id;

                    if ($student) {
                        $attendance->student_id = $student->id;
                    } else {
                        $attendance->user_id = $user->id;
                    }
                }

                /*
                 * On conserve le premier pointage de la journée
                 * comme heure d'arrivée.
                 */
                if (
                    empty($attendance->checked_at)
                    || $occurredAt->lt(Carbon::parse($attendance->checked_at))
                ) {
                    $attendance->checked_at = $occurredAt;
                }

                $attendance->status = 'present';
                $attendance->save();

                $event->status = 'processed';
                $event->processed_at = now();
                $event->save();

                $result['processed']++;
            } catch (\Throwable $e) {
                $result['failed']++;

                Log::error('Erreur synchronisation pointage ZKTeco', [
                    'log' => $log,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $result['errors'][] = [
                    'identifier' => $log['identifier'] ?? null,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    /**
     * Ancienne méthode conservée pour compatibilité avec le code existant.
     *
     * La communication réelle est désormais entièrement faite par Python/pyzk.
     */
    public function deleteUserFingerprint(
        string $userId,
        int $fingerIndex = 0
    ): bool {
        /*
         * Le script actuel gère la suppression complète de l'utilisateur.
         * Une suppression d'un seul doigt n'est pas exposée par zk_manage.py.
         *
         * On ne fait volontairement plus de socket PHP brut ici.
         */
        Log::warning('deleteUserFingerprint appelé, mais suppression individuelle du doigt non exposée par pyzk.', [
            'user_id' => $userId,
            'finger_index' => $fingerIndex,
        ]);

        return false;
    }
}
