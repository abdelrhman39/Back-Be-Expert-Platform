<?php

namespace App\Services\MicrosoftTeams;

use App\Models\AttendanceSession;
use App\Models\SessionMaterial;
use App\Models\SessionRecording;
use App\Models\User;
use App\Support\RecordingSettings;
use App\Support\TeamsSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TeamsRecordingSyncService
{
    public function __construct(protected TeamsGraphClient $graph) {}

    public function syncDueSessions(): int
    {
        if (! TeamsSettings::isEnabled() || ! TeamsSettings::isConfigured()) {
            return 0;
        }

        $sessions = AttendanceSession::query()
            ->whereNotNull('teams_meeting_id')
            ->whereDate('session_date', '<=', today())
            ->whereHas('section')
            ->with('recording')
            ->limit(25)
            ->get();

        $synced = 0;

        foreach ($sessions as $session) {
            if ($this->syncSession($session)) {
                $synced++;
            }
        }

        return $synced;
    }

    public function syncSession(AttendanceSession $session): bool
    {
        if (! $session->teams_meeting_id) {
            return false;
        }

        $organizerId = $session->teams_organizer_id ?: TeamsSettings::organizerUserId();

        if (! $organizerId) {
            return false;
        }

        $recording = SessionRecording::query()->firstOrCreate(
            ['attendance_session_id' => $session->id],
            [
                'teams_meeting_id' => $session->teams_meeting_id,
                'status' => 'processing',
                'source' => 'teams_graph',
            ],
        );

        if ($recording->isPublished() && $recording->recording_url) {
            return false;
        }

        $response = $this->graph->get(
            "/users/{$organizerId}/onlineMeetings/{$session->teams_meeting_id}/recordings"
        );

        if (! $response || empty($response['value'])) {
            if ($this->sessionEndedHoursAgo($session, 6)) {
                $recording->update(['synced_at' => now()]);
            }

            return false;
        }

        $item = $response['value'][0];
        $recordingId = $item['id'] ?? null;
        $recordingUrl = $item['recordingContentUrl']
            ?? $item['contentUrl']
            ?? $item['recordingWebUrl']
            ?? null;

        if (! $recordingUrl) {
            $recording->update([
                'graph_recording_id' => $recordingId,
                'raw_graph_payload' => $item,
                'synced_at' => now(),
                'status' => 'processing',
            ]);

            return false;
        }

        $recordedAt = isset($item['createdDateTime']) ? Carbon::parse($item['createdDateTime']) : now();
        $retentionDays = RecordingSettings::retentionDays();

        $recording->update([
            'teams_meeting_id' => $session->teams_meeting_id,
            'graph_recording_id' => $recordingId,
            'recording_url' => $recordingUrl,
            'duration_seconds' => (int) ($item['durationInSeconds'] ?? $item['duration'] ?? 0) ?: null,
            'recorded_at' => $recordedAt,
            'synced_at' => now(),
            'expires_at' => $recordedAt->copy()->addDays($retentionDays),
            'raw_graph_payload' => $item,
            'status' => $recording->status === 'published' ? 'published' : 'available',
        ]);

        app(SessionRecordingService::class)->maybeAutoPublish($recording);

        return true;
    }

    protected function sessionEndedHoursAgo(AttendanceSession $session, int $hours): bool
    {
        $date = $session->session_date->toDateString();
        $time = $session->time_end ? substr((string) $session->time_end, 0, 8) : '23:59:59';
        $end = Carbon::parse("{$date} {$time}");

        return now()->greaterThan($end->copy()->addHours($hours));
    }
}
