<?php

namespace App\Services;

use App\Models\Order;
use App\Support\MoyasarSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoyasarService
{
    public function isConfigured(): bool
    {
        return MoyasarSettings::isConfigured();
    }

    public function publishableKey(): ?string
    {
        return MoyasarSettings::publishableKey();
    }

    public function amountInHalalas(float $amountSar): int
    {
        return (int) round($amountSar * 100);
    }

    /** @return array<string, mixed>|null */
    public function fetchPayment(string $paymentId): ?array
    {
        $secret = MoyasarSettings::secretKey();

        if (! $secret) {
            return null;
        }

        $response = Http::withBasicAuth($secret, '')
            ->acceptJson()
            ->get('https://api.moyasar.com/v1/payments/'.$paymentId);

        if (! $response->successful()) {
            Log::warning('Moyasar fetch payment failed', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }

    public function verifyPaymentForOrder(string $paymentId, Order $order): bool
    {
        $payment = $this->fetchPayment($paymentId);

        if (! $payment) {
            return false;
        }

        if (($payment['status'] ?? '') !== 'paid') {
            return false;
        }

        $expectedCurrency = strtoupper((string) ($order->currency ?: MoyasarSettings::currency()));

        if (strtoupper((string) ($payment['currency'] ?? '')) !== $expectedCurrency) {
            return false;
        }

        if ((int) ($payment['amount'] ?? 0) !== $this->amountInHalalas((float) $order->total)) {
            return false;
        }

        $metadata = $payment['metadata'] ?? [];

        if (! is_array($metadata)
            || ! isset($metadata['order_id'], $metadata['order_reference'])
            || (int) $metadata['order_id'] !== (int) $order->id
            || ! hash_equals((string) $order->reference, (string) $metadata['order_reference'])) {
            return false;
        }

        return true;
    }

    /** @return list<string> */
    public function methodsForPaymentMethod(string $paymentMethodId): array
    {
        return match ($paymentMethodId) {
            'mada' => ['mada'],
            'apple_pay' => ['applepay'],
            default => ['creditcard'],
        };
    }
}
