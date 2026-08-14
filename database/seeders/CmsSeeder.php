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
            ['slug' => 'terms-and-conditions', 'title' => 'الشروط والأحكام', 'legacy' => 'terms-and-conditions'],
            ['slug' => 'privacy-policy', 'title' => 'سياسة الخصوصية', 'legacy' => 'privacy-policy'],
            ['slug' => 'e-learning-policy', 'title' => 'سياسة التدريب الإلكتروني', 'legacy' => 'e-learning-policy'],
            ['slug' => 'attendance-and-learning-policy', 'title' => 'سياسات الحضور والتعلّم', 'legacy' => 'attendance-and-learning-policy'],
            ['slug' => 'e-learning-integrity-policy', 'title' => 'سياسات نزاهة التعلم', 'legacy' => 'e-learning-integrity-policy'],
            ['slug' => 'roles-and-responsibilities-document', 'title' => 'وثيقة الأدوار والمسؤوليات', 'legacy' => 'roles-and-responsibilities-document'],
            ['slug' => 'supervisory-staff-of-the-training-environment', 'title' => 'الكادر الإشرافي', 'legacy' => 'supervisory-staff-of-the-training-environment'],
            ['slug' => 'technical-and-educational-support-policy', 'title' => 'سياسة الدعم الفني', 'legacy' => 'technical-and-educational-support-policy'],
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
        }
    }

    protected function seedMenus(): void
    {
        $header = CmsMenu::query()->updateOrCreate(
            ['key' => 'header_main'],
            ['label_ar' => 'القائمة الرئيسية', 'label_en' => 'Main menu', 'is_active' => true],
        );

        CmsMenuItem::query()->where('menu_id', $header->id)->delete();

        $this->item($header->id, 10, 'الرئيسية', 'route', 'home');
        $this->item($header->id, 20, 'عن المنصة', 'route', 'about');
        $programs = $this->item($header->id, 30, 'البرامج التدريبية', 'none');
        $this->item($header->id, 31, 'الشهادات الاحترافية', 'url', url: '/ar#section-certificates', parentId: $programs->id);
        $this->item($header->id, 32, 'الدبلومات', 'url', url: '/ar#section-diplomas', parentId: $programs->id);
        $this->item($header->id, 33, 'الزمالات المهنية', 'url', url: '/ar#section-fellowships', parentId: $programs->id);
        $this->item($header->id, 34, 'برنامج مهارات', 'url', url: '/ar#section-mahara', parentId: $programs->id);
        $registerMenu = $this->item($header->id, 40, 'تسجيل الطلبات', 'none');
        $this->item($header->id, 41, 'التسجيل الأكاديمي', 'route', 'register', parentId: $registerMenu->id);
        $this->item($header->id, 42, 'طلب عميل (فرد)', 'url', url: '/ar/apply/client', parentId: $registerMenu->id);
        $this->item($header->id, 43, 'طلب شركة', 'url', url: '/ar/apply/company', parentId: $registerMenu->id);
        $this->item($header->id, 44, 'طلب مدرب', 'url', url: '/ar/apply/instructor', parentId: $registerMenu->id);
        $this->item($header->id, 45, 'التدريب التعاوني', 'url', url: '/ar/apply/cooperative', parentId: $registerMenu->id);
        $this->item($header->id, 46, 'برنامج وعد — موظف', 'url', url: '/ar/apply/employee', parentId: $registerMenu->id);
        $this->item($header->id, 47, 'برنامج وعد — باحث', 'url', url: '/ar/apply/job_seeker', parentId: $registerMenu->id);
        $this->item($header->id, 50, 'تواصل معنا', 'route', 'contact');

        $footerPrograms = CmsMenu::query()->updateOrCreate(
            ['key' => 'footer_programs'],
            ['label_ar' => 'فوتر البرامج', 'is_active' => true],
        );
        CmsMenuItem::query()->where('menu_id', $footerPrograms->id)->delete();
        $this->item($footerPrograms->id, 10, 'الشهادات الاحترافية', 'url', url: '/ar#section-certificates');
        $this->item($footerPrograms->id, 20, 'الدبلومات', 'url', url: '/ar#section-diplomas');
        $this->item($footerPrograms->id, 30, 'الزمالات المهنية', 'url', url: '/ar#section-fellowships');
        $this->item($footerPrograms->id, 40, 'برنامج مهارات', 'url', url: '/ar#section-mahara');

        $footerPolicies = CmsMenu::query()->updateOrCreate(
            ['key' => 'footer_policies'],
            ['label_ar' => 'فوتر السياسات', 'is_active' => true],
        );
        CmsMenuItem::query()->where('menu_id', $footerPolicies->id)->delete();

        $pages = CmsPage::query()->where('show_in_footer', true)->orderBy('sort_order')->get();

        foreach ($pages as $i => $page) {
            $this->item(
                $footerPolicies->id,
                ($i + 1) * 10,
                $page->translate('ar')?->title ?? 'صفحة',
                'page',
                pageId: $page->id,
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
    ): CmsMenuItem {
        return CmsMenuItem::query()->create([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'sort_order' => $sort,
            'label_ar' => $labelAr,
            'link_type' => $linkType,
            'route_name' => $routeName,
            'url' => $url,
            'page_id' => $pageId,
            'is_active' => true,
        ]);
    }
}
