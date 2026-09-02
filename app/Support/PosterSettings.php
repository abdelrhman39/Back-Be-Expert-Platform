<?php

namespace App\Support;

use App\Models\PlatformSetting;

class PosterSettings
{
    /** @var list<string> Legacy placeholder filenames — never render these; use the university logo instead. */
    private const LEGACY_POSTER_BASENAMES = [
        '1861641489031145.png',
        'site-favicon.png',
    ];

    public static function defaultAssetPath(): string
    {
        return LogoSettings::storedPath(LogoSettings::KEY_PRIMARY)
            ?? LogoSettings::defaultPath(LogoSettings::KEY_PRIMARY);
    }

    public static function isLegacyPoster(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        $normalized = basename(str_replace('\\', '/', trim($path)));

        return in_array($normalized, self::LEGACY_POSTER_BASENAMES, true);
    }

    public static function storedPath(): ?string
    {
        $value = PlatformSetting::get('default_poster_image');

        if (filled($value) && self::isLegacyPoster($value)) {
            return null;
        }

        return filled($value) ? $value : null;
    }

    public static function url(): string
    {
        return self::resolve(null);
    }

    public static function resolve(?string $path): string
    {
        if (filled($path) && ! self::isLegacyPoster($path)) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, '/storage/')) {
                return asset(ltrim($path, '/'));
            }

            return static_asset(ltrim($path, './'));
        }

        $default = self::storedPath() ?? self::defaultAssetPath();

        if (str_starts_with($default, 'http://') || str_starts_with($default, 'https://')) {
            return $default;
        }

        if (str_starts_with($default, '/storage/')) {
            return asset(ltrim($default, '/'));
        }

        return static_asset(ltrim($default, './'));
    }
}
