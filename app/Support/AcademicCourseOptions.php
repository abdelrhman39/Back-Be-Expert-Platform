<?php

namespace App\Support;

class AcademicCourseOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'فعال',
            'inactive' => 'غير فعال',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }
}
