<?php

namespace App\Services;

use App\Models\Order;
use App\Support\BnplSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TabbyService
{
    public function isConfigured(): bool
    {
        return BnplSettings::tabbyReady();
    }

    /**
     * Create a Tabby hosted checkout for the FULL order amount.
     * Tabby (not the platform) owns the installment schedule.
     */
    public function createCheckoutUrl(Order $order): string
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'بوابة Tabby غير مهيأة حالياً.',
            ]);
        }

        $order->loadMissing(['items', 'user']);
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $user = $order->user;
        $amount = number_format((float) $order->total, 2, '.', '');

        $payload = [
            'payment' => [
                'amount' => $amount,
                'currency' => $order->currency ?: 'SAR',
                'description' => 'طلب '.$order->reference,
                'buyer' => [
                    'name' => $user?->displayName() ?: 'عميل',
                    'email' => $user?->email ?: 'noreply@example.com',
                    'phone' => $this->normalizePhone($user?->phone),
                ],
                'shipping_address' => [
                    'city' => 'Riyadh',
                    'address' => 'Digital delivery',
                    'zip' => '11564',
                ],
                'order' => [
                    'reference_id' => $order->reference,
                    'items' => $order->items->map(fn ($item) => [
                        'title' => $item->course_title ?: ('Course #'.$item->course_id),
                        'quantity' => 1,
                        'unit_price' => number_format((float) $item->price, 2, '.', ''),
                        'category' => 'education',
                    ])->values()->all(),
                ],
                'meta' => [
                    'order_id' => (string) $order->id,
                    'order_reference' => $order->reference,
                ],
            ],
            'lang' => $locale,
            'merchant_code' => BnplSettings::tabbyMerchantCode(),
            'merchant_urls' => [
                'success' => route('checkout.bnpl.callback', ['locale' => $locale, 'provider' => 'tabby', 'order' => $order->reference]),
                'cancel' => route('checkout', ['locale' => $locale, 'order' => $order->reference, 'payment' => 'failed']),
                'failure' => route('checkout', ['locale' => $locale, 'order' => $order->reference, 'payment' => 'failed']),
            ],
        ];

        $response = Http::withToken((string) BnplSettings::tabbySecretKey())
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->baseUrl().'/api/v2/checkout', $payload);

        if (! $response->successful()) {
            Log::warning('Tabby checkout failed', [
                'order' => $order->reference,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw ValidationException::withMessages([
                'paymentMethod' => 'تعذر إنشاء جلسة Tabby. تحقق من الإعدادات أو جرّب طريقة دفع أخرى.',
            ]);
        }

        $data = $response->json();
        $paymentId = data_get($data, 'payment.id');
        $checkoutUrl = data_get($data, 'configuration.available_products.installments.0.web_url')
            ?: data_get($data, 'configuration.products.installments.web_url')
            ?: data_get($data, 'configuration.available_products.installments.web_url');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            // Fallback: some Tabby responses expose api_url / web_url at top level configuration
            $checkoutUrl = data_get($data, 'configuration.api_url')
                ?: data_get($data, 'web_url');
        }

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            Log::warning('Tabby checkout missing redirect URL', ['order' => $order->reference, 'body' => $data]);

            throw ValidationException::withMessages([
                'paymentMethod' => 'Tabby لم يُرجع رابط دفع صالحاً لهذا الطلب.',
            ]);
        }

        $order->update([
            'gateway' => 'tabby',
            'gateway_payment_id' => is_string($paymentId) ? $paymentId : $order->gateway_payment_id,
        ]);

        return $checkoutUrl;
    }

    public function verifyPayment(string $paymentId, Order $order): bool
    {
        if (! $this->isConfigured() || $paymentId === '') {
            return false;
        }

        $response = Http::withToken((string) BnplSettings::tabbySecretKey())
            ->acceptJson()
            ->timeout(20)
            ->get($this->baseUrl().'/api/v2/payments/'.$paymentId);

        if (! $response->successful()) {
            return false;
        }

        $status = strtoupper((string) data_get($response->json(), 'status', ''));
        $amount = (float) data_get($response->json(), 'amount', 0);
        $reference = (string) data_get($response->json(), 'order.reference_id', '');

        if (! in_array($status, ['AUTHORIZED', 'CLOSED', 'CAPTURED'], true)) {
            return false;
        }

        if (abs($amount - (float) $order->total) > 0.05) {
            return false;
        }

        return $reference === '' || hash_equals($order->reference, $reference);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.tabby.base_url', 'https://api.tabby.sa'), '/');
    }

    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if (str_starts_with($digits, '966')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+966'.substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '+966'.$digits;
        }

        return '+966500000001';
    }
}
