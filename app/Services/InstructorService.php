<?php

namespace App\Services;

use App\Models\AcademicSection;
use App\Models\AcademicStaff;
use App\Models\AcademicStudent;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Support\InstructorPermissions;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InstructorService
{
    public function resolveStaff(User $user): ?AcademicStaff
    {
        if (
            ! $user->isInstructor()
            || $user->status !== 'active'
            || $user->isLocked()
        ) {
            return null;
        }

        $staff = $user->academicStaff;

        return $staff?->status === 'active' ? $staff : null;
    }

    public function canAccessSection(User $user, AcademicSection $section): bool
    {
        if (! $user->isInstructor()) {
            return false;
        }

        $staff = $this->resolveStaff($user);

        if (! $staff) {
            return false;
        }

        return AcademicSection::query()
            ->whereKey($section->id)
            ->whereHas('schedule', fn ($q) => $q->where('staff_id', $staff->id))
            ->exists();
    }

    public function canAccessSession(User $user, AttendanceSession $session): bool
    {
        $session->loadMissing('section');

        return $session->section && $this->canAccessSection($user, $session->section);
    }

    public function authorizeSection(User $user, AcademicSection $section): void
    {
        abort_unless($this->canAccessSection($user, $section), 403);
    }

    public function authorizeSession(User $user, AttendanceSession $session): void
    {
        abort_unless($this->canAccessSession($user, $session), 403);
    }

    public function authorizePermission(User $user, string $permission): void
    {
        abort_unless(InstructorPermissions::can($user, $permission), 403);
    }

    /** @return Collection<int, AcademicSection> */
    public function sectionsFor(User $user): Collection
    {
        $staff = $this->resolveStaff($user);

        if (! $staff) {
            return collect();
        }

        return AcademicSection::query()
            ->with(['course', 'program', 'batch', 'schedule'])
            ->withCount('students')
            ->whereHas('schedule', fn ($q) => $q->where('staff_id', $staff->id))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, AttendanceSession> */
    public function sessionsForSection(AcademicSection $section): Collection
    {
        return AttendanceSession::query()
            ->with(['recording'])
            ->withCount(['materials', 'assignments'])
            ->where('section_id', $section->id)
            ->orderByDesc('session_date')
            ->orderByDesc('time_start')
            ->get();
    }

    /** @return Collection<int, AttendanceSession> */
    public function upcomingSessionsFor(User $user, int $limit = 5): Collection
    {
        $sectionIds = $this->sectionsFor($user)->pluck('id');

        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return AttendanceSession::query()
            ->with(['section.course'])
            ->whereIn('section_id', $sectionIds)
            ->where('session_date', '>=', now()->toDateString())
            ->orderBy('session_date')
            ->orderBy('time_start')
            ->limit($limit)
            ->get();
    }

    /** @return array{sections: int, sessions_week: int, pending_grades: int, pending_exams: int, students: int, live_now: int, today: int} */
    public function dashboardStats(User $user): array
    {
        $sections = $this->sectionsFor($user);
        $sectionIds = $sections->pluck('id');

        $sessionsThisWeek = 0;
        $pendingGrades = 0;
        $pendingExams = 0;
        $liveNow = 0;
        $today = 0;

        if ($sectionIds->isNotEmpty()) {
            $weekStart = now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $weekEnd = now()->endOfWeek(Carbon::SATURDAY)->toDateString();

            $sessionsThisWeek = AttendanceSession::query()
                ->whereIn('section_id', $sectionIds)
                ->whereBetween('session_date', [$weekStart, $weekEnd])
                ->count();

            $pendingGrades = AssignmentSubmission::query()
                ->whereIn('status', ['submitted', 'late'])
                ->whereHas('assignment', fn ($q) => $q->whereIn('section_id', $sectionIds))
                ->count();

            $pendingExams = \App\Models\ExamAttempt::query()
                ->where('status', 'pending_grading')
                ->whereHas('exam', fn ($q) => $q->whereIn('section_id', $sectionIds))
                ->count();

            $todaySessions = AttendanceSession::query()
                ->whereIn('section_id', $sectionIds)
                ->whereDate('session_date', today())
                ->with(['section'])
                ->get();

            $today = $todaySessions->count();
            $timing = app(AcademicSessionService::class);
            $liveNow = $todaySessions->filter(fn ($session) => $timing->resolveTiming($session)['state'] === 'live')->count();
        }

        return [
            'sections' => $sections->count(),
            'sessions_week' => $sessionsThisWeek,
            'pending_grades' => $pendingGrades,
            'pending_exams' => $pendingExams,
            'students' => (int) $sections->sum('students_count'),
            'live_now' => $liveNow,
            'today' => $today,
        ];
    }

    /** @return Collection<int, AttendanceSession> */
    public function todaySessionsFor(User $user): Collection
    {
        $sectionIds = $this->sectionsFor($user)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return AttendanceSession::query()
            ->with(['section.course', 'recording'])
            ->whereIn('section_id', $sectionIds)
            ->whereDate('session_date', today())
            ->orderBy('time_start')
            ->get();
    }

    /** @return Collection<int, AssignmentSubmission> */
    public function pendingAssignmentSubmissionsFor(User $user, int $limit = 50): Collection
    {
        $sectionIds = $this->sectionsFor($user)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return AssignmentSubmission::query()
            ->with(['assignment.section.course', 'student'])
            ->whereIn('status', ['submitted', 'late'])
            ->whereHas('assignment', fn ($q) => $q->whereIn('section_id', $sectionIds))
            ->latest('submitted_at')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, \App\Models\ExamAttempt> */
    public function pendingExamAttemptsFor(User $user, int $limit = 30): Collection
    {
        $sectionIds = $this->sectionsFor($user)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return \App\Models\ExamAttempt::query()
            ->with(['exam.section', 'student'])
            ->where('status', 'pending_grading')
            ->whereHas('exam', fn ($q) => $q->whereIn('section_id', $sectionIds))
            ->latest('submitted_at')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, AcademicStudent> */
    public function rosterForSection(AcademicSection $section): Collection
    {
        return AcademicStudent::query()
            ->with('user:id,name,name_ar,email,phone,status')
            ->where('section_id', $section->id)
            ->orderBy('name_ar')
            ->get();
    }

    /** @return Collection<int, AttendanceSession> */
    public function recentSessionsForAttendance(User $user, int $limit = 20): Collection
    {
        $sectionIds = $this->sectionsFor($user)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return AttendanceSession::query()
            ->with(['section.course'])
            ->withCount([
                'records as present_count' => fn ($q) => $q->whereIn('status', ['present', 'late']),
                'records as absent_count' => fn ($q) => $q->where('status', 'absent'),
                'records as excused_count' => fn ($q) => $q->where('status', 'excused'),
                'records as records_count',
            ])
            ->whereIn('section_id', $sectionIds)
            ->where('session_date', '<=', now()->toDateString())
            ->orderByDesc('session_date')
            ->orderByDesc('time_start')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, Assignment> */
    public function assignmentsForSession(AttendanceSession $session): Collection
    {
        return Assignment::query()
            ->withCount([
                'submissions',
                'submissions as graded_count' => fn ($q) => $q->where('status', 'graded'),
                'submissions as pending_count' => fn ($q) => $q->whereIn('status', ['submitted', 'late']),
            ])
            ->where('section_id', $session->section_id)
            ->where(function ($q) use ($session) {
                $q->where('attendance_session_id', $session->id)
                    ->orWhere(function ($q) {
                        $q->where('scope', 'section')->whereNull('attendance_session_id');
                    });
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
