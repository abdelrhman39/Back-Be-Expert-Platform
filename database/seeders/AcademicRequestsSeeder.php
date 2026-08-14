<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\AcademicRequest;
use App\Models\AcademicStudent;
use App\Support\AcademicRequestOptions;
use Illuminate\Database\Seeder;

class AcademicRequestsSeeder extends Seeder
{
    public function run(): void
    {
        if (AcademicRequest::query()->exists()) {
            return;
        }

        $students = AcademicStudent::query()->with('batch.program')->limit(40)->get();
        $programs = AcademicProgram::query()->orderBy('name_ar')->get();

        if ($students->isEmpty() || $programs->isEmpty()) {
            return;
        }

        $semesters = AcademicRequestOptions::semesters();
        $changeReasons = [
            'رغبة في التخصص الأنسب لسوق العمل',
            'تعارض الجدول الدراسي مع العمل',
            'تغيير مسار مهني بعد استشارة أكاديمية',
        ];
        $excuseReasons = [
            'مشاكل في تأمين دخول المحاضرات',
            'ظروف صحية تمنع الاستمرار في الفصل',
            'التزامات عمل لا تتفق مع الجدول الدراسي',
            'ظروف عائلية طارئة',
        ];
        $deferralReasons = [
            'ظروف صحية مؤقتة',
            'سفر خارج المملكة',
            'التزامات عائلية',
        ];

        $seed = 0;

        foreach ($students->take(15) as $i => $student) {
            $program = $student->batch?->program ?? $programs->random();
            $semester = $semesters[$i % count($semesters)];

            AcademicRequest::query()->create([
                'request_no' => AcademicRequestOptions::generateRequestNo($seed++),
                'type' => 'withdrawal',
                'student_id' => $student->id,
                'student_name' => $student->name_ar,
                'student_national_id' => $student->national_id,
                'program_id' => $program?->id,
                'program_name' => $program?->name_ar,
                'status' => $i % 5 === 0 ? 'approved' : 'processing',
                'review_status' => $i % 5 === 0 ? 'reviewed' : 'pending',
                'reason' => null,
                'payload' => ['payment_method' => 'دفع إلكتروني'],
                'submitted_at' => now()->subDays($i + 1),
            ]);
        }

        foreach ($students->take(25) as $i => $student) {
            $current = $student->batch?->program ?? $programs[$i % $programs->count()];
            $next = $programs[($i + 1) % $programs->count()];
            $approved = $i % 7 === 0;

            AcademicRequest::query()->create([
                'request_no' => $i === 0 ? '1779589387102157' : AcademicRequestOptions::generateRequestNo($seed++),
                'type' => 'program_change',
                'student_id' => $student->id,
                'student_name' => $student->name_ar,
                'student_national_id' => $student->national_id,
                'program_id' => $current?->id,
                'program_name' => $current?->name_ar,
                'status' => $approved ? 'approved' : 'processing',
                'review_status' => $approved ? 'reviewed' : 'pending',
                'reason' => $changeReasons[$i % count($changeReasons)],
                'payload' => [
                    'current_program' => $current?->name_ar,
                    'current_program_full' => ($current?->name_ar ?? '').' (دبلوم متوسط مهني)',
                    'current_duration' => $current?->duration_label ?? 'عام دراسي',
                    'new_program' => $next->name_ar,
                    'new_program_full' => $next->name_ar.' (دبلوم متوسط مهني)',
                    'new_duration' => $next->duration_label ?? 'عامان دراسيان',
                    'new_program_id' => $next->id,
                ],
                'submitted_at' => now()->subDays($i + 2),
            ]);
        }

        foreach ($students->take(20) as $i => $student) {
            $program = $student->batch?->program ?? $programs->random();
            $semester = $semesters[$i % count($semesters)];
            $reviewed = $i % 3 === 0;

            AcademicRequest::query()->create([
                'request_no' => $i === 0 ? '177913/17798993' : AcademicRequestOptions::generateRequestNo($seed++),
                'type' => 'semester_excuse',
                'student_id' => $student->id,
                'student_name' => $student->name_ar,
                'student_national_id' => $student->national_id,
                'program_id' => $program?->id,
                'program_name' => $program?->name_ar,
                'semester_key' => $semester['key'],
                'semester_label' => $semester['label'],
                'status' => 'pending',
                'review_status' => $reviewed ? 'reviewed' : 'pending',
                'reason' => $excuseReasons[$i % count($excuseReasons)],
                'payload' => [
                    'program_full' => ($program?->name_ar ?? '').' (دبلوم متوسط مهني)',
                    'added_by' => $student->name_ar,
                ],
                'submitted_at' => now()->subDays($i + 3),
            ]);
        }

        foreach ($students->take(12) as $i => $student) {
            $program = $student->batch?->program ?? $programs->random();
            $semester = $semesters[$i % count($semesters)];

            AcademicRequest::query()->create([
                'request_no' => AcademicRequestOptions::generateRequestNo($seed++),
                'type' => 'deferral',
                'student_id' => $student->id,
                'student_name' => $student->name_ar,
                'student_national_id' => $student->national_id,
                'program_id' => $program?->id,
                'program_name' => $program?->name_ar,
                'semester_key' => $semester['key'],
                'semester_label' => $semester['label'],
                'status' => $i % 4 === 0 ? 'processing' : 'pending',
                'review_status' => $i % 4 === 0 ? 'reviewed' : 'pending',
                'reason' => $deferralReasons[$i % count($deferralReasons)],
                'payload' => [
                    'target_semester' => $semesters[($i + 1) % count($semesters)]['label'],
                ],
                'submitted_at' => now()->subDays($i + 4),
            ]);
        }
    }
}
