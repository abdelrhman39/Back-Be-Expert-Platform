<?php

namespace Database\Seeders;

use App\Models\Fellowship;
use Illuminate\Database\Seeder;

class FellowshipSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'slug' => 'human-resources-fellowship',
                'legacy_slug' => 'human-resources-fellowship',
                'title_ar' => 'زمالة الموارد البشرية',
                'title_en' => 'Human Resources Fellowship',
                'description_ar' => 'برنامج زمالة متخصص في الموارد البشرية وإدارة الكفاءات.',
            ],
            [
                'slug' => 'marketing-corporate-communications-and-sales-fellowship',
                'legacy_slug' => 'marketing,-corporate-communications-and-sales-fellowship',
                'title_ar' => 'زمالة التسويق والاتصال المؤسسي والمبيعات',
                'title_en' => 'Marketing, Corporate Communications and Sales Fellowship',
                'description_ar' => 'زمالة مهنية في التسويق والاتصال المؤسسي واستراتيجيات المبيعات.',
            ],
            [
                'slug' => 'public-spokesperson-and-corporate-communications-fellowship',
                'legacy_slug' => 'public-spokesperson-and-corporate-communications-fellowship',
                'title_ar' => 'زمالة المتحدث الرسمي والاتصال المؤسسي',
                'title_en' => 'Public Spokesperson and Corporate Communications Fellowship',
                'description_ar' => 'برنامج زمالة في الإعلام المؤسسي والتحدث أمام الجمهور.',
            ],
            [
                'slug' => 'artificial-intelligence-for-business-fellowship',
                'legacy_slug' => 'artificial-intelligence-for-business-fellowship',
                'title_ar' => 'زمالة الذكاء الاصطناعي للأعمال',
                'title_en' => 'Artificial Intelligence for Business Fellowship',
                'description_ar' => 'زمالة تطبيقية في الذكاء الاصطناعي وتحول الأعمال الرقمي.',
            ],
            [
                'slug' => 'advanced-fellowship-in-entrepreneurship',
                'legacy_slug' => 'advanced-fellowship-in-entrepreneurship',
                'title_ar' => 'الزمالة المتقدمة في ريادة الأعمال',
                'title_en' => 'Advanced Fellowship in Entrepreneurship',
                'description_ar' => 'زمالة متقدمة لرواد الأعمال وبناء المشاريع الناشئة.',
            ],
        ];

        foreach ($items as $i => $item) {
            Fellowship::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title_ar' => $item['title_ar'],
                    'title_en' => $item['title_en'],
                    'description_ar' => $item['description_ar'],
                    'description_en' => $item['title_en'],
                    'status' => 'open',
                    'application_open' => true,
                    'legacy_slug' => $item['legacy_slug'],
                    'sort_order' => ($i + 1) * 10,
                ],
            );
        }
    }
}
