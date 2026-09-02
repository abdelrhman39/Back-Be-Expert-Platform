@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $catalog = app(\App\Services\CatalogCourseService::class);
    $schedule = $catalog->trainingSchedule($course);
    $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $enrollUrl = $showUrl.'#course-enroll';
    $priceValue = $course->displayPriceValue();
    $displayPrice = $priceValue !== null ? number_format($priceValue, 0) : null;
    $deliveryLabel = $course->deliveryModesLabel();
    $deliveryIconClass = match ($course->normalizedDeliveryType()) {
        'online' => 'fa-laptop',
        'both' => 'fa-layer-group',
        default => 'fa-building',
    };
    $brief = $course->details?->tabContent('brief');
    $summary = $brief ? \Illuminate\Support\Str::limit(strip_tags($brief), 96) : null;
    $inWishlist = app(\App\Services\WishlistService::class)->isInWishlist($course->id);
    $hours = $course->duration_hours ?: ($schedule['hours'] ?? null);
    $isDiploma = $course->categories->contains('id', \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS);
    $isCertificate = $course->categories->contains('id', \App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES);
    $typePill = $isDiploma
        ? ($isEn ? 'Academic Diploma' : 'دبلوم أكاديمي')
        : ($isCertificate
            ? ($isEn ? 'Professional Certificate' : 'شهادة احترافية')
            : ($course->primaryCategory()?->displayTitle() ?: ($isEn ? 'Program' : 'برنامج')));
    $usesBrandPoster = ! $course->hasCustomPoster();
    $deliveryType = $course->delivery_type === 'onsite' ? 'offline' : 'online';
@endphp

<article class="diploma-list-card">
    <a @class(['diploma-list-card__media', 'diploma-list-card__media--brand' => $usesBrandPoster]) href="{{ $showUrl }}">
        <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" loading="lazy">
        <span class="diploma-list-card__pill">{{ $typePill }}</span>
        @if ($course->is_featured)
            <span class="diploma-list-card__pill diploma-list-card__pill--featured">{{ $isEn ? 'Featured' : 'مميز' }}</span>
        @endif
    </a>

    <div class="diploma-list-card__body">
        <div class="diploma-list-card__meta">
            @if ($course->duration_label)
                <span><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>{{ $course->duration_label }}</span>
            @endif
            @if ($hours)
                <span><i class="fa-solid fa-clock" aria-hidden="true"></i>{{ $hours }} {{ $isEn ? 'hours' : 'ساعة' }}</span>
            @endif
            @if ($course->city)
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $course->city }}</span>
            @endif
            <span><i class="fa-solid {{ $deliveryIconClass }}" aria-hidden="true"></i>{{ $deliveryLabel }}</span>
            <span><i class="fa-solid {{ $course->installmentOffered() ? 'fa-calendar-check' : 'fa-money-bill-wave' }}" aria-hidden="true"></i>{{ $course->installmentOffered() ? ($isEn ? 'Payment plan available' : 'تقسيط متاح') : ($isEn ? 'Full payment' : 'سداد كامل') }}</span>
        </div>

        <h3 class="diploma-list-card__title">
            <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
        </h3>

        @if ($summary)
            <p class="diploma-list-card__summary">{{ $summary }}</p>
        @endif

        <div class="diploma-list-card__price">
            @if ($displayPrice)
                <span class="diploma-list-card__price-label">{{ $isEn ? 'Tuition fees' : 'الرسوم الدراسية' }}</span>
                <strong>{{ $displayPrice }} <span>{{ $isEn ? 'SAR' : 'ر.س' }}</span></strong>
                @if ($course->installmentOffered())
                    <em class="diploma-list-card__installment">{{ $isEn ? 'Payment plan available' : 'تقسيط متاح' }}</em>
                @endif
            @else
                <span class="diploma-list-card__price-label">{{ $isEn ? 'Fees' : 'الرسوم' }}</span>
                <strong>{{ $isEn ? 'Shown on the details page' : 'تُعرض في صفحة التفاصيل' }}</strong>
            @endif
        </div>

        <div class="diploma-list-card__actions">
            <button type="button"
                class="diploma-list-card__wish makeWishlist @if($inWishlist) active @endif"
                data-course_id="{{ $course->id }}"
                title="{{ $isEn ? 'Add to wishlist' : 'إضافة إلى المفضلة' }}"
                aria-label="{{ $isEn ? 'Add to wishlist' : 'إضافة إلى المفضلة' }}">
                @include('partials.catalog.heart-icon', ['active' => $inWishlist])
            </button>
            <a href="{{ $showUrl }}" class="btn diploma-list-card__btn diploma-list-card__btn--ghost">{{ $isEn ? 'View details' : 'عرض التفاصيل' }}</a>
            <a href="{{ $enrollUrl }}" class="btn diploma-list-card__btn diploma-list-card__btn--solid">{{ $isEn ? 'Register and pay' : 'التسجيل والدفع' }}</a>
        </div>
    </div>

    {{-- Keep commerce data hooks for cart scripts --}}
    <span class="d-none"
        data-course_id="{{ $course->id }}"
        data-course-title="{{ $course->displayTitle() }}"
        data-course-slug="{{ $course->showSlug() }}"
        data-single-type="{{ $deliveryType }}"
        data-price="{{ $priceValue ?? 0 }}"></span>
</article>
