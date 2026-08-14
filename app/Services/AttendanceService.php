<?php

namespace App\Services;

use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use App\Support\AttendanceOptions;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function setRecordStatus(
        AttendanceSession $session,
        int $studentId,
        string $status,
        User $recorder,
        bool $isOverride = false,
    ): AttendanceRecord {
        if (! array_key_exists($status, AttendanceOptions::recordStatuses())) {
            throw ValidationException::withMessages(['status' => 'حالة الحضور غير صالحة.']);
        }

        $belongsToSection = AcademicStudent::query()
            ->where('id', $studentId)
            ->where('section_id', $session->section_id)
            ->exists();

        if (! $belongsToSection) {
            throw ValidationException::withMessages(['student' => 'الطالب لا ينتمي لهذه الشعبة.']);
        }

        $existing = AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->where('student_id', $studentId)
            ->first();

        $source = 'manual';

        $isProviderRecord = in_array($existing?->source, ['teams_sync', 'zoom_sync'], true);

        if ($isOverride || ($isProviderRecord && $existing->status !== $status)) {
            $source = $isProviderRecord ? 'override' : 'manual';
        }

        return AttendanceRecord::query()->updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'source' => $source,
                'recorded_by' => $recorder->id,
            ],
        );
    }

    /** @return Collection<int, AttendanceRecord> */
    public function rosterForSession(AttendanceSession $session, AcademicSection $section): Collection
    {
        $records = AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->with('student')
            ->get()
            ->keyBy('student_id');

        return $section->students()
            ->orderBy('name_ar')
            ->get()
            ->map(function (AcademicStudent $student) use ($records, $session) {
                $record = $records->get($student->id);

                if ($record) {
                    return $record;
                }

                return (new AttendanceRecord([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => 'absent',
                ]))->setRelation('student', $student);
            });
    }

    public function markAllPresent(AttendanceSession $session, User $recorder): int
    {
        $section = $session->section;

        if (! $section) {
            return 0;
        }

        $count = 0;

        foreach ($section->students as $student) {
            $this->setRecordStatus($session, $student->id, 'present', $recorder);
            $count++;
        }

        return $count;
    }

    public function instructorMayEditRecord(User $user, ?AttendanceRecord $record): bool
    {
        if (in_array($record?->source, ['teams_sync', 'zoom_sync'], true) && $record->exists) {
            return $user->canInstructor('instructor.attendance.override');
        }

        return $user->canInstructor('instructor.attendance.record_manual');
    }
}
