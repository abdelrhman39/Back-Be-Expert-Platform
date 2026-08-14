<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Services\TamaraService;
use App\Support\BnplSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TamaraWebhookController extends Controller
{
    public function __invoke(Request $request, TamaraService $tamara, OrderPaymentService $payments): JsonResponse
    {
        $notificationToken = BnplSettings::tamaraNotificationToken();
        $provided = (string) $request->header('Authorization', $request->input('tamaraToken', ''));

        if ($notificationToken && $provided !== '' && ! str_contains($provided, $notificationToken)) {
            // Tamara often sends the notification token in the Authorization header.
            if (! hash_equals($notificationToken, trim(str_ireplace('Bearer', '', $provided)))) {
                Log::warning('Tamara webhook rejected: invalid notification token');

                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->all();
        $event = strtoupper((string) data_get($payload, 'event_type', data_get($payload, 'order_status', '')));
        $orderId = (string) data_get($payload, 'order_id', data_get($payload, 'data.order_id', ''));
        $reference = (string) data_get($payload, 'order_reference_id', data_get($payload, 'data.order_reference_id', ''));

        $accepted = in_array($event, [
            'ORDER_APPROVED',
            'ORDER_AUTHORISED',
            'ORDER_AUTHORIZED',
            'ORDER_CAPTURED',
            'fully_captured',
            'approved',
            'authorised',
            'authorized',
        ], true) || in_array(strtoupper((string) data_get($payload, 'order_status', '')), [
            'APPROVED', 'AUTHORISED', 'AUTHORIZED', 'FULLY_CAPTURED',
        ], true);

        if (! $accepted || ($orderId === '' && $reference === '')) {
            return response()->json(['received' => true]);
        }

        $order = $reference !== ''
            ? Order::query()->where('reference', $reference)->first()
            : Order::query()->where('gateway', 'tamara')->where('gateway_payment_id', $orderId)->first();

        if (! $order) {
            Log::info('Tamara webhook: order not found', ['order_id' => $orderId, 'reference' => $reference]);

            return response()->json(['received' => true]);
        }

        $verifyId = $orderId !== '' ? $orderId : (string) $order->gateway_payment_id;

        if ($verifyId !== '' && $tamara->verifyPayment($verifyId, $order)) {
            $payments->markAsPaid($order, 'tamara', $verifyId);
        }

        return response()->json(['received' => true]);
    }
}
