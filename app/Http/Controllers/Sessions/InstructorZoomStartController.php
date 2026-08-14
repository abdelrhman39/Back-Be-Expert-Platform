<?php

namespace App\Http\Controllers\Sessions;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\InstructorService;
use App\Services\Zoom\ZoomApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstructorZoomStartController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        AttendanceSession $session,
        InstructorService $instructors,
        ZoomApiClient $api,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user, 403);
        $instructors->authorizeSession($user, $session);
        $instructors->authorizePermission($user, 'instructor.zoom.meeting.start');

        $meeting = $session->zoomMeeting;
        abort_unless($meeting, 404);

        $remote = $api->get('/meetings/'.$meeting->meeting_id);
        if (filled($remote['start_url'] ?? null)) {
            $meeting->update(['start_url' => $remote['start_url'], 'last_synced_at' => now()]);
        }

        abort_unless(filled($meeting->fresh()->start_url), 404);

        return redirect()->away($meeting->fresh()->start_url);
    }
}
