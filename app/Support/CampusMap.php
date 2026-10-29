<?php

namespace App\Support;

class CampusMap
{
    public static function embedUrl(): string
    {
        $query = IdentityThemes::resolvedBranding()['map_query']
            ?? 'الجامعة العربية المفتوحة السعودية، حطين، الرياض';

        return 'https://www.google.com/maps?q='.rawurlencode((string) $query).'&hl=ar&z=16&output=embed';
    }

    public static function isLegacyHailEmbed(?string $url): bool
    {
        if (! filled($url)) {
            return true;
        }

        return str_contains($url, '41.699758')
            || str_contains($url, '27.564384')
            || str_contains($url, '157645de57c7ca57')
            || str_contains($url, 'embed.html');
    }
}
