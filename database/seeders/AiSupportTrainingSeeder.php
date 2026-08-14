<?php

namespace Database\Seeders;

use App\Models\AiSupportConversation;
use App\Models\AiSupportMessage;
use Illuminate\Database\Seeder;

/**
 * Seeds high-quality Q&A pairs approved for later OpenAI fine-tuning export.
 * Run: php artisan db:seed --class=AiSupportTrainingSeeder
 * Export: php artisan ai-support:export-training --approved-only
 */
class AiSupportTrainingSeeder extends Seeder
{
    public function run(): void
    {
        $pairs = $this->pairs();

        $conversation = AiSupportConversation::query()->create([
            'locale' => 'ar',
            'audience' => 'visitor',
            'status' => 'closed',
            'page_url' => '/ar/support/faq',
            'meta' => ['seed' => 'AiSupportTrainingSeeder'],
        ]);

        foreach ($pairs as $pair) {
            AiSupportMessage::query()->create([
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $pair['q'],
            ]);

            AiSupportMessage::query()->create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $pair['a'],
                'training_approved' => true,
                'feedback' => 1,
                'knowledge_refs' => $pair['refs'] ?? [],
                'model' => 'seeded-expert',
            ]);

            $conversation->increment('message_count', 2);
        }

        $conversation->update(['last_message_at' => now()]);
    }

    /** @return list<array{q: string, a: string, refs?: list<string>}> */
    private function pairs(): array
    {
        return [
            [
                'q' => 'كيف أسجّل دخول للمنصة؟',
                'a' => "يمكنك تسجيل الدخول من صفحة `/ar/login` بطريقتين:\n1) رقم الهوية الوطنية + كلمة المرور\n2) رقم الجوال (+966) + كلمة المرور\n\nملاحظة: الدخول اليومي يكون بكلمة المرور دائماً، ورمز OTP يُستخدم فقط لاستعادة كلمة المرور عبر الجوال.",
                'refs' => ['auth-account'],
            ],
            [
                'q' => 'نسيت كلمة المرور ماذا أفعل؟',
                'a' => "من صفحة `/ar/password/reset` اختر إحدى الطريقتين:\n- البريد الإلكتروني: يصلك رابط إعادة تعيين.\n- الجوال: يصلك رمز OTP ثم تعيّن كلمة مرور جديدة.\n\nبعد التعيين استخدم كلمة المرور الجديدة للدخول بالهوية أو الجوال.",
                'refs' => ['auth-account'],
            ],
            [
                'q' => 'أين أجد دوراتي بعد الدفع؟',
                'a' => "بعد نجاح الدفع:\n1) ادخل لوحة التحكم `/ar/profile`\n2) افتح قائمة التعلم `/ar/learning-list`\n3) اضغط «متابعة التعلم» للوصول للمحتوى.\n\nإذا لم تظهر الدورة بعد دقائق، راجع طلبات الشراء `/ar/my-orders` ثم افتح تذكرة دعم مع رقم الطلب.",
                'refs' => ['courses-learning', 'payments-checkout'],
            ],
            [
                'q' => 'ما طرق الدفع المتاحة؟',
                'a' => "الطرق الشائعة على المنصة:\n- الدفع الإلكتروني عبر Moyasar (بطاقات/مدى)\n- التحويل البنكي عند تفعيله من الإدارة\n- Tabby أو Tamara عند توفرهما للمنتج\n- التقسيط الداخلي للدبلومات المؤهلة عبر صفحة أقساطي `/ar/installments`\n\nاختر الطريقة المناسبة في صفحة إتمام الشراء.",
                'refs' => ['payments-checkout'],
            ],
            [
                'q' => 'كيف أفتح تذكرة دعم؟',
                'a' => "1) اذهب إلى `/ar/support/ticket/new`\n2) عبّئ الاسم والبريد والجوال وموضوع المشكلة وحدد التصنيف المناسب\n3) احفظ رمز التذكرة (Reference)\n4) للمتابعة لاحقاً استخدم `/ar/support/ticket/search` بالرمز + البريد\n\nالتصنيفات: تقني، حساب، مقرر، دفع، استفسار عام.",
                'refs' => ['support-tickets'],
            ],
            [
                'q' => 'كيف أتحقق من شهادة؟',
                'a' => "افتح صفحة التحقق `/ar/certificate-verify` وأدخل رمز الشهادة. يمكن لأي زائر التحقق دون تسجيل دخول.\nللطلاب: الشهادات الصادرة تظهر أيضاً في `/ar/certificates` إن كان القسم مفعّلاً.",
                'refs' => ['certificates'],
            ],
            [
                'q' => 'كيف أحضر المحاضرة المباشرة؟',
                'a' => "1) سجّل الدخول\n2) افتح صفحة حصصي `/ar/sessions`\n3) عند اقتراب الموعد سيظهر رابط الانضمام (Zoom أو Teams حسب الإعداد)\n4) ادخل بالرابط واضبط الميكروفون/الكاميرا حسب تعليمات المدرب\n\nإذا لم يظهر الرابط: قد تكون خارج نافذة الانضمام أو الحصة غير مرتبطة بحسابك — افتح تذكرة «تقني» مع اسم الحصة ووقتها.",
                'refs' => ['sessions-live'],
            ],
            [
                'q' => 'تأخرت عن سداد قسط ماذا يحدث؟',
                'a' => "من صفحة أقساطي `/ar/installments` راجع القسط المستحق وتاريخه. بعد مهلة السماح قد يُعلَّق الوصول للمحتوى حتى السداد حسب سياسة المنصة.\nادفع القسط المتأخر إلكترونياً، وإن استمر التعليق بعد الدفع الناجح افتح تذكرة فئة «دفع وفواتير» مع رقم العقد/الطلب.",
                'refs' => ['installments'],
            ],
            [
                'q' => 'كيف أقدّم طلب تأجيل أو انسحاب؟',
                'a' => "من حساب الطالب افتح طلباتي الأكاديمية `/ar/user-requests` واختر نوع الطلب (تأجيل/انسحاب/تغيير برنامج/عذر فصل…) ثم أرسل الطلب.\nالموافقة إدارية وقد تستغرق وقتاً — تابع الحالة من نفس الصفحة. المساعد لا يستطيع اعتماد الطلب نيابة عن الإدارة.",
                'refs' => ['academic-requests'],
            ],
            [
                'q' => 'أنا زائر أريد تصفح الدورات كيف أبدأ؟',
                'a' => "ابدأ من `/ar/courses` لاستعراض الكتالوج، ثم افتح صفحة الدورة لقراءة التفاصيل والأسعار.\nللشراء ستحتاج حساباً عبر `/ar/register` ثم إتمام الدفع من صفحة الشراء.\nللاستفسارات العامة استخدم المساعد أو `/ar/support/faq` أو تذكرة دعم.",
                'refs' => ['visitor-guide', 'platform-overview'],
            ],
            [
                'q' => 'How do I reset my password?',
                'a' => "Go to `/en/password/reset`.\n- Email: receive a reset link.\n- Mobile: receive an OTP, then set a new password.\nDaily login always uses the password (national ID or phone). OTP is only for password recovery.",
                'refs' => ['auth-account'],
            ],
            [
                'q' => 'Where are my courses after purchase?',
                'a' => "After successful payment, open your dashboard `/en/profile`, then Learning List `/en/learning-list`, and click Continue learning.\nIf the course is missing, check `/en/my-orders` and open a support ticket with your order reference.",
                'refs' => ['courses-learning'],
            ],
        ];
    }
}
