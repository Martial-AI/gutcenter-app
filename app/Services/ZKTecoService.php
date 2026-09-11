<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\BiometricEvent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ZKTecoService
{
    protected string $ip;
    protected int $port;
    protected int $timeout;
    protected $socket = null;
    protected int $sessionId = 0;
    protected int $replyId = 0;

    const CMD_CONNECT = 1000;
    const CMD_EXIT = 1001;
    const CMD_ENABLEDEVICE = 1002;
    const CMD_DISABLEDEVICE = 1003;
    const CMD_RESTART = 1004;
    const CMD_POWEROFF = 1005;
    const CMD_USER_RRQ = 8;
    const CMD_USERTEMP_RRQ = 9;
    const CMD_DELETE_USER = 18;
    const CMD_STARTENROLL = 61;
    const CMD_USER_WRQ = 72;
    const CMD_DELETE_USER_TEMP = 134;
    const CMD_GET_TIME = 201;
    const CMD_SET_TIME = 202;
    const CMD_ATTLOG_RRQ = 500;
    const CMD_CLEAR_DATA = 501;
    const CMD_CLEAR_ATTLOG = 503;

    const CMD_ACK_OK = 2000;
    const CMD_ACK_ERROR = 2001;
    const CMD_ACK_DATA = 2002;
    const CMD_PREPARE_DATA = 1500;
    const CMD_DATA = 1501;

    public function __construct(?string $ip = null, ?int $port = null, ?int $timeout = null)
    {
        $this->ip = $ip 
            ?: (\App\Models\Setting::get('zkteco_ip') 
                ?: config('services.zkteco.ip', '192.168.0.201'));
        $this->port = $port 
            ?: (int) (\App\Models\Setting::get('zkteco_port') 
                ?: config('services.zkteco.port', 4370));
        $this->timeout = $timeout ?? (int) config('services.zkteco.timeout', 5);
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function testConnection(): bool
    {
        if ($this->connect()) {
            $this->disconnect();
            return true;
        }
        return false;
    }

    public function getPythonBinary(): string
    {
        $custom = \App\Models\Setting::get('zkteco_python_path')
            ?: config('services.zkteco.python_path');

        if ($custom && is_executable($custom)) {
            return $custom;
        }

        $candidates = PHP_OS_FAMILY === 'Windows'
            ? [
                base_path('venv\\Scripts\\python.exe'),
                base_path('.venv\\Scripts\\python.exe'),
                'python',
                'py',
                'C:\\Python314\\python.exe',
                'C:\\Python311\\python.exe',
                'C:\\Python310\\python.exe',
            ]
            : [
                base_path('venv/bin/python'),
                base_path('venv/bin/python3'),
                base_path('.venv/bin/python'),
                '/usr/bin/python3',
                '/usr/local/bin/python3',
                'python3',
                'python',
            ];

        // Find any working Python binary (no external lib needed anymore)
        foreach ($candidates as $bin) {
            $check = @shell_exec(escapeshellcmd($bin) . ' --version 2>&1');
            if ($check && str_contains(strtolower($check), 'python')) {
                return $bin;
            }
        }

        return PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
    }

    public function enrollFingerprint(string $userId, string $name = '', int $fingerId = 0, int $timeout = 60): array
    {
        $cleanId = trim($userId);
        $cleanName = trim($name ?: $cleanId);
        $python = $this->getPythonBinary();
        $scriptPath = base_path('scripts/zk_manage.py');

        if (! file_exists($scriptPath)) {
            return [
                'success' => false,
                'message' => "Script de pont ZKTeco introuvable à {$scriptPath}.",
            ];
        }

        $command = sprintf(
            '%s %s enroll --ip %s --port %d --user-id %s --name %s --finger-index %d --timeout %d',
            $python,
            escapeshellarg($scriptPath),
            escapeshellarg($this->ip),
            $this->port,
            escapeshellarg($cleanId),
            escapeshellarg($cleanName),
            $fingerId,
            $timeout
        );

        Log::info("ZKTeco executing enroll command: {$command}");

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptors, $pipes, base_path());

        if (! is_resource($process)) {
            return [
                'success' => false,
                'message' => "Impossible de lancer le processus d'enrôlement Python.",
            ];
        }

        fclose($pipes[0]);

        $maxWaitSeconds = $timeout + 15;
        $startTime = time();
        $stdout = '';
        $stderr = '';

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (true) {
            $r = [$pipes[1], $pipes[2]];
            $w = null;
            $e = null;
            $num = @stream_select($r, $w, $e, 1);

            if ($num > 0) {
                foreach ($r as $pipe) {
                    if ($pipe === $pipes[1]) {
                        $stdout .= fread($pipe, 4096);
                    } elseif ($pipe === $pipes[2]) {
                        $stderr .= fread($pipe, 4096);
                    }
                }
            }

            $status = proc_get_status($process);
            if (! $status['running']) {
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);
                break;
            }

            if ((time() - $startTime) > $maxWaitSeconds) {
                proc_terminate($process, 9);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return [
                    'success' => false,
                    'message' => "Délai d'enrôlement dépassé ({$timeout}s). Le doigt n'a pas été posé à temps sur le pointeur.",
                ];
            }

            usleep(100000); // 100ms
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        $output = trim($stdout);
        $result = json_decode($output, true);

        if (! is_array($result)) {
            Log::error("ZKTeco enroll raw output: {$output} | Stderr: {$stderr}");
            return [
                'success' => false,
                'message' => "Réponse inattendue du script d'enrôlement : " . ($output ?: $stderr ?: 'Aucune sortie.'),
            ];
        }

        return $result;
    }

    public function deleteUser(string $userId): array
    {
        $cleanId = trim($userId);
        $python = $this->getPythonBinary();
        $scriptPath = base_path('scripts/zk_manage.py');

        if (file_exists($scriptPath)) {
            $command = sprintf(
                '%s %s delete --ip %s --port %d --user-id %s',
                $python,
                escapeshellarg($scriptPath),
                escapeshellarg($this->ip),
                $this->port,
                escapeshellarg($cleanId)
            );

            $output = @shell_exec($command);
            $result = json_decode(trim($output), true);
            if (is_array($result)) {
                return $result;
            }
        }

        return $this->deleteUserSocket($userId);
    }

    public function deleteUserSocket(string $userId): array
    {
        if (! $this->socket && ! $this->connect()) {
            return [
                'success' => false,
                'message' => "Impossible de se connecter au pointeur ({$this->ip}).",
            ];
        }

        try {
            $this->sendCommand(self::CMD_DISABLEDEVICE);

            $cleanId = trim($userId);
            // CMD_DELETE_USER = 18
            $commandString = pack('a24', $cleanId);
            $buf = $this->createHeader(self::CMD_DELETE_USER, 0, $this->sessionId, $this->replyId, $commandString);
            @fwrite($this->socket, $buf);

            $response = @fread($this->socket, 1024);
            $ackCode = 0;
            if (strlen($response) >= 8) {
                $u = unpack('vcode', substr($response, 0, 2));
                $ackCode = $u['code'] ?? 0;
            }

            $this->sendCommand(self::CMD_ENABLEDEVICE);

            return [
                'success' => true,
                'ack_code' => $ackCode,
                'message' => "Identifiant {$cleanId} supprimé du pointeur biométrique.",
            ];
        } catch (\Throwable $e) {
            Log::error('ZKTeco deleteUser failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur suppression pointeur : ' . $e->getMessage(),
            ];
        } finally {
            $this->disconnect();
        }
    }

    public function deleteUserFingerprint(string $userId, int $fingerId = 0): array
    {
        if (! $this->socket && ! $this->connect()) {
            return [
                'success' => false,
                'message' => "Connexion au pointeur impossible.",
            ];
        }

        try {
            $this->sendCommand(self::CMD_DISABLEDEVICE);

            $cleanId = trim($userId);
            // CMD_DELETE_USER_TEMP = 134
            $commandString = pack('a24C', $cleanId, $fingerId);
            $buf = $this->createHeader(self::CMD_DELETE_USER_TEMP, 0, $this->sessionId, $this->replyId, $commandString);
            @fwrite($this->socket, $buf);

            $response = @fread($this->socket, 1024);
            $this->sendCommand(self::CMD_ENABLEDEVICE);

            return [
                'success' => true,
                'message' => "Empreinte de {$cleanId} supprimée du pointeur.",
            ];
        } catch (\Throwable $e) {
            Log::error('ZKTeco deleteUserFingerprint failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            $this->disconnect();
        }
    }

    protected function sendCommand(int $command, string $payload = ''): ?string
    {
        if (! $this->socket) {
            return null;
        }

        $buf = $this->createHeader($command, 0, $this->sessionId, $this->replyId, $payload);
        @fwrite($this->socket, $buf);
        return @fread($this->socket, 1024);
    }

    public function connect(): bool
    {
        try {
            // ZKTeco standard communication over UDP/TCP 4370
            $remote = 'udp://' . $this->ip . ':' . $this->port;
            $this->socket = @stream_socket_client($remote, $errno, $errstr, $this->timeout);

            if (! $this->socket) {
                return false;
            }

            stream_set_timeout($this->socket, $this->timeout);

            $command = self::CMD_CONNECT;
            $command_string = '';
            $chksum = 0;
            $session_id = 0;
            $reply_id = -1 + 1;

            $buf = $this->createHeader($command, $chksum, $session_id, $reply_id, $command_string);
            fwrite($this->socket, $buf);

            $response = fread($this->socket, 1024);

            if (strlen($response) >= 8) {
                $u = unpack('H2h1/H2h2/H2h3/H2h4/H2h5/H2h6/H2h7/H2h8', substr($response, 0, 8));
                $this->sessionId = hexdec($u['h6'] . $u['h5']);
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('ZKTeco Connection failed: ' . $e->getMessage());
            return false;
        }
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            $command = self::CMD_EXIT;
            $buf = $this->createHeader($command, 0, $this->sessionId, $this->replyId, '');
            @fwrite($this->socket, $buf);
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    public function getAttendanceLogs(): array
    {
        if (! $this->socket && ! $this->connect()) {
            return [];
        }

        try {
            $command = self::CMD_ATTLOG_RRQ;
            $buf = $this->createHeader($command, 0, $this->sessionId, $this->replyId, '');
            fwrite($this->socket, $buf);

            $response = fread($this->socket, 65535);
            $logs = [];

            if (strlen($response) > 8) {
                // Parse binary records (each log entry is typically 40 bytes or 16 bytes depending on firmware)
                $data = substr($response, 8);
                $logSize = 40;
                $count = floor(strlen($data) / $logSize);

                if ($count <= 0 && strlen($data) >= 16) {
                    $logSize = 16;
                    $count = floor(strlen($data) / $logSize);
                }

                for ($i = 0; $i < $count; $i++) {
                    $record = substr($data, $i * $logSize, $logSize);
                    if (strlen($record) < $logSize) continue;

                    // Extract user ID and date
                    $userId = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '', substr($record, 0, 24)));
                    if (blank($userId)) {
                        $userId = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '', substr($record, 0, 16)));
                    }
                    if (blank($userId)) {
                        $u = unpack('v1user_id', substr($record, 0, 2));
                        $userId = (string) ($u['user_id'] ?? '');
                    }

                    $timestamp = $this->decodeRecordTimestamp($record);

                    if (! empty($userId)) {
                        $logs[] = [
                            'identifier' => $userId,
                            'timestamp' => $timestamp,
                            'status' => 'present',
                        ];
                    }
                }
            }

            return $logs;
        } catch (\Throwable $e) {
            Log::error('ZKTeco Read logs failed: ' . $e->getMessage());
            return [];
        } finally {
            $this->disconnect();
        }
    }

    public function syncToDatabase(string $deviceIdentifier = 'ZKTeco-ZK3969'): array
    {
        $logs = $this->getAttendanceLogs();
        $processed = 0;
        $failed = 0;

        foreach ($logs as $log) {
            $identifier = $log['identifier'];
            $occurredAt = Carbon::parse($log['timestamp']);

            $event = BiometricEvent::create([
                'device_identifier' => $deviceIdentifier,
                'external_identifier' => $identifier,
                'occurred_at' => $occurredAt,
                'payload' => $log,
                'status' => 'pending',
            ]);

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
                    'error_message' => 'Identifiant inconnu : ' . $identifier,
                    'processed_at' => now(),
                ]);
                $failed++;
                continue;
            }

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
                        'status' => 'present',
                        'method' => 'biometric',
                        'checked_at' => $occurredAt,
                        'note' => 'Pointage auto via ' . $deviceIdentifier,
                    ]
                );
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
                        'status' => 'present',
                        'method' => 'biometric',
                        'checked_at' => $occurredAt,
                        'note' => 'Pointage auto via ' . $deviceIdentifier,
                    ]
                );
            }

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            $processed++;
        }

        return [
            'total' => count($logs),
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    protected function decodeRecordTimestamp(string $record): string
    {
        // 1. Try ASCII text pattern (YYYY-MM-DD HH:MM:SS) inside record
        if (preg_match('/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/', $record, $matches)) {
            return $matches[0];
        }

        // 2. Try ZKTeco 4-byte packed time (standard at offset 24 for 40-byte record or offset 4 for 16-byte record)
        $offset = (strlen($record) >= 40) ? 24 : 4;
        if (strlen($record) >= $offset + 4) {
            $unpacked = unpack('Vtime', substr($record, $offset, 4));
            $t = $unpacked['time'] ?? 0;
            if ($t > 0) {
                // Check if encoded ZKTeco formula
                $sec = $t % 60;
                $t = intdiv($t, 60);
                $min = $t % 60;
                $t = intdiv($t, 60);
                $hour = $t % 24;
                $t = intdiv($t, 24);
                $day = ($t % 31) + 1;
                $t = intdiv($t, 31);
                $month = ($t % 12) + 1;
                $t = intdiv($t, 12);
                $year = $t + 2000;

                if ($year >= 2020 && $year <= 2035 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31 && $hour <= 23 && $min <= 59 && $sec <= 59) {
                    return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $min, $sec);
                }

                // Check if standard Unix epoch
                if ($unpacked['time'] >= 1577836800 && $unpacked['time'] <= 2082758400) {
                    return date('Y-m-d H:i:s', $unpacked['time']);
                }
            }
        }

        return now()->toDateTimeString();
    }

    protected function createHeader(int $command, int $chksum, int $session_id, int $reply_id, string $command_string): string
    {
        $buf = pack('vvvv', $command, $chksum, $session_id, $reply_id) . $command_string;
        $buf = unpack('C*', $buf);
        $u = $this->calculateChecksum($buf);
        $chksum = $u['chksum'];
        return pack('vvvv', $command, $chksum, $session_id, $reply_id) . $command_string;
    }

    protected function calculateChecksum(array $p): array
    {
        $l = count($p);
        $chksum = 0;
        $i = 1;
        while ($l > 1) {
            $chksum += $p[$i] + ($p[$i + 1] << 8);
            $l -= 2;
            $i += 2;
        }
        if ($l) {
            $chksum += $p[$i];
        }
        while ($chksum >> 16) {
            $chksum = ($chksum & 0xFFFF) + ($chksum >> 16);
        }
        return ['chksum' => (~$chksum) & 0xFFFF];
    }
}
