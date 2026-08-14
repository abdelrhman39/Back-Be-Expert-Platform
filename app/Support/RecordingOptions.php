<?php

namespace App\Support;

class RecordingOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'processing' => 'قيد المعالجة',
            'available' => 'متاح (لم يُنشر)',
            'published' => 'منشور للطلاب',
            'hidden' => 'مخفي',
            'expired' => 'منتهي الصلاحية',
            'failed' => 'فشل المزامنة',
        ];
    }

    /** @return array<string, string> */
    public static function publishModes(): array
    {
        return [
            'manual' => 'نشر يدوي (المدرب/الأدمن)',
            'auto_delayed' => 'نشر تلقائي بعد تأخير',
        ];
    }

    /** @return array<string, string> */
    public static function accessPolicies(): array
    {
        return [
            'enrolled_only' => 'كل طلاب الشعبة',
            'attended_only' => 'الحاضرون فقط',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'published' => 'admin-badge--success',
            'available' => 'admin-badge--info',
            'processing' => 'admin-badge--warn',
            'hidden', 'expired' => 'admin-badge--muted',
            'failed' => 'admin-badge--danger',
            default => 'admin-badge--muted',
        };
    }
}
