<?php

namespace Database\Seeders;

use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $sections = AcademicSection::query()
            ->with(['schedule', 'students'])
            ->whereHas('students')
            ->get();

        if ($sections->isEmpty()) {
            return;
        }

        $statusPool = ['present', 'present', 'present', 'present', 'present', 'present', 'late', 'absent', 'excused'];

        foreach ($sections as $section) {
            $students = $section->students;

            if ($students->isEmpty()) {
                continue;
            }

            $sessionCount = min(8, max(4, $students->count()));

            for ($i = 0; $i < $sessionCount; $i++) {
                $sessionDate = Carbon::now()->subWeeks($sessionCount - $i)->startOfWeek()->addDays(
                    match ($section->schedule?->day_of_week) {
                        'sunday' => 0,
                        'monday' => 1,
                        'tuesday' => 2,
                        'wednesday' => 3,
                        'thursday' => 4,
                        'friday' => 5,
                        'saturday' => 6,
                        default => 1,
                    }
                );

                $session = AttendanceSession::query()->updateOrCreate(
                    [
                        'section_id' => $section->id,
                        'session_date' => $sessionDate->toDateString(),
                    ],
                    [
                        'schedule_id' => $section->schedule?->id,
                        'title' => 'محاضرة '.($i + 1),
                        'session_number' => $i + 1,
                        'time_start' => $section->schedule?->time_start,
                        'time_end' => $section->schedule?->time_end,
                        'status' => 'completed',
                        'source' => 'manual',
                        'published_at' => $sessionDate,
                    ],
                );

                foreach ($students as $student) {
                    AttendanceRecord::query()->updateOrCreate(
                        [
                            'attendance_session_id' => $session->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'status' => $statusPool[array_rand($statusPool)],
                            'source' => 'manual',
                        ],
                    );
                }
            }
        }

        $orphanStudents = AcademicStudent::query()
            ->whereNotNull('section_id')
            ->whereDoesntHave('attendanceRecords')
            ->with('section.schedule')
            ->limit(5)
            ->get();

        foreach ($orphanStudents as $student) {
            if (! $student->section) {
                continue;
            }

            $session = AttendanceSession::query()->firstOrCreate(
                [
                    'section_id' => $student->section_id,
                    'session_date' => Carbon::now()->subWeek()->toDateString(),
                ],
                [
                    'schedule_id' => $student->section->schedule?->id,
                    'title' => 'محاضرة تجريبية',
                    'status' => 'completed',
                    'source' => 'manual',
                ],
            );

            AttendanceRecord::query()->firstOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'present',
                    'source' => 'manual',
                ],
            );
        }
    }
}
