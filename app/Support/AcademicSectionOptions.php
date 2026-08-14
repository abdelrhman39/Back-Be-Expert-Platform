<?php

namespace App\Support;

class AcademicSectionOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'فعال',
            'inactive' => 'غير فعال',
        ];
    }

    /** @return array<string, string> */
    public static function periods(): array
    {
        return [
            'morning' => 'صباحي',
            'evening' => 'مسائي',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function periodLabel(?string $period): string
    {
        return $period ? (static::periods()[$period] ?? $period) : '—';
    }
}
