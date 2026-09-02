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

        $this->assertCount(9, IdentityThemes::presets());

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
