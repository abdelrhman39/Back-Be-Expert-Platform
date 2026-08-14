<?php

namespace Database\Seeders;

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicSchedule;
use App\Models\AcademicSection;
use App\Models\AcademicStaff;
use App\Models\AcademicStudent;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\PlatformSetting;
use App\Models\SessionMaterial;
use App\Models\User;
use App\Services\ExamPublicationService;
use App\Support\PhoneNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * حزمة تجريبية كاملة: طالب + مدرب + دبلوم + محتوى + اختبار نهائي بأسئلة + مسار شهادة.
 *
 * الحسابات:
 * - الطالب: diploma.student@beexpert.test / Demo@123456
 * - المدرب: diploma.instructor@beexpert.test / Demo@123456
 */
class CompleteDiplomaDemoSeeder extends Seeder
{
    public const STUDENT_EMAIL = 'diploma.student@beexpert.test';

    public const INSTRUCTOR_EMAIL = 'diploma.instructor@beexpert.test';

    public const PASSWORD = 'Demo@123456';

    public const PROGRAM_CODE = 'DEMO-DIP-AI';

    public function run(): void
    {
        $this->call([
            CertificateTemplateSeeder::class,
            CertificateAccessSettingsSeeder::class,
        ]);

        // بعد اجتياز الاختبار النهائي تظهر الشهادة للطالب مباشرة (بدون انتظار تخرج يدوي).
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'certificate_default_visibility_mode'],
            [
                'value' => 'after_exam_pass',
                'group' => 'certificates',
                'label_ar' => 'شرط ظهور الشهادة الموحد',
                'type' => 'string',
            ],
        );
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'certificate_auto_issue_enabled'],
            [
                'value' => '1',
                'group' => 'certificates',
                'label_ar' => 'الإصدار التلقائي للشهادات',
                'type' => 'boolean',
            ],
        );
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'certificate_required_exam_type'],
            [
                'value' => 'final',
                'group' => 'certificates',
                'label_ar' => 'نوع الاختبار المطلوب لاجتياز الشهادة',
                'type' => 'string',
            ],
        );

        $instructor = User::query()->updateOrCreate(
            ['email' => self::INSTRUCTOR_EMAIL],
            [
                'name' => 'Demo Diploma Instructor',
                'name_ar' => 'أ. نورة المدربة التجريبية',
                'national_id' => '1088776655',
                'phone' => PhoneNormalizer::toE164('550001001'),
                'password' => Hash::make(self::PASSWORD),
                'locale' => 'ar',
                'status' => 'active',
                'role' => 'instructor',
                'email_verified_at' => now(),
            ],
        );

        $studentUser = User::query()->updateOrCreate(
            ['email' => self::STUDENT_EMAIL],
            [
                'name' => 'Demo Diploma Student',
                'name_ar' => 'محمد الطالب التجريبي',
                'national_id' => '1099001122',
                'phone' => PhoneNormalizer::toE164('550001002'),
                'password' => Hash::make(self::PASSWORD),
                'locale' => 'ar',
                'status' => 'active',
                'role' => 'student',
                'email_verified_at' => now(),
            ],
        );

        $staff = AcademicStaff::query()->updateOrCreate(
            ['name_ar' => 'أ. نورة المدربة التجريبية'],
            [
                'user_id' => $instructor->id,
                'name_en' => 'Noura Demo Instructor',
                'role' => 'instructor',
                'permission_preset' => 'instructor.lead',
                'specialty' => 'الذكاء الاصطناعي التطبيقي',
                'gender' => 'أنثى',
                'courses_count' => 1,
                'hours_per_week' => 8,
                'compensation_total' => 9000,
                'status' => 'active',
            ],
        );

        $program = AcademicProgram::query()->updateOrCreate(
            ['code' => self::PROGRAM_CODE],
            [
                'name_ar' => 'دبلوم الذكاء الاصطناعي التطبيقي (تجريبي)',
                'name_en' => 'Applied AI Diploma (Demo)',
                'name_on_certificate' => 'Diploma in Applied Artificial Intelligence',
                'symbol' => 'AI-DEMO',
                'duration_months' => 6,
                'duration_label' => '6 أشهر',
                'start_date' => now()->subMonths(1)->toDateString(),
                'status' => 'active',
                'type' => 'diploma',
                'coordinator' => 'أ. نورة المدربة التجريبية',
                'email' => 'ai-demo@beexpert.test',
                'phone' => PhoneNormalizer::toE164('550001000'),
                'city' => 'الرياض',
                'summary' => 'دبلوم تجريبي كامل لاختبار مسار الطالب والمدرب والاختبارات والشهادات.',
                'skills' => ['أساسيات الذكاء الاصطناعي', 'التعلم الآلي', 'تطبيقات عملية'],
                'study_status' => 'فعال — دفعة تجريبية',
            ],
        );

        $level = AcademicLevel::query()->updateOrCreate(
            [
                'program_id' => $program->id,
                'name_ar' => 'المستوى الأول',
            ],
            [
                'sort_order' => 1,
                'status' => 'active',
            ],
        );

        $course = AcademicCourse::query()->updateOrCreate(
            ['code' => 'DEMO-AI-101'],
            [
                'program_id' => $program->id,
                'level_id' => $level->id,
                'name_ar' => 'مقدمة في الذكاء الاصطناعي',
                'name_en' => 'Introduction to Artificial Intelligence',
                'symbol_ar' => 'ذكا 101',
                'symbol_en' => 'AI 101',
                'credit_hours' => 3,
                'status' => 'active',
                'target_group' => 'المتدربون التجريبيون',
                'summary' => 'مقرر تأسيسي يغطي مفاهيم الذكاء الاصطناعي والتعلم الآلي.',
                'added_by' => $instructor->id,
            ],
        );

        $batch = AcademicBatch::query()->updateOrCreate(
            ['code' => 'DEMO-AI-B1'],
            [
                'program_id' => $program->id,
                'name' => 'الدفعة التجريبية الأولى',
                'semester' => 'ربيع 2026',
                'semester_key' => '2026-spring',
                'start_date' => now()->subMonths(1)->toDateString(),
                'end_date' => now()->addMonths(5)->toDateString(),
                'students_count' => 1,
                'capacity' => 30,
                'tuition_amount' => 4500,
                'installment_allowed' => true,
                'study_mode' => 'online',
                'coordinator' => 'أ. نورة المدربة التجريبية',
                'enrollment_open' => true,
                'notes' => 'دفعة مخصصة للاختبار الداخلي للمنصة.',
                'status' => 'active',
            ],
        );

        $section = AcademicSection::query()->updateOrCreate(
            ['code' => 'DEMO-AI-SEC-1'],
            [
                'batch_id' => $batch->id,
                'program_id' => $program->id,
                'course_id' => $course->id,
                'level_id' => $level->id,
                'name' => 'شعبة الذكاء الاصطناعي التجريبية',
                'subtitle' => 'مقدمة في الذكاء الاصطناعي — مجموعة أ',
                'max_capacity' => 30,
                'students_count' => 1,
                'supervisor' => 'أ. نورة المدربة التجريبية',
                'period' => 'مسائي',
                'semester' => 'ربيع 2026',
                'semester_key' => '2026-spring',
                'status' => 'active',
                'added_by' => $instructor->id,
            ],
        );

        $schedule = AcademicSchedule::query()->updateOrCreate(
            ['section_id' => $section->id],
            [
                'batch_id' => $batch->id,
                'level_id' => $level->id,
                'semester_key' => '2026-spring',
                'period' => 'مسائي',
                'staff_id' => $staff->id,
                'trainer_name' => $staff->name_ar,
                'day_of_week' => 'الأحد',
                'time_start' => '18:00:00',
                'time_end' => '20:00:00',
                'meeting_url' => 'https://teams.microsoft.com/l/meetup-join/demo-ai-diploma',
            ],
        );

        AcademicStudent::query()->updateOrCreate(
            ['academic_id' => 'DEMO-STU-AI-001'],
            [
                'user_id' => $studentUser->id,
                'batch_id' => $batch->id,
                'section_id' => $section->id,
                'name_ar' => 'محمد الطالب التجريبي',
                'name_en' => 'Mohammed Demo Student',
                'national_id' => '1099001122',
                'mobile' => PhoneNormalizer::toE164('550001002'),
                'email' => self::STUDENT_EMAIL,
                'gender' => 'ذكر',
                'city' => 'الرياض',
                'nationality' => 'سعودي',
                'study_period' => 'مسائي',
                'study_status' => 'منتظم',
                'academic_status' => 'studying',
                'graduated_at' => null,
                'login_allowed' => true,
                'joined_at' => now()->subWeeks(2),
            ],
        );

        $batch->update(['students_count' => 1]);
        $section->update(['students_count' => 1]);

        $this->seedContent($section, $schedule, $instructor);
        $this->seedExam($section, $course, $instructor);

        $this->command?->info('تم تجهيز الدبلوم التجريبي.');
        $this->command?->info('الطالب: '.self::STUDENT_EMAIL.' / '.self::PASSWORD);
        $this->command?->info('المدرب: '.self::INSTRUCTOR_EMAIL.' / '.self::PASSWORD);
        $this->command?->info('الدبلوم: '.$program->name_ar.' ('.$program->code.')');
    }

    private function seedContent(AcademicSection $section, AcademicSchedule $schedule, User $instructor): void
    {
        $sessions = [
            [
                'session_number' => 1,
                'title' => 'محاضرة 1: ما هو الذكاء الاصطناعي؟',
                'description' => 'تعريف الذكاء الاصطناعي وتطبيقاته في الحياة والعمل.',
                'session_date' => now()->subWeeks(2)->toDateString(),
                'status' => 'completed',
                'materials' => [
                    [
                        'title' => 'شريحة التعريف بالذكاء الاصطناعي',
                        'external_url' => 'https://example.com/demo/ai-intro-slides',
                    ],
                    [
                        'title' => 'قراءة تأسيسية مختصرة',
                        'external_url' => 'https://example.com/demo/ai-intro-reading',
                    ],
                ],
            ],
            [
                'session_number' => 2,
                'title' => 'محاضرة 2: التعلم الآلي عملياً',
                'description' => 'مفاهيم supervised و unsupervised مع أمثلة مبسطة.',
                'session_date' => now()->subWeek()->toDateString(),
                'status' => 'completed',
                'materials' => [
                    [
                        'title' => 'ورقة عمل التعلم الآلي',
                        'external_url' => 'https://example.com/demo/ml-worksheet',
                    ],
                ],
            ],
            [
                'session_number' => 3,
                'title' => 'محاضرة 3: مراجعة قبل الاختبار النهائي',
                'description' => 'مراجعة شاملة للمفاهيم مع جلسة أسئلة وأجوبة.',
                'session_date' => now()->toDateString(),
                'status' => 'scheduled',
                'materials' => [
                    [
                        'title' => 'ملخص المراجعة النهائية',
                        'external_url' => 'https://example.com/demo/final-review',
                    ],
                ],
            ],
        ];

        foreach ($sessions as $payload) {
            $materials = $payload['materials'];
            unset($payload['materials']);

            $session = AttendanceSession::query()->updateOrCreate(
                [
                    'section_id' => $section->id,
                    'session_number' => $payload['session_number'],
                ],
                [
                    'schedule_id' => $schedule->id,
                    'title' => $payload['title'],
                    'description' => $payload['description'],
                    'session_date' => $payload['session_date'],
                    'time_start' => '18:00:00',
                    'time_end' => '20:00:00',
                    'meeting_url' => $schedule->meeting_url,
                    'status' => $payload['status'],
                    'source' => 'manual',
                    'published_at' => now(),
                ],
            );

            foreach ($materials as $index => $material) {
                SessionMaterial::query()->updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'title' => $material['title'],
                    ],
                    [
                        'type' => 'link',
                        'external_url' => $material['external_url'],
                        'sort_order' => $index + 1,
                        'visibility' => 'published',
                        'uploaded_by' => $instructor->id,
                        'published_at' => now(),
                    ],
                );
            }
        }
    }

    private function seedExam(AcademicSection $section, AcademicCourse $course, User $instructor): void
    {
        $exam = Exam::query()->updateOrCreate(
            [
                'section_id' => $section->id,
                'title' => 'الاختبار النهائي — دبلوم الذكاء الاصطناعي التجريبي',
            ],
            [
                'course_id' => $course->id,
                'created_by' => $instructor->id,
                'title_en' => 'Final Exam — Applied AI Demo Diploma',
                'instructions' => "اختبار نهائي تجريبي.\nأجب عن جميع الأسئلة. الاجتياز من 60٪ ويؤدي إلى أهلية الشهادة.",
                'instructions_en' => 'Demo final exam. Pass mark is 60% and unlocks the certificate.',
                'type' => 'final',
                'language_policy' => 'ar_only',
                'status' => 'draft',
                'opens_at' => now()->subDay(),
                'closes_at' => now()->addMonths(2),
                'duration_minutes' => 45,
                'max_attempts' => 3,
                'attempt_policy' => 'limited',
                'grade_selection' => 'highest',
                'passing_percent' => 60,
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'one_question_per_page' => false,
                'allow_back_navigation' => true,
                'require_access_code' => false,
                'result_release' => 'immediate',
                'review_policy' => 'correct_answers',
                'published_at' => null,
                'archived_at' => null,
            ],
        );

        $part = $exam->parts()->firstOrCreate(
            ['title' => 'أسئلة الاختبار النهائي'],
            [
                'sort_order' => 1,
                'shuffle_questions' => false,
            ],
        );

        $bank = [
            [
                'prompt' => 'ما المقصود بالذكاء الاصطناعي؟',
                'points' => 2,
                'options' => [
                    ['A', 'قدرة الأنظمة على محاكاة بعض قدرات التفكير البشري', true],
                    ['B', 'نوع من أجهزة الطباعة فقط', false],
                    ['C', 'نظام تشغيل للهواتف فقط', false],
                ],
            ],
            [
                'prompt' => 'التعلم الآلي هو فرع من فروع الذكاء الاصطناعي.',
                'points' => 2,
                'type' => 'true_false',
                'options' => [
                    ['true', 'صح', true],
                    ['false', 'خطأ', false],
                ],
            ],
            [
                'prompt' => 'أي مما يلي مثال على بيانات تدريب؟',
                'points' => 2,
                'options' => [
                    ['A', 'مجموعة صور مصنّفة مسبقاً', true],
                    ['B', 'شاشة فارغة بدون محتوى', false],
                    ['C', 'كابل شبكة فقط', false],
                ],
            ],
            [
                'prompt' => 'الهدف من الاختبار النهائي في هذه الدفعة التجريبية هو التحقق من مسار الشهادة.',
                'points' => 2,
                'type' => 'true_false',
                'options' => [
                    ['true', 'صح', true],
                    ['false', 'خطأ', false],
                ],
            ],
            [
                'prompt' => 'ما أفضل وصف للتعلم بإشراف (Supervised Learning)؟',
                'points' => 2,
                'options' => [
                    ['A', 'تعلم من بيانات مصنّفة بمخرجات معروفة', true],
                    ['B', 'تعلم بدون أي بيانات نهائياً', false],
                    ['C', 'طباعة الشهادات فقط', false],
                ],
            ],
        ];

        $links = [];
        foreach ($bank as $index => $item) {
            $type = $item['type'] ?? 'single_choice';
            $correct = collect($item['options'])->first(fn (array $opt) => $opt[2])[0];

            $question = ExamQuestion::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'prompt' => $item['prompt'],
                ],
                [
                    'created_by' => $instructor->id,
                    'type' => $type,
                    'default_points' => $item['points'],
                    'difficulty' => 'easy',
                    'scope' => 'course',
                    'status' => 'published',
                    'answer_key' => ['correct' => $correct],
                    'published_at' => now(),
                ],
            );

            $question->options()->delete();
            foreach ($item['options'] as $optIndex => $option) {
                $question->options()->create([
                    'option_key' => $option[0],
                    'content' => $option[1],
                    'is_correct' => $option[2],
                    'sort_order' => $optIndex + 1,
                ]);
            }

            $links[] = [
                'question_id' => $question->id,
                'points' => $item['points'],
                'sort_order' => $index + 1,
            ];
        }

        $part->questionLinks()->delete();
        $part->questionLinks()->createMany($links);
        $exam->refreshTotalPoints();

        if ($exam->status !== 'published' || $exam->publications()->doesntExist()) {
            app(ExamPublicationService::class)->publish($exam->fresh(), $instructor);
        } else {
            $exam->snapshotCandidates();
            $exam->update([
                'opens_at' => now()->subDay(),
                'closes_at' => now()->addMonths(2),
            ]);
        }
    }
}
