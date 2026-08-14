<?php

namespace Database\Seeders;

use App\Models\AcademicStudent;
use App\Models\Certificate;
use Illuminate\Database\Seeder;

class CertificatesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $students = AcademicStudent::query()
            ->with(['user', 'batch.program'])
            ->where('academic_status', 'graduated')
            ->limit(5)
            ->get();

        foreach ($students as $student) {
            $code = 'BE-'.($student->academic_id ?: str_pad((string) $student->id, 6, '0', STR_PAD_LEFT));

            Certificate::query()->updateOrCreate(
                ['code' => $code],
                [
                    'user_id' => $student->user_id,
                    'academic_student_id' => $student->id,
                    'holder_name' => $student->name_ar,
                    'program_name' => $student->batch?->program?->name_ar ?? 'برنامج أكاديمي',
                    'issued_at' => $student->graduated_at?->toDateString() ?? now()->subMonths(2)->toDateString(),
                    'status' => 'active',
                ],
            );
        }
    }
}
