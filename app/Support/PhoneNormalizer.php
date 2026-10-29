<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function toE164(string $phone, ?string $dialOrIso = null): string
    {
        $trimmed = trim($phone);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($trimmed, '+') || str_starts_with($digits, '00')) {
            if (str_starts_with($digits, '00')) {
                $digits = substr($digits, 2);
            }

            return '+'.$digits;
        }

        $dial = CountryDialCodes::dialDigits($dialOrIso) ?? '966';

        if (str_starts_with($digits, $dial)) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        return '+'.$dial.$digits;
    }
}
