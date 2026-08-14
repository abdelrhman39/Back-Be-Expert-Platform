<?php

namespace App\Support;

class SessionMaterialOptions
{
    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            'file' => 'ملف',
            'link' => 'رابط',
            'teams_recording' => 'تسجيل Teams',
        ];
    }

    /** @return array<string, string> */
    public static function visibilities(): array
    {
        return [
            'published' => 'منشور',
            'draft' => 'مسودة',
            'hidden' => 'مخفي',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return static::types()[$type] ?? $type;
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
