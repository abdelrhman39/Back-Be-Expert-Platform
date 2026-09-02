<?php

namespace Tests\Feature;

use App\Support\CmsBlockDefaults;
use Database\Seeders\CmsPageBlocksSeeder;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_defaults_include_identity_and_offerings(): void
    {
        $types = collect(CmsBlockDefaults::about('ar'))->pluck('type')->all();

        $this->assertContains('breadcrumb', $types);
        $this->assertContains('rich_text_split', $types);
        $this->assertContains('cards_grid', $types);
        $this->assertContains('path_cards', $types);
        $this->assertContains('features_grid', $types);
        $this->assertContains('stats', $types);
        $this->assertContains('download_cta', $types);
        $this->assertContains('cta_banner', $types);

        $intro = collect(CmsBlockDefaults::about('ar'))->firstWhere('id', 'about_intro');
        $this->assertStringContainsString('{platform_org}', $intro['data']['eyebrow'] ?? '');
        $this->assertSame(platform_campus_path('entrance'), $intro['data']['image'] ?? '');
        $this->assertCount(3, $intro['data']['paragraphs'] ?? []);
        $this->assertNotEmpty($intro['data']['highlights'] ?? []);
    }

    public function test_published_about_page_renders_professional_layout(): void
    {
        $this->seed([CmsSeeder::class, CmsPageBlocksSeeder::class]);

        $response = $this->get(route('about', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('atelier-about', false);
        $response->assertSee('cms-features', false);
        $response->assertSee('cms-features__card', false);
        $response->assertSee('about-profile__visual', false);
        $response->assertSee('np-cta__copy', false);
        $response->assertSee('نبني القدرات المهنية بمعايير أكاديمية واضحة');
        $response->assertSee('رسالتنا ورؤيتنا وأهدافنا');
        $response->assertSee('ماذا نقدّم؟');
        $response->assertSee('الشهادات الاحترافية');
        $response->assertSee('الدبلومات');
        $response->assertSee('التأهيل المؤسسي');
        $response->assertSee('لمن نقدّم خدماتنا');
        $response->assertSee('الملف التعريفي');
        $response->assertSee('ابدأ رحلتك مع مركز التعلم المستمر');
        $response->assertSee('الجامعة العربية المفتوحة');
        $response->assertSee('aou-campus-entrance.jpg', false);
        $response->assertDontSee('{platform_name}');
        $response->assertDontSee('برنامج مهارات');
    }
}
