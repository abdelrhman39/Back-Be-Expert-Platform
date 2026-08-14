<?php

namespace Database\Seeders;

use App\Models\AcademicBatch;
use App\Models\AcademicStudent;
use App\Support\AcademicStudentOptions;
use Illuminate\Database\Seeder;

class BatchStudentsSyncSeeder extends Seeder
{
    public function run(): void
    {
        $batch = AcademicBatch::query()->where('code', '251010')->first();
        if (! $batch) {
            return;
        }

        $students = [
            ['عمر خالد العتيبي', 'OMAR KHALID ALOTAIBI', '26103001', 'studying'],
            ['سارة محمد الدوسري', 'SARA MOHAMMED ALDOSARI', '26103002', 'studying'],
            ['فهد عبدالرحمن القحطاني', 'FAHD ABDULRAHMAN ALQAHTANI', '26103003', 'studying'],
            ['هند سعد الغامدي', 'HIND SAAD ALGHAMDI', '26103004', 'pending'],
            ['محمد ناصر الحربي', 'MOHAMMED NASSER ALHARBI', '26103005', 'studying'],
            ['ريم فيصل الزهراني', 'REEM FAISAL ALZAHRANI', '26103006', 'studying'],
            ['خالد سليمان المطيري', 'KHALID SULEIMAN ALMUTAIRI', '26103007', 'studying'],
            ['نوف عبدالله الشهري', 'NOUF ABDULLAH ALSHEHRI', '26103008', 'pending'],
            ['يوسف أحمد السبيعي', 'YOUSEF AHMED ALSUBAIE', '26103009', 'studying'],
            ['لمى تركي العنزي', 'LAMA TURKI ALANAZI', '26103010', 'studying'],
            ['عبدالعزيز فهد البقمي', 'ABDULAZIZ FAHD ALBAQMI', '26103011', 'studying'],
            ['مها سعد العساف', 'MAHA SAAD ALASSAF', '26103012', 'studying'],
            ['سلمان راشد الراشد', 'SALMAN RASHID ALRASHID', '26103013', 'studying'],
            ['جواهر نايف الشمري', 'JAWAHIR NAIF ALSHAMRI', '26103014', 'studying'],
            ['تركي بندر السديري', 'TURKI BANDAR ALSUDAIRI', '26103015', 'studying'],
            ['أمل فيصل الخثعمي', 'AMAL FAISAL ALKHATHAMI', '26103016', 'studying'],
            ['ماجد سعود العنبر', 'MAJED SAUD ALANBAR', '26103017', 'studying'],
        ];

        $cities = ['الرياض', 'مقرن', 'جدة', 'الدمام', 'تبوك'];

        foreach ($students as $index => [$nameAr, $nameEn, $academicId, $status]) {
            AcademicStudent::query()->updateOrCreate(
                ['academic_id' => $academicId],
                [
                    'batch_id' => $batch->id,
                    'name_ar' => $nameAr,
                    'name_en' => $nameEn,
                    'national_id' => '10'.str_pad((string) (900000000 + $index), 9, '0', STR_PAD_LEFT),
                    'mobile' => '9665'.str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                    'email' => 'student'.($index + 1).'@demo.local',
                    'gender' => $index % 2 === 0 ? 'ذكر' : 'أنثى',
                    'city' => $cities[$index % count($cities)],
                    'study_status' => AcademicStudentOptions::academicStatusLabel($status),
                    'academic_status' => $status,
                    'login_allowed' => true,
                    'joined_at' => now()->subDays(30 - $index),
                ]
            );
        }

        AcademicBatch::query()->each(fn (AcademicBatch $b) => $b->refreshStudentsCount());
    }
}
