@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $visibleCategories = $categories->where('sidebar_visible', true);
    $hiddenCategories = $categories->where('sidebar_visible', false);
    $visibleFields = $fields->where('sidebar_visible', true);
    $hiddenFields = $fields->where('sidebar_visible', false);
    $showPrice = (int) ($priceRange['max'] ?? 0) > 0;
    $hasSidebarContent = $categories->isNotEmpty() || $fields->isNotEmpty() || $courseTypes !== [] || $showPrice;
@endphp

@if ($hasSidebarContent)
<div class="col-lg-4 theiaStickySidebar" wire:ignore.self>
    <div class="theiaStickySidebar">
        <aside class="sidebar-widget catalog-filter" aria-label="{{ $isEn ? 'Filter programs' : 'تصفية البرامج' }}">
            <div class="catalog-filter__head">
                <h2>{{ $isEn ? 'Filter results' : 'تصفية النتائج' }}</h2>
                @if ($hasActiveFilters)
                    <button type="button" class="catalog-filter__reset" wire:click="clearFilters">
                        {{ $isEn ? 'Reset' : 'مسح الكل' }}
                    </button>
                @endif
            </div>

            <div class="sidebar-body p-0">
                @if ($categories->isNotEmpty())
                    <div class="collapse-card">
                        <h3 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#categories" aria-expanded="true">
                                {{ $isEn ? 'Categories' : 'الأقسام' }}
                            </a>
                        </h3>
                        <div id="categories" class="collapse show">
                            <div class="collapse-body">
                                <ul class="checkbox-list">
                                    @foreach ($visibleCategories as $category)
                                        <li wire:key="category-filter-{{ $category->id }}">
                                            <label class="custom_check">
                                                <input type="checkbox" wire:model.live="categoryFilters" value="{{ $category->id }}" id="category-{{ $category->id }}">
                                                <span class="checkmark"></span>
                                                <span class="checked-title">{{ $category->displayTitle() }}</span>
                                                @if (($category->courses_count ?? 0) > 0)
                                                    <span class="catalog-filter__count">{{ $category->courses_count }}</span>
                                                @endif
                                            </label>
                                        </li>
                                    @endforeach
                                    @if ($hiddenCategories->isNotEmpty())
                                        <li>
                                            <a href="javascript:void(0);" class="viewall-button-one" data-show_less="{{ $isEn ? 'Show less' : 'عرض أقل' }}" data-show_all="{{ $isEn ? 'Show all' : 'عرض الكل' }}"><span>{{ $isEn ? 'Show all' : 'عرض الكل' }}</span></a>
                                        </li>
                                        <li>
                                            <div class="view-content">
                                                <div class="viewall-one" style="display: none;">
                                                    <ul>
                                                        @foreach ($hiddenCategories as $category)
                                                            <li wire:key="category-filter-hidden-{{ $category->id }}">
                                                                <label class="custom_check">
                                                                    <input type="checkbox" wire:model.live="categoryFilters" value="{{ $category->id }}" id="category-{{ $category->id }}">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">{{ $category->displayTitle() }}</span>
                                                                    @if (($category->courses_count ?? 0) > 0)
                                                                        <span class="catalog-filter__count">{{ $category->courses_count }}</span>
                                                                    @endif
                                                                </label>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($fields->isNotEmpty())
                    <div class="collapse-card">
                        <h3 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#field" aria-expanded="true">
                                {{ $isEn ? 'Fields' : 'المجالات' }}
                            </a>
                        </h3>
                        <div id="field" class="collapse show">
                            <div class="collapse-body">
                                <ul class="checkbox-list">
                                    @foreach ($visibleFields as $field)
                                        <li wire:key="field-filter-{{ $field->id }}">
                                            <label class="custom_check">
                                                <input type="checkbox" wire:model.live="fieldFilters" value="{{ $field->id }}" id="field-{{ $field->id }}">
                                                <span class="checkmark"></span>
                                                <span class="checked-title">{{ $field->displayTitle() }}</span>
                                                @if (($field->courses_count ?? 0) > 0)
                                                    <span class="catalog-filter__count">{{ $field->courses_count }}</span>
                                                @endif
                                            </label>
                                        </li>
                                    @endforeach
                                    @if ($hiddenFields->isNotEmpty())
                                        <li>
                                            <a href="javascript:void(0);" class="viewall-button-one" data-show_less="{{ $isEn ? 'Show less' : 'عرض أقل' }}" data-show_all="{{ $isEn ? 'Show all' : 'عرض الكل' }}"><span>{{ $isEn ? 'Show all' : 'عرض الكل' }}</span></a>
                                        </li>
                                        <li>
                                            <div class="view-content">
                                                <div class="viewall-one" style="display: none;">
                                                    <ul>
                                                        @foreach ($hiddenFields as $field)
                                                            <li wire:key="field-filter-hidden-{{ $field->id }}">
                                                                <label class="custom_check">
                                                                    <input type="checkbox" wire:model.live="fieldFilters" value="{{ $field->id }}" id="field-{{ $field->id }}">
                                                                    <span class="checkmark"></span>
                                                                    <span class="checked-title">{{ $field->displayTitle() }}</span>
                                                                    @if (($field->courses_count ?? 0) > 0)
                                                                        <span class="catalog-filter__count">{{ $field->courses_count }}</span>
                                                                    @endif
                                                                </label>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($courseTypes !== [])
                    <div class="collapse-card">
                        <h3 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#types" aria-expanded="true">
                                {{ $isEn ? 'Delivery' : 'طريقة الحضور' }}
                            </a>
                        </h3>
                        <div id="types" class="collapse show">
                            <div class="collapse-body">
                                <ul class="checkbox-list">
                                    @foreach ($courseTypes as $value => $label)
                                        <li wire:key="type-filter-{{ $value }}">
                                            <label class="custom_check">
                                                <input type="checkbox" wire:model.live="courseTypes" value="{{ $value }}">
                                                <span class="checkmark"></span>
                                                <span class="checked-title">{{ $label }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($showPrice)
                    <div class="collapse-card">
                        <h3 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#budget" aria-expanded="true">
                                {{ $isEn ? 'Price' : 'السعر' }}
                            </a>
                        </h3>
                        <div id="budget" class="collapse show">
                            <div class="collapse-body">
                                <p class="catalog-filter__range">
                                    {{ number_format($priceRange['min']) }}
                                    –
                                    {{ number_format($priceRange['max']) }}
                                    @include('partials.catalog.sar-icon')
                                </p>
                                <div class="catalog-filter__prices">
                                    <input type="number" min="0" class="form-control" wire:model.live.debounce.500ms="minPrice" placeholder="{{ $isEn ? 'Min' : 'الأدنى' }}" aria-label="{{ $isEn ? 'Minimum price' : 'السعر الأدنى' }}">
                                    <input type="number" min="0" class="form-control" wire:model.live.debounce.500ms="maxPrice" placeholder="{{ $isEn ? 'Max' : 'الأعلى' }}" aria-label="{{ $isEn ? 'Maximum price' : 'السعر الأعلى' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-primary w-100 catalog-filter__apply" wire:click="applyFilters">
                <i class="fa-solid fa-filter" aria-hidden="true"></i>{{ $isEn ? 'Apply filters' : 'تصفية' }}
            </button>
        </aside>
    </div>
</div>
@endif
