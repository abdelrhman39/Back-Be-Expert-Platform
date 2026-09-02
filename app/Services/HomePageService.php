<?php

namespace App\Services;

use App\Models\Fellowship;
use Illuminate\Support\Collection;

class HomePageService
{
    public function __construct(
        private readonly CatalogCourseService $catalog,
    ) {}

    /** @return Collection<int, \App\Models\CatalogField> */
    public function popularFields(int $limit = 8): Collection
    {
        return $this->catalog->homePopularFields($limit);
    }

    /** @return Collection<int, \App\Models\CatalogCourse> */
    public function professionalCertificates(int $limit = 12): Collection
    {
        $courses = $this->catalog->featuredByCategory(
            CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES,
            $limit,
        );

        return $courses->isNotEmpty() ? $courses : $this->catalog->featured($limit);
    }

    /** @return Collection<int, \App\Models\CatalogCourse> */
    public function diplomas(int $limit = 12): Collection
    {
        $courses = $this->catalog->featuredByCategory(
            CatalogCourseService::CATEGORY_DIPLOMAS,
            $limit,
        );

        return $courses;
    }

    /** @return Collection<int, Fellowship> */
    public function fellowships(int $limit = 8): Collection
    {
        return Fellowship::query()
            ->where('status', 'open')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Compact hero metrics for the campus identity panel.
     *
     * @return list<array{value: ?string, suffix: string, label: string, icon: string}>
     */
    public function heroMetrics(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $isEn = $locale === 'en';

        $programs = $this->catalog->publishedCount();
        $fields = $this->catalog->homePopularFields(12)->count();
        if ($fields === 0) {
            $fields = $this->catalog->sidebarFields()->count();
        }
        $certificates = $this->catalog->publishedCountByCategory(
            CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES,
        );

        $items = [];

        if ($programs > 0) {
            $items[] = [
                'value' => (string) $programs,
                'suffix' => $programs >= 10 ? '+' : '',
                'label' => $isEn ? 'Training programs' : 'برنامج تدريبي',
                'icon' => 'fa-solid fa-book-open',
            ];
        }

        if ($fields > 0) {
            $items[] = [
                'value' => (string) $fields,
                'suffix' => '',
                'label' => $isEn ? 'Training fields' : 'مجال تدريبي',
                'icon' => 'fa-solid fa-layer-group',
            ];
        }

        if ($certificates > 0) {
            $items[] = [
                'value' => (string) $certificates,
                'suffix' => $certificates >= 10 ? '+' : '',
                'label' => $isEn ? 'Professional certificates' : 'شهادة احترافية',
                'icon' => 'fa-solid fa-certificate',
            ];
        }

        $fallbacks = [
            [
                'value' => null,
                'suffix' => '',
                'label' => $isEn ? 'Verified certificates' : 'شهادات قابلة للتحقق',
                'icon' => 'fa-solid fa-certificate',
            ],
            [
                'value' => null,
                'suffix' => '',
                'label' => $isEn ? 'Accredited diplomas' : 'دبلومات معتمدة',
                'icon' => 'fa-solid fa-graduation-cap',
            ],
            [
                'value' => null,
                'suffix' => '',
                'label' => $isEn ? 'Academic affiliation' : 'انتماء أكاديمي',
                'icon' => 'fa-solid fa-building-columns',
            ],
        ];

        foreach ($fallbacks as $fallback) {
            if (count($items) >= 3) {
                break;
            }

            $items[] = $fallback;
        }

        return array_slice($items, 0, 3);
    }
}
