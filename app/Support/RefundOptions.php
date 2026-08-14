<?php

namespace App\Support;

class RefundOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'pending' => 'قيد المراجعة',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            'processed' => 'تم الاسترداد',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'processed' => 'admin-badge--success',
            'approved' => 'admin-badge--info',
            'pending' => 'admin-badge--warn',
            default => 'admin-badge--danger',
        };
    }

    public static function portalStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'processed' => 'portal-badge--success',
            'approved' => 'portal-badge--info',
            'pending' => 'portal-badge--warn',
            default => 'portal-badge--danger',
        };
    }
}
