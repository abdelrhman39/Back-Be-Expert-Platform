<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\InstallmentContract;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Services\MoyasarService;
use App\Services\OrderPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstallmentCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        InstallmentContract $contract,
        InstallmentSchedule $schedule,
        MoyasarService $moyasar,
        OrderPaymentService $payments,
    ): RedirectResponse {
        abort_unless($contract->user_id === auth()->id(), 403);
        abort_unless($schedule->contract_id === $contract->id, 404);

        $paymentId = (string) $request->query('id', '');
        $orderReference = (string) $request->query('order', '');

        if ($paymentId === '' || $orderReference === '') {
            return redirect()->route('installments.pay', [
                'locale' => $locale,
                'contract' => $contract->id,
                'schedule' => $schedule->id,
                'payment' => 'failed',
            ])->with('checkout_error', 'بيانات الدفع غير مكتملة.');
        }

        $order = Order::query()
            ->where('reference', $orderReference)
            ->where('user_id', auth()->id())
            ->where('installment_schedule_id', $schedule->id)
            ->firstOrFail();

        if (! $moyasar->verifyPaymentForOrder($paymentId, $order)) {
            return redirect()->route('installments.pay', [
                'locale' => $locale,
                'contract' => $contract->id,
                'schedule' => $schedule->id,
                'order' => $order->reference,
                'payment' => 'failed',
            ])->with('checkout_error', 'تعذر التحقق من الدفع.');
        }

        $payments->markAsPaid($order, 'moyasar', $paymentId);

        return redirect()->route('installments.pay', [
            'locale' => $locale,
            'contract' => $contract->id,
            'schedule' => $schedule->id,
            'success' => '1',
        ]);
    }
}
