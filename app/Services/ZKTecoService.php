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
    const CMD_ATTLOG_RRQ = 500;
    const CMD_CLEAR_ATTLOG = 503;
    const CMD_GET_TIME = 201;

    const CMD_ACK_OK = 2000;
    const CMD_ACK_ERROR = 2001;
    const CMD_ACK_DATA = 2002;
    const CMD_PREPARE_DATA = 1500;
    const CMD_DATA = 1501;

    public function __construct(string $ip = '192.168.1.201', int $port = 4370, int $timeout = 5)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
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
                    $userId = trim(preg_replace('/[^a-zA-Z0-9_\-]/', '', substr($record, 0, 16)));
                    if (blank($userId)) {
                        $u = unpack('v1user_id', substr($record, 0, 2));
                        $userId = (string) ($u['user_id'] ?? '');
                    }

                    if (! empty($userId)) {
                        $logs[] = [
                            'identifier' => $userId,
                            'timestamp' => now()->toDateTimeString(),
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
