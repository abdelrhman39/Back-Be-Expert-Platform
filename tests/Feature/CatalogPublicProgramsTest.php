<?php

namespace Tests\Feature;

use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use App\Services\CatalogCourseService;
use Database\Seeders\CatalogPublicProgramsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPublicProgramsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_publishes_varied_certificates_and_diplomas_with_content(): void
    {
        $this->seed(CatalogPublicProgramsSeeder::class);

        $certificates = CatalogCourse::query()
            ->where('status', 'published')
            ->whereHas('categories', fn ($query) => $query->whereKey(CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES))
            ->count();
        $diplomas = CatalogCourse::query()
            ->where('status', 'published')
            ->whereHas('categories', fn ($query) => $query->whereKey(CatalogCourseService::CATEGORY_DIPLOMAS))
            ->count();

        $this->assertGreaterThanOrEqual(5, $certificates);
        $this->assertGreaterThanOrEqual(4, $diplomas);
        $this->assertGreaterThanOrEqual(40, CatalogCourseModule::query()->count());
        $this->assertGreaterThanOrEqual(100, CatalogCourseLesson::query()->count());

        $course = CatalogCourse::query()->where('slug', 'professional-project-management-certificate')->firstOrFail();
        $this->assertNotNull($course->details?->brief_ar);
        $this->assertNotEmpty(strip_tags((string) $course->details->brief_ar));
        $this->assertGreaterThanOrEqual(3, $course->modules()->count());
        $this->assertTrue($course->modules()->first()?->lessons()->where('is_preview', true)->exists());
    }

    public function test_public_listing_and_show_pages_render_seeded_programs(): void
    {
        $this->seed(CatalogPublicProgramsSeeder::class);

        $this->get(route('courses.certificates', ['locale' => 'ar']))
            ->assertRedirect();

        $this->get(route('courses.index', [
            'locale' => 'ar',
            'categories' => [CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES],
        ]))
            ->assertOk()
            ->assertSee('شهادة محترف إدارة المشاريع')
            ->assertSee('شهادة محترف الموارد البشرية')
            ->assertSee('شهادة أساسيات الأمن السيبراني')
            ->assertSee('شهادة احترافية')
            ->assertSee('diploma-list-card', false)
            ->assertSee('diploma-list-card__media--brand', false)
            ->assertSee('aou-logo.png', false)
            ->assertSee('الرسوم الدراسية')
            ->assertDontSee('atelier-program trainingCard', false)
            ->assertDontSee('سيتم نشر البرامج قريباً');

        $this->get(route('courses.index', [
            'locale' => 'ar',
            'categories' => [CatalogCourseService::CATEGORY_DIPLOMAS],
        ]))
            ->assertOk()
            ->assertSee('دبلوم إدارة الموارد البشرية')
            ->assertSee('دبلوم تقنية المعلومات')
            ->assertSee('دبلوم المحاسبة والمالية');

        $this->get(route('courses.show', [
            'locale' => 'ar',
            'course' => 'hr-management-diploma',
        ]))
            ->assertOk()
            ->assertSee('دبلوم إدارة الموارد البشرية')
            ->assertSee('الوصف العام')
            ->assertSee('الأهداف')
            ->assertSee('مركز التعلم المستمر')
            ->assertSee('course-show-tabbar', false)
            ->assertSee('listing-tab--with-scroll-controls', false)
            ->assertSee('course-sidebar-enroll--focus sticky-top', false)
            ->assertSee('برامج ذات صلة')
            ->assertSee('diploma-list-card', false)
            ->assertSee('عرض كل البرامج')
            ->assertDontSee('gigs-slider', false)
            ->assertDontSee('trainingCard gigs-grid', false);

        $this->get(route('courses.show', [
            'locale' => 'en',
            'course' => 'digital-marketing-professional-certificate',
        ]))
            ->assertOk()
            ->assertSee('Digital Marketing Professional Certificate')
            ->assertDontSee('شهادة محترف التسويق الرقمي');
    }
}
