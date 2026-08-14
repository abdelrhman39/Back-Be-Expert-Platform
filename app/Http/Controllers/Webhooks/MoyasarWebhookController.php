<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MoyasarService;
use App\Services\OrderPaymentService;
use App\Support\MoyasarSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoyasarWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        MoyasarService $moyasar,
        OrderPaymentService $payments,
    ): JsonResponse {
        $payload = $request->all();
        $secret = MoyasarSettings::webhookSecret();

        if (! $secret) {
            Log::error('Moyasar webhook rejected: webhook secret is not configured');

            return response()->json(['message' => 'Webhook is not configured'], 503);
        }

        $providedSecret = (string) ($payload['secret_token'] ?? '');

        if ($providedSecret === '' || ! hash_equals($secret, $providedSecret)) {
            Log::warning('Moyasar webhook rejected: invalid secret');

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];
        $paymentId = $data['id'] ?? null;

        if (! $paymentId || ! in_array($type, ['payment_paid', 'payment_captured'], true)) {
            return response()->json(['received' => true]);
        }

        $metadata = $data['metadata'] ?? [];
        $order = ! empty($metadata['order_id']) && ! empty($metadata['order_reference'])
            ? Order::query()
                ->whereKey($metadata['order_id'])
                ->where('reference', $metadata['order_reference'])
                ->first()
            : null;

        if (! $order) {
            Log::info('Moyasar webhook: order not found', ['payment_id' => $paymentId]);

            return response()->json(['received' => true]);
        }

        if ($moyasar->verifyPaymentForOrder($paymentId, $order)) {
            $payments->markAsPaid($order, 'moyasar', $paymentId, $paymentId);
        }

        return response()->json(['received' => true]);
    }
}
