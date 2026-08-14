<?php

namespace App\Support;

use App\Models\PlatformSetting;

class TeamsSettings
{
    public const ENABLED = 'teams_enabled';

    public const TENANT_ID = 'teams_tenant_id';

    public const CLIENT_ID = 'teams_client_id';

    public const CLIENT_SECRET = 'teams_client_secret';

    public const ORGANIZER_USER_ID = 'teams_organizer_user_id';

    public const AUTO_ATTENDANCE = 'teams_auto_attendance';

    public const SYNC_INTERVAL = 'teams_sync_interval_minutes';

    public static function isEnabled(): bool
    {
        $stored = PlatformSetting::get(self::ENABLED);

        if ($stored !== null && $stored !== '') {
            return in_array(strtolower($stored), ['1', 'true', 'yes', 'on'], true);
        }

        return self::isConfigured();
    }

    public static function isConfigured(): bool
    {
        return filled(self::tenantId())
            && filled(self::clientId())
            && filled(self::clientSecret());
    }

    public static function tenantId(): ?string
    {
        return self::value(self::TENANT_ID, 'teams.tenant_id');
    }

    public static function clientId(): ?string
    {
        return self::value(self::CLIENT_ID, 'teams.client_id');
    }

    public static function clientSecret(): ?string
    {
        return self::value(self::CLIENT_SECRET, 'teams.client_secret');
    }

    public static function organizerUserId(): ?string
    {
        return self::value(self::ORGANIZER_USER_ID, 'teams.organizer_user_id');
    }

    public static function redirectUri(): string
    {
        $stored = PlatformSetting::get('teams_redirect_uri');

        if (filled($stored)) {
            return $stored;
        }

        return config('teams.redirect_uri') ?: url('/integrations/microsoft/callback');
    }

    public static function autoAttendanceEnabled(): bool
    {
        $stored = PlatformSetting::get(self::AUTO_ATTENDANCE);

        return $stored === null || $stored === '' || in_array(strtolower($stored), ['1', 'true', 'yes', 'on'], true);
    }

    public static function syncIntervalMinutes(): int
    {
        $stored = PlatformSetting::get(self::SYNC_INTERVAL);

        return max(5, (int) ($stored ?: 15));
    }

    public static function studentScopes(): string
    {
        return config('teams.scopes.student', 'openid profile email offline_access User.Read');
    }

    public static function hasStoredClientSecret(): bool
    {
        return filled(PlatformSetting::get(self::CLIENT_SECRET));
    }

    public static function setEnabled(bool $enabled): void
    {
        PlatformSetting::set(self::ENABLED, $enabled ? '1' : '0', 'teams', 'تفعيل Microsoft Teams');
    }

    public static function setTenantId(?string $value): void
    {
        PlatformSetting::set(self::TENANT_ID, $value ?? '', 'teams', 'Azure Tenant ID');
    }

    public static function setClientId(?string $value): void
    {
        PlatformSetting::set(self::CLIENT_ID, $value ?? '', 'teams', 'Azure Client ID');
    }

    public static function setClientSecret(?string $value): void
    {
        if ($value !== null && $value !== '') {
            PlatformSetting::set(self::CLIENT_SECRET, $value, 'teams', 'Azure Client Secret');
        }
    }

    public static function setOrganizerUserId(?string $value): void
    {
        PlatformSetting::set(self::ORGANIZER_USER_ID, $value ?? '', 'teams', 'Organizer User ID');
    }

    public static function setAutoAttendance(bool $enabled): void
    {
        PlatformSetting::set(self::AUTO_ATTENDANCE, $enabled ? '1' : '0', 'teams', 'مزامنة الحضور التلقائية');
    }

    public static function setSyncIntervalMinutes(int $minutes): void
    {
        PlatformSetting::set(self::SYNC_INTERVAL, (string) max(5, $minutes), 'teams', 'فترة المزامنة (دقائق)');
    }

    private static function value(string $key, string $configKey): ?string
    {
        $stored = PlatformSetting::get($key);

        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        $fromConfig = config($configKey);

        return filled($fromConfig) ? (string) $fromConfig : null;
    }
}
