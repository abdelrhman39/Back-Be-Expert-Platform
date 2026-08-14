@php
    use App\Support\PaymentMethods;

    $methods = PaymentMethods::forCheckout();
    $selected = $selectedMethod ?? 'bank_transfer';
    $hasPlatformPlans = $hasPlatformPlans ?? true;

    if (! $hasPlatformPlans) {
        $methods = array_filter(
            $methods,
            fn ($method) => ($method['group'] ?? '') !== 'platform_installment'
        );
    }

    $groups = [
        'full' => [
            'title' => 'سداد كامل',
            'hint' => 'يُحصَّل المبلغ بالكامل عبر البطاقة أو التحويل — تفعيل فوري بعد السداد (أو بعد مراجعة التحويل البنكي)',
            'ids' => [],
        ],
        'platform' => [
            'title' => 'تقسيط المنصة',
            'hint' => 'خطة مرتبطة بالبرنامج: توقيع ← دفعة أولى ← تفعيل الاشتراك',
            'ids' => [],
        ],
        'bnpl' => [
            'title' => 'تقسيط عبر بوابة خارجية',
            'hint' => 'يُسدَّد كامل المبلغ للبوابة، والتقسيط يتم بين المتدرب والبوابة نفسها',
            'ids' => [],
        ],
    ];

    foreach ($methods as $id => $method) {
        $bucket = match ($method['group'] ?? '') {
            'platform_installment' => 'platform',
            'bnpl' => 'bnpl',
            default => 'full',
        };
        $groups[$bucket]['ids'][$id] = $method;
    }

    $groups = array_filter($groups, fn ($g) => $g['ids'] !== []);
@endphp

@if ($methods === [])
    <div class="checkout-pay-note checkout-pay-note--warn mb-0">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <span>لا توجد بوابات دفع نشطة حالياً. يرجى التواصل مع الدعم.</span>
    </div>
@else
    @foreach ($groups as $groupKey => $group)
        <div class="checkout-pay-group" wire:key="pay-group-{{ $groupKey }}">
            <div class="checkout-pay-group__head">
                <h3 class="checkout-pay-group__title">{{ $group['title'] }}</h3>
                <p class="checkout-pay-group__hint">{{ $group['hint'] }}</p>
            </div>

            <div class="payment-methods-grid">
                @foreach ($group['ids'] as $method)
                    <label class="payment-method-card {{ $selected === $method['id'] ? 'is-selected' : '' }}" for="pay_{{ $method['id'] }}">
                        <input type="radio"
                            class="payment-method-input"
                            wire:model.live="paymentMethod"
                            id="pay_{{ $method['id'] }}"
                            value="{{ $method['id'] }}">

                        <span class="payment-method-card__check" aria-hidden="true"></span>

                        <span class="payment-method-card__icon">
                            @if ($method['id'] === 'bank_transfer')
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M3 10H21M7 15H7.01M11 15H13M3 6C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 6V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V6Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            @elseif ($method['id'] === 'apple_pay')
                                <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M16.365 12.17c.033-2.144 1.745-3.17 1.823-3.223-1.003-1.465-2.563-1.665-3.113-1.685-1.327-.134-2.593.782-3.266.782-.686 0-1.733-.763-2.848-.743-1.465.02-2.817.852-3.57 2.164-1.524 2.644-.39 6.558 1.095 8.706.726 1.048 1.59 2.223 2.724 2.18 1.09-.043 1.5-.705 2.817-.705 1.303 0 1.676.705 2.823.684 1.166-.02 1.908-1.06 2.622-2.113.826-1.206 1.166-2.374 1.186-2.434-.026-.012-2.276-.873-2.295-3.453zm-2.14-6.342c.6-.728 1.003-1.742.892-2.748-.862.035-1.904.575-2.524 1.302-.556.644-1.043 1.674-.913 2.66.967.075 1.956-.492 2.545-1.214z"/>
                                </svg>
                            @elseif ($method['icon'])
                                <img src="{{ static_asset($method['icon']) }}" alt="{{ $method['label'] }}" loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                <span class="payment-method-fallback" style="display:none;">{{ $method['label'] }}</span>
                            @else
                                <span class="payment-method-fallback">{{ $method['label'] }}</span>
                            @endif
                        </span>

                        <span class="payment-method-card__body">
                            <strong class="payment-method-card__title">{{ $method['label'] }}</strong>
                            <span class="payment-method-card__desc">{{ $method['description'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    @if ($selected === 'bank_transfer' && ! empty($bankInstructions))
        <div class="mt-3">
            @include('partials.commerce.bank-transfer-instructions', [
                'html' => $bankInstructions,
                'amount' => isset($orderTotal) ? (float) $orderTotal : null,
                'variant' => 'checkout',
            ])
        </div>
        <div class="checkout-pay-note mt-3 mb-0">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <span>بعد إتمام التحويل ومراجعة الإدارة يتم تفعيل اشتراكك فعلياً في البرنامج.</span>
        </div>
    @elseif ($selected !== 'bank_transfer')
        <div @class(['checkout-pay-note', 'checkout-pay-note--warn' => ! ($moyasarConfigured ?? false) && \App\Support\PaymentMethods::usesMoyasar($selected)])>
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            @if (($moyasarConfigured ?? false) && \App\Support\PaymentMethods::usesMoyasar($selected))
                <span>بعد تأكيد الطلب ستسدّد <strong>كامل المبلغ</strong> إلكترونياً، ثم تُفعَّل عضويتك فوراً.</span>
            @elseif (\App\Support\PaymentMethods::isPlatformInstallment($selected))
                <span>اختر الخطة المرتبطة ببرنامجك أدناه، ثم وقّع وأقرّ النظام، وبعد سداد الدفعة الأولى تصبح مشتركاً فعلياً.</span>
            @elseif (\App\Support\PaymentMethods::isBnplGateway($selected))
                <span>
                    سيُرسل <strong>كامل مبلغ الطلب</strong> إلى {{ PaymentMethods::label($selected) }}.
                    التقسيط يتم بينك وبين البوابة مباشرة، وبعد نجاح الدفع تُفعَّل عضويتك في المنصة.
                </span>
            @else
                <span>بعد تأكيد الطلب سيتم توجيهك لإتمام الدفع عبر {{ PaymentMethods::label($selected) }}.</span>
            @endif
        </div>
    @endif
@endif
