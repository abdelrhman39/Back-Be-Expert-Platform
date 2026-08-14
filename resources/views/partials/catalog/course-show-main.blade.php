@php
    $defaultTab = $course->details?->defaultTabKey();
    $tabs = $course->details?->availableTabs() ?? [];
@endphp

<div class="col-lg-8 course-show-tabs">
    <div class="tab-content">
        <div class="tab-pane fade active show" id="main_about" role="tabpanel">
            @if ($tabs !== [])
                <div class="listing-tab mt-3 listing-tab--with-scroll-controls" id="course-section-tabs">
                    <button type="button" class="listing-tab-scroll-btn listing-tab-scroll-btn--inline-start" aria-label="التمرير نحو بداية التبويبات">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg>
                    </button>
                    <button type="button" class="listing-tab-scroll-btn listing-tab-scroll-btn--inline-end" aria-label="التمرير نحو نهاية التبويبات">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                    </button>
                    <div class="listing-slider">
                        <ul class="nav nav-tabs" role="tablist">
                            @foreach ($tabs as $key => $tab)
                                <li class="nav-item" role="presentation">
                                    <a href="#{{ $tab['html_id'] }}"
                                       @class(['nav-link', 'active' => $key === $defaultTab])
                                       role="tab"
                                       @if ($key === $defaultTab) aria-selected="true" @else aria-selected="false" tabindex="-1" @endif>
                                        <div class="tabs-icon">
                                            @include('partials.catalog.course-tab-nav-icon', ['key' => $key])
                                        </div>
                                        {{ $tab['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="slider-card singleImage mb-4 mt-3">
                    <div class="slider service-slider">
                        <div class="service-img-wrap">
                            <img class="img-fluid" src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}">
                        </div>
                    </div>
                </div>

                <div class="tab-content mt-2 course-sections">
                    @foreach ($tabs as $key => $tab)
                        <section @class(['tab-pane fade course-section', 'active show' => $key === $defaultTab]) id="{{ $tab['html_id'] }}" role="tabpanel" aria-labelledby="tab-{{ $tab['html_id'] }}">
                            <div class="service-wrap">
                                <h3>
                                    @include('partials.catalog.course-tab-heading-icon')
                                    {{ $tab['label'] }}
                                </h3>
                                {!! $tab['content'] !!}
                            </div>
                        </section>
                    @endforeach
                </div>
            @else
                <div class="slider-card singleImage mb-4 mt-3">
                    <div class="slider service-slider">
                        <div class="service-img-wrap">
                            <img class="img-fluid" src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}">
                        </div>
                    </div>
                </div>
                <div class="service-wrap">
                    <p>تفاصيل هذه الدورة قيد الإعداد. تواصل معنا للاستفسار.</p>
                </div>
            @endif
        </div>
    </div>
</div>
