<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicSessionOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sessions_are_ordered_by_session_number_then_date(): void
    {
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج ترتيب المحاضرات',
            'code' => 'SESSION-ORDER',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة ترتيب المحاضرات',
            'code' => 'SESSION-ORDER-BATCH',
        ]);
        $section = AcademicSection::query()->create([
            'batch_id' => $batch->id,
            'program_id' => $program->id,
            'name' => 'شعبة ترتيب المحاضرات',
            'code' => 'SESSION-ORDER-SECTION',
        ]);
        $student = AcademicStudent::query()->create([
            'batch_id' => $batch->id,
            'section_id' => $section->id,
            'name_ar' => 'طالب ترتيب المحاضرات',
            'academic_status' => 'studying',
        ]);

        foreach ([3, 1, 2] as $number) {
            AttendanceSession::query()->create([
                'section_id' => $section->id,
                'title' => 'محاضرة '.$number,
                'session_number' => $number,
                'session_date' => now()->addDays($number)->toDateString(),
                'time_start' => '18:00:00',
                'time_end' => '20:00:00',
                'status' => 'scheduled',
                'source' => 'manual',
                'published_at' => now(),
            ]);
        }

        $numbers = app(AcademicSessionService::class)
            ->forStudent($student)
            ->pluck('session_number')
            ->all();

        $this->assertSame([1, 2, 3], $numbers);
    }
}
