<?php

namespace Database\Seeders;

use App\Models\InstallmentDunningPolicy;
use App\Models\InstallmentDunningStep;
use App\Models\PlatformSetting;
use App\Support\InstallmentDunningActions;
use Illuminate\Database\Seeder;

class InstallmentDunningSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSetting::set('installment_dunning_enabled', '1', 'finance', 'تفعيل مسار تصعيد المتأخرات');
        PlatformSetting::set('installment_dunning_time', '09:00', 'finance', 'وقت تشغيل مسار التصعيد');

        $policy = InstallmentDunningPolicy::query()->firstOrCreate(
            ['is_default' => true],
            [
                'name' => 'مسار التصعيد الافتراضي',
                'description' => 'سلسلة تصعيد مرنة للأقساط المتأخرة: تذكير → تحذير → إنذار بالقفل → قفل مؤقت → إنذار نهائي → قفل نهائي. يمكن للأدمن تعديل أي خطوة أو إضافة خطوات جديدة.',
                'is_active' => true,
                'process_time' => '09:00',
            ]
        );

        if ($policy->steps()->exists()) {
            return;
        }

        $steps = [
            [
                'sort_order' => 1,
                'name' => 'طلب السداد',
                'admin_notes' => 'أول تواصل بعد التأخر — بدون قيود على الحساب.',
                'trigger_offset_days' => 1,
                'trigger_hour' => null,
                'actions' => [InstallmentDunningActions::SEND_NOTIFICATION],
                'email_subject' => 'تذكير بسداد قسط متأخر — {{installment_label}}',
                'email_body' => "مرحباً {{student_name}},\n\nنذكّرك بأن القسط {{installment_label}} بمبلغ {{amount}} ر.س قد تجاوز موعد الاستحقاق ({{due_date}}).\n\nيرجى السداد عبر الرابط التالي:\n{{pay_url}}\n\nرقم العقد: {{contract_no}}",
            ],
            [
                'sort_order' => 2,
                'name' => 'تحذير بإيقاف الحساب والاختبارات',
                'admin_notes' => 'تحذير صريح قبل تطبيق أي قيد.',
                'trigger_offset_days' => 3,
                'actions' => [InstallmentDunningActions::SEND_NOTIFICATION],
                'email_subject' => 'تحذير: قد يُوقف حسابك إن لم يتم السداد',
                'email_body' => "مرحباً {{student_name}},\n\nلم نستلم سداد القسط {{installment_label}} ({{amount}} ر.س) حتى الآن.\n\nإذا استمر التأخر فسيتم إيقاف حسابك ومنعك من حضور الاختبارات.\n\nسدّد الآن: {{pay_url}}",
            ],
            [
                'sort_order' => 3,
                'name' => 'إنذار بالقفل خلال يومين',
                'admin_notes' => 'إنذار زمني قبل القفل المؤقت.',
                'trigger_offset_days' => 5,
                'actions' => [InstallmentDunningActions::SEND_NOTIFICATION, InstallmentDunningActions::BLOCK_EXAMS],
                'email_subject' => 'إنذار: قفل الحساب خلال يومين إن لم يتم السداد',
                'email_body' => "مرحباً {{student_name}},\n\nتبقّى وقت قصير قبل قفل الحساب بسبب تأخر سداد {{installment_label}}.\nالمبلغ: {{amount}} ر.س — أيام التأخر: {{days_overdue}}.\n\nتم تعليق الاختبارات حالياً. سدّد فوراً لتجنب قفل الدخول:\n{{pay_url}}",
            ],
            [
                'sort_order' => 4,
                'name' => 'إنذار بالقفل اليوم',
                'admin_notes' => 'إنذار أخير في يوم تنفيذ القفل المؤقت.',
                'trigger_offset_days' => 7,
                'trigger_hour' => 8,
                'actions' => [InstallmentDunningActions::SEND_NOTIFICATION, InstallmentDunningActions::BLOCK_EXAMS],
                'email_subject' => 'إنذار أخير: سيتم قفل حسابك اليوم إن لم يتم السداد',
                'email_body' => "مرحباً {{student_name}},\n\nسيتم قفل حسابك اليوم إذا لم يتم سداد القسط {{installment_label}} بمبلغ {{amount}} ر.س.\n\nالرابط السريع للسداد:\n{{pay_url}}",
            ],
            [
                'sort_order' => 5,
                'name' => 'قفل مؤقت + مهلة 3 أيام',
                'admin_notes' => 'إيقاف التعلم وقفل الدخول مؤقتاً مع مهلة أخيرة قبل القفل النهائي.',
                'trigger_offset_days' => 7,
                'trigger_hour' => 18,
                'actions' => [
                    InstallmentDunningActions::SEND_NOTIFICATION,
                    InstallmentDunningActions::SUSPEND_LEARNING,
                    InstallmentDunningActions::BLOCK_EXAMS,
                    InstallmentDunningActions::LOCK_LOGIN,
                ],
                'email_subject' => 'تم قفل حسابك مؤقتاً بسبب عدم السداد',
                'email_body' => "مرحباً {{student_name}},\n\nتم قفل حسابك مؤقتاً وإيقاف التعلم والاختبارات بسبب عدم سداد {{installment_label}} ({{amount}} ر.س).\n\nلديك مهلة 3 أيام للسداد وإلا سيتم قفل الحساب بشكل نهائي.\n\nللسداد تواصل مع التحصيل أو استخدم:\n{{pay_url}}",
            ],
            [
                'sort_order' => 6,
                'name' => 'قفل نهائي',
                'admin_notes' => 'آخر خطوة: قفل نهائي ووسم العقد متعثراً.',
                'trigger_offset_days' => 10,
                'actions' => [
                    InstallmentDunningActions::SEND_NOTIFICATION,
                    InstallmentDunningActions::PERMANENT_LOCK,
                ],
                'email_subject' => 'تم قفل حسابك نهائياً بسبب تعثر السداد',
                'email_body' => "مرحباً {{student_name}},\n\nتم قفل حسابك بشكل نهائي ووسم عقد التقسيط {{contract_no}} كمتعثر بسبب استمرار عدم سداد {{installment_label}}.\n\nلرفع القفل بعد السداد يرجى التواصل مع الإدارة المالية.",
            ],
        ];

        foreach ($steps as $step) {
            InstallmentDunningStep::query()->create([
                'policy_id' => $policy->id,
                'sort_order' => $step['sort_order'],
                'name' => $step['name'],
                'admin_notes' => $step['admin_notes'],
                'enabled' => true,
                'trigger_offset_days' => $step['trigger_offset_days'],
                'trigger_hour' => $step['trigger_hour'] ?? null,
                'actions' => $step['actions'],
                'email_enabled' => true,
                'email_subject' => $step['email_subject'],
                'email_body' => $step['email_body'],
                'channels' => ['mail', 'database'],
            ]);
        }
    }
}
