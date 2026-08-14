<?php

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\InstallmentCheckoutService;
use App\Services\MoyasarService;
use App\Services\TabbyService;
use App\Services\TamaraService;
use App\Support\PaymentMethods;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('إتمام الشراء | منصة مركز التعلم المستمر')]
class extends Component
{
    public function layout(): string
    {
        return 'layouts.app-user';
    }
    public $items = [];

    public float $total = 0;

    public string $paymentMethod = 'bank_transfer';

    public ?string $successReference = null;

    public string $bankInstructions = '';

    public string $step = 'review';

    public ?int $pendingOrderId = null;

    public ?string $paymentError = null;

    public ?int $installmentPlanId = null;

    public bool $installmentAcknowledged = false;

    public function mount(CartService $cart, MoyasarService $moyasar): void
    {
        $locale = request()->route('locale') ?? 'ar';

        if ($successRef = request()->query('success')) {
            $order = Order::query()
                ->where('reference', $successRef)
                ->where('user_id', auth()->id())
                ->first();

            if ($order) {
                $this->showSuccess($order);

                return;
            }
        }

        if ($orderRef = request()->query('order')) {
            $order = Order::query()
                ->with('items')
                ->where('reference', $orderRef)
                ->where('user_id', auth()->id())
                ->where('status', 'pending_payment')
                ->first();

            if ($order) {
                $this->loadPendingOrder($order, $moyasar);

                if (request()->query('payment') === 'failed') {
                    $this->paymentError = session('checkout_error', 'فشل الدفع. يرجى المحاولة مرة أخرى.');
                }

                return;
            }
        }

        if ($cart->items()->isEmpty()) {
            $this->redirect(route('cart', ['locale' => $locale]));

            return;
        }

        $this->items = $cart->items();
        $this->total = $cart->total();
        $this->loadBankInstructions();
        $this->ensureDefaultPaymentMethod();
        $this->ensureDefaultInstallmentPlan();
    }

    protected function ensureDefaultPaymentMethod(): void
    {
        $available = PaymentMethods::checkoutIds();

        if ($available === []) {
            return;
        }

        if (PaymentMethods::isPlatformInstallment($this->paymentMethod)
            && $this->availableInstallmentPlans()->isEmpty()) {
            $available = array_values(array_filter(
                $available,
                fn (string $id) => ! PaymentMethods::isPlatformInstallment($id)
            ));
        }

        if ($available === []) {
            return;
        }

        if (! in_array($this->paymentMethod, $available, true)) {
            $this->paymentMethod = $available[0];
        }
    }

    protected function availableInstallmentPlans()
    {
        $cartItems = collect($this->items);

        return app(InstallmentCheckoutService::class)->availablePlans($cartItems);
    }

    protected function ensureDefaultInstallmentPlan(): void
    {
        if (! PaymentMethods::isPlatformInstallment($this->paymentMethod)) {
            return;
        }

        $plans = $this->availableInstallmentPlans();
        if ($plans->isEmpty()) {
            $this->installmentPlanId = null;

            return;
        }

        if (! $this->installmentPlanId || ! $plans->contains('id', (int) $this->installmentPlanId)) {
            $this->installmentPlanId = (int) $plans->first()->id;
        }
    }

    public function updatedPaymentMethod(): void
    {
        if ($this->paymentMethod === 'bank_transfer') {
            $this->loadBankInstructions();
        }

        if (! PaymentMethods::isPlatformInstallment($this->paymentMethod)) {
            $this->installmentAcknowledged = false;
        }

        $this->ensureDefaultInstallmentPlan();
    }

    public function updatedInstallmentPlanId(): void
    {
        $this->installmentAcknowledged = false;
    }

    public function placeOrder(CheckoutService $checkout, MoyasarService $moyasar, TabbyService $tabby, TamaraService $tamara): void
    {
        $this->paymentError = null;

        if ($this->pendingOrderId) {
            $existing = Order::query()
                ->with('items')
                ->where('id', $this->pendingOrderId)
                ->where('user_id', auth()->id())
                ->where('status', 'pending_payment')
                ->first();

            if ($existing) {
                if (PaymentMethods::isOffline($existing->payment_method ?? '')) {
                    $this->showSuccess($existing);

                    return;
                }

                if (PaymentMethods::usesMoyasar($existing->payment_method ?? '') && $moyasar->isConfigured()) {
                    $this->loadPendingOrder($existing, $moyasar);
                    $this->step = 'pay';
                    $this->dispatch('init-moyasar');

                    return;
                }

                if (PaymentMethods::isBnplGateway($existing->payment_method ?? '')) {
                    try {
                        $url = $existing->payment_method === 'tabby'
                            ? $tabby->createCheckoutUrl($existing)
                            : $tamara->createCheckoutUrl($existing);
                        $this->redirect($url);

                        return;
                    } catch (ValidationException $exception) {
                        $this->addError('paymentMethod', collect($exception->errors())->flatten()->first() ?? 'تعذر متابعة الدفع.');

                        return;
                    }
                }
            }
        }

        $this->validate([
            'paymentMethod' => ['required', Rule::in(PaymentMethods::checkoutIds())],
        ], [], [
            'paymentMethod' => 'طريقة الدفع',
        ]);

        if (PaymentMethods::usesMoyasar($this->paymentMethod) && ! $moyasar->isConfigured()) {
            $this->addError('paymentMethod', 'بوابة الدفع الإلكتروني غير مفعّلة حالياً. اختر تحويلاً بنكياً أو تواصل مع الدعم.');

            return;
        }

        if (PaymentMethods::isPlatformInstallment($this->paymentMethod)) {
            $this->ensureDefaultInstallmentPlan();
            $this->validate([
                'installmentPlanId' => ['required', 'exists:installment_plan_templates,id'],
                'installmentAcknowledged' => ['accepted'],
            ], [
                'installmentAcknowledged.accepted' => 'يجب الإقرار بنظام التقسيط قبل المتابعة.',
            ], [
                'installmentPlanId' => 'خطة التقسيط',
                'installmentAcknowledged' => 'الإقرار بنظام التقسيط',
            ]);

            if (! $moyasar->isConfigured()) {
                $this->addError('paymentMethod', 'بوابة الدفع مطلوبة لسداد الدفعة الأولى.');

                return;
            }

            try {
                $result = app(InstallmentCheckoutService::class)->startFromCart(
                    auth()->user(),
                    (int) $this->installmentPlanId,
                    'mada',
                );
            } catch (ValidationException $exception) {
                $this->addError('cart', collect($exception->errors())->flatten()->first() ?? 'تعذر إنشاء عقد التقسيط.');

                return;
            }

            $this->dispatch('commerce-counts-updated', cartCount: 0);
            $locale = app()->getLocale();
            $contract = $result['contract'];

            // Signature acknowledgment of the installment system, then first payment.
            if ($contract->needsStudentSignature()) {
                $this->redirect(route('installments.show', ['locale' => $locale, 'contract' => $contract->id]), navigate: true);

                return;
            }

            if ($result['order']) {
                $this->redirect(route('installments.pay', [
                    'locale' => $locale,
                    'contract' => $contract->id,
                    'schedule' => $result['schedule']->id,
                    'order' => $result['order']->reference,
                ]), navigate: true);

                return;
            }

            $this->redirect(route('installments.show', ['locale' => $locale, 'contract' => $contract->id]), navigate: true);

            return;
        }

        try {
            $order = $checkout->createOrderFromCart(auth()->user(), $this->paymentMethod);
        } catch (ValidationException $exception) {
            $this->addError('cart', $exception->errors()['cart'][0] ?? 'تعذر إنشاء الطلب.');

            return;
        }

        $this->dispatch('commerce-counts-updated', cartCount: 0);

        if (PaymentMethods::isBnplGateway($this->paymentMethod) && (float) $order->total > 0) {
            try {
                $url = $this->paymentMethod === 'tabby'
                    ? $tabby->createCheckoutUrl($order)
                    : $tamara->createCheckoutUrl($order);
            } catch (ValidationException $exception) {
                $this->addError('paymentMethod', collect($exception->errors())->flatten()->first() ?? 'تعذر بدء الدفع بالتقسيط الخارجي.');

                return;
            }

            $this->redirect($url);

            return;
        }

        if (PaymentMethods::usesMoyasar($this->paymentMethod) && (float) $order->total > 0) {
            $this->loadPendingOrder($order->load('items'), $moyasar);
            $this->step = 'pay';
            $this->dispatch('init-moyasar');

            return;
        }

        $this->showSuccess($order);
    }

    protected function loadPendingOrder(Order $order, MoyasarService $moyasar): void
    {
        $this->pendingOrderId = $order->id;
        $this->paymentMethod = $order->payment_method ?? $this->paymentMethod;
        $this->total = (float) $order->total;
        $this->items = $order->items;
        $this->loadBankInstructions();

        if (PaymentMethods::isOffline($this->paymentMethod)) {
            $this->showSuccess($order);

            return;
        }

        $this->step = PaymentMethods::usesMoyasar($this->paymentMethod) && $moyasar->isConfigured()
            ? 'pay'
            : 'review';
    }

    protected function showSuccess(Order $order): void
    {
        $this->successReference = $order->reference;
        $this->paymentMethod = $order->payment_method ?? $this->paymentMethod;
        $this->total = (float) $order->total;
        $this->step = 'success';
        $this->loadBankInstructions();
    }

    protected function loadBankInstructions(): void
    {
        $this->bankInstructions = \App\Support\BankTransferInstructions::html();
    }
};
?>

@php
    $locale = app()->getLocale();
    $moyasar = app(\App\Services\MoyasarService::class);
    $pendingOrder = $pendingOrderId ? \App\Models\Order::query()->find($pendingOrderId) : null;
    $itemCount = is_countable($items) ? count($items) : 0;
    $catalog = app(\App\Support\LegacyCourseCatalog::class);
    $checkoutDisabled = PaymentMethods::forCheckout() === [];
    $installmentPlans = collect($items)->isNotEmpty()
        ? app(InstallmentCheckoutService::class)->availablePlans(collect($items))
        : collect();
    $selectedPlan = $installmentPlanId
        ? $installmentPlans->firstWhere('id', (int) $installmentPlanId)
        : null;
    $planPreview = $selectedPlan ? $selectedPlan->schedulePreview((float) $total) : [];
    $firstDue = collect($planPreview)->firstWhere('is_first', true);
    $requiresSignature = \App\Support\InstallmentSettings::requiresSignature();

    $ctaLabel = match (true) {
        PaymentMethods::isPlatformInstallment($paymentMethod) => $requiresSignature
            ? 'متابعة للتوقيع وسداد الدفعة الأولى'
            : 'متابعة لسداد الدفعة الأولى',
        PaymentMethods::isBnplGateway($paymentMethod) => 'متابعة عبر '.PaymentMethods::label($paymentMethod),
        PaymentMethods::usesMoyasar($paymentMethod) => 'متابعة للدفع الكامل',
        $paymentMethod === 'bank_transfer' => 'تأكيد الطلب والتحويل البنكي',
        default => 'تأكيد الطلب',
    };
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'my-orders', 'portalTitle' => 'إتمام الشراء'])
@include('partials.commerce-styles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v=2">
@endpush

<div class="portal-dashboard portal-commerce-page portal-checkout-page">
    @if ($step === 'success' && $successReference)
        <nav class="checkout-steps" aria-label="خطوات إتمام الشراء">
            <div class="checkout-steps__item is-done">
                <span class="checkout-steps__num"><i class="fa-solid fa-check"></i></span>
                <span class="checkout-steps__label">المراجعة</span>
            </div>
            <span class="checkout-steps__sep" aria-hidden="true"></span>
            <div class="checkout-steps__item is-done">
                <span class="checkout-steps__num"><i class="fa-solid fa-check"></i></span>
                <span class="checkout-steps__label">الدفع</span>
            </div>
            <span class="checkout-steps__sep" aria-hidden="true"></span>
            <div class="checkout-steps__item is-active">
                <span class="checkout-steps__num">3</span>
                <span class="checkout-steps__label">التأكيد</span>
            </div>
        </nav>

        <section class="portal-panel">
            <div class="checkout-success">
                <div class="checkout-success__icon" aria-hidden="true">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h1 class="checkout-success__title">
                    {{ PaymentMethods::usesMoyasar($paymentMethod) ? 'تم الدفع بنجاح' : 'تم إنشاء الطلب' }}
                </h1>
                <p class="checkout-success__ref">
                    رقم الطلب: <strong dir="ltr">{{ $successReference }}</strong>
                </p>

                <div class="checkout-success__meta">
                    <div class="checkout-success__meta-item">
                        <span>طريقة الدفع</span>
                        <strong>{{ PaymentMethods::label($paymentMethod) }}</strong>
                    </div>
                    <div class="checkout-success__meta-item">
                        <span>المبلغ</span>
                        <strong dir="ltr">{{ number_format($total, 2) }} <small>ر.س</small></strong>
                    </div>
                </div>

                @if ($paymentMethod === 'bank_transfer' && $bankInstructions)
                    <div class="checkout-success__bank">
                        @include('partials.commerce.bank-transfer-instructions', [
                            'html' => $bankInstructions,
                            'orderRef' => $successReference,
                            'amount' => (float) $total,
                            'variant' => 'checkout',
                            'showNotice' => true,
                        ])
                    </div>
                    <div class="checkout-pay-note mb-4">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <span>
                            بعد إتمام التحويل البنكي ووصول المبلغ، تقوم الإدارة بمراجعته ثم
                            <strong>تفعيل اشتراكك فعلياً</strong> في البرنامج/الدورة. حتى ذلك الحين يبقى الطلب قيد المراجعة.
                        </span>
                    </div>
                @elseif ($paymentMethod === 'bank_transfer')
                    <p class="text-muted mb-4">بعد إتمام التحويل تتم مراجعة الإدارة ثم تفعيل الاشتراك فعلياً.</p>
                @else
                    <p class="text-muted mb-4">تم تأكيد الدفع وأصبحت مشتركاً فعلياً. يمكنك متابعة التعلم من لوحة التحكم.</p>
                @endif

                <div class="checkout-success__actions">
                    <a href="{{ route('my-orders.show', ['locale' => $locale, 'order' => $successReference]) }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-receipt"></i> تفاصيل الطلب
                    </a>
                    <a href="{{ route('profile', ['locale' => $locale]) }}" class="btn btn-primary">
                        <i class="fa-solid fa-gauge-high"></i> لوحة التحكم
                    </a>
                </div>
            </div>
        </section>
    @elseif ($step === 'pay' && $pendingOrder && $moyasar->isConfigured())
        <nav class="checkout-steps" aria-label="خطوات إتمام الشراء">
            <div class="checkout-steps__item is-done">
                <span class="checkout-steps__num"><i class="fa-solid fa-check"></i></span>
                <span class="checkout-steps__label">المراجعة</span>
            </div>
            <span class="checkout-steps__sep" aria-hidden="true"></span>
            <div class="checkout-steps__item is-active">
                <span class="checkout-steps__num">2</span>
                <span class="checkout-steps__label">الدفع</span>
            </div>
            <span class="checkout-steps__sep" aria-hidden="true"></span>
            <div class="checkout-steps__item">
                <span class="checkout-steps__num">3</span>
                <span class="checkout-steps__label">التأكيد</span>
            </div>
        </nav>

        <section class="portal-panel checkout-pay-shell">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-lock"></i> إتمام الدفع الآمن</h2>
                <span class="portal-commerce-badge" dir="ltr">{{ $pendingOrder->reference }}</span>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                <div class="checkout-pay-amount">
                    <span>المبلغ المستحق</span>
                    <strong dir="ltr">{{ number_format($total, 2) }} <small>ر.س</small></strong>
                </div>

                @if ($paymentError)
                    <div class="alert alert-danger">{{ $paymentError }}</div>
                @endif

                @include('partials.commerce.moyasar-form', [
                    'order' => $pendingOrder,
                    'paymentMethod' => $paymentMethod,
                    'moyasar' => $moyasar,
                ])
            </div>
        </section>
    @else
        <nav class="checkout-steps" aria-label="خطوات إتمام الشراء">
            <div class="checkout-steps__item is-active">
                <span class="checkout-steps__num">1</span>
                <span class="checkout-steps__label">المراجعة</span>
            </div>
            <span class="checkout-steps__sep" aria-hidden="true"></span>
            <div class="checkout-steps__item">
                <span class="checkout-steps__num">2</span>
                <span class="checkout-steps__label">الدفع</span>
            </div>
            <span class="checkout-steps__sep" aria-hidden="true"></span>
            <div class="checkout-steps__item">
                <span class="checkout-steps__num">3</span>
                <span class="checkout-steps__label">التأكيد</span>
            </div>
        </nav>

        <section class="portal-commerce-hero">
            <div class="portal-commerce-hero__main">
                <span class="portal-commerce-hero__icon" aria-hidden="true">
                    <i class="fa-solid fa-credit-card"></i>
                </span>
                <div class="portal-commerce-hero__text">
                    <h1 class="portal-commerce-hero__title">إتمام الشراء</h1>
                    <p class="portal-commerce-hero__desc">اختر طريقة الدفع المناسبة ثم أكّد طلبك بأمان</p>
                </div>
            </div>
            <div class="portal-commerce-hero__aside">
                <div class="portal-commerce-hero__stats">
                    <div class="portal-commerce-hero__stat">
                        <span class="portal-commerce-hero__stat-label">عدد الدورات</span>
                        <strong class="portal-commerce-hero__stat-value">{{ $itemCount }}</strong>
                    </div>
                    <div class="portal-commerce-hero__stat portal-commerce-hero__stat--total">
                        <span class="portal-commerce-hero__stat-label">الإجمالي</span>
                        <strong class="portal-commerce-hero__stat-value" dir="ltr">{{ number_format($total, 2) }} <small>ر.س</small></strong>
                    </div>
                </div>
                <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm portal-commerce-hero__cta">
                    <i class="fa-solid fa-arrow-right"></i> العودة للسلة
                </a>
            </div>
        </section>

        <div class="row g-3 portal-commerce-layout">
            <div class="col-lg-8">
                <section class="portal-panel mb-3">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-list"></i> تفاصيل الطلب</h2>
                        <span class="portal-commerce-badge">{{ $itemCount }} عنصر</span>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="commerce-list">
                            @foreach ($items as $item)
                                @include('components.commerce.list-item', ['item' => $item, 'mode' => 'readonly'])
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="portal-panel mb-3">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-wallet"></i> طريقة الدفع</h2>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        @include('components.commerce.payment-methods', [
                            'selectedMethod' => $paymentMethod,
                            'bankInstructions' => $bankInstructions,
                            'moyasarConfigured' => $moyasar->isConfigured(),
                            'orderTotal' => $total,
                            'hasPlatformPlans' => $installmentPlans->isNotEmpty(),
                        ])

                        @error('paymentMethod')
                            <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                        @enderror
                        @error('installmentPlanId')
                            <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                        @enderror
                        @error('installmentAcknowledged')
                            <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                        @enderror
                        @error('cart')
                            <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                @if (PaymentMethods::isPlatformInstallment($paymentMethod))
                    <section class="portal-panel mb-3">
                        <div class="portal-panel__head">
                            <h2 class="portal-panel__title"><i class="fa-solid fa-calendar-check"></i> خطة التقسيط</h2>
                        </div>
                        <div class="portal-panel__body portal-panel__body--padded">
                            <div class="checkout-flow-steps mb-3">
                                <div class="checkout-flow-steps__item is-current"><span>1</span> اختيار الخطة</div>
                                <div class="checkout-flow-steps__item"><span>2</span> التوقيع والإقرار</div>
                                <div class="checkout-flow-steps__item"><span>3</span> سداد الدفعة الأولى</div>
                                <div class="checkout-flow-steps__item"><span>4</span> تفعيل الاشتراك</div>
                            </div>

                            <div class="checkout-installment-plans">
                                @forelse ($installmentPlans as $plan)
                                    <label class="payment-method-card checkout-plan-card {{ (int) $installmentPlanId === $plan->id ? 'is-selected' : '' }}">
                                        <input type="radio" class="payment-method-input" wire:model.live="installmentPlanId" value="{{ $plan->id }}">
                                        <span class="payment-method-card__check" aria-hidden="true"></span>
                                        <span class="payment-method-card__body checkout-plan-card__body">
                                            <strong class="payment-method-card__title">{{ $plan->name_ar }}</strong>
                                            <span class="payment-method-card__desc">
                                                {{ $plan->description_ar ?: 'خطة تقسيط المنصة' }}
                                                — {{ $plan->items->count() }} دفعات
                                            </span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="checkout-pay-note checkout-pay-note--warn mb-0">
                                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                        <span>لا توجد خطة تقسيط مرتبطة بعناصر سلتك حالياً. يمكنك اختيار سداد كامل أو Tabby/Tamara إن كانت مفعّلة.</span>
                                    </div>
                                @endforelse
                            </div>

                            @if ($selectedPlan && $planPreview !== [])
                                <div class="checkout-plan-preview mt-3">
                                    <div class="checkout-plan-preview__head">
                                        <strong>جدول الأقساط المتوقع</strong>
                                        <span>الإجمالي {{ number_format($total, 2) }} ر.س</span>
                                    </div>
                                    <ul class="checkout-plan-preview__list">
                                        @foreach ($planPreview as $row)
                                            <li @class(['is-first' => $row['is_first']])>
                                                <div>
                                                    <strong>{{ $row['label'] }}</strong>
                                                    <span>{{ number_format($row['percent'], 1) }}%</span>
                                                </div>
                                                <em dir="ltr">{{ number_format($row['amount'], 2) }} ر.س</em>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($firstDue)
                                        <div class="checkout-plan-preview__due">
                                            <span>الدفعة الأولى المستحقة الآن</span>
                                            <strong dir="ltr">{{ number_format($firstDue['amount'], 2) }} ر.س</strong>
                                        </div>
                                    @endif
                                    <p class="checkout-plan-preview__note">
                                        بعد التوقيع وسداد الدفعة الأولى تُفعَّل عضويتك فعلياً في البرنامج/الدبلوم، وتُستحق بقية الأقساط حسب الجدول.
                                    </p>
                                </div>

                                <label class="checkout-ack">
                                    <input type="checkbox" wire:model.live="installmentAcknowledged">
                                    <span>
                                        أقرّ بأنني اطّلعت على نظام التقسيط أعلاه، وأوافق على الالتزام بجدول الأقساط والتوقيع الإلكتروني قبل سداد الدفعة الأولى.
                                    </span>
                                </label>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            <div class="col-lg-4">
                <aside class="portal-panel portal-commerce-summary sticky-top" style="top:100px">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-receipt"></i> ملخص الطلب</h2>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="checkout-summary-items">
                            @foreach ($items as $item)
                                @php($meta = $catalog->resolveForItem($item))
                                <div class="checkout-summary-item">
                                    <span class="checkout-summary-item__title">{{ \Illuminate\Support\Str::limit($meta['title'], 48) }}</span>
                                    <span class="checkout-summary-item__price" dir="ltr">{{ number_format((float) $item->price_snapshot, 0) }} ر.س</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="portal-commerce-summary__rows">
                            <div class="portal-commerce-summary__row">
                                <span>عدد العناصر</span>
                                <strong>{{ $itemCount }}</strong>
                            </div>
                            <div class="portal-commerce-summary__row">
                                <span>طريقة الدفع</span>
                                <strong>{{ PaymentMethods::label($paymentMethod) }}</strong>
                            </div>
                            @if (PaymentMethods::isPlatformInstallment($paymentMethod) && $firstDue)
                                <div class="portal-commerce-summary__row">
                                    <span>الدفعة الأولى</span>
                                    <strong dir="ltr">{{ number_format($firstDue['amount'], 2) }} ر.س</strong>
                                </div>
                            @endif
                            <div class="portal-commerce-summary__row portal-commerce-summary__row--total">
                                <span>{{ PaymentMethods::isPlatformInstallment($paymentMethod) ? 'إجمالي البرنامج' : 'الإجمالي' }}</span>
                                <strong dir="ltr">{{ number_format($total, 2) }} <small>ر.س</small></strong>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary w-100 portal-commerce-summary__checkout"
                            wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            @disabled($checkoutDisabled || (PaymentMethods::isPlatformInstallment($paymentMethod) && ($installmentPlans->isEmpty() || ! $installmentAcknowledged)))
                        >
                            <span wire:loading.remove wire:target="placeOrder">
                                <i class="fa-solid fa-shield-halved"></i> {{ $ctaLabel }}
                            </span>
                            <span wire:loading wire:target="placeOrder">
                                <i class="fa-solid fa-spinner fa-spin"></i> جاري المعالجة...
                            </span>
                        </button>

                        <ul class="checkout-summary-trust">
                            @if (PaymentMethods::isPlatformInstallment($paymentMethod))
                                <li><i class="fa-solid fa-file-signature"></i> توقيع إلكتروني على نظام التقسيط</li>
                                <li><i class="fa-solid fa-coins"></i> تفعيل الاشتراك بعد سداد الدفعة الأولى</li>
                            @elseif (PaymentMethods::isBnplGateway($paymentMethod))
                                <li><i class="fa-solid fa-building-columns"></i> يُسدَّد كامل المبلغ للبوابة</li>
                                <li><i class="fa-solid fa-handshake"></i> التقسيط يتم بينك وبين {{ PaymentMethods::label($paymentMethod) }}</li>
                            @elseif ($paymentMethod === 'bank_transfer')
                                <li><i class="fa-solid fa-clock"></i> التفعيل بعد مراجعة التحويل</li>
                                <li><i class="fa-solid fa-user-check"></i> تصبح مشتركاً فعلياً بعد التأكيد</li>
                            @else
                                <li><i class="fa-solid fa-lock"></i> دفع آمن عبر بوابات معتمدة</li>
                                <li><i class="fa-solid fa-circle-check"></i> تفعيل فوري بعد إتمام السداد</li>
                            @endif
                        </ul>

                        <a href="{{ route('cart', ['locale' => $locale]) }}" class="checkout-back-link">
                            <i class="fa-solid fa-arrow-right"></i> العودة للسلة
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    @endif
</div>

@script
<script>
    const initMoyasarForm = () => {
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

    $wire.on('init-moyasar', () => {
        setTimeout(initMoyasarForm, 150);
    });

    if (document.querySelector('.mysr-form')) {
        setTimeout(initMoyasarForm, 150);
    }
</script>
@endscript

@include('partials.portal.shell-end')
