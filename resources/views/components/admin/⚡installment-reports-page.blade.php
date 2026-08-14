<?php

use App\Services\InstallmentReportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تقارير التقسيط | لوحة التحكم')]
class extends Component
{
    #[Url]
    public string $month = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.view'), 403);

        if ($this->month === '') {
            $this->month = now()->format('Y-m');
        }
    }

    public function summary(): array
    {
        return app(InstallmentReportService::class)->monthlySummary(
            \Carbon\Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()
        );
    }

    public function payments()
    {
        return app(InstallmentReportService::class)->monthlyPayments(
            \Carbon\Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()
        );
    }

    public function trend()
    {
        return app(InstallmentReportService::class)->lastMonthsTrend(6);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-reports'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'تقارير التقسيط'],
    ],
])

@php $summary = $this->summary(); $payments = $this->payments(); $trend = $this->trend(); @endphp

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--split">
        <div>
            <h2>تقرير تحصيل الأقساط</h2>
            <p class="admin-crud-card__meta">المتوقع مقابل المحصّل والمتأخرات.</p>
        </div>
        <div class="admin-field" style="margin:0;min-width:12rem;">
            <input type="month" class="admin-control" wire:model.live="month">
        </div>
    </div>

    <div class="admin-kpi-row" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0.75rem;margin-bottom:1rem;">
        <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">المستحق الشهري</span><strong>{{ number_format($summary['expected'], 0) }} ر.س</strong></div>
        <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">المحصّل</span><strong>{{ number_format($summary['collected'], 0) }} ر.س</strong></div>
        <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">نسبة التحصيل</span><strong>{{ $summary['collection_rate'] }}%</strong></div>
        <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">أقساط مدفوعة</span><strong>{{ $summary['paid_count'] }}</strong></div>
        <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">متأخرات إجمالية</span><strong>{{ number_format($summary['overdue'], 0) }} ر.س</strong></div>
    </div>

    <h3 style="font-size:0.95rem;margin:1rem 0 0.5rem;">اتجاه آخر 6 أشهر</h3>
    <div class="admin-table-wrap" style="margin-bottom:1rem;">
        <table class="admin-data-table">
            <thead><tr><th>الشهر</th><th>المستحق</th><th>المحصّل</th></tr></thead>
            <tbody>
                @foreach ($trend as $row)
                    <tr wire:key="t-{{ $row['month_key'] }}">
                        <td>{{ $row['month'] }}</td>
                        <td>{{ number_format($row['expected'], 2) }} ر.س</td>
                        <td>{{ number_format($row['collected'], 2) }} ر.س</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3 style="font-size:0.95rem;margin:1rem 0 0.5rem;">مدفوعات {{ $summary['month'] }}</h3>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الطالب</th>
                    <th>العقد</th>
                    <th>القسط</th>
                    <th>المبلغ</th>
                    <th>القناة</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td dir="ltr">{{ $payment['paid_at'] }}</td>
                        <td>{{ $payment['student'] }}</td>
                        <td>{{ $payment['contract'] }}</td>
                        <td>{{ $payment['installment'] }}</td>
                        <td>{{ number_format($payment['amount'], 2) }} ر.س</td>
                        <td>{{ $payment['gateway'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">لا توجد مدفوعات في هذا الشهر.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@include('partials.admin.shell-end')
