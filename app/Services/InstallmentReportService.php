<?php

namespace App\Services;

use App\Models\InstallmentPayment;
use App\Models\InstallmentSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InstallmentReportService
{
    /** @return array{month: string, expected: float, collected: float, overdue: float, paid_count: int, pending_count: int}> */
    public function monthlySummary(?Carbon $month = null): array
    {
        $month ??= now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $dueInMonth = InstallmentSchedule::query()
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['cancelled', 'waived']);

        $expected = (float) (clone $dueInMonth)->sum('amount');

        $collected = (float) InstallmentPayment::query()
            ->where('status', 'success')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $overdue = (float) InstallmentSchedule::query()
            ->where('status', 'overdue')
            ->sum('amount');

        return [
            'month' => $month->translatedFormat('F Y'),
            'month_key' => $month->format('Y-m'),
            'expected' => $expected,
            'collected' => $collected,
            'overdue' => $overdue,
            'paid_count' => (clone $dueInMonth)->where('status', 'paid')->count(),
            'pending_count' => (clone $dueInMonth)->whereIn('status', ['pending', 'overdue'])->count(),
            'collection_rate' => $expected > 0 ? round(($collected / $expected) * 100, 1) : 0,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function monthlyPayments(?Carbon $month = null): Collection
    {
        $month ??= now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        return InstallmentPayment::query()
            ->with(['schedule.contract.user', 'schedule.contract.student'])
            ->where('status', 'success')
            ->whereBetween('paid_at', [$start, $end])
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (InstallmentPayment $p) => [
                'paid_at' => $p->paid_at?->format('Y-m-d H:i'),
                'amount' => (float) $p->amount,
                'gateway' => $p->gateway,
                'student' => $p->schedule?->contract?->student?->name_ar ?? $p->schedule?->contract?->user?->displayName(),
                'contract' => $p->schedule?->contract?->contract_no,
                'installment' => $p->schedule?->label,
            ]);
    }

    /** @return Collection<int, array{month_key: string, month: string, expected: float, collected: float}> */
    public function lastMonthsTrend(int $months = 6): Collection
    {
        $rows = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $summary = $this->monthlySummary($m);
            $rows->push([
                'month_key' => $summary['month_key'],
                'month' => $summary['month'],
                'expected' => $summary['expected'],
                'collected' => $summary['collected'],
            ]);
        }

        return $rows;
    }
}
