<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\NotificationRule;
use App\Models\User;
use App\Support\NotificationTypes;
use Carbon\Carbon;

class LectureReminderService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function dispatch(): int
    {
        $sent = 0;
        $sent += $this->dispatchReminders();
        $sent += $this->dispatchLiveNow();

        return $sent;
    }

    protected function dispatchReminders(): int
    {
        $rules = NotificationRule::query()
            ->where('type', NotificationTypes::LECTURE_REMINDER)
            ->where('is_enabled', true)
            ->where('trigger_kind', 'before_event')
            ->whereNotNull('offset_minutes')
            ->get();

        if ($rules->isEmpty()) {
            return 0;
        }

        $sessions = $this->upcomingSessions();
        $sent = 0;

        foreach ($rules as $rule) {
            foreach ($sessions as $session) {
                $startsAt = $session->startsAt();

                if (! $startsAt || ! $this->isInReminderWindow($startsAt, (int) $rule->offset_minutes)) {
                    continue;
                }

                $sent += $this->notifySessionStudents($session, $rule, NotificationTypes::LECTURE_REMINDER);
            }
        }

        return $sent;
    }

    protected function dispatchLiveNow(): int
    {
        $rule = NotificationRule::query()
            ->where('type', NotificationTypes::LECTURE_LIVE_NOW)
            ->where('is_enabled', true)
            ->first();

        if (! $rule) {
            return 0;
        }

        $sent = 0;

        foreach ($this->liveSessions() as $session) {
            $sent += $this->notifySessionStudents($session, $rule, NotificationTypes::LECTURE_LIVE_NOW);
        }

        return $sent;
    }

    protected function notifySessionStudents(
        AttendanceSession $session,
        NotificationRule $rule,
        string $type,
    ): int {
        $session->loadMissing('section.course');
        $sectionId = (int) $session->section_id;
        $startsAt = $session->startsAt();
        $locale = app()->getLocale();
        $sessionUrl = route('sessions.show', ['locale' => $locale, 'session' => $session->id]);
        $joinUrl = app(AcademicSessionService::class)->joinUrl($session);

        if ($type === NotificationTypes::LECTURE_LIVE_NOW) {
            $title = 'المحاضرة جارية الآن';
            $body = '«'.$session->displayTitle().'» بدأت الآن. انضم للمحاضرة.';
            $actionUrl = $joinUrl ?: $sessionUrl;
            $icon = 'fa-circle-dot';
        } else {
            $title = 'تذكير: '.$session->displayTitle();
            $body = 'محاضرة «'.$session->displayTitle().'» تبدأ '.$startsAt?->translatedFormat('d M Y — H:i').'.';
            $actionUrl = $joinUrl ?: $sessionUrl;
            $icon = 'fa-chalkboard-user';
        }

        $count = 0;

        foreach ($this->notifications->usersForSection($sectionId) as $user) {
            if ($this->alreadySent($rule, $user, $session)) {
                continue;
            }

            $this->notifications->send(
                user: $user,
                type: $type,
                title: $title,
                body: $body,
                actionUrl: $actionUrl,
                icon: $icon,
                subject: $session,
                rule: $rule,
            );
            $count++;
        }

        return $count;
    }

    protected function alreadySent(NotificationRule $rule, User $user, AttendanceSession $session): bool
    {
        foreach ($rule->channelList() as $channel) {
            if ($this->notifications->wasDelivered($rule, $user, $session, $channel)) {
                return true;
            }
        }

        return false;
    }

    protected function isInReminderWindow(Carbon $startsAt, int $offsetMinutes): bool
    {
        $target = now()->addMinutes($offsetMinutes);

        return $startsAt->between($target->copy()->subMinutes(5), $target->copy()->addMinutes(5));
    }

    /** @return \Illuminate\Support\Collection<int, AttendanceSession> */
    protected function upcomingSessions()
    {
        return AttendanceSession::query()
            ->whereIn('status', ['scheduled'])
            ->whereDate('session_date', '>=', today())
            ->whereDate('session_date', '<=', today()->addDays(2))
            ->with('section.schedule')
            ->get()
            ->filter(fn (AttendanceSession $s) => $s->startsAt()?->isFuture());
    }

    /** @return \Illuminate\Support\Collection<int, AttendanceSession> */
    protected function liveSessions()
    {
        return AttendanceSession::query()
            ->whereIn('status', ['scheduled'])
            ->whereDate('session_date', '>=', today()->subDay())
            ->whereDate('session_date', '<=', today()->addDay())
            ->with('section.schedule')
            ->get()
            ->filter(function (AttendanceSession $session) {
                $startsAt = $session->startsAt();
                $endsAt = $session->endsAt();

                return $startsAt && $endsAt && now()->between($startsAt, $endsAt);
            });
    }
}
