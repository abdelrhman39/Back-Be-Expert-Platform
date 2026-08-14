<?php

namespace App\Support;

class CmsOptions
{
    /** @return array<string, string> */
    public static function pageLayouts(): array
    {
        return [
            'default' => 'افتراضي — بطاقة مركزية',
            'wide' => 'عرض كامل',
            'policy' => 'وثيقة / سياسة',
            'minimal' => 'Minimal — بدون بطاقة',
        ];
    }

    /** @return array<string, string> */
    public static function pageTypes(): array
    {
        return [
            'custom' => 'صفحة مخصصة',
            'policy' => 'سياسة / وثيقة',
            'landing' => 'صفحة هبوط',
            'home' => 'الصفحة الرئيسية',
            'about' => 'عن المنصة',
            'contact' => 'تواصل',
        ];
    }

    /** @return array<string, string> */
    public static function contentModes(): array
    {
        return [
            'blocks' => 'بلوكات مرنة (مكونات الصفحة)',
            'html' => 'محتوى HTML حر',
        ];
    }

    /** @return array<string, string> */
    public static function pageStatuses(): array
    {
        return [
            'draft' => 'مسودة',
            'published' => 'منشورة',
            'archived' => 'مؤرشفة',
        ];
    }

    /** @return array<string, string> */
    public static function linkTypes(): array
    {
        return [
            'none' => 'عنصر تجميعي (بدون رابط)',
            'route' => 'مسار Laravel',
            'page' => 'صفحة CMS',
            'url' => 'رابط خارجي',
        ];
    }

    /** @return array<string, string> */
    public static function menuKeys(): array
    {
        return [
            'header_main' => 'القائمة الرئيسية (الهيدر)',
            'footer_programs' => 'فوتر — البرامج التدريبية',
            'footer_policies' => 'فوتر — السياسات',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'published' => 'admin-badge admin-badge--success',
            'archived' => 'admin-badge admin-badge--muted',
            default => 'admin-badge admin-badge--warn',
        };
    }
}
