<?php

namespace Database\Seeders;

use App\Models\NotificationRule;
use App\Support\NotificationTypes;
use Illuminate\Database\Seeder;

class NotificationRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'type' => NotificationTypes::LECTURE_REMINDER,
                'name_ar' => 'تذكير قبل 24 ساعة',
                'name_en' => '24h reminder',
                'trigger_kind' => 'before_event',
                'offset_minutes' => 1440,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::LECTURE_REMINDER,
                'name_ar' => 'تذكير قبل ساعتين',
                'name_en' => '2h reminder',
                'trigger_kind' => 'before_event',
                'offset_minutes' => 120,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::LECTURE_REMINDER,
                'name_ar' => 'تذكير قبل 30 دقيقة',
                'name_en' => '30min reminder',
                'trigger_kind' => 'before_event',
                'offset_minutes' => 30,
                'channels' => ['database'],
            ],
            [
                'type' => NotificationTypes::LECTURE_LIVE_NOW,
                'name_ar' => 'المحاضرة جارية الآن',
                'name_en' => 'Live now',
                'trigger_kind' => 'live_window',
                'offset_minutes' => 0,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::ASSIGNMENT_PUBLISHED,
                'name_ar' => 'واجب جديد',
                'name_en' => 'Assignment published',
                'trigger_kind' => 'immediate',
                'offset_minutes' => null,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::RECORDING_PUBLISHED,
                'name_ar' => 'تسجيل محاضرة',
                'name_en' => 'Recording published',
                'trigger_kind' => 'immediate',
                'offset_minutes' => null,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::CERTIFICATE_ISSUED,
                'name_ar' => 'إصدار شهادة جديدة',
                'name_en' => 'Certificate issued',
                'trigger_kind' => 'immediate',
                'offset_minutes' => null,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::ACADEMIC_REQUEST_STATUS,
                'name_ar' => 'تحديث طلب أكاديمي',
                'name_en' => 'Academic request status',
                'trigger_kind' => 'immediate',
                'offset_minutes' => null,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::INSTALLMENT_DUE_SOON,
                'name_ar' => 'تذكير بقسط مستحق',
                'name_en' => 'Installment due soon',
                'trigger_kind' => 'before_event',
                'offset_minutes' => 10080,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::INSTALLMENT_OVERDUE,
                'name_ar' => 'قسط متأخر',
                'name_en' => 'Installment overdue',
                'trigger_kind' => 'immediate',
                'offset_minutes' => null,
                'channels' => ['database', 'mail'],
            ],
            [
                'type' => NotificationTypes::ENROLLMENT_SUSPENDED,
                'name_ar' => 'إيقاف الالتحاق — متأخرات',
                'name_en' => 'Enrollment suspended',
                'trigger_kind' => 'immediate',
                'offset_minutes' => null,
                'channels' => ['database', 'mail'],
            ],
        ];

        foreach ($rules as $rule) {
            NotificationRule::query()->updateOrCreate(
                [
                    'type' => $rule['type'],
                    'name_ar' => $rule['name_ar'],
                ],
                array_merge($rule, ['is_enabled' => true, 'audience' => 'enrolled_students']),
            );
        }
    }
}
