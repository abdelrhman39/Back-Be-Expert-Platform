<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'platform_name_ar', 'value' => 'Ù…Ù†ØµØ© Ù…Ø±ÙƒØ² Ø§Ù„ØªØ¹Ù„ÙŠÙ… Ø§Ù„Ù…Ø³ØªÙ…Ø±', 'label_ar' => 'Ø§Ø³Ù… Ø§Ù„Ù…Ù†ØµØ© (Ø¹Ø±Ø¨ÙŠ)', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_name_en', 'value' => 'Continuing Learning Center Platform', 'label_ar' => 'Ø§Ø³Ù… Ø§Ù„Ù…Ù†ØµØ© (Ø¥Ù†Ø¬Ù„ÙŠØ²ÙŠ)', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_org_ar', 'value' => 'Ø¬Ø§Ù…Ø¹Ø© Ù…Ù‚Ø±Ù†', 'label_ar' => 'Ø§Ù„Ø¬Ù‡Ø© / Ø§Ù„Ø¬Ø§Ù…Ø¹Ø© (Ø¹Ø±Ø¨ÙŠ)', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_org_en', 'value' => 'Muqrin University', 'label_ar' => 'Ø§Ù„Ø¬Ù‡Ø© / Ø§Ù„Ø¬Ø§Ù…Ø¹Ø© (Ø¥Ù†Ø¬Ù„ÙŠØ²ÙŠ)', 'group' => 'general', 'is_public' => true],
            ['key' => 'support_email', 'value' => 'info@domain.edu.sa', 'label_ar' => 'Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø±Ø³Ù…ÙŠ', 'group' => 'general'],
            ['key' => 'support_phone', 'value' => '966543406744', 'label_ar' => 'Ù‡Ø§ØªÙ Ø§Ù„Ø¯Ø¹Ù…', 'group' => 'general', 'is_public' => true],
            ['key' => 'whatsapp_number', 'value' => '966543406744', 'label_ar' => 'ÙˆØ§ØªØ³Ø§Ø¨', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_locale', 'value' => 'ar', 'label_ar' => 'Ø§Ù„Ù„ØºØ© Ø§Ù„Ø§ÙØªØ±Ø§Ø¶ÙŠØ©', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'label_ar' => 'ÙˆØ¶Ø¹ Ø§Ù„ØµÙŠØ§Ù†Ø©', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'footer_copyright_ar', 'value' => 'جميع الحقوق محفوظة جامعة الامير مقرن، تطوير وتصميم <span class="fw-blod">مركز التعلم المستمر</span>', 'label_ar' => 'حقوق النشر في الفوتر (عربي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'footer_copyright_en', 'value' => 'All Rights Reserved Muqrin University, Developed and Designed by <span class="fw-blod">مركز التعلم المستمر</span>', 'label_ar' => 'حقوق النشر في الفوتر (إنجليزي)', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_poster_image', 'value' => 'assets/vendor/images/site-favicon.png', 'label_ar' => 'Ø§Ù„ØµÙˆØ±Ø© Ø§Ù„Ø§ÙØªØ±Ø§Ø¶ÙŠØ© Ù„Ù„Ø¨ÙˆØ³ØªØ±', 'group' => 'general', 'is_public' => true],
            ['key' => 'platform_logo_primary', 'value' => 'assets/ba5c2cc1-5c62-4b77-8607-bead454d224e.png', 'label_ar' => 'Ø§Ù„Ø´Ø¹Ø§Ø± Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_logo_secondary', 'value' => 'assets/d8e8b170-8627-42bc-86e9-f3c2e5c73222.png', 'label_ar' => 'Ø§Ù„Ø´Ø¹Ø§Ø± Ø§Ù„Ø«Ø§Ù†ÙˆÙŠ (Ø§Ù„Ù‡ÙŠØ¯Ø±)', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_logo_footer', 'value' => 'assets/ba5c2cc1-5c62-4b77-8607-bead454d224e(1).png', 'label_ar' => 'Ø´Ø¹Ø§Ø± Ø§Ù„ÙÙˆØªØ±', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_logo_vision', 'value' => 'assets/visionLogo.png', 'label_ar' => 'Ø´Ø¹Ø§Ø± Ø§Ù„Ø±Ø¤ÙŠØ© (Ø§Ù„ÙÙˆØªØ±)', 'group' => 'branding', 'is_public' => true],
            ['key' => 'platform_favicon', 'value' => 'assets/vendor/images/site-favicon.png', 'label_ar' => 'Ø£ÙŠÙ‚ÙˆÙ†Ø© Ø§Ù„Ù…ÙˆÙ‚Ø¹ (Favicon)', 'group' => 'branding', 'is_public' => true],
            ['key' => 'logo_primary_visible', 'value' => '1', 'label_ar' => 'Ø¥Ø¸Ù‡Ø§Ø± Ø§Ù„Ø´Ø¹Ø§Ø± Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠ', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'logo_secondary_visible', 'value' => '1', 'label_ar' => 'Ø¥Ø¸Ù‡Ø§Ø± Ø§Ù„Ø´Ø¹Ø§Ø± Ø§Ù„Ø«Ø§Ù†ÙˆÙŠ', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'logo_footer_visible', 'value' => '1', 'label_ar' => 'إظهار شعار الفوتر', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'logo_vision_visible', 'value' => '0', 'label_ar' => 'إظهار شعار الرؤية', 'group' => 'branding', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_logos_mode', 'value' => 'first', 'label_ar' => 'وضع شعارات الفوتر', 'group' => 'branding', 'is_public' => true],
            ['key' => 'theme_color_primary', 'value' => '', 'label_ar' => 'Ø§Ù„Ù„ÙˆÙ† Ø§Ù„Ø£Ø³Ø§Ø³ÙŠ', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_secondary', 'value' => '', 'label_ar' => 'Ø§Ù„Ù„ÙˆÙ† Ø§Ù„Ø«Ø§Ù†ÙˆÙŠ', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_primary_dark', 'value' => '', 'label_ar' => 'Ø§Ù„Ù„ÙˆÙ† Ø§Ù„Ø£Ø³Ø§Ø³ÙŠ Ø§Ù„Ø¯Ø§ÙƒÙ†', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_primary_light', 'value' => '', 'label_ar' => 'Ø§Ù„Ù„ÙˆÙ† Ø§Ù„Ø£Ø³Ø§Ø³ÙŠ Ø§Ù„ÙØ§ØªØ­', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_page_bg', 'value' => '', 'label_ar' => 'Ø®Ù„ÙÙŠØ© Ø§Ù„ØµÙØ­Ø§Øª', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_text', 'value' => '', 'label_ar' => 'Ù„ÙˆÙ† Ø§Ù„Ù†Øµ Ø§Ù„Ø£Ø³Ø§Ø³ÙŠ', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_footer_bg', 'value' => '', 'label_ar' => 'Ø®Ù„ÙÙŠØ© Ø§Ù„ÙÙˆØªØ±', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_color_footer_text', 'value' => '', 'label_ar' => 'Ù„ÙˆÙ† Ù†Øµ Ø§Ù„ÙÙˆØªØ±', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_bg', 'value' => '', 'label_ar' => 'Ø®Ù„ÙÙŠØ© Ø§Ù„Ù‡ÙŠØ¯Ø± (Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠØ©)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_bg_fixed', 'value' => '', 'label_ar' => 'Ø®Ù„ÙÙŠØ© Ø§Ù„Ù‡ÙŠØ¯Ø± (Ø¯Ø§Ø®Ù„ÙŠØ©)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_nav_color', 'value' => '', 'label_ar' => 'Ù„ÙˆÙ† Ø±ÙˆØ§Ø¨Ø· Ø§Ù„Ù†Ø§ÙØ¨Ø§Ø± (Ø§Ù„Ø±Ø¦ÙŠØ³ÙŠØ©)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_nav_color_inner', 'value' => '', 'label_ar' => 'Ù„ÙˆÙ† Ø±ÙˆØ§Ø¨Ø· Ø§Ù„Ù†Ø§ÙØ¨Ø§Ø± (Ø¯Ø§Ø®Ù„ÙŠØ©)', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_nav_hover', 'value' => '', 'label_ar' => 'Ù„ÙˆÙ† Ø§Ù„Ø±ÙˆØ§Ø¨Ø· Ø¹Ù†Ø¯ Ø§Ù„ØªÙ…Ø±ÙŠØ±', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_border', 'value' => '', 'label_ar' => 'Ø­Ø¯ÙˆØ¯ Ø§Ù„Ù‡ÙŠØ¯Ø±', 'group' => 'theme', 'is_public' => true],
            ['key' => 'theme_header_toolbar_color', 'value' => '', 'label_ar' => 'Ù„ÙˆÙ† Ø£ÙŠÙ‚ÙˆÙ†Ø§Øª Ø§Ù„Ø´Ø±ÙŠØ· Ø§Ù„Ø¹Ù„ÙˆÙŠ', 'group' => 'theme', 'is_public' => true],
            ['key' => 'footer_show_payment_icons', 'value' => '1', 'label_ar' => 'Ø¥Ø¸Ù‡Ø§Ø± Ø£ÙŠÙ‚ÙˆÙ†Ø§Øª Ø§Ù„Ø¯ÙØ¹ ÙÙŠ Ø§Ù„ÙÙˆØªØ±', 'group' => 'footer', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_show_contact_section', 'value' => '1', 'label_ar' => 'Ø¥Ø¸Ù‡Ø§Ø± Ù‚Ø³Ù… Ø§Ù„ØªÙˆØ§ØµÙ„ ÙÙŠ Ø§Ù„ÙÙˆØªØ±', 'group' => 'footer', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_show_social_links', 'value' => '1', 'label_ar' => 'Ø¥Ø¸Ù‡Ø§Ø± Ø±ÙˆØ§Ø¨Ø· Ø§Ù„ØªÙˆØ§ØµÙ„ Ø§Ù„Ø§Ø¬ØªÙ…Ø§Ø¹ÙŠ', 'group' => 'footer', 'type' => 'boolean', 'is_public' => true],
            ['key' => 'footer_social_twitter', 'value' => 'https://x.com/_UOH', 'label_ar' => 'Ø±Ø§Ø¨Ø· X ÙÙŠ Ø§Ù„ÙÙˆØªØ±', 'group' => 'footer', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
