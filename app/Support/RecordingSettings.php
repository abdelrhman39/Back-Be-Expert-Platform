<?php

namespace App\Support;

use App\Models\PlatformSetting;

class RecordingSettings
{
    public const AUTO_RECORD = 'teams_auto_record';

    public const PUBLISH_MODE = 'teams_recording_publish_mode';

    public const AUTO_PUBLISH_HOURS = 'teams_recording_auto_publish_hours';

    public const RETENTION_DAYS = 'teams_recording_retention_days';

    public const ALLOW_DOWNLOAD = 'teams_recording_allow_download';

    public const ACCESS_POLICY = 'teams_recording_access_policy';

    public static function autoRecordEnabled(): bool
    {
        $stored = PlatformSetting::get(self::AUTO_RECORD);

        return $stored === null || $stored === '' || in_array(strtolower($stored), ['1', 'true', 'yes', 'on'], true);
    }

    public static function publishMode(): string
    {
        return PlatformSetting::get(self::PUBLISH_MODE, 'manual') ?: 'manual';
    }

    public static function autoPublishHours(): int
    {
        return max(1, (int) (PlatformSetting::get(self::AUTO_PUBLISH_HOURS, '24') ?: 24));
    }

    public static function retentionDays(): int
    {
        return max(30, (int) (PlatformSetting::get(self::RETENTION_DAYS, '365') ?: 365));
    }

    public static function allowDownload(): bool
    {
        $stored = PlatformSetting::get(self::ALLOW_DOWNLOAD);

        return in_array(strtolower((string) $stored), ['1', 'true', 'yes', 'on'], true);
    }

    public static function accessPolicy(): string
    {
        $policy = PlatformSetting::get(self::ACCESS_POLICY, 'enrolled_only');

        return in_array($policy, ['enrolled_only', 'attended_only'], true) ? $policy : 'enrolled_only';
    }

    public static function setAutoRecord(bool $enabled): void
    {
        PlatformSetting::set(self::AUTO_RECORD, $enabled ? '1' : '0', 'teams', 'تسجيل تلقائي للمحاضرات');
    }

    public static function setPublishMode(string $mode): void
    {
        PlatformSetting::set(self::PUBLISH_MODE, $mode, 'teams', 'وضع نشر التسجيل');
    }

    public static function setAutoPublishHours(int $hours): void
    {
        PlatformSetting::set(self::AUTO_PUBLISH_HOURS, (string) max(1, $hours), 'teams', 'تأخير النشر التلقائي (ساعات)');
    }

    public static function setRetentionDays(int $days): void
    {
        PlatformSetting::set(self::RETENTION_DAYS, (string) max(30, $days), 'teams', 'مدة الاحتفاظ بالتسجيل (أيام)');
    }

    public static function setAllowDownload(bool $allowed): void
    {
        PlatformSetting::set(self::ALLOW_DOWNLOAD, $allowed ? '1' : '0', 'teams', 'السماح بتحميل التسجيل');
    }

    public static function setAccessPolicy(string $policy): void
    {
        PlatformSetting::set(self::ACCESS_POLICY, $policy, 'teams', 'سياسة الوصول للتسجيل');
    }
}
