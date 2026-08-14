<?php

namespace App\Support;

class NotificationRuleCatalog
{
    /** @return array<string, array{label: string, icon: string, description: string}> */
    public static function categories(): array
    {
        return [
            'lecture' => [
                'label' => 'المحاضرات',
                'icon' => 'fa-chalkboard-user',
                'description' => 'تذكيرات قبل الحصة وإشعار «جارية الآن»',
            ],
            'academic' => [
                'label' => 'أكاديمي',
                'icon' => 'fa-graduation-cap',
                'description' => 'واجبات، تسجيلات، وتحديثات الطلبات الأكاديمية',
            ],
        ];
    }

    public static function categoryForType(string $type): string
    {
        return match ($type) {
            NotificationTypes::LECTURE_REMINDER, NotificationTypes::LECTURE_LIVE_NOW => 'lecture',
            default => 'academic',
        };
    }

    /** @return array<string, array{label: string, icon: string, color: string}> */
    public static function channels(): array
    {
        return [
            'database' => ['label' => 'داخل المنصة', 'icon' => 'fa-bell', 'color' => 'green'],
            'mail' => ['label' => 'بريد إلكتروني', 'icon' => 'fa-envelope', 'color' => 'blue'],
        ];
    }

    public static function offsetLabel(?int $minutes): string
    {
        if ($minutes === null || $minutes === 0) {
            return '—';
        }

        if ($minutes >= 1440 && $minutes % 1440 === 0) {
            $days = (int) ($minutes / 1440);

            return $days === 1 ? '24 ساعة' : "{$days} أيام";
        }

        if ($minutes >= 60 && $minutes % 60 === 0) {
            $hours = (int) ($minutes / 60);

            return $hours === 1 ? 'ساعة' : "{$hours} ساعات";
        }

        return "{$minutes} دقيقة";
    }

    public static function triggerLabel(string $kind): string
    {
        return match ($kind) {
            'before_event' => 'قبل الحدث',
            'live_window' => 'أثناء المحاضرة',
            'immediate' => 'فوري',
            default => $kind,
        };
    }
}
