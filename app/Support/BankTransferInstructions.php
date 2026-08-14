<?php

namespace App\Support;

use App\Models\PaymentSetting;

class BankTransferInstructions
{
    public static function html(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $key = $locale === 'en' ? 'bank_transfer_instructions_en' : 'bank_transfer_instructions_ar';

        return self::enhance(PaymentSetting::get($key, '') ?? '');
    }

    public static function enhance(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $enhanced = preg_replace_callback(
            '/<li[^>]*>(.*?<strong>[^<]*(?:IBAN|رقم\s*IBAN)[^<]*:<\/strong>\s*)([^<]+)<\/li>/iu',
            function (array $matches): string {
                $iban = trim(html_entity_decode(strip_tags($matches[2])));
                $button = sprintf(
                    '<button type="button" class="portal-bank-transfer__copy-btn" data-copy-iban="%s"><i class="fa-solid fa-copy"></i> نسخ</button>',
                    e($iban)
                );

                return '<li class="portal-bank-transfer__iban-row">'.$matches[1].'<span class="portal-bank-transfer__iban-value" dir="ltr">'.e($iban).'</span>'.$button.'</li>';
            },
            $html
        );

        return $enhanced ?? $html;
    }
}
