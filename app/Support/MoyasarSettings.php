<?php

namespace App\Support;

use App\Models\PaymentSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class MoyasarSettings
{
    public const SECRET_KEY = 'moyasar_secret_key';

    public const PUBLISHABLE_KEY = 'moyasar_publishable_key';

    public const WEBHOOK_SECRET = 'moyasar_webhook_secret';

    public const CURRENCY = 'moyasar_currency';

    public const ENABLED = 'moyasar_enabled';

    public static function isEnabled(): bool
    {
        $stored = PaymentSetting::get(self::ENABLED);

        if ($stored !== null && $stored !== '') {
            return in_array(strtolower($stored), ['1', 'true', 'yes', 'on'], true);
        }

        return self::secretKey() !== null && self::publishableKey() !== null;
    }

    public static function secretKey(): ?string
    {
        return self::value(self::SECRET_KEY, 'moyasar.secret_key');
    }

    public static function publishableKey(): ?string
    {
        return self::value(self::PUBLISHABLE_KEY, 'moyasar.publishable_key');
    }

    public static function webhookSecret(): ?string
    {
        return self::value(self::WEBHOOK_SECRET, 'moyasar.webhook_secret');
    }

    public static function currency(): string
    {
        return self::value(self::CURRENCY, 'moyasar.currency') ?: 'SAR';
    }

    public static function isConfigured(): bool
    {
        return self::isEnabled()
            && filled(self::secretKey())
            && filled(self::publishableKey())
            && filled(self::webhookSecret());
    }

    public static function hasStoredSecretKey(): bool
    {
        return filled(PaymentSetting::get(self::SECRET_KEY));
    }

    public static function hasStoredWebhookSecret(): bool
    {
        return filled(PaymentSetting::get(self::WEBHOOK_SECRET));
    }

    public static function setEnabled(bool $enabled): void
    {
        PaymentSetting::set(self::ENABLED, $enabled ? '1' : '0');
    }

    public static function setSecretKey(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::SECRET_KEY, self::encrypt($value));
        }
    }

    public static function setPublishableKey(?string $value): void
    {
        PaymentSetting::set(self::PUBLISHABLE_KEY, $value ?? '');
    }

    public static function setWebhookSecret(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::WEBHOOK_SECRET, self::encrypt($value));
        }
    }

    public static function setCurrency(string $currency): void
    {
        PaymentSetting::set(self::CURRENCY, strtoupper(trim($currency)) ?: 'SAR');
    }

    /** @return list<string> */
    public static function allowedCurrencies(): array
    {
        return ['SAR', 'USD', 'EUR', 'AED', 'KWD', 'BHD', 'OMR', 'QAR'];
    }

    private static function value(string $key, string $configKey): ?string
    {
        $stored = PaymentSetting::get($key);

        if ($stored !== null && $stored !== '') {
            return in_array($key, [self::SECRET_KEY, self::WEBHOOK_SECRET], true)
                ? self::decrypt($stored)
                : $stored;
        }

        $fromConfig = config($configKey);

        return filled($fromConfig) ? (string) $fromConfig : null;
    }

    private static function encrypt(string $value): string
    {
        return 'encrypted:'.Crypt::encryptString(trim($value));
    }

    private static function decrypt(string $value): ?string
    {
        if (! str_starts_with($value, 'encrypted:')) {
            // Backward compatibility: the next save migrates legacy plaintext values.
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, 10));
        } catch (DecryptException) {
            return null;
        }
    }
}
