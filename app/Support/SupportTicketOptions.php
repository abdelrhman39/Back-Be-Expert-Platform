<?php

namespace App\Support;

class SupportTicketOptions
{
    /** @return array<string, string> */
    public static function categories(): array
    {
        return [
            'tech' => 'تقني',
            'account' => 'حساب مستخدم',
            'course' => 'مقرر أو محتوى',
            'payment' => 'دفع وفواتير',
            'general' => 'استفسار عام',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'open' => 'مفتوحة',
            'in_progress' => 'قيد المعالجة',
            'waiting_customer' => 'بانتظار رد العميل',
            'resolved' => 'تم الحل',
            'closed' => 'مغلقة',
        ];
    }

    public static function categoryLabel(string $category): string
    {
        return static::categories()[$category] ?? $category;
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'open' => 'portal-status-pill--pending',
            'in_progress' => 'portal-status-pill--default',
            'waiting_customer' => 'portal-status-pill--pending',
            'resolved' => 'portal-status-pill--paid',
            'closed' => 'portal-status-pill--cancelled',
            default => 'portal-status-pill--default',
        };
    }

    public static function adminBadgeClass(string $status): string
    {
        return match ($status) {
            'open' => 'admin-badge--warn',
            'in_progress' => 'admin-badge--info',
            'waiting_customer' => 'admin-badge--warn',
            'resolved' => 'admin-badge--success',
            'closed' => 'admin-badge--danger',
            default => '',
        };
    }
}
