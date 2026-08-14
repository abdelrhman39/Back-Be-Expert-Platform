<?php

namespace App\Support;

use App\Models\CatalogCourse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CatalogSlugResolver
{
    /** @var array<int, string>|null */
    protected static ?array $legacyMap = null;

    /** @return array<int, string> */
    public static function legacyMap(): array
    {
        if (static::$legacyMap !== null) {
            return static::$legacyMap;
        }

        static::$legacyMap = [];
        $dir = dirname(base_path()).'/en-version/mirror/en/course';

        if (! is_dir($dir)) {
            return static::$legacyMap;
        }

        foreach (File::glob($dir.'/*.html') as $path) {
            $html = file_get_contents($path);
            if (! preg_match('/name="course_id"\s+value="(\d+)"/', $html, $match)) {
                continue;
            }

            $slug = static::normalizeSlug(basename($path, '.html'));
            static::$legacyMap[(int) $match[1]] = $slug;
        }

        return static::$legacyMap;
    }

    public static function resolveForCourse(CatalogCourse|int $course): string
    {
        if (is_int($course)) {
            $course = CatalogCourse::query()->findOrFail($course);
        }

        $map = static::legacyMap();
        if (isset($map[$course->id])) {
            return static::ensureUnique($map[$course->id], $course->id);
        }

        $base = Str::slug($course->title_en ?: $course->title_ar);
        if ($base === '') {
            $base = 'course-'.$course->id;
        }

        return static::ensureUnique($base, $course->id);
    }

    public static function normalizeSlug(string $slug): string
    {
        $slug = str_replace('.html', '', $slug);
        $slug = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $slug) ?? $slug;
        $normalized = Str::slug($slug);

        return $normalized !== '' ? $normalized : $slug;
    }

    public static function ensureUnique(string $slug, ?int $exceptId = null): string
    {
        $slug = static::normalizeSlug($slug);
        if ($slug === '') {
            $slug = $exceptId ? 'course-'.$exceptId : 'course';
        }

        $candidate = $slug;
        $suffix = 0;

        while (static::slugTaken($candidate, $exceptId)) {
            $suffix++;
            $candidate = $slug.'-'.$suffix;
        }

        return $candidate;
    }

    protected static function slugTaken(string $slug, ?int $exceptId): bool
    {
        $variants = array_unique([$slug, $slug.'.html']);

        return CatalogCourse::query()
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->whereIn('slug', $variants)
            ->exists();
    }

    public static function assignSlug(CatalogCourse $course, ?string $slug = null): void
    {
        $course->update(['slug' => $slug ?? static::resolveForCourse($course)]);
    }

    public static function fixAllCourses(): int
    {
        $fixed = 0;

        foreach (CatalogCourse::query()->orderBy('id')->get() as $course) {
            $resolved = static::resolveForCourse($course);
            $current = static::normalizeSlug((string) ($course->slug ?: ''));

            if ($current !== $resolved) {
                $course->update(['slug' => $resolved]);
                $fixed++;
            }
        }

        return $fixed;
    }
}
