<?php

namespace Database\Seeders;

use App\Models\AcademicStaff;
use Illuminate\Database\Seeder;

class AcademicStaffSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            ['name_ar' => 'د. سعد العتيبي', 'role' => 'coordinator', 'specialty' => 'إدارة المشاريع', 'gender' => 'ذكر', 'courses_count' => 4, 'hours_per_week' => 12, 'compensation_total' => 18500],
            ['name_ar' => 'م. فهد الشمري', 'role' => 'instructor', 'specialty' => 'الأمن والسلامة', 'gender' => 'ذكر', 'courses_count' => 3, 'hours_per_week' => 10, 'compensation_total' => 14200],
            ['name_ar' => 'د. نورة الحربي', 'role' => 'coordinator', 'specialty' => 'المحاسبة', 'gender' => 'أنثى', 'courses_count' => 5, 'hours_per_week' => 14, 'compensation_total' => 21000],
            ['name_ar' => 'أ. منى القحطاني', 'role' => 'instructor', 'specialty' => 'الموارد البشرية', 'gender' => 'أنثى', 'courses_count' => 2, 'hours_per_week' => 8, 'compensation_total' => 9800],
            ['name_ar' => 'د. خالد المطيري', 'role' => 'instructor', 'specialty' => 'أمن المعلومات', 'gender' => 'ذكر', 'courses_count' => 3, 'hours_per_week' => 9, 'compensation_total' => 12600],
            ['name_ar' => 'أ. سارة العنزي', 'role' => 'instructor', 'specialty' => 'إدارة الأعمال', 'gender' => 'أنثى', 'courses_count' => 2, 'hours_per_week' => 7, 'compensation_total' => 8400],
            ['name_ar' => 'م. عبدالرحمن الدوسري', 'role' => 'instructor', 'specialty' => 'الذكاء الاصطناعي', 'gender' => 'ذكر', 'courses_count' => 2, 'hours_per_week' => 6, 'compensation_total' => 11200],
            ['name_ar' => 'د. هيفاء الزهراني', 'role' => 'reviewer', 'specialty' => 'ضمان الجودة', 'gender' => 'أنثى', 'courses_count' => 1, 'hours_per_week' => 4, 'compensation_total' => 6500],
            ['name_ar' => 'أ. يوسف الغامدي', 'role' => 'instructor', 'specialty' => 'التسويق', 'gender' => 'ذكر', 'courses_count' => 2, 'hours_per_week' => 5, 'compensation_total' => 7200],
            ['name_ar' => 'د. لمى السبيعي', 'role' => 'instructor', 'specialty' => 'القانون', 'gender' => 'أنثى', 'courses_count' => 2, 'hours_per_week' => 6, 'compensation_total' => 8900],
            ['name_ar' => 'م. تركي الحربي', 'role' => 'assistant', 'specialty' => 'الدعم الأكاديمي', 'gender' => 'ذكر', 'courses_count' => 1, 'hours_per_week' => 8, 'compensation_total' => 5400],
        ];

        foreach ($staff as $row) {
            AcademicStaff::query()->updateOrCreate(
                ['name_ar' => $row['name_ar']],
                array_merge($row, ['status' => 'active']),
            );
        }
    }
}
