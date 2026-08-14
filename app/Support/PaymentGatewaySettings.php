<?php

namespace App\Support;

use App\Models\PaymentSetting;
use App\Services\InstallmentCheckoutService;

class PaymentGatewaySettings
{
    /** @var array<string, string> */
    public const KEYS = [
        'bank_transfer' => 'payment_bank_transfer_enabled',
        'mada' => 'payment_mada_enabled',
        'visa' => 'payment_visa_enabled',
        'mastercard' => 'payment_mastercard_enabled',
        'apple_pay' => 'payment_apple_pay_enabled',
        'tabby' => 'payment_tabby_enabled',
        'tamara' => 'payment_tamara_enabled',
        'platform_installment' => 'payment_platform_installment_enabled',
    ];

    /** @var array<string, bool> */
    protected const DEFAULTS = [
        'bank_transfer' => true,
        'mada' => true,
        'visa' => true,
        'mastercard' => true,
        'apple_pay' => true,
        'tabby' => false,
        'tamara' => false,
        'platform_installment' => true,
    ];

    public static function isEnabled(string $methodId): bool
    {
        if (! PaymentMethods::find($methodId)) {
            return false;
        }

        if (! static::toggleEnabled($methodId)) {
            return false;
        }

        return match ($methodId) {
            'bank_transfer' => static::bankTransferReady(),
            'mada', 'visa', 'mastercard', 'apple_pay' => MoyasarSettings::isConfigured(),
            'tabby', 'tamara' => static::thirdPartyInstallmentReady($methodId),
            'platform_installment' => static::platformInstallmentReady(),
            default => false,
        };
    }

    public static function setEnabled(string $methodId, bool $enabled): void
    {
        $key = static::KEYS[$methodId] ?? null;

        if (! $key) {
            return;
        }

        PaymentSetting::set($key, $enabled ? '1' : '0');
    }

    public static function toggleEnabled(string $methodId): bool
    {
        $key = static::KEYS[$methodId] ?? null;

        if (! $key) {
            return static::DEFAULTS[$methodId] ?? false;
        }

        $stored = PaymentSetting::get($key);

        if ($stored === null || $stored === '') {
            return static::DEFAULTS[$methodId] ?? false;
        }

        return in_array(strtolower($stored), ['1', 'true', 'yes', 'on'], true);
    }

    protected static function bankTransferReady(): bool
    {
        return filled(PaymentSetting::get('bank_transfer_instructions_ar'));
    }

    protected static function thirdPartyInstallmentReady(string $methodId): bool
    {
        return match ($methodId) {
            'tabby' => BnplSettings::tabbyReady(),
            'tamara' => BnplSettings::tamaraReady(),
            default => false,
        };
    }

    protected static function platformInstallmentReady(): bool
    {
        if (! InstallmentSettings::checkoutEnabled()) {
            return false;
        }

        if (! MoyasarSettings::isConfigured()) {
            return false;
        }

        return app(InstallmentCheckoutService::class)->availablePlans()->isNotEmpty();
    }

    /** @return array<string, bool> */
    public static function togglesForAdmin(): array
    {
        $toggles = [];

        foreach (array_keys(static::KEYS) as $methodId) {
            $toggles[$methodId] = static::toggleEnabled($methodId);
        }

        return $toggles;
    }
}
