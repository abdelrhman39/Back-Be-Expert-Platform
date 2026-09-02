<?php

namespace Tests\Feature;

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Services\CatalogCourseService;
use App\Services\CmsMenuService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderProgramMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_menu_links_go_to_catalog_pages(): void
    {
        $this->seed(CmsSeeder::class);

        $response = $this->get(route('home', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee(route('courses.certificates', ['locale' => 'ar']), false);
        $response->assertSee(route('courses.diplomas', ['locale' => 'ar']), false);
        $response->assertSee(route('fellowships.index', ['locale' => 'ar']), false);
        $response->assertDontSee('/ar#section-certificates', false);
        $response->assertDontSee('/ar#section-diplomas', false);
        $response->assertDontSee('برنامج مهارات');
        $response->assertDontSee('برنامج وعد');
        $response->assertDontSee('/ar/apply/employee', false);
        $response->assertDontSee('/ar/apply/job_seeker', false);
        $response->assertSee('الشهادات الاحترافية');
        $response->assertSee('الدبلومات');
        $response->assertSee('التقديم والتسجيل');
        $response->assertDontSee('>تسجيل الطلبات<', false);
        $response->assertSee('site-nav__link', false);
        $response->assertSee('site-nav__submenu', false);
        $response->assertSee('id="site-main-menu"', false);
        $response->assertSee('id="mobile_btn"', false);
        $response->assertSee('aria-controls="site-main-menu"', false);
        $response->assertSee('class="sidebar-overlay"', false);
    }

    public function test_legacy_homepage_hashes_resolve_to_catalog_routes(): void
    {
        $this->seed(CmsSeeder::class);

        $item = new CmsMenuItem([
            'link_type' => 'url',
            'url' => '/ar#section-certificates',
        ]);

        $url = app(CmsMenuService::class)->resolveUrl($item, 'ar');

        $this->assertSame(route('courses.certificates', ['locale' => 'ar']), $url);
    }

    public function test_certificate_and_diploma_shortcuts_redirect_to_filtered_catalog(): void
    {
        $this->get(route('courses.certificates', ['locale' => 'ar']))
            ->assertRedirect(route('courses.index', [
                'locale' => 'ar',
                'categories' => [CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES],
            ]));

        $this->get(route('courses.diplomas', ['locale' => 'ar']))
            ->assertRedirect(route('courses.index', [
                'locale' => 'ar',
                'categories' => [CatalogCourseService::CATEGORY_DIPLOMAS],
            ]));
    }

    public function test_fellowships_index_is_public(): void
    {
        $this->get(route('fellowships.index', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('الزمالات المهنية');
    }

    public function test_sync_command_rewrites_hash_links_to_routes(): void
    {
        $this->seed(CmsSeeder::class);

        $header = CmsMenu::query()->where('key', 'header_main')->firstOrFail();
        $child = CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'الشهادات الاحترافية')
            ->firstOrFail();

        $child->update([
            'link_type' => 'url',
            'route_name' => null,
            'url' => '/ar#section-certificates',
        ]);

        $this->artisan('cms:sync-home-menu')->assertSuccessful();

        $this->assertSame('route', $child->fresh()->link_type);
        $this->assertSame('courses.certificates', $child->fresh()->route_name);
    }

    public function test_english_footer_shows_translated_policy_links(): void
    {
        $this->seed(CmsSeeder::class);

        $response = $this->get(route('home', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Policies');
        $response->assertSee('Privacy Policy');
        $response->assertSee('Terms and Conditions');
        $response->assertSee('Technical Support Policy');
        $response->assertDontSee('سياسة الخصوصية');
        $response->assertDontSee('الشروط والأحكام');
    }

    public function test_footer_policies_follow_cms_pages_dynamically(): void
    {
        $this->seed(CmsSeeder::class);

        CmsMenuItem::query()
            ->whereHas('menu', fn ($q) => $q->where('key', 'footer_policies'))
            ->delete();

        app(CmsMenuService::class)->forgetCache('footer_policies');

        $pages = app(\App\Services\CmsPageService::class);
        $page = $pages->save([
            'type' => 'policy',
            'status' => 'published',
            'sort_order' => 90,
            'show_in_footer' => true,
            'translations' => [
                'ar' => [
                    'title' => 'سياسة ديناميكية',
                    'slug' => 'dynamic-policy-ar',
                    'body' => '<p>عربي</p>',
                ],
                'en' => [
                    'title' => 'Dynamic Footer Policy',
                    'slug' => 'dynamic-policy-en',
                    'body' => '<p>English</p>',
                ],
            ],
        ]);

        $this->get(route('home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Dynamic Footer Policy')
            ->assertSee(route('cms.page', ['locale' => 'en', 'slug' => 'dynamic-policy-en']), false)
            ->assertDontSee('سياسة ديناميكية');

        $this->get(route('home', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('سياسة ديناميكية');

        $pages->save([
            'type' => 'policy',
            'status' => 'published',
            'sort_order' => 90,
            'show_in_footer' => true,
            'translations' => [
                'ar' => [
                    'title' => 'سياسة ديناميكية',
                    'slug' => 'dynamic-policy-ar',
                    'body' => '<p>عربي</p>',
                ],
                'en' => [
                    'title' => 'Updated Footer Policy',
                    'slug' => 'dynamic-policy-en',
                    'body' => '<p>English</p>',
                ],
            ],
        ], $page);

        $this->get(route('home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Updated Footer Policy')
            ->assertDontSee('Dynamic Footer Policy');
    }

    public function test_english_header_translates_menu_labels_without_label_en(): void
    {
        $this->seed(CmsSeeder::class);

        CmsMenuItem::query()
            ->where('label_ar', 'تواصل معنا')
            ->update(['label_en' => null]);

        app(CmsMenuService::class)->forgetCache('header_main');

        $this->get(route('home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Contact us')
            ->assertSee('Apply and register')
            ->assertDontSee('>تواصل معنا<', false);
    }

    public function test_registration_menu_uses_apply_and_register_label(): void
    {
        $this->seed(CmsSeeder::class);

        $this->get(route('home', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Apply and register')
            ->assertDontSee('Student registration')
            ->assertDontSee('تسجيل الطلبات');
    }

    public function test_certificates_and_diplomas_are_under_training_programs(): void
    {
        $this->seed(CmsSeeder::class);

        $header = CmsMenu::query()->where('key', 'header_main')->firstOrFail();
        $programs = CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'البرامج التدريبية')
            ->firstOrFail();

        $certificates = CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'الشهادات الاحترافية')
            ->firstOrFail();
        $diplomas = CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'الدبلومات')
            ->firstOrFail();

        $this->assertSame($programs->id, $certificates->parent_id);
        $this->assertSame($programs->id, $diplomas->parent_id);
        $this->assertSame('courses.certificates', $certificates->route_name);
        $this->assertSame('courses.diplomas', $diplomas->route_name);

        $html = $this->get(route('home', ['locale' => 'ar']))->getContent();
        $this->assertSame(1, preg_match(
            '/البرامج التدريبية<\/span>[\s\S]*?<ul class="submenu site-nav__submenu">([\s\S]*?)<\/ul>/u',
            $html,
            $submenu,
        ));
        $this->assertStringContainsString('الشهادات الاحترافية', $submenu[1]);
        $this->assertStringContainsString('الدبلومات', $submenu[1]);
        $this->assertStringContainsString('الزمالات المهنية', $submenu[1]);
    }

    public function test_sync_command_nests_certificate_and_diploma_under_programs(): void
    {
        $this->seed(CmsSeeder::class);

        $header = CmsMenu::query()->where('key', 'header_main')->firstOrFail();
        $programs = CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'البرامج التدريبية')
            ->firstOrFail();

        CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'الشهادات الاحترافية')
            ->update(['parent_id' => null, 'sort_order' => 25]);

        $this->artisan('cms:sync-home-menu')->assertSuccessful();

        $certificates = CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'الشهادات الاحترافية')
            ->firstOrFail();

        $this->assertSame($programs->id, $certificates->fresh()->parent_id);
        $this->assertSame('courses.certificates', $certificates->fresh()->route_name);
        $this->assertSame(1, CmsMenuItem::query()
            ->where('menu_id', $header->id)
            ->where('label_ar', 'الشهادات الاحترافية')
            ->count());
    }
}
