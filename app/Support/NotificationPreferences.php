<?php

namespace App\Support;

use App\Models\User;

class NotificationPreferences
{
    /** أنواع يمكن للطالب إيقاف البريد فيها (لا تشمل الحرجة) */
    public const OPT_OUT_TYPES = [
        NotificationTypes::LECTURE_REMINDER,
        NotificationTypes::ASSIGNMENT_PUBLISHED,
        NotificationTypes::RECORDING_PUBLISHED,
        NotificationTypes::ACADEMIC_REQUEST_STATUS,
    ];

    public static function allowsChannel(User $user, string $type, string $channel): bool
    {
        if ($channel === 'database') {
            return true;
        }

        if (in_array($type, [NotificationTypes::LECTURE_LIVE_NOW], true)) {
            return true;
        }

        if ($channel !== 'mail') {
            return true;
        }

        $prefs = $user->notification_preferences ?? [];

        return ($prefs[$type]['mail'] ?? true) !== false;
    }

    /** @return array<string, array{mail: bool}> */
    public static function forUser(User $user): array
    {
        $stored = $user->notification_preferences ?? [];
        $result = [];

        foreach (self::OPT_OUT_TYPES as $type) {
            $result[$type] = [
                'mail' => ($stored[$type]['mail'] ?? true) !== false,
            ];
        }

        return $result;
    }

    /** @param  array<string, array{mail?: bool}>  $prefs */
    public static function save(User $user, array $prefs): void
    {
        $normalized = [];

        foreach (self::OPT_OUT_TYPES as $type) {
            $normalized[$type] = [
                'mail' => ($prefs[$type]['mail'] ?? true) !== false,
            ];
        }

        $user->update(['notification_preferences' => $normalized]);
    }
}
