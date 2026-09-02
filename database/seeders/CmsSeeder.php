<?php

namespace Database\Seeders;

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsPageTranslation;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPolicyPages();
        $this->seedCorePages();
        $this->seedMenus();
    }

    protected function seedCorePages(): void
    {
        $pages = [
            ['type' => 'home', 'slug' => 'home', 'title' => 'الصفحة الرئيسية', 'legacy' => 'home'],
            ['type' => 'about', 'slug' => 'about', 'title' => 'عن المنصة', 'legacy' => 'about'],
            ['type' => 'contact', 'slug' => 'contact', 'title' => 'تواصل معنا', 'legacy' => 'contact'],
        ];

        foreach ($pages as $i => $pageData) {
            $page = CmsPage::query()->updateOrCreate(
                ['type' => $pageData['type']],
                [
                    'status' => 'draft',
                    'sort_order' => $i + 1,
                    'show_in_footer' => false,
                    'legacy_slug' => $pageData['legacy'],
                ],
            );

            CmsPageTranslation::query()->updateOrCreate(
                ['page_id' => $page->id, 'locale' => 'ar'],
                [
                    'title' => $pageData['title'],
                    'slug' => $pageData['slug'],
                    'body' => '<p>محتوى «'.$pageData['title'].'» — عدّله من لوحة التحكم → صفحات الموقع، ثم انشر الصفحة لاستبدال النسخة الثابتة.</p>',
                ],
            );
        }
    }

    protected function seedPolicyPages(): void
    {
        $policies = [
            ['slug' => 'terms-and-conditions', 'title' => 'الشروط والأحكام', 'title_en' => 'Terms and Conditions', 'legacy' => 'terms-and-conditions'],
            ['slug' => 'privacy-policy', 'title' => 'سياسة الخصوصية', 'title_en' => 'Privacy Policy', 'legacy' => 'privacy-policy'],
            ['slug' => 'e-learning-policy', 'title' => 'سياسة التدريب الإلكتروني', 'title_en' => 'E-learning Policy', 'legacy' => 'e-learning-policy'],
            ['slug' => 'attendance-and-learning-policy', 'title' => 'سياسات الحضور والتعلّم', 'title_en' => 'Attendance and Learning Policies', 'legacy' => 'attendance-and-learning-policy'],
            ['slug' => 'e-learning-integrity-policy', 'title' => 'سياسات نزاهة التعلم', 'title_en' => 'Learning Integrity Policy', 'legacy' => 'e-learning-integrity-policy'],
            ['slug' => 'roles-and-responsibilities-document', 'title' => 'وثيقة الأدوار والمسؤوليات', 'title_en' => 'Roles and Responsibilities', 'legacy' => 'roles-and-responsibilities-document'],
            ['slug' => 'supervisory-staff-of-the-training-environment', 'title' => 'الكادر الإشرافي', 'title_en' => 'Supervisory Staff', 'legacy' => 'supervisory-staff-of-the-training-environment'],
            ['slug' => 'technical-and-educational-support-policy', 'title' => 'سياسة الدعم الفني', 'title_en' => 'Technical Support Policy', 'legacy' => 'technical-and-educational-support-policy'],
        ];

        foreach ($policies as $i => $policy) {
            $page = CmsPage::query()->updateOrCreate(
                ['legacy_slug' => $policy['legacy']],
                [
                    'type' => 'policy',
                    'status' => 'published',
                    'sort_order' => $i + 1,
                    'show_in_footer' => true,
                    'published_at' => now(),
                ],
            );

            CmsPageTranslation::query()->updateOrCreate(
                ['page_id' => $page->id, 'locale' => 'ar'],
                [
                    'title' => $policy['title'],
                    'slug' => $policy['slug'],
                    'body' => '<p>محتوى «'.$policy['title'].'» — يمكن تعديله من لوحة التحكم → صفحات الموقع.</p>',
                ],
            );

            CmsPageTranslation::query()->updateOrCreate(
                ['page_id' => $page->id, 'locale' => 'en'],
                [
                    'title' => $policy['title_en'],
                    'slug' => $policy['slug'],
                    'body' => '<p>Content for “'.$policy['title_en'].'” — edit it from the admin panel → Site pages.</p>',
                ],
            );
        }
    }

    protected function seedMenus(): void
    {
        $header = CmsMenu::query()->updateOrCreate(
            ['key' => 'header_main'],
            ['label_ar' => 'القائمة الرئيسية', 'label_en' => 'Main menu', 'is_active' => true],
        );

        CmsMenuItem::query()->where('menu_id', $header->id)->delete();

        $this->item($header->id, 10, 'الرئيسية', 'route', 'home', labelEn: 'Home');
        $this->item($header->id, 20, 'عن المنصة', 'route', 'about', labelEn: 'About the platform');
        $programs = $this->item($header->id, 30, 'البرامج التدريبية', 'route', 'courses.index', labelEn: 'Training programs');
        $this->item($header->id, 31, 'الشهادات الاحترافية', 'route', 'courses.certificates', parentId: $programs->id, labelEn: 'Professional certificates');
        $this->item($header->id, 32, 'الدبلومات', 'route', 'courses.diplomas', parentId: $programs->id, labelEn: 'Diplomas');
        $this->item($header->id, 33, 'الزمالات المهنية', 'route', 'fellowships.index', parentId: $programs->id, labelEn: 'Professional fellowships');
        $registerMenu = $this->item($header->id, 40, 'التقديم والتسجيل', 'none', labelEn: 'Apply and register');
        $this->item($header->id, 41, 'التسجيل الأكاديمي', 'route', 'register', parentId: $registerMenu->id, labelEn: 'Academic registration');
        $this->item($header->id, 42, 'طلب عميل (فرد)', 'url', url: '/ar/apply/client', parentId: $registerMenu->id, labelEn: 'Individual client request');
        $this->item($header->id, 43, 'طلب شركة', 'url', url: '/ar/apply/company', parentId: $registerMenu->id, labelEn: 'Organization request');
        $this->item($header->id, 44, 'طلب مدرب', 'url', url: '/ar/apply/instructor', parentId: $registerMenu->id, labelEn: 'Instructor application');
        $this->item($header->id, 45, 'التدريب التعاوني', 'url', url: '/ar/apply/cooperative', parentId: $registerMenu->id, labelEn: 'Cooperative training');
        $this->item($header->id, 50, 'تواصل معنا', 'route', 'contact', labelEn: 'Contact us');

        $footerPrograms = CmsMenu::query()->updateOrCreate(
            ['key' => 'footer_programs'],
            ['label_ar' => 'فوتر البرامج', 'is_active' => true],
        );
        CmsMenuItem::query()->where('menu_id', $footerPrograms->id)->delete();
        $this->item($footerPrograms->id, 10, 'الشهادات الاحترافية', 'route', 'courses.certificates', labelEn: 'Professional certificates');
        $this->item($footerPrograms->id, 20, 'الدبلومات', 'route', 'courses.diplomas', labelEn: 'Diplomas');
        $this->item($footerPrograms->id, 30, 'الزمالات المهنية', 'route', 'fellowships.index', labelEn: 'Professional fellowships');

        $footerPolicies = CmsMenu::query()->updateOrCreate(
            ['key' => 'footer_policies'],
            ['label_ar' => 'فوتر السياسات', 'is_active' => true],
        );
        CmsMenuItem::query()->where('menu_id', $footerPolicies->id)->delete();

        $pages = CmsPage::query()
            ->where('show_in_footer', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        foreach ($pages as $i => $page) {
            $this->item(
                $footerPolicies->id,
                ($i + 1) * 10,
                $page->translations->firstWhere('locale', 'ar')?->title ?? 'صفحة',
                'page',
                pageId: $page->id,
                labelEn: $page->translations->firstWhere('locale', 'en')?->title,
            );
        }
    }

    protected function item(
        int $menuId,
        int $sort,
        string $labelAr,
        string $linkType,
        ?string $routeName = null,
        ?string $url = null,
        ?int $parentId = null,
        ?int $pageId = null,
        ?string $labelEn = null,
    ): CmsMenuItem {
        return CmsMenuItem::query()->create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'sort_order' => $sort,
            'label_ar' => $labelAr,
            'label_en' => $labelEn,
            'link_type' => $linkType,
            'route_name' => $routeName,
            'url' => $url,
            'page_id' => $pageId,
            'is_active' => true,
        ]);
    }
}
