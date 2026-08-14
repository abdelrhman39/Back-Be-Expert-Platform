<?php

namespace App\Support;

use App\Models\PlatformSetting;

class LogoSettings
{
    public const KEY_PRIMARY = 'platform_logo_primary';

    public const KEY_SECONDARY = 'platform_logo_secondary';

    public const KEY_FOOTER = 'platform_logo_footer';

    public const KEY_VISION = 'platform_logo_vision';

    public const KEY_FAVICON = 'platform_favicon';

    public const KEY_PRIMARY_VISIBLE = 'logo_primary_visible';

    public const KEY_SECONDARY_VISIBLE = 'logo_secondary_visible';

    public const KEY_FOOTER_VISIBLE = 'logo_footer_visible';

    public const KEY_VISION_VISIBLE = 'logo_vision_visible';

    /** first = شعار الفوتر فقط · both = شعار الفوتر + شعار الرؤية */
    public const KEY_FOOTER_LOGOS_MODE = 'footer_logos_mode';

    public const FOOTER_LOGOS_FIRST = 'first';

    public const FOOTER_LOGOS_BOTH = 'both';

    /** @return array<string, string> */
    public static function defaults(): array
    {
        return [
            self::KEY_PRIMARY => 'assets/ba5c2cc1-5c62-4b77-8607-bead454d224e.png',
            self::KEY_SECONDARY => 'assets/d8e8b170-8627-42bc-86e9-f3c2e5c73222.png',
            self::KEY_FOOTER => 'assets/ba5c2cc1-5c62-4b77-8607-bead454d224e(1).png',
            self::KEY_VISION => 'assets/visionLogo.png',
            self::KEY_FAVICON => 'assets/vendor/images/site-favicon.png',
        ];
    }

    public static function defaultPath(string $key): string
    {
        return self::defaults()[$key] ?? self::defaults()[self::KEY_PRIMARY];
    }

    public static function storedPath(string $key): ?string
    {
        $value = PlatformSetting::get($key);

        return filled($value) ? $value : null;
    }

    public static function primaryUrl(): string
    {
        return self::url(self::KEY_PRIMARY);
    }

    public static function secondaryUrl(): string
    {
        return self::url(self::KEY_SECONDARY);
    }

    public static function footerUrl(): string
    {
        return self::url(self::KEY_FOOTER);
    }

    public static function visionUrl(): string
    {
        return self::url(self::KEY_VISION);
    }

    public static function faviconUrl(): string
    {
        return self::url(self::KEY_FAVICON);
    }

    /**
     * Display constraints and upload optimization targets per logo slot.
     *
     * @return array{max_width: int, max_height: int, label_ar: string, square?: bool}
     */
    public static function slot(string $key): array
    {
        return match ($key) {
            self::KEY_PRIMARY => [
                'max_width' => 240,
                'max_height' => 75,
                'label_ar' => 'الشعار الرئيسي في الهيدر',
            ],
            self::KEY_SECONDARY => [
                'max_width' => 160,
                'max_height' => 126,
                'label_ar' => 'الشعار الثانوي بجانب الرئيسي',
            ],
            self::KEY_FOOTER => [
                'max_width' => 280,
                'max_height' => 100,
                'label_ar' => 'شعار الفوتر',
            ],
            self::KEY_VISION => [
                'max_width' => 200,
                'max_height' => 100,
                'label_ar' => 'شعار الرؤية في الفوتر',
            ],
            self::KEY_FAVICON => [
                'max_width' => 64,
                'max_height' => 64,
                'label_ar' => 'أيقونة الموقع',
                'square' => true,
            ],
            default => [
                'max_width' => 240,
                'max_height' => 75,
                'label_ar' => 'شعار المنصة',
            ],
        };
    }

    public static function cssClass(string $key): string
    {
        return match ($key) {
            self::KEY_PRIMARY => 'platform-logo platform-logo--primary',
            self::KEY_SECONDARY => 'platform-logo platform-logo--secondary',
            self::KEY_FOOTER => 'platform-logo platform-logo--footer',
            self::KEY_VISION => 'platform-logo platform-logo--vision',
            self::KEY_FAVICON => 'platform-logo platform-logo--favicon',
            default => 'platform-logo',
        };
    }

    public static function slotHint(string $key): string
    {
        $slot = self::slot($key);

        if ($slot['square'] ?? false) {
            return 'الحجم الموصى به: '.$slot['max_width'].'×'.$slot['max_height'].' بكسل (مربع). تُحسَّن الصورة تلقائياً عند الرفع.';
        }

        return 'الحجم الموصى به: حتى '.$slot['max_width'].'×'.$slot['max_height'].' بكسل. تُحسَّن الصورة تلقائياً عند الرفع مع الحفاظ على النسبة.';
    }

    public static function visibilityKey(string $logoKey): ?string
    {
        return match ($logoKey) {
            self::KEY_PRIMARY => self::KEY_PRIMARY_VISIBLE,
            self::KEY_SECONDARY => self::KEY_SECONDARY_VISIBLE,
            self::KEY_FOOTER => self::KEY_FOOTER_VISIBLE,
            self::KEY_VISION => self::KEY_VISION_VISIBLE,
            default => null,
        };
    }

    public static function isVisible(string $logoKey): bool
    {
        $visibilityKey = self::visibilityKey($logoKey);

        if (! $visibilityKey) {
            return true;
        }

        return PlatformSetting::get(
            $visibilityKey,
            self::defaultVisible($logoKey) ? '1' : '0'
        ) !== '0';
    }

    public static function footerLogosMode(): string
    {
        $mode = PlatformSetting::get(self::KEY_FOOTER_LOGOS_MODE, self::FOOTER_LOGOS_FIRST);

        return $mode === self::FOOTER_LOGOS_BOTH
            ? self::FOOTER_LOGOS_BOTH
            : self::FOOTER_LOGOS_FIRST;
    }

    public static function showFooterPrimaryLogo(): bool
    {
        return self::isVisible(self::KEY_FOOTER);
    }

    public static function showFooterSecondaryLogo(): bool
    {
        return self::footerLogosMode() === self::FOOTER_LOGOS_BOTH
            && self::isVisible(self::KEY_VISION);
    }

    public static function defaultVisible(string $logoKey): bool
    {
        return $logoKey !== self::KEY_VISION;
    }

    public static function url(string $key): string
    {
        return self::resolve(self::storedPath($key), $key);
    }

    public static function resolve(?string $path, ?string $key = null): string
    {
        if (filled($path)) {
            return self::resolvePath($path);
        }

        $default = $key ? self::defaultPath($key) : self::defaultPath(self::KEY_PRIMARY);

        return self::resolvePath($default);
    }

    protected static function resolvePath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return static_asset(ltrim($path, './'));
    }
}
