@php
    $locale = app()->getLocale();
    $categoryId = \App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES;
    $allUrl = route('courses.index', ['locale' => $locale, 'categories' => [$categoryId]]);
@endphp
@if ($courses->isNotEmpty())
    <section id="section-certificates" class="explore-gigs-section home-catalog-section">
        <div class="container">
            <div class="section-head home-catalog-section__head">
                <div class="section-header" data-aos="fade-up">
                    <p class="home-catalog-section__eyebrow">برامج معتمدة</p>
                    <h2>الشهادات الاحترافية</h2>
                    <p class="home-catalog-section__lead">شهادات مهنية لتطوير المسار الوظيفي مع تسجيل ودفع مباشر.</p>
                </div>
            </div>

            <div class="home-catalog-slider-wrap" data-aos="fade-up">
                <div
                    class="js-home-catalog-slider owl-carousel owl-rtl"
                    data-slides="{{ min(3, $courses->count()) }}"
                >
                    @foreach ($courses as $course)
                        <div class="home-catalog-slide">
                            @include('partials.catalog.course-card-gigs', ['course' => $course])
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="home-catalog-section__cta">
                <a href="{{ $allUrl }}" class="btn btn-primary">
                    جميع الشهادات
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/catalog-public.css') }}?v=3">
    @endpush
@endif
