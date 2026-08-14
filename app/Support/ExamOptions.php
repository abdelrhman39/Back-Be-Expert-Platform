<?php

namespace App\Support;

class ExamOptions
{
    /** @return array<string, string> */
    public static function questionTypes(): array
    {
        return [
            'single_choice' => 'اختيار من متعدد — إجابة واحدة',
            'multiple_choice' => 'اختيار من متعدد — عدة إجابات',
            'true_false' => 'صح أو خطأ',
            'short_text' => 'إجابة نصية قصيرة',
            'fill_blank' => 'إكمال الفراغات',
            'essay' => 'سؤال مقالي',
            'matching' => 'مطابقة',
            'ordering' => 'ترتيب',
            'numeric' => 'إجابة رقمية',
            'file_upload' => 'رفع ملف',
        ];
    }

    /** @return array<string, string> */
    public static function difficulties(): array
    {
        return [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
            'expert' => 'متقدم',
        ];
    }

    /** @return array<string, string> */
    public static function examTypes(): array
    {
        return [
            'quiz' => 'اختبار قصير',
            'exam' => 'اختبار',
            'midterm' => 'اختبار نصفي',
            'final' => 'اختبار نهائي',
            'practice' => 'تدريب غير مرصود',
            'placement' => 'اختبار تحديد مستوى',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'draft' => 'مسودة',
            'published' => 'منشور',
            'closed' => 'مغلق',
            'archived' => 'مؤرشف',
        ];
    }

    /** @return array<string, string> */
    public static function resultReleasePolicies(): array
    {
        return [
            'immediate' => 'فور التسليم',
            'after_grading' => 'بعد اكتمال التصحيح',
            'after_close' => 'بعد إغلاق الاختبار',
            'manual' => 'بقرار المدرب',
        ];
    }

    /** @return array<string, string> */
    public static function reviewPolicies(): array
    {
        return [
            'none' => 'عدم إتاحة مراجعة الإجابات',
            'score_only' => 'عرض الدرجة فقط',
            'answers' => 'عرض إجابات الطالب ودرجاتها',
            'correct_answers' => 'عرض التصحيح والإجابات الصحيحة والخاطئة',
        ];
    }

    /** @return array<string, string> */
    public static function attemptPolicies(): array
    {
        return [
            'single' => 'محاولة واحدة فقط',
            'limited' => 'عدد محدد من المحاولات',
            'unlimited' => 'محاولات غير محدودة',
        ];
    }

    /** @return array<string, string> */
    public static function gradeSelectionPolicies(): array
    {
        return [
            'highest' => 'اعتماد أعلى درجة',
            'latest' => 'اعتماد آخر محاولة',
        ];
    }

    /** @return array<string, string> */
    public static function languagePolicies(): array
    {
        return [
            'ar_only' => 'العربية إلزامية لجميع الطلاب',
            'en_only' => 'English mandatory for all students',
            'student_locale' => 'تلقائياً حسب لغة حساب الطالب',
            'student_choice' => 'يختار الطالب العربية أو English قبل البدء',
        ];
    }

    /** @return array<int, string> */
    public static function autoGradableTypes(): array
    {
        return [
            'single_choice',
            'multiple_choice',
            'true_false',
            'short_text',
            'fill_blank',
            'matching',
            'ordering',
            'numeric',
        ];
    }

    public static function questionTypeLabel(?string $type): string
    {
        return $type ? (static::questionTypes()[$type] ?? $type) : '—';
    }

    public static function statusLabel(?string $status): string
    {
        return $status ? (static::statuses()[$status] ?? $status) : '—';
    }
}
