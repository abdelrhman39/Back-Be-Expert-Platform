<?php

namespace App\Services;

use App\Models\CatalogCategory;
use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogField;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogCourseService
{
    public const CATEGORY_PROFESSIONAL_CERTIFICATES = 12;

    public const CATEGORY_DIPLOMAS = 14;

    public function publishedQuery(): Builder
    {
        return CatalogCourse::query()
            ->with(['details', 'categories'])
            ->where('status', 'published');
    }

    public function paginatePublished(
        string $search = '',
        string $sort = 'latest',
        array $courseTypes = [],
        array $categoryIds = [],
        array $fieldIds = [],
        ?int $minPrice = null,
        ?int $maxPrice = null,
        int $perPage = 12,
    ): LengthAwarePaginator {
        $query = $this->applyCatalogFilters(
            $this->publishedQuery(),
            $search,
            $courseTypes,
            $categoryIds,
            $fieldIds,
            $minPrice,
            $maxPrice,
        );

        $query = match ($sort) {
            'oldest' => $query->orderBy('id'),
            'price_asc' => $query->orderByRaw('COALESCE(price_online, price_onsite) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(price_online, price_onsite) desc'),
            default => $query->orderByDesc('is_featured')->orderByDesc('id'),
        };

        return $query->paginate($perPage);
    }

    /** @return Collection<int, CatalogCategory> */
    public function sidebarCategories(): Collection
    {
        return CatalogCategory::query()
            ->whereHas('courses', fn (Builder $q) => $q->where('status', 'published'))
            ->withCount(['courses as courses_count' => fn (Builder $q) => $q->where('status', 'published')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, CatalogField> */
    public function sidebarFields(): Collection
    {
        return CatalogField::query()
            ->whereHas('courses', fn (Builder $q) => $q->where('status', 'published'))
            ->withCount(['courses as courses_count' => fn (Builder $q) => $q->where('status', 'published')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, CatalogField> */
    public function homePopularFields(int $limit = 6): Collection
    {
        return CatalogField::query()
            ->where('home_visible', true)
            ->whereHas('courses', fn (Builder $q) => $q->where('status', 'published'))
            ->withCount(['courses as courses_count' => fn (Builder $q) => $q->where('status', 'published')])
            ->orderByDesc('courses_count')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /** @return \Illuminate\Support\Collection<int, CatalogCourse> */
    public function featuredByCategory(int $categoryId, int $limit = 12)
    {
        return $this->publishedQuery()
            ->whereHas('categories', fn (Builder $q) => $q->whereKey($categoryId))
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** @return array<int, string> */
    public function courseTypeOptions(): array
    {
        return [
            'online' => 'عن بعد',
            'offline' => 'حضوري',
            'self_learning' => 'التعلم الذاتي',
        ];
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return [
            'latest' => 'الأحدث',
            'oldest' => 'الأقدم',
            'price_asc' => 'السعر: الأقل',
            'price_desc' => 'السعر: الأعلى',
        ];
    }

    /** @return array<string, string> */
    public function availableCourseTypeOptions(): array
    {
        $options = $this->courseTypeOptions();
        $base = $this->publishedQuery();
        $available = [];

        if ((clone $base)->whereNotNull('price_online')->exists()) {
            $available['online'] = $options['online'];
        }

        if ((clone $base)->whereNotNull('price_onsite')->exists()) {
            $available['offline'] = $options['offline'];
        }

        if ((clone $base)->where('is_self_learning', true)->exists()) {
            $available['self_learning'] = $options['self_learning'];
        }

        return $available;
    }

    /** @return array{min: int, max: int} */
    public function publishedPriceRange(): array
    {
        $row = $this->publishedQuery()
            ->selectRaw('MIN(COALESCE(price_online, price_onsite)) as min_price, MAX(COALESCE(price_online, price_onsite)) as max_price')
            ->first();

        return [
            'min' => (int) ($row->min_price ?? 0),
            'max' => (int) ($row->max_price ?? 0),
        ];
    }

    protected function applyCatalogFilters(
        Builder $query,
        string $search = '',
        array $courseTypes = [],
        array $categoryIds = [],
        array $fieldIds = [],
        ?int $minPrice = null,
        ?int $maxPrice = null,
    ): Builder {
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        if ($categoryIds !== []) {
            $ids = array_map('intval', $categoryIds);
            $query->whereHas('categories', fn (Builder $q) => $q->whereIn('catalog_categories.id', $ids));
        }

        if ($fieldIds !== []) {
            $ids = array_map('intval', $fieldIds);
            $query->whereHas('fields', fn (Builder $q) => $q->whereIn('catalog_fields.id', $ids));
        }

        if ($courseTypes !== []) {
            $query->where(function (Builder $q) use ($courseTypes) {
                foreach ($courseTypes as $type) {
                    $q->orWhere(function (Builder $sub) use ($type) {
                        match ($type) {
                            'online' => $sub->whereNotNull('price_online'),
                            'offline' => $sub->whereNotNull('price_onsite'),
                            'self_learning' => $sub->where('is_self_learning', true),
                            default => null,
                        };
                    });
                }
            });
        }

        if ($minPrice !== null) {
            $query->whereRaw('COALESCE(price_online, price_onsite) >= ?', [$minPrice]);
        }

        if ($maxPrice !== null) {
            $query->whereRaw('COALESCE(price_online, price_onsite) <= ?', [$maxPrice]);
        }

        return $query;
    }

    public function findPublishedBySlug(string $slug): ?CatalogCourse
    {
        $slug = trim($slug);
        $candidates = array_unique([
            $slug,
            str_ends_with($slug, '.html') ? $slug : $slug.'.html',
            str_replace('.html', '', $slug),
        ]);

        return $this->publishedQuery()
            ->whereIn('slug', $candidates)
            ->first();
    }

    /** @return \Illuminate\Support\Collection<int, CatalogCourseLesson> */
    public function previewLessons(CatalogCourse $course)
    {
        return CatalogCourseLesson::query()
            ->where('is_preview', true)
            ->where('status', 'published')
            ->whereHas('module', fn (Builder $q) => $q
                ->where('course_id', $course->id)
                ->where('status', 'published'))
            ->with('module')
            ->orderBy('sort_order')
            ->limit(5)
            ->get();
    }

    public function contentStats(CatalogCourse $course): array
    {
        $modules = $course->modules()->where('status', 'published')->count();
        $lessons = CatalogCourseLesson::query()
            ->where('status', 'published')
            ->whereHas('module', fn (Builder $q) => $q
                ->where('course_id', $course->id)
                ->where('status', 'published'))
            ->count();

        $duration = (int) CatalogCourseLesson::query()
            ->whereHas('module', fn (Builder $q) => $q
                ->where('course_id', $course->id)
                ->where('status', 'published'))
            ->where('status', 'published')
            ->sum('duration_minutes');

        return compact('modules', 'lessons', 'duration');
    }

    /** @return \Illuminate\Support\Collection<int, CatalogCourse> */
    public function featured(int $limit = 12)
    {
        return $this->publishedQuery()
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** @return \Illuminate\Support\Collection<int, CatalogCourse> */
    public function related(CatalogCourse $course, int $limit = 12)
    {
        return $this->publishedQuery()
            ->whereKeyNot($course->id)
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** @return array{hours: ?int, days: ?int} */
    public function trainingSchedule(CatalogCourse $course): array
    {
        if ($course->duration_hours || $course->duration_days) {
            return [
                'hours' => $course->duration_hours ? (int) $course->duration_hours : null,
                'days' => $course->duration_days ? (int) $course->duration_days : null,
            ];
        }

        $minutes = $this->contentStats($course)['duration'];

        if ($minutes <= 0) {
            return ['hours' => null, 'days' => null];
        }

        $hours = max(1, (int) round($minutes / 60));
        $days = max(1, (int) ceil($hours / 8));

        return compact('hours', 'days');
    }

    public function findPreviewLesson(CatalogCourse $course, int $lessonId): ?CatalogCourseLesson
    {
        return CatalogCourseLesson::query()
            ->whereKey($lessonId)
            ->where('is_preview', true)
            ->where('status', 'published')
            ->whereHas('module', fn (Builder $q) => $q
                ->where('course_id', $course->id)
                ->where('status', 'published'))
            ->with('module')
            ->first();
    }
}
