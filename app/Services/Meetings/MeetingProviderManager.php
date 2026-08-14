<?php

namespace App\Services\Meetings;

use App\Models\AttendanceSession;
use App\Services\MicrosoftTeams\TeamsMeetingService;
use App\Services\Zoom\ZoomMeetingService;
use App\Support\MeetingSettings;
use App\Support\TeamsSettings;
use App\Support\ZoomSettings;

class MeetingProviderManager
{
    public function ensureMeetingForSession(AttendanceSession $session): bool
    {
        return match (MeetingSettings::defaultProvider()) {
            'zoom' => $this->ensureZoom($session),
            'teams' => $this->ensureTeams($session),
            default => false,
        };
    }

    private function ensureZoom(AttendanceSession $session): bool
    {
        if (! ZoomSettings::enabled()) {
            return false;
        }

        app(ZoomMeetingService::class)->ensureMeeting($session);

        return true;
    }

    private function ensureTeams(AttendanceSession $session): bool
    {
        return TeamsSettings::isEnabled()
            && app(TeamsMeetingService::class)->ensureMeetingForSession($session);
    }
}
