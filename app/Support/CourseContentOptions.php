<?php

namespace App\Support;

class CourseContentOptions
{
    /** @return array<string, string> */
    public static function lessonTypes(): array
    {
        return [
            'html' => 'محتوى HTML',
            'video' => 'فيديو',
            'document' => 'قراءة / مستند',
        ];
    }

    public static function lessonTypeLabel(string $type): string
    {
        return static::lessonTypes()[$type] ?? $type;
    }

    /** @return array<string, string> */
    public static function lessonStatuses(): array
    {
        return [
            'published' => 'منشور — يظهر للمتدرب',
            'draft' => 'مسودة — مخفي عن المتدرب',
            'hidden' => 'مخفي — محجوب مؤقتاً',
        ];
    }

    public static function lessonStatusLabel(string $status): string
    {
        return static::lessonStatuses()[$status] ?? $status;
    }

    public static function lessonStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'draft' => 'cc-badge cc-badge--draft',
            'hidden' => 'cc-badge cc-badge--hidden',
            default => 'cc-badge cc-badge--published',
        };
    }

    /** @return array<string, string> */
    public static function videoProviders(): array
    {
        return [
            'youtube' => 'YouTube',
            'vimeo' => 'Vimeo',
            'custom' => 'Embed مخصص',
        ];
    }

    public static function videoProviderLabel(?string $provider): string
    {
        return static::videoProviders()[$provider ?? 'custom'] ?? 'Embed مخصص';
    }

    public static function normalizeVideoEmbedUrl(?string $url, ?string $provider = null): ?string
    {
        if (blank($url)) {
            return null;
        }

        $url = trim($url);

        if (str_contains($url, '/embed/') || str_contains($url, 'player.vimeo.com')) {
            return $url;
        }

        $provider ??= 'custom';

        if ($provider === 'youtube' || str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/', $url, $matches)) {
                return 'https://www.youtube.com/embed/'.$matches[1];
            }
        }

        if ($provider === 'vimeo' || str_contains($url, 'vimeo.com')) {
            if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
                return 'https://player.vimeo.com/video/'.$matches[1];
            }
        }

        return $url;
    }
}
