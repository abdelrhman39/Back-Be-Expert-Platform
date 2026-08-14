<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\Exam;
use App\Models\ExamAccommodation;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Services\ExamAttemptService;
use App\Services\ExamGradingService;
use App\Services\ExamIntegrityService;
use App\Services\ExamPublicationService;
use App\Services\ExamQuestionAuthoringService;
use App\Services\ExamQuestionBankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExamLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_attempt_is_snapshotted_auto_graded_then_manually_completed(): void
    {
        $user = User::query()->create([
            'name' => 'Student',
            'name_ar' => 'طالب اختبار',
            'email' => 'exam-student@example.test',
            'password' => 'password123',
            'role' => 'student',
            'status' => 'active',
        ]);
        $instructor = User::query()->create([
            'name' => 'Instructor',
            'name_ar' => 'مدرب اختبار',
            'email' => 'exam-instructor@example.test',
            'password' => 'password123',
            'role' => 'instructor',
            'status' => 'active',
        ]);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج الاختبار',
            'code' => 'EXAM-PROGRAM',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة الاختبار',
            'code' => 'EXAM-BATCH',
        ]);
        $course = AcademicCourse::query()->create([
            'program_id' => $program->id,
            'name_ar' => 'مقرر الاختبار',
            'code' => 'EXAM-101',
            'credit_hours' => 3,
            'status' => 'active',
        ]);
        $section = AcademicSection::query()->create([
            'batch_id' => $batch->id,
            'program_id' => $program->id,
            'course_id' => $course->id,
            'name' => 'شعبة الاختبار',
            'code' => 'EXAM-SEC-1',
            'status' => 'active',
        ]);
        $student = AcademicStudent::query()->create([
            'user_id' => $user->id,
            'batch_id' => $batch->id,
            'section_id' => $section->id,
            'name_ar' => 'طالب اختبار',
            'academic_status' => 'studying',
        ]);
        $exam = Exam::query()->create([
            'section_id' => $section->id,
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'title' => 'اختبار دورة الحياة',
            'status' => 'published',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'passing_percent' => 60,
            'published_at' => now(),
        ]);
        $part = $exam->parts()->create([
            'title' => 'الأسئلة',
            'sort_order' => 1,
        ]);

        $choice = ExamQuestion::query()->create([
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'type' => 'single_choice',
            'prompt' => 'اختر الإجابة الصحيحة',
            'default_points' => 2,
            'difficulty' => 'easy',
            'scope' => 'course',
            'status' => 'published',
            'answer_key' => ['correct' => 'A'],
            'published_at' => now(),
        ]);
        $choice->options()->createMany([
            ['option_key' => 'A', 'content' => 'الصحيحة', 'is_correct' => true, 'sort_order' => 1],
            ['option_key' => 'B', 'content' => 'الخاطئة', 'is_correct' => false, 'sort_order' => 2],
        ]);
        $essay = ExamQuestion::query()->create([
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'type' => 'essay',
            'prompt' => 'اشرح إجابتك',
            'default_points' => 3,
            'difficulty' => 'medium',
            'scope' => 'course',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $part->questionLinks()->createMany([
            ['question_id' => $choice->id, 'points' => 2, 'sort_order' => 1],
            ['question_id' => $essay->id, 'points' => 3, 'sort_order' => 2],
        ]);
        $exam->refreshTotalPoints();
        $exam->snapshotCandidates();

        $attempts = app(ExamAttemptService::class);
        $attempt = $attempts->start($exam->fresh(), $student);

        $this->assertCount(2, $attempt->question_snapshot);
        $this->assertArrayNotHasKey('answer_key', $attempt->question_snapshot[0]);

        $attempts->saveAnswer($attempt, $choice->id, ['value' => 'A']);
        $attempts->saveAnswer($attempt, $essay->id, null, 'إجابة الطالب');
        $attempt = $attempts->submit($attempt);

        $this->assertSame('pending_grading', $attempt->status);
        $this->assertSame('2.00', $attempt->auto_score);
        $this->assertNull($attempt->total_score);

        $essayAnswer = $attempt->answers->firstWhere('question_id', $essay->id);
        $attempt = app(ExamGradingService::class)->gradeAnswer(
            $essayAnswer,
            3,
            'إجابة مكتملة',
            $instructor,
        );

        $this->assertSame('graded', $attempt->status);
        $this->assertSame('5.00', $attempt->total_score);
        $this->assertSame('100.00', $attempt->percentage);
        $this->assertTrue($attempt->passed);

        $secondExam = Exam::query()->create([
            'section_id' => $section->id,
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'title' => 'اختبار آخر',
            'status' => 'draft',
        ]);
        $secondPart = $secondExam->parts()->create(['title' => 'الأسئلة', 'sort_order' => 1]);
        $secondPart->questionLinks()->create([
            'question_id' => $choice->id,
            'points' => 2,
            'sort_order' => 1,
        ]);
        $firstLink = $part->questionLinks()->where('question_id', $choice->id)->firstOrFail();

        $updatedQuestion = app(ExamQuestionAuthoringService::class)->updateAttached(
            exam: $exam,
            link: $firstLink,
            actor: $instructor,
            type: 'single_choice',
            prompt: 'نص معدل للاختبار الأول فقط',
            explanation: null,
            difficulty: 'medium',
            points: 4,
            options: [
                ['content' => 'الصحيحة الجديدة', 'correct' => false],
                ['content' => 'الخاطئة', 'correct' => false],
            ],
            correctScalar: '0',
        );

        $this->assertNotSame($choice->id, $updatedQuestion->id);
        $this->assertSame($choice->id, $secondPart->questionLinks()->first()->question_id);
        $this->assertSame($updatedQuestion->id, $firstLink->fresh()->question_id);
        $this->assertSame('اختر الإجابة الصحيحة', $attempt->fresh()->question_snapshot[0]['prompt']);

        $randomExam = Exam::query()->create([
            'section_id' => $section->id,
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'title' => 'اختبار عشوائي',
            'status' => 'published',
            'max_attempts' => 1,
            'published_at' => now(),
        ]);
        $randomPart = $randomExam->parts()->create(['title' => 'الأسئلة', 'sort_order' => 1]);
        $randomPart->questionLinks()->create([
            'question_id' => $essay->id,
            'points' => 3,
            'sort_order' => 1,
        ]);
        $authoring = app(ExamQuestionAuthoringService::class);

        foreach (range(1, 3) as $number) {
            $authoring->createForBank(
                exam: $randomExam,
                actor: $instructor,
                type: 'true_false',
                prompt: 'سؤال عشوائي '.$number,
                explanation: null,
                difficulty: 'medium',
                points: 1.5,
                correctScalar: 'true',
            );
        }

        $bank = app(ExamQuestionBankService::class);
        $candidateCount = $bank->configureRandomPool(
            $randomExam,
            $randomPart,
            $instructor,
            count: 2,
            points: 1.5,
            type: 'true_false',
        );
        $publication = app(ExamPublicationService::class)->publish($randomExam, $instructor);
        ExamQuestion::query()
            ->where('course_id', $course->id)
            ->where('type', 'true_false')
            ->update(['prompt' => 'نص تغير بعد النشر']);
        $randomExam->update(['total_points' => 100, 'passing_percent' => 100, 'title' => 'عنوان تغير بعد النشر']);
        $randomAttempt = $attempts->start($randomExam->fresh(), $student);

        $this->assertSame(3, $candidateCount);
        $this->assertSame('6.00', $publication->total_points);
        $this->assertSame($publication->id, $randomAttempt->publication_id);
        $this->assertSame(6.0, $randomAttempt->effectiveTotalPoints());
        $this->assertSame(60.0, $randomAttempt->effectivePassingPercent());
        $this->assertSame('اختبار عشوائي', $randomAttempt->effectiveExamTitle());
        $this->assertCount(3, $randomAttempt->question_snapshot);
        $this->assertCount(
            2,
            collect($randomAttempt->question_snapshot)->where('source', 'random_pool')
        );
        $this->assertFalse(
            collect($randomAttempt->question_snapshot)->contains(
                fn (array $snapshot) => $snapshot['prompt'] === 'نص تغير بعد النشر'
            )
        );

        $attempts->submit($randomAttempt);
        $randomExam->update(['status' => 'closed']);
        ExamAccommodation::query()->create([
            'exam_id' => $randomExam->id,
            'student_id' => $student->id,
            'unlimited_attempts' => true,
            'override_exam_availability' => true,
            'opens_at' => now(),
            'approved_by' => $instructor->id,
        ]);
        $reopenedAttempt = $attempts->start($randomExam->fresh(), $student);

        $this->assertSame(2, $reopenedAttempt->attempt_number);
        $this->assertTrue($randomExam->fresh()->isAvailableFor($student));
        $this->assertNull($randomExam->fresh()->attemptLimitFor($student));

        $integrity = app(ExamIntegrityService::class);
        $integrity->record($reopenedAttempt, 'page_hidden', ['question_id' => $essay->id], '127.0.0.1');
        $integrity->record($reopenedAttempt, 'paste_attempt', ['question_id' => $essay->id], '127.0.0.1');
        $this->assertSame(3, $reopenedAttempt->fresh()->integrity_flags);
        $this->assertSame('medium', $integrity->risk(3)['key']);
        $integrity->review($reopenedAttempt, $instructor, 'cleared', 'تمت المراجعة');
        $this->assertSame('cleared', $reopenedAttempt->fresh()->integrity_review_status);
        $integrity->record($reopenedAttempt, 'copy_attempt', null, '127.0.0.1');
        $this->assertSame('unreviewed', $reopenedAttempt->fresh()->integrity_review_status);

        $csv = implode("\n", [
            'type,prompt,explanation,difficulty,points,category,tags,options,correct_answer,structured_answer,numeric_tolerance',
            'single_choice,"سؤال مستورد",,easy,2,"الوحدة الأولى","أساسيات||تجريبي","A:صحيح||B:خطأ",A,,0',
            'short_text,"سؤال نصي مستورد",,medium,1,"الوحدة الأولى",,,,نعم||صحيح,0',
        ]);
        $imported = $bank->importCsv(
            $randomExam,
            $instructor,
            UploadedFile::fake()->createWithContent('questions.csv', $csv),
            $authoring,
        );

        $this->assertSame(2, $imported);
        $this->assertDatabaseHas('exam_questions', ['prompt' => 'سؤال مستورد']);
        $this->assertDatabaseHas('exam_question_categories', ['name' => 'الوحدة الأولى']);
    }
}
