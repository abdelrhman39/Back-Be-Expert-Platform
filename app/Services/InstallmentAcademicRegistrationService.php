<?php

namespace App\Services;

use App\Models\AcademicBatch;
use App\Models\InstallmentPlanTemplate;
use App\Models\InstallmentSchedule;
use App\Models\User;
use App\Support\InstallmentSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentAcademicRegistrationService
{
    public function __construct(
        private readonly AcademicRegistrationService $registration,
        private readonly InstallmentContractService $contracts,
        private readonly InstallmentPaymentService $payments,
    ) {}

    /** @return array{student: \App\Models\AcademicStudent, contract: \App\Models\InstallmentContract, schedule: InstallmentSchedule, order: ?\App\Models\Order} */
    /** @param  array<string, mixed>  $profile */
    public function start(User $user, int $batchId, int $templateId, array $profile = []): array
    {
        if (! InstallmentSettings::academicRegistrationEnabled()) {
            throw ValidationException::withMessages(['batchId' => 'التسجيل الأكاديمي بالتقسيط غير مفعّل حالياً.']);
        }

        if (! $this->registration->userCanRegister($user)) {
            throw ValidationException::withMessages(['batchId' => 'لا يمكنك التسجيل أكاديمياً في الوقت الحالي.']);
        }

        $batch = AcademicBatch::query()->with('program')->findOrFail($batchId);
        $template = InstallmentPlanTemplate::query()
            ->where('is_active', true)
            ->findOrFail($templateId);

        $tuition = (float) $batch->tuition_amount;
        $title = ($batch->program?->name_ar ?? $batch->name).' — '.$batch->name;

        return DB::transaction(function () use ($user, $batch, $template, $tuition, $title, $profile) {
            $student = $this->registration->createOrRefreshPendingStudent($user, $batch, $profile);

            $existingContract = $user->installmentContracts()
                ->where('batch_id', $batch->id)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->first();

            if ($existingContract) {
                $contract = $existingContract->load('schedules');
            } else {
                $contract = $this->contracts->createFromTemplate(
                    studentUser: $user,
                    template: $template,
                    totalAmount: $tuition,
                    academicStudent: $student,
                    startsAt: now()->startOfDay(),
                    creator: $user,
                    title: $title,
                    adminNotes: 'أُنشئ من التسجيل الأكاديمي',
                );
            }

            $firstSchedule = $contract->schedules()->orderBy('sequence')->first();

            if (! $firstSchedule) {
                throw ValidationException::withMessages(['template' => 'فشل إنشاء جدول الأقساط.']);
            }

            $order = null;

            if ($contract->isStudentSigned() && $firstSchedule->isPayable()) {
                try {
                    $order = $this->payments->createPaymentOrder($firstSchedule, $user, 'mada');
                } catch (ValidationException) {
                    $order = null;
                }
            }

            app(AcademicEnrollmentLifecycleService::class)
                ->markRegistrationAwaitingPayment($user->fresh(['academicStudent.batch.program']));

            return [
                'student' => $student->fresh(['batch.program']),
                'contract' => $contract->fresh(['schedules']),
                'schedule' => $firstSchedule,
                'order' => $order,
            ];
        });
    }

    /** @return \Illuminate\Support\Collection<int, InstallmentPlanTemplate> */
    public function availablePlans(): \Illuminate\Support\Collection
    {
        return InstallmentPlanTemplate::query()
            ->where('is_active', true)
            ->with('items')
            ->orderBy('name_ar')
            ->get()
            ->filter(fn (InstallmentPlanTemplate $t) => abs($t->totalPercent() - 100) < 0.05);
    }
}
