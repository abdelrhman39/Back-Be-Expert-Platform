<?php

namespace App\Services\MicrosoftTeams;

use App\Models\AttendanceSession;
use App\Models\MicrosoftTeamsConnection;
use App\Support\RecordingSettings;
use App\Support\TeamsSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TeamsMeetingService
{
    public function __construct(protected TeamsGraphClient $graph) {}

    public function ensureMeetingForSession(AttendanceSession $session): bool
    {
        if ($session->teams_meeting_id && $session->teams_join_web_url) {
            return true;
        }

        if (! TeamsSettings::isEnabled() || ! TeamsSettings::isConfigured()) {
            return false;
        }

        $organizerId = TeamsSettings::organizerUserId();

        if (! $organizerId) {
            return false;
        }

        $session->loadMissing('section.course');

        $start = $this->sessionStart($session);
        $end = $this->sessionEnd($session);

        $payload = [
            'startDateTime' => $start->toIso8601String(),
            'endDateTime' => $end->toIso8601String(),
            'subject' => $session->displayTitle(),
            'recordAutomatically' => RecordingSettings::autoRecordEnabled(),
            'allowRecording' => true,
        ];

        $result = $this->graph->post("/users/{$organizerId}/onlineMeetings", $payload);

        if (! $result || empty($result['id'])) {
            return false;
        }

        $joinUrl = $result['joinWebUrl'] ?? $result['joinUrl'] ?? null;

        $session->update([
            'teams_meeting_id' => $result['id'],
            'teams_join_web_url' => $joinUrl,
            'teams_organizer_id' => $organizerId,
            'meeting_url' => $joinUrl ?: $session->meeting_url,
            'source' => $session->source === 'manual' ? 'teams_sync' : $session->source,
        ]);

        \App\Models\SessionRecording::query()->firstOrCreate(
            ['attendance_session_id' => $session->id],
            [
                'teams_meeting_id' => $result['id'],
                'status' => 'processing',
                'source' => 'teams_graph',
            ],
        );

        return true;
    }

    protected function sessionStart(AttendanceSession $session): Carbon
    {
        $date = $session->session_date->toDateString();
        $time = $session->time_start ? substr((string) $session->time_start, 0, 8) : '08:00:00';

        return Carbon::parse("{$date} {$time}");
    }

    protected function sessionEnd(AttendanceSession $session): Carbon
    {
        $start = $this->sessionStart($session);

        if ($session->time_end) {
            $date = $session->session_date->toDateString();
            $time = substr((string) $session->time_end, 0, 8);

            return Carbon::parse("{$date} {$time}");
        }

        return $start->copy()->addHours(2);
    }
}
