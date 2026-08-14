@php
    $locale = app()->getLocale();
    $categoryId = \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS;
    $allUrl = route('courses.index', ['locale' => $locale, 'categories' => [$categoryId]]);
@endphp
@if ($diplomas->isNotEmpty())
    <section id="section-diplomas" class="explore-gigs-section home-catalog-section diplomas-section">
        <div class="container">
            <div class="section-head home-catalog-section__head diplomas-section__head">
                <div class="section-header" data-aos="fade-up">
                    <p class="home-catalog-section__eyebrow diplomas-section__eyebrow">برامج أكاديمية معتمدة</p>
                    <h2>الدبلومات</h2>
                    <p class="home-catalog-section__lead diplomas-section__lead">مسارات مهنية منظّمة بتفاصيل كاملة وتسجيل مباشر.</p>
                </div>
            </div>

            <div class="home-catalog-slider-wrap" data-aos="fade-up">
                <div
                    class="js-home-catalog-slider owl-carousel owl-rtl"
                    data-slides="{{ min(3, $diplomas->count()) }}"
                >
                    @foreach ($diplomas as $course)
                        @php
                            $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
                            $enrollUrl = $showUrl.'#course-enroll';
                            $schedule = app(\App\Services\CatalogCourseService::class)->trainingSchedule($course);
                            $price = $course->displayPriceValue();
                            $deliveryLabel = $course->deliveryModesLabel();
                            $brief = $course->details?->tabContent('brief');
                            $summary = $brief
                                ? \Illuminate\Support\Str::limit(strip_tags($brief), 110)
                                : null;
                        @endphp
                        <div class="home-catalog-slide">
                            <article class="diploma-card h-100">
                                <a class="diploma-card__media" href="{{ $showUrl }}">
                                    <img
                                        src="{{ $course->posterUrl() }}"
                                        alt="{{ $course->displayTitle() }}"
                                        loading="lazy"
                                    >
                                    <div class="diploma-card__media-shade" aria-hidden="true"></div>
                                    <span class="diploma-card__pill">دبلوم أكاديمي</span>
                                    @if ($course->is_featured)
                                        <span class="diploma-card__pill diploma-card__pill--open">مميز</span>
                                    @endif
                                </a>

                                <div class="diploma-card__body">
                                    <div class="diploma-card__meta">
                                        @if ($course->duration_label)
                                            <span>
                                                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                                                {{ $course->duration_label }}
                                            </span>
                                        @endif
                                        @if ($course->duration_hours || ($schedule['hours'] ?? null))
                                            <span>
                                                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                                {{ $course->duration_hours ?: $schedule['hours'] }} ساعة
                                            </span>
                                        @endif
                                        @if ($course->city)
                                            <span>
                                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                                {{ $course->city }}
                                            </span>
                                        @endif
                                        <span>
                                            <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
                                            {{ $deliveryLabel }}
                                        </span>
                                        <span>
                                            <i class="fa-solid {{ $course->installmentOffered() ? 'fa-calendar-check' : 'fa-money-bill-wave' }}" aria-hidden="true"></i>
                                            {{ $course->installmentOffered() ? 'تقسيط متاح' : 'سداد كامل' }}
                                        </span>
                                    </div>

                                    <h3 class="diploma-card__title">
                                        <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
                                    </h3>

                                    @if ($summary)
                                        <p class="diploma-card__summary">{{ $summary }}</p>
                                    @endif

                                    <div class="diploma-card__footer">
                                        @if ($price !== null)
                                            <div class="diploma-card__price">
                                                <span class="diploma-card__price-label">الرسوم الدراسية</span>
                                                <strong>{{ number_format($price, 0) }} <span>ر.س</span></strong>
                                                @if ($course->installmentOffered())
                                                    <em>أقساط متاحة</em>
                                                @endif
                                            </div>
                                        @else
                                            <div class="diploma-card__price diploma-card__price--muted">
                                                <span class="diploma-card__price-label">التسجيل</span>
                                                <strong>عرض التفاصيل للتسجيل</strong>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="diploma-card__actions">
                                        <a href="{{ $showUrl }}" class="btn diploma-card__btn diploma-card__btn--ghost">التفاصيل</a>
                                        <a href="{{ $enrollUrl }}" class="btn diploma-card__btn diploma-card__btn--solid">التسجيل والدفع</a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="home-catalog-section__cta diplomas-section__cta">
                <a href="{{ $allUrl }}" class="btn btn-primary diplomas-section__all">
                    جميع الدبلومات
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- CSS/JS loaded from layouts/app (home-catalog-slider + home-diplomas) --}}
@endif
