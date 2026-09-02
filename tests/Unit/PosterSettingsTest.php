<?php

namespace Tests\Unit;

use App\Models\PlatformSetting;
use App\Support\LogoSettings;
use App\Support\PosterSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_poster_is_the_university_logo(): void
    {
        $this->assertSame(
            LogoSettings::defaultPath(LogoSettings::KEY_PRIMARY),
            PosterSettings::defaultAssetPath()
        );
        $this->assertStringContainsString('aou-logo.png', PosterSettings::url());
    }

    public function test_legacy_favicon_poster_falls_back_to_university_logo(): void
    {
        $this->assertTrue(PosterSettings::isLegacyPoster('assets/vendor/images/site-favicon.png'));

        PlatformSetting::set(
            'default_poster_image',
            'assets/vendor/images/site-favicon.png',
            'general',
            'الصورة الافتراضية للبوستر'
        );

        $this->assertNull(PosterSettings::storedPath());
        $this->assertStringContainsString('aou-logo.png', PosterSettings::url());
        $this->assertStringContainsString('aou-logo.png', PosterSettings::resolve('assets/vendor/images/site-favicon.png'));
    }
}
