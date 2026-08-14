<?php

namespace App\Support;

use App\Models\PaymentSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class BnplSettings
{
    public const TABBY_PUBLIC_KEY = 'tabby_public_key';

    public const TABBY_SECRET_KEY = 'tabby_secret_key';

    public const TABBY_MERCHANT_CODE = 'tabby_merchant_code';

    public const TAMARA_API_TOKEN = 'tamara_api_token';

    public const TAMARA_NOTIFICATION_TOKEN = 'tamara_notification_token';

    public static function tabbyPublicKey(): ?string
    {
        return self::plain(self::TABBY_PUBLIC_KEY);
    }

    public static function tabbySecretKey(): ?string
    {
        return self::secret(self::TABBY_SECRET_KEY);
    }

    public static function tabbyMerchantCode(): ?string
    {
        return self::plain(self::TABBY_MERCHANT_CODE);
    }

    public static function tamaraApiToken(): ?string
    {
        return self::secret(self::TAMARA_API_TOKEN);
    }

    public static function tamaraNotificationToken(): ?string
    {
        return self::secret(self::TAMARA_NOTIFICATION_TOKEN);
    }

    public static function tabbyReady(): bool
    {
        return filled(self::tabbySecretKey()) && filled(self::tabbyMerchantCode());
    }

    public static function tamaraReady(): bool
    {
        return filled(self::tamaraApiToken());
    }

    public static function setTabbyPublicKey(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::TABBY_PUBLIC_KEY, trim($value));
        }
    }

    public static function setTabbySecretKey(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::TABBY_SECRET_KEY, self::encrypt($value));
        }
    }

    public static function setTabbyMerchantCode(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::TABBY_MERCHANT_CODE, trim($value));
        }
    }

    public static function setTamaraApiToken(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::TAMARA_API_TOKEN, self::encrypt($value));
        }
    }

    public static function setTamaraNotificationToken(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PaymentSetting::set(self::TAMARA_NOTIFICATION_TOKEN, self::encrypt($value));
        }
    }

    public static function hasStoredTabbySecret(): bool
    {
        return filled(PaymentSetting::get(self::TABBY_SECRET_KEY));
    }

    public static function hasStoredTamaraToken(): bool
    {
        return filled(PaymentSetting::get(self::TAMARA_API_TOKEN));
    }

    protected static function plain(string $key): ?string
    {
        $value = PaymentSetting::get($key);

        return filled($value) ? (string) $value : null;
    }

    protected static function secret(string $key): ?string
    {
        $stored = PaymentSetting::get($key);

        if (! filled($stored)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $stored);
        } catch (DecryptException) {
            return (string) $stored;
        }
    }

    protected static function encrypt(string $value): string
    {
        return Crypt::encryptString($value);
    }
}
