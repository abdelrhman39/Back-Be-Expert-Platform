<?php

use App\Models\Order;
use App\Support\OrderOptions;
use App\Support\PaymentMethods;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('طلباتي | مركز التعلم المستمر')]
class extends Component
{
    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    /** @var array<string, string> */
    private array $allowedFilters = [
        'all' => 'الكل',
        'pending_payment' => 'بانتظار الدفع',
        'paid' => 'مدفوع',
        'cancelled' => 'ملغي',
        'refunded' => 'مسترد',
    ];

    public function setFilter(string $filter): void
    {
        if (array_key_exists($filter, $this->allowedFilters)) {
            $this->statusFilter = $filter;
        }
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    #[Computed]
    public function filteredOrders()
    {
        if ($this->statusFilter === 'all') {
            return $this->orders;
        }

        return $this->orders->where('status', $this->statusFilter)->values();
    }

    #[Computed]
    public function stats(): array
    {
        $orders = $this->orders;

        return [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'pending_payment')->count(),
            'paid' => $orders->where('status', 'paid')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
            'total_spent' => (float) $orders->where('status', 'paid')->sum('total'),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function filterOptions(): array
    {
        return $this->allowedFilters;
    }
};
?>

@php
    $locale = app()->getLocale();
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'my-orders', 'portalTitle' => 'طلباتي'])

<div class="portal-dashboard portal-orders-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">طلبات الشراء</h1>
            <p class="portal-orders-intro__desc">تابع حالة طلباتك، تفاصيل الدورات، وطرق الدفع من مكان واحد.</p>
        </div>
        <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> طلب جديد
        </a>
    </div>

    @if ($this->orders->isNotEmpty())
        <div class="portal-kpi-strip portal-kpi-strip--orders">
            <div class="portal-kpi-v2 portal-kpi-v2--orders">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-file-invoice"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['total'] }}</span>
                    <span class="portal-kpi-v2__label">إجمالي الطلبات</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--paid">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-circle-check"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['paid'] }}</span>
                    <span class="portal-kpi-v2__label">مدفوعة</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--cert">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-clock"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['pending'] }}</span>
                    <span class="portal-kpi-v2__label">بانتظار الدفع</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--cart">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-wallet"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ number_format($this->stats['total_spent'], 0) }}</span>
                    <span class="portal-kpi-v2__label">إجمالي المدفوع (ر.س)</span>
                </span>
            </div>
        </div>

        @if ($this->stats['pending'] > 0)
            <div class="portal-pending-banner">
                <div class="portal-pending-banner__icon"><i class="fa-solid fa-clock"></i></div>
                <div class="portal-pending-banner__text">
                    <strong>{{ $this->stats['pending'] }} {{ $this->stats['pending'] === 1 ? 'طلب' : 'طلبات' }} بانتظار الدفع</strong>
                    <span>أكمل الدفع لتفعيل الوصول للدورات</span>
                </div>
                <button type="button" class="btn btn-sm btn-warning" wire:click="setFilter('pending_payment')">عرض الطلبات المعلقة</button>
            </div>
        @endif
    @endif

    @if ($this->orders->isNotEmpty())
        <div class="portal-orders-filters" role="tablist" aria-label="تصفية الطلبات">
            @foreach ($this->filterOptions as $key => $label)
                @php
                    $count = $key === 'all'
                        ? $this->stats['total']
                        : $this->orders->where('status', $key)->count();
                @endphp
                <button
                    type="button"
                    wire:click="setFilter('{{ $key }}')"
                    @class(['portal-orders-filter', 'portal-orders-filter--active' => $statusFilter === $key])
                    role="tab"
                    aria-selected="{{ $statusFilter === $key ? 'true' : 'false' }}"
                >
                    {{ $label }}
                    @if ($count > 0)
                        <span class="portal-orders-filter__count">{{ $count }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    @if ($this->orders->isEmpty())
        <div class="portal-panel">
            <div class="portal-empty portal-empty--lg">
                <div class="portal-empty__icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <p>لا توجد طلبات شراء بعد</p>
                <span class="portal-empty__hint">ابدأ رحلتك التعليمية بتصفح دوراتنا وإضافتها للسلة</span>
                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm mt-2">تصفح الدورات</a>
            </div>
        </div>
    @elseif ($this->filteredOrders->isEmpty())
        <div class="portal-panel">
            <div class="portal-empty">
                <div class="portal-empty__icon"><i class="fa-solid fa-filter"></i></div>
                <p>لا توجد طلبات في هذه الفئة</p>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" wire:click="setFilter('all')">عرض جميع الطلبات</button>
            </div>
        </div>
    @else
        <div class="portal-order-cards">
            @foreach ($this->filteredOrders as $order)
                @php
                    $statusClass = match ($order->status) {
                        'paid' => 'portal-status-pill--paid',
                        'pending_payment' => 'portal-status-pill--pending',
                        'cancelled' => 'portal-status-pill--cancelled',
                        'refunded' => 'portal-status-pill--refunded',
                        default => 'portal-status-pill--default',
                    };
                    $statusIcon = match ($order->status) {
                        'paid' => 'fa-circle-check',
                        'pending_payment' => 'fa-clock',
                        'cancelled' => 'fa-circle-xmark',
                        'refunded' => 'fa-rotate-left',
                        default => 'fa-circle',
                    };
                    $payment = PaymentMethods::find($order->payment_method ?? '');
                @endphp
                <article @class(['portal-order-card', 'portal-order-card--pending' => $order->status === 'pending_payment'])>
                    <header class="portal-order-card__head">
                        <div class="portal-order-card__main">
                            <span class="portal-order-card__ref">{{ $order->reference }}</span>
                            <span class="portal-order-card__meta">
                                <span><i class="fa-regular fa-calendar"></i> {{ $order->created_at?->translatedFormat('d M Y — H:i') }}</span>
                                <span><i class="fa-solid fa-book"></i> {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'دورة' : 'دورات' }}</span>
                            </span>
                        </div>
                        <div class="portal-order-card__side">
                            <span class="portal-status-pill {{ $statusClass }}">
                                <i class="fa-solid {{ $statusIcon }}"></i>
                                {{ OrderOptions::statusLabelForOrder($order) }}
                            </span>
                            <span class="portal-order-card__total" dir="ltr">
                                {{ number_format((float) $order->total, 2) }} <small>ر.س</small>
                            </span>
                        </div>
                    </header>

                    <div class="portal-order-card__items">
                        @foreach ($order->items as $item)
                            <div class="portal-order-card__item">
                                <span class="portal-order-card__item-icon"><i class="fa-solid fa-graduation-cap"></i></span>
                                <span class="portal-order-card__item-info">
                                    <strong>{{ $item->course_title ?: 'دورة #' . $item->course_id }}</strong>
                                    <span>{{ OrderOptions::deliveryLabel($item->delivery_type ?? 'online') }}</span>
                                </span>
                                <span class="portal-order-card__item-price" dir="ltr">{{ number_format((float) $item->price, 2) }} ر.س</span>
                            </div>
                        @endforeach
                    </div>

                    <footer class="portal-order-card__foot">
                        <div class="portal-order-card__payment">
                            <span class="portal-order-card__payment-label">طريقة الدفع</span>
                            <span class="portal-order-card__payment-value">
                                @if ($payment && ! empty($payment['icon']))
                                    <img src="{{ static_asset($payment['icon']) }}" alt="" class="portal-order-card__pay-icon">
                                @endif
                                {{ PaymentMethods::label($order->payment_method ?? '') ?: '—' }}
                            </span>
                            @if ($order->payment_ref)
                                <span class="portal-order-card__payment-ref" dir="ltr">
                                    <i class="fa-solid fa-hashtag"></i> {{ $order->payment_ref }}
                                </span>
                            @endif
                        </div>
                        <div class="portal-order-card__actions">
                            <a href="{{ route('my-orders.show', ['locale' => $locale, 'order' => $order->reference]) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-eye"></i> التفاصيل
                            </a>
                            @if ($order->needsOnlinePayment())
                                <a href="{{ route('checkout', ['locale' => $locale, 'order' => $order->reference]) }}" class="btn btn-warning btn-sm">
                                    <i class="fa-solid fa-credit-card"></i> إتمام الدفع
                                </a>
                            @elseif ($order->status === 'paid')
                                <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fa-solid fa-book-open"></i> قائمة التعلم
                                </a>
                            @endif
                        </div>
                    </footer>
                </article>
            @endforeach
        </div>
    @endif
</div>

@include('partials.portal.shell-end')
