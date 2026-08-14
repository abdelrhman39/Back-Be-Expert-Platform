<?php

namespace App\Support;

use App\Models\PlatformSetting;

class ThemeSettings
{
    /** @return array<string, array{label_ar: string, default: string, group: string, css_vars?: array<int, string>}> */
    public static function definitions(): array
    {
        return [
            // ——— ألوان المنصة العامة ———
            'theme_color_primary' => [
                'label_ar' => 'اللون الأساسي',
                'default' => '#1b8354',
                'group' => 'platform',
                'css_vars' => ['--primary', '--sa-green', '--bs-primary', '--dash-teal', '--portal-teal'],
            ],
            'theme_color_secondary' => [
                'label_ar' => 'اللون الثانوي (ذهبي)',
                'default' => '#b8943f',
                'group' => 'platform',
                'css_vars' => ['--secondary', '--sa-gold', '--dash-pink'],
            ],
            'theme_color_primary_dark' => [
                'label_ar' => 'اللون الأساسي الداكن',
                'default' => '#135f3d',
                'group' => 'platform',
                'css_vars' => ['--sa-green-dark', '--sa-flag-green', '--dash-navy', '--portal-footer-end'],
            ],
            'theme_color_primary_light' => [
                'label_ar' => 'اللون الأساسي الفاتح',
                'default' => '#2d9a6a',
                'group' => 'platform',
                'css_vars' => ['--sa-green-light', '--dash-blue', '--portal-teal-light'],
            ],
            'theme_color_page_bg' => [
                'label_ar' => 'خلفية الصفحات',
                'default' => '#f7faf8',
                'group' => 'platform',
                'css_vars' => ['--sa-mist', '--surface-page', '--dash-bg'],
            ],
            'theme_color_text' => [
                'label_ar' => 'لون النص الأساسي',
                'default' => '#1a1a1a',
                'group' => 'platform',
                'css_vars' => ['--sa-ink', '--portal-navy'],
            ],
            'theme_color_footer_bg' => [
                'label_ar' => 'خلفية الفوتر',
                'default' => '#ffffff',
                'group' => 'platform',
                'css_vars' => ['--platform-footer-bg'],
            ],
            'theme_color_footer_text' => [
                'label_ar' => 'لون نص الفوتر',
                'default' => '#414040',
                'group' => 'platform',
                'css_vars' => ['--platform-footer-text'],
            ],

            // ——— الهيدر والنافبار ———
            'theme_header_bg' => [
                'label_ar' => 'خلفية الهيدر (الصفحة الرئيسية)',
                'default' => 'transparent',
                'group' => 'header',
                'css_vars' => ['--platform-header-bg'],
            ],
            'theme_header_bg_fixed' => [
                'label_ar' => 'خلفية الهيدر (عند التمرير / الصفحات الداخلية)',
                'default' => '#ffffff',
                'group' => 'header',
                'css_vars' => ['--platform-header-bg-fixed'],
            ],
            'theme_header_nav_color' => [
                'label_ar' => 'لون روابط النافبار',
                'default' => '#ffffff',
                'group' => 'header',
                'css_vars' => ['--platform-header-nav-color'],
            ],
            'theme_header_nav_color_inner' => [
                'label_ar' => 'لون روابط النافبار (الصفحات الداخلية)',
                'default' => '#494949',
                'group' => 'header',
                'css_vars' => ['--platform-header-nav-color-inner'],
            ],
            'theme_header_nav_hover' => [
                'label_ar' => 'لون الروابط عند التمرير',
                'default' => '#1b8354',
                'group' => 'header',
                'css_vars' => ['--platform-header-nav-hover'],
            ],
            'theme_header_border' => [
                'label_ar' => 'لون حدود الهيدر',
                'default' => '#eceff1',
                'group' => 'header',
                'css_vars' => ['--platform-header-border'],
            ],
            'theme_header_toolbar_color' => [
                'label_ar' => 'لون أيقونات شريط الأدوات (السلة، اللغة، الحساب)',
                'default' => '#515151',
                'group' => 'header',
                'css_vars' => ['--platform-header-toolbar-color'],
            ],
        ];
    }

    /** @return array<string, string> */
    public static function formDefaults(): array
    {
        $values = [];

        foreach (self::definitions() as $key => $definition) {
            $stored = PlatformSetting::get($key);
            $values[$key] = filled($stored) ? $stored : $definition['default'];
        }

        return $values;
    }

    public static function effective(string $key): string
    {
        $definition = self::definitions()[$key] ?? null;

        if (! $definition) {
            return '';
        }

        $stored = PlatformSetting::get($key);

        return filled($stored) ? $stored : $definition['default'];
    }

    /** @return array<string, string> */
    public static function cssVariables(): array
    {
        $variables = [];

        foreach (self::definitions() as $key => $definition) {
            $value = self::effective($key);

            if ($value === '') {
                continue;
            }

            foreach ($definition['css_vars'] ?? [] as $cssVar) {
                $variables[$cssVar] = $value;
            }
        }

        if (isset($variables['--primary']) && ! isset($variables['--bs-primary-rgb'])) {
            $variables['--bs-primary-rgb'] = self::hexToRgbTriplet($variables['--primary']);
        }

        return $variables;
    }

    /** @return array<string, array{label_ar: string, default: string}> */
    public static function group(string $group): array
    {
        return array_filter(
            self::definitions(),
            fn (array $definition) => $definition['group'] === $group,
        );
    }

    public static function normalizeColor(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $value = trim($value);

        if (strtolower($value) === 'transparent') {
            return 'transparent';
        }

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) === 1) {
            if (strlen($value) === 4) {
                return '#'.$value[1].$value[1].$value[2].$value[2].$value[3].$value[3];
            }

            return strtolower($value);
        }

        return null;
    }

    public static function hexToRgbTriplet(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return $r.', '.$g.', '.$b;
    }

    /** @return array<string, array<int, string>> */
    public static function validationRules(): array
    {
        $rules = [];

        foreach (array_keys(self::definitions()) as $key) {
            $rules["themeColors.{$key}"] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }
}
