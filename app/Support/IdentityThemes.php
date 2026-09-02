<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Str;

class IdentityThemes
{
    public const KEY_ACTIVE = 'identity_theme_active';

    public const KEY_CUSTOM_PACKS = 'identity_theme_custom_packs';

    public const DEFAULT = 'aou-navy';

    /**
     * Built-in identity packs. Each pack only changes visual identity
     * (colors / home atmosphere). Page structure and CMS blocks stay intact.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function presets(): array
    {
        return [
            'aou-navy' => self::pack(
                id: 'aou-navy',
                nameAr: 'الجامعة العربية المفتوحة',
                nameEn: 'Arab Open University',
                taglineAr: 'هوية الجامعة العربية المفتوحة — الكحلي والعاجي',
                audienceAr: 'الجامعة العربية المفتوحة ومراكز التعلم المستمر',
                category: 'university',
                primary: '#002d58',
                secondary: '#c5a572',
                dark: '#001a33',
                light: '#1a4d7a',
                pageBg: '#f4f6fa',
                footerBg: '#0b0b0b',
                footerText: '#e8e0d8',
                headerHover: '#c5a572',
            ),
            'muqrin-green' => self::pack(
                id: 'muqrin-green',
                nameAr: 'الأخضر الوطني',
                nameEn: 'National Green',
                taglineAr: 'هوية مراكز التعلم الجامعي — الأخضر والذهبي',
                audienceAr: 'جامعات ومراكز التعلم المستمر',
                category: 'university',
                primary: '#1b8354',
                secondary: '#b8943f',
                dark: '#135f3d',
                light: '#2d9a6a',
                pageBg: '#f7faf8',
            ),
            'navy-gold' => self::pack(
                id: 'navy-gold',
                nameAr: 'الكحلي والذهبي',
                nameEn: 'Navy & Gold',
                taglineAr: 'هوية أكاديمية كلاسيكية بوقار جامعي',
                audienceAr: 'جامعات وكليات تقليدية',
                category: 'university',
                primary: '#0c2e5a',
                secondary: '#c9a227',
                dark: '#071e3d',
                light: '#1a4a7a',
                pageBg: '#f5f7fb',
            ),
            'burgundy-gold' => self::pack(
                id: 'burgundy-gold',
                nameAr: 'العنابي والذهبي',
                nameEn: 'Burgundy & Gold',
                taglineAr: 'هوية تراثية فاخرة للكليات والأكاديميات',
                audienceAr: 'كليات وأكاديميات متخصصة',
                category: 'university',
                primary: '#7a1f3d',
                secondary: '#c4a35a',
                dark: '#54152a',
                light: '#9b3a58',
                pageBg: '#fbf7f6',
            ),
            'oasis-teal' => self::pack(
                id: 'oasis-teal',
                nameAr: 'تركواز الواحة',
                nameEn: 'Oasis Teal',
                taglineAr: 'هوية عصرية لمعاهد التدريب والتأهيل',
                audienceAr: 'معاهد تدريب ومراكز مهارات',
                category: 'institute',
                primary: '#0e7c7b',
                secondary: '#d4a017',
                dark: '#0a5554',
                light: '#1aa3a1',
                pageBg: '#f4fbfb',
            ),
            'sand-olive' => self::pack(
                id: 'sand-olive',
                nameAr: 'الرملي الزيتوني',
                nameEn: 'Sand & Olive',
                taglineAr: 'هوية هادئة مستوحاة من البيئة المحلية',
                audienceAr: 'جهات حكومية ومبادرات وطنية',
                category: 'government',
                primary: '#5f6b32',
                secondary: '#c4a574',
                dark: '#3f4721',
                light: '#7d8a48',
                pageBg: '#faf7f0',
            ),
            'royal-blue' => self::pack(
                id: 'royal-blue',
                nameAr: 'الأزرق الملكي',
                nameEn: 'Royal Blue',
                taglineAr: 'هوية مؤسسية رسمية للشركات والهيئات',
                audienceAr: 'شركات وهيئات ومؤسسات',
                category: 'corporate',
                primary: '#1e4d8c',
                secondary: '#d4af37',
                dark: '#12305c',
                light: '#3a6bb5',
                pageBg: '#f4f7fc',
            ),
            'charcoal-amber' => self::pack(
                id: 'charcoal-amber',
                nameAr: 'الفحمي والعنبر',
                nameEn: 'Charcoal & Amber',
                taglineAr: 'هوية حديثة للقطاع الخاص والمنصات الرقمية',
                audienceAr: 'قطاع خاص ومنصات تعليم تقني',
                category: 'corporate',
                primary: '#1f2937',
                secondary: '#d97706',
                dark: '#111827',
                light: '#374151',
                pageBg: '#f8fafc',
                headerHover: '#d97706',
            ),
            'crimson-ivory' => self::pack(
                id: 'crimson-ivory',
                nameAr: 'القرمزي والعاجي',
                nameEn: 'Crimson & Ivory',
                taglineAr: 'هوية دافئة للكليات الصحية والبرامج المهنية',
                audienceAr: 'كليات صحية وبرامج مهنية',
                category: 'institute',
                primary: '#9b1c2c',
                secondary: '#c9a66b',
                dark: '#6b121e',
                light: '#c0392b',
                pageBg: '#fbf8f5',
            ),
        ];
    }

    /** @return array<string, string> */
    public static function categories(): array
    {
        return [
            'all' => 'الكل',
            'university' => 'جامعي',
            'institute' => 'معاهد وتدريب',
            'corporate' => 'شركات ومؤسسات',
            'government' => 'جهات حكومية',
            'custom' => 'محفوظ لديّ',
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return array_merge(self::presets(), self::customPacks());
    }

    /** @return array<string, mixed>|null */
    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function activeKey(): string
    {
        $key = PlatformSetting::get(self::KEY_ACTIVE, self::DEFAULT) ?: self::DEFAULT;

        return self::find($key) ? $key : self::DEFAULT;
    }

    /** @return array<string, mixed> */
    public static function active(): array
    {
        return self::find(self::activeKey()) ?? self::presets()[self::DEFAULT];
    }

    public static function isActive(string $key): bool
    {
        return self::activeKey() === $key;
    }

    /**
     * Apply an identity pack: colors only. Logos, names, and CMS blocks are untouched.
     */
    public static function apply(string $key): void
    {
        $pack = self::find($key);

        if (! $pack) {
            throw new \InvalidArgumentException('قالب الهوية غير موجود: '.$key);
        }

        foreach ($pack['colors'] as $settingKey => $value) {
            $definition = ThemeSettings::definitions()[$settingKey] ?? null;

            if (! $definition) {
                continue;
            }

            PlatformSetting::set($settingKey, $value, 'theme', $definition['label_ar']);
        }

        PlatformSetting::set(self::KEY_ACTIVE, $key, 'theme', 'قالب الهوية النشط');
    }

    /** @return array<string, string> */
    public static function currentColors(): array
    {
        return ThemeSettings::formDefaults();
    }

    public static function isCustomized(): bool
    {
        $pack = self::active();

        return ! self::colorsMatch($pack['colors'] ?? [], self::currentColors());
    }

    /**
     * @param  array<string, string>  $expected
     * @param  array<string, string>  $actual
     */
    public static function colorsMatch(array $expected, array $actual): bool
    {
        foreach (array_keys(ThemeSettings::definitions()) as $key) {
            $left = strtolower(trim((string) ($expected[$key] ?? '')));
            $right = strtolower(trim((string) ($actual[$key] ?? '')));

            if ($left !== $right) {
                return false;
            }
        }

        return true;
    }

    public static function saveCustom(string $nameAr, ?string $nameEn = null, ?array $colors = null): string
    {
        $nameAr = trim($nameAr);

        if ($nameAr === '') {
            throw new \InvalidArgumentException('أدخل اسماً للهوية المخصصة.');
        }

        $id = 'custom-'.Str::lower(Str::ulid());
        $colors ??= self::currentColors();
        $packs = self::customPacks();

        $packs[$id] = [
            'id' => $id,
            'name_ar' => $nameAr,
            'name_en' => trim((string) $nameEn) !== '' ? trim((string) $nameEn) : $nameAr,
            'tagline_ar' => 'هوية محفوظة من الألوان الحالية للمنصة',
            'audience_ar' => 'مخصص لهذه الجهة',
            'category' => 'custom',
            'builtin' => false,
            'created_at' => now()->toDateTimeString(),
            'colors' => self::normalizeColorSet($colors),
        ];

        self::storeCustomPacks($packs);

        return $id;
    }

    public static function deleteCustom(string $key): void
    {
        if (! str_starts_with($key, 'custom-')) {
            return;
        }

        $packs = self::customPacks();
        unset($packs[$key]);
        self::storeCustomPacks($packs);

        if (self::activeKey() === $key || PlatformSetting::get(self::KEY_ACTIVE) === $key) {
            PlatformSetting::set(self::KEY_ACTIVE, self::DEFAULT, 'theme', 'قالب الهوية النشط');
        }
    }

    /** @return array<string, array<string, mixed>> */
    public static function customPacks(): array
    {
        $raw = PlatformSetting::get(self::KEY_CUSTOM_PACKS);

        if (! filled($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        $packs = [];

        foreach ($decoded as $key => $pack) {
            if (! is_array($pack) || ! is_string($key)) {
                continue;
            }

            $pack['id'] = $key;
            $pack['builtin'] = false;
            $pack['category'] = 'custom';
            $pack['colors'] = self::normalizeColorSet($pack['colors'] ?? []);
            $packs[$key] = $pack;
        }

        return $packs;
    }

    /**
     * Five swatches for gallery cards: dark, primary, light, gold, page.
     *
     * @param  array<string, mixed>  $pack
     * @return list<string>
     */
    public static function swatches(array $pack): array
    {
        $colors = $pack['colors'] ?? [];

        return [
            $colors['theme_color_primary_dark'] ?? '#135f3d',
            $colors['theme_color_primary'] ?? '#1b8354',
            $colors['theme_color_primary_light'] ?? '#2d9a6a',
            $colors['theme_color_secondary'] ?? '#b8943f',
            $colors['theme_color_page_bg'] ?? '#f7faf8',
        ];
    }

    /**
     * @param  array<string, mixed>  $colors
     * @return array<string, string>
     */
    protected static function normalizeColorSet(array $colors): array
    {
        $normalized = [];

        foreach (ThemeSettings::definitions() as $key => $definition) {
            $value = trim((string) ($colors[$key] ?? $definition['default']));
            $normalized[$key] = $value !== '' ? $value : $definition['default'];
        }

        return $normalized;
    }

    /** @param  array<string, array<string, mixed>>  $packs */
    protected static function storeCustomPacks(array $packs): void
    {
        PlatformSetting::set(
            self::KEY_CUSTOM_PACKS,
            json_encode($packs, JSON_UNESCAPED_UNICODE),
            'theme',
            'قوالب الهوية المخصصة',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected static function pack(
        string $id,
        string $nameAr,
        string $nameEn,
        string $taglineAr,
        string $audienceAr,
        string $category,
        string $primary,
        string $secondary,
        string $dark,
        string $light,
        string $pageBg,
        string $text = '#1a1a1a',
        string $footerBg = '#ffffff',
        string $footerText = '#414040',
        ?string $headerHover = null,
    ): array {
        return [
            'id' => $id,
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'tagline_ar' => $taglineAr,
            'audience_ar' => $audienceAr,
            'category' => $category,
            'builtin' => true,
            'colors' => [
                'theme_color_primary' => $primary,
                'theme_color_secondary' => $secondary,
                'theme_color_primary_dark' => $dark,
                'theme_color_primary_light' => $light,
                'theme_color_page_bg' => $pageBg,
                'theme_color_text' => $text,
                'theme_color_footer_bg' => $footerBg,
                'theme_color_footer_text' => $footerText,
                'theme_header_bg' => 'transparent',
                'theme_header_bg_fixed' => '#ffffff',
                'theme_header_nav_color' => '#ffffff',
                'theme_header_nav_color_inner' => '#494949',
                'theme_header_nav_hover' => $headerHover ?: $primary,
                'theme_header_border' => '#eceff1',
                'theme_header_toolbar_color' => '#515151',
            ],
        ];
    }
}
