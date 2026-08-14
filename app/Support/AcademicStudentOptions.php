<?php

namespace App\Support;

class AcademicStudentOptions
{
    /** @return array<string, string> */
    public static function academicStatuses(): array
    {
        return [
            'studying' => 'مستمر دراسياً',
            'pending' => 'بانتظار إكمال التسجيل',
            'graduated' => 'خريج',
            'withdrawn' => 'منسحب',
            'deferred' => 'مؤجل',
            'suspended' => 'متوقف',
            'eligible' => 'مؤهل للتخرج',
            'expected' => 'متوقع التخرج',
        ];
    }

    /** @return array<string, string> */
    public static function enrollmentStatuses(): array
    {
        return [
            'studying' => 'مستمر دراسياً',
            'pending' => 'بانتظار إكمال التسجيل',
            'withdrawn' => 'منسحب',
            'deferred' => 'مؤجل',
            'suspended' => 'متوقف',
        ];
    }

    /** @return array<string, string> */
    public static function graduationStatuses(): array
    {
        return [
            'graduated' => 'خريج',
            'eligible' => 'مؤهل للتخرج',
            'expected' => 'متوقع التخرج',
        ];
    }

    /** @return array<string, string> */
    public static function genders(): array
    {
        return [
            'ذكر' => 'ذكر',
            'أنثى' => 'أنثى',
        ];
    }

    public static function academicStatusLabel(?string $status): string
    {
        return $status ? (static::academicStatuses()[$status] ?? $status) : '—';
    }
}
