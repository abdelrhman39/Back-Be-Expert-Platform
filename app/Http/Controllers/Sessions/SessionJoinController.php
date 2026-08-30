<?php

namespace App\Http\Controllers\Sessions;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use App\Services\InstructorService;
use App\Services\ZoxAgent\ZoxAgentApiException;
use App\Services\ZoxAgent\ZoxAgentMeetingService;
use App\Support\ZoxAgentSettings;
use App\Support\ZoomSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionJoinController extends Controller
{
    public function __invoke(
        Request $request,
        AttendanceSession $session,
        AcademicSessionService $sessions,
        InstructorService $instructors,
        ZoxAgentMeetingService $zoxAgent,
    ): RedirectResponse|View {
        $user = $request->user();
        $isStudent = $user && $sessions->studentCanAccess($user, $session);
        $isInstructor = $user && $instructors->canAccessSession($user, $session);
        $isAdmin = $user && $user->canAdmin('attendance.manage');

        abort_unless($isStudent || $isInstructor || $isAdmin, 403);

        $session->loadMissing(['zoomMeeting.registrants', 'section.schedule', 'zoxAgentMeeting']);

        if ($isStudent) {
            $timing = $sessions->resolveTiming($session);
            abort_if(in_array($timing['state'], ['completed', 'cancelled'], true), 403);

            $joinWindow = $session->zoxAgentMeeting
                ? ZoxAgentSettings::joinWindowMinutes()
                : ZoomSettings::joinWindowMinutes();

            if ($timing['starts_at']) {
                abort_if(now()->lt($timing['starts_at']->copy()->subMinutes($joinWindow)), 403);
            }
        }

        if ($session->zoxAgentMeeting && ZoxAgentSettings::enabled()) {
            $returnUrl = $isInstructor
                ? route('instructor.sessions.show', [
                    'locale' => app()->getLocale(),
                    'section' => $session->section_id,
                    'session' => $session->id,
                ])
                : ($isAdmin
                    ? route('admin.sessions')
                    : route('sessions.show', [
                        'locale' => app()->getLocale(),
                        'session' => $session->id,
                    ]));

            return $this->joinZoxAgent(
                $request,
                $session,
                $zoxAgent,
                (bool) $isInstructor || (bool) $isAdmin,
                (bool) $isStudent,
                $returnUrl,
            );
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

    private function joinZoxAgent(
        Request $request,
        AttendanceSession $session,
        ZoxAgentMeetingService $zoxAgent,
        bool $isInstructor,
        bool $isStudent,
        string $returnUrl,
    ): RedirectResponse|View {
        $user = $request->user();

        try {
            if (! $session->zoxAgentMeeting?->room_code) {
                $zoxAgent->ensureMeeting($session);
                $session->load('zoxAgentMeeting');
            }

            $payload = $zoxAgent->mintEmbedSession(
                $session,
                $user,
                $zoxAgent->meetingRoleFor($user, $isInstructor),
            );
        } catch (ZoxAgentApiException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        if ($isStudent && $user->academicStudent) {
            $zoxAgent->markStudentJoined($session, $user->academicStudent);
        }

        $roomUrl = $payload['roomUrl'] ?? null;
        abort_unless(filled($roomUrl), 404);

        $separator = str_contains($roomUrl, '?') ? '&' : '?';
        $roomUrlWithLeave = $roomUrl.$separator.'leaveUrl='.urlencode($returnUrl);

        if (ZoxAgentSettings::prefersRedirectJoin()) {
            return redirect()->away($roomUrlWithLeave);
        }

        $iframe = $payload['iframe'] ?? [];

        return view('sessions.zoxagent-join', [
            'session' => $session,
            'embedUrl' => $roomUrlWithLeave,
            'iframeAllow' => $iframe['allow'] ?? 'camera; microphone; display-capture; autoplay; fullscreen',
            'returnUrl' => $returnUrl,
        ]);
    }
}
