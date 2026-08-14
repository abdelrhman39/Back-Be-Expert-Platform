<?php

namespace Database\Seeders;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicStudent;
use Illuminate\Database\Seeder;

class AcademicDemoSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'name_ar' => 'دبلوم إدارة المشاريع الاحترافية',
                'name_en' => 'Professional Project Management Diploma',
                'name_on_certificate' => 'Diploma in Professional Project Management',
                'code' => 'PMP-202',
                'symbol' => 'مشروع 111',
                'duration_months' => 12,
                'duration_label' => 'عام دراسي',
                'start_date' => '2024-09-01',
                'status' => 'inactive',
                'type' => 'certificate',
                'coordinator' => 'د. سعد العتيبي',
                'email' => 'pmp@domain.local',
                'city' => 'مقرن',
                'summary' => 'برنامج متخصص في إدارة المشاريع وفق أفضل الممارسات العالمية.',
                'skills' => ['تخطيط المشاريع', 'إدارة المخاطر', 'إدارة الفرق'],
                'study_status' => 'غير فعال — بانتظار تفعيل الدفعة القادمة',
            ],
            [
                'name_ar' => 'دبلوم الأمن والسلامة المهنية',
                'name_en' => 'Diploma in Occupational Safety',
                'name_on_certificate' => 'Diploma in Occupational Safety',
                'code' => 'OSH-101',
                'symbol' => 'سلامة 101',
                'duration_months' => 12,
                'duration_label' => '12 شهر',
                'start_date' => '2024-09-01',
                'status' => 'active',
                'type' => 'diploma',
                'coordinator' => 'م. فهد الشمري',
                'email' => 'osh@domain.local',
                'city' => 'مقرن',
                'summary' => 'برنامج تأهيلي شامل في الأمن والسلامة المهنية.',
                'skills' => ['تقييم المخاطر', 'السلامة المهنية'],
                'study_status' => 'فعال — دفعة 2024/2025',
            ],
            [
                'name_ar' => 'دبلوم المحاسبة العامة',
                'name_en' => 'General Accounting Diploma',
                'name_on_certificate' => 'Diploma in General Accounting',
                'code' => 'BBA-103',
                'symbol' => 'محاسبة 103',
                'duration_months' => 24,
                'duration_label' => 'عامان دراسيان',
                'start_date' => '2023-09-01',
                'status' => 'active',
                'type' => 'diploma',
                'coordinator' => 'د. نورة الحربي',
                'email' => 'bba@domain.local',
                'city' => 'مقرن',
                'summary' => 'دبلوم محاسبة شامل لمسار التحصيلي.',
                'skills' => ['محاسبة مالية', 'تحليل مالي'],
                'study_status' => 'فعال',
            ],
        ];

        foreach ($programs as $data) {
            AcademicProgram::query()->updateOrCreate(['code' => $data['code']], $data);
        }

        $pmp = AcademicProgram::query()->where('code', 'PMP-202')->first();
        $osh = AcademicProgram::query()->where('code', 'OSH-101')->first();
        $bba = AcademicProgram::query()->where('code', 'BBA-103')->first();

        $batches = [
            ['program_id' => $bba?->id, 'name' => 'دفعة دبلوم المحاسبة — 2026/2027', 'code' => '251010', 'semester' => '2026/2027 — الفصل الأول', 'students_count' => 172, 'status' => 'active'],
            ['program_id' => $pmp?->id, 'name' => 'دفعة إدارة المشاريع — خريف 2025', 'code' => '251009', 'semester' => '2025/2026 — الفصل الأول', 'students_count' => 103, 'status' => 'planned'],
            ['program_id' => $osh?->id, 'name' => 'دفعة الأمن والسلامة — 2025', 'code' => '251008', 'semester' => '2025/2026 — الفصل الأول', 'students_count' => 36, 'status' => 'active', 'enrollment_open' => true, 'tuition_amount' => 15000, 'installment_allowed' => true],
        ];

        foreach ($batches as $data) {
            AcademicBatch::query()->updateOrCreate(['code' => $data['code']], $data);
        }

        $batch = AcademicBatch::query()->where('code', '251010')->first();

        AcademicStudent::query()->updateOrCreate(
            ['academic_id' => '26102234'],
            [
                'batch_id' => $batch?->id,
                'name_ar' => 'عبدالله علي صالح القيسي',
                'name_en' => 'ABDULLAH ALI SALEH ALQUBAISI',
                'national_id' => '1088540792',
                'mobile' => '966546472008',
                'email' => 'abdullah2025@gmail.com',
                'gender' => 'ذكر',
                'city' => 'الرياض',
                'study_status' => 'مستمر دراسياً',
                'academic_status' => 'studying',
                'login_allowed' => true,
                'joined_at' => now()->subWeek(),
            ]
        );

        AcademicStudent::query()->updateOrCreate(
            ['academic_id' => '26101880'],
            [
                'batch_id' => $batch?->id,
                'name_ar' => 'بدر عيد سعود المطيري',
                'name_en' => 'BADR EID SAUD ALMUTAIRI',
                'national_id' => '1091234567',
                'mobile' => '966501234567',
                'email' => 'badr.mutairi@example.com',
                'gender' => 'ذكر',
                'city' => 'مقرن',
                'study_status' => 'مستمر دراسياً',
                'academic_status' => 'studying',
                'login_allowed' => true,
                'joined_at' => now()->subMonths(2),
            ]
        );

        AcademicStudent::query()->updateOrCreate(
            ['academic_id' => '26101501'],
            [
                'batch_id' => $batch?->id,
                'name_ar' => 'نورة سعد المالكي',
                'name_en' => 'NOURA SAAD ALMALKI',
                'national_id' => '1098765432',
                'mobile' => '966502223344',
                'email' => 'noura.malki@example.com',
                'gender' => 'أنثى',
                'city' => 'الرياض',
                'study_status' => 'بانتظار إكمال التسجيل',
                'academic_status' => 'pending',
                'login_allowed' => true,
                'joined_at' => now()->subDays(3),
            ]
        );

        AcademicStudent::query()->updateOrCreate(
            ['academic_id' => '26100999'],
            [
                'batch_id' => AcademicBatch::query()->where('code', '251008')->value('id'),
                'name_ar' => 'ريم عبدالله الشهري',
                'name_en' => 'REEM ABDULLAH ALSHEHRI',
                'national_id' => '1101122334',
                'mobile' => '966503334455',
                'email' => 'reem.shehri@example.com',
                'gender' => 'أنثى',
                'city' => 'جدة',
                'study_status' => 'خريجة',
                'academic_status' => 'graduated',
                'graduated_at' => now()->subMonths(6),
                'login_allowed' => false,
                'joined_at' => now()->subYears(2),
            ]
        );
    }
}
