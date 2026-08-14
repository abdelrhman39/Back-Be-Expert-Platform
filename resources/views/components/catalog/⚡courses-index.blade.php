<?php

use App\Services\CatalogCourseService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app-inner')]
#[Title('الدورات | مركز التعلم المستمر')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'sort')]
    public string $sort = 'latest';

    /** @var array<int, string> */
    #[Url(as: 'categories')]
    public array $categoryFilters = [];

    /** @var array<int, string> */
    #[Url(as: 'fields')]
    public array $fieldFilters = [];

    /** @var array<int, string> */
    #[Url(as: 'types')]
    public array $courseTypes = [];

    #[Url(as: 'min')]
    public ?int $minPrice = null;

    #[Url(as: 'max')]
    public ?int $maxPrice = null;

    public function mount(): void
    {
        $this->categoryFilters = $this->normalizeFilterValues($this->categoryFilters);
        $this->fieldFilters = $this->normalizeFilterValues($this->fieldFilters);
    }

    /** @param  array<int, string>  $values */
    protected function normalizeFilterValues(array $values): array
    {
        return array_values(array_filter($values, fn ($value) => $value !== '' && $value !== null));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilters(): void
    {
        $this->resetPage();
    }

    public function updatedFieldFilters(): void
    {
        $this->resetPage();
    }

    public function updatedCourseTypes(): void
    {
        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function priceRange(): array
    {
        return app(CatalogCourseService::class)->publishedPriceRange();
    }

    #[Computed]
    public function filterCategories()
    {
        return app(CatalogCourseService::class)->sidebarCategories();
    }

    #[Computed]
    public function filterFields()
    {
        return app(CatalogCourseService::class)->sidebarFields();
    }

    #[Computed]
    public function filterCourseTypes(): array
    {
        return app(CatalogCourseService::class)->availableCourseTypeOptions();
    }

    #[Computed]
    public function sortOptions(): array
    {
        return app(CatalogCourseService::class)->sortOptions();
    }

    #[Computed]
    public function courses()
    {
        return app(CatalogCourseService::class)->paginatePublished(
            search: trim($this->search),
            sort: $this->sort,
            courseTypes: $this->courseTypes,
            categoryIds: $this->categoryFilters,
            fieldIds: $this->fieldFilters,
            minPrice: $this->minPrice,
            maxPrice: $this->maxPrice,
        );
    }
};
?>

@php
    $locale = app()->getLocale();
    $diplomasOnly = in_array((string) \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS, array_map('strval', $this->categoryFilters), true)
        && count($this->categoryFilters) === 1;
@endphp

<div>
    <div class="breadcrumb-bar">
        <div class="breadcrumb-img">
            <div class="breadcrumb-left">
                <img src="{{ static_asset('assets/banner-bg-03.png') }}" alt="img">
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <nav aria-label="breadcrumb" class="page-breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $diplomasOnly ? 'الدبلومات' : 'الدورات' }}</li>
                        </ol>
                    </nav>
                    <h1 class="breadcrumb-title">{{ $diplomasOnly ? 'الدبلومات' : 'الدورات' }}</h1>

                    <div class="service-sliders owl-carousel owl-rtl owl-loaded owl-drag">
                        <div class="owl-stage-outer"><div class="owl-stage"></div></div>
                        <div class="owl-dots disabled"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content pb-0">
        <div class="container">
            <div class="title-section">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="title-header">
                            <h2>{{ $diplomasOnly ? 'الدبلومات' : 'الدورات' }}</h2>
                            @if ($diplomasOnly)
                                <p class="text-muted mb-0 mt-1" style="font-size:.92rem;">برامج أكاديمية معتمدة — تفاصيل كاملة وتسجيل مباشر</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="title-filter">
                            <div class="form-group search-group">
                                <a href="javascript:void(0);">
                                    <span class="search-icon"><i class="feather-search"></i></span>
                                </a>
                                <input type="text" class="form-control" placeholder="بحث" wire:model.live.debounce.400ms="search" value="">
                            </div>
                            <div class="search-filter-selected">
                                <div class="form-group">
                                    <select class="form-control select" wire:model.live="sort">
                                        @foreach ($this->sortOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="service-gigs">
                <div class="row">
                    @include('partials.catalog.courses-index-sidebar', [
                        'priceRange' => $this->priceRange,
                        'categories' => $this->filterCategories,
                        'fields' => $this->filterFields,
                        'courseTypes' => $this->filterCourseTypes,
                    ])

                    <div class="col-lg-8">
                        <div class="row">
                            @forelse ($this->courses as $course)
                                @php
                                    $isDiplomaCard = $course->categories->contains(
                                        'id',
                                        \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS
                                    );
                                @endphp
                                <div class="col-xl-6 col-md-6" wire:key="course-card-{{ $course->id }}">
                                    @if ($isDiplomaCard)
                                        @include('partials.catalog.course-card-diploma', ['course' => $course])
                                    @else
                                        @include('partials.catalog.course-card-gigs', ['course' => $course])
                                    @endif
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <p class="mb-0">لا توجد دورات مطابقة لبحثك.</p>
                                    </div>
                                </div>
                            @endforelse

                            {{ $this->courses->links('partials.catalog.courses-pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalog-public.css') }}">
@endpush
