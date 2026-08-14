<?php

namespace App\Services;

use App\Models\InstallmentContract;
use App\Models\InstallmentSchedule;
use App\Models\User;
use App\Support\InstallmentSettings;
use App\Support\NotificationTypes;

class InstallmentOverdueService
{
    public function __construct(
        protected InstallmentContractService $contracts,
        protected NotificationService $notifications,
        protected AuditLogService $audit,
    ) {}

    public function process(): array
    {
        $this->contracts->refreshOverdueStatuses();

        $lateFees = $this->applyLateFees();

        if (! InstallmentSettings::suspensionEnabled()) {
            return [
                'overdue_notified' => 0,
                'suspended' => 0,
                'restored' => 0,
                'late_fees' => $lateFees,
            ];
        }

        $overdueNotified = $this->notifyNewOverdue();
        $suspended = $this->suspendDelinquentContracts();
        $restored = $this->restoreClearedContracts();

        return [
            'overdue_notified' => $overdueNotified,
            'suspended' => $suspended,
            'restored' => $restored,
            'late_fees' => $lateFees,
        ];
    }

    /**
     * Apply a one-time late fee to overdue installments once the configured
     * post-due window has elapsed. Skips installments that already carry a fee.
     */
    protected function applyLateFees(): int
    {
        if (! InstallmentSettings::lateFeesEnabled()) {
            return 0;
        }

        $threshold = now()->subDays(InstallmentSettings::lateFeeApplyAfterDays())->toDateString();

        $schedules = InstallmentSchedule::query()
            ->where('status', 'overdue')
            ->where('late_fee_amount', '<=', 0)
            ->whereDate('due_date', '<=', $threshold)
            ->get();

        $count = 0;

        foreach ($schedules as $schedule) {
            $fee = InstallmentSettings::calculateLateFee((float) $schedule->amount);

            if ($fee <= 0) {
                continue;
            }

            $schedule->update(['late_fee_amount' => $fee]);
            $count++;
        }

        return $count;
    }

    protected function notifyNewOverdue(): int
    {
        $graceEnd = now()->subDays(InstallmentSettings::graceDays())->toDateString();

        $schedules = InstallmentSchedule::query()
            ->with(['contract.user'])
            ->where('status', 'overdue')
            ->whereDate('due_date', '<=', $graceEnd)
            ->get();

        $sent = 0;

        foreach ($schedules as $schedule) {
            if (! $schedule->isPayable()) {
                continue;
            }

            $offsets = $schedule->reminder_offsets_sent ?? [];

            if (in_array('overdue', $offsets, true)) {
                continue;
            }

            $user = $schedule->contract?->user;

            if (! $user) {
                continue;
            }

            $locale = $user->locale ?: 'ar';

            $this->notifications->send(
                user: $user,
                type: NotificationTypes::INSTALLMENT_OVERDUE,
                title: 'قسط متأخر — '.$schedule->label,
                body: 'تجاوزت موعد سداد قسط بمبلغ '.number_format((float) $schedule->amount, 2).' ر.س. يرجى السداد فوراً لتجنب إيقاف الالتحاق.',
                actionUrl: route('installments.pay', [
                    'locale' => $locale,
                    'contract' => $schedule->contract_id,
                    'schedule' => $schedule->id,
                ]),
                icon: 'fa-triangle-exclamation',
                subject: $schedule,
            );

            $offsets[] = 'overdue';
            $schedule->update(['reminder_offsets_sent' => $offsets, 'reminder_sent_at' => now()]);
            $sent++;
        }

        return $sent;
    }

    protected function suspendDelinquentContracts(): int
    {
        $threshold = now()->subDays(InstallmentSettings::suspendAfterDays())->toDateString();
        $count = 0;

        $contracts = InstallmentContract::query()
            ->with(['user', 'student'])
            ->whereIn('status', ['active', 'pending_signature'])
            ->whereHas('schedules', fn ($q) => $q
                ->whereIn('status', ['overdue'])
                ->whereDate('due_date', '<=', $threshold))
            ->get();

        foreach ($contracts as $contract) {
            if ($contract->status === 'suspended') {
                continue;
            }

            $contract->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspension_reason' => 'تأخر في سداد الأقساط لأكثر من '.InstallmentSettings::suspendAfterDays().' يوماً',
            ]);

            $student = $contract->student;

            if ($student) {
                $student->update([
                    'academic_status' => 'suspended',
                    'study_status' => 'موقوف — أقساط متأخرة',
                ]);
            }

            $user = $contract->user;

            if ($user) {
                $locale = $user->locale ?: 'ar';

                $this->notifications->send(
                    user: $user,
                    type: NotificationTypes::ENROLLMENT_SUSPENDED,
                    title: 'تم إيقاف الالتحاق مؤقتاً',
                    body: 'لديك أقساط متأخرة. سدّد المستحقات لاستعادة الوصول للمنصة.',
                    actionUrl: route('installments', ['locale' => $locale]),
                    icon: 'fa-ban',
                    subject: $contract,
                );
            }

            $this->audit->log(
                action: 'installment_contract.suspended',
                descriptionAr: 'إيقاف عقد '.$contract->contract_no.' لمتأخرات',
                group: 'finance',
                actor: null,
                subject: $contract,
                subjectLabel: $contract->contract_no,
            );

            $count++;
        }

        return $count;
    }

    public function restoreClearedContracts(): int
    {
        $count = 0;

        $contracts = InstallmentContract::query()
            ->with('student')
            ->where('status', 'suspended')
            ->get();

        foreach ($contracts as $contract) {
            $hasOverdue = InstallmentSchedule::query()
                ->where('contract_id', $contract->id)
                ->where('status', 'overdue')
                ->exists();

            if ($hasOverdue) {
                continue;
            }

            $contract->update([
                'status' => 'active',
                'suspended_at' => null,
                'suspension_reason' => null,
            ]);

            $student = $contract->student;

            if ($student && $student->academic_status === 'suspended') {
                $student->update([
                    'academic_status' => 'studying',
                    'study_status' => 'مستمر دراسياً',
                ]);
            }

            $count++;
        }

        return $count;
    }

    public function userIsSuspendedForInstallments(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return InstallmentContract::query()
            ->where('user_id', $user->id)
            ->where('status', 'suspended')
            ->exists();
    }
}
