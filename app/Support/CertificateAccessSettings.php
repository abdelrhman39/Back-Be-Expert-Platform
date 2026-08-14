<?php

namespace App\Support;

use App\Models\PlatformSetting;

class CertificateAccessSettings
{
    public static function defaultVisibilityMode(): string
    {
        $mode = (string) PlatformSetting::get('certificate_default_visibility_mode', 'after_graduation');

        return in_array($mode, ['immediate', 'after_graduation', 'after_exam_pass', 'after_graduation_and_exam'], true)
            ? $mode
            : 'after_graduation';
    }

    public static function requiredExamType(): string
    {
        $type = (string) PlatformSetting::get('certificate_required_exam_type', 'final');

        return in_array($type, ['any', 'exam', 'midterm', 'final'], true) ? $type : 'final';
    }

    public static function portalEnabled(): bool
    {
        return static::flag('certificate_student_portal_enabled', true);
    }

    public static function autoIssueEnabled(): bool
    {
        return static::flag('certificate_auto_issue_enabled', true);
    }

    public static function autoIssueNotificationsEnabled(): bool
    {
        return static::flag('certificate_auto_issue_notifications_enabled', true);
    }

    public static function downloadsEnabled(): bool
    {
        return static::flag('certificate_student_downloads_enabled', true);
    }

    public static function printingEnabled(): bool
    {
        return static::flag('certificate_student_printing_enabled', true);
    }

    public static function detailsEnabled(): bool
    {
        return static::flag('certificate_student_details_enabled', true);
    }

    public static function hideRevoked(): bool
    {
        return static::flag('certificate_hide_revoked_from_students', false);
    }

    public static function requireIntegrityForDownload(): bool
    {
        return static::flag('certificate_require_integrity_for_download', true);
    }

    public static function requireActiveForDownload(): bool
    {
        return static::flag('certificate_require_active_for_download', true);
    }

    private static function flag(string $key, bool $default): bool
    {
        return PlatformSetting::get($key, $default ? '1' : '0') !== '0';
    }
}
