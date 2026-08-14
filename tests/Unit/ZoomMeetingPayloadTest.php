<?php

namespace Tests\Unit;

use App\Models\AttendanceSession;
use App\Models\ZoomHost;
use App\Services\Zoom\ZoomApiClient;
use App\Services\Zoom\ZoomMeetingService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class ZoomMeetingPayloadTest extends TestCase
{
    public function test_meeting_payload_uses_registration_waiting_room_and_cloud_recording_settings(): void
    {
        config()->set([
            'zoom.registration_required' => true,
            'zoom.waiting_room' => true,
            'zoom.recording_policy' => 'automatic',
        ]);
        $session = new AttendanceSession([
            'title' => 'Session title',
            'session_date' => '2026-07-20',
            'time_start' => '10:00:00',
            'time_end' => '11:30:00',
        ]);
        $session->setRelation('zoomMeeting', null);
        $session->setRelation('schedule', null);

        $service = new class(Mockery::mock(ZoomApiClient::class)) extends ZoomMeetingService
        {
            public function resolveHost(AttendanceSession $session): ?ZoomHost
            {
                return null;
            }
        };

        Carbon::setTestNow('2026-07-17 10:00:00');
        $payload = $service->meetingPayload($session, 'PASS1234');
        Carbon::setTestNow();

        $this->assertSame('Session title', $payload['topic']);
        $this->assertSame(90, $payload['duration']);
        $this->assertSame('PASS1234', $payload['password']);
        $this->assertTrue($payload['settings']['waiting_room']);
        $this->assertSame(0, $payload['settings']['approval_type']);
        $this->assertSame('cloud', $payload['settings']['auto_recording']);
    }
}
