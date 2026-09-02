@php
    $locale = app()->getLocale();
    $catalog = app(\App\Services\CatalogCourseService::class);
    $schedule = $catalog->trainingSchedule($course);
    $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $priceValue = $course->displayPriceValue();
    $deliveryType = $course->delivery_type === 'onsite' ? 'offline' : 'online';
    $deliveryLabel = $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري';
    $displayPrice = $priceValue !== null ? number_format($priceValue, 0) : null;
    $category = $course->primaryCategory();
    $enrollUrl = $showUrl.'#course-enroll';
@endphp

<article class="atelier-program trainingCard gigs-grid fellowship-card">
    <a class="atelier-program__media" href="{{ $showUrl }}">
        <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" loading="lazy">
        <span class="atelier-program__shade" aria-hidden="true"></span>
    </a>

    @if ($displayPrice)
        <div class="atelier-program__price" aria-label="{{ $displayPrice }} ريال">
            {{ $displayPrice }}
            <small>ر.س</small>
        </div>
    @endif

    <div class="atelier-program__body">
        <div class="atelier-program__kicker">
            @if ($category)
                <span class="atelier-program__cat">{{ $category->displayTitle() }}</span>
            @endif
            <span>{{ $deliveryLabel }}</span>
        </div>

        <h3 class="atelier-program__title">
            <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
        </h3>

        <div class="atelier-program__meta">
            @if ($schedule['hours'])
                <span><i class="fa-solid fa-clock" aria-hidden="true"></i>{{ $schedule['hours'] }} ساعة</span>
            @endif
            @if ($schedule['days'])
                <span><i class="fa-solid fa-calendar-day" aria-hidden="true"></i>{{ $schedule['days'] }} يوم</span>
            @endif
            @if ($course->installmentOffered())
                <span><i class="fa-solid fa-calendar-check" aria-hidden="true"></i>تقسيط</span>
            @endif
        </div>

        @include('partials.catalog.course-card-actions', [
            'showUrl' => $showUrl,
            'enrollUrl' => $enrollUrl,
            'courseId' => $course->id,
            'showWish' => true,
        ])
    </div>

    {{-- Keep commerce data hooks for cart scripts --}}
    <span class="d-none"
        data-course_id="{{ $course->id }}"
        data-course-title="{{ $course->displayTitle() }}"
        data-course-slug="{{ $course->showSlug() }}"
        data-single-type="{{ $deliveryType }}"
        data-price="{{ $priceValue ?? 0 }}"></span>
</article>
