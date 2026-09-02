@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $catalog = app(\App\Services\CatalogCourseService::class);
    $schedule = $catalog->trainingSchedule($course);
    $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $priceValue = $course->displayPriceValue();
    $displayPrice = $priceValue !== null ? number_format($priceValue, 0) : null;
    $category = $course->primaryCategory();
    $kicker = $kicker ?? ($category?->displayTitle() ?: ($isEn ? 'Training program' : 'برنامج تدريبي'));
    $hours = $course->duration_hours ?: ($schedule['hours'] ?? null);
    $brief = $course->details?->tabContent('brief');
    $summary = $brief ? \Illuminate\Support\Str::limit(strip_tags($brief), 90) : null;
    $usesBrandPoster = ! $course->hasCustomPoster();
    $metaBits = collect([
        $course->duration_label,
        $hours ? $hours.' '.($isEn ? 'hours' : 'ساعة') : null,
        $course->deliveryModesLabel(),
    ])->filter()->unique()->take(2);
@endphp

<article class="lg-news-card home-program-card">
    <a @class(['lg-news-card__media', 'home-program-card__media', 'home-program-card__media--brand' => $usesBrandPoster]) href="{{ $showUrl }}">
        <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" loading="lazy">
        @if ($displayPrice)
            <span class="home-program-card__price">{{ $displayPrice }} <small>{{ $isEn ? 'SAR' : 'ر.س' }}</small></span>
        @endif
    </a>
    <div class="lg-news-card__body">
        <span class="lg-news-card__badge">{{ $kicker }}</span>
        <h3 class="lg-news-card__title">
            <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
        </h3>
        @if ($summary)
            <p class="lg-news-card__excerpt">{{ $summary }}</p>
        @endif
        <div class="lg-news-card__meta">
            <span>{{ $metaBits->implode(' · ') }}</span>
            <a href="{{ $showUrl }}">{{ $isEn ? 'Learn more' : 'اعرف المزيد' }}</a>
        </div>
    </div>
</article>
