<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Services\TabbyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TabbyWebhookController extends Controller
{
    public function __invoke(Request $request, TabbyService $tabby, OrderPaymentService $payments): JsonResponse
    {
        $payload = $request->all();
        $status = strtoupper((string) data_get($payload, 'status', data_get($payload, 'payment.status', '')));
        $paymentId = (string) data_get($payload, 'id', data_get($payload, 'payment.id', ''));
        $reference = (string) data_get($payload, 'order.reference_id', data_get($payload, 'payment.order.reference_id', ''));

        if ($paymentId === '' || ! in_array($status, ['AUTHORIZED', 'CLOSED', 'CAPTURED'], true)) {
            return response()->json(['received' => true]);
        }

        $order = $reference !== ''
            ? Order::query()->where('reference', $reference)->first()
            : Order::query()->where('gateway', 'tabby')->where('gateway_payment_id', $paymentId)->first();

        if (! $order) {
            Log::info('Tabby webhook: order not found', ['payment_id' => $paymentId, 'reference' => $reference]);

            return response()->json(['received' => true]);
        }

        if ($tabby->verifyPayment($paymentId, $order)) {
            $payments->markAsPaid($order, 'tabby', $paymentId);
        }

        return response()->json(['received' => true]);
    }
}
