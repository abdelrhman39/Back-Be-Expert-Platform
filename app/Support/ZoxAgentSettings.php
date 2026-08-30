<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Crypt;

class ZoxAgentSettings
{
    private const PREFIX = 'zoxagent_';

    public static function enabled(): bool
    {
        return self::boolean('enabled', false) && self::configured();
    }

    public static function configured(): bool
    {
        return filled(self::baseUrl()) && filled(self::apiKey());
    }

    public static function baseUrl(): ?string
    {
        $value = self::value('base_url') ?? 'https://app.zoxagent.com';

        return filled($value) ? rtrim($value, '/') : null;
    }

    public static function apiKey(): ?string
    {
        return self::secret('api_key');
    }

    public static function hasApiKey(): bool
    {
        return filled(self::apiKey());
    }

    public static function embedOrigin(): string
    {
        $stored = self::value('embed_origin');
        if (filled($stored)) {
            return rtrim($stored, '/');
        }

        return rtrim((string) url('/'), '/');
    }

    public static function apiBase(): string
    {
        return (self::baseUrl() ?? '').'/api/v1';
    }

    public static function joinMode(): string
    {
        return self::choice('join_mode', ['redirect', 'embed'], 'redirect');
    }

    public static function prefersRedirectJoin(): bool
    {
        return self::joinMode() === 'redirect';
    }

    public static function autoRecord(): bool
    {
        return self::boolean('auto_record', true);
    }

    public static function autoAttendance(): bool
    {
        return self::boolean('auto_attendance', true);
    }

    public static function attendanceMode(): string
    {
        return self::choice('attendance_mode', ['join', 'duration'], 'join');
    }

    public static function startLeadMinutes(): int
    {
        return min(180, max(0, self::integer('start_lead_minutes', 5)));
    }

    public static function joinWindowMinutes(): int
    {
        return min(1440, max(0, self::integer('join_window_minutes', 30)));
    }

    public static function lateMinutes(): int
    {
        return max(0, self::integer('late_minutes', 10));
    }

    public static function minimumAttendanceMinutes(): int
    {
        return max(0, self::integer('minimum_attendance_minutes', 1));
    }

    public static function minimumAttendancePercent(): int
    {
        return min(100, max(0, self::integer('minimum_attendance_percent', 0)));
    }

    public static function inboundWebhookUrl(): string
    {
        return url('/webhooks/zoxagent');
    }

    public static function webhookSecret(): ?string
    {
        return self::secret('webhook_secret');
    }

    public static function webhookEndpointId(): ?string
    {
        return self::value('webhook_endpoint_id');
    }

    public static function recordingStorageMode(): string
    {
        return self::choice('recording_storage_mode', ['managed', 'byo'], 'managed');
    }

    public static function s3Bucket(): ?string
    {
        return self::value('s3_bucket');
    }

    public static function s3Region(): string
    {
        return self::value('s3_region') ?: 'eu-central-1';
    }

    public static function s3Endpoint(): ?string
    {
        return self::value('s3_endpoint');
    }

    public static function s3PublicBaseUrl(): ?string
    {
        return self::value('s3_public_base_url');
    }

    public static function s3Label(): string
    {
        return self::value('s3_label') ?: 'Be Expert LMS';
    }

    public static function s3AccessKey(): ?string
    {
        return self::secret('s3_access_key');
    }

    public static function s3SecretKey(): ?string
    {
        return self::secret('s3_secret_key');
    }

    public static function s3ForcePathStyle(): bool
    {
        return self::boolean('s3_force_path_style', false);
    }

    public static function hasS3Credentials(): bool
    {
        return filled(self::s3AccessKey()) && filled(self::s3SecretKey()) && filled(self::s3Bucket());
    }

    public static function allowScreenShare(): bool
    {
        return self::boolean('livekit_allow_screen_share', true);
    }

    public static function allowStudentCamera(): bool
    {
        return self::boolean('livekit_allow_student_camera', false);
    }

    public static function screenShareQuality(): string
    {
        return self::choice('livekit_screen_share_quality', ['off', '720p', '1080p'], '720p');
    }

    public static function cameraQuality(): string
    {
        return self::choice('livekit_camera_quality', ['360p', '540p', '720p'], '540p');
    }

    public static function recordingQuality(): string
    {
        return self::choice('livekit_recording_quality', ['off', '720p', '1080p'], '720p');
    }

    public static function adaptiveStream(): bool
    {
        return self::boolean('livekit_adaptive_stream', true);
    }

    public static function dynacast(): bool
    {
        return self::boolean('livekit_dynacast', true);
    }

    public static function emptyTimeoutSec(): int
    {
        return min(3600, max(60, self::integer('livekit_empty_timeout_sec', 300)));
    }

    public static function autoDispatchAgents(): bool
    {
        return self::boolean('livekit_auto_dispatch_agents', false);
    }

    /** @return array<string, mixed> */
    public static function mediaPolicy(): array
    {
        $screen = self::screenShareQuality();
        $recording = self::recordingQuality();

        return [
            'autoStartRecording' => self::autoRecord() && $recording !== 'off',
            'allowScreenShare' => self::allowScreenShare() && $screen !== 'off',
            'allowStudentCamera' => self::allowStudentCamera(),
            'screenShareQuality' => $screen,
            'cameraQuality' => self::cameraQuality(),
            'recordingQuality' => $recording,
            'adaptiveStream' => self::adaptiveStream(),
            'dynacast' => self::dynacast(),
            'emptyTimeoutSec' => self::emptyTimeoutSec(),
            'autoDispatchAgents' => self::autoDispatchAgents(),
        ];
    }

    public static function set(string $name, mixed $value, bool $secret = false, ?int $updatedBy = null): void
    {
        $secret = $secret || in_array($name, ['api_key', 'webhook_secret', 's3_access_key', 's3_secret_key'], true);
        $stored = $secret && filled($value) ? Crypt::encryptString((string) $value) : (string) ($value ?? '');

        PlatformSetting::set(
            self::PREFIX.$name,
            $stored,
            'zoxagent',
            'ZoxAgent '.$name,
            'string',
            $secret,
            null,
            $updatedBy,
        );
    }

    private static function value(string $name): ?string
    {
        $stored = PlatformSetting::get(self::PREFIX.$name);

        return filled($stored) ? $stored : null;
    }

    private static function secret(string $name): ?string
    {
        $stored = PlatformSetting::get(self::PREFIX.$name);
        if (! filled($stored)) {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return $stored;
        }
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
