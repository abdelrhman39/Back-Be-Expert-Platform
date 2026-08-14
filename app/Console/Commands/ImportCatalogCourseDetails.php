<?php

namespace App\Console\Commands;

use App\Models\CatalogCourse;
use App\Models\CatalogCourseDetail;
use App\Services\Catalog\CatalogCourseArabicGenerator;
use App\Services\Catalog\LegacyCourseHtmlParser;
use App\Support\CatalogSlugResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportCatalogCourseDetails extends Command
{
    protected $signature = 'catalog:import-details
                            {--force : Overwrite existing details}
                            {--generate-ar : Generate Arabic tabs from English when no Arabic HTML exists}
                            {--course= : Limit to a single course id or slug}';

    protected $description = 'Import catalog course marketing tabs from legacy HTML files';

    public function handle(
        LegacyCourseHtmlParser $parser,
        CatalogCourseArabicGenerator $arabicGenerator,
    ): int {
        $enDir = dirname(base_path()).'/en-version/mirror/en/course';
        $arDir = dirname(base_path()).'/New-Platform';

        if (! is_dir($enDir)) {
            $this->error('EN course HTML directory not found.');

            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;
        $courseFilter = $this->option('course');
        $enFiles = $this->resolveEnFiles($enDir, $courseFilter);

        foreach ($enFiles as $enPath) {
            $html = file_get_contents($enPath);
            $enSlug = str_replace('.html', '', basename($enPath));
            $courseId = $parser->extractMainCourseId($html);

            $course = $this->resolveCourse($enSlug, $courseId);

            if (! $course) {
                if (! $courseFilter) {
                    $this->warn("Course {$enSlug} (#{$courseId}) not in DB — skip ".basename($enPath));
                }
                $skipped++;

                continue;
            }

            if ($courseFilter && ! $this->matchesFilter($course, $courseFilter)) {
                continue;
            }

            if (! $this->option('force') && CatalogCourseDetail::query()->where('course_id', $course->id)->exists()) {
                $skipped++;

                continue;
            }

            $payload = $this->buildPayload($parser, $course, $html, $enPath, $arDir);

            if ($this->option('generate-ar')) {
                $payload = $arabicGenerator->generateArabicFields($payload);
            }

            CatalogCourseDetail::query()->updateOrCreate(
                ['course_id' => $course->id],
                $payload,
            );

            $course->update([
                'title_en' => $course->title_en ?: $parser->extractTitle($html),
                'title_ar' => $course->title_ar ?: ($parser->extractTitle($this->readArabicHtml($parser, $arDir, $course) ?? '') ?: $course->title_ar),
                'slug' => CatalogSlugResolver::ensureUnique($enSlug, (int) $course->id),
            ]);

            $this->line("Imported: {$course->displayTitle()} (#{$course->id})");
            $imported++;
        }

        $this->info("Imported details for {$imported} courses (skipped {$skipped}).");

        return self::SUCCESS;
    }

    protected function resolveCourse(string $enSlug, ?int $courseId): ?CatalogCourse
    {
        $course = CatalogCourse::query()
            ->where(function ($query) use ($enSlug) {
                $query->where('slug', $enSlug)
                    ->orWhere('slug', $enSlug.'.html');
            })
            ->first();

        if ($course) {
            return $course;
        }

        if ($courseId) {
            $course = CatalogCourse::query()->find($courseId);
            if ($course) {
                return $course;
            }
        }

        return null;
    }

    protected function matchesFilter(CatalogCourse $course, string $filter): bool
    {
        return (string) $course->id === $filter
            || $course->slug === $filter
            || str_replace('.html', '', (string) $course->slug) === $filter;
    }

    /** @return array<string, mixed> */
    protected function buildPayload(
        LegacyCourseHtmlParser $parser,
        CatalogCourse $course,
        string $enHtml,
        string $enPath,
        string $arDir,
    ): array {
        $payload = ['course_id' => $course->id];
        $payload['meta_description_en'] = $parser->extractMetaDescription($enHtml);

        foreach ($parser->extractTabs($enHtml) as $field => $content) {
            $payload[$field.'_en'] = $content;
        }

        $arHtml = $this->readArabicHtml($parser, $arDir, $course);
        if ($arHtml) {
            $payload['meta_description_ar'] = $parser->extractMetaDescription($arHtml);

            foreach ($parser->extractTabs($arHtml) as $field => $content) {
                $payload[$field.'_ar'] = $content;
            }
        }

        return $payload;
    }

    protected function readArabicHtml(LegacyCourseHtmlParser $parser, string $arDir, CatalogCourse $course): ?string
    {
        $path = $parser->resolveArabicHtmlPath($arDir, $course);

        return $path && is_readable($path) ? file_get_contents($path) : null;
    }

    /** @return list<string> */
    protected function resolveEnFiles(string $enDir, ?string $courseFilter): array
    {
        if (! $courseFilter) {
            return File::glob($enDir.'/*.html') ?: [];
        }

        $course = CatalogCourse::query()->find($courseFilter)
            ?? CatalogCourse::query()
                ->where(function ($query) use ($courseFilter) {
                    $query->where('slug', $courseFilter)
                        ->orWhere('slug', $courseFilter.'.html');
                })
                ->first();

        if (! $course) {
            return File::glob($enDir.'/*.html') ?: [];
        }

        $slug = str_replace('.html', '', (string) $course->slug);
        $direct = $enDir.'/'.$slug.'.html';

        if (is_file($direct)) {
            return [$direct];
        }

        foreach (File::glob($enDir.'/*.html') ?: [] as $path) {
            if (str_replace('.html', '', basename($path)) === $slug) {
                return [$path];
            }
        }

        return File::glob($enDir.'/*.html') ?: [];
    }
}
