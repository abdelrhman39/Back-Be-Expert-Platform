<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPaymentService
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    public function markAsPaid(Order $order, string $gateway, string $gatewayPaymentId, ?string $paymentRef = null): Order
    {
        return DB::transaction(function () use ($order, $gateway, $gatewayPaymentId, $paymentRef) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            $usedByAnotherOrder = Order::query()
                ->where('gateway', $gateway)
                ->where('gateway_payment_id', $gatewayPaymentId)
                ->where('id', '!=', $lockedOrder->id)
                ->exists();

            if ($usedByAnotherOrder) {
                throw ValidationException::withMessages([
                    'payment' => 'عملية الدفع مستخدمة مسبقاً لطلب آخر.',
                ]);
            }

            if ($lockedOrder->status === 'paid'
                && filled($lockedOrder->gateway_payment_id)
                && ! hash_equals((string) $lockedOrder->gateway_payment_id, $gatewayPaymentId)) {
                throw ValidationException::withMessages([
                    'payment' => 'الطلب مرتبط بعملية دفع مختلفة.',
                ]);
            }

            $wasAlreadyPaid = $lockedOrder->status === 'paid';

            if (! $wasAlreadyPaid) {
                $lockedOrder->update([
                    'status' => 'paid',
                    'gateway' => $gateway,
                    'gateway_payment_id' => $gatewayPaymentId,
                    'payment_ref' => $paymentRef ?? $gatewayPaymentId,
                    'paid_at' => now(),
                ]);
            }

            $lockedOrder = $lockedOrder->fresh();

            if ($lockedOrder->installment_schedule_id) {
                app(InstallmentPaymentService::class)->processPaidOrder($lockedOrder, $gateway, $gatewayPaymentId);

                return $lockedOrder->fresh();
            }

            if (! $wasAlreadyPaid) {
                $this->enrollments->syncFromOrder($lockedOrder);
            }

            return $lockedOrder;
        });
    }
}
