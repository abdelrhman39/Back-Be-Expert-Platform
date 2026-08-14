<?php

namespace App\Services;

use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Services\Meetings\MeetingProviderManager;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AcademicSessionService
{
    /** @return Collection<int, AttendanceSession> */
    public function forStudent(?AcademicStudent $student): Collection
    {
        if (! $student?->section_id) {
            return collect();
        }

        return AttendanceSession::query()
            ->with(['section.course', 'section.schedule'])
            ->with(['publishedMaterials'])
            ->where('section_id', $student->section_id)
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('published_at')
            ->orderByRaw('CASE WHEN session_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('session_number')
            ->orderBy('session_date')
            ->orderBy('time_start')
            ->get();
    }

    public function nextSessionNumber(int $sectionId): int
    {
        return (int) AttendanceSession::query()
            ->where('section_id', $sectionId)
            ->max('session_number') + 1;
    }

    /**
     * @param  array{
     *     title?: ?string,
     *     session_number?: ?int,
     *     description?: ?string,
     *     session_date: string,
     *     time_start?: ?string,
     *     time_end?: ?string,
     *     meeting_url?: ?string,
     *     status?: string,
     *     notes?: ?string,
     *     published?: bool,
     *     source?: string,
     * }  $data
     */
    public function createForSection(AcademicSection $section, array $data, bool $ensureMeeting = true): AttendanceSession
    {
        $section->loadMissing(['schedule', 'students']);

        $sessionDate = Carbon::parse($data['session_date'])->toDateString();

        $exists = AttendanceSession::query()
            ->where('section_id', $section->id)
            ->whereDate('session_date', $sessionDate)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'sessionDate' => 'يوجد حصة مسجّلة لهذه الشعبة في نفس التاريخ.',
            ]);
        }

        $sessionNumber = isset($data['session_number']) && $data['session_number'] !== null && $data['session_number'] !== ''
            ? (int) $data['session_number']
            : $this->nextSessionNumber($section->id);

        $published = array_key_exists('published', $data) ? (bool) $data['published'] : true;

        $session = AttendanceSession::query()->create([
            'section_id' => $section->id,
            'schedule_id' => $section->schedule?->id,
            'title' => filled($data['title'] ?? null) ? trim((string) $data['title']) : ('الحصة '.$sessionNumber),
            'session_number' => $sessionNumber,
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'session_date' => $sessionDate,
            'time_start' => $data['time_start'] ?? $section->schedule?->time_start,
            'time_end' => $data['time_end'] ?? $section->schedule?->time_end,
            'meeting_url' => filled($data['meeting_url'] ?? null) ? trim((string) $data['meeting_url']) : null,
            'status' => $data['status'] ?? 'scheduled',
            'source' => $data['source'] ?? 'manual',
            'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
            'published_at' => $published ? now() : null,
        ]);

        $this->seedAbsentRecords($session, $section);

        if ($ensureMeeting) {
            app(MeetingProviderManager::class)->ensureMeetingForSession($session);
        }

        return $session->fresh(['section.course', 'zoomMeeting']) ?? $session;
    }

    /**
     * @param  array{
     *     section_id?: int,
     *     title?: ?string,
     *     session_number?: ?int,
     *     description?: ?string,
     *     session_date?: string,
     *     time_start?: ?string,
     *     time_end?: ?string,
     *     meeting_url?: ?string,
     *     status?: string,
     *     notes?: ?string,
     *     published?: bool,
     * }  $data
     */
    public function updateSession(AttendanceSession $session, array $data): AttendanceSession
    {
        $sectionId = (int) ($data['section_id'] ?? $session->section_id);
        $sessionDate = Carbon::parse($data['session_date'] ?? $session->session_date)->toDateString();

        $exists = AttendanceSession::query()
            ->where('section_id', $sectionId)
            ->whereDate('session_date', $sessionDate)
            ->where('id', '!=', $session->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'sessionDate' => 'يوجد حصة أخرى لهذه الشعبة في نفس التاريخ.',
            ]);
        }

        $payload = [
            'section_id' => $sectionId,
            'title' => array_key_exists('title', $data)
                ? (filled($data['title']) ? trim((string) $data['title']) : null)
                : $session->title,
            'session_number' => array_key_exists('session_number', $data)
                ? (filled($data['session_number']) ? (int) $data['session_number'] : null)
                : $session->session_number,
            'description' => array_key_exists('description', $data)
                ? (filled($data['description']) ? trim((string) $data['description']) : null)
                : $session->description,
            'session_date' => $sessionDate,
            'time_start' => array_key_exists('time_start', $data) ? ($data['time_start'] ?: null) : $session->time_start,
            'time_end' => array_key_exists('time_end', $data) ? ($data['time_end'] ?: null) : $session->time_end,
            'meeting_url' => array_key_exists('meeting_url', $data)
                ? (filled($data['meeting_url']) ? trim((string) $data['meeting_url']) : null)
                : $session->meeting_url,
            'status' => $data['status'] ?? $session->status,
            'notes' => array_key_exists('notes', $data)
                ? (filled($data['notes']) ? trim((string) $data['notes']) : null)
                : $session->notes,
        ];

        if (array_key_exists('published', $data)) {
            $payload['published_at'] = $data['published']
                ? ($session->published_at ?? now())
                : null;
        }

        if ($sectionId !== (int) $session->section_id) {
            $section = AcademicSection::query()->with('schedule')->find($sectionId);
            $payload['schedule_id'] = $section?->schedule?->id;
        }

        $session->update($payload);

        return $session->fresh(['section.course', 'zoomMeeting']) ?? $session;
    }

    public function deleteSession(AttendanceSession $session): void
    {
        $session->delete();
    }

    public function seedAbsentRecords(AttendanceSession $session, ?AcademicSection $section = null): void
    {
        if (! $section) {
            $session->loadMissing('section.students');
            $section = $session->section;
        } else {
            $section->loadMissing('students');
        }

        if (! $section) {
            return;
        }

        foreach ($section->students as $student) {
            AttendanceRecord::query()->firstOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'absent',
                    'source' => 'manual',
                ]
            );
        }
    }

    /** @return array{state: string, starts_at: ?Carbon, ends_at: ?Carbon} */
    public function resolveTiming(AttendanceSession $session): array
    {
        $session->loadMissing(['section.schedule']);

        $startsAt = $this->combineDateAndTime($session->session_date, $session->time_start ?? $session->section?->schedule?->time_start);
        $endsAt = $this->combineDateAndTime($session->session_date, $session->time_end ?? $session->section?->schedule?->time_end);

        if ($startsAt && $endsAt && $endsAt->lte($startsAt)) {
            $endsAt = $startsAt->copy()->addHours(2);
        }

        if ($session->status === 'cancelled') {
            return ['state' => 'cancelled', 'starts_at' => $startsAt, 'ends_at' => $endsAt];
        }

        if (! $startsAt || ! $endsAt) {
            return ['state' => $session->status === 'completed' ? 'completed' : 'scheduled', 'starts_at' => $startsAt, 'ends_at' => $endsAt];
        }

        $now = now();

        if ($now->between($startsAt, $endsAt)) {
            return ['state' => 'live', 'starts_at' => $startsAt, 'ends_at' => $endsAt];
        }

        if ($startsAt->isFuture()) {
            return ['state' => 'upcoming', 'starts_at' => $startsAt, 'ends_at' => $endsAt];
        }

        return ['state' => 'completed', 'starts_at' => $startsAt, 'ends_at' => $endsAt];
    }

    public function joinUrl(AttendanceSession $session): ?string
    {
        return $session->teams_join_web_url ?: $session->meeting_url ?: $session->section?->schedule?->meeting_url;
    }

    public function studentCanAccess(User $user, AttendanceSession $session): bool
    {
        $student = $user->academicStudent;

        return $student && $student->section_id === $session->section_id && $session->published_at;
    }

    public function generateUpcomingForSection(AcademicSection $section, int $weeks = 4): int
    {
        $section->loadMissing('schedule');

        $schedule = $section->schedule;

        if (! $schedule?->day_of_week || ! $schedule->time_start) {
            return 0;
        }

        $weekday = $this->dayToCarbon($schedule->day_of_week);

        if ($weekday === null) {
            return 0;
        }

        $created = 0;
        $startWeek = now()->startOfWeek(Carbon::SUNDAY);

        for ($week = 0; $week < $weeks; $week++) {
            $date = $startWeek->copy()->addWeeks($week)->addDays($weekday);

            if ($date->isPast() && ! $date->isToday()) {
                continue;
            }

            $exists = AttendanceSession::query()
                ->where('section_id', $section->id)
                ->whereDate('session_date', $date->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            $sessionNumber = (int) AttendanceSession::query()
                ->where('section_id', $section->id)
                ->max('session_number') + 1;

            $session = AttendanceSession::query()->create([
                'section_id' => $section->id,
                'schedule_id' => $schedule->id,
                'session_number' => $sessionNumber,
                'title' => 'محاضرة '.$sessionNumber,
                'session_date' => $date->toDateString(),
                'time_start' => $schedule->time_start,
                'time_end' => $schedule->time_end,
                'status' => 'scheduled',
                'source' => 'schedule',
                'published_at' => now(),
            ]);

            app(MeetingProviderManager::class)->ensureMeetingForSession($session);

            $created++;
        }

        return $created;
    }

    public function generateUpcomingForAllSections(int $weeks = 4): int
    {
        $total = 0;

        AcademicSection::query()
            ->with('schedule')
            ->whereHas('schedule')
            ->chunkById(50, function ($sections) use ($weeks, &$total) {
                foreach ($sections as $section) {
                    $total += $this->generateUpcomingForSection($section, $weeks);
                }
            });

        return $total;
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
