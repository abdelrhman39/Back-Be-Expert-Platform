<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MoyasarService;
use App\Services\OrderPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        MoyasarService $moyasar,
        OrderPaymentService $payments,
    ): RedirectResponse {
        $paymentId = (string) $request->query('id', '');
        $orderReference = (string) $request->query('order', '');

        if ($paymentId === '' || $orderReference === '') {
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

        if (! $moyasar->verifyPaymentForOrder($paymentId, $order)) {
            return redirect()->route('checkout', [
                'locale' => $locale,
                'order' => $order->reference,
                'payment' => 'failed',
            ])->with('checkout_error', 'تعذر التحقق من الدفع. حاول مرة أخرى أو تواصل مع الدعم.');
        }

        $payments->markAsPaid($order, 'moyasar', $paymentId);

        return redirect()->route('checkout', [
            'locale' => $locale,
            'success' => $order->reference,
        ]);
    }
}
