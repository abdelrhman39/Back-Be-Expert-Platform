<?php

namespace App\Services;

use App\Models\InstallmentContract;
use App\Models\InstallmentDunningExecution;
use App\Models\InstallmentDunningPolicy;
use App\Models\InstallmentDunningStep;
use App\Models\InstallmentSchedule;
use App\Models\User;
use App\Support\InstallmentDunningActions;
use App\Support\InstallmentSettings;
use App\Support\NotificationTypes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentDunningService
{
    public function __construct(
        protected InstallmentContractService $contracts,
        protected NotificationService $notifications,
        protected AuditLogService $audit,
    ) {}

    /** @return array{processed: int, executed: int, skipped: int} */
    public function process(?Carbon $now = null): array
    {
        $now ??= now();
        $this->contracts->refreshOverdueStatuses();

        if (! InstallmentSettings::dunningEnabled()) {
            return ['processed' => 0, 'executed' => 0, 'skipped' => 0];
        }

        $schedules = InstallmentSchedule::query()
            ->with(['contract.user', 'contract.student', 'contract.dunningPolicy.steps'])
            ->whereIn('status', ['pending', 'overdue'])
            ->whereHas('contract', fn ($q) => $q->whereIn('status', [
                'active', 'pending_signature', 'suspended', 'defaulted',
            ]))
            ->orderBy('due_date')
            ->get();

        $processed = 0;
        $executed = 0;
        $skipped = 0;

        foreach ($schedules as $schedule) {
            $processed++;
            $result = $this->processSchedule($schedule, $now);
            $executed += $result['executed'];
            $skipped += $result['skipped'];
        }

        return compact('processed', 'executed', 'skipped');
    }

    /** @return array{executed: int, skipped: int} */
    public function processSchedule(InstallmentSchedule $schedule, ?Carbon $now = null): array
    {
        $now ??= now();
        $contract = $schedule->contract;

        if (! $contract || ! $schedule->isPayable()) {
            return ['executed' => 0, 'skipped' => 0];
        }

        $policy = $this->resolvePolicy($contract);

        if (! $policy || ! $policy->is_active) {
            return ['executed' => 0, 'skipped' => 0];
        }

        $executed = 0;
        $skipped = 0;

        foreach ($policy->steps()->where('enabled', true)->orderBy('sort_order')->get() as $step) {
            if ($this->alreadyExecuted($schedule->id, $step->id)) {
                $skipped++;

                continue;
            }

            if (! $this->isStepDue($schedule, $step, $now)) {
                continue;
            }

            $this->executeStep($schedule->fresh(['contract.user', 'contract.student']), $step, $policy, $now);
            $executed++;
        }

        return compact('executed', 'skipped');
    }

    public function resolvePolicy(InstallmentContract $contract): ?InstallmentDunningPolicy
    {
        if ($contract->dunning_policy_id) {
            return InstallmentDunningPolicy::query()
                ->with('steps')
                ->find($contract->dunning_policy_id)
                ?? InstallmentDunningPolicy::defaultPolicy();
        }

        return InstallmentDunningPolicy::defaultPolicy();
    }

    public function isStepDue(InstallmentSchedule $schedule, InstallmentDunningStep $step, Carbon $now): bool
    {
        $due = Carbon::parse($schedule->due_date)->startOfDay();
        $triggerDay = $due->copy()->addDays((int) $step->trigger_offset_days)->startOfDay();

        if ($now->copy()->startOfDay()->lt($triggerDay)) {
            return false;
        }

        if ($step->trigger_hour !== null) {
            $triggerAt = $triggerDay->copy()->setTime((int) $step->trigger_hour, 0);

            if ($now->lt($triggerAt)) {
                return false;
            }
        }

        // خطوات ما بعد الاستحقاق تتطلب قسطاً غير مدفوع ومتأخر فعلياً (أو يوم الاستحقاق نفسه).
        if ((int) $step->trigger_offset_days >= 0 && $schedule->status === 'pending' && $due->isFuture()) {
            return false;
        }

        return true;
    }

    public function executeStep(
        InstallmentSchedule $schedule,
        InstallmentDunningStep $step,
        InstallmentDunningPolicy $policy,
        ?Carbon $now = null,
    ): InstallmentDunningExecution {
        $now ??= now();
        $contract = $schedule->contract;
        $applied = [];
        $messageSent = false;

        return DB::transaction(function () use ($schedule, $step, $policy, $now, $contract, &$applied, &$messageSent) {
            foreach ($step->actionList() as $action) {
                $this->applyAction($action, $schedule, $contract, $applied);
            }

            if ($step->email_enabled || in_array(InstallmentDunningActions::SEND_NOTIFICATION, $step->actionList(), true)) {
                $messageSent = $this->sendStepMessage($schedule, $step, $contract);
                if ($messageSent && ! in_array(InstallmentDunningActions::SEND_NOTIFICATION, $applied, true)) {
                    $applied[] = InstallmentDunningActions::SEND_NOTIFICATION;
                }
            }

            $execution = InstallmentDunningExecution::query()->create([
                'policy_id' => $policy->id,
                'step_id' => $step->id,
                'schedule_id' => $schedule->id,
                'contract_id' => $contract->id,
                'status' => 'executed',
                'executed_at' => $now,
                'actions_applied' => $applied,
                'message_sent' => $messageSent,
                'meta' => [
                    'step_name' => $step->name,
                    'trigger_offset_days' => $step->trigger_offset_days,
                    'amount' => (float) $schedule->amount,
                ],
            ]);

            $this->audit->log(
                action: 'installment.dunning_step',
                descriptionAr: 'تصعيد أقساط: '.$step->name.' — '.$contract->contract_no,
                group: 'finance',
                actor: null,
                subject: $schedule,
                subjectLabel: $contract->contract_no,
                newValues: ['step_id' => $step->id, 'actions' => $applied],
            );

            return $execution;
        });
    }

    /**
     * Called after a schedule is paid: stop further escalation for that installment
     * and restore access if no other overdue installments remain.
     */
    public function handleSchedulePaid(InstallmentSchedule $schedule): void
    {
        $contract = $schedule->contract?->fresh(['user', 'student', 'schedules']);

        if (! $contract) {
            return;
        }

        $hasOverdue = $contract->schedules
            ->whereIn('status', ['overdue', 'pending'])
            ->filter(fn (InstallmentSchedule $item) => $item->id !== $schedule->id && $item->isPayable() && (
                $item->status === 'overdue'
                || ($item->due_date && Carbon::parse($item->due_date)->isPast())
            ))
            ->isNotEmpty();

        if (! $hasOverdue) {
            $this->clearRestrictions($contract, notifyCleared: true);
        }
    }

    public function clearRestrictions(InstallmentContract $contract, bool $notifyCleared = false): void
    {
        $previousRestrictions = $contract->dunning_restrictions ?? [];

        $updates = [
            'dunning_restrictions' => null,
            'suspended_at' => null,
            'suspension_reason' => null,
        ];

        if (in_array($contract->status, ['suspended', 'defaulted'], true)) {
            $updates['status'] = 'active';
        }

        $contract->update($updates);

        $student = $contract->student;

        if ($student && $student->academic_status === 'suspended') {
            $student->update([
                'academic_status' => 'studying',
                'study_status' => 'مستمر دراسياً',
            ]);
        }

        $user = $contract->user;

        if (
            $user
            && $user->status === 'suspended'
            && (
                ($previousRestrictions['lock_login'] ?? false)
                || ($previousRestrictions['permanent_lock'] ?? false)
            )
        ) {
            if (! $this->userHasLoginLock($user, exceptContractId: $contract->id)) {
                $user->update(['status' => 'active']);
            }
        }

        if ($notifyCleared && $user) {
            $locale = $user->locale ?: 'ar';
            $this->notifications->send(
                user: $user,
                type: NotificationTypes::INSTALLMENT_PAID,
                title: 'تم رفع قيود الأقساط',
                body: 'تم تسجيل السداد وإيقاف مسار التصعيد. تم استعادة الوصول وفق حالة حسابك الحالية.',
                actionUrl: route('installments', ['locale' => $locale]),
                icon: 'fa-circle-check',
                subject: $contract,
            );
        }
    }

    public function userHasLoginLock(?User $user, ?int $exceptContractId = null): bool
    {
        if (! $user) {
            return false;
        }

        return InstallmentContract::query()
            ->where('user_id', $user->id)
            ->when($exceptContractId, fn ($q) => $q->where('id', '!=', $exceptContractId))
            ->whereIn('status', ['active', 'pending_signature', 'suspended', 'defaulted'])
            ->get()
            ->contains(fn (InstallmentContract $c) => $c->hasDunningRestriction('lock_login')
                || $c->hasDunningRestriction('permanent_lock'));
    }

    public function userHasLearningSuspension(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (app(InstallmentOverdueService::class)->userIsSuspendedForInstallments($user)) {
            return true;
        }

        return InstallmentContract::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending_signature', 'suspended', 'defaulted'])
            ->get()
            ->contains(fn (InstallmentContract $c) => $c->hasDunningRestriction('suspend_learning')
                || $c->hasDunningRestriction('permanent_lock'));
    }

    public function userHasExamBlock(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->userHasLearningSuspension($user)) {
            return true;
        }

        return InstallmentContract::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending_signature', 'suspended', 'defaulted'])
            ->get()
            ->contains(fn (InstallmentContract $c) => $c->hasDunningRestriction('block_exams')
                || $c->hasDunningRestriction('permanent_lock'));
    }

    protected function alreadyExecuted(int $scheduleId, int $stepId): bool
    {
        return InstallmentDunningExecution::query()
            ->where('schedule_id', $scheduleId)
            ->where('step_id', $stepId)
            ->where('status', 'executed')
            ->exists();
    }

    /** @param  list<string>  $applied */
    protected function applyAction(string $action, InstallmentSchedule $schedule, InstallmentContract $contract, array &$applied): void
    {
        $restrictions = $contract->dunning_restrictions ?? [];

        match ($action) {
            InstallmentDunningActions::SEND_NOTIFICATION => null,
            InstallmentDunningActions::SUSPEND_LEARNING => $this->suspendLearning($contract, $restrictions),
            InstallmentDunningActions::BLOCK_EXAMS => $restrictions['block_exams'] = true,
            InstallmentDunningActions::LOCK_LOGIN => $this->lockLogin($contract, $restrictions),
            InstallmentDunningActions::MARK_DEFAULTED => $this->markDefaulted($contract),
            InstallmentDunningActions::PERMANENT_LOCK => $this->permanentLock($contract, $restrictions),
            InstallmentDunningActions::APPLY_LATE_FEE => $this->applyLateFee($schedule),
            default => Log::warning('Unknown dunning action', ['action' => $action]),
        };

        if ($action !== InstallmentDunningActions::SEND_NOTIFICATION) {
            $contract->update(['dunning_restrictions' => $restrictions]);
            $applied[] = $action;
        }
    }

    /** @param  array<string, mixed>  $restrictions */
    protected function suspendLearning(InstallmentContract $contract, array &$restrictions): void
    {
        $restrictions['suspend_learning'] = true;

        if (! in_array($contract->status, ['suspended', 'defaulted'], true)) {
            $contract->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspension_reason' => 'تصعيد متأخرات الأقساط — إيقاف التعلم مؤقتاً',
            ]);
        }

        $student = $contract->student;

        if ($student) {
            $student->update([
                'academic_status' => 'suspended',
                'study_status' => 'موقوف — أقساط متأخرة',
            ]);
        }
    }

    /** @param  array<string, mixed>  $restrictions */
    protected function lockLogin(InstallmentContract $contract, array &$restrictions): void
    {
        $restrictions['lock_login'] = true;
        $user = $contract->user;

        if ($user && $user->status === 'active') {
            $user->update(['status' => 'suspended']);
        }
    }

    protected function markDefaulted(InstallmentContract $contract): void
    {
        $contract->update([
            'status' => 'defaulted',
            'suspended_at' => $contract->suspended_at ?? now(),
            'suspension_reason' => $contract->suspension_reason ?: 'تعثر سداد الأقساط',
        ]);
    }

    /** @param  array<string, mixed>  $restrictions */
    protected function permanentLock(InstallmentContract $contract, array &$restrictions): void
    {
        $restrictions['permanent_lock'] = true;
        $restrictions['lock_login'] = true;
        $restrictions['suspend_learning'] = true;
        $restrictions['block_exams'] = true;
        $this->lockLogin($contract, $restrictions);
        $this->suspendLearning($contract, $restrictions);
        $this->markDefaulted($contract);
    }

    protected function applyLateFee(InstallmentSchedule $schedule): void
    {
        if ((float) $schedule->late_fee_amount > 0 || ! InstallmentSettings::lateFeesEnabled()) {
            return;
        }

        $fee = InstallmentSettings::calculateLateFee((float) $schedule->amount);

        if ($fee > 0) {
            $schedule->update(['late_fee_amount' => $fee]);
        }
    }

    protected function sendStepMessage(InstallmentSchedule $schedule, InstallmentDunningStep $step, InstallmentContract $contract): bool
    {
        $user = $contract->user;

        if (! $user) {
            return false;
        }

        $locale = $user->locale ?: 'ar';
        $payUrl = route('installments.pay', [
            'locale' => $locale,
            'contract' => $contract->id,
            'schedule' => $schedule->id,
        ]);

        $vars = [
            '{{student_name}}' => $user->displayName(),
            '{{amount}}' => number_format((float) $schedule->amount, 2),
            '{{due_date}}' => optional($schedule->due_date)->format('Y-m-d') ?? '',
            '{{contract_no}}' => $contract->contract_no,
            '{{installment_label}}' => $schedule->label ?? ('قسط #'.$schedule->sequence),
            '{{pay_url}}' => $payUrl,
            '{{step_name}}' => $step->name,
            '{{days_overdue}}' => (string) max(0, Carbon::parse($schedule->due_date)->startOfDay()->diffInDays(now()->startOfDay())),
        ];

        $title = $this->replaceVars($step->email_subject ?: $step->name, $vars);
        $body = $this->replaceVars($step->email_body ?: 'يرجى سداد القسط المستحق في أقرب وقت.', $vars);

        $channels = $step->channelList() ?: ['mail', 'database'];

        $this->notifications->send(
            user: $user,
            type: NotificationTypes::INSTALLMENT_DUNNING,
            title: $title,
            body: $body,
            actionUrl: $payUrl,
            icon: 'fa-triangle-exclamation',
            subject: $schedule,
            channelsOverride: $channels,
        );

        return true;
    }

    /** @param  array<string, string>  $vars */
    protected function replaceVars(string $text, array $vars): string
    {
        return str_replace(array_keys($vars), array_values($vars), $text);
    }
}
