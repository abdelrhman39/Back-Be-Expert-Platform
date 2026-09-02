<?php

namespace App\Support;

class AcademicRegistrationOptions
{
    /** @return array<string, string> */
    public static function nationalities(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ['sa' => 'Saudi', 'other' => 'Non-Saudi']
            : ['sa' => 'سعودي / سعودية', 'other' => 'غير سعودي'];
    }

    /** @return array<string, string> */
    public static function cities(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? [
                'riyadh' => 'Riyadh',
                'jeddah' => 'Jeddah',
                'dammam' => 'Dammam',
                'hail' => 'Hail',
                'other' => 'Other',
            ]
            : [
                'riyadh' => 'الرياض',
                'jeddah' => 'جدة',
                'dammam' => 'الدمام',
                'hail' => 'حائل',
                'other' => 'أخرى',
            ];
    }

    /** @return array<string, string> */
    public static function genders(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ['ذكر' => 'Male', 'أنثى' => 'Female']
            : ['ذكر' => 'ذكر', 'أنثى' => 'أنثى'];
    }

    /** @return array<string, string> */
    public static function employmentStatuses(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ['employed' => 'Employed', 'unemployed' => 'Not employed']
            : ['employed' => 'موظف', 'unemployed' => 'غير موظف'];
    }

    /** @return array<string, string> */
    public static function studyPeriods(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return $locale === 'en'
            ? ['evening' => 'Evening']
            : ['evening' => 'مسائي'];
    }

    public static function cityLabel(?string $key, ?string $locale = null): string
    {
        return $key ? (static::cities($locale)[$key] ?? $key) : '—';
    }

    public static function nationalityLabel(?string $key, ?string $locale = null): string
    {
        return $key ? (static::nationalities($locale)[$key] ?? $key) : '—';
    }
}
