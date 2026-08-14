<?php

namespace App\Services\MicrosoftTeams;

use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\MicrosoftTeamsConnection;
use App\Support\TeamsSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TeamsAttendanceSyncService
{
    public function __construct(
        protected TeamsGraphClient $graph,
        protected TeamsMeetingService $meetings,
    ) {}

    public function syncDueSessions(): int
    {
        if (! TeamsSettings::isEnabled() || ! TeamsSettings::autoAttendanceEnabled()) {
            return 0;
        }

        $sessions = AttendanceSession::query()
            ->with(['section.students.user.microsoftTeamsConnection'])
            ->whereNotNull('teams_meeting_id')
            ->whereDate('session_date', '<=', today())
            ->where(function ($q) {
                $q->whereNull('teams_attendance_synced_at')
                    ->orWhere('teams_attendance_synced_at', '<', now()->subMinutes(TeamsSettings::syncIntervalMinutes()));
            })
            ->whereIn('status', ['scheduled', 'completed'])
            ->limit(25)
            ->get();

        $synced = 0;

        foreach ($sessions as $session) {
            if ($this->syncSession($session)) {
                $synced++;
            }
        }

        return $synced;
    }

    public function syncSession(AttendanceSession $session): bool
    {
        if (! $session->teams_meeting_id) {
            $this->meetings->ensureMeetingForSession($session);
            $session->refresh();
        }

        if (! $session->teams_meeting_id) {
            return false;
        }

        $organizerId = $session->teams_organizer_id ?: TeamsSettings::organizerUserId();

        if (! $organizerId) {
            return false;
        }

        $reports = $this->graph->get(
            "/users/{$organizerId}/onlineMeetings/{$session->teams_meeting_id}/attendanceReports"
        );

        if (! $reports || empty($reports['value'])) {
            if ($this->sessionEndedRecently($session)) {
                $session->update(['teams_attendance_synced_at' => now()]);
            }

            return false;
        }

        $reportId = $reports['value'][0]['id'] ?? null;

        if (! $reportId) {
            return false;
        }

        $records = $this->graph->get(
            "/users/{$organizerId}/onlineMeetings/{$session->teams_meeting_id}/attendanceReports/{$reportId}/attendanceRecords"
        );

        if (! $records || empty($records['value'])) {
            return false;
        }

        $students = $session->section?->students ?? collect();
        $emailMap = $this->buildStudentEmailMap($students);

        foreach ($records['value'] as $record) {
            $email = strtolower($record['emailAddress'] ?? '');

            if (! $email || ! isset($emailMap[$email])) {
                continue;
            }

            $studentId = $emailMap[$email];
            $totalSeconds = (int) ($record['totalAttendanceInSeconds'] ?? 0);
            $joinedAt = $this->firstJoinTime($record);

            $status = $this->resolveStatus($session, $totalSeconds, $joinedAt);

            AttendanceRecord::query()->updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $studentId,
                ],
                [
                    'status' => $status,
                    'source' => 'teams_sync',
                    'teams_attendance_seconds' => $totalSeconds,
                    'teams_joined_at' => $joinedAt,
                    'notes' => 'مزامنة تلقائية من Microsoft Teams',
                ],
            );

            MicrosoftTeamsConnection::query()
                ->whereHas('user.academicStudent', fn ($q) => $q->where('id', $studentId))
                ->update(['last_synced_at' => now()]);
        }

        $session->update([
            'teams_attendance_synced_at' => now(),
            'status' => $session->status === 'scheduled' ? 'completed' : $session->status,
        ]);

        return true;
    }

    /** @param Collection<int, AcademicStudent> $students @return array<string, int> */
    protected function buildStudentEmailMap(Collection $students): array
    {
        $map = [];

        foreach ($students as $student) {
            $emails = array_filter([
                strtolower($student->email ?? ''),
                strtolower($student->user?->email ?? ''),
                strtolower($student->user?->microsoftTeamsConnection?->microsoft_email ?? ''),
            ]);

            foreach ($emails as $email) {
                if ($email) {
                    $map[$email] = $student->id;
                }
            }
        }

        return $map;
    }

    /** @param array<string, mixed> $record */
    protected function firstJoinTime(array $record): ?Carbon
    {
        $intervals = $record['attendanceIntervals'] ?? [];

        if (empty($intervals[0]['joinDateTime'])) {
            return null;
        }

        try {
            return Carbon::parse($intervals[0]['joinDateTime']);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolveStatus(AttendanceSession $session, int $totalSeconds, ?Carbon $joinedAt): string
    {
        if ($totalSeconds <= 0) {
            return 'absent';
        }

        $lateThreshold = $this->sessionStart($session)->copy()->addMinutes(10);

        if ($joinedAt && $joinedAt->greaterThan($lateThreshold)) {
            return 'late';
        }

        return 'present';
    }

    protected function sessionStart(AttendanceSession $session): Carbon
    {
        $date = $session->session_date->toDateString();
        $time = $session->time_start ? substr((string) $session->time_start, 0, 8) : '08:00:00';

        return Carbon::parse("{$date} {$time}");
    }

    protected function sessionEndedRecently(AttendanceSession $session): bool
    {
        $end = $this->sessionStart($session);

        if ($session->time_end) {
            $date = $session->session_date->toDateString();
            $time = substr((string) $session->time_end, 0, 8);
            $end = Carbon::parse("{$date} {$time}");
        } else {
            $end = $end->copy()->addHours(2);
        }

        return now()->greaterThan($end);
    }
}
