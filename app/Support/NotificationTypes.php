<?php

namespace App\Support;

class NotificationTypes
{
    public const LECTURE_REMINDER = 'lecture.reminder';

    public const LECTURE_LIVE_NOW = 'lecture.live_now';

    public const ASSIGNMENT_PUBLISHED = 'assignment.published';

    public const EXAM_PUBLISHED = 'exam.published';

    public const EXAM_SUBMITTED = 'exam.submitted';

    public const EXAM_RESULT_RELEASED = 'exam.result_released';

    public const CERTIFICATE_ISSUED = 'certificate.issued';

    public const RECORDING_PUBLISHED = 'recording.published';

    public const ACADEMIC_REQUEST_STATUS = 'academic_request.status';

    public const INSTALLMENT_DUE_SOON = 'installment.due_soon';

    public const INSTALLMENT_PAYMENT_LINK = 'installment.payment_link';

    public const INSTALLMENT_PAID = 'installment.paid';

    public const INSTALLMENT_COMPLETED = 'installment.completed';

    public const INSTALLMENT_OVERDUE = 'installment.overdue';

    public const INSTALLMENT_DUNNING = 'installment.dunning_step';

    public const ENROLLMENT_SUSPENDED = 'enrollment.suspended_installment';

    public const SYSTEM_ANNOUNCEMENT = 'system.announcement';

    public const APPLICATION_NEW = 'application.new';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::LECTURE_REMINDER => 'تذكير قبل المحاضرة',
            self::LECTURE_LIVE_NOW => 'المحاضرة جارية الآن',
            self::ASSIGNMENT_PUBLISHED => 'واجب جديد',
            self::EXAM_PUBLISHED => 'اختبار جديد',
            self::EXAM_SUBMITTED => 'تأكيد تسليم اختبار',
            self::EXAM_RESULT_RELEASED => 'نتيجة اختبار',
            self::CERTIFICATE_ISSUED => 'إصدار شهادة',
            self::RECORDING_PUBLISHED => 'تسجيل محاضرة',
            self::ACADEMIC_REQUEST_STATUS => 'تحديث طلب أكاديمي',
            self::INSTALLMENT_DUE_SOON => 'تذكير بقسط مستحق',
            self::INSTALLMENT_PAYMENT_LINK => 'رابط سداد قسط',
            self::INSTALLMENT_PAID => 'تأكيد سداد قسط',
            self::INSTALLMENT_COMPLETED => 'اكتمال عقد التقسيط',
            self::INSTALLMENT_OVERDUE => 'قسط متأخر',
            self::INSTALLMENT_DUNNING => 'تصعيد متأخرات الأقساط',
            self::ENROLLMENT_SUSPENDED => 'إيقاف الالتحاق — متأخرات',
            self::SYSTEM_ANNOUNCEMENT => 'إعلان من الإدارة',
            self::APPLICATION_NEW => 'طلب تسجيل جديد',
        ];
    }

    /** @return array<string, string> */
    public static function audienceLabels(): array
    {
        return [
            'all' => 'جميع المستخدمين',
            'students' => 'الطلاب / المتدربون',
            'instructors' => 'المدربون',
            'admins' => 'الإداريون',
            'staff' => 'الإداريون والمدربون',
        ];
    }
}
