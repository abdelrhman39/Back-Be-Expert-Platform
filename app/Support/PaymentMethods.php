<?php

namespace App\Support;

class PaymentMethods
{
    /** @return array<string, array{id: string, label: string, description: string, icon: ?string, group: string}> */
    public static function forCheckout(): array
    {
        $methods = [];

        foreach (static::all() as $id => $method) {
            if (PaymentGatewaySettings::isEnabled($id)) {
                $methods[$id] = $method;
            }
        }

        return $methods;
    }

    /** @return array<string, array{id: string, label: string, description: string, icon: ?string, group: string}> */
    public static function all(): array
    {
        return [
            'bank_transfer' => [
                'id' => 'bank_transfer',
                'label' => 'تحويل بنكي',
                'description' => 'سداد كامل المبلغ عبر التحويل البنكي',
                'icon' => null,
                'group' => 'offline',
            ],
            'mada' => [
                'id' => 'mada',
                'label' => 'مدى',
                'description' => 'سداد كامل المبلغ ببطاقة مدى',
                'icon' => 'assets/mada_mini.webp',
                'group' => 'card',
            ],
            'visa' => [
                'id' => 'visa',
                'label' => 'Visa',
                'description' => 'سداد كامل المبلغ ببطاقة فيزا',
                'icon' => 'assets/credit_card_mini.png',
                'group' => 'card',
            ],
            'mastercard' => [
                'id' => 'mastercard',
                'label' => 'Mastercard',
                'description' => 'سداد كامل المبلغ ببطاقة ماستركارد',
                'icon' => 'assets/credit_card_mini.png',
                'group' => 'card',
            ],
            'apple_pay' => [
                'id' => 'apple_pay',
                'label' => 'Apple Pay',
                'description' => 'سداد كامل المبلغ عبر Apple Pay',
                'icon' => null,
                'group' => 'wallet',
            ],
            'tabby' => [
                'id' => 'tabby',
                'label' => 'Tabby',
                'description' => 'سداد كامل للمنصة — التقسيط بينك وبين Tabby',
                'icon' => 'assets/tabby_installment_mini.png',
                'group' => 'bnpl',
            ],
            'tamara' => [
                'id' => 'tamara',
                'label' => 'Tamara',
                'description' => 'سداد كامل للمنصة — التقسيط بينك وبين Tamara',
                'icon' => 'assets/tamara.png',
                'group' => 'bnpl',
            ],
            'platform_installment' => [
                'id' => 'platform_installment',
                'label' => 'تقسيط المنصة',
                'description' => 'خطة مرتبطة بالبرنامج — توقيع ثم سداد الدفعة الأولى',
                'icon' => 'assets/credit_card_mini.png',
                'group' => 'platform_installment',
            ],
        ];
    }

    public static function ids(): array
    {
        return array_keys(static::all());
    }

    /** @return array<int, string> */
    public static function checkoutIds(): array
    {
        return array_keys(static::forCheckout());
    }

    public static function find(string $id): ?array
    {
        return static::all()[$id] ?? null;
    }

    public static function label(string $id): string
    {
        return static::find($id)['label'] ?? $id;
    }

    public static function usesMoyasar(string $id): bool
    {
        $method = static::find($id);

        return in_array($method['group'] ?? '', ['card', 'wallet'], true);
    }

    public static function isOffline(string $id): bool
    {
        return (static::find($id)['group'] ?? '') === 'offline';
    }

    /** Third-party BNPL (Tabby / Tamara): full order amount; gateway owns installments. */
    public static function isBnplGateway(string $id): bool
    {
        return (static::find($id)['group'] ?? '') === 'bnpl';
    }

    public static function isInstallment(string $id): bool
    {
        $group = static::find($id)['group'] ?? '';

        return in_array($group, ['bnpl', 'installment', 'platform_installment'], true);
    }

    public static function isPlatformInstallment(string $id): bool
    {
        return (static::find($id)['group'] ?? '') === 'platform_installment';
    }
}
