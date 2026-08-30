<?php

namespace App\Services\ZoxAgent;

use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Models\ZoxAgentMeeting;
use App\Support\ZoxAgentSettings;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZoxAgentMeetingService
{
    public function isReady(): bool
    {
        return ZoxAgentSettings::enabled();
    }

    /** @return array<string, mixed> */
    public function testConnection(): array
    {
        if (! filled(ZoxAgentSettings::baseUrl()) || ! filled(ZoxAgentSettings::apiKey())) {
            throw new ZoxAgentApiException('أدخل رابط ZoxAgent ومفتاح API أولاً.');
        }

        $response = $this->request('GET', ZoxAgentSettings::apiBase().'/account');
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر الاتصال بـ ZoxAgent'));
        }

        return $response->json() ?? [];
    }

    public function matchStudentPublic($students, array $row): ?AcademicStudent
    {
        return $this->matchStudent($students, $row);
    }

    /** @return array<string, mixed> */
    public function pushMediaPolicy(): array
    {
        $response = $this->request('PUT', ZoxAgentSettings::apiBase().'/media-policy', ZoxAgentSettings::mediaPolicy());
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر حفظ سياسة LiveKit في ZoxAgent'));
        }

        return $response->json() ?? [];
    }

    /** @return array<string, mixed> */
    public function pushRecordingStorage(): array
    {
        $mode = ZoxAgentSettings::recordingStorageMode();
        $payload = [
            'mode' => $mode,
            'label' => ZoxAgentSettings::s3Label(),
            'activate' => $mode === 'byo',
            'acceptLiability' => $mode === 'byo',
        ];

        if ($mode === 'byo') {
            $payload['bucket'] = ZoxAgentSettings::s3Bucket();
            $payload['region'] = ZoxAgentSettings::s3Region();
            $payload['endpoint'] = ZoxAgentSettings::s3Endpoint();
            $payload['publicBaseUrl'] = ZoxAgentSettings::s3PublicBaseUrl();
            $payload['forcePathStyle'] = ZoxAgentSettings::s3ForcePathStyle();
            if (filled(ZoxAgentSettings::s3AccessKey())) {
                $payload['accessKey'] = ZoxAgentSettings::s3AccessKey();
            }
            if (filled(ZoxAgentSettings::s3SecretKey())) {
                $payload['secretKey'] = ZoxAgentSettings::s3SecretKey();
            }
        }

        $response = $this->request('PUT', ZoxAgentSettings::apiBase().'/storage', $payload);
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر حفظ إعدادات التخزين في ZoxAgent'));
        }

        return $response->json() ?? [];
    }

    /** @return array<string, mixed> */
    public function testRecordingStorage(array $credentials = []): array
    {
        $response = $this->request('POST', ZoxAgentSettings::apiBase().'/storage/test', $credentials);
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'فشل اختبار S3'));
        }

        return $response->json() ?? [];
    }

    /** @return array<string, mixed> */
    public function pushWebhooks(): array
    {
        $url = app(ZoxAgentWebhookService::class)->inboundUrl();
        $response = $this->request('PUT', ZoxAgentSettings::apiBase().'/webhooks', [
            'url' => $url,
            'events' => [
                'attendance.joined',
                'recording.processing',
                'recording.ready',
                'room.ended',
            ],
        ]);

        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر ربط الويب هوك مع ZoxAgent'));
        }

        $json = $response->json() ?? [];
        $endpoint = is_array($json['endpoint'] ?? null) ? $json['endpoint'] : [];
        if (filled($endpoint['id'] ?? null)) {
            ZoxAgentSettings::set('webhook_endpoint_id', (string) $endpoint['id']);
        }
        if (filled($endpoint['secret'] ?? null)) {
            ZoxAgentSettings::set('webhook_secret', (string) $endpoint['secret'], true);
        }

        return $json;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listRoomRecordings(string $roomCode): array
    {
        $response = $this->request(
            'GET',
            ZoxAgentSettings::apiBase().'/rooms/'.rawurlencode(strtoupper($roomCode)).'/recordings',
        );
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر سحب قائمة التسجيلات'));
        }

        $json = $response->json() ?? [];

        return is_array($json['recordings'] ?? null) ? $json['recordings'] : [];
    }

    public function pullRecordings(AttendanceSession $session): int
    {
        $session->loadMissing('zoxAgentMeeting');
        $meeting = $session->zoxAgentMeeting;
        if (! $meeting?->room_code) {
            return 0;
        }

        $imported = 0;
        $webhooks = app(ZoxAgentWebhookService::class);
        foreach ($this->listRoomRecordings($meeting->room_code) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $status = (string) ($item['status'] ?? '');
            $ready = $status === 'completed' && (
                filled($item['playbackUrl'] ?? null)
                || filled($item['url'] ?? null)
                || filled($item['recordingKey'] ?? null)
            );
            $result = $webhooks->importRecording(array_merge($item, [
                'roomCode' => $meeting->room_code,
                'code' => $meeting->room_code,
            ]), $ready);
            if (($result['ok'] ?? false) && ! ($result['ignored'] ?? false)) {
                $imported++;
            }
        }

        $meeting->update(['recordings_synced_at' => now(), 'last_synced_at' => now()]);

        return $imported;
    }

    public function syncDueRecordings(): int
    {
        if (! $this->isReady() || ! ZoxAgentSettings::autoRecord()) {
            return 0;
        }

        $count = 0;
        ZoxAgentMeeting::query()
            ->whereHas('session', fn ($query) => $query->whereDate('session_date', '<=', today()))
            ->whereNotNull('last_started_at')
            ->where(fn ($query) => $query->whereNull('recordings_synced_at')
                ->orWhere('recordings_synced_at', '<', now()->subMinutes(10)))
            ->with('session')
            ->limit(20)
            ->get()
            ->each(function (ZoxAgentMeeting $meeting) use (&$count) {
                try {
                    $this->pullRecordings($meeting->session);
                    $count++;
                } catch (\Throwable $e) {
                    Log::warning('ZoxAgent recording pull failed', [
                        'session_id' => $meeting->attendance_session_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return $count;
    }

    public function endDueRooms(): int
    {
        if (! $this->isReady()) {
            return 0;
        }

        $ended = 0;
        ZoxAgentMeeting::query()
            ->whereNotNull('last_started_at')
            ->where(fn ($query) => $query->whereNull('last_ended_at')
                ->orWhereColumn('last_ended_at', '<', 'last_started_at'))
            ->with('session.section.schedule')
            ->orderBy('id')
            ->chunkById(40, function ($meetings) use (&$ended) {
                foreach ($meetings as $meeting) {
                    $session = $meeting->session;
                    $endsAt = $session?->endsAt();
                    if (! $endsAt || now()->lt($endsAt->copy()->addMinutes(2))) {
                        continue;
                    }

                    try {
                        $result = $this->endRoom($session, true);
                        if (! is_array($result) || ($result['skipped'] ?? false)) {
                            continue;
                        }
                        $this->syncAttendance($session);
                        $this->pullRecordings($session);
                        $ended++;
                    } catch (\Throwable $e) {
                        Log::warning('ZoxAgent auto-end failed', [
                            'session_id' => $session?->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $ended;
    }

    public function ensureMeeting(AttendanceSession $session): ZoxAgentMeeting
    {
        if (! $this->isReady()) {
            throw new ZoxAgentApiException('تكامل ZoxAgent غير مفعّل أو بياناته غير مكتملة.');
        }

        $session->loadMissing(['section.students', 'section.course', 'zoxAgentMeeting']);
        $meeting = $session->zoxAgentMeeting;

        try {
            if ($meeting?->room_code) {
                $patched = $this->request(
                    'PATCH',
                    ZoxAgentSettings::apiBase().'/rooms/'.rawurlencode($meeting->room_code),
                    $this->roomPayload($session, false),
                );

                if ($patched->successful()) {
                    return $this->storeMeeting($session, $patched->json('room') ?? [], $meeting);
                }

                if ($patched->status() !== 404) {
                    throw new ZoxAgentApiException($this->errorMessage($patched, 'تعذر تحديث قاعة ZoxAgent'));
                }
            }

            $created = $this->request('POST', ZoxAgentSettings::apiBase().'/rooms', $this->roomPayload($session, true));
            if ($created->status() === 409) {
                $payload = $this->roomPayload($session, true);
                $payload['code'] = strtoupper(substr('BE'.$session->id.Str::upper(Str::random(3)), 0, 16));
                $created = $this->request('POST', ZoxAgentSettings::apiBase().'/rooms', $payload);
            }
            if (! $created->successful()) {
                throw new ZoxAgentApiException($this->errorMessage($created, 'تعذر إنشاء قاعة ZoxAgent'));
            }

            return $this->storeMeeting($session, $created->json('room') ?? [], $meeting);
        } catch (\Throwable $e) {
            if ($meeting) {
                $meeting->update(['last_error' => $e->getMessage()]);
            }
            Log::warning('ZoxAgent room sync failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
            throw $e instanceof ZoxAgentApiException ? $e : new ZoxAgentApiException($e->getMessage(), 0, $e);
        }
    }

    public function startRoom(AttendanceSession $session): ?array
    {
        $meeting = $session->zoxAgentMeeting ?? $this->ensureMeeting($session);
        if (! filled($meeting->room_code)) {
            return null;
        }

        $response = $this->request(
            'POST',
            ZoxAgentSettings::apiBase().'/rooms/'.rawurlencode($meeting->room_code),
            ['action' => 'start'],
        );
        if (! $response->successful()) {
            $message = $this->errorMessage($response, 'تعذر بدء قاعة ZoxAgent');
            $meeting->update(['last_error' => $message]);
            throw new ZoxAgentApiException($message);
        }

        $meeting->update([
            'last_started_at' => now(),
            'last_error' => null,
            'last_synced_at' => now(),
        ]);

        return $response->json() ?? [];
    }

    public function endRoom(AttendanceSession $session, bool $onlyIfEmpty = false): ?array
    {
        $meeting = $session->zoxAgentMeeting;
        if (! $meeting?->room_code) {
            return null;
        }

        $response = $this->request(
            'POST',
            ZoxAgentSettings::apiBase().'/rooms/'.rawurlencode($meeting->room_code),
            ['action' => 'end', 'onlyIfEmpty' => $onlyIfEmpty],
        );
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر إنهاء قاعة ZoxAgent'));
        }

        $json = $response->json() ?? [];
        if ($onlyIfEmpty && ($json['skipped'] ?? false)) {
            return $json;
        }

        $meeting->update([
            'last_ended_at' => now(),
            'last_error' => null,
            'last_synced_at' => now(),
        ]);

        return $json;
    }

    /** @return array<string, mixed> */
    public function mintEmbedSession(AttendanceSession $session, User $user, string $role): array
    {
        $meeting = $session->zoxAgentMeeting;
        if (! $this->isReady() || ! $meeting?->room_code) {
            throw new ZoxAgentApiException('قاعة ZoxAgent غير جاهزة لهذه الحصة.');
        }

        $student = $user->academicStudent;
        $identityId = $student?->id ?: $user->id;

        $response = $this->request('POST', ZoxAgentSettings::baseUrl().'/api/embed/session', [
            'roomCode' => $meeting->room_code,
            'role' => $role,
            'displayName' => $user->name ?: ($student?->name_ar ?: 'مستخدم #'.$user->id),
            'email' => $user->email ?: $student?->email,
            'studentId' => (string) $identityId,
            'identityKey' => ($student ? 'sid:' : 'uid:').$identityId,
            'origin' => ZoxAgentSettings::embedOrigin(),
            'ttlSeconds' => 6 * 60 * 60,
        ]);

        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر إصدار جلسة الدخول للقاعة'));
        }

        $data = $response->json() ?? [];
        $data['role'] = $role;
        $token = $data['embedToken'] ?? null;
        if (empty($data['roomUrl']) && $token) {
            $data['roomUrl'] = ZoxAgentSettings::baseUrl()
                .'/ar/room/'.$meeting->room_code
                .'?token='.urlencode((string) $token);
        }

        return $data;
    }

    public function meetingRoleFor(User $user, bool $isInstructor): string
    {
        return $isInstructor ? 'host' : 'student';
    }

    /** @return list<array<string, mixed>> */
    public function fetchAttendance(ZoxAgentMeeting $meeting): array
    {
        if (! $this->isReady() || ! filled($meeting->room_code)) {
            return [];
        }

        $response = $this->request(
            'GET',
            ZoxAgentSettings::apiBase().'/rooms/'.rawurlencode($meeting->room_code).'/attendance',
        );
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر سحب حضور القاعة'));
        }

        return $response->json('attendance') ?? [];
    }

    public function syncAttendance(AttendanceSession $session): int
    {
        if (! ZoxAgentSettings::autoAttendance()) {
            return 0;
        }

        $session->loadMissing(['zoxAgentMeeting', 'section.students.user']);
        $meeting = $session->zoxAgentMeeting;
        if (! $meeting) {
            return 0;
        }

        $rows = $this->fetchAttendance($meeting);
        $students = $session->section->students;
        $aggregated = [];

        foreach ($rows as $row) {
            if (($row['role'] ?? 'student') !== 'student') {
                continue;
            }

            $joinedAt = filled($row['joinedAt'] ?? null) ? Carbon::parse($row['joinedAt']) : now();
            $rowDate = $joinedAt->timezone(config('app.timezone'))->toDateString();
            if ($rowDate !== $session->session_date->toDateString()) {
                continue;
            }

            $student = $this->matchStudent($students, $row);
            if (! $student) {
                continue;
            }

            $leftAt = filled($row['leftAt'] ?? null) ? Carbon::parse($row['leftAt']) : null;
            $seconds = max(0, (int) ($row['durationSec'] ?? ($leftAt ? $joinedAt->diffInSeconds($leftAt) : 0)));
            $entry = $aggregated[$student->id] ?? [
                'seconds' => 0,
                'joined_at' => null,
                'left_at' => null,
                'participant_id' => null,
                'segments' => [],
            ];
            $entry['seconds'] += $seconds;
            $entry['joined_at'] = ! $entry['joined_at'] || $joinedAt->lt($entry['joined_at']) ? $joinedAt : $entry['joined_at'];
            $entry['left_at'] = ! $entry['left_at'] || ($leftAt && $leftAt->gt($entry['left_at'])) ? $leftAt : $entry['left_at'];
            $entry['participant_id'] ??= $row['participantId'] ?? null;
            $entry['segments'][] = $row;
            $aggregated[$student->id] = $entry;
        }

        $synced = 0;
        foreach ($students as $student) {
            $existing = AttendanceRecord::query()
                ->where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();
            if (
                $existing?->source === 'override'
                || ($existing?->source === 'manual' && $existing->recorded_by !== null)
            ) {
                continue;
            }

            $attendance = $aggregated[$student->id] ?? null;
            AttendanceRecord::query()->updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => $attendance ? $this->attendanceStatus($session, $attendance) : ($existing?->status ?? 'absent'),
                    'source' => 'zoxagent_sync',
                    'provider' => 'zoxagent',
                    'external_participant_id' => $attendance['participant_id'] ?? $existing?->external_participant_id,
                    'attendance_seconds' => $attendance['seconds'] ?? $existing?->attendance_seconds ?? 0,
                    'joined_at' => $attendance['joined_at'] ?? $existing?->joined_at,
                    'left_at' => $attendance['left_at'] ?? $existing?->left_at,
                    'provider_payload' => $attendance ? ['segments' => $attendance['segments']] : $existing?->provider_payload,
                    'notes' => 'مزامنة تلقائية من ZoxAgent',
                ],
            );
            if ($attendance) {
                $synced++;
            }
        }

        $meeting->update([
            'attendance_synced_at' => now(),
            'last_synced_at' => now(),
            'last_error' => null,
        ]);

        return $synced;
    }

    public function markStudentJoined(AttendanceSession $session, AcademicStudent $student): void
    {
        if (! ZoxAgentSettings::autoAttendance()) {
            return;
        }

        $existing = AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();
        if (
            $existing?->source === 'override'
            || ($existing?->source === 'manual' && $existing->recorded_by !== null)
        ) {
            return;
        }

        AttendanceRecord::query()->updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
            ],
            [
                'status' => $existing?->status === 'late' ? 'late' : 'present',
                'source' => 'zoxagent_join',
                'provider' => 'zoxagent',
                'joined_at' => $existing?->joined_at ?? now(),
                'notes' => 'دخول من قاعة ZoxAgent',
            ],
        );
    }

    /**
     * @return array{url:?string,status:?string,pending:bool}
     */
    public function recordingPlayback(string $sessionId): array
    {
        $response = $this->request(
            'GET',
            ZoxAgentSettings::apiBase().'/recordings/'.rawurlencode($sessionId),
        );
        $json = $response->json() ?? [];
        if ($response->status() === 409 || ($json['pending'] ?? false)) {
            return [
                'url' => $json['url'] ?? null,
                'status' => $json['status'] ?? 'processing',
                'pending' => true,
            ];
        }
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر جلب رابط التسجيل'));
        }

        return [
            'url' => $json['url'] ?? null,
            'status' => $json['status'] ?? 'completed',
            'pending' => false,
        ];
    }

    public function startDueSessions(): int
    {
        if (! $this->isReady()) {
            return 0;
        }

        $lead = ZoxAgentSettings::startLeadMinutes();
        $started = 0;

        ZoxAgentMeeting::query()
            ->whereHas('session', function ($query) {
                $query->whereDate('session_date', today())
                    ->whereIn('status', ['scheduled']);
            })
            ->with('session.section.schedule')
            ->orderBy('id')
            ->chunkById(40, function ($meetings) use (&$started, $lead) {
                foreach ($meetings as $meeting) {
                    $session = $meeting->session;
                    $startsAt = $session->startsAt();
                    $endsAt = $session->endsAt();
                    if (! $startsAt || ! $endsAt) {
                        continue;
                    }

                    $windowStart = $startsAt->copy()->subMinutes($lead);
                    if (now()->lt($windowStart) || now()->gt($endsAt)) {
                        continue;
                    }

                    if ($meeting->last_started_at?->isToday()) {
                        try {
                            $this->syncAttendance($session);
                        } catch (\Throwable $e) {
                            Log::warning('ZoxAgent attendance sync failed', [
                                'session_id' => $session->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                        continue;
                    }

                    try {
                        $this->startRoom($session);
                        $started++;
                        $this->syncAttendance($session);
                    } catch (\Throwable $e) {
                        Log::warning('ZoxAgent auto-start failed', [
                            'session_id' => $session->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $started;
    }

    public function syncDueAttendance(): int
    {
        if (! $this->isReady() || ! ZoxAgentSettings::autoAttendance()) {
            return 0;
        }

        $count = 0;
        ZoxAgentMeeting::query()
            ->whereHas('session', fn ($query) => $query->whereDate('session_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('attendance_synced_at')
                ->orWhere('attendance_synced_at', '<', now()->subMinutes(5)))
            ->with('session.section.students.user')
            ->limit(25)
            ->get()
            ->each(function (ZoxAgentMeeting $meeting) use (&$count) {
                try {
                    $this->syncAttendance($meeting->session);
                    $count++;
                } catch (\Throwable $e) {
                    Log::warning('ZoxAgent attendance sync failed', [
                        'session_id' => $meeting->attendance_session_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return $count;
    }

    /** @return array<string, mixed> */
    private function roomPayload(AttendanceSession $session, bool $includeCode): array
    {
        $studentsCount = $session->section?->students?->count() ?? 0;
        $startsAt = $session->startsAt();
        $endsAt = $session->endsAt();
        $title = $session->displayTitle();
        $course = $session->section?->course?->name_ar;
        if ($course) {
            $title = $course.' — '.$title;
        }

        $payload = [
            'title' => Str::limit($title, 120, ''),
            'maxParticipants' => max(20, min(5000, $studentsCount + 30)),
            'autoStartRecording' => ZoxAgentSettings::autoRecord() && ZoxAgentSettings::recordingQuality() !== 'off',
            'attendanceMode' => ZoxAgentSettings::attendanceMode(),
            'attendanceIdentityRequired' => 'either',
            'attendanceThresholdPct' => max(1, ZoxAgentSettings::minimumAttendancePercent() ?: 75),
            'attendanceMinMinutes' => max(1, ZoxAgentSettings::minimumAttendanceMinutes()),
            'mediaPolicy' => ZoxAgentSettings::mediaPolicy(),
        ];

        if ($includeCode) {
            $payload['code'] = $session->zoxAgentMeeting?->room_code ?: $this->suggestedRoomCode($session);
        }
        if ($startsAt) {
            $payload['scheduledAt'] = $startsAt->clone()->utc()->format('Y-m-d\TH:i:s\Z');
        }
        if ($endsAt) {
            $payload['scheduledEndAt'] = $endsAt->clone()->utc()->format('Y-m-d\TH:i:s\Z');
        }

        return $payload;
    }

    private function suggestedRoomCode(AttendanceSession $session): string
    {
        return strtoupper(substr('BE'.str_pad((string) $session->id, 4, '0', STR_PAD_LEFT), 0, 16));
    }

    /** @param  array<string, mixed>  $room */
    private function storeMeeting(AttendanceSession $session, array $room, ?ZoxAgentMeeting $meeting): ZoxAgentMeeting
    {
        $code = (string) ($room['code'] ?? $meeting?->room_code ?? $this->suggestedRoomCode($session));
        $joinUrl = ZoxAgentSettings::baseUrl().'/ar/room/'.$code;

        $meeting = ZoxAgentMeeting::query()->updateOrCreate(
            ['attendance_session_id' => $session->id],
            [
                'room_id' => $room['id'] ?? $meeting?->room_id,
                'room_code' => $code,
                'join_url' => $joinUrl,
                'auto_record' => ZoxAgentSettings::autoRecord() && ZoxAgentSettings::recordingQuality() !== 'off',
                'last_error' => null,
                'last_synced_at' => now(),
            ],
        );

        $session->update([
            'meeting_url' => $joinUrl,
            'source' => $session->source === 'manual' ? 'zoxagent' : $session->source,
        ]);

        return $meeting->fresh();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AcademicStudent>  $students
     * @param  array<string, mixed>  $row
     */
    private function matchStudent($students, array $row): ?AcademicStudent
    {
        $externalId = (string) ($row['studentId'] ?? '');
        if ($externalId !== '' && ctype_digit($externalId)) {
            $byId = $students->firstWhere('id', (int) $externalId);
            if ($byId) {
                return $byId;
            }
            $byUser = $students->firstWhere('user_id', (int) $externalId);
            if ($byUser) {
                return $byUser;
            }
        }

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email !== '') {
            $byEmail = $students->first(function (AcademicStudent $student) use ($email) {
                return strtolower((string) ($student->email ?: $student->user?->email)) === $email;
            });
            if ($byEmail) {
                return $byEmail;
            }
        }

        $identity = (string) ($row['identityKey'] ?? '');
        if (str_starts_with($identity, 'sid:')) {
            $sid = substr($identity, 4);
            if (ctype_digit($sid)) {
                return $students->firstWhere('id', (int) $sid);
            }
        }

        return null;
    }

    /** @param  array{seconds: int, joined_at: ?Carbon}  $attendance */
    private function attendanceStatus(AttendanceSession $session, array $attendance): string
    {
        if (ZoxAgentSettings::attendanceMode() === 'join') {
            return $this->lateOrPresent($session, $attendance);
        }

        $start = $session->startsAt();
        $end = $session->endsAt();
        $scheduledSeconds = $start && $end ? max(60, $start->diffInSeconds($end)) : 0;
        $required = max(
            ZoxAgentSettings::minimumAttendanceMinutes() * 60,
            (int) ceil($scheduledSeconds * ZoxAgentSettings::minimumAttendancePercent() / 100),
        );
        if ($attendance['seconds'] <= 0 || $attendance['seconds'] < $required) {
            return 'absent';
        }

        return $this->lateOrPresent($session, $attendance);
    }

    /** @param  array{joined_at: ?Carbon}  $attendance */
    private function lateOrPresent(AttendanceSession $session, array $attendance): string
    {
        $start = $session->startsAt();
        if ($start && $attendance['joined_at']?->gt($start->copy()->addMinutes(ZoxAgentSettings::lateMinutes()))) {
            return 'late';
        }

        return 'present';
    }

    /** @return array<string, mixed> */
    public function billingSnapshot(): array
    {
        if (! filled(ZoxAgentSettings::baseUrl()) || ! filled(ZoxAgentSettings::apiKey())) {
            throw new ZoxAgentApiException('أدخل رابط ZoxAgent ومفتاح API أولاً.');
        }

        $response = $this->request('GET', ZoxAgentSettings::apiBase().'/billing');
        if ($response->status() === 404) {
            throw new ZoxAgentApiException('واجهة الفوترة غير مفعّلة على خادم ZoxAgent بعد. انشر أحدث نسخة ثم أعد المحاولة.');
        }
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر جلب بيانات الاشتراك من ZoxAgent'));
        }

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createBillingCheckout(array $payload): array
    {
        if (! filled(ZoxAgentSettings::baseUrl()) || ! filled(ZoxAgentSettings::apiKey())) {
            throw new ZoxAgentApiException('أدخل رابط ZoxAgent ومفتاح API أولاً.');
        }

        $response = $this->request('POST', ZoxAgentSettings::apiBase().'/billing/checkout', $payload, 45);
        if ($response->status() === 404) {
            throw new ZoxAgentApiException('واجهة الفوترة غير مفعّلة على خادم ZoxAgent بعد. انشر أحدث نسخة ثم أعد المحاولة.');
        }
        if (! $response->successful()) {
            throw new ZoxAgentApiException($this->errorMessage($response, 'تعذر إنشاء عملية الدفع'));
        }

        return $response->json() ?? [];
    }

    /** @param  array<string, mixed>  $payload */
    private function request(string $method, string $url, array $payload = [], int $timeout = 25): Response
    {
        $http = Http::timeout($timeout)
            ->acceptJson()
            ->withToken((string) ZoxAgentSettings::apiKey())
            ->withHeaders([
                'X-Api-Key' => (string) ZoxAgentSettings::apiKey(),
            ]);

        $method = strtoupper($method);
        if ($method === 'GET') {
            return $http->get($url);
        }

        return $http->send($method, $url, ['json' => $payload]);
    }

    private function errorMessage(Response $response, string $fallback): string
    {
        $json = $response->json();
        $fromApi = is_array($json) ? ($json['error'] ?? $json['message'] ?? null) : null;
        $hint = $fromApi ? (is_string($fromApi) ? $fromApi : json_encode($fromApi)) : $response->body();

        return $fallback.' (HTTP '.$response->status().')'.($hint ? ': '.Str::limit(strip_tags((string) $hint), 280) : '');
    }
}
