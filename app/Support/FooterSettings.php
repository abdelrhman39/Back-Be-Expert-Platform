<?php

namespace App\Support;

use App\Models\PlatformSetting;

class FooterSettings
{
    /** @return array<string, array{ar: string, en: string, label_ar: string}> */
    public static function textFields(): array
    {
        return [
            'about' => [
                'label_ar' => 'نبذة عن المنصة (تحت الشعارات)',
                'ar' => 'منصة مركز التعلم المستمر التابعة لجامعة الامير مقرن تقدم برامج تعليمية وتدريبية مبتكرة لدعم مختلف القطاعات وتعزيز نشر المعرفة.',
                'en' => 'The Continuing Learning Center Platform at Muqrin University offers innovative educational and training programs to support various sectors and spread knowledge.',
            ],
            'programs_title' => [
                'label_ar' => 'عنوان عمود البرامج',
                'ar' => 'البرامج التدريبية',
                'en' => 'Training Programs',
            ],
            'policies_title' => [
                'label_ar' => 'عنوان عمود السياسات',
                'ar' => 'السياسات',
                'en' => 'Policies',
            ],
            'contact_phone_label' => [
                'label_ar' => 'تسمية رقم الجوال',
                'ar' => 'رقم الجوال',
                'en' => 'Phone',
            ],
            'contact_whatsapp_label' => [
                'label_ar' => 'تسمية الواتساب',
                'ar' => 'رقم الواتساب',
                'en' => 'WhatsApp',
            ],
            'contact_email_label' => [
                'label_ar' => 'تسمية البريد الإلكتروني',
                'ar' => 'البريد الإلكتروني للتواصل',
                'en' => 'Contact Email',
            ],
            'link_statement' => [
                'label_ar' => 'رابط «تقديم الإفادة» (النص)',
                'ar' => 'تقديم الإفادة',
                'en' => 'Request Statement',
            ],
            'link_certificate' => [
                'label_ar' => 'رابط «التحقق من الشهادة» (النص)',
                'ar' => 'التحقق من الشهادة',
                'en' => 'Verify Certificate',
            ],
        ];
    }

    /** @return array<string, array{label_ar: string, default_ar: string, default_en: string}> */
    public static function linkFields(): array
    {
        return [
            'statement' => [
                'label_ar' => 'رابط تقديم الإفادة',
                'default_ar' => 'ar/statment.html',
                'default_en' => 'en/statment.html',
            ],
            'certificate' => [
                'label_ar' => 'رابط التحقق من الشهادة',
                'default_ar' => 'certificate-verify',
                'default_en' => 'certificate-verify',
            ],
        ];
    }

    /** @return array<string, array{label_ar: string, default: string}> */
    public static function socialFields(): array
    {
        return [
            'twitter' => [
                'label_ar' => 'رابط X (تويتر)',
                'default' => 'https://x.com/_UOH',
            ],
            'facebook' => [
                'label_ar' => 'رابط فيسبوك',
                'default' => '',
            ],
            'linkedin' => [
                'label_ar' => 'رابط لينكدإن',
                'default' => '',
            ],
            'youtube' => [
                'label_ar' => 'رابط يوتيوب',
                'default' => '',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function formDefaults(): array
    {
        $values = [];

        foreach (self::textFields() as $stem => $field) {
            foreach (['ar', 'en'] as $locale) {
                $key = self::textKey($stem, $locale);
                $stored = PlatformSetting::get($key);
                $values[$key] = filled($stored) ? $stored : $field[$locale];
            }
        }

        foreach (self::linkFields() as $stem => $field) {
            foreach (['ar', 'en'] as $locale) {
                $key = "footer_link_{$stem}_url_{$locale}";
                $stored = PlatformSetting::get($key);
                $defaultKey = 'default_'.$locale;
                $values[$key] = filled($stored) ? $stored : $field[$defaultKey];
            }
        }

        foreach (self::socialFields() as $stem => $field) {
            $key = "footer_social_{$stem}";
            $stored = PlatformSetting::get($key);
            $values[$key] = filled($stored) ? $stored : $field['default'];
        }

        $values['footer_copyright_ar'] = PlatformSetting::get('footer_copyright_ar') ?? self::defaultCopyright('ar');
        $values['footer_copyright_en'] = PlatformSetting::get('footer_copyright_en') ?? self::defaultCopyright('en');

        return $values;
    }

    public static function textKey(string $stem, string $locale): string
    {
        return "footer_{$stem}_{$locale}";
    }

    public static function text(string $stem, ?string $locale = null): string
    {
        $locale = self::resolveLocale($locale);
        $key = self::textKey($stem, $locale);
        $stored = PlatformSetting::get($key);

        if (filled($stored)) {
            return $stored;
        }

        return self::textFields()[$stem][$locale] ?? self::textFields()[$stem]['ar'] ?? '';
    }

    public static function linkUrl(string $stem, ?string $locale = null): string
    {
        $locale = self::resolveLocale($locale);
        $key = "footer_link_{$stem}_url_{$locale}";
        $stored = trim((string) PlatformSetting::get($key));

        if ($stored === '') {
            $field = self::linkFields()[$stem] ?? null;
            $stored = $field['default_'.$locale] ?? '';
        }

        return self::resolveUrl($stored, $locale);
    }

    public static function socialUrl(string $stem): ?string
    {
        $key = "footer_social_{$stem}";
        $stored = trim((string) PlatformSetting::get($key));

        if ($stored === '') {
            $stored = trim((string) (self::socialFields()[$stem]['default'] ?? ''));
        }

        return filled($stored) ? $stored : null;
    }

    /** @return array<int, array{key: string, url: string, icon: string, label: string}> */
    public static function socialLinks(?string $locale = null): array
    {
        $locale = self::resolveLocale($locale);
        $links = [];

        $map = [
            'twitter' => ['icon' => 'fa-brands fa-x-twitter', 'label' => 'X'],
            'facebook' => ['icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
            'linkedin' => ['icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
            'youtube' => ['icon' => 'fa-brands fa-youtube', 'label' => 'YouTube'],
        ];

        foreach ($map as $key => $meta) {
            $url = self::socialUrl($key);

            if ($url) {
                $links[] = [
                    'key' => $key,
                    'url' => $url,
                    'icon' => $meta['icon'],
                    'label' => $meta['label'],
                ];
            }
        }

        return $links;
    }

    public static function contactPhone(): string
    {
        return PlatformSetting::get('support_phone', '') ?? '';
    }

    public static function contactWhatsapp(): string
    {
        return PlatformSetting::get('whatsapp_number', '') ?? '';
    }

    public static function contactEmail(): string
    {
        return PlatformSetting::get('support_email', '') ?? '';
    }

    public static function showPaymentIcons(): bool
    {
        return PlatformSetting::get('footer_show_payment_icons', '1') !== '0';
    }

    public static function showContactSection(): bool
    {
        return PlatformSetting::get('footer_show_contact_section', '1') !== '0';
    }

    public static function showSocialLinks(): bool
    {
        return PlatformSetting::get('footer_show_social_links', '1') !== '0';
    }

    public static function defaultCopyright(string $locale): string
    {
        return match ($locale) {
            'en' => 'All Rights Reserved Muqrin University, Developed and Designed by <span class="fw-blod">مركز التعلم المستمر</span>',
            default => 'جميع الحقوق محفوظة جامعة الامير مقرن، تطوير وتصميم <span class="fw-blod">مركز التعلم المستمر</span>',
        };
    }

    public static function copyrightHtml(?string $locale = null): string
    {
        $locale = self::resolveLocale($locale);
        $key = $locale === 'en' ? 'footer_copyright_en' : 'footer_copyright_ar';
        $stored = PlatformSetting::get($key);

        return filled($stored) ? $stored : self::defaultCopyright($locale);
    }

    protected static function resolveLocale(?string $locale): string
    {
        $locale ??= app()->getLocale();

        return in_array($locale, ['ar', 'en'], true) ? $locale : 'ar';
    }

    protected static function resolveUrl(string $value, string $locale): string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/') || str_contains($value, '/')) {
            return legacy_page(ltrim($value, '/'));
        }

        return route($value, ['locale' => $locale]);
    }
}
