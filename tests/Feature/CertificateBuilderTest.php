<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Notifications\PlatformAlertNotification;
use App\Services\AutomaticCertificateIssuanceService;
use App\Services\CertificateService;
use App\Services\CertificateTemplateService;
use App\Support\CertificateAccessPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CertificateBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_template_library_and_visual_builder(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $service = app(CertificateTemplateService::class);
        $template = CertificateTemplate::query()->create([
            'name' => 'قالب الاختبار',
            'slug' => 'builder-test',
            'canvas_width' => 1123,
            'canvas_height' => 794,
            'orientation' => 'landscape',
            'elements' => $service->defaultElements(),
            'settings' => $service->defaultSettings(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.certificate-templates'))
            ->assertOk()
            ->assertSee('منشئ قوالب الشهادات');

        $this->actingAs($admin)
            ->get(route('admin.certificate-templates.builder', $template))
            ->assertOk()
            ->assertSee('محرر قالب الشهادة');

        $this->actingAs($admin)
            ->get(route('admin.certificates'))
            ->assertOk()
            ->assertSee('مركز إدارة الاعتمادات');
    }

    public function test_certificate_is_issued_from_an_immutable_template_snapshot_with_pdf_and_secure_verification(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $studentUser = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'الدبلوم المهني',
            'name_en' => 'Professional Diploma',
            'name_on_certificate' => 'الدبلوم المهني المتقدم',
            'code' => 'PD-01',
            'status' => 'active',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة 2026',
            'code' => 'B-26',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
        ]);
        $student = AcademicStudent::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $studentUser->id,
            'name_ar' => 'محمد أحمد',
            'name_en' => 'Mohammed Ahmed',
            'academic_id' => '1001',
            'national_id' => '1012345678',
            'academic_status' => 'graduated',
            'graduated_at' => '2026-06-30',
            'login_allowed' => true,
        ]);
        $templateService = app(CertificateTemplateService::class);
        $template = CertificateTemplate::query()->create([
            'name' => 'قالب الدبلوم',
            'slug' => 'diploma-template',
            'canvas_width' => 1123,
            'canvas_height' => 794,
            'orientation' => 'landscape',
            'elements' => $templateService->defaultElements(),
            'settings' => $templateService->defaultSettings(),
            'status' => 'active',
            'is_default' => true,
            'version' => 3,
        ]);

        $certificate = app(CertificateService::class)->issueForStudent(
            $student,
            null,
            $admin,
            $template,
        );

        $this->assertSame($template->id, $certificate->certificate_template_id);
        $this->assertSame(3, $certificate->template_version);
        $this->assertSame('الدبلوم المهني المتقدم', $certificate->program_name);
        $this->assertSame('1012345678', $certificate->data_snapshot['student.national_id']);
        $this->assertSame(3, $certificate->template_snapshot['version']);
        $this->assertNotNull($certificate->credential_hash);
        $this->assertStringContainsString($certificate->verify_token, $certificate->verifyUrl());
        $this->assertNotNull($certificate->pdf_path);
        Storage::disk('local')->assertExists($certificate->pdf_path);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($certificate->pdf_path));
        $this->assertTrue($certificate->hasValidIntegrity());

        $this->get($certificate->verifyUrl())
            ->assertOk()
            ->assertSee('شهادة أصلية وسارية');

        $this->actingAs($studentUser)
            ->get(route('certificates.download', ['locale' => 'ar', 'certificate' => $certificate]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $student->update(['academic_status' => 'eligible']);

        $this->actingAs($studentUser)
            ->get(route('certificates.show', ['locale' => 'ar', 'certificate' => $certificate]))
            ->assertNotFound();

        $this->actingAs($studentUser)
            ->get(route('certificates.download', ['locale' => 'ar', 'certificate' => $certificate]))
            ->assertForbidden();

        $student->update(['academic_status' => 'graduated']);

        $template->update(['elements' => []]);
        $this->assertNotEmpty($certificate->fresh()->template_snapshot['elements']);

        $tampered = $certificate->data_snapshot;
        $tampered['student.name_ar'] = 'اسم معدل';
        $certificate->update(['data_snapshot' => $tampered]);
        $this->assertFalse($certificate->fresh()->hasValidIntegrity());
    }

    public function test_template_must_be_disabled_before_it_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $service = app(CertificateTemplateService::class);
        $template = CertificateTemplate::query()->create([
            'name' => 'قالب قابل للحذف',
            'slug' => 'deletable-template',
            'elements' => $service->defaultElements(),
            'settings' => $service->defaultSettings(),
            'status' => 'active',
        ]);

        try {
            $service->delete($template, $admin);
            $this->fail('Active certificate templates must not be deleted.');
        } catch (ValidationException) {
            $this->assertDatabaseHas('certificate_templates', [
                'id' => $template->id,
                'deleted_at' => null,
            ]);
        }

        $template->update(['status' => 'draft']);
        $service->delete($template->fresh(), $admin);

        $this->assertSoftDeleted('certificate_templates', ['id' => $template->id]);
    }

    public function test_global_exam_pass_policy_applies_to_all_existing_certificates(): void
    {
        $user = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج الاختبار',
            'name_en' => 'Exam Program',
            'code' => 'EXAM-P',
            'status' => 'active',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة الاختبار',
            'code' => 'EXAM-B',
            'status' => 'active',
        ]);
        $section = AcademicSection::query()->create([
            'batch_id' => $batch->id,
            'program_id' => $program->id,
            'name' => 'شعبة الاختبار',
            'code' => 'EXAM-S',
        ]);
        $student = AcademicStudent::query()->create([
            'batch_id' => $batch->id,
            'section_id' => $section->id,
            'user_id' => $user->id,
            'name_ar' => 'طالب الاختبار',
            'academic_id' => 'EX-1',
            'academic_status' => 'eligible',
            'login_allowed' => true,
        ]);
        $certificate = Certificate::query()->create([
            'code' => 'CERT-EXAM-1',
            'verify_token' => 'exam-policy-token',
            'user_id' => $user->id,
            'academic_student_id' => $student->id,
            'holder_name' => $student->name_ar,
            'program_name' => $program->name_ar,
            'issued_at' => now(),
            'status' => 'active',
        ]);

        PlatformSetting::set('certificate_default_visibility_mode', 'after_exam_pass');
        PlatformSetting::set('certificate_required_exam_type', 'final');

        $certificate->load('academicStudent');
        $this->assertFalse(CertificateAccessPolicy::isVisible($certificate));

        $exam = Exam::query()->create([
            'section_id' => $section->id,
            'title' => 'الاختبار النهائي',
            'type' => 'final',
            'status' => 'closed',
        ]);
        ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'status' => 'graded',
            'started_at' => now()->subHour(),
            'graded_at' => now(),
            'percentage' => 90,
            'passed' => true,
            'question_snapshot' => [],
        ]);

        $this->assertTrue(CertificateAccessPolicy::isVisible($certificate));
        $this->assertCount(1, app(CertificateService::class)->forUser($user));
    }

    public function test_eligible_student_receives_one_automatic_certificate_and_notification(): void
    {
        Notification::fake();
        Storage::fake('local');

        PlatformSetting::set('certificate_default_visibility_mode', 'after_graduation');
        PlatformSetting::set('certificate_auto_issue_enabled', '1');
        PlatformSetting::set('certificate_auto_issue_notifications_enabled', '1');

        $user = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج الإصدار الآلي',
            'name_en' => 'Automatic Program',
            'code' => 'AUTO-P',
            'status' => 'active',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة الإصدار الآلي',
            'code' => 'AUTO-B',
            'status' => 'active',
        ]);
        $student = AcademicStudent::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $user->id,
            'name_ar' => 'طالب مؤهل آلياً',
            'academic_id' => 'AUTO-1',
            'academic_status' => 'graduated',
            'graduated_at' => now(),
            'login_allowed' => true,
        ]);
        $templateService = app(CertificateTemplateService::class);
        CertificateTemplate::query()->create([
            'name' => 'قالب الإصدار الآلي',
            'slug' => 'automatic-issuance-template',
            'elements' => $templateService->defaultElements(),
            'settings' => $templateService->defaultSettings(),
            'status' => 'active',
            'is_default' => true,
        ]);

        $service = app(AutomaticCertificateIssuanceService::class);
        $certificate = $service->issueForStudentId($student->id, 'graduation_approved');

        $this->assertNotNull($certificate);
        $this->assertSame('automatic', $certificate->metadata['issuance_mode']);
        $this->assertNull($service->issueForStudentId($student->id, 'duplicate_trigger'));
        $this->assertDatabaseCount('certificates', 1);

        Notification::assertSentTo(
            $user,
            PlatformAlertNotification::class,
            fn (PlatformAlertNotification $notification) => $notification->alertType === 'certificate.issued',
        );
    }
}
