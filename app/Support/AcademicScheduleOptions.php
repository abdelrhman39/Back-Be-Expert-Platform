<?php

namespace App\Support;

class AcademicScheduleOptions
{
    /** @return array<string, string> */
    public static function days(): array
    {
        return [
            '' => '-- اليوم --',
            'sun' => 'الأحد',
            'mon' => 'الإثنين',
            'tue' => 'الثلاثاء',
            'wed' => 'الأربعاء',
            'thu' => 'الخميس',
            'fri' => 'الجمعة',
            'sat' => 'السبت',
        ];
    }

    public static function dayLabel(?string $day): string
    {
        return $day ? (static::days()[$day] ?? $day) : '—';
    }

    /** @return array<string, string> */
    public static function periods(): array
    {
        return AcademicSectionOptions::periods();
    }

    public static function formatTimeRange(?string $start, ?string $end): string
    {
        if (! $start && ! $end) {
            return '—';
        }

        $start = $start ? substr($start, 0, 5) : '—';
        $end = $end ? substr($end, 0, 5) : '—';

        return $start.' – '.$end;
    }
}
