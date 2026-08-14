<?php

namespace App\Support;

class AcademicRegistrationOptions
{
    /** @return array<string, string> */
    public static function nationalities(): array
    {
        return [
            'sa' => 'سعودي / سعودية',
            'other' => 'غير سعودي',
        ];
    }

    /** @return array<string, string> */
    public static function cities(): array
    {
        return [
            'riyadh' => 'الرياض',
            'jeddah' => 'جدة',
            'dammam' => 'الدمام',
            'hail' => 'الامير مقرن',
            'other' => 'أخرى',
        ];
    }

    /** @return array<string, string> */
    public static function employmentStatuses(): array
    {
        return [
            'employed' => 'موظف',
            'unemployed' => 'غير موظف',
        ];
    }

    /** @return array<string, string> */
    public static function studyPeriods(): array
    {
        return [
            'evening' => 'مسائي',
        ];
    }

    public static function cityLabel(?string $key): string
    {
        return $key ? (static::cities()[$key] ?? $key) : '—';
    }

    public static function nationalityLabel(?string $key): string
    {
        return $key ? (static::nationalities()[$key] ?? $key) : '—';
    }
}
