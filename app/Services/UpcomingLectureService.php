<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UpcomingLectureService
{
    /** @return array<string, mixed>|null */
    public function forUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $candidates = collect();

        $student = $user->academicStudent()->with(['section.schedule', 'section.course'])->first();

        if ($student?->section_id) {
            $candidates = $candidates->merge(
                $this->sessionsForSection($student->section_id, $student->section)
            );
        }

        $best = $candidates
            ->filter(fn ($item) => $item['ends_at']->isFuture())
            ->sortBy('starts_at')
            ->first();

        if (! $best) {
            return null;
        }

        $now = now();

        if ($now->between($best['starts_at'], $best['ends_at'])) {
            $best['state'] = 'live';
        } elseif ($best['starts_at']->isFuture()) {
            $best['state'] = 'upcoming';
        } else {
            return null;
        }

        $best['starts_at_formatted'] = $best['starts_at']->translatedFormat('d M Y — H:i');
        $best['ends_at_formatted'] = $best['ends_at']->format('H:i');
        $best['countdown_minutes'] = max(0, (int) $now->diffInMinutes($best['starts_at'], false));

        return $best;
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function sessionsForSection(int $sectionId, $section): Collection
    {
        $items = collect();

        $dated = AttendanceSession::query()
            ->with('zoxAgentMeeting')
            ->where('section_id', $sectionId)
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereDate('session_date', '>=', today())
            ->orderBy('session_date')
            ->orderBy('time_start')
            ->get();

        foreach ($dated as $session) {
            $slot = $this->slotFromSession($session, $section);

            if ($slot) {
                $items->push($slot);
            }
        }

        $schedule = $section->schedule;

        if ($schedule?->day_of_week && $schedule->time_start && $schedule->time_end) {
            for ($week = 0; $week < 3; $week++) {
                $slot = $this->slotFromRecurringSchedule($schedule, $section, $week);

                if ($slot) {
                    $items->push($slot);
                }
            }
        }

        return $items;
    }

    /** @return array<string, mixed>|null */
    protected function slotFromSession(AttendanceSession $session, $section): ?array
    {
        if ($session->status === 'cancelled') {
            return null;
        }

        $startsAt = $this->combineDateAndTime($session->session_date, $session->time_start ?? $section->schedule?->time_start);
        $endsAt = $this->combineDateAndTime($session->session_date, $session->time_end ?? $section->schedule?->time_end);

        if (! $startsAt || ! $endsAt) {
            return null;
        }

        if ($endsAt->lte($startsAt)) {
            $endsAt = $startsAt->copy()->addHours(2);
        }

        $meetingUrl = $session->teams_join_web_url ?? $session->meeting_url ?? $section->schedule?->meeting_url;
        if ($session->zoxAgentMeeting?->room_code && \App\Support\ZoxAgentSettings::enabled()) {
            $meetingUrl = route('sessions.join', [
                'locale' => app()->getLocale(),
                'session' => $session->id,
            ]);
        }

        return $this->buildSlot(
            title: $session->displayTitle(),
            courseName: $section->course?->name_ar ?? $section->subtitle ?? $section->name,
            trainer: $section->schedule?->trainer_name ?? $section->supervisor,
            startsAt: $startsAt,
            endsAt: $endsAt,
            meetingUrl: $meetingUrl,
            source: 'session',
        );
    }

    /** @return array<string, mixed>|null */
    protected function slotFromRecurringSchedule($schedule, $section, int $weekOffset): ?array
    {
        $weekday = $this->dayToCarbon($schedule->day_of_week);

        if ($weekday === null) {
            return null;
        }

        $date = now()->startOfWeek(Carbon::SUNDAY)->addWeeks($weekOffset)->addDays($weekday);

        if ($date->isPast() && ! $date->isToday()) {
            return null;
        }

        $startsAt = $this->combineDateAndTime($date, $schedule->time_start);
        $endsAt = $this->combineDateAndTime($date, $schedule->time_end);

        if (! $startsAt || ! $endsAt) {
            return null;
        }

        if ($endsAt->lte($startsAt)) {
            $endsAt = $startsAt->copy()->addHours(2);
        }

        return $this->buildSlot(
            title: 'محاضرة '.($section->course?->name_ar ?? $section->subtitle ?? 'الشعبة'),
            courseName: $section->course?->name_ar ?? $section->subtitle ?? $section->name,
            trainer: $schedule->trainer_name ?? $section->supervisor,
            startsAt: $startsAt,
            endsAt: $endsAt,
            meetingUrl: $schedule->meeting_url,
            source: 'schedule',
        );
    }

    /** @return array<string, mixed> */
    protected function buildSlot(
        string $title,
        ?string $courseName,
        ?string $trainer,
        Carbon $startsAt,
        Carbon $endsAt,
        ?string $meetingUrl,
        string $source,
    ): array {
        return [
            'title' => $title,
            'course_name' => $courseName,
            'trainer' => $trainer,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'meeting_url' => $meetingUrl,
            'source' => $source,
            'state' => 'upcoming',
        ];
    }

    protected function combineDateAndTime(Carbon|string $date, ?string $time): ?Carbon
    {
        if (! $time) {
            return null;
        }

        $dateStr = $date instanceof Carbon ? $date->toDateString() : (string) $date;
        $timeStr = substr($time, 0, 5);

        return Carbon::parse($dateStr.' '.$timeStr);
    }

    protected function dayToCarbon(?string $day): ?int
    {
        return match ($day) {
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
            default => null,
        };
    }
}
