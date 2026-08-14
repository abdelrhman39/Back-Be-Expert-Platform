<?php

namespace Database\Seeders;

use App\Models\AcademicStudent;
use Illuminate\Database\Seeder;

class GraduationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $students = AcademicStudent::query()->orderBy('id')->limit(5)->get();

        if ($students->count() < 3) {
            return;
        }

        $students[0]?->update([
            'academic_status' => 'graduated',
            'study_status' => 'خريج',
            'graduated_at' => now()->subMonths(2),
        ]);

        $students[1]?->update([
            'academic_status' => 'eligible',
            'study_status' => 'مؤهل للتخرج',
        ]);

        $students[2]?->update([
            'academic_status' => 'expected',
            'study_status' => 'متوقع التخرج',
        ]);
    }
}
