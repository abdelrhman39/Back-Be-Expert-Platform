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

    public function test_taibah_host_uses_the_applied_college_and_research_institute(): void
    {
        $this->bindTaibahRequest();

        $this->assertSame('الكلية التطبيقية', platform_name('ar'));
        $this->assertSame('Applied College', platform_name('en'));
        $this->assertSame(
            'الكلية التطبيقية — جامعة طيبة',
            Utf8Text::interpolate('{platform_name} — الجامعة العربية المفتوحة', 'ar'),
        );
        $this->assertSame(
            'الكلية التطبيقية',
            IdentityOverlay::rewriteText('مركز التعلم المستمر'),
        );
        $this->assertSame(
            'الكلية التطبيقية التابعة لجامعة طيبة',
            IdentityOverlay::rewriteText('منصة التعليم المستمر التابعة لجامعة طيبة'),
        );
        $this->assertSame(
            'تقدّمه الكلية التطبيقية في جامعة طيبة',
            IdentityOverlay::rewriteText('يقدّمه مركز التعلم المستمر في جامعة طيبة'),
        );
        $this->assertSame(
            'الكلية التطبيقية التابعة لجامعة طيبة تقدّم برامج',
            IdentityOverlay::rewriteText('مركز التعلم المستمر التابع لـ جامعة طيبة يقدّم برامج'),
        );
        $this->assertSame(
            'معهد البحوث والاستشارات',
            IdentityOverlay::rewriteText('معهد البحوث والدراسات الاستشارية'),
        );
    }

    protected function bindKkuRequest(): void
    {
        $this->app->instance('request', Request::create('https://kku.platformuni.com/ar', 'GET'));
    }

    protected function bindTaibahRequest(): void
    {
        $this->app->instance('request', Request::create('https://taibah.platformuni.com/ar', 'GET'));
    }
}
