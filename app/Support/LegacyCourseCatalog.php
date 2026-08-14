<?php

namespace App\Support;

use App\Models\CatalogCourse;
use App\Services\CatalogCourseService;
use Illuminate\Support\Facades\Cache;

class LegacyCourseCatalog
{
    public function find(int $courseId): ?array
    {
        $index = $this->index();

        return $index[$courseId] ?? null;
    }

    /**
     * @return array{
     *   title: string,
     *   image: ?string,
     *   slug: ?string,
     *   url: string,
     *   is_diploma: bool,
     *   type_label: string
     * }
     */
    public function resolveForItem(object $item): array
    {
        $locale = app()->getLocale();
        $courseId = (int) ($item->course_id ?? 0);
        $course = $courseId > 0
            ? CatalogCourse::query()->with('categories:id')->find($courseId)
            : null;

        if ($course) {
            $isDiploma = $course->categories->contains('id', CatalogCourseService::CATEGORY_DIPLOMAS);

            return [
                'title' => $item->course_title ?: $course->displayTitle(),
                'image' => $item->course_image ?: $course->image,
                'slug' => $course->showSlug(),
                'url' => route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]),
                'is_diploma' => $isDiploma,
                'type_label' => $isDiploma ? 'دبلوم' : 'شهادة احترافية',
            ];
        }

        $catalog = $this->find($courseId);
        $slug = $item->course_slug ?? ($catalog['slug'] ?? null);
        $slugClean = $slug ? str_replace('.html', '', (string) $slug) : null;

        return [
            'title' => $item->course_title ?: ($catalog['title'] ?? ('دورة #'.$courseId)),
            'image' => $item->course_image ?: ($catalog['image'] ?? null),
            'slug' => $slugClean,
            'url' => $slugClean
                ? route('courses.show', ['locale' => $locale, 'course' => $slugClean])
                : route('courses.index', ['locale' => $locale]),
            'is_diploma' => false,
            'type_label' => 'برنامج تدريبي',
        ];
    }

    /** @return array<int, array{title: string, image: ?string, slug: ?string}> */
    private function index(): array
    {
        return Cache::remember('legacy_course_catalog', 3600, function () {
            $path = dirname(base_path()).'/New-Platform/courses.html';

            if (! is_readable($path)) {
                return [];
            }

            $html = file_get_contents($path);
            $courses = [];

            preg_match_all(
                '/<div class="trainingCard gigs-grid[\s\S]*?cart-form-(\d+)[\s\S]*?<img src="(\.\/assets\/[^"]+)"[\s\S]*?<div class="gigs-title">\s*<h3>\s*<a href="(\.\/[^"]+)">\s*([\s\S]*?)\s*<\/a>/',
                $html,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $id = (int) $match[1];
                $courses[$id] = [
                    'title' => trim(html_entity_decode(strip_tags($match[4]))),
                    'image' => $match[2],
                    'slug' => ltrim($match[3], './'),
                ];
            }

            return $courses;
        });
    }
}
