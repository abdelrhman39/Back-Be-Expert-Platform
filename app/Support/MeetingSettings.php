<?php

namespace App\Support;

use App\Models\PlatformSetting;

class MeetingSettings
{
    public const DEFAULT_PROVIDER = 'meeting_default_provider';

    public static function defaultProvider(): string
    {
        $provider = PlatformSetting::get(self::DEFAULT_PROVIDER, 'zoom') ?: 'zoom';

        return in_array($provider, ['zoom', 'teams', 'manual'], true) ? $provider : 'zoom';
    }

    public static function setDefaultProvider(string $provider): void
    {
        if (! in_array($provider, ['zoom', 'teams', 'manual'], true)) {
            $provider = 'zoom';
        }

        PlatformSetting::set(
            self::DEFAULT_PROVIDER,
            $provider,
            'meetings',
            'مزوّد المحاضرات الافتراضي',
            updatedBy: auth()->id(),
        );
    }

    /** @return array<string, string> */
    public static function providers(): array
    {
        return [
            'zoom' => 'Zoom',
            'teams' => 'Microsoft Teams',
            'manual' => 'رابط يدوي',
        ];
    }
}
