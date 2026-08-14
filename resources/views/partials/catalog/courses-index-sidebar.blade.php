@php
    $locale = app()->getLocale();
    $visibleCategories = $categories->where('sidebar_visible', true);
    $hiddenCategories = $categories->where('sidebar_visible', false);
    $visibleFields = $fields->where('sidebar_visible', true);
    $hiddenFields = $fields->where('sidebar_visible', false);
@endphp

<div class="col-lg-4 theiaStickySidebar" wire:ignore.self>
    <div class="theiaStickySidebar">
        <div class="sidebar-widget">
            <div class="sidebar-body p-0">
                @if ($categories->isNotEmpty())
                    <div class="collapse-card">
                        <h4 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#categories" aria-expanded="true">
                                <img src="{{ static_asset('assets/category-icon.svg') }}" alt="icon"> الأقسام
                            </a>
                        </h4>
                        <div id="categories" class="collapse show">
                            <div class="collapse-body">
                                <ul class="checkbox-list">
                                    @foreach ($visibleCategories as $category)
                                        <li wire:key="category-filter-{{ $category->id }}">
                                            <label class="custom_check">
                                                <input type="checkbox" wire:model.live="categoryFilters" value="{{ $category->id }}" id="category-{{ $category->id }}">
                                                <span class="checkmark"></span>
                                                <span class="checked-title">{{ $category->displayTitle() }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                    @if ($hiddenCategories->isNotEmpty())
                                        <li>
                                            <a href="javascript:void(0);" class="viewall-button-one" data-show_less="عرض أقل" data-show_all="عرض الكل"><span>عرض الكل</span></a>
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
                        <h4 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#field" aria-expanded="true">
                                <img src="{{ static_asset('assets/category-icon.svg') }}" alt="icon"> المجالات
                            </a>
                        </h4>
                        <div id="field" class="collapse show">
                            <div class="collapse-body">
                                <ul class="checkbox-list">
                                    @foreach ($visibleFields as $field)
                                        <li wire:key="field-filter-{{ $field->id }}">
                                            <label class="custom_check">
                                                <input type="checkbox" wire:model.live="fieldFilters" value="{{ $field->id }}" id="field-{{ $field->id }}">
                                                <span class="checkmark"></span>
                                                <span class="checked-title">{{ $field->displayTitle() }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                    @if ($hiddenFields->isNotEmpty())
                                        <li>
                                            <a href="javascript:void(0);" class="viewall-button-one" data-show_less="عرض أقل" data-show_all="عرض الكل"><span>عرض الكل</span></a>
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
                        <h4 class="card-title">
                            <a class="" data-bs-toggle="collapse" href="#types" aria-expanded="true">
                                <img src="{{ static_asset('assets/category-icon.svg') }}" alt="icon"> أنواع
                            </a>
                        </h4>
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

                <div class="collapse-card">
                    <h4 class="card-title">
                        <a class="" data-bs-toggle="collapse" href="#budget" aria-expanded="true">
                            <img src="{{ static_asset('assets/money-icon.svg') }}" alt="icon">
                            السعر
                        </a>
                    </h4>
                    <div id="budget" class="collapse show">
                        <div class="collapse-body">
                            <div class="d-flex gap-2 align-items-center">
                                <span class="text-dark">السعر :</span>
                                {{ number_format($priceRange['min']) }} : {{ number_format($priceRange['max']) }}
                                @include('partials.catalog.sar-icon')
                            </div>
                            <div class="form-group search-group">
                                <input type="number" min="0" class="form-control" wire:model.live.debounce.500ms="minPrice" placeholder="السعر الأدنى">
                            </div>
                            <div class="form-group search-group">
                                <input type="number" min="0" class="form-control" wire:model.live.debounce.500ms="maxPrice" placeholder="السعر الأعلى">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary w-100" wire:click="$refresh">
                <i class="fa-solid fa-caret-right"></i>تصفية
            </button>
        </div>
    </div>
</div>
