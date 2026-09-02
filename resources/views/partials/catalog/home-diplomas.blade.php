@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $categoryId = \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS;
    $allUrl = route('courses.index', ['locale' => $locale, 'categories' => [$categoryId]]);
    $diplomas = collect($diplomas ?? []);
@endphp
<section id="section-diplomas" class="explore-gigs-section home-catalog-section diplomas-section">
    <div class="container">
        <div class="section-head home-catalog-section__head diplomas-section__head">
            <div class="section-header" data-aos="fade-up">
                <p class="home-catalog-section__eyebrow diplomas-section__eyebrow">{{ $isEn ? 'Academic tracks' : 'برامج أكاديمية معتمدة' }}</p>
                <h2>{{ $isEn ? 'Diplomas' : 'الدبلومات' }}</h2>
                <p class="home-catalog-section__lead diplomas-section__lead">{{ $isEn ? 'Structured professional diplomas with complete details and direct enrollment.' : 'مسارات مهنية منظّمة بتفاصيل كاملة وتسجيل مباشر.' }}</p>
            </div>
        </div>

        @if ($diplomas->isNotEmpty())
            @include('partials.catalog.home-program-slider', [
                'items' => $diplomas,
                'slides' => 2,
                'label' => $isEn ? 'Diplomas' : 'الدبلومات',
            ])
        @else
            <a class="lg-program-teaser lg-program-teaser--diploma" href="{{ $allUrl }}" data-aos="fade-up">
                <span class="lg-program-teaser__icon" aria-hidden="true"><i class="fa-solid fa-scroll"></i></span>
                <span class="lg-program-teaser__copy">
                    <strong>{{ $isEn ? 'Browse academic diplomas' : 'تصفح الدبلومات الأكاديمية' }}</strong>
                    <span>{{ $isEn ? 'Longer professional tracks with structured learning outcomes.' : 'مسارات أطول بمخرجات تعلم واضحة ومتابعة أكاديمية.' }}</span>
                </span>
                <span class="lg-program-teaser__cta">{{ $isEn ? 'View all' : 'عرض الكل' }}</span>
            </a>
        @endif

        <div class="home-catalog-section__cta diplomas-section__cta">
            <a href="{{ $allUrl }}" class="btn btn-primary diplomas-section__all">
                {{ $isEn ? 'All diplomas' : 'جميع الدبلومات' }}
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>
