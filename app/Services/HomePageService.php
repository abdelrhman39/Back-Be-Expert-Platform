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
    public function popularFields(int $limit = 6): Collection
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
    public function diplomas(int $limit = 6): Collection
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
}
