<?php

namespace Database\Seeders;

use App\Models\CatalogField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogFieldsSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            ['id' => 8, 'title_ar' => 'إدارة المشاريع', 'title_en' => 'Project Management'],
            ['id' => 10, 'title_ar' => 'الحاسب وتقنية المعلومات', 'title_en' => 'Computing & IT'],
            ['id' => 11, 'title_ar' => 'التسويق', 'title_en' => 'Marketing'],
            ['id' => 13, 'title_ar' => 'الموارد البشرية', 'title_en' => 'Human Resources'],
            ['id' => 14, 'title_ar' => 'المحاسبة والمالية', 'title_en' => 'Accounting & Finance'],
            ['id' => 16, 'title_ar' => 'السياحة والضيافة', 'title_en' => 'Tourism & Hospitality'],
        ];

        foreach ($fields as $index => $field) {
            CatalogField::query()->updateOrCreate(
                ['id' => $field['id']],
                [
                    'title_ar' => $field['title_ar'],
                    'title_en' => $field['title_en'],
                    'slug' => Str::slug($field['title_en']),
                    'icon' => CatalogField::defaultIconMap()[$field['id']] ?? null,
                    'sort_order' => $index + 1,
                    'sidebar_visible' => true,
                    'home_visible' => true,
                ],
            );
        }
    }
}
