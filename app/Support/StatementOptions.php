<?php

namespace App\Support;

class StatementOptions
{
    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            'enrollment' => 'إفادة التحاق',
            'graduation' => 'إفادة تخرج',
            'attendance' => 'إفادة قيد / حضور',
            'acceptance' => 'إفادة قبول',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'pending' => 'قيد المراجعة',
            'issued' => 'صادرة',
            'rejected' => 'مرفوضة',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return static::types()[$type] ?? $type;
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }
}
