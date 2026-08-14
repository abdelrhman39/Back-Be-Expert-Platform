<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Services\ExamAttemptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ExamAttemptPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_attempt_limits_and_selected_grade_policy_are_enforced(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج سياسات المحاولات',
            'code' => 'ATTEMPT-POLICY',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة المحاولات',
            'code' => 'ATTEMPT-BATCH',
        ]);
        $course = AcademicCourse::query()->create([
            'program_id' => $program->id,
            'name_ar' => 'مقرر المحاولات',
            'code' => 'ATTEMPT-101',
            'status' => 'active',
        ]);
        $section = AcademicSection::query()->create([
            'batch_id' => $batch->id,
            'program_id' => $program->id,
            'course_id' => $course->id,
            'name' => 'شعبة المحاولات',
            'code' => 'ATTEMPT-SEC',
            'status' => 'active',
        ]);
        $student = AcademicStudent::query()->create([
            'batch_id' => $batch->id,
            'section_id' => $section->id,
            'name_ar' => 'طالب المحاولات',
            'academic_status' => 'studying',
        ]);
        $exam = Exam::query()->create([
            'section_id' => $section->id,
            'course_id' => $course->id,
            'created_by' => $admin->id,
            'title' => 'اختبار سياسة المحاولات',
            'type' => 'exam',
            'language_policy' => 'ar_only',
            'status' => 'published',
            'attempt_policy' => 'single',
            'max_attempts' => 5,
            'grade_selection' => 'highest',
            'total_points' => 100,
            'passing_percent' => 60,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'one_question_per_page' => false,
            'allow_back_navigation' => true,
            'require_access_code' => false,
            'result_release' => 'after_grading',
            'review_policy' => 'score_only',
        ]);
        $exam->candidates()->create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'batch_id' => $batch->id,
            'student_name' => $student->name_ar,
            'status' => 'eligible',
            'assigned_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test('admin.exam-form-page', ['exam' => $exam])
            ->assertSet('attemptPolicy', 'single')
            ->set('attemptPolicy', 'limited')
            ->set('maxAttempts', 3)
            ->set('gradeSelection', 'latest')
            ->set('reviewPolicy', 'correct_answers')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('limited', $exam->fresh()->attempt_policy);
        $this->assertSame(3, $exam->fresh()->max_attempts);
        $this->assertSame('latest', $exam->fresh()->grade_selection);
        $this->assertSame('correct_answers', $exam->fresh()->review_policy);

        $exam->refresh()->update([
            'attempt_policy' => 'single',
            'max_attempts' => 5,
            'grade_selection' => 'highest',
        ]);

        $service = app(ExamAttemptService::class);
        $first = $service->start($exam, $student);
        $first->update([
            'status' => 'graded',
            'total_score' => 80,
            'percentage' => 80,
            'passed' => true,
            'submitted_at' => now(),
            'graded_at' => now(),
        ]);

        $this->assertSame(1, $exam->attemptLimitFor($student));
        $this->assertSame('graded', $first->fresh()->status);
        $this->assertSame(1, $exam->attempts()->where('student_id', $student->id)->count());
        $this->assertSame('single', $exam->fresh()->attempt_policy);
        $this->assertSame(1, $exam->fresh()->attemptLimitFor($student));
        $this->assertStartIsRejected($service, $exam, $student, 'single');

        $exam->update(['attempt_policy' => 'limited', 'max_attempts' => 2]);
        $second = $service->start($exam->fresh(), $student);
        $second->update([
            'status' => 'graded',
            'total_score' => 50,
            'percentage' => 50,
            'passed' => false,
            'submitted_at' => now(),
            'graded_at' => now(),
        ]);

        $this->assertSame(2, $exam->fresh()->attemptLimitFor($student));
        $this->assertStartIsRejected($service, $exam->fresh(), $student, 'limited');
        $this->assertSame($first->id, $exam->fresh()->selectedAttemptFor($student)?->id);

        $exam->update(['grade_selection' => 'latest']);
        $this->assertSame($second->id, $exam->fresh()->selectedAttemptFor($student)?->id);
        $this->assertFalse($exam->fresh()->selectedAttemptFor($student)->passed);

        $exam->update(['attempt_policy' => 'unlimited']);
        $this->assertNull($exam->fresh()->attemptLimitFor($student));
        $third = $service->start($exam->fresh(), $student);
        $this->assertSame(3, $third->attempt_number);
    }

    public function test_student_correction_page_respects_snapshotted_review_policy(): void
    {
        $user = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج مراجعة الاختبار',
            'code' => 'REVIEW-POLICY',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة المراجعة',
            'code' => 'REVIEW-BATCH',
        ]);
        $course = AcademicCourse::query()->create([
            'program_id' => $program->id,
            'name_ar' => 'مقرر المراجعة',
            'code' => 'REVIEW-101',
            'status' => 'active',
        ]);
        $section = AcademicSection::query()->create([
            'batch_id' => $batch->id,
            'program_id' => $program->id,
            'course_id' => $course->id,
            'name' => 'شعبة المراجعة',
            'code' => 'REVIEW-SEC',
            'status' => 'active',
        ]);
        $student = AcademicStudent::query()->create([
            'user_id' => $user->id,
            'batch_id' => $batch->id,
            'section_id' => $section->id,
            'name_ar' => 'طالب المراجعة',
            'academic_status' => 'studying',
        ]);
        $exam = Exam::query()->create([
            'section_id' => $section->id,
            'course_id' => $course->id,
            'created_by' => $admin->id,
            'title' => 'اختبار مراجعة الإجابات',
            'status' => 'published',
            'total_points' => 1,
            'passing_percent' => 60,
            'result_release' => 'immediate',
            'review_policy' => 'correct_answers',
        ]);
        $exam->candidates()->create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'batch_id' => $batch->id,
            'student_name' => $student->name_ar,
            'status' => 'eligible',
            'assigned_at' => now(),
        ]);
        $question = ExamQuestion::query()->create([
            'course_id' => $course->id,
            'created_by' => $admin->id,
            'type' => 'single_choice',
            'prompt' => 'اختر الإجابة الصحيحة',
            'explanation' => 'لأن الخيار الأول هو الصحيح.',
            'default_points' => 1,
            'difficulty' => 'easy',
            'scope' => 'course',
            'status' => 'published',
            'answer_key' => ['correct' => 'A'],
        ]);
        $snapshot = [
            'id' => $question->id,
            'type' => 'single_choice',
            'prompt' => 'اختر الإجابة الصحيحة',
            'explanation' => 'لأن الخيار الأول هو الصحيح.',
            'points' => 1,
            'options' => [
                ['key' => 'A', 'content' => 'الخيار الصحيح'],
                ['key' => 'B', 'content' => 'الخيار الخاطئ'],
            ],
        ];
        $attempt = $exam->attempts()->create([
            'student_id' => $student->id,
            'attempt_number' => 1,
            'status' => 'graded',
            'started_at' => now()->subMinutes(5),
            'submitted_at' => now(),
            'graded_at' => now(),
            'total_score' => 0,
            'percentage' => 0,
            'passed' => false,
            'question_snapshot' => [$snapshot],
            'settings_snapshot' => [
                'review_policy' => 'score_only',
                'result_release' => 'immediate',
                'total_points' => 1,
                'passing_percent' => 60,
            ],
        ]);
        $attempt->answers()->create([
            'question_id' => $question->id,
            'answer' => ['value' => 'B'],
            'status' => 'graded',
            'is_correct' => false,
            'auto_score' => 0,
            'question_snapshot' => $snapshot,
            'grading_key' => ['correct' => 'A'],
            'graded_at' => now(),
        ]);

        $route = route('exam-attempts.review', ['locale' => 'ar', 'attempt' => $attempt]);

        $this->actingAs($user, 'portal')->get($route)->assertForbidden();

        $attempt->update(['settings_snapshot' => [
            ...$attempt->settings_snapshot,
            'review_policy' => 'answers',
        ]]);
        $this->actingAs($user, 'portal')
            ->get($route)
            ->assertOk()
            ->assertSee('إجاباتك ودرجاتها فقط')
            ->assertDontSee('إجابة خاطئة');

        $attempt->update(['settings_snapshot' => [
            ...$attempt->settings_snapshot,
            'review_policy' => 'correct_answers',
        ]]);
        $this->actingAs($user, 'portal')
            ->get($route)
            ->assertOk()
            ->assertSee('إجابة خاطئة')
            ->assertSee('الإجابة الصحيحة')
            ->assertSee('لأن الخيار الأول هو الصحيح.');

        $exam->update(['status' => 'closed']);
        $this->assertTrue($exam->fresh()->studentCanAccess($student));
        $this->actingAs($user, 'portal')
            ->get(route('exams.show', ['locale' => 'ar', 'exam' => $exam]))
            ->assertOk()
            ->assertSee('عرض التصحيح');
    }

    private function assertStartIsRejected(
        ExamAttemptService $service,
        Exam $exam,
        AcademicStudent $student,
        string $context,
    ): void {
        try {
            $service->start($exam, $student);
            $this->fail("Expected the {$context} attempt limit to reject a new attempt.");
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('exam', $exception->errors());
        }
    }
}
