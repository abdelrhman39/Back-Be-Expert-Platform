<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class InstallmentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'installment_reminders_enabled', 'value' => '1', 'group' => 'finance', 'label_ar' => 'تفعيل تذكيرات الأقساط'],
            ['key' => 'installment_reminder_days', 'value' => '7,3,1', 'group' => 'finance', 'label_ar' => 'تذكير قبل الاستحقاق (أيام)'],
            ['key' => 'installment_reminder_time', 'value' => '08:00', 'group' => 'finance', 'label_ar' => 'وقت إرسال التذكيرات اليومي'],
            ['key' => 'installment_suspension_enabled', 'value' => '1', 'group' => 'finance', 'label_ar' => 'تفعيل إيقاف الالتحاق عند التأخر'],
            ['key' => 'installment_grace_days', 'value' => '7', 'group' => 'finance', 'label_ar' => 'أيام السماح بعد الاستحقاق'],
            ['key' => 'installment_suspend_after_days', 'value' => '14', 'group' => 'finance', 'label_ar' => 'إيقاف الالتحاق بعد (يوم من الاستحقاق)'],
            ['key' => 'installment_overdue_time', 'value' => '09:00', 'group' => 'finance', 'label_ar' => 'وقت معالجة المتأخرات اليومي'],
            ['key' => 'installment_checkout_enabled', 'value' => '1', 'group' => 'finance', 'label_ar' => 'تقسيط المنصة في صفحة الدفع'],
            ['key' => 'installment_academic_registration_enabled', 'value' => '1', 'group' => 'finance', 'label_ar' => 'تقسيط التسجيل الأكاديمي'],
            ['key' => 'installment_requires_signature', 'value' => '1', 'group' => 'finance', 'label_ar' => 'يتطلب توقيع الطالب الإلكتروني'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group'], 'label_ar' => $setting['label_ar']],
            );
        }
    }
}
