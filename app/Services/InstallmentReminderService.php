<?php

namespace App\Services;

use App\Models\InstallmentSchedule;
use App\Models\NotificationRule;
use App\Support\InstallmentSettings;
use App\Support\NotificationTypes;
use Carbon\Carbon;

class InstallmentReminderService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function dispatch(): int
    {
        if (! InstallmentSettings::remindersEnabled()) {
            return 0;
        }

        $sent = 0;

        foreach (InstallmentSettings::reminderDaysBefore() as $daysBefore) {
            $sent += $this->dispatchForOffset($daysBefore);
        }

        return $sent;
    }

    protected function dispatchForOffset(int $daysBefore): int
    {
        $targetDate = now()->addDays($daysBefore)->toDateString();
        $offsetKey = (string) $daysBefore;

        $schedules = InstallmentSchedule::query()
            ->with(['contract.user'])
            ->whereIn('status', ['pending'])
            ->whereDate('due_date', $targetDate)
            ->whereHas('contract', fn ($q) => $q->whereIn('status', ['active', 'pending_signature']))
            ->get();

        $rule = NotificationRule::query()
            ->where('type', NotificationTypes::INSTALLMENT_DUE_SOON)
            ->where('is_enabled', true)
            ->first();

        $sent = 0;

        foreach ($schedules as $schedule) {
            $offsetsSent = $schedule->reminder_offsets_sent ?? [];

            if (in_array($offsetKey, $offsetsSent, true)) {
                continue;
            }

            $user = $schedule->contract?->user;

            if (! $user) {
                continue;
            }

            $locale = $user->locale ?: 'ar';
            $contract = $schedule->contract;

            $this->notifications->send(
                user: $user,
                type: NotificationTypes::INSTALLMENT_DUE_SOON,
                title: 'تذكير: قسط مستحق خلال '.$daysBefore.' '.($daysBefore === 1 ? 'يوم' : 'أيام'),
                body: $schedule->label.' — مبلغ '.number_format((float) $schedule->amount, 2).' ر.س. تاريخ الاستحقاق: '.$schedule->due_date->format('Y-m-d').'.',
                actionUrl: route('installments.show', ['locale' => $locale, 'contract' => $contract->id]),
                icon: 'fa-calendar-days',
                subject: $schedule,
                rule: $rule,
            );

            $offsetsSent[] = $offsetKey;
            $schedule->update([
                'reminder_offsets_sent' => $offsetsSent,
                'reminder_sent_at' => now(),
            ]);

            $sent++;
        }

        return $sent;
    }
}
