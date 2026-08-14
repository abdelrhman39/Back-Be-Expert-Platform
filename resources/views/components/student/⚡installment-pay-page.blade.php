<?php

use App\Models\InstallmentContract;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Services\InstallmentPaymentService;
use App\Services\MoyasarService;
use App\Support\PaymentGatewaySettings;
use App\Support\PaymentMethods;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('سداد قسط | منصة مركز التعلم المستمر')]
class extends Component
{
    public InstallmentContract $contract;

    public InstallmentSchedule $schedule;

    public string $paymentMethod = 'mada';

    public ?string $paymentError = null;

    public ?string $successMessage = null;

    public ?Order $pendingOrder = null;

    /** @var array<string, array<string, mixed>> */
    public array $paymentMethods = [];

    public function mount(
        InstallmentContract $contract,
        InstallmentSchedule $schedule,
        InstallmentPaymentService $payments,
        MoyasarService $moyasar,
    ): void {
        abort_unless($contract->user_id === auth()->id(), 403);
        abort_unless($schedule->contract_id === $contract->id, 404);

        $this->contract = $contract;
        $this->schedule = $schedule;

        if (request()->query('success') && $schedule->fresh()->status === 'paid') {
            $this->successMessage = 'تم سداد القسط بنجاح. شكراً لك!';

            return;
        }

        abort_unless($payments->studentCanPay(auth()->user(), $schedule), 403);

        $this->paymentMethods = collect(PaymentMethods::forCheckout())
            ->filter(fn (array $method) => PaymentMethods::usesMoyasar($method['id']))
            ->all();
        $this->paymentMethod = (string) (array_key_first($this->paymentMethods) ?? 'mada');

        if (request()->query('payment') === 'failed') {
            $this->paymentError = session('checkout_error', 'فشل الدفع. يرجى المحاولة مرة أخرى.');
        }

        abort_unless($contract->isStudentSigned(), 403, 'يجب التوقيع على العقد قبل السداد.');

        if ($orderRef = request()->query('order')) {
            $order = Order::query()
                ->where('reference', $orderRef)
                ->where('user_id', auth()->id())
                ->where('installment_schedule_id', $schedule->id)
                ->first();

            if ($order && $order->status === 'pending_payment') {
                $this->pendingOrder = $order;
                $this->paymentMethod = (string) $order->payment_method;
            }
        }
    }

    public function preparePayment(InstallmentPaymentService $payments, MoyasarService $moyasar): void
    {
        $this->paymentError = null;

        if (! $moyasar->isConfigured() || ! PaymentGatewaySettings::isEnabled($this->paymentMethod)) {
            $this->paymentError = 'بوابة الدفع غير مفعّلة حالياً. تواصل مع الشؤون المالية.';

            return;
        }

        $this->pendingOrder = $payments->createPaymentOrder($this->schedule, auth()->user(), $this->paymentMethod);
        $this->dispatch('init-moyasar');
    }
};
?>

@php
    $locale = app()->getLocale();
    $moyasar = app(\App\Services\MoyasarService::class);
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'installments', 'portalTitle' => 'سداد قسط'])
@include('partials.commerce-styles')

<div class="portal-dashboard portal-installments-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">سداد {{ $schedule->label }}</h1>
            <p class="portal-orders-intro__desc">{{ $contract->title }} — استحقاق {{ $schedule->due_date->format('Y-m-d') }}</p>
        </div>
    </div>

    @if ($successMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ $successMessage }}</div>
        </div>
        <div class="portal-inst-hub-actions">
            <a href="{{ route('installments.show', ['locale' => $locale, 'contract' => $contract->id]) }}" class="portal-btn portal-btn--primary portal-btn--sm">العودة لتفاصيل العقد</a>
            <a href="{{ route('installments', ['locale' => $locale]) }}" class="portal-btn portal-btn--secondary portal-btn--sm">أقساطي</a>
        </div>
    @else
        <section class="portal-inst-panel">
            <div class="portal-inst-kpis portal-inst-kpis--compact" style="margin-bottom:1rem;">
                <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ number_format($schedule->amount, 2) }}</span><span class="portal-inst-kpi__label">مبلغ القسط</span></div>
                <div class="portal-inst-kpi portal-inst-kpi--warn"><span class="portal-inst-kpi__value">{{ number_format($contract->remaining_balance, 2) }}</span><span class="portal-inst-kpi__label">المتبقي على العقد</span></div>
            </div>

            @if ($paymentError)
                <div class="portal-alert portal-alert--warn portal-alert--compact"><div class="portal-alert__content">{{ $paymentError }}</div></div>
            @endif

            @if ($pendingOrder && $moyasar->isConfigured())
                @php
                    $callbackUrl = route('installments.pay.callback', [
                        'locale' => $locale,
                        'contract' => $contract->id,
                        'schedule' => $schedule->id,
                        'order' => $pendingOrder->reference,
                    ]);
                @endphp
                <p class="portal-inst-empty" style="margin-bottom:1rem;">الطلب <strong>{{ $pendingOrder->reference }}</strong> — {{ number_format($pendingOrder->total, 2) }} {{ $pendingOrder->currency }}</p>

                <link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.14.0/moyasar.css">
                <script src="https://cdn.moyasar.com/mpf/1.14.0/moyasar.js"></script>
                <div
                    class="mysr-form"
                    data-amount="{{ $moyasar->amountInHalalas((float) $pendingOrder->total) }}"
                    data-currency="{{ $pendingOrder->currency }}"
                    data-description="قسط {{ $schedule->label }} — {{ $contract->contract_no }}"
                    data-publishable-key="{{ $moyasar->publishableKey() }}"
                    data-callback-url="{{ $callbackUrl }}"
                    data-methods="{{ json_encode($moyasar->methodsForPaymentMethod($paymentMethod)) }}"
                    data-metadata="{{ json_encode(['order_id' => $pendingOrder->id, 'order_reference' => $pendingOrder->reference, 'installment_schedule_id' => $schedule->id]) }}"
                ></div>
            @else
                <div class="portal-field" style="max-width:20rem;margin-bottom:1rem;">
                    <label>طريقة الدفع</label>
                    <select class="portal-control" wire:model="paymentMethod">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method['id'] }}">{{ $method['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="portal-btn portal-btn--primary" wire:click="preparePayment" @disabled($paymentMethods === [])>متابعة للدفع الإلكتروني</button>
            @endif

            <a href="{{ route('installments.show', ['locale' => $locale, 'contract' => $contract->id]) }}" class="portal-btn portal-btn--ghost portal-btn--sm" style="margin-top:1rem;">إلغاء</a>
        </section>
    @endif
</div>

@script
<script>
    const initMoyasarInstallment = () => {
        if (typeof Moyasar === 'undefined') return;
        const el = document.querySelector('.mysr-form');
        if (!el || el.dataset.initialized === '1') return;
        el.dataset.initialized = '1';
        el.innerHTML = '';
        Moyasar.init({
            element: '.mysr-form',
            amount: parseInt(el.dataset.amount, 10),
            currency: el.dataset.currency || 'SAR',
            description: el.dataset.description || '',
            publishable_api_key: el.dataset.publishableKey,
            callback_url: el.dataset.callbackUrl,
            methods: JSON.parse(el.dataset.methods || '["creditcard"]'),
            metadata: JSON.parse(el.dataset.metadata || '{}'),
        });
    };
    $wire.on('init-moyasar', () => setTimeout(initMoyasarInstallment, 150));
    if (document.querySelector('.mysr-form')) setTimeout(initMoyasarInstallment, 150);
</script>
@endscript

@include('partials.portal.shell-end')
