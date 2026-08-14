<?php

namespace App\Services;

use App\Models\Order;
use App\Support\BnplSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TamaraService
{
    public function isConfigured(): bool
    {
        return BnplSettings::tamaraReady();
    }

    /**
     * Create a Tamara hosted checkout for the FULL order amount.
     * Tamara (not the platform) owns the installment schedule.
     */
    public function createCheckoutUrl(Order $order): string
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'بوابة Tamara غير مهيأة حالياً.',
            ]);
        }

        $order->loadMissing(['items', 'user']);
        $locale = app()->getLocale() === 'en' ? 'en_US' : 'ar_SA';
        $user = $order->user;
        $amount = round((float) $order->total, 2);

        $payload = [
            'total_amount' => [
                'amount' => $amount,
                'currency' => $order->currency ?: 'SAR',
            ],
            'shipping_amount' => [
                'amount' => 0,
                'currency' => $order->currency ?: 'SAR',
            ],
            'tax_amount' => [
                'amount' => 0,
                'currency' => $order->currency ?: 'SAR',
            ],
            'order_reference_id' => $order->reference,
            'order_number' => $order->reference,
            'currency' => $order->currency ?: 'SAR',
            'description' => 'طلب '.$order->reference,
            'country_code' => 'SA',
            'payment_type' => 'PAY_BY_INSTALMENTS',
            'locale' => $locale,
            'items' => $order->items->map(fn ($item) => [
                'reference_id' => (string) ($item->course_id ?: $item->id),
                'type' => 'Digital',
                'name' => $item->course_title ?: ('Course #'.$item->course_id),
                'sku' => 'course-'.($item->course_id ?: $item->id),
                'quantity' => 1,
                'total_amount' => [
                    'amount' => round((float) $item->price, 2),
                    'currency' => $order->currency ?: 'SAR',
                ],
            ])->values()->all(),
            'consumer' => [
                'first_name' => $this->firstName($user?->displayName()),
                'last_name' => $this->lastName($user?->displayName()),
                'phone_number' => $this->normalizePhone($user?->phone),
                'email' => $user?->email ?: 'noreply@example.com',
            ],
            'shipping_address' => [
                'first_name' => $this->firstName($user?->displayName()),
                'last_name' => $this->lastName($user?->displayName()),
                'line1' => 'Digital delivery',
                'city' => 'Riyadh',
                'country_code' => 'SA',
                'phone_number' => $this->normalizePhone($user?->phone),
            ],
            'merchant_url' => [
                'success' => route('checkout.bnpl.callback', ['locale' => app()->getLocale(), 'provider' => 'tamara', 'order' => $order->reference]),
                'failure' => route('checkout', ['locale' => app()->getLocale(), 'order' => $order->reference, 'payment' => 'failed']),
                'cancel' => route('checkout', ['locale' => app()->getLocale(), 'order' => $order->reference, 'payment' => 'failed']),
                'notification' => url('/webhooks/tamara'),
            ],
        ];

        $response = Http::withToken((string) BnplSettings::tamaraApiToken())
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->baseUrl().'/checkout', $payload);

        if (! $response->successful()) {
            Log::warning('Tamara checkout failed', [
                'order' => $order->reference,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw ValidationException::withMessages([
                'paymentMethod' => 'تعذر إنشاء جلسة Tamara. تحقق من الإعدادات أو جرّب طريقة دفع أخرى.',
            ]);
        }

        $data = $response->json();
        $checkoutUrl = data_get($data, 'checkout_url');
        $checkoutId = data_get($data, 'checkout_id') ?: data_get($data, 'order_id');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Tamara لم يُرجع رابط دفع صالحاً لهذا الطلب.',
            ]);
        }

        $order->update([
            'gateway' => 'tamara',
            'gateway_payment_id' => is_string($checkoutId) ? $checkoutId : $order->gateway_payment_id,
        ]);

        return $checkoutUrl;
    }

    public function verifyPayment(string $orderId, Order $order): bool
    {
        if (! $this->isConfigured() || $orderId === '') {
            return false;
        }

        $response = Http::withToken((string) BnplSettings::tamaraApiToken())
            ->acceptJson()
            ->timeout(20)
            ->get($this->baseUrl().'/orders/'.$orderId);

        if (! $response->successful()) {
            return false;
        }

        $status = strtoupper((string) data_get($response->json(), 'status', ''));
        $amount = (float) data_get($response->json(), 'total_amount.amount', 0);
        $reference = (string) data_get($response->json(), 'order_reference_id', '');

        if (! in_array($status, ['APPROVED', 'AUTHORISED', 'AUTHORIZED', 'FULLY_CAPTURED', 'PARTIALLY_CAPTURED'], true)) {
            return false;
        }

        if (abs($amount - (float) $order->total) > 0.05) {
            return false;
        }

        return $reference === '' || hash_equals($order->reference, $reference);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.tamara.base_url', 'https://api.tamara.co'), '/');
    }

    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            return substr($digits, 3);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return substr($digits, 1);
        }

        return $digits !== '' ? $digits : '500000001';
    }

    protected function firstName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];

        return $parts[0] ?? 'Customer';
    }

    protected function lastName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];

        return count($parts) > 1 ? (string) end($parts) : 'User';
    }
}
