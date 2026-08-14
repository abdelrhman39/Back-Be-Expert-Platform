<?php

namespace Database\Seeders;

use App\Models\AcademicBatch;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use Illuminate\Database\Seeder;

class SectionStudentsSyncSeeder extends Seeder
{
    public function run(): void
    {
        AcademicBatch::query()
            ->with(['sections' => fn ($q) => $q->orderBy('code')])
            ->each(function (AcademicBatch $batch) {
                $sections = $batch->sections;

                if ($sections->isEmpty()) {
                    return;
                }

                $students = AcademicStudent::query()
                    ->where('batch_id', $batch->id)
                    ->orderBy('academic_id')
                    ->get();

                if ($students->isEmpty()) {
                    return;
                }

                foreach ($students as $index => $student) {
                    $section = $sections[$index % $sections->count()];

                    $student->update(['section_id' => $section->id]);
                }

                $sections->each->refreshStudentsCount();
            });
    }
}
