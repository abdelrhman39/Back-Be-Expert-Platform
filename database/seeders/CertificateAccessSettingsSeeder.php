<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class CertificateAccessSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'certificate_student_portal_enabled', 'value' => '1', 'label_ar' => 'إظهار قسم شهاداتي للطالب'],
            ['key' => 'certificate_auto_issue_enabled', 'value' => '1', 'label_ar' => 'الإصدار التلقائي للشهادات'],
            ['key' => 'certificate_auto_issue_notifications_enabled', 'value' => '1', 'label_ar' => 'إشعار الطالب عند الإصدار التلقائي'],
            ['key' => 'certificate_default_visibility_mode', 'value' => 'after_graduation', 'label_ar' => 'شرط ظهور الشهادة الموحد'],
            ['key' => 'certificate_required_exam_type', 'value' => 'final', 'label_ar' => 'نوع الاختبار المطلوب لاجتياز الشهادة'],
            ['key' => 'certificate_student_downloads_enabled', 'value' => '1', 'label_ar' => 'السماح بتنزيل الشهادات'],
            ['key' => 'certificate_student_printing_enabled', 'value' => '1', 'label_ar' => 'السماح بطباعة الشهادات'],
            ['key' => 'certificate_student_details_enabled', 'value' => '1', 'label_ar' => 'عرض تفاصيل الشهادات'],
            ['key' => 'certificate_hide_revoked_from_students', 'value' => '0', 'label_ar' => 'إخفاء الشهادات الملغاة'],
            ['key' => 'certificate_require_integrity_for_download', 'value' => '1', 'label_ar' => 'اشتراط سلامة البصمة للتنزيل'],
            ['key' => 'certificate_require_active_for_download', 'value' => '1', 'label_ar' => 'اشتراط سريان الشهادة للتنزيل'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::query()->firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => 'certificates',
                    'label_ar' => $setting['label_ar'],
                    'type' => in_array($setting['key'], [
                        'certificate_default_visibility_mode',
                        'certificate_required_exam_type',
                    ], true) ? 'string' : 'boolean',
                ],
            );
        }
    }
}
