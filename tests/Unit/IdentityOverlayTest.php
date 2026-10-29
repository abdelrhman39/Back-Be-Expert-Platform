<?php

namespace Tests\Unit;

use App\Support\CampusMap;
use App\Support\IdentityOverlay;
use App\Support\PosterSettings;
use App\Support\Utf8Text;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class IdentityOverlayTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_host_does_not_rewrite_arab_open_university_copy(): void
    {
        $this->assertFalse(IdentityOverlay::enabled());
        $this->assertSame(
            'برامج الجامعة العربية المفتوحة',
            IdentityOverlay::rewriteText('برامج الجامعة العربية المفتوحة'),
        );
        $this->assertStringContainsString('aou-logo.png', PosterSettings::url());
        $this->assertStringContainsString(
            rawurlencode('الجامعة العربية المفتوحة السعودية'),
            CampusMap::embedUrl(),
        );
    }

    public function test_kku_host_rewrites_names_posters_and_campus_map(): void
    {
        $this->bindKkuRequest();

        $this->assertTrue(IdentityOverlay::enabled());
        $this->assertSame(
            'برامج جامعة الملك خالد',
            IdentityOverlay::rewriteText('برامج الجامعة العربية المفتوحة'),
        );
        $this->assertSame(
            'King Khalid University programs',
            IdentityOverlay::rewriteText('Arab Open University programs'),
        );
        $this->assertSame(
            'assets/branding/kku-logo.png',
            IdentityOverlay::rewriteAssetPath('assets/branding/aou-logo.png'),
        );
        $this->assertStringContainsString('kku-logo.png', PosterSettings::url());
        $this->assertStringContainsString('kku-logo.png', PosterSettings::resolve('assets/branding/aou-logo.png'));
        $this->assertStringContainsString(
            rawurlencode('جامعة الملك خالد، أبها'),
            CampusMap::embedUrl(),
        );
        $this->assertSame(
            'مركز التعلم المستمر — جامعة الملك خالد',
            Utf8Text::interpolate('{platform_name} — الجامعة العربية المفتوحة', 'ar'),
        );
    }

    protected function bindKkuRequest(): void
    {
        $this->app->instance('request', Request::create('https://kku.platformuni.com/ar', 'GET'));
    }
}
