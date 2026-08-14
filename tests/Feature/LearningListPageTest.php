<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LearningListPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_list_shows_academic_program_card_for_enrolled_student(): void
    {
        $user = User::query()->create([
            'name' => 'Learning Student',
            'name_ar' => 'طالب قائمة التعلم',
            'email' => 'learning-list@example.test',
            'password' => 'password123',
            'role' => 'student',
            'status' => 'active',
        ]);

        $program = AcademicProgram::query()->create([
            'name_ar' => 'دبلوم قائمة التعلم',
            'code' => 'LEARN-DIP',
            'type' => 'diploma',
            'status' => 'active',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة قائمة التعلم',
            'code' => 'LEARN-BATCH',
            'status' => 'active',
        ]);
        $course = AcademicCourse::query()->create([
            'program_id' => $program->id,
            'name_ar' => 'مقرر قائمة التعلم',
            'code' => 'LEARN-101',
            'credit_hours' => 3,
            'status' => 'active',
        ]);
        $section = AcademicSection::query()->create([
            'batch_id' => $batch->id,
            'program_id' => $program->id,
            'course_id' => $course->id,
            'name' => 'شعبة قائمة التعلم',
            'code' => 'LEARN-SEC',
            'status' => 'active',
        ]);

        AcademicStudent::query()->create([
            'user_id' => $user->id,
            'batch_id' => $batch->id,
            'section_id' => $section->id,
            'name_ar' => 'طالب قائمة التعلم',
            'academic_status' => 'studying',
            'login_allowed' => true,
        ]);

        Livewire::actingAs($user)
            ->test('student.learning-list-page')
            ->assertSee('برامجك الأكاديمية')
            ->assertSee('دبلوم قائمة التعلم')
            ->assertSee('منهج البرنامج')
            ->assertDontSee('لا توجد برامج أو دورات في قائمة التعلم بعد');
    }

    public function test_learning_list_shows_empty_state_without_academic_or_catalog_items(): void
    {
        $user = User::query()->create([
            'name' => 'Empty Learning Student',
            'name_ar' => 'طالب فارغ',
            'email' => 'learning-empty@example.test',
            'password' => 'password123',
            'role' => 'student',
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test('student.learning-list-page')
            ->assertSee('لا توجد برامج أو دورات في قائمة التعلم بعد')
            ->assertDontSee('id="academic-learning-heading"', false)
            ->assertDontSee('منهج البرنامج');
    }
}
