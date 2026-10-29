<?php

namespace Tests\Unit;

use App\Models\PlatformSetting;
use App\Support\IdentityThemes;
use App\Support\ThemeSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentityThemesTest extends TestCase
{
    use RefreshDatabase;

    public function test_builtin_presets_cover_all_theme_color_keys(): void
    {
        $keys = array_keys(ThemeSettings::definitions());

        $this->assertCount(11, IdentityThemes::presets());

        foreach (IdentityThemes::presets() as $pack) {
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $pack['colors']);
                $this->assertNotSame('', $pack['colors'][$key]);
            }
        }
    }

    public function test_default_identity_is_arab_open_university(): void
    {
        $this->assertSame('aou-navy', IdentityThemes::DEFAULT);
        $this->assertSame('aou-navy', IdentityThemes::activeKey());
        $this->assertSame('#002d58', IdentityThemes::active()['colors']['theme_color_primary']);
    }

    public function test_apply_writes_identity_colors_without_touching_platform_name(): void
    {
        PlatformSetting::set('platform_name_ar', 'منصة اختبار الجهة', 'general', 'اسم المنصة');

        IdentityThemes::apply('navy-gold');

        $this->assertSame('navy-gold', IdentityThemes::activeKey());
        $this->assertSame('#0c2e5a', ThemeSettings::effective('theme_color_primary'));
        $this->assertSame('#c9a227', ThemeSettings::effective('theme_color_secondary'));
        $this->assertSame('منصة اختبار الجهة', PlatformSetting::get('platform_name_ar'));
        $this->assertFalse(IdentityThemes::isCustomized());
    }

    public function test_custom_pack_can_be_saved_and_reapplied(): void
    {
        IdentityThemes::apply('burgundy-gold');
        $id = IdentityThemes::saveCustom('هوية الكلية', 'College identity');

        $this->assertTrue(str_starts_with($id, 'custom-'));
        $this->assertArrayHasKey($id, IdentityThemes::customPacks());

        IdentityThemes::apply('muqrin-green');
        $this->assertSame('#1b8354', ThemeSettings::effective('theme_color_primary'));

        IdentityThemes::apply($id);
        $this->assertSame('#7a1f3d', ThemeSettings::effective('theme_color_primary'));
        $this->assertSame($id, IdentityThemes::activeKey());
    }

    public function test_king_khalid_identity_is_a_separate_division_from_aou(): void
    {
        PlatformSetting::set('platform_name_ar', 'مركز التعلم المستمر', 'general', 'اسم المنصة');

        $this->assertArrayHasKey('aou-navy', IdentityThemes::divisions());
        $this->assertArrayHasKey('kku-green', IdentityThemes::divisions());
        $this->assertSame('الجامعة العربية المفتوحة', IdentityThemes::presets()['aou-navy']['name_ar']);
        $this->assertSame('جامعة الملك خالد', IdentityThemes::presets()['kku-green']['name_ar']);

        IdentityThemes::apply('kku-green');

        $this->assertSame('kku-green', IdentityThemes::activeKey());
        $this->assertSame('#006838', ThemeSettings::effective('theme_color_primary'));
        $this->assertSame('جامعة الملك خالد', PlatformSetting::get('platform_org_ar'));
        $this->assertSame('assets/branding/kku-logo.png', PlatformSetting::get('platform_logo_primary'));
        $this->assertSame('مركز التعلم المستمر', PlatformSetting::get('platform_name_ar'));
        $this->assertArrayHasKey('aou-navy', IdentityThemes::all());

        IdentityThemes::apply('aou-navy');

        $this->assertSame('aou-navy', IdentityThemes::activeKey());
        $this->assertSame('#002d58', ThemeSettings::effective('theme_color_primary'));
        $this->assertSame('الجامعة العربية المفتوحة', PlatformSetting::get('platform_org_ar'));
        $this->assertSame('assets/branding/aou-logo.png', PlatformSetting::get('platform_logo_primary'));
        $this->assertSame('جامعة الملك خالد', IdentityThemes::presets()['kku-green']['branding']['platform_org_ar']);
    }

    public function test_color_palette_does_not_replace_university_branding(): void
    {
        IdentityThemes::apply('kku-green');
        IdentityThemes::apply('navy-gold');

        $this->assertSame('navy-gold', IdentityThemes::activeKey());
        $this->assertSame('#0c2e5a', ThemeSettings::effective('theme_color_primary'));
        $this->assertSame('جامعة الملك خالد', PlatformSetting::get('platform_org_ar'));
        $this->assertSame('assets/branding/kku-logo.png', PlatformSetting::get('platform_logo_primary'));
    }

    public function test_kku_subdomain_locks_king_khalid_identity_without_replacing_aou(): void
    {
        $this->assertSame('kku-green', IdentityThemes::keyForHost('kku.platformuni.com'));
        $this->assertNull(IdentityThemes::keyForHost('platformuni.com'));
        $this->assertSame('assets/branding/kku-campus.jpg', IdentityThemes::presets()['kku-green']['campus']['aerial']);
        $this->assertSame('assets/branding/aou-campus-aerial.jpg', IdentityThemes::presets()['aou-navy']['campus']['aerial']);
        $this->assertSame('aou-navy', IdentityThemes::storedKey());
        $this->assertSame('aou-navy', IdentityThemes::activeKey());
    }

    public function test_taibah_identity_matches_the_official_palette(): void
    {
        $pack = IdentityThemes::presets()['taibah-green'];

        $this->assertSame('جامعة طيبة', $pack['name_ar']);
        $this->assertSame('#074d31', $pack['colors']['theme_color_primary']);
        $this->assertSame('#161616', $pack['colors']['theme_color_secondary']);
        $this->assertSame('#161616', $pack['colors']['theme_color_footer_bg']);
        $this->assertSame('#ffffff', $pack['colors']['theme_header_bg']);
        $this->assertSame('جامعة طيبة', $pack['branding']['platform_org_ar']);
        $this->assertSame('assets/branding/taibah-logo.svg', $pack['branding']['platform_logo_primary']);
        $this->assertSame('assets/branding/taibah-campus.jpg', $pack['campus']['aerial']);
        $this->assertSame('taibah-green', IdentityThemes::keyForHost('taibah.platformuni.com'));

        IdentityThemes::apply('taibah-green');

        $this->assertSame('taibah-green', IdentityThemes::activeKey());
        $this->assertSame('جامعة طيبة', PlatformSetting::get('platform_org_ar'));
        $this->assertSame('assets/branding/taibah-logo-white.svg', PlatformSetting::get('platform_logo_footer'));
    }

    public function test_css_variables_include_home_identity_tokens(): void
    {
        IdentityThemes::apply('royal-blue');

        $variables = ThemeSettings::cssVariables();

        $this->assertSame('#1e4d8c', $variables['--primary']);
        $this->assertSame('#12305c', $variables['--np-hero-green']);
        $this->assertArrayHasKey('--np-hero-overlay-rgb', $variables);
        $this->assertMatchesRegularExpression('/^\d+, \d+, \d+$/', $variables['--np-hero-overlay-rgb']);
    }
}
