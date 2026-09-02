@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $catalog = app(\App\Services\CatalogCourseService::class);
    $schedule = $catalog->trainingSchedule($course);
    $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $enrollUrl = $showUrl.'#course-enroll';
    $priceValue = $course->displayPriceValue();
    $displayPrice = $priceValue !== null ? number_format($priceValue, 0) : null;
    $category = $course->primaryCategory();
    $kicker = $kicker ?? ($category?->displayTitle() ?: ($isEn ? 'Training program' : 'برنامج تدريبي'));
    $deliveryLabel = $course->deliveryModesLabel() ?: ($course->delivery_type === 'online'
        ? ($isEn ? 'Remote' : 'عن بعد')
        : ($isEn ? 'In person' : 'حضوري'));
    $hours = $course->duration_hours ?: ($schedule['hours'] ?? null);
    $brief = $course->details?->tabContent('brief');
    $summary = $brief ? \Illuminate\Support\Str::limit(strip_tags($brief), 110) : null;
    $icon = $icon ?? 'fa-solid fa-graduation-cap';
    $usesBrandPoster = ! $course->hasCustomPoster();
@endphp

<article class="lg-service">
    <div class="lg-service__copy">
        <span class="lg-service__icon" aria-hidden="true">
            <i class="{{ $icon }}"></i>
        </span>
        <span class="lg-service__kicker">{{ $kicker }}</span>
        <h3 class="lg-service__title">
            <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
        </h3>
        @if ($summary)
            <p class="lg-service__excerpt">{{ $summary }}</p>
        @endif
        <p class="lg-service__meta">
            @if ($course->duration_label)
                <span>{{ $course->duration_label }}</span>
            @elseif ($hours)
                <span>{{ $hours }} {{ $isEn ? 'hours' : 'ساعة' }}</span>
            @endif
            <span>{{ $deliveryLabel }}</span>
            @if ($course->installmentOffered())
                <span>{{ $isEn ? 'Installments available' : 'تقسيط متاح' }}</span>
            @endif
        </p>
        <a class="lg-service__more" href="{{ $showUrl }}">
            {{ $isEn ? 'Learn more' : 'اعرف المزيد' }}
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        </a>
        <div class="lg-service__actions">
            @include('partials.catalog.course-card-actions', [
                'showUrl' => $showUrl,
                'enrollUrl' => $enrollUrl,
                'courseId' => $course->id,
                'showWish' => false,
            ])
        </div>
    </div>
    <a
        @class(['lg-service__media', 'lg-service__media--brand' => $usesBrandPoster])
        href="{{ $showUrl }}"
        aria-hidden="true"
        tabindex="-1"
    >
        <img src="{{ $course->posterUrl() }}" alt="" loading="lazy">
        @if ($displayPrice)
            <span class="lg-service__price">{{ $displayPrice }} <small>{{ $isEn ? 'SAR' : 'ر.س' }}</small></span>
        @endif
    </a>
</article>
