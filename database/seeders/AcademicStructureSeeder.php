<?php

namespace Database\Seeders;

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $bba = AcademicProgram::query()->where('code', 'BBA-103')->first();
        $pmp = AcademicProgram::query()->where('code', 'PMP-202')->first();
        $osh = AcademicProgram::query()->where('code', 'OSH-101')->first();

        $batchUpdates = [
            '251010' => [
                'program_id' => $bba?->id,
                'name' => 'دفعة دبلوم المحاسبة العامة — مسار التحصيلي 2026/2027',
                'semester' => 'الفصل الأول للعام الدراسي 2026/2027',
                'semester_key' => '2026-f1',
                'start_date' => '2026-09-01',
                'end_date' => '2027-06-30',
                'students_count' => 172,
                'capacity' => 200,
                'study_mode' => 'morning',
                'coordinator' => 'د. نورة الحربي',
                'enrollment_open' => true,
                'status' => 'active',
                'notes' => 'دفعة صباحية — مسار التحصيلي العام.',
            ],
            '251009' => [
                'program_id' => $pmp?->id,
                'name' => 'دفعة دبلوم إدارة المشاريع الاحترافية — خريف 2025',
                'semester' => 'الفصل الأول للعام الدراسي 2025/2026',
                'semester_key' => '2025-f1',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
                'students_count' => 103,
                'capacity' => 120,
                'study_mode' => 'evening',
                'coordinator' => 'د. سعد العتيبي',
                'enrollment_open' => false,
                'status' => 'planned',
                'notes' => 'بانتظار اكتمال التسجيل.',
            ],
            '251008' => [
                'program_id' => $osh?->id,
                'name' => 'دفعة دبلوم الأمن والسلامة المهنية — 2025',
                'semester' => 'الفصل الأول للعام الدراسي 2025/2026',
                'semester_key' => '2025-f1',
                'start_date' => '2025-09-15',
                'end_date' => '2026-07-15',
                'students_count' => 36,
                'capacity' => 50,
                'study_mode' => 'morning',
                'coordinator' => 'م. فهد الشمري',
                'enrollment_open' => true,
                'status' => 'active',
            ],
        ];

        foreach ($batchUpdates as $code => $data) {
            AcademicBatch::query()->updateOrCreate(['code' => $code], $data);
        }

        $batch = AcademicBatch::query()->where('code', '251010')->first();
        $course = AcademicCourse::query()->where('code', '03101')->first();
        $level = AcademicLevel::query()->where('program_id', $pmp?->id)->where('name_ar', 'المستوى الأول')->first();

        if ($batch && $bba) {
            AcademicSection::query()->updateOrCreate(
                ['code' => '2410125101'],
                [
                    'batch_id' => $batch->id,
                    'program_id' => $bba->id,
                    'course_id' => $course?->id,
                    'level_id' => $level?->id,
                    'name' => 'شعبة محاسبة — صباحي 001',
                    'subtitle' => 'مبادئ المحاسبة',
                    'max_capacity' => 35,
                    'students_count' => 28,
                    'supervisor' => 'د. نورة الحربي',
                    'period' => 'morning',
                    'semester_key' => '2026-f1',
                    'semester' => 'الفصل الأول للعام الدراسي 2026/2027',
                    'status' => 'active',
                    'added_by' => 'مدير النظام',
                ]
            );

            AcademicSection::query()->updateOrCreate(
                ['code' => '2410125102'],
                [
                    'batch_id' => $batch->id,
                    'program_id' => $bba->id,
                    'name' => 'شعبة محاسبة — مسائي 002',
                    'subtitle' => 'محاسبة مالية',
                    'max_capacity' => 30,
                    'students_count' => 22,
                    'supervisor' => 'أ. سارة الدوسري',
                    'period' => 'evening',
                    'semester_key' => '2026-f1',
                    'semester' => 'الفصل الأول للعام الدراسي 2026/2027',
                    'status' => 'active',
                    'added_by' => 'مدير النظام',
                ]
            );
        }
    }
}
