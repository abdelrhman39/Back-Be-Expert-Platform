<?php

namespace App\Support;

class InstallmentOptions
{
    /** @return array<string, string> */
    public static function contractStatuses(): array
    {
        return [
            'draft' => 'مسودة',
            'pending_signature' => 'بانتظار التوقيع',
            'active' => 'نشط',
            'completed' => 'مكتمل',
            'defaulted' => 'متعثر',
            'suspended' => 'موقوف — متأخرات',
            'cancelled' => 'ملغى',
        ];
    }

    /** @return array<string, string> */
    public static function scheduleStatuses(): array
    {
        return [
            'pending' => 'بانتظار السداد',
            'paid' => 'مدفوع',
            'overdue' => 'متأخر',
            'waived' => 'معفى',
            'cancelled' => 'ملغى',
        ];
    }

    /** @return array<string, string> */
    public static function dueRules(): array
    {
        return [
            'at_enrollment' => 'عند التسجيل (فوري)',
            'month_offset' => 'بعد أشهر من البداية',
        ];
    }

    /** @return array<string, string> */
    public static function programTypes(): array
    {
        return [
            'diploma_1y' => 'دبلوم سنة واحدة',
            'diploma_2y' => 'دبلوم سنتين',
            '' => 'جميع البرامج',
        ];
    }

    public static function contractStatusLabel(string $status): string
    {
        return static::contractStatuses()[$status] ?? $status;
    }

    public static function scheduleStatusLabel(string $status): string
    {
        return static::scheduleStatuses()[$status] ?? $status;
    }

    public static function dueRuleLabel(string $rule): string
    {
        return static::dueRules()[$rule] ?? $rule;
    }

    public static function scheduleBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'portal-inst-badge--att-present',
            'overdue' => 'portal-inst-badge--att-absent',
            'waived' => 'portal-inst-badge--att-excused',
            'pending' => 'portal-inst-badge--upcoming',
            default => '',
        };
    }
}
