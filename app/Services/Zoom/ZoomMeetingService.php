<?php

namespace App\Services\Zoom;

use App\Models\AttendanceSession;
use App\Models\ZoomHost;
use App\Models\ZoomMeeting;
use App\Models\ZoomRegistrant;
use App\Support\ZoomSettings;
use Illuminate\Support\Str;

class ZoomMeetingService
{
    public function __construct(private readonly ZoomApiClient $api) {}

    public function ensureMeeting(AttendanceSession $session): ZoomMeeting
    {
        $session->loadMissing(['section.students.user', 'schedule.staff.zoomHost', 'zoomMeeting.host']);
        $meeting = $session->zoomMeeting;
        $host = $meeting?->host ?? $this->resolveHost($session);
        if (! $host) {
            throw new ZoomApiException('No active Zoom host is available for this session.');
        }

        $passcode = $meeting?->passcode ?: Str::upper(Str::random(8));
        $payload = $this->meetingPayload($session, $passcode);

        if ($meeting) {
            $this->api->patch('/meetings/'.$meeting->meeting_id, $payload);
            $remote = $this->api->get('/meetings/'.$meeting->meeting_id);
        } else {
            $remote = $this->api->post('/users/'.rawurlencode($host->zoom_user_id).'/meetings', $payload);
        }

        if (empty($remote['id'])) {
            throw new ZoomApiException('Zoom meeting response did not contain a meeting ID.');
        }

        $meeting = ZoomMeeting::query()->updateOrCreate(
            ['attendance_session_id' => $session->id],
            [
                'zoom_host_id' => $host->id,
                'meeting_id' => (string) $remote['id'],
                'meeting_uuid' => $remote['uuid'] ?? $meeting?->meeting_uuid,
                'join_url' => $remote['join_url'] ?? $meeting?->join_url,
                'start_url' => $remote['start_url'] ?? $meeting?->start_url,
                'passcode' => $remote['password'] ?? $passcode,
                'registration_mode' => ZoomSettings::registrationRequired() ? 'required' : 'none',
                'recording_mode' => ZoomSettings::recordingPolicy(),
                'status' => $remote['status'] ?? 'scheduled',
                'alternative_hosts' => $this->alternativeHosts($session, $host),
                'settings' => $remote['settings'] ?? $payload['settings'],
                'raw_payload' => $this->sanitizePayload($remote),
                'last_synced_at' => now(),
            ],
        );

        $session->update([
            'meeting_url' => $meeting->join_url ?: $session->meeting_url,
            'source' => $session->source === 'manual' ? 'zoom' : $session->source,
        ]);

        if (ZoomSettings::registrationRequired()) {
            $this->syncRegistrants($meeting);
        }

        return $meeting->refresh();
    }

    public function resolveHost(AttendanceSession $session): ?ZoomHost
    {
        $active = ZoomHost::query()->where('is_active', true);
        $strategy = ZoomSettings::hostStrategy();

        if ($strategy === 'instructor') {
            $instructor = $session->schedule?->staff?->zoomHost;
            if ($instructor?->is_active) {
                return $instructor;
            }
        }

        if ($strategy === 'pool') {
            $poolHost = (clone $active)
                ->withCount(['meetings' => fn ($query) => $query->whereIn('status', ['scheduled', 'started'])])
                ->orderBy('meetings_count')
                ->orderBy('priority')
                ->first();
            if ($poolHost) {
                return $poolHost;
            }
        }

        $default = ZoomSettings::defaultHost();
        if ($default) {
            $central = (clone $active)
                ->where(fn ($query) => $query->where('zoom_user_id', $default)->orWhere('email', $default))
                ->first();
            if ($central) {
                return $central;
            }
        }

        return $active->orderBy('priority')->first();
    }

    /** @return array<string, mixed> */
    public function meetingPayload(AttendanceSession $session, string $passcode): array
    {
        $start = $session->startsAt() ?? now()->addHour();
        $end = $session->endsAt() ?? $start->copy()->addHours(2);
        $host = $session->zoomMeeting?->host ?? $this->resolveHost($session);

        $settings = array_filter([
            'waiting_room' => ZoomSettings::waitingRoom(),
            'host_video' => ZoomSettings::hostVideo(),
            'participant_video' => ZoomSettings::participantVideo(),
            'mute_upon_entry' => ZoomSettings::muteUponEntry(),
            'join_before_host' => ZoomSettings::joinBeforeHost(),
            'allow_multiple_devices' => ZoomSettings::allowMultipleDevices(),
            'audio' => ZoomSettings::audioMode(),
            'approval_type' => ZoomSettings::registrationRequired() ? 0 : 2,
            'registration_type' => ZoomSettings::registrationRequired() ? 1 : null,
            'auto_recording' => ZoomSettings::recordingPolicy() === 'automatic' ? 'cloud' : 'none',
            // Zoom supports preassigning alternative hosts; co-hosts can only be promoted during a live meeting.
            'alternative_hosts' => implode(',', $this->alternativeHosts($session, $host)),
        ], fn (mixed $value): bool => $value !== null);

        return [
            'topic' => $session->displayTitle(),
            'type' => 2,
            'start_time' => $start->toIso8601String(),
            'duration' => (int) max(1, $start->diffInMinutes($end)),
            'timezone' => config('app.timezone'),
            'password' => $passcode,
            'settings' => $settings,
        ];
    }

    public function syncRegistrants(ZoomMeeting $meeting): void
    {
        $meeting->loadMissing('session.section.students.user');
        foreach ($meeting->session->section->students as $student) {
            $email = strtolower((string) ($student->email ?: $student->user?->email));
            if ($email === '') {
                continue;
            }

            $existing = ZoomRegistrant::query()
                ->where('zoom_meeting_id', $meeting->id)
                ->where('student_id', $student->id)
                ->first();
            if ($existing?->registrant_id && $existing->email === $email) {
                continue;
            }
            if ($existing?->registrant_id) {
                $this->api->delete('/meetings/'.$meeting->meeting_id.'/registrants/'.rawurlencode($existing->registrant_id));
            }

            [$firstName, $lastName] = $this->studentNames($student->name_en ?: $student->name_ar);
            $remote = $this->api->post('/meetings/'.$meeting->meeting_id.'/registrants', [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            ZoomRegistrant::query()->updateOrCreate(
                ['zoom_meeting_id' => $meeting->id, 'student_id' => $student->id],
                [
                    'registrant_id' => $remote['registrant_id'] ?? $remote['id'] ?? null,
                    'email' => $email,
                    'join_url' => $remote['join_url'] ?? null,
                    'status' => 'approved',
                    'raw_payload' => $this->sanitizeRegistrantPayload($remote),
                    'last_synced_at' => now(),
                ],
            );
        }
    }

    /** @return array<int, string> */
    private function alternativeHosts(AttendanceSession $session, ?ZoomHost $primary): array
    {
        $configured = $session->zoomMeeting?->alternative_hosts ?? [];
        $instructor = $session->schedule?->staff?->zoomHost;
        if ($instructor?->is_active && $instructor->id !== $primary?->id) {
            $configured[] = $instructor->email;
        }

        return array_values(array_unique(array_filter($configured)));
    }

    private function studentNames(string $name): array
    {
        $parts = preg_split('/\s+/u', trim($name), 2) ?: [];

        return [$parts[0] ?: 'Student', $parts[1] ?? '-'];
    }

    private function sanitizePayload(array $payload): array
    {
        unset($payload['start_url'], $payload['password']);

        return $payload;
    }

    private function sanitizeRegistrantPayload(array $payload): array
    {
        unset($payload['join_url']);

        return $payload;
    }
}
