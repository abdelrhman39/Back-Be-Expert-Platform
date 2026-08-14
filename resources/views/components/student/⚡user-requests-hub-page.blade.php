<?php

use App\Models\RefundRequest;
use App\Services\AcademicRequestService;
use App\Services\RefundService;
use App\Support\AcademicRequestOptions;
use App\Support\RefundOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('طلباتي | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Url]
    public string $tab = 'all';

    #[Computed]
    public function student()
    {
        return app(AcademicRequestService::class)->resolveStudent(auth()->user());
    }

    #[Computed]
    public function academicRequests()
    {
        return app(AcademicRequestService::class)->forUser(auth()->user());
    }

    #[Computed]
    public function refunds()
    {
        return app(RefundService::class)->forUser(auth()->user());
    }

    #[Computed]
    public function stats(): array
    {
        $academic = $this->academicRequests;
        $refunds = $this->refunds;

        return [
            'pending' => $academic->whereIn('status', ['pending', 'processing'])->count()
                + $refunds->whereIn('status', ['pending', 'approved'])->count(),
            'approved' => $academic->where('status', 'approved')->count()
                + $refunds->where('status', 'processed')->count(),
            'rejected' => $academic->where('status', 'rejected')->count()
                + $refunds->where('status', 'rejected')->count(),
            'total' => $academic->count() + $refunds->count(),
        ];
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['all', 'academic', 'financial'], true)) {
            $this->tab = $tab;
        }
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'user-requests', 'portalTitle' => 'طلباتي'])

<div class="portal-dashboard portal-user-requests-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">مركز طلباتي</h1>
            <p class="portal-orders-intro__desc">قدّم ومتابعة الطلبات الأكاديمية والمالية من مكان واحد.</p>
        </div>
    </div>

    @if (! $this->student)
        <div class="portal-alert portal-alert--warn portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-info"></i></span>
            <div class="portal-alert__content">لم يُربط حسابك بسجل أكاديمي بعد. يمكنك تقديم طلبات الاسترداد فقط، أو التواصل مع شؤون الطلاب لتفعيل الطلبات الأكاديمية.</div>
        </div>
    @endif

    <div class="portal-ur-kpis">
        <div class="portal-ur-kpi">
            <span class="portal-ur-kpi__value">{{ $this->stats['pending'] }}</span>
            <span class="portal-ur-kpi__label">قيد المراجعة</span>
        </div>
        <div class="portal-ur-kpi">
            <span class="portal-ur-kpi__value">{{ $this->stats['approved'] }}</span>
            <span class="portal-ur-kpi__label">مكتملة / موافق</span>
        </div>
        <div class="portal-ur-kpi">
            <span class="portal-ur-kpi__value">{{ $this->stats['rejected'] }}</span>
            <span class="portal-ur-kpi__label">مرفوضة</span>
        </div>
        <div class="portal-ur-kpi">
            <span class="portal-ur-kpi__value">{{ $this->stats['total'] }}</span>
            <span class="portal-ur-kpi__label">إجمالي الطلبات</span>
        </div>
    </div>

    <section class="portal-ur-actions">
        <h2 class="portal-ur-section-title"><i class="fa-solid fa-plus-circle"></i> تقديم طلب جديد</h2>
        <div class="portal-ur-action-grid">
            @foreach (AcademicRequestOptions::hubActions($locale) as $action)
                @if ($action['type'] === 'academic' && ! $this->student)
                    @continue
                @endif
                <a href="{{ $action['route'] }}" class="portal-ur-action-card portal-ur-action-card--{{ $action['type'] }}" wire:key="act-{{ $action['key'] }}">
                    <span class="portal-ur-action-card__icon"><i class="fa-solid {{ $action['icon'] }}"></i></span>
                    <strong>{{ $action['label'] }}</strong>
                    <span>{{ $action['description'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="portal-orders-filters portal-ur-tabs">
        @foreach (['all' => 'الكل', 'academic' => 'أكاديمية', 'financial' => 'مالية'] as $key => $label)
            <button type="button" @class(['portal-orders-filter', 'portal-orders-filter--active' => $tab === $key]) wire:click="setTab('{{ $key }}')">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <section class="portal-ur-list">
        @php
            $showAcademic = in_array($tab, ['all', 'academic'], true);
            $showFinancial = in_array($tab, ['all', 'financial'], true);
            $hasItems = ($showAcademic && $this->academicRequests->isNotEmpty()) || ($showFinancial && $this->refunds->isNotEmpty());
        @endphp

        @if (! $hasItems)
            <div class="portal-empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>لا توجد طلبات{{ $tab !== 'all' ? ' في هذا القسم' : '' }} بعد.</p>
            </div>
        @else
            @if ($showAcademic)
                @foreach ($this->academicRequests as $request)
                    <article class="portal-ur-item portal-ur-item--academic" wire:key="ar-{{ $request->id }}">
                        <div class="portal-ur-item__icon"><i class="fa-solid {{ AcademicRequestOptions::studentIcon($request->type) }}"></i></div>
                        <div class="portal-ur-item__body">
                            <div class="portal-ur-item__head">
                                <strong>{{ AcademicRequestOptions::studentSingularLabel($request->type) }}</strong>
                                <span class="portal-badge {{ AcademicRequestOptions::statusBadgeClass($request->status) }}">{{ $request->statusLabel() }}</span>
                            </div>
                            <div class="portal-ur-item__meta">
                                <span dir="ltr"><i class="fa-solid fa-hashtag"></i> {{ $request->request_no }}</span>
                                <span><i class="fa-regular fa-clock"></i> {{ $request->submitted_at?->translatedFormat('d M Y') }}</span>
                                @if ($request->program_name)
                                    <span><i class="fa-solid fa-graduation-cap"></i> {{ $request->program_name }}</span>
                                @endif
                            </div>
                            @if ($request->admin_notes && in_array($request->status, ['rejected', 'approved'], true))
                                <p class="portal-ur-item__note"><strong>ملاحظة الإدارة:</strong> {{ $request->admin_notes }}</p>
                            @endif
                        </div>
                        <a href="{{ route('user-requests.show', ['locale' => $locale, 'academicRequest' => $request]) }}" class="portal-btn-secondary portal-btn-secondary--sm">التفاصيل</a>
                    </article>
                @endforeach
            @endif

            @if ($showFinancial)
                @foreach ($this->refunds as $refund)
                    <article class="portal-ur-item portal-ur-item--financial" wire:key="rf-{{ $refund->id }}">
                        <div class="portal-ur-item__icon"><i class="fa-solid fa-rotate-left"></i></div>
                        <div class="portal-ur-item__body">
                            <div class="portal-ur-item__head">
                                <strong>طلب استرداد</strong>
                                <span class="portal-badge {{ RefundOptions::portalStatusBadgeClass($refund->status) }}">{{ RefundOptions::statusLabel($refund->status) }}</span>
                            </div>
                            <div class="portal-ur-item__meta">
                                <span dir="ltr">{{ $refund->reference_no }}</span>
                                <span>{{ number_format((float) $refund->amount, 2) }} ر.س</span>
                                <span>{{ $refund->created_at->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('refunds', ['locale' => $locale]) }}" class="portal-btn-secondary portal-btn-secondary--sm">عرض</a>
                    </article>
                @endforeach
            @endif
        @endif
    </section>
</div>

@include('partials.portal.shell-end')
