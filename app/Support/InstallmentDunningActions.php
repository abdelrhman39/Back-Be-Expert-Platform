<?php

namespace App\Support;

class InstallmentDunningActions
{
    public const SEND_NOTIFICATION = 'send_notification';

    public const SUSPEND_LEARNING = 'suspend_learning';

    public const BLOCK_EXAMS = 'block_exams';

    public const LOCK_LOGIN = 'lock_login';

    public const MARK_DEFAULTED = 'mark_defaulted';

    public const PERMANENT_LOCK = 'permanent_lock';

    public const APPLY_LATE_FEE = 'apply_late_fee';

    /**
     * @return array<string, array{label: string, description: string, tone: string}>
     */
    public static function catalog(): array
    {
        return [
            self::SEND_NOTIFICATION => [
                'label' => 'إرسال إشعار / بريد',
                'description' => 'يرسل عنوان ونص الخطوة عبر البريد و/أو إشعار المنصة.',
                'tone' => 'info',
            ],
            self::SUSPEND_LEARNING => [
                'label' => 'إيقاف التعلم مؤقتاً',
                'description' => 'يمنع حضور الحصص والواجبات والمحتوى التعليمي حتى السداد (مع الإبقاء على صفحة الأقساط).',
                'tone' => 'warn',
            ],
            self::BLOCK_EXAMS => [
                'label' => 'منع الاختبارات',
                'description' => 'يمنع بدء أو متابعة الاختبارات فقط دون إيقاف كامل للتعلم (أو معه إن فُعّل إيقاف التعلم).',
                'tone' => 'warn',
            ],
            self::LOCK_LOGIN => [
                'label' => 'قفل تسجيل الدخول مؤقتاً',
                'description' => 'يمنع دخول الحساب للبوابة حتى سداد المتأخرات أو تدخل الأدمن.',
                'tone' => 'danger',
            ],
            self::MARK_DEFAULTED => [
                'label' => 'وسم العقد متعثراً',
                'description' => 'يغيّر حالة عقد التقسيط إلى defaulted للمتابعة المالية.',
                'tone' => 'danger',
            ],
            self::PERMANENT_LOCK => [
                'label' => 'قفل نهائي + تعثر',
                'description' => 'يقفل الدخول ويوسم العقد متعثراً ويضع علامة قفل نهائي (يُرفع تلقائياً عند تصفير المتأخرات أو يدوياً).',
                'tone' => 'danger',
            ],
            self::APPLY_LATE_FEE => [
                'label' => 'تطبيق رسوم تأخير',
                'description' => 'يحسب رسوم التأخير مرة واحدة وفق إعدادات الرسوم إن لم تُطبَّق مسبقاً.',
                'tone' => 'info',
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::catalog());
    }

    public static function label(string $key): string
    {
        return self::catalog()[$key]['label'] ?? $key;
    }
}
