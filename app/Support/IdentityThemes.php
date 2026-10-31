<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Str;

class IdentityThemes
{
    public const KEY_ACTIVE = 'identity_theme_active';

    public const KEY_CUSTOM_PACKS = 'identity_theme_custom_packs';

    public const KEY_PACK_OVERRIDES = 'identity_theme_pack_overrides';

    public const DEFAULT = 'aou-navy';

    /**
     * Built-in identity packs. University identities keep their own logos
     * and names; color palettes only change atmosphere.
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
                logo: 'assets/branding/aou-logo.png',
                branding: self::aouBranding(),
                campusAerial: 'assets/branding/aou-campus-aerial.jpg',
                campusEntrance: 'assets/branding/aou-campus-entrance.jpg',
            ),
            'kku-green' => self::pack(
                id: 'kku-green',
                nameAr: 'جامعة الملك خالد',
                nameEn: 'King Khalid University',
                taglineAr: 'هوية جامعة الملك خالد — الأخضر الغابي والرمادي',
                audienceAr: 'جامعة الملك خالد ومراكز التعلم المستمر',
                category: 'university',
                primary: '#006838',
                secondary: '#8a8a8a',
                dark: '#003d20',
                light: '#1f8a52',
                pageBg: '#f4f7f5',
                footerBg: '#0b0b0b',
                footerText: '#e8e8e8',
                headerHover: '#006838',
                headerBgFixed: '#0b0b0b',
                headerNavInner: '#ffffff',
                headerBorder: '#1f1f1f',
                headerToolbar: '#d4d4d4',
                logo: 'assets/branding/kku-logo.png',
                branding: self::kkuBranding(),
                campusAerial: 'assets/branding/kku-campus.jpg',
                campusEntrance: 'assets/branding/kku-campus.jpg',
            ),
            'taibah-green' => self::pack(
                id: 'taibah-green',
                nameAr: 'جامعة طيبة',
                nameEn: 'Taibah University',
                taglineAr: 'الكلية التطبيقية ومعهد البحوث والاستشارات — الأخضر السعودي ولون الشعار',
                audienceAr: 'الكلية التطبيقية ومعهد البحوث والاستشارات بجامعة طيبة',
                category: 'university',
                primary: '#074d31',
                secondary: '#161616',
                dark: '#000000',
                light: '#074d31',
                pageBg: '#ffffff',
                text: '#161616',
                footerBg: '#161616',
                footerText: '#ffffff',
                headerHover: '#074d31',
                headerBgFixed: '#ffffff',
                headerNavInner: '#161616',
                headerBorder: '#d2d6db',
                headerToolbar: '#384250',
                headerBg: '#ffffff',
                headerNavColor: '#161616',
                logo: 'assets/branding/taibah-logo.svg',
                branding: self::taibahBranding(),
                campusAerial: 'assets/branding/taibah-campus.jpg',
                campusEntrance: 'assets/branding/taibah-campus.jpg',
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

    /** @return list<string> */
    public static function brandingKeys(): array
    {
        return [
            'platform_org_ar',
            'platform_org_en',
            'platform_logo_primary',
            'platform_logo_footer',
            'platform_favicon',
            'logo_primary_visible',
            'logo_secondary_visible',
            'logo_footer_visible',
            'logo_vision_visible',
            'footer_logos_mode',
        ];
    }

    /** @param  array<string, mixed>  $pack */
    public static function hasBranding(array $pack): bool
    {
        return filled($pack['branding'] ?? null);
    }

    /** @return array<string, array<string, mixed>> */
    public static function divisions(): array
    {
        return array_filter(self::all(), fn (array $pack) => self::hasBranding($pack));
    }

    /** @return array<string, array<string, mixed>> */
    public static function palettes(): array
    {
        return array_filter(self::all(), fn (array $pack) => ! self::hasBranding($pack));
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

    public static function storedKey(): string
    {
        $key = PlatformSetting::get(self::KEY_ACTIVE, self::DEFAULT) ?: self::DEFAULT;

        return self::find($key) ? $key : self::DEFAULT;
    }

    public static function keyForHost(?string $host = null): ?string
    {
        $host = strtolower((string) ($host ?? request()->getHost()));
        $host = preg_replace('/^www\./', '', $host) ?: $host;

        $map = config('identity.hosts', []);
        $mapped = is_array($map) ? ($map[$host] ?? null) : null;

        if (is_string($mapped) && self::find($mapped)) {
            return $mapped;
        }

        return null;
    }

    public static function activeKey(): string
    {
        $fromHost = self::keyForHost();

        if ($fromHost) {
            return $fromHost;
        }

        $fromEnv = trim((string) env('PLATFORM_IDENTITY', ''));

        if ($fromEnv !== '' && self::find($fromEnv)) {
            return $fromEnv;
        }

        return self::storedKey();
    }

    /** @return array<string, mixed> */
    public static function active(): array
    {
        return self::find(self::activeKey()) ?? self::presets()[self::DEFAULT];
    }

    /** @return array<string, string> */
    public static function resolvedBranding(): array
    {
        if (! self::keyForHost() && trim((string) env('PLATFORM_IDENTITY', '')) === '') {
            return [];
        }

        $pack = self::active();

        return is_array($pack['branding'] ?? null) ? $pack['branding'] : [];
    }

    public static function campusPath(string $which = 'aerial'): ?string
    {
        $pack = self::active();
        $key = $which === 'entrance' ? 'entrance' : 'aerial';
        $path = $pack['campus'][$key] ?? null;

        return filled($path) ? (string) $path : null;
    }

    public static function isActive(string $key): bool
    {
        return self::activeKey() === $key;
    }

    /**
     * Apply an identity pack. University identities restore their own logos
     * and names; other packs change colors only. The previous identity is
     * snapshotted so it can be reactivated later.
     */
    public static function apply(string $key): void
    {
        $pack = self::find($key);

        if (! $pack) {
            throw new \InvalidArgumentException('قالب الهوية غير موجود: '.$key);
        }

        self::snapshotActive();

        $effective = self::effectivePack($key);

        foreach ($effective['colors'] as $settingKey => $value) {
            $definition = ThemeSettings::definitions()[$settingKey] ?? null;

            if (! $definition) {
                continue;
            }

            PlatformSetting::set($settingKey, $value, 'theme', $definition['label_ar']);
        }

        if (self::hasBranding($effective)) {
            self::applyBranding($effective['branding'] ?? []);
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
        $pack = self::effectivePack(self::activeKey());

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
        $logo = PlatformSetting::get(LogoSettings::KEY_PRIMARY) ?: LogoSettings::defaultPath(LogoSettings::KEY_PRIMARY);

        $packs[$id] = [
            'id' => $id,
            'name_ar' => $nameAr,
            'name_en' => trim((string) $nameEn) !== '' ? trim((string) $nameEn) : $nameAr,
            'tagline_ar' => 'هوية محفوظة من الألوان والشعار الحاليين للمنصة',
            'audience_ar' => 'مخصص لهذه الجهة',
            'category' => 'custom',
            'builtin' => false,
            'created_at' => now()->toDateTimeString(),
            'logo' => $logo,
            'branding' => self::currentBranding(),
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

        $overrides = self::packOverrides();
        unset($overrides[$key]);
        self::storePackOverrides($overrides);

        if (self::storedKey() === $key || PlatformSetting::get(self::KEY_ACTIVE) === $key) {
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
            $pack['branding'] = is_array($pack['branding'] ?? null)
                ? self::normalizeBranding($pack['branding'])
                : [];
            $pack['logo'] = is_string($pack['logo'] ?? null) ? $pack['logo'] : ($pack['branding']['platform_logo_primary'] ?? null);
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

    /** @param  array<string, mixed>  $pack */
    public static function logoUrl(array $pack): ?string
    {
        $path = $pack['logo'] ?? $pack['branding']['platform_logo_primary'] ?? null;

        if (! filled($path)) {
            return null;
        }

        return LogoSettings::resolve((string) $path);
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

    protected static function snapshotActive(): void
    {
        $key = PlatformSetting::get(self::KEY_ACTIVE) ?: self::storedKey();
        $pack = self::find($key);

        if (! $pack) {
            return;
        }

        $snapshot = ['colors' => self::currentColors()];

        if (self::hasBranding($pack)) {
            $snapshot['branding'] = self::currentBranding();
        }

        $overrides = self::packOverrides();
        $overrides[$key] = $snapshot;
        self::storePackOverrides($overrides);
    }

    /** @return array<string, mixed> */
    protected static function effectivePack(string $key): array
    {
        $pack = self::find($key) ?? self::presets()[self::DEFAULT];
        $override = self::packOverrides()[$key] ?? null;

        if (! is_array($override)) {
            return $pack;
        }

        if (isset($override['colors']) && is_array($override['colors'])) {
            $pack['colors'] = array_merge($pack['colors'] ?? [], self::normalizeColorSet($override['colors']));
        }

        if (self::hasBranding($pack) && isset($override['branding']) && is_array($override['branding'])) {
            foreach (self::normalizeBranding($override['branding']) as $settingKey => $value) {
                if ($value === '') {
                    continue;
                }

                $pack['branding'][$settingKey] = $value;
            }

            $pack['logo'] = $pack['branding']['platform_logo_primary'] ?? $pack['logo'] ?? null;
        }

        return $pack;
    }

    /** @return array<string, array<string, mixed>> */
    protected static function packOverrides(): array
    {
        $raw = PlatformSetting::get(self::KEY_PACK_OVERRIDES);

        if (! filled($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @param  array<string, array<string, mixed>>  $overrides */
    protected static function storePackOverrides(array $overrides): void
    {
        PlatformSetting::set(
            self::KEY_PACK_OVERRIDES,
            json_encode($overrides, JSON_UNESCAPED_UNICODE),
            'theme',
            'لقطات هويات الجهات',
        );
    }

    /** @return array<string, string> */
    protected static function currentBranding(): array
    {
        $branding = [];

        foreach (self::brandingKeys() as $key) {
            $branding[$key] = (string) (PlatformSetting::get($key) ?? '');
        }

        return self::normalizeBranding($branding);
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    protected static function applyBranding(array $branding): void
    {
        $labels = [
            'platform_org_ar' => 'الجهة / الجامعة (عربي)',
            'platform_org_en' => 'الجهة / الجامعة (إنجليزي)',
            'platform_logo_primary' => 'الشعار الرئيسي',
            'platform_logo_footer' => 'شعار الفوتر',
            'platform_favicon' => 'أيقونة الموقع (Favicon)',
            'logo_primary_visible' => 'إظهار الشعار الرئيسي',
            'logo_secondary_visible' => 'إظهار الشعار الثانوي',
            'logo_footer_visible' => 'إظهار شعار الفوتر',
            'logo_vision_visible' => 'إظهار شعار الرؤية',
            'footer_logos_mode' => 'وضع شعارات الفوتر',
        ];

        foreach (self::normalizeBranding($branding) as $key => $value) {
            $group = str_starts_with($key, 'platform_org') ? 'general' : 'branding';
            PlatformSetting::set($key, $value, $group, $labels[$key] ?? $key);
        }
    }

    /**
     * @param  array<string, mixed>  $branding
     * @return array<string, string>
     */
    protected static function normalizeBranding(array $branding): array
    {
        $normalized = [];

        foreach (self::brandingKeys() as $key) {
            if (! array_key_exists($key, $branding)) {
                continue;
            }

            $normalized[$key] = trim((string) $branding[$key]);
        }

        return $normalized;
    }

    /** @return array<string, string> */
    protected static function aouBranding(): array
    {
        return [
            'platform_org_ar' => 'الجامعة العربية المفتوحة',
            'platform_org_en' => 'Arab Open University',
            'platform_logo_primary' => 'assets/branding/aou-logo.png',
            'platform_logo_footer' => 'assets/branding/aou-logo-footer.png',
            'platform_favicon' => 'assets/branding/aou-favicon.png',
            'map_query' => 'الجامعة العربية المفتوحة السعودية، حطين، الرياض',
            'logo_primary_visible' => '1',
            'logo_secondary_visible' => '0',
            'logo_footer_visible' => '1',
            'logo_vision_visible' => '0',
            'footer_logos_mode' => LogoSettings::FOOTER_LOGOS_FIRST,
        ];
    }

    /** @return array<string, string> */
    protected static function kkuBranding(): array
    {
        return [
            'platform_org_ar' => 'جامعة الملك خالد',
            'platform_org_en' => 'King Khalid University',
            'platform_logo_primary' => 'assets/branding/kku-logo.png',
            'platform_logo_footer' => 'assets/branding/kku-logo.png',
            'platform_favicon' => 'assets/branding/kku-favicon.png',
            'map_query' => 'جامعة الملك خالد، أبها',
            'logo_primary_visible' => '1',
            'logo_secondary_visible' => '0',
            'logo_footer_visible' => '1',
            'logo_vision_visible' => '0',
            'footer_logos_mode' => LogoSettings::FOOTER_LOGOS_FIRST,
        ];
    }

    /** @return array<string, string> */
    protected static function taibahBranding(): array
    {
        return [
            'platform_org_ar' => 'جامعة طيبة',
            'platform_org_en' => 'Taibah University',
            'platform_name_ar' => 'الكلية التطبيقية',
            'platform_name_en' => 'Applied College',
            'platform_logo_primary' => 'assets/branding/taibah-logo.svg',
            'platform_logo_footer' => 'assets/branding/taibah-logo-white.svg',
            'platform_favicon' => 'assets/branding/taibah-favicon.png',
            'map_query' => 'جامعة طيبة، المدينة المنورة',
            'logo_primary_visible' => '1',
            'logo_secondary_visible' => '0',
            'logo_footer_visible' => '1',
            'logo_vision_visible' => '0',
            'footer_logos_mode' => LogoSettings::FOOTER_LOGOS_FIRST,
        ];
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
        ?string $headerBgFixed = null,
        ?string $headerNavInner = null,
        ?string $headerBorder = null,
        ?string $headerToolbar = null,
        ?string $headerBg = null,
        ?string $headerNavColor = null,
        ?string $logo = null,
        array $branding = [],
        ?string $campusAerial = null,
        ?string $campusEntrance = null,
    ): array {
        return [
            'id' => $id,
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'tagline_ar' => $taglineAr,
            'audience_ar' => $audienceAr,
            'category' => $category,
            'builtin' => true,
            'logo' => $logo,
            'branding' => $branding,
            'campus' => array_filter([
                'aerial' => $campusAerial,
                'entrance' => $campusEntrance,
            ]),
            'colors' => [
                'theme_color_primary' => $primary,
                'theme_color_secondary' => $secondary,
                'theme_color_primary_dark' => $dark,
                'theme_color_primary_light' => $light,
                'theme_color_page_bg' => $pageBg,
                'theme_color_text' => $text,
                'theme_color_footer_bg' => $footerBg,
                'theme_color_footer_text' => $footerText,
                'theme_header_bg' => $headerBg ?: 'transparent',
                'theme_header_bg_fixed' => $headerBgFixed ?: '#ffffff',
                'theme_header_nav_color' => $headerNavColor ?: '#ffffff',
                'theme_header_nav_color_inner' => $headerNavInner ?: '#494949',
                'theme_header_nav_hover' => $headerHover ?: $primary,
                'theme_header_border' => $headerBorder ?: '#eceff1',
                'theme_header_toolbar_color' => $headerToolbar ?: '#515151',
            ],
        ];
    }
}
