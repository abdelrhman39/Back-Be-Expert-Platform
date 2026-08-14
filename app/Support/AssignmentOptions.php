<?php

namespace App\Support;

class AssignmentOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'draft' => 'مسودة',
            'published' => 'منشور',
            'closed' => 'مغلق',
            'archived' => 'مؤرشف',
        ];
    }

    /** @return array<string, string> */
    public static function scopes(): array
    {
        return [
            'section' => 'عام للشعبة',
            'session' => 'مرتبط بحصة',
        ];
    }

    /** @return array<string, string> */
    public static function submissionStatuses(): array
    {
        return [
            'draft' => 'مسودة',
            'submitted' => 'مُسلَّم',
            'late' => 'متأخر',
            'graded' => 'مُقيَّم',
            'returned' => 'مُعاد للتعديل',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function scopeLabel(string $scope): string
    {
        return static::scopes()[$scope] ?? $scope;
    }

    public static function submissionStatusLabel(string $status): string
    {
        return static::submissionStatuses()[$status] ?? $status;
    }

    /** @return array<string, string> */
    public static function submissionBadgeClass(string $status): string
    {
        return match ($status) {
            'graded' => 'admin-badge--success',
            'submitted', 'late' => 'admin-badge--info',
            'returned' => 'admin-badge--warn',
            default => 'admin-badge--muted',
        };
    }

    /** @return array<int, string> */
    public static function allowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'png', 'jpg', 'jpeg'];
    }

    public static function maxFileKb(): int
    {
        return 51200;
    }
}
