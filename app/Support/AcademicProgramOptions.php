<?php

namespace App\Support;

class AcademicProgramOptions
{
    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            'diploma' => 'دبلوم',
            'certificate' => 'شهادة مهنية',
            'fellowship' => 'زمالة',
            'maharat' => 'برنامج مهارات',
            'workshop' => 'ورشة عمل',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'فعال',
            'inactive' => 'غير فعال',
            'draft' => 'مسودة',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return static::types()[$type] ?? $type;
    }

    /**
     * High-level student enrollment tracks for admin filtering.
     *
     * @return array<string, array{label: string, hint: string}>
     */
    public static function studentTracks(): array
    {
        return [
            'diploma' => [
                'label' => 'الدبلومات',
                'hint' => 'طلاب مسجّلون في برامج من نوع دبلوم',
            ],
            'certificate' => [
                'label' => 'الشهادات الاحترافية',
                'hint' => 'طلاب مسجّلون في برامج الشهادات المهنية / الاحترافية',
            ],
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    /** @return array<int, int> */
    public static function durationMonthsOptions(): array
    {
        return [3, 6, 12, 18, 24];
    }
}
