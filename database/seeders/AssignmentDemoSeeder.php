<?php

namespace Database\Seeders;

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AttendanceSession;
use App\Services\AssignmentService;
use Illuminate\Database\Seeder;

class AssignmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $section = AcademicSection::query()->whereHas('students')->first();

        if (! $section) {
            return;
        }

        $session = AttendanceSession::query()
            ->where('section_id', $section->id)
            ->orderByDesc('session_date')
            ->first();

        $assignment = Assignment::query()->updateOrCreate(
            [
                'section_id' => $section->id,
                'title' => 'واجب تجريبي — قراءة وتحليل',
            ],
            [
                'attendance_session_id' => $session?->id,
                'scope' => $session ? 'session' : 'section',
                'instructions' => "1. اقرأ الفصل المطلوب.\n2. لخّص النقاط الرئيسية في صفحة واحدة.\n3. أرفق ملف PDF أو Word.",
                'max_score' => 100,
                'due_at' => now()->addDays(7),
                'allow_late_submission' => true,
                'late_penalty_percent' => 10,
                'max_attempts' => 2,
                'max_files' => 3,
                'status' => 'draft',
            ],
        );

        app(AssignmentService::class)->publish($assignment);
    }
}
