<?php

use App\Services\InstallmentContractService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('أقساطي | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function contracts()
    {
        return app(InstallmentContractService::class)->forUser(auth()->user());
    }

    #[Computed]
    public function stats(): array
    {
        $contracts = $this->contracts;

        return [
            'total' => $contracts->sum('total_amount'),
            'paid' => $contracts->sum('paid_amount'),
            'remaining' => $contracts->sum('remaining_balance'),
            'overdue' => $contracts->flatMap->schedules->filter(fn ($s) => $s->displayStatus() === 'overdue')->count(),
        ];
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'installments', 'portalTitle' => 'أقساطي'])

<div class="portal-dashboard portal-installments-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">أقساطي الدراسية</h1>
            <p class="portal-orders-intro__desc">متابعة جدول الدفعات والسداد الإلكتروني من المنصة.</p>
        </div>
    </div>

    <div class="portal-inst-kpis">
        <div class="portal-inst-kpi">
            <span class="portal-inst-kpi__value">{{ number_format($this->stats['total'], 0) }}</span>
            <span class="portal-inst-kpi__label">إجمالي الالتزام (ر.س)</span>
        </div>
        <div class="portal-inst-kpi portal-inst-kpi--success">
            <span class="portal-inst-kpi__value">{{ number_format($this->stats['paid'], 0) }}</span>
            <span class="portal-inst-kpi__label">المسدّد</span>
        </div>
        <div class="portal-inst-kpi portal-inst-kpi--warn">
            <span class="portal-inst-kpi__value">{{ number_format($this->stats['remaining'], 0) }}</span>
            <span class="portal-inst-kpi__label">المتبقي</span>
        </div>
        <div class="portal-inst-kpi @if($this->stats['overdue'] > 0) portal-inst-kpi--danger @endif">
            <span class="portal-inst-kpi__value">{{ $this->stats['overdue'] }}</span>
            <span class="portal-inst-kpi__label">أقساط متأخرة</span>
        </div>
    </div>

    @if ($this->contracts->isEmpty())
        <div class="portal-alert portal-alert--info portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-info"></i></span>
            <div class="portal-alert__content">لا توجد عقود تقسيط مرتبطة بحسابك. تواصل مع الشؤون المالية إذا كنت تتوقع خطة تقسيط.</div>
        </div>
    @else
        <div class="portal-inst-section-grid">
            @foreach ($this->contracts as $contract)
                @php $next = $contract->nextDueSchedule(); @endphp
                <a href="{{ route('installments.show', ['locale' => $locale, 'contract' => $contract->id]) }}" class="portal-inst-section-card portal-inst-contract-card" wire:key="ic-{{ $contract->id }}">
                    <div class="portal-inst-section-card__head">
                        <span class="portal-inst-section-card__code">{{ $contract->contract_no }}</span>
                        <span class="portal-inst-badge">{{ $contract->statusLabel() }}</span>
                    </div>
                    <h3>{{ $contract->title }}</h3>
                    <div class="portal-inst-progress">
                        <div class="portal-inst-progress__bar" style="width:{{ $contract->progressPercent() }}%"></div>
                    </div>
                    <p>{{ number_format($contract->paid_amount, 0) }} / {{ number_format($contract->total_amount, 0) }} ر.س — {{ $contract->progressPercent() }}%</p>
                    @if ($next)
                        <div class="portal-inst-contract-card__next">
                            <i class="fa-solid fa-calendar"></i>
                            القسط التالي: {{ $next->label }} — {{ number_format($next->amount, 2) }} ر.س
                            <span class="portal-inst-badge {{ \App\Support\InstallmentOptions::scheduleBadgeClass($next->displayStatus()) }}">{{ $next->statusLabel() }}</span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>

@include('partials.portal.shell-end')
