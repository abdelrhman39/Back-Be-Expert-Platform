<?php

namespace Database\Seeders;

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicSchedule;
use App\Models\AcademicSection;
use App\Models\AcademicStaff;
use Illuminate\Database\Seeder;

class AcademicSchedulesSeeder extends Seeder
{
    public function run(): void
    {
        $bba = AcademicProgram::query()->where('code', 'BBA-103')->first();
        if (! $bba) {
            return;
        }

        $level1 = AcademicLevel::query()->updateOrCreate(
            ['program_id' => $bba->id, 'name_ar' => 'المستوى الأول'],
            ['sort_order' => 1, 'status' => 'active']
        );

        $batch = AcademicBatch::query()->where('code', '251010')->first();
        if (! $batch) {
            return;
        }

        $course = AcademicCourse::query()->firstOrCreate(
            ['code' => '02101'],
            [
                'program_id' => $bba->id,
                'level_id' => $level1->id,
                'name_ar' => 'مبادئ المحاسبة المالية',
                'name_en' => 'Financial Accounting Principles',
                'symbol_ar' => '101 محاسبة',
                'credit_hours' => 3,
                'status' => 'active',
                'added_by' => 'مدير النظام',
            ]
        );

        $sections = [
            [
                'code' => '2410125101',
                'name' => 'شعبة محاسبة — صباحي 001',
                'subtitle' => 'مبادئ المحاسبة المالية',
                'period' => 'morning',
                'supervisor' => 'د. نورة الحربي',
                'schedule' => ['day' => 'sun', 'start' => '08:00', 'end' => '10:00'],
            ],
            [
                'code' => '2410125102',
                'name' => 'شعبة محاسبة — صباحي 002',
                'subtitle' => 'محاسبة التكاليف',
                'period' => 'morning',
                'supervisor' => 'أ. سارة العنزي',
                'schedule' => ['day' => 'tue', 'start' => '10:00', 'end' => '12:00'],
            ],
            [
                'code' => '2410125103',
                'name' => 'شعبة محاسبة — صباحي 003',
                'subtitle' => 'مهارات الحاسب الآلي',
                'period' => 'morning',
                'supervisor' => 'م. فهد الشمري',
                'schedule' => ['day' => 'mon', 'start' => '09:00', 'end' => '11:00'],
            ],
        ];

        $staffByName = AcademicStaff::query()->pluck('id', 'name_ar');

        foreach ($sections as $data) {
            $section = AcademicSection::query()->updateOrCreate(
                ['code' => $data['code']],
                [
                    'batch_id' => $batch->id,
                    'program_id' => $bba->id,
                    'course_id' => $course?->id,
                    'level_id' => $level1->id,
                    'name' => $data['name'],
                    'subtitle' => $data['subtitle'],
                    'max_capacity' => 35,
                    'supervisor' => $data['supervisor'],
                    'period' => $data['period'],
                    'semester_key' => '2026-f1',
                    'semester' => 'الفصل الأول للعام الدراسي 2026/2027',
                    'status' => 'active',
                    'added_by' => 'مدير النظام',
                ]
            );

            $staffId = $staffByName[$data['supervisor']] ?? null;

            AcademicSchedule::query()->updateOrCreate(
                ['section_id' => $section->id],
                [
                    'batch_id' => $batch->id,
                    'level_id' => $level1->id,
                    'semester_key' => '2026-f1',
                    'period' => $data['period'],
                    'staff_id' => $staffId,
                    'trainer_name' => $data['supervisor'],
                    'day_of_week' => $data['schedule']['day'],
                    'time_start' => $data['schedule']['start'],
                    'time_end' => $data['schedule']['end'],
                ]
            );
        }
    }
}
