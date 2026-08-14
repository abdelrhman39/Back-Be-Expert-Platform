<?php

namespace App\Support;

class AcademicStaffOptions
{
    /** @return array<string, string> */
    public static function roles(): array
    {
        return [
            'instructor' => 'مدرب',
            'coordinator' => 'منسق',
            'reviewer' => 'مراجع',
            'assistant' => 'مساعد أكاديمي',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'on_leave' => 'في إجازة',
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

    public static function roleLabel(string $role): string
    {
        return static::roles()[$role] ?? $role;
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }
}
