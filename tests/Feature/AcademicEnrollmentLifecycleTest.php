<?php

namespace Tests\Feature;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicStudent;
use App\Models\CrmContact;
use App\Models\User;
use App\Services\AcademicEnrollmentLifecycleService;
use App\Services\CrmContactStatusService;
use App\Support\AccessControl;
use App\Support\CrmOptions;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicEnrollmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
        AccessControl::forget();
        CrmOptions::forgetCache();
    }

    public function test_registration_pushes_crm_to_awaiting_payment_and_paid_activates_student(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $program = AcademicProgram::query()->create([
            'name_ar' => 'دبلوم التقنية',
            'name_en' => 'Tech Diploma',
            'code' => 'DIP-TECH',
            'status' => 'active',
            'type' => 'diploma',
        ]);
        $batch = AcademicBatch::query()->create([
            'program_id' => $program->id,
            'name' => 'دفعة 1',
            'code' => 'B1',
            'status' => 'active',
            'enrollment_open' => true,
            'installment_allowed' => true,
            'tuition_amount' => 1000,
        ]);
        $student = AcademicStudent::query()->create([
            'user_id' => $user->id,
            'batch_id' => $batch->id,
            'name_ar' => $user->displayName(),
            'academic_id' => '26000001',
            'academic_status' => 'pending',
            'study_status' => 'بانتظار إكمال التسجيل',
            'login_allowed' => true,
            'joined_at' => now(),
        ]);

        $lifecycle = app(AcademicEnrollmentLifecycleService::class);
        $contact = $lifecycle->markRegistrationAwaitingPayment($user->fresh(['academicStudent.batch.program']));

        $this->assertNotNull($contact);
        $this->assertSame('awaiting_payment', $contact->status);
        $this->assertSame($program->id, $contact->program_id);

        $receipt = UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf');
        app(CrmContactStatusService::class)->change(
            $contact,
            'paid',
            $admin,
            null,
            $receipt,
        );

        $this->assertSame('studying', $student->fresh()->academic_status);
        $this->assertSame('paid', $contact->fresh()->status);
        $this->assertNotNull($contact->fresh()->paid_at);
    }

    public function test_leaving_paid_crm_status_requires_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $contact = CrmContact::query()->create([
            'user_id' => $user->id,
            'name' => $user->displayName(),
            'status' => 'paid',
            'priority' => 'medium',
            'paid_at' => now(),
            'payment_receipt_path' => 'crm/payment-receipts/x.pdf',
            'payment_receipt_name' => 'x.pdf',
        ]);

        try {
            app(CrmContactStatusService::class)->change(
                $contact,
                'awaiting_payment',
                $admin,
                null,
            );
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('lostReason', $e->errors());
        }
    }
}
