@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $categoryId = \App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES;
    $allUrl = route('courses.index', ['locale' => $locale, 'categories' => [$categoryId]]);
    $courses = collect($courses ?? []);
@endphp
<section id="section-certificates" class="explore-gigs-section home-catalog-section">
    <div class="container">
        <div class="section-head home-catalog-section__head">
            <div class="section-header" data-aos="fade-up">
                <p class="home-catalog-section__eyebrow">{{ $isEn ? 'Certified tracks' : 'برامج معتمدة' }}</p>
                <h2>{{ $isEn ? 'Professional certificates' : 'الشهادات الاحترافية' }}</h2>
                <p class="home-catalog-section__lead">{{ $isEn ? 'Selected professional tracks with clear details and direct enrollment.' : 'مسارات مهنية مختارة بعناية، بتفاصيل واضحة وتسجيل مباشر.' }}</p>
            </div>
        </div>

        @if ($courses->isNotEmpty())
            @include('partials.catalog.home-program-slider', [
                'items' => $courses,
                'slides' => 2,
                'label' => $isEn ? 'Professional certificates' : 'الشهادات الاحترافية',
            ])
        @else
            <a class="lg-program-teaser" href="{{ $allUrl }}" data-aos="fade-up">
                <span class="lg-program-teaser__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                <span class="lg-program-teaser__copy">
                    <strong>{{ $isEn ? 'Browse professional certificates' : 'تصفح الشهادات الاحترافية' }}</strong>
                    <span>{{ $isEn ? 'Explore certified programs aligned with labor-market needs.' : 'استكشف البرامج المعتمدة المتوافقة مع احتياج سوق العمل.' }}</span>
                </span>
                <span class="lg-program-teaser__cta">{{ $isEn ? 'View all' : 'عرض الكل' }}</span>
            </a>
        @endif

        <div class="home-catalog-section__cta">
            <a href="{{ $allUrl }}" class="btn btn-primary">
                {{ $isEn ? 'All certificates' : 'جميع الشهادات' }}
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>
