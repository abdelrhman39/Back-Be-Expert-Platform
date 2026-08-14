<?php

namespace App\Services\Zoom;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ZoomMeeting;
use App\Support\ZoomSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ZoomAttendanceSyncService
{
    public function __construct(private readonly ZoomApiClient $api) {}

    public function syncDueSessions(): int
    {
        if (! ZoomSettings::enabled() || ! ZoomSettings::autoAttendance()) {
            return 0;
        }

        $meetings = ZoomMeeting::query()
            ->whereHas('session', fn ($query) => $query->whereDate('session_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('attendance_synced_at')
                ->orWhere('attendance_synced_at', '<', now()->subMinutes(ZoomSettings::syncInterval())))
            ->with('session.section.students.user')
            ->limit(25)
            ->get();

        $count = 0;
        foreach ($meetings as $meeting) {
            if ($this->syncMeeting($meeting)) {
                $count++;
            }
        }

        return $count;
    }

    public function syncMeeting(ZoomMeeting $meeting): bool
    {
        $meeting->loadMissing(['session.section.students.user', 'registrants']);
        $identifier = $meeting->meeting_uuid ?: $meeting->meeting_id;
        $participants = $this->api->paginate(
            '/past_meetings/'.$this->pastMeetingIdentifier($identifier).'/participants',
            [],
            'participants',
        );

        $students = $meeting->session->section->students;
        $registrantMap = $meeting->registrants->whereNotNull('registrant_id')->keyBy('registrant_id');
        $emailMap = [];
        foreach ($students as $student) {
            foreach (array_filter([$student->email, $student->user?->email]) as $email) {
                $emailMap[strtolower($email)] = $student->id;
            }
        }

        $aggregated = [];
        foreach ($participants as $participant) {
            $registrantIds = array_filter([
                (string) ($participant['registrant_id'] ?? ''),
                (string) ($participant['id'] ?? ''),
            ]);
            $studentId = null;
            foreach ($registrantIds as $registrantId) {
                $studentId = $registrantMap->get($registrantId)?->student_id;
                if ($studentId) {
                    break;
                }
            }
            $email = strtolower((string) ($participant['user_email'] ?? $participant['email'] ?? ''));
            $studentId ??= $emailMap[$email] ?? null;
            if (! $studentId) {
                continue;
            }

            $join = $this->date($participant['join_time'] ?? null);
            $leave = $this->date($participant['leave_time'] ?? null);
            $entry = $aggregated[$studentId] ?? [
                'seconds' => 0, 'joined_at' => null, 'left_at' => null,
                'participant_id' => null, 'segments' => [],
            ];
            $entry['seconds'] += max(0, (int) ($participant['duration'] ?? ($join && $leave ? $join->diffInSeconds($leave) : 0)));
            $entry['joined_at'] = ! $entry['joined_at'] || ($join && $join->lt($entry['joined_at'])) ? $join : $entry['joined_at'];
            $entry['left_at'] = ! $entry['left_at'] || ($leave && $leave->gt($entry['left_at'])) ? $leave : $entry['left_at'];
            $entry['participant_id'] ??= $participant['id'] ?? $participant['user_id'] ?? null;
            $entry['segments'][] = [
                'join_time' => $participant['join_time'] ?? null,
                'leave_time' => $participant['leave_time'] ?? null,
                'duration' => $participant['duration'] ?? null,
            ];
            $aggregated[$studentId] = $entry;
        }

        DB::transaction(function () use ($meeting, $students, $aggregated): void {
            foreach ($students as $student) {
                $existing = AttendanceRecord::query()
                    ->where('attendance_session_id', $meeting->attendance_session_id)
                    ->where('student_id', $student->id)
                    ->first();
                if (
                    $existing?->source === 'override'
                    || ($existing?->source === 'manual' && $existing->recorded_by !== null)
                ) {
                    continue;
                }

                $attendance = $aggregated[$student->id] ?? null;
                AttendanceRecord::query()->updateOrCreate(
                    [
                        'attendance_session_id' => $meeting->attendance_session_id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => $attendance ? $this->status($meeting->session, $attendance) : 'absent',
                        'source' => 'zoom_sync',
                        'provider' => 'zoom',
                        'external_participant_id' => $attendance['participant_id'] ?? null,
                        'attendance_seconds' => $attendance['seconds'] ?? 0,
                        'joined_at' => $attendance['joined_at'] ?? null,
                        'left_at' => $attendance['left_at'] ?? null,
                        'provider_payload' => $attendance ? ['segments' => $attendance['segments']] : null,
                        'notes' => 'مزامنة تلقائية من Zoom',
                    ],
                );
            }

            $meeting->update(['attendance_synced_at' => now(), 'last_synced_at' => now()]);
            if ($meeting->session->status === 'scheduled') {
                $meeting->session->update(['status' => 'completed']);
            }
        });

        return true;
    }

    public function pastMeetingIdentifier(string $identifier): string
    {
        $encoded = rawurlencode($identifier);

        return str_starts_with($identifier, '/') || str_contains($identifier, '//')
            ? rawurlencode($encoded)
            : $encoded;
    }

    private function status(AttendanceSession $session, array $attendance): string
    {
        $start = $session->startsAt();
        $end = $session->endsAt();
        $scheduledSeconds = $start && $end ? max(60, $start->diffInSeconds($end)) : 0;
        $required = max(
            ZoomSettings::minimumAttendanceMinutes() * 60,
            (int) ceil($scheduledSeconds * ZoomSettings::minimumAttendancePercent() / 100),
        );
        if ($attendance['seconds'] <= 0 || $attendance['seconds'] < $required) {
            return 'absent';
        }
        if ($start && $attendance['joined_at']?->gt($start->copy()->addMinutes(ZoomSettings::lateMinutes()))) {
            return 'late';
        }

        return 'present';
    }

    private function date(mixed $value): ?Carbon
    {
        try {
            return filled($value) ? Carbon::parse($value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
