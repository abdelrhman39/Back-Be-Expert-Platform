<?php

namespace App\Support;

use App\Models\Order;

class OrderOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'pending_payment' => 'بانتظار الدفع',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغي',
            'refunded' => 'مسترد',
        ];
    }

    /** @return array<string, string> */
    public static function deliveryTypes(): array
    {
        return [
            'online' => 'عن بعد',
            'onsite' => 'حضوري',
            'recorded' => 'مسجّل',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function statusLabelForOrder(Order $order): string
    {
        if ($order->isAwaitingBankTransfer()) {
            return 'بانتظار تأكيد التحويل';
        }

        return static::statusLabel($order->status);
    }

    public static function deliveryLabel(string $type): string
    {
        return static::deliveryTypes()[$type] ?? $type;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'admin-badge--success',
            'pending_payment' => 'admin-badge--warn',
            'refunded' => 'admin-badge--info',
            default => 'admin-badge--danger',
        };
    }

    public static function canManageStatus(string $status): bool
    {
        return ! in_array($status, ['refunded'], true);
    }
}
