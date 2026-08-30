<?php

namespace App\Services\ZoxAgent;

use App\Models\AcademicStudent;
use App\Models\AttendanceSession;
use App\Models\SessionRecording;
use App\Models\ZoxAgentMeeting;
use App\Services\SessionRecordingService;
use App\Support\RecordingSettings;
use App\Support\ZoxAgentSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZoxAgentWebhookService
{
    public function inboundUrl(): string
    {
        return ZoxAgentSettings::inboundWebhookUrl();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(string $event, array $data, string $rawBody, string $signature): array
    {
        if (! ZoxAgentSettings::enabled()) {
            return ['ok' => true, 'ignored' => true, 'reason' => 'zoxagent_disabled'];
        }

        if (! $this->signatureOk($rawBody, $signature)) {
            return ['ok' => false, 'error' => 'invalid_signature'];
        }

        return match ($event) {
            'attendance.joined', 'participant.joined' => $this->markJoined($data),
            'recording.processing' => $this->importRecording($data, false),
            'recording.ready' => $this->importRecording($data, true),
            'room.ended' => $this->markRoomEnded($data),
            'webhook.test' => ['ok' => true, 'pong' => true],
            default => ['ok' => true, 'ignored' => true, 'event' => $event],
        };
    }

    private function signatureOk(string $rawBody, string $signature): bool
    {
        $secret = (string) (ZoxAgentSettings::webhookSecret() ?? '');
        if ($secret === '' || $signature === '') {
            return $secret === '' && $signature !== '';
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, strtolower($signature))
            || hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function markJoined(array $data): array
    {
        if (! ZoxAgentSettings::autoAttendance()) {
            return ['ok' => true, 'ignored' => true, 'reason' => 'attendance_disabled'];
        }

        $meeting = $this->findMeeting($data);
        if (! $meeting?->session) {
            return ['ok' => true, 'ignored' => true, 'reason' => 'meeting_not_found'];
        }

        $session = $meeting->session->loadMissing('section.students.user');
        $student = $this->matchStudent($session, $data);
        if (! $student) {
            return ['ok' => true, 'matched' => false];
        }

        app(ZoxAgentMeetingService::class)->markStudentJoined($session, $student);

        return [
            'ok' => true,
            'attended' => true,
            'student_id' => $student->id,
            'session_id' => $session->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function importRecording(array $data, bool $ready): array
    {
        $meeting = $this->findMeeting($data);
        if (! $meeting?->session) {
            return ['ok' => true, 'ignored' => true, 'reason' => 'meeting_not_found'];
        }

        $externalId = (string) ($data['recordingId'] ?? $data['sessionId'] ?? $data['recordingKey'] ?? '');
        if ($externalId === '') {
            return ['ok' => false, 'error' => 'missing_recording_id'];
        }

        $playback = $ready
            ? (string) ($data['playbackUrl'] ?? $data['downloadUrl'] ?? $data['url'] ?? '')
            : '';

        $recording = SessionRecording::query()->updateOrCreate(
            ['attendance_session_id' => $meeting->attendance_session_id],
            [
                'provider' => 'zoxagent',
                'source' => 'zoxagent',
                'external_recording_id' => $externalId,
                'recording_url' => $playback !== '' ? $playback : null,
                'play_url' => $playback !== '' ? $playback : null,
                'download_url' => $playback !== '' ? $playback : null,
                'status' => $ready && $playback !== '' ? 'available' : 'processing',
                'synced_at' => now(),
                'recorded_at' => filled($data['startedAt'] ?? null)
                    ? Carbon::parse((string) $data['startedAt'])
                    : now(),
                'expires_at' => now()->addDays(RecordingSettings::retentionDays()),
                'provider_payload' => $data,
            ],
        );

        if ($ready && $playback !== '') {
            app(SessionRecordingService::class)->maybeAutoPublish($recording);
        }

        $meeting->update(['recordings_synced_at' => now(), 'last_synced_at' => now()]);

        return [
            'ok' => true,
            'recording' => true,
            'ready' => $ready,
            'session_id' => $meeting->attendance_session_id,
            'recording_id' => $recording->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function markRoomEnded(array $data): array
    {
        $meeting = $this->findMeeting($data);
        if (! $meeting) {
            return ['ok' => true, 'ignored' => true, 'reason' => 'meeting_not_found'];
        }

        $meeting->update(['last_ended_at' => now(), 'last_synced_at' => now()]);

        return ['ok' => true, 'ended' => true, 'session_id' => $meeting->attendance_session_id];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function findMeeting(array $data): ?ZoxAgentMeeting
    {
        $code = strtoupper(trim((string) ($data['roomCode'] ?? $data['code'] ?? '')));
        if ($code === '') {
            return null;
        }

        return ZoxAgentMeeting::query()
            ->whereRaw('UPPER(room_code) = ?', [$code])
            ->with('session.section.students.user')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function matchStudent(AttendanceSession $session, array $data): ?AcademicStudent
    {
        $students = $session->section?->students ?? collect();

        return app(ZoxAgentMeetingService::class)->matchStudentPublic($students, $data);
    }
}
