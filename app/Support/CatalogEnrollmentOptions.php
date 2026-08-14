<?php

namespace App\Support;

class CatalogEnrollmentOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'نشط',
            'pending' => 'قيد التفعيل',
            'completed' => 'مكتمل',
            'suspended' => 'موقوف',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'active' => 'portal-enrollment-badge--active',
            'completed' => 'portal-enrollment-badge--completed',
            'pending' => 'portal-enrollment-badge--pending',
            default => 'portal-enrollment-badge--default',
        };
    }

    public static function deliveryLabel(string $type): string
    {
        return OrderOptions::deliveryLabel($type);
    }
}
