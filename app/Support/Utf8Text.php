<?php

namespace App\Support;

class Utf8Text
{
    public static function looksMojibake(string $text): bool
    {
        return (bool) preg_match('/[ØÙÃÂ]/u', $text);
    }

    public static function repair(string $text): string
    {
        if ($text === '' || ! self::looksMojibake($text)) {
            return $text;
        }

        $repaired = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');

        if (
            is_string($repaired)
            && $repaired !== ''
            && mb_check_encoding($repaired, 'UTF-8')
            && preg_match('/\p{Arabic}/u', $repaired)
        ) {
            return $repaired;
        }

        return $text;
    }

    public static function interpolate(string $text, ?string $locale = null): string
    {
        $text = self::repair($text);

        if (! str_contains($text, '{')) {
            return $text;
        }

        return strtr($text, [
            '{platform_name}' => platform_name($locale),
            '{platform_org}' => platform_org($locale),
        ]);
    }

    public static function deep(mixed $value, ?string $locale = null): mixed
    {
        if (is_string($value)) {
            return self::interpolate($value, $locale);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::deep($item, $locale);
            }
        }

        return $value;
    }
}
