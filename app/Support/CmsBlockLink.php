<?php

namespace App\Support;

class CmsBlockLink
{
    /** @param  array<string, mixed>  $link */
    public static function href(array $link, string $locale): string
    {
        $type = $link['link_type'] ?? 'route';
        $target = trim((string) ($link['link'] ?? ''));

        if ($target === '') {
            return '#';
        }

        if ($type === 'url') {
            return $target;
        }

        try {
            return route($target, ['locale' => $locale]);
        } catch (\Throwable) {
            return '#';
        }
    }

    public static function phoneDisplay(string $digits): string
    {
        $digits = preg_replace('/\D+/', '', $digits) ?? '';

        if ($digits === '') {
            return '';
        }

        return str_starts_with($digits, '966') ? '+'.$digits : '+966'.ltrim($digits, '0');
    }
}
