<?php

namespace App\Support;

class AttendanceOptions
{
    /** @return array<string, string> */
    public static function recordStatuses(): array
    {
        return [
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'معذور',
        ];
    }

    /** @return array<string, string> */
    public static function sessionStatuses(): array
    {
        return [
            'scheduled' => 'مجدولة',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
        ];
    }

    /** @return array<string, string> */
    public static function sources(): array
    {
        return [
            'manual' => 'يدوي',
            'teams_sync' => 'Microsoft Teams',
            'override' => 'تصحيح',
            'schedule' => 'جدول تلقائي',
        ];
    }

    public static function recordStatusLabel(string $status): string
    {
        return static::recordStatuses()[$status] ?? $status;
    }

    public static function sessionStatusLabel(string $status): string
    {
        return static::sessionStatuses()[$status] ?? $status;
    }

    public static function sourceLabel(string $source): string
    {
        return static::sources()[$source] ?? $source;
    }

    /** @return array<string, string> */
    public static function recordBadgeClass(string $status): string
    {
        return match ($status) {
            'present' => 'admin-badge--success',
            'late' => 'admin-badge--warn',
            'excused' => 'admin-badge--info',
            default => 'admin-badge--danger',
        };
    }

    /**
     * @param  iterable<\App\Models\AttendanceRecord>  $records
     * @return array{sessions: int, present: int, absent: int, late: int, excused: int, rate: float}
     */
    public static function summarizeRecords(iterable $records): array
    {
        $counts = [
            'sessions' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ];

        foreach ($records as $record) {
            $counts['sessions']++;
            if (isset($counts[$record->status])) {
                $counts[$record->status]++;
            }
        }

        $attended = $counts['present'] + $counts['late'];
        $counts['rate'] = $counts['sessions'] > 0
            ? round(($attended / $counts['sessions']) * 100, 1)
            : 0.0;

        return $counts;
    }
}
