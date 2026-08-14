<?php

namespace Database\Seeders;

use App\Models\RegistrationApplication;
use App\Support\RegistrationApplicationOptions;
use Illuminate\Database\Seeder;

class RegistrationApplicationsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (RegistrationApplication::query()->exists()) {
            return;
        }

        $samples = [
            [
                'type' => 'client',
                'applicant_name' => 'أحمد محمد العتيبي',
                'applicant_email' => 'client-demo@domain.test',
                'applicant_phone' => '+966501234567',
                'payload' => [
                    'education_level' => 'bachelor',
                    'item_type' => 'course',
                    'interested_programs' => 'ورشة عمل الابتكار والإبداع في العمل المؤسسي',
                ],
            ],
            [
                'type' => 'company',
                'applicant_name' => 'شركة أفق للتقنية',
                'applicant_email' => 'hr@afaq-demo.test',
                'applicant_phone' => '+966551112233',
                'payload' => [
                    'activity' => 'تقنية المعلومات',
                    'responsible_name' => 'سارة الحربي',
                    'n_employee' => '25',
                    'message' => 'نرغب بتدريب موظفينا على الشهادات الاحترافية.',
                ],
            ],
            [
                'type' => 'instructor',
                'applicant_name' => 'خالد عبدالله',
                'applicant_email' => 'instructor-candidate@domain.test',
                'applicant_phone' => '+966509876543',
                'status' => 'under_review',
                'payload' => [
                    'job_title' => 'أستاذ مساعد',
                    'nationality' => 'سعودي',
                    'specialization' => 'إدارة أعمال',
                ],
            ],
        ];

        foreach ($samples as $i => $sample) {
            RegistrationApplication::query()->create([
                'application_no' => 'APP-DEMO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'type' => $sample['type'],
                'status' => $sample['status'] ?? 'pending',
                'applicant_name' => $sample['applicant_name'],
                'applicant_email' => $sample['applicant_email'],
                'applicant_phone' => $sample['applicant_phone'],
                'approved_role' => RegistrationApplicationOptions::approvedRoleForType($sample['type']),
                'payload' => $sample['payload'],
                'submitted_at' => now()->subDays(3 - $i),
            ]);
        }
    }
}
