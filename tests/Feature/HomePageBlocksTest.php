<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\CatalogCourse;
use App\Services\CatalogCourseService;
use App\Support\CmsBlockDefaults;
use Database\Seeders\CmsPageBlocksSeeder;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_defaults_include_identity_ready_sections(): void
    {
        $types = collect(CmsBlockDefaults::home('ar'))->pluck('type')->all();

        $this->assertContains('hero', $types);
        $this->assertContains('path_cards', $types);
        $this->assertContains('steps_grid', $types);
        $this->assertContains('cta_banner', $types);
        $this->assertContains('catalog_section', $types);
        $this->assertContains('faq', $types);

        $hero = collect(CmsBlockDefaults::home('ar'))->firstWhere('id', 'hero');
        $this->assertSame('{platform_name}', $hero['data']['title'] ?? null);
        $this->assertStringContainsString('{platform_org}', $hero['data']['subtitle_lines'][1] ?? '');
        $this->assertSame(platform_campus_path('aerial'), $hero['data']['image'] ?? null);
        $this->assertSame(platform_campus_gallery(), $hero['data']['gallery'] ?? null);
        $this->assertSame(platform_campus_path('entrance'), $hero['data']['showcase_image'] ?? null);

        $features = collect(CmsBlockDefaults::home('ar'))->firstWhere('id', 'platform_features');
        $this->assertSame('مزايا المنصة', $features['data']['eyebrow'] ?? null);
        $this->assertNotEmpty($features['data']['lead'] ?? '');

        $fields = collect(CmsBlockDefaults::home('ar'))->firstWhere('id', 'popular_fields');
        $this->assertFalse($fields['enabled'] ?? true);
    }

    public function test_hero_metrics_fill_empty_counts_with_trust_items(): void
    {
        $items = app(\App\Services\HomePageService::class)->heroMetrics('ar');

        $this->assertCount(3, $items);
        $this->assertSame('شهادات قابلة للتحقق', $items[0]['label']);
        $this->assertNotEmpty($items[0]['icon'] ?? '');
    }

    public function test_published_home_page_renders_new_blocks(): void
    {
        $this->seed([CmsSeeder::class, CmsPageBlocksSeeder::class]);

        $response = $this->get(route('home', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('مسارات واضحة لكل مستفيد');
        $response->assertSee('كيف تبدأ؟');
        $response->assertSee('ابدأ رحلتك التعليمية اليوم');
        $response->assertSee('الأسئلة الشائعة');
        $response->assertSee('مركز التعلم المستمر');
        $response->assertSee('الجامعة العربية المفتوحة');
        $response->assertSee('aou-campus-aerial.jpg', false);
        $response->assertSee('aou-campus-entrance.jpg', false);
        $response->assertSee('data-hero-slider', false);
        $response->assertSee('np-home-hero__slide', false);
        $response->assertSee('np-home-hero__panel-media--reel', false);
        $response->assertSee('np-home-hero__panel', false);
        $response->assertSee('تعرّف على المنصة');
        $response->assertSee('حرم الجامعة');
        $response->assertDontSee('Ù');
        $response->assertDontSee('{platform_name}');
        $response->assertDontSee('برنامج مهارات');
        $response->assertSee('الشهادات الاحترافية');
        $response->assertSee('الدبلومات');
        $response->assertDontSee('تخصصات المنصة');
        $response->assertDontSee('جميع المجالات');
        $response->assertDontSee('lg-fields-section', false);
        $response->assertSee('cms-features--home', false);
        $response->assertSee('cms-features__card--featured', false);
        $response->assertSee('مزايا المنصة');
        $response->assertSee('انتماء أكاديمي واضح');
        $response->assertSee('footer--atelier', false);
        $response->assertSee('data-identity-theme', false);
        $response->assertSee('data-logo-marquee', false);
        $response->assertSee('lg-logo-marquee__group', false);
        $response->assertSee('js/logo-marquee.js', false);
        $response->assertSee('js/home-hero.js', false);
        $response->assertSee('js/home-catalog-slider.js', false);
    }

    public function test_home_certificates_and_diplomas_render_as_split_card_slider(): void
    {
        $this->seed([CmsSeeder::class, CmsPageBlocksSeeder::class]);

        CatalogCategory::query()->updateOrCreate(
            ['id' => CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES],
            ['title_ar' => 'الشهادات الاحترافية', 'title_en' => 'Professional certificates', 'slug' => 'professional-certificates', 'sort_order' => 10, 'sidebar_visible' => true],
        );
        CatalogCategory::query()->updateOrCreate(
            ['id' => CatalogCourseService::CATEGORY_DIPLOMAS],
            ['title_ar' => 'الدبلومات', 'title_en' => 'Diplomas', 'slug' => 'diplomas', 'sort_order' => 20, 'sidebar_visible' => true],
        );

        foreach ([801, 802, 803] as $index => $id) {
            $course = CatalogCourse::query()->create([
                'id' => $id,
                'title_ar' => 'شهادة اختبار '.$id,
                'title_en' => 'Certificate '.$id,
                'slug' => 'home-slider-cert-'.$id,
                'status' => 'published',
                'is_featured' => $index === 0,
                'price_online' => 1900,
                'duration_hours' => 30,
            ]);
            $course->categories()->sync([CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES]);
        }

        foreach ([811, 812] as $id) {
            $course = CatalogCourse::query()->create([
                'id' => $id,
                'title_ar' => 'دبلوم اختبار '.$id,
                'title_en' => 'Diploma '.$id,
                'slug' => 'home-slider-diploma-'.$id,
                'status' => 'published',
                'price_online' => 10500,
                'duration_hours' => 340,
            ]);
            $course->categories()->sync([CatalogCourseService::CATEGORY_DIPLOMAS]);
        }

        $response = $this->get(route('home', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('data-program-slider', false);
        $response->assertSee('home-program-track', false);
        $response->assertSee('js/home-catalog-slider.js', false);
        $response->assertSee('diploma-list-card', false);
        $response->assertSee('شهادة اختبار 801');
        $response->assertSee('دبلوم اختبار 811');
        $response->assertSee('دبلوم أكاديمي');
        $response->assertSee('عرض التفاصيل');
        $response->assertSee('التسجيل والدفع');
        $response->assertDontSee('lg-program-grid', false);
        $response->assertDontSee('home-program-card', false);
        $response->assertDontSee('lg-service', false);
    }

    public function test_english_home_translates_features_stats_and_shows_news(): void
    {
        $features = collect(CmsBlockDefaults::home('en'))->firstWhere('id', 'platform_features');
        $this->assertSame('Why this platform?', $features['data']['title'] ?? null);
        $this->assertSame('Accredited certificates', $features['data']['items'][0]['title'] ?? null);

        $stats = collect(CmsBlockDefaults::home('en'))->firstWhere('id', 'stats');
        $this->assertSame('Learners', $stats['data']['items'][0]['label'] ?? null);

        $this->seed([
            CmsSeeder::class,
            CmsPageBlocksSeeder::class,
            \Database\Seeders\ArticleSeeder::class,
        ]);

        $response = $this->get(route('home', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Why this platform?');
        $response->assertSee('Accredited certificates');
        $response->assertSee('Diverse specializations');
        $response->assertSee('in Numbers');
        $response->assertSee('Learners');
        $response->assertSee('FAQ');
        $response->assertSee('News & Events');
        $response->assertSee('Knowledge partnership');
        $response->assertSee('Read more');
        $response->assertSee('All news & events');
        $response->assertDontSee('شهادات معتمدة');
        $response->assertDontSee('لا توجد أخبار منشورة حالياً');
    }
}
