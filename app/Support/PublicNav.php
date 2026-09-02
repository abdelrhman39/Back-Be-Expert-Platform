<?php

namespace App\Support;

class PublicNav
{
    public static function isActive(?string $url, array $children = []): bool
    {
        if (self::urlMatches($url)) {
            return true;
        }

        foreach ($children as $child) {
            if (self::isActive($child['url'] ?? null, $child['children'] ?? [])) {
                return true;
            }
        }

        return false;
    }

    public static function isExact(?string $url): bool
    {
        return self::urlMatches($url, exact: true);
    }

    public static function urlMatches(?string $url, bool $exact = false): bool
    {
        if (! filled($url) || in_array($url, ['#', 'javascript:void(0)'], true)) {
            return false;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return false;
        }

        if ($exact || in_array($path, ['ar', 'en'], true)) {
            return request()->is($path);
        }

        return request()->is($path) || request()->is($path.'/*');
    }
}
