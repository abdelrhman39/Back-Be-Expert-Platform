<?php

namespace App\Support;

class CourseModuleOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'published' => 'منشور — يظهر للمتدرب',
            'draft' => 'مسودة — مخفي عن المتدرب',
            'hidden' => 'مخفي — محجوب مؤقتاً',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'draft' => 'cc-badge cc-badge--draft',
            'hidden' => 'cc-badge cc-badge--hidden',
            default => 'cc-badge cc-badge--published',
        };
    }

    /** @return array<string, string> */
    public static function completionRules(): array
    {
        return [
            'all_lessons' => 'إكمال جميع الدروس',
            'any_lesson' => 'إكمال درس واحد على الأقل',
            'manual' => 'يدوي — بدون قفل تلقائي',
        ];
    }

    public static function completionRuleLabel(string $rule): string
    {
        return static::completionRules()[$rule] ?? $rule;
    }

    /** @return array<string, string> */
    public static function icons(): array
    {
        return [
            '' => 'بدون أيقونة',
            'fa-book-open' => 'كتاب',
            'fa-layer-group' => 'طبقات',
            'fa-video' => 'فيديو',
            'fa-file-lines' => 'مستند',
            'fa-lightbulb' => 'فكرة',
            'fa-users' => 'فريق',
            'fa-chart-line' => 'تحليل',
            'fa-award' => 'شهادة',
            'fa-rocket' => 'انطلاق',
            'fa-graduation-cap' => 'تخرج',
        ];
    }

    public static function iconLabel(?string $icon): string
    {
        return static::icons()[$icon ?? ''] ?? 'بدون أيقونة';
    }
}
