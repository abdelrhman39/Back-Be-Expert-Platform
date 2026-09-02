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

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->sort = 'latest';
        $this->categoryFilters = [];
        $this->fieldFilters = [];
        $this->courseTypes = [];
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->resetPage();
    }

    public function removeCategoryFilter(string $id): void
    {
        $this->categoryFilters = $this->normalizeFilterValues(array_filter(
            $this->categoryFilters,
            fn ($value) => (string) $value !== $id
        ));
        $this->resetPage();
    }

    public function removeFieldFilter(string $id): void
    {
        $this->fieldFilters = $this->normalizeFilterValues(array_filter(
            $this->fieldFilters,
            fn ($value) => (string) $value !== $id
        ));
        $this->resetPage();
    }

    public function removeTypeFilter(string $id): void
    {
        $this->courseTypes = $this->normalizeFilterValues(array_filter(
            $this->courseTypes,
            fn ($value) => (string) $value !== $id
        ));
        $this->resetPage();
    }

    public function toggleFieldFilter(string $id): void
    {
        $current = array_map('strval', $this->fieldFilters);
        $this->fieldFilters = in_array($id, $current, true)
            ? $this->normalizeFilterValues(array_filter($current, fn ($value) => $value !== $id))
            : $this->normalizeFilterValues([...$current, $id]);
        $this->resetPage();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return trim($this->search) !== ''
            || $this->categoryFilters !== []
            || $this->fieldFilters !== []
            || $this->courseTypes !== []
            || $this->minPrice !== null
            || $this->maxPrice !== null;
    }

    #[Computed]
    public function catalogIsEmpty(): bool
    {
        return app(CatalogCourseService::class)->publishedCount() === 0;
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
    public function popularFields()
    {
        return app(CatalogCourseService::class)->homePopularFields(8);
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
    $isEn = $locale === 'en';
    $categoryIds = array_map('strval', $this->categoryFilters);
    $fieldIds = array_map('strval', $this->fieldFilters);
    $diplomasOnly = in_array((string) \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS, $categoryIds, true)
        && count($this->categoryFilters) === 1;
    $certificatesOnly = in_array((string) \App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES, $categoryIds, true)
        && count($this->categoryFilters) === 1;
    $listingTitle = $diplomasOnly
        ? ($isEn ? 'Diplomas' : 'الدبلومات')
        : ($certificatesOnly
            ? ($isEn ? 'Professional certificates' : 'الشهادات الاحترافية')
            : ($isEn ? 'Programs' : 'البرامج'));
    $listingLead = $diplomasOnly
        ? ($isEn ? 'Accredited academic diplomas with complete details and direct enrollment.' : 'برامج أكاديمية معتمدة — تفاصيل كاملة وتسجيل مباشر.')
        : ($certificatesOnly
            ? ($isEn ? 'Certified professional tracks aligned with labor-market needs.' : 'مسارات مهنية معتمدة متوافقة مع احتياج سوق العمل.')
            : ($isEn
                ? 'Browse certificates, diplomas, and professional tracks from the Continuing Learning Center.'
                : 'استكشف الشهادات الاحترافية والدبلومات والمسارات المهنية في مركز التعلم المستمر.'));
    $resultTotal = $this->courses->total();
    $resultLabel = $isEn
        ? ($resultTotal === 1 ? '1 program' : $resultTotal.' programs')
        : ($resultTotal === 1 ? 'برنامج واحد' : $resultTotal.' برنامج');
    $pathCards = [
        [
            'icon' => 'fa-award',
            'title' => $isEn ? 'Professional certificates' : 'الشهادات الاحترافية',
            'body' => $isEn ? 'Certified tracks aligned with the labor market.' : 'مسارات مهنية معتمدة وفق احتياج سوق العمل.',
            'url' => route('courses.certificates', ['locale' => $locale]),
            'cta' => $isEn ? 'Browse' : 'تصفّح',
        ],
        [
            'icon' => 'fa-graduation-cap',
            'title' => $isEn ? 'Diplomas' : 'الدبلومات',
            'body' => $isEn ? 'Academic programs with full details and enrollment.' : 'برامج أكاديمية بتفاصيل كاملة وتسجيل مباشر.',
            'url' => route('courses.diplomas', ['locale' => $locale]),
            'cta' => $isEn ? 'Browse' : 'تصفّح',
        ],
        [
            'icon' => 'fa-user-tie',
            'title' => $isEn ? 'Professional fellowships' : 'الزمالات المهنية',
            'body' => $isEn ? 'Specialized tracks with a dedicated application form.' : 'مسارات تخصصية بنموذج تقديم مستقل.',
            'url' => route('fellowships.index', ['locale' => $locale]),
            'cta' => $isEn ? 'View' : 'اطّلع',
        ],
    ];
@endphp

<div class="atelier-catalog">
    <div class="breadcrumb-bar">
        <div class="breadcrumb-img">
            <div class="breadcrumb-left">
                <img src="{{ static_asset(platform_campus_path('aerial')) }}" alt="{{ platform_org() }}">
            </div>
        </div>
        <div class="container">
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['locale' => $locale]) }}">{{ $isEn ? 'Home' : 'الرئيسية' }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $listingTitle }}</li>
                </ol>
            </nav>
            <span class="catalog-hero__eyebrow">{{ platform_org() }}</span>
            <h1 class="breadcrumb-title">{{ $listingTitle }}</h1>
            <p class="catalog-hero__lead">{{ $listingLead }}</p>
        </div>
    </div>

    <div class="catalog-body">
        <div class="container">
            <div class="catalog-toolbar">
                <div class="catalog-toolbar__meta">
                    <span class="catalog-toolbar__count">{{ $resultLabel }}</span>
                    @if ($this->hasActiveFilters)
                        <button type="button" class="catalog-toolbar__clear" wire:click="clearFilters">
                            {{ $isEn ? 'Clear filters' : 'مسح التصفية' }}
                        </button>
                    @endif
                </div>
                <div class="catalog-toolbar__controls">
                    <label class="catalog-search">
                        <i class="feather-search" aria-hidden="true"></i>
                        <input
                            type="search"
                            class="form-control"
                            wire:model.live.debounce.400ms="search"
                            placeholder="{{ $isEn ? 'Search programs…' : 'ابحث عن برنامج…' }}"
                            aria-label="{{ $isEn ? 'Search programs' : 'البحث في البرامج' }}"
                        >
                    </label>
                    <label class="catalog-sort">
                        <span>{{ $isEn ? 'Sort by' : 'ترتيب حسب' }}</span>
                        <select class="form-control" wire:model.live="sort">
                            @foreach ($this->sortOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            @if ($this->popularFields->isNotEmpty())
                <div class="catalog-field-pills" role="list">
                    @foreach ($this->popularFields as $field)
                        <button
                            type="button"
                            class="catalog-field-pills__item{{ in_array((string) $field->id, $fieldIds, true) ? ' is-active' : '' }}"
                            wire:click="toggleFieldFilter('{{ $field->id }}')"
                            wire:key="field-pill-{{ $field->id }}"
                            role="listitem"
                        >
                            {{ $field->displayTitle() }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if ($this->hasActiveFilters)
                <div class="catalog-chips">
                    @if (trim($this->search) !== '')
                        <span class="catalog-chips__item">{{ $isEn ? 'Search' : 'بحث' }}: {{ $this->search }}</span>
                    @endif
                    @foreach ($this->filterCategories as $category)
                        @if (in_array((string) $category->id, $categoryIds, true))
                            <button type="button" class="catalog-chips__item" wire:click="removeCategoryFilter('{{ $category->id }}')" wire:key="chip-cat-{{ $category->id }}">
                                {{ $category->displayTitle() }} <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        @endif
                    @endforeach
                    @foreach ($this->filterFields as $field)
                        @if (in_array((string) $field->id, $fieldIds, true))
                            <button type="button" class="catalog-chips__item" wire:click="removeFieldFilter('{{ $field->id }}')" wire:key="chip-field-{{ $field->id }}">
                                {{ $field->displayTitle() }} <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        @endif
                    @endforeach
                    @foreach ($this->filterCourseTypes as $value => $label)
                        @if (in_array((string) $value, array_map('strval', $this->courseTypes), true))
                            <button type="button" class="catalog-chips__item" wire:click="removeTypeFilter('{{ $value }}')" wire:key="chip-type-{{ $value }}">
                                {{ $label }} <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        @endif
                    @endforeach
                    @if ($this->minPrice !== null || $this->maxPrice !== null)
                        <span class="catalog-chips__item">
                            {{ $isEn ? 'Price' : 'السعر' }}:
                            {{ $this->minPrice !== null ? number_format($this->minPrice) : '—' }}
                            –
                            {{ $this->maxPrice !== null ? number_format($this->maxPrice) : '—' }}
                        </span>
                    @endif
                </div>
            @endif

            @php
                $hasSidebar = $this->filterCategories->isNotEmpty()
                    || $this->filterFields->isNotEmpty()
                    || $this->filterCourseTypes !== []
                    || (int) ($this->priceRange['max'] ?? 0) > 0;
            @endphp

            <div class="service-gigs catalog-layout">
                <div class="row g-4">
                    @include('partials.catalog.courses-index-sidebar', [
                        'priceRange' => $this->priceRange,
                        'categories' => $this->filterCategories,
                        'fields' => $this->filterFields,
                        'courseTypes' => $this->filterCourseTypes,
                        'hasActiveFilters' => $this->hasActiveFilters,
                    ])

                    <div class="{{ $hasSidebar ? 'col-lg-8' : 'col-12' }}">
                        <div class="catalog-results" wire:loading.class="is-loading">
                            <div class="row g-4">
                                @forelse ($this->courses as $course)
                                    <div class="col-xl-6 col-md-6" wire:key="course-card-{{ $course->id }}">
                                        @include('partials.catalog.course-card-diploma', ['course' => $course])
                                    </div>
                                @empty
                                    <div class="col-12">
                                        @if ($this->catalogIsEmpty)
                                            <div class="catalog-empty">
                                                <div class="catalog-empty__icon" aria-hidden="true">
                                                    <i class="fa-solid fa-graduation-cap"></i>
                                                </div>
                                                <h2 class="catalog-empty__title">{{ $isEn ? 'Programs will be published soon' : 'سيتم نشر البرامج قريباً' }}</h2>
                                                <p class="catalog-empty__body">
                                                    {{ $isEn
                                                        ? 'The Continuing Learning Center is preparing certificate and diploma tracks. Explore the available paths or contact us for upcoming cohorts.'
                                                        : 'مركز التعلم المستمر يجهّز مسارات الشهادات والدبلومات. تصفّح المسارات المتاحة أو تواصل معنا لمعرفة الدفعات القادمة.' }}
                                                </p>
                                                <div class="catalog-empty__actions">
                                                    <a class="catalog-btn catalog-btn--solid" href="{{ route('contact', ['locale' => $locale]) }}">{{ $isEn ? 'Contact us' : 'تواصل معنا' }}</a>
                                                    <a class="catalog-btn catalog-btn--ghost" href="{{ route('about', ['locale' => $locale]) }}">{{ $isEn ? 'About the center' : 'تعرّف على المنصة' }}</a>
                                                </div>
                                                <div class="catalog-paths">
                                                    @foreach ($pathCards as $card)
                                                        <a class="catalog-paths__card" href="{{ $card['url'] }}">
                                                            <span class="catalog-paths__icon" aria-hidden="true"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                                                            <strong>{{ $card['title'] }}</strong>
                                                            <span>{{ $card['body'] }}</span>
                                                            <em>{{ $card['cta'] }}</em>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="catalog-empty catalog-empty--filtered">
                                                <div class="catalog-empty__icon" aria-hidden="true">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </div>
                                                <h2 class="catalog-empty__title">{{ $isEn ? 'No programs match your search' : 'لا توجد برامج مطابقة لبحثك' }}</h2>
                                                <p class="catalog-empty__body">
                                                    {{ $isEn
                                                        ? 'Try different keywords, or clear the filters to see the full catalog.'
                                                        : 'جرّب كلمات بحث مختلفة، أو امسح عوامل التصفية لعرض كامل الكتالوج.' }}
                                                </p>
                                                <div class="catalog-empty__actions">
                                                    <button type="button" class="catalog-btn catalog-btn--solid" wire:click="clearFilters">{{ $isEn ? 'Clear filters' : 'مسح التصفية' }}</button>
                                                    <a class="catalog-btn catalog-btn--ghost" href="{{ route('contact', ['locale' => $locale]) }}">{{ $isEn ? 'Contact us' : 'تواصل معنا' }}</a>
                                                </div>
                                            </div>
                                        @endif
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
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalog-public.css') }}?v=7">
@endpush
