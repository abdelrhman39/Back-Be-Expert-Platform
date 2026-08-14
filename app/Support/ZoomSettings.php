<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Crypt;

class ZoomSettings
{
    private const PREFIX = 'zoom_';

    public static function enabled(): bool
    {
        return self::boolean('enabled', (bool) config('zoom.enabled')) && self::configured();
    }

    public static function configured(): bool
    {
        return filled(self::accountId()) && filled(self::clientId()) && filled(self::clientSecret());
    }

    public static function accountId(): ?string
    {
        return self::value('account_id');
    }

    public static function clientId(): ?string
    {
        return self::value('client_id');
    }

    public static function clientSecret(): ?string
    {
        return self::secret('client_secret');
    }

    public static function webhookSecret(): ?string
    {
        return self::secret('webhook_secret');
    }

    public static function defaultHost(): ?string
    {
        return self::value('default_host');
    }

    public static function hostStrategy(): string
    {
        return self::choice('host_strategy', ['central', 'instructor', 'pool'], 'central');
    }

    public static function autoAttendance(): bool
    {
        return self::boolean('auto_attendance', true);
    }

    public static function syncInterval(): int
    {
        return max(5, self::integer('sync_interval', 15));
    }

    public static function lateMinutes(): int
    {
        return max(0, self::integer('late_minutes', 10));
    }

    public static function minimumAttendancePercent(): int
    {
        return min(100, max(0, self::integer('minimum_attendance_percent', 0)));
    }

    public static function minimumAttendanceMinutes(): int
    {
        return max(0, self::integer('minimum_attendance_minutes', 1));
    }

    public static function registrationRequired(): bool
    {
        return self::boolean('registration_required', true);
    }

    public static function waitingRoom(): bool
    {
        return self::boolean('waiting_room', true);
    }

    public static function hostVideo(): bool
    {
        return self::boolean('host_video', true);
    }

    public static function participantVideo(): bool
    {
        return self::boolean('participant_video', false);
    }

    public static function muteUponEntry(): bool
    {
        return self::boolean('mute_upon_entry', true);
    }

    public static function joinBeforeHost(): bool
    {
        return self::boolean('join_before_host', false);
    }

    public static function allowMultipleDevices(): bool
    {
        return self::boolean('allow_multiple_devices', false);
    }

    public static function audioMode(): string
    {
        return self::choice('audio_mode', ['both', 'voip', 'telephony'], 'both');
    }

    public static function joinWindowMinutes(): int
    {
        return min(1440, max(0, self::integer('join_window_minutes', 30)));
    }

    public static function recordingPolicy(): string
    {
        return self::choice('recording_policy', ['automatic', 'manual', 'disabled'], 'automatic');
    }

    public static function recordingDestination(): string
    {
        return self::choice('recording_destination', ['zoom', 's3', 'google'], 'zoom');
    }

    public static function s3Disk(): string
    {
        return self::value('s3_disk') ?? 's3';
    }

    public static function googleDisk(): string
    {
        return self::value('google_disk') ?? 'google';
    }

    public static function set(string $name, mixed $value, bool $secret = false, ?int $updatedBy = null): void
    {
        $secret = $secret || in_array($name, ['client_secret', 'webhook_secret'], true);
        $stored = $secret && filled($value) ? Crypt::encryptString((string) $value) : (string) $value;
        PlatformSetting::set(
            self::PREFIX.$name,
            $stored,
            'zoom',
            'Zoom '.$name,
            'string',
            $secret,
            null,
            $updatedBy,
        );
    }

    private static function value(string $name): ?string
    {
        $stored = PlatformSetting::get(self::PREFIX.$name);
        if (filled($stored)) {
            return $stored;
        }

        $fallback = config('zoom.'.$name);

        return filled($fallback) ? (string) $fallback : null;
    }

    private static function secret(string $name): ?string
    {
        $stored = PlatformSetting::get(self::PREFIX.$name);
        if (filled($stored)) {
            try {
                return Crypt::decryptString($stored);
            } catch (\Throwable) {
                return null;
            }
        }

        $fallback = config('zoom.'.$name);

        return filled($fallback) ? (string) $fallback : null;
    }

    private static function boolean(string $name, bool $default): bool
    {
        $value = self::value($name);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOL);
    }

    private static function integer(string $name, int $default): int
    {
        return (int) (self::value($name) ?? $default);
    }

    private static function choice(string $name, array $allowed, string $default): string
    {
        $value = self::value($name) ?? $default;

        return in_array($value, $allowed, true) ? $value : $default;
    }
}
