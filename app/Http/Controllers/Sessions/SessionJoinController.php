<?php

namespace App\Http\Controllers\Sessions;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use App\Services\InstructorService;
use App\Support\ZoomSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SessionJoinController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        AttendanceSession $session,
        AcademicSessionService $sessions,
        InstructorService $instructors,
    ): RedirectResponse {
        $user = $request->user();
        $isStudent = $user && $sessions->studentCanAccess($user, $session);
        $isInstructor = $user && $instructors->canAccessSession($user, $session);

        abort_unless($isStudent || $isInstructor, 403);

        $session->loadMissing(['zoomMeeting.registrants', 'section.schedule']);

        if ($isStudent) {
            $timing = $sessions->resolveTiming($session);
            abort_if(in_array($timing['state'], ['completed', 'cancelled'], true), 403);

            if ($timing['starts_at']) {
                abort_if(now()->lt($timing['starts_at']->copy()->subMinutes(ZoomSettings::joinWindowMinutes())), 403);
            }
        }

        $url = null;

        if ($session->zoomMeeting) {
            if ($isStudent && $user->academicStudent) {
                $url = $session->zoomMeeting->registrants
                    ->firstWhere('student_id', $user->academicStudent->id)
                    ?->join_url;
            }

            $url ??= $session->zoomMeeting->join_url;
        }

        $url ??= $sessions->joinUrl($session);
        abort_unless(filled($url), 404);

        return redirect()->away($url);
    }
}
