@php
    $locale = app()->getLocale();
    $catalog = app(\App\Services\CatalogCourseService::class);
    $schedule = $catalog->trainingSchedule($course);
    $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $priceValue = $course->displayPriceValue();
    $pricesJson = json_encode(array_filter([
        'online' => $course->price_online,
        'onsite' => $course->price_onsite,
    ]));
    $deliveryType = $course->delivery_type === 'onsite' ? 'offline' : 'online';
    $deliveryLabel = $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري';
    $displayPrice = $priceValue !== null ? number_format($priceValue, 0) : null;
    $category = $course->primaryCategory();
    $enrollUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]).'#course-enroll';
@endphp

<div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    <div class="gigs-img">
        <a href="{{ $showUrl }}">
            <img src="{{ $course->posterUrl() }}" class="img-fluid" alt="{{ $course->displayTitle() }}" loading="lazy">
        </a>
    </div>
    <div class="gigs-content">
        <div class="gigs-info">
            @if ($category)
                <a href="{{ $showUrl }}" class="badge bg-primary-light cardBadge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M7 3h9a3 3 0 0 1 3 3v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3m0 1a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3v6.7l-3-2.1l-3 2.1V4m5 0H8v4.78l2-1.4l2 1.4V4Z"></path>
                    </svg>
                    {{ $category->displayTitle() }}
                </a>
            @endif

            @if ($schedule['hours'] || $schedule['days'])
                <div class="gigs-card-footer justify-content-start gap-2 mb-0">
                    @if ($schedule['hours'])
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 1024 1024" aria-hidden="true">
                                <path fill="currentColor" d="M512 0C229.232 0 0 229.232 0 512c0 282.784 229.232 512 512 512c282.784 0 512-229.216 512-512C1024 229.232 794.784 0 512 0zm0 961.008c-247.024 0-448-201.984-448-449.01c0-247.024 200.976-448 448-448s448 200.977 448 448s-200.976 449.01-448 449.01zm32-462V192.002c0-17.664-14.336-32-32-32s-32 14.336-32 32v320c0 9.056 3.792 17.2 9.856 23.007c.529.624.96 1.296 1.537 1.887l158.384 158.4c12.496 12.481 32.752 12.481 45.248 0c12.496-12.496 12.496-32.768 0-45.264z"></path>
                            </svg>
                            <span>{{ $schedule['hours'] }} ساعة</span>
                        </div>
                    @endif
                    @if ($schedule['days'])
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21" aria-hidden="true">
                                <g fill="none" fill-rule="evenodd" transform="translate(2 2)">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.5.5h12.027a2 2 0 0 1 2 2v11.99a2 2 0 0 1-1.85 1.995l-.16.006l-12.027-.058a2 2 0 0 1-1.99-2V2.5a2 2 0 0 1 2-2zm-2 4h16.027"></path>
                                    <circle cx="4.5" cy="8.5" r="1" fill="currentColor"></circle>
                                </g>
                            </svg>
                            <span>{{ $schedule['days'] }} يوم</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="gigs-title">
            <h3>
                <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
            </h3>
        </div>

        @if ($displayPrice)
            <div class="course-card-price-row">
                <div class="course-card-price-row__info badge d-flex flex-column mb-0" id="flag_type_select_{{ $deliveryType }}_{{ $course->id }}" data-course_type="{{ $deliveryType }}">
                    <span class="course_type">{{ $deliveryLabel }}</span>
                    <span class="course_price">{{ $displayPrice }} @include('partials.catalog.sar-icon')</span>
                </div>
                @include('partials.catalog.course-card-wish', ['courseId' => $course->id])
            </div>

            @include('partials.catalog.course-card-actions', [
                'showUrl' => $showUrl,
                'enrollUrl' => $enrollUrl,
                'courseId' => $course->id,
                'showWish' => false,
            ])
        @else
            <a href="{{ $showUrl }}" class="btn btn-outline-primary w-100 mt-2">عرض التفاصيل</a>
        @endif
    </div>

    <div class="card-tooltip-overlay">
        <div class="tooltip-content">
            <div class="gigs-content">
                <div class="gigs-title">
                    <h3>
                        <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
                    </h3>
                </div>

                <div class="card p-4 sticky-top">
                    <div class="card-content main-card">
                        <ul>
                            @if ($schedule['hours'])
                                <li>
                                    <div class="sidbar-icon">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <div class="d-flex justify-content-between w-100">
                                        <h5>عدد الساعات</h5>
                                        <p>{{ $schedule['hours'] }} ساعة</p>
                                    </div>
                                </li>
                            @endif
                            @if ($schedule['days'])
                                <li>
                                    <div class="sidbar-icon">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#2b2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <div class="d-flex justify-content-between w-100">
                                        <h5>عدد الأيام</h5>
                                        <p>{{ $schedule['days'] }} يوم</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if ($displayPrice)
                    <div class="course-card-price-row">
                        <div class="course-card-price-row__info badge d-flex flex-column selected-course course-type-item mb-0" data-course-type="{{ $deliveryType }}" data-id="{{ $course->id }}">
                            <span class="course_type">{{ $deliveryLabel }}</span>
                            <span class="course_price">{{ $displayPrice }} @include('partials.catalog.sar-icon')</span>
                        </div>
                        @include('partials.catalog.course-card-wish', ['courseId' => $course->id])
                    </div>
                @endif

                <div class="gigs-card-footer justify-content-start gap-2 hoverShow">
                    @include('partials.catalog.course-card-actions', [
                        'showUrl' => $showUrl,
                        'enrollUrl' => $enrollUrl,
                        'courseId' => $course->id,
                        'compact' => true,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
