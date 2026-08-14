<?php

namespace App\Support;

class AcademicBatchOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'active' => 'فعال',
            'planned' => 'مخطط',
            'closed' => 'مغلق',
            'draft' => 'مسودة',
        ];
    }

    /** @return array<string, string> */
    public static function studyModes(): array
    {
        return [
            'morning' => 'صباحي',
            'evening' => 'مسائي',
            'remote' => 'عن بُعد',
            'intensive' => 'مكثف',
        ];
    }

    /** @return array<string, string> */
    public static function semesters(): array
    {
        return [
            '2026-f1' => 'الفصل الأول للعام الدراسي 2026/2027',
            '2025-f2' => 'الفصل الثاني للعام الدراسي 2025/2026',
            '2025-f1' => 'الفصل الأول للعام الدراسي 2025/2026',
            '2024-f1' => 'الفصل الأول للعام الدراسي 2024/2025',
            '2024-s2' => 'الفصل الثاني للعام الدراسي 2023/2024',
            '2023-f1' => 'الفصل الأول للعام الدراسي 2023/2024',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return static::statuses()[$status] ?? $status;
    }

    public static function studyModeLabel(?string $mode): string
    {
        return $mode ? (static::studyModes()[$mode] ?? $mode) : '—';
    }

    public static function semesterLabel(?string $key, ?string $fallback = null): string
    {
        if ($key && isset(static::semesters()[$key])) {
            return static::semesters()[$key];
        }

        return $fallback ?: '—';
    }
}
