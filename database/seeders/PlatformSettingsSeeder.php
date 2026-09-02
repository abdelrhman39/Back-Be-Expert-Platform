<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use App\Support\IdentityThemes;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'platform_name_ar', 'value' => 'مركز التعلم المستمر', 'label_ar' => 'اسم المنصة (عربي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_name_en', 'value' => 'Continuing Learning Center', 'label_ar' => 'اسم المنصة (إنجليزي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_org_ar', 'value' => 'الجامعة العربية المفتوحة', 'label_ar' => 'الجهة / الجامعة (عربي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_org_en', 'value' => 'Arab Open University', 'label_ar' => 'الجهة / الجامعة (إنجليزي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'support_email', 'value' => 'info@domain.edu.sa', 'label_ar' => 'البريد الرسمي', 'group' => 'general'],
            ['key' => 'support_phone', 'value' => '966543406744', 'label_ar' => 'هاتف الدعم', 'group' => 'general', 'is_public' => true],
            ['key' => 'whatsapp_number', 'value' => '966543406744', 'label_ar' => 'واتساب', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_locale', 'value' => 'ar', 'label_ar' => 'اللغة الافتراضية', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'label_ar' => 'وضع الصيانة', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'footer_copyright_ar', 'value' => 'جميع الحقوق محفوظة {platform_org}، تطوير وتصميم <span class="fw-bold">{platform_name}</span>', 'label_ar' => 'حقوق النشر في الفوتر (عربي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'footer_copyright_en', 'value' => 'All rights reserved, {platform_org}. Designed and developed by <span class="fw-bold">{platform_name}</span>', 'label_ar' => 'حقوق النشر في الفوتر (إنجليزي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_poster_image', 'value' => 'assets/branding/aou-logo.png', 'label_ar' => 'الصورة الافتراضية للبوستر', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_logo_primary', 'value' => 'assets/branding/aou-logo.png', 'label_ar' => 'الشعار الرئيسي', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_logo_secondary', 'value' => 'assets/d8e8b170-8627-42bc-86e9-f3c2e5c73222.png', 'label_ar' => 'الشعار الثانوي (الهيدر)', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_logo_footer', 'value' => 'assets/branding/aou-logo-footer.png', 'label_ar' => 'شعار الفوتر', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_logo_vision', 'value' => 'assets/visionLogo.png', 'label_ar' => 'شعار الرؤية (الفوتر)', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_favicon', 'value' => 'assets/branding/aou-favicon.png', 'label_ar' => 'أيقونة الموقع (Favicon)', 'group' => 'branding', 'is_public' => true],
            ['key' => 'logo_primary_visible', 'value' => '1', 'label_ar' => 'إظهار الشعار الرئيسي', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'logo_secondary_visible', 'value' => '0', 'label_ar' => 'إظهار الشعار الثانوي', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'logo_footer_visible', 'value' => '1', 'label_ar' => 'إظهار شعار الفوتر', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'logo_vision_visible', 'value' => '0', 'label_ar' => 'إظهار شعار الرؤية', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_logos_mode', 'value' => 'first', 'label_ar' => 'وضع شعارات الفوتر', 'group' => 'branding', 'is_public' => true],
            ['key' => 'theme_color_primary', 'value' => '', 'label_ar' => 'اللون الأساسي', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_secondary', 'value' => '', 'label_ar' => 'اللون الثانوي', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_primary_dark', 'value' => '', 'label_ar' => 'اللون الأساسي الداكن', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_primary_light', 'value' => '', 'label_ar' => 'اللون الأساسي الفاتح', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_page_bg', 'value' => '', 'label_ar' => 'خلفية الصفحات', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_text', 'value' => '', 'label_ar' => 'لون النص الأساسي', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_footer_bg', 'value' => '', 'label_ar' => 'خلفية الفوتر', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_footer_text', 'value' => '', 'label_ar' => 'لون نص الفوتر', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_bg', 'value' => '', 'label_ar' => 'خلفية الهيدر (الرئيسية)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_bg_fixed', 'value' => '', 'label_ar' => 'خلفية الهيدر (داخلية)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_nav_color', 'value' => '', 'label_ar' => 'لون روابط النافبار (الرئيسية)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_nav_color_inner', 'value' => '', 'label_ar' => 'لون روابط النافبار (داخلية)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_nav_hover', 'value' => '', 'label_ar' => 'لون الروابط عند التمرير', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_border', 'value' => '', 'label_ar' => 'حدود الهيدر', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_toolbar_color', 'value' => '', 'label_ar' => 'لون أيقونات الشريط العلوي', 'group' => 'theme', 'is_public' => true],
            ['key' => 'footer_show_payment_icons', 'value' => '1', 'label_ar' => 'إظهار أيقونات الدفع في الفوتر', 'group' => 'footer', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_show_contact_section', 'value' => '1', 'label_ar' => 'إظهار قسم التواصل في الفوتر', 'group' => 'footer', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_show_social_links', 'value' => '1', 'label_ar' => 'إظهار روابط التواصل الاجتماعي', 'group' => 'footer', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_social_twitter', 'value' => 'https://x.com/_UOH', 'label_ar' => 'رابط X في الفوتر', 'group' => 'footer', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
            PlatformSetting::forgetCache($setting['key']);
        }

        IdentityThemes::apply(IdentityThemes::DEFAULT);
    }
}
