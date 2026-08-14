<?php

namespace Database\Seeders;

use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use Illuminate\Database\Seeder;

class AcademicCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $pmp = AcademicProgram::query()->where('code', 'PMP-202')->first();
        if (! $pmp) {
            return;
        }

        $level1 = AcademicLevel::query()->updateOrCreate(
            ['program_id' => $pmp->id, 'name_ar' => 'المستوى الأول'],
            ['sort_order' => 1, 'status' => 'active']
        );
        $level2 = AcademicLevel::query()->updateOrCreate(
            ['program_id' => $pmp->id, 'name_ar' => 'المستوى الثاني'],
            ['sort_order' => 2, 'status' => 'active']
        );

        $courses = [
            ['code' => '03101', 'name_ar' => 'أسس إدارة المشاريع', 'name_en' => 'Project Management Foundations', 'symbol_ar' => '111 مشروع', 'symbol_en' => '111 PM', 'credit_hours' => 3, 'level_id' => $level1->id, 'target_group' => 'لم يتم التحديد بعد', 'image_url' => 'new-platform/assets/ba5c2cc1-5c62-4b77-8607-bead454d224e.png'],
            ['code' => '03102', 'name_ar' => 'تخطيط المشاريع', 'name_en' => 'Project Planning', 'symbol_ar' => '112 مشروع', 'symbol_en' => '112 PM', 'credit_hours' => 3, 'level_id' => $level1->id, 'target_group' => 'لم يتم التحديد بعد'],
            ['code' => '03103', 'name_ar' => 'إدارة المخاطر', 'name_en' => 'Risk Management', 'symbol_ar' => '113 مشروع', 'symbol_en' => '113 PM', 'credit_hours' => 2, 'level_id' => $level1->id, 'target_group' => 'الموظفون على رأس العمل'],
            ['code' => '03104', 'name_ar' => 'إدارة التكلفة', 'name_en' => 'Cost Management', 'symbol_ar' => '114 مشروع', 'symbol_en' => '114 PM', 'credit_hours' => 2, 'level_id' => $level1->id, 'target_group' => 'لم يتم التحديد بعد'],
            ['code' => '03105', 'name_ar' => 'إدارة الجودة', 'name_en' => 'Quality Management', 'symbol_ar' => '115 مشروع', 'symbol_en' => '115 PM', 'credit_hours' => 2, 'level_id' => $level1->id, 'target_group' => 'لم يتم التحديد بعد'],
            ['code' => '03201', 'name_ar' => 'إدارة الفريق', 'name_en' => 'Team Management', 'symbol_ar' => '121 مشروع', 'symbol_en' => '121 PM', 'credit_hours' => 3, 'level_id' => $level2->id, 'target_group' => 'قادة الفرق'],
            ['code' => '03202', 'name_ar' => 'أدوات MS Project', 'name_en' => 'MS Project Tools', 'symbol_ar' => '122 مشروع', 'symbol_en' => '122 PM', 'credit_hours' => 4, 'level_id' => $level2->id, 'target_group' => 'لم يتم التحديد بعد'],
            ['code' => '03203', 'name_ar' => 'دراسة الحالة النهائية', 'name_en' => 'Final Case Study', 'symbol_ar' => '123 مشروع', 'symbol_en' => '123 PM', 'credit_hours' => 4, 'level_id' => $level2->id, 'target_group' => 'متدربو الدفعة الحالية'],
        ];

        foreach ($courses as $data) {
            AcademicCourse::query()->updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, [
                    'program_id' => $pmp->id,
                    'status' => 'active',
                    'added_by' => 'مدير النظام',
                ])
            );
        }

        $osh = AcademicProgram::query()->where('code', 'OSH-101')->first();
        if ($osh) {
            $oshLevel = AcademicLevel::query()->updateOrCreate(
                ['program_id' => $osh->id, 'name_ar' => 'المستوى الأول'],
                ['sort_order' => 1, 'status' => 'active']
            );

            AcademicCourse::query()->updateOrCreate(
                ['code' => '02101'],
                [
                    'program_id' => $osh->id,
                    'level_id' => $oshLevel->id,
                    'name_ar' => 'مبادئ السلامة المهنية',
                    'name_en' => 'Occupational Safety Principles',
                    'symbol_ar' => '101 سلامة',
                    'symbol_en' => '101 OSH',
                    'credit_hours' => 3,
                    'status' => 'active',
                    'target_group' => 'العاملون في البيئات الصناعية',
                    'added_by' => 'مدير النظام',
                ]
            );
        }

        $pmp->update([
            'media_url' => $pmp->media_url ?: 'https://domain.local/programs/pmp',
            'attachments' => $pmp->attachments ?: [
                ['name' => 'دليل البرنامج.pdf', 'url' => null],
                ['name' => 'خطة المقررات.pdf', 'url' => null],
            ],
        ]);
    }
}
