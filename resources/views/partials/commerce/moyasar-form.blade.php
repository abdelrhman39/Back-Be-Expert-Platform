@php
    $locale = app()->getLocale();
    $callbackUrl = route('checkout.callback', [
        'locale' => $locale,
        'order' => $order->reference,
    ]);
    $metadata = [
        'order_id' => $order->id,
        'order_reference' => $order->reference,
        'user_id' => auth()->id(),
    ];
@endphp

<link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.14.0/moyasar.css">
<script src="https://cdn.moyasar.com/mpf/1.14.0/moyasar.js"></script>

<div
    class="mysr-form"
    data-amount="{{ $moyasar->amountInHalalas((float) $order->total) }}"
    data-currency="{{ \App\Support\MoyasarSettings::currency() }}"
    data-description="طلب {{ $order->reference }}"
    data-publishable-key="{{ $moyasar->publishableKey() }}"
    data-callback-url="{{ $callbackUrl }}"
    data-methods="{{ json_encode($moyasar->methodsForPaymentMethod($paymentMethod)) }}"
    data-metadata="{{ json_encode($metadata) }}"
></div>

<p class="text-muted small text-center mt-3 mb-0">
    <i class="fa-solid fa-lock"></i> الدفع آمن عبر Moyasar
</p>
