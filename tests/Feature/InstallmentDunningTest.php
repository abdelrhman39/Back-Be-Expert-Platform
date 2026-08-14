<?php

namespace Tests\Feature;

use App\Models\InstallmentContract;
use App\Models\InstallmentDunningPolicy;
use App\Models\InstallmentDunningStep;
use App\Models\InstallmentSchedule;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\InstallmentDunningService;
use App\Support\InstallmentDunningActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstallmentDunningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_dunning_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.installment-dunning'))
            ->assertOk()
            ->assertSee('مسار تصعيد متأخرات الأقساط')
            ->assertSee('دليل العمل');
    }

    public function test_dunning_step_executes_and_blocks_exams(): void
    {
        PlatformSetting::set('installment_dunning_enabled', '1', 'finance');

        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $policy = InstallmentDunningPolicy::query()->create([
            'name' => 'Test',
            'is_active' => true,
            'is_default' => true,
            'process_time' => '09:00',
        ]);

        $step = InstallmentDunningStep::query()->create([
            'policy_id' => $policy->id,
            'sort_order' => 1,
            'name' => 'منع اختبارات',
            'enabled' => true,
            'trigger_offset_days' => 0,
            'actions' => [
                InstallmentDunningActions::SEND_NOTIFICATION,
                InstallmentDunningActions::BLOCK_EXAMS,
            ],
            'email_enabled' => true,
            'email_subject' => 'تحذير {{student_name}}',
            'email_body' => 'المبلغ {{amount}}',
            'channels' => ['database'],
        ]);

        $contract = InstallmentContract::query()->create([
            'contract_no' => 'DN-001',
            'user_id' => $student->id,
            'title' => 'عقد تجريبي',
            'total_amount' => 1000,
            'paid_amount' => 0,
            'remaining_balance' => 1000,
            'currency' => 'SAR',
            'status' => 'active',
            'dunning_policy_id' => $policy->id,
        ]);

        $schedule = InstallmentSchedule::query()->create([
            'contract_id' => $contract->id,
            'sequence' => 1,
            'label' => 'القسط 1',
            'amount' => 1000,
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'overdue',
        ]);

        $result = app(InstallmentDunningService::class)->processSchedule($schedule->fresh(['contract.user']));

        $this->assertSame(1, $result['executed']);
        $this->assertTrue($contract->fresh()->hasDunningRestriction('block_exams'));
        $this->assertTrue(app(InstallmentDunningService::class)->userHasExamBlock($student));
        $this->assertDatabaseHas('installment_dunning_executions', [
            'schedule_id' => $schedule->id,
            'step_id' => $step->id,
            'status' => 'executed',
        ]);
    }

    public function test_payment_clears_dunning_restrictions(): void
    {
        PlatformSetting::set('installment_dunning_enabled', '1', 'finance');

        $student = User::factory()->create(['role' => 'student', 'status' => 'suspended']);
        $contract = InstallmentContract::query()->create([
            'contract_no' => 'DN-002',
            'user_id' => $student->id,
            'title' => 'عقد',
            'total_amount' => 500,
            'paid_amount' => 0,
            'remaining_balance' => 500,
            'currency' => 'SAR',
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => 'test',
            'dunning_restrictions' => [
                'suspend_learning' => true,
                'block_exams' => true,
                'lock_login' => true,
            ],
        ]);

        $schedule = InstallmentSchedule::query()->create([
            'contract_id' => $contract->id,
            'sequence' => 1,
            'label' => 'قسط',
            'amount' => 500,
            'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        app(InstallmentDunningService::class)->handleSchedulePaid($schedule->fresh(['contract.user', 'contract.student', 'contract.schedules']));

        $contract->refresh();
        $student->refresh();

        $this->assertNull($contract->dunning_restrictions);
        $this->assertSame('active', $contract->status);
        $this->assertSame('active', $student->status);
    }

    public function test_livewire_can_create_step(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test('admin.installment-dunning-page')
            ->call('openCreateStep')
            ->set('stepName', 'خطوة اختبار')
            ->set('triggerOffsetDays', 2)
            ->set('stepActions', [InstallmentDunningActions::SEND_NOTIFICATION])
            ->set('emailSubject', 'عنوان')
            ->set('emailBody', 'نص')
            ->call('saveStep')
            ->assertSet('showStepForm', false);

        $this->assertDatabaseHas('installment_dunning_steps', [
            'name' => 'خطوة اختبار',
            'trigger_offset_days' => 2,
        ]);
    }
}
