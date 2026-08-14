<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicStudent;
use App\Models\CatalogCourse;
use App\Models\CatalogEnrollment;
use App\Models\User;
use App\Services\ExternalCertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ExternalCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_certificate_is_stored_privately_and_available_only_to_its_student(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $studentUser = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $otherStudent = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج الاختبار',
            'name_en' => 'Test Program',
            'code' => 'EXT-PROG',
            'status' => 'active',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة خارجية',
            'code' => 'EXT-BATCH',
            'status' => 'active',
        ]);
        $student = AcademicStudent::query()->create([
            'batch_id' => $batch->id,
            'user_id' => $studentUser->id,
            'name_ar' => 'طالب الاعتماد',
            'name_en' => 'Credential Student',
            'academic_id' => 'EXT-1001',
            'academic_status' => 'active',
            'login_allowed' => true,
        ]);
        $course = CatalogCourse::query()->create([
            'id' => 991,
            'title_ar' => 'دورة إدارة الجودة',
            'title_en' => 'Quality Management',
            'slug' => 'quality-management-test',
            'status' => 'published',
        ]);
        CatalogEnrollment::query()->create([
            'user_id' => $studentUser->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress_percent' => 25,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.certificates-page')
            ->call('openExternalUpload')
            ->call('chooseExternalStudent', $student->id)
            ->assertSee('برنامج الاختبار')
            ->assertSee('دورة إدارة الجودة');

        $file = UploadedFile::fake()->create(
            'international-certificate.pdf',
            120,
            'application/pdf',
        );

        $certificate = app(ExternalCertificateService::class)->upload(
            $student,
            $file,
            [
                'title' => 'شهادة دولية في إدارة المشاريع',
                'issuer' => 'International Accreditation Body',
                'credential_id' => 'CRED-2026-100',
                'verification_url' => 'https://example.com/verify/CRED-2026-100',
                'related_program_type' => 'academic_program',
                'related_program_id' => $program->id,
                'related_program_name' => $program->name_ar,
                'issued_at' => '2026-07-19',
                'expires_at' => null,
                'visibility_mode' => 'immediate',
                'visible_from' => null,
            ],
            $admin,
        );

        $this->assertTrue($certificate->isExternal());
        $this->assertSame('International Accreditation Body', $certificate->external_issuer);
        $this->assertSame('برنامج الاختبار', $certificate->related_program_name);
        $this->assertSame('international-certificate.pdf', $certificate->external_file_name);
        $this->assertNotNull($certificate->credential_hash);
        $this->assertTrue($certificate->hasValidIntegrity());
        Storage::disk('local')->assertExists($certificate->pdf_path);

        $this->actingAs($studentUser)
            ->get(route('certificates.download', ['locale' => 'ar', 'certificate' => $certificate]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($otherStudent)
            ->get(route('certificates.download', ['locale' => 'ar', 'certificate' => $certificate]))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->get(route('certificates.show', ['locale' => 'ar', 'certificate' => $certificate]))
            ->assertOk()
            ->assertSee('International Accreditation Body');

        $hidden = app(ExternalCertificateService::class)->upload(
            $student,
            UploadedFile::fake()->create('hidden.pdf', 20, 'application/pdf'),
            [
                'title' => 'اعتماد مخفي إدارياً',
                'issuer' => 'Hidden Issuer',
                'related_program_type' => 'academic_program',
                'related_program_id' => $program->id,
                'related_program_name' => $program->name_ar,
                'issued_at' => '2026-07-19',
                'visibility_mode' => 'hidden',
                'visible_from' => null,
            ],
            $admin,
        );
        $scheduled = app(ExternalCertificateService::class)->upload(
            $student,
            UploadedFile::fake()->create('scheduled.pdf', 20, 'application/pdf'),
            [
                'title' => 'اعتماد مجدول',
                'issuer' => 'Scheduled Issuer',
                'related_program_type' => 'academic_program',
                'related_program_id' => $program->id,
                'related_program_name' => $program->name_ar,
                'issued_at' => '2026-07-19',
                'visibility_mode' => 'scheduled',
                'visible_from' => now()->addDay(),
            ],
            $admin,
        );

        $this->actingAs($studentUser)
            ->get(route('certificates', ['locale' => 'ar']))
            ->assertOk()
            ->assertDontSee($hidden->program_name)
            ->assertDontSee($scheduled->program_name);

        $this->actingAs($studentUser)
            ->get(route('certificates.download', ['locale' => 'ar', 'certificate' => $hidden]))
            ->assertForbidden();

        $this->travel(2)->days();

        $this->actingAs($studentUser)
            ->get(route('certificates', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee($scheduled->program_name)
            ->assertDontSee($hidden->program_name);
    }
}
