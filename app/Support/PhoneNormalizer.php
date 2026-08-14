<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function toE164(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '966')) {
            return '+'. $digits;
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return '+966'. $digits;
    }
}
