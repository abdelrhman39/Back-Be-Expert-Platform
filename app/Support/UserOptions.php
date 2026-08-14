<?php

namespace App\Support;

class UserOptions
{
    /** @return array<string, string> */
    public static function roles(): array
    {
        return [
            'student' => 'طالب',
            'instructor' => 'مدرب / كادر أكاديمي',
            'sales' => 'موظف مبيعات CRM',
            'admin' => 'مسؤول',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'نشط',
            'suspended' => 'موقوف',
            'pending' => 'بانتظار التفعيل',
        ];
    }

    /** @return array<string, string> */
    public static function locales(): array
    {
        return [
            'ar' => 'العربية',
            'en' => 'English',
        ];
    }

    public static function roleLabel(?string $role): string
    {
        return $role ? (static::roles()[$role] ?? $role) : '—';
    }

    public static function statusLabel(?string $status): string
    {
        return $status ? (static::statuses()[$status] ?? $status) : '—';
    }

    public static function localeLabel(?string $locale): string
    {
        return $locale ? (static::locales()[$locale] ?? $locale) : '—';
    }
}
