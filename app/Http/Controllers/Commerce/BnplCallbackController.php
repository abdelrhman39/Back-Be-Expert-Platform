<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Services\TabbyService;
use App\Services\TamaraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BnplCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        string $provider,
        OrderPaymentService $payments,
        TabbyService $tabby,
        TamaraService $tamara,
    ): RedirectResponse {
        $provider = strtolower($provider);
        $orderReference = (string) $request->query('order', '');

        if (! in_array($provider, ['tabby', 'tamara'], true) || $orderReference === '') {
            return redirect()
                ->route('checkout', ['locale' => $locale, 'payment' => 'failed'])
                ->with('checkout_error', 'بيانات الدفع غير مكتملة.');
        }

        $order = Order::query()
            ->where('reference', $orderReference)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status === 'paid') {
            return redirect()->route('checkout', [
                'locale' => $locale,
                'success' => $order->reference,
            ]);
        }

        $paymentId = (string) ($request->query('payment_id')
            ?: $request->query('orderId')
            ?: $request->query('order_id')
            ?: $order->gateway_payment_id
            ?: '');

        $verified = match ($provider) {
            'tabby' => $paymentId !== '' && $tabby->verifyPayment($paymentId, $order),
            'tamara' => $paymentId !== '' && $tamara->verifyPayment($paymentId, $order),
            default => false,
        };

        if (! $verified) {
            return redirect()->route('checkout', [
                'locale' => $locale,
                'order' => $order->reference,
                'payment' => 'failed',
            ])->with('checkout_error', 'تعذر التحقق من دفع '.ucfirst($provider).'.');
        }

        $payments->markAsPaid($order, $provider, $paymentId);

        return redirect()->route('checkout', [
            'locale' => $locale,
            'success' => $order->reference,
        ]);
    }
}
