<?php

namespace Tests\Feature;

use App\Models\CatalogField;
use Database\Seeders\CatalogFieldsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCoursesIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_courses_index_renders_professional_empty_catalog(): void
    {
        $this->seed(CatalogFieldsSeeder::class);

        $response = $this->get(route('courses.index', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('atelier-catalog', false);
        $response->assertSee('سيتم نشر البرامج قريباً');
        $response->assertSee('استكشف الشهادات الاحترافية والدبلومات');
        $response->assertSee('الشهادات الاحترافية');
        $response->assertSee('الدبلومات');
        $response->assertSee('الزمالات المهنية');
        $response->assertSee('إدارة المشاريع');
        $response->assertSee('aou-campus-aerial.jpg', false);
        $response->assertSee(platform_org());
        $response->assertDontSee('لا توجد دورات مطابقة لبحثك.');
    }

    public function test_english_courses_index_renders_empty_catalog_copy(): void
    {
        $response = $this->get(route('courses.index', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Programs will be published soon');
        $response->assertSee('Browse certificates, diplomas, and professional tracks');
        $response->assertSee('Contact us');
    }

    public function test_sidebar_fields_appear_even_without_published_courses(): void
    {
        CatalogField::query()->create([
            'id' => 99,
            'title_ar' => 'الذكاء الاصطناعي',
            'title_en' => 'Artificial Intelligence',
            'slug' => 'ai',
            'sort_order' => 1,
            'sidebar_visible' => true,
            'home_visible' => true,
        ]);

        $this->get(route('courses.index', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('الذكاء الاصطناعي')
            ->assertSee('تصفية النتائج');
    }
}
