<?php

use App\Models\Order;
use App\Services\RefundService;
use App\Support\OrderOptions;
use App\Support\PaymentMethods;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تفاصيل الطلب | منصة مركز التعلم المستمر')]
class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $this->order = $order->load('items');
    }

    #[Computed]
    public function itemsTotal(): float
    {
        return (float) $this->order->items->sum(fn ($item) => (float) $item->price);
    }

    #[Computed]
    public function openRefund()
    {
        return app(RefundService::class)->openForOrder($this->order);
    }

    #[Computed]
    public function bankInstructions(): string
    {
        if (! $this->order->isAwaitingBankTransfer()) {
            return '';
        }

        return \App\Support\BankTransferInstructions::html();
    }
};
?>

@php
    $locale = app()->getLocale();
    $order = $this->order;
    $statusClass = match ($order->status) {
        'paid' => 'portal-status-pill--paid',
        'pending_payment' => 'portal-status-pill--pending',
        'cancelled' => 'portal-status-pill--cancelled',
        'refunded' => 'portal-status-pill--refunded',
        default => 'portal-status-pill--default',
    };
    $statusIcon = match (true) {
        $order->status === 'paid' => 'fa-circle-check',
        $order->isAwaitingBankTransfer() => 'fa-building-columns',
        $order->status === 'pending_payment' => 'fa-clock',
        $order->status === 'cancelled' => 'fa-circle-xmark',
        $order->status === 'refunded' => 'fa-rotate-left',
        default => 'fa-circle',
    };
    $payment = PaymentMethods::find($order->payment_method ?? '');
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'my-orders', 'portalTitle' => $order->reference])

<div class="portal-dashboard portal-orders-page">
    <div class="portal-order-detail-head">
        <a href="{{ route('my-orders', ['locale' => $locale]) }}" class="portal-order-detail-back">
            <i class="fa-solid fa-arrow-right"></i> العودة للطلبات
        </a>
        <div class="portal-order-detail-hero">
            <div class="portal-order-detail-hero__main">
                <span class="portal-order-detail-hero__label">رقم الطلب</span>
                <code class="portal-order-detail-hero__ref">{{ $order->reference }}</code>
                <span class="portal-status-pill {{ $statusClass }} mt-2">
                    <i class="fa-solid {{ $statusIcon }}"></i>
                    {{ OrderOptions::statusLabelForOrder($order) }}
                </span>
            </div>
            <div class="portal-order-detail-hero__total">
                <span class="portal-order-detail-hero__label">إجمالي الطلب</span>
                <strong dir="ltr">{{ number_format((float) $order->total, 2) }} <small>ر.س</small></strong>
            </div>
        </div>
    </div>

    <div class="portal-dashboard-grid portal-dashboard-grid--wide">
        <div class="portal-main-col">
            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-list"></i> عناصر الطلب</h2>
                </div>
                <div class="portal-panel__body">
                    <div class="portal-order-card__items p-3">
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
                    <div class="portal-order-detail-summary">
                        <div class="portal-order-detail-summary__row">
                            <span>مجموع العناصر</span>
                            <strong dir="ltr">{{ number_format($this->itemsTotal, 2) }} ر.س</strong>
                        </div>
                        <div class="portal-order-detail-summary__row portal-order-detail-summary__row--total">
                            <span>الإجمالي</span>
                            <strong dir="ltr">{{ number_format((float) $order->total, 2) }} ر.س</strong>
                        </div>
                    </div>
                </div>
            </section>

            @if ($order->isAwaitingBankTransfer() && $this->bankInstructions)
                @include('partials.commerce.bank-transfer-instructions', [
                    'html' => $this->bankInstructions,
                    'orderRef' => $order->reference,
                    'amount' => (float) $order->total,
                    'variant' => 'portal',
                ])
            @endif
        </div>

        <aside class="portal-side-col">
            <div class="portal-widget portal-widget--academic">
                <div class="portal-widget__head">
                    <span class="portal-widget__head-icon"><i class="fa-solid fa-credit-card"></i></span>
                    <h3 class="portal-widget__title">معلومات الدفع</h3>
                </div>
                <div class="portal-academic-list">
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">طريقة الدفع</span>
                        <strong class="d-inline-flex align-items-center gap-1">
                            @if ($payment && ! empty($payment['icon']))
                                <img src="{{ static_asset($payment['icon']) }}" alt="" class="portal-order-card__pay-icon">
                            @endif
                            {{ PaymentMethods::label($order->payment_method ?? '') ?: '—' }}
                        </strong>
                    </div>
                    @if ($order->payment_ref)
                        <div class="portal-academic-item">
                            <span class="portal-academic-item__label">مرجع الدفع</span>
                            <strong dir="ltr" style="font-family:ui-monospace,monospace;font-size:0.78rem">{{ $order->payment_ref }}</strong>
                        </div>
                    @endif
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">تاريخ الطلب</span>
                        <strong>{{ $order->created_at?->translatedFormat('d M Y — H:i') }}</strong>
                    </div>
                    @if ($order->updated_at && ! $order->created_at?->eq($order->updated_at))
                        <div class="portal-academic-item">
                            <span class="portal-academic-item__label">آخر تحديث</span>
                            <strong>{{ $order->updated_at->translatedFormat('d M Y — H:i') }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <div class="portal-order-detail-actions">
                @if ($order->needsOnlinePayment())
                    <a href="{{ route('checkout', ['locale' => $locale, 'order' => $order->reference]) }}" class="btn btn-warning w-100">
                        <i class="fa-solid fa-credit-card"></i> إتمام الدفع
                    </a>
                @elseif ($order->isAwaitingBankTransfer() && ! $this->bankInstructions)
                    <div class="alert alert-warning small mb-0">
                        <i class="fa-solid fa-circle-info"></i>
                        تم تأكيد طلبك. يرجى إتمام التحويل البنكي — سيتم تفعيل الدورة بعد مراجعة الإدارة.
                    </div>
                @elseif ($order->status === 'paid')
                    <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="btn btn-primary w-100">
                        <i class="fa-solid fa-book-open"></i> الذهاب لقائمة التعلم
                    </a>
                    @if (! $this->openRefund)
                        <a href="{{ route('refunds', ['locale' => $locale]) }}" class="btn btn-outline-secondary w-100">
                            <i class="fa-solid fa-rotate-left"></i> طلب استرداد
                        </a>
                    @elseif ($this->openRefund)
                        <div class="alert alert-info small mb-0">
                            طلب استرداد: <strong dir="ltr">{{ $this->openRefund->reference_no }}</strong>
                            — {{ \App\Support\RefundOptions::statusLabel($this->openRefund->status) }}
                        </div>
                    @endif
                @endif
                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-outline-primary w-100">تصفح المزيد من الدورات</a>
            </div>
        </aside>
    </div>
</div>

@include('partials.portal.shell-end')
