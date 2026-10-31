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
    $briefText = trim(preg_replace('/\s+/u', ' ', strip_tags(str_ireplace(
        ['</p>', '</div>', '</li>', '<br>', '<br/>', '<br />'],
        ' ',
        (string) $brief
    ))) ?? '');
    $taibahHomeCard = in_array(($presentation ?? null), ['taibah-certificate', 'taibah-home'], true);
    if ($taibahHomeCard && $briefText !== '') {
        $summary = preg_match('/^.*?[.!?؟]/u', $briefText, $summaryMatch)
            ? trim($summaryMatch[0])
            : $briefText;
    } else {
        $summary = $briefText !== '' ? \Illuminate\Support\Str::limit($briefText, 96) : null;
    }
    $usesBrandPoster = ! $course->hasCustomPoster();
    $daysCount = $course->duration_days ? (int) $course->duration_days : null;
    $dateLabel = null;
    if ($daysCount) {
        $dateLabel = $isEn
            ? $daysCount.' '.($daysCount === 1 ? 'day' : 'days')
            : $daysCount.' '.($daysCount === 1 ? 'يوم' : ($daysCount === 2 ? 'يومان' : ($daysCount <= 10 ? 'أيام' : 'يوماً')));
    } elseif (filled($course->duration_label)) {
        $dateParts = preg_split('/\s*\/\s*/u', trim((string) $course->duration_label), 2) ?: [];
        $dateLabel = trim(preg_replace('/\s*(حضورية|حضوري|عن بعد|نحو\s*\d+\s*ساعة)\s*/iu', ' ', (string) ($dateParts[0] ?? '')) ?? '');
        $dateLabel = $dateLabel !== '' ? $dateLabel : null;
    }
    $attendanceLabel = match ($course->normalizedDeliveryType()) {
        'online' => $isEn ? 'Remote' : 'عن بعد',
        'onsite' => $isEn ? 'In person' : 'حضوري',
        default => $isEn ? 'Remote or in person' : 'عن بعد أو حضوري',
    };
    $inWishlist = app(\App\Services\WishlistService::class)->isInWishlist($course->id);
    $hours = $course->duration_hours ?: ($schedule['hours'] ?? null);
    $hoursNumber = null;
    if ($hours !== null && $hours !== '') {
        $hoursNumber = ((float) $hours == (int) $hours) ? (string) (int) $hours : (string) $hours;
    } elseif ($taibahHomeCard && preg_match('/(\d+(?:[.,]\d+)?)\s*ساعة/u', (string) ($course->duration_label ?? ''), $hoursMatch)) {
        $parsedHours = str_replace(',', '.', $hoursMatch[1]);
        $hoursNumber = ((float) $parsedHours == (int) $parsedHours) ? (string) (int) $parsedHours : $hoursMatch[1];
    }
    $summaryMentionsPlace = $taibahHomeCard && $summary && $course->city && str_contains($summary, (string) $course->city);
    $summaryMentionsDelivery = $taibahHomeCard;
    $factCount = ($dateLabel ? 1 : 0) + ($hoursNumber ? 1 : 0) + 1;
    $hoursAlreadyInLabel = $hoursNumber
        && preg_match('/(?<!\d)'.preg_quote($hoursNumber, '/').'(?!\d)/u', (string) ($course->duration_label ?? ''));
    $isDiploma = $course->categories->contains('id', \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS);
    $isCertificate = $course->categories->contains('id', \App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES);
    $typePill = $isDiploma
        ? ($isEn ? 'Academic Diploma' : 'دبلوم أكاديمي')
        : ($isCertificate
            ? ($isEn ? 'Professional Certificate' : 'شهادة احترافية')
            : ($course->primaryCategory()?->displayTitle() ?: ($isEn ? 'Program' : 'برنامج')));
    $deliveryType = $course->delivery_type === 'onsite' ? 'offline' : 'online';
@endphp

<article class="diploma-list-card">
    <a @class([
        'diploma-list-card__media',
        'diploma-list-card__media--brand' => $usesBrandPoster,
    ]) href="{{ $showUrl }}">
        <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" loading="lazy">
        @unless ($taibahHomeCard)
            <span class="diploma-list-card__pill">{{ $typePill }}</span>
            @if ($course->is_featured)
                <span class="diploma-list-card__pill diploma-list-card__pill--featured">{{ $isEn ? 'Featured' : 'مميز' }}</span>
            @endif
        @endunless
    </a>

    <div class="diploma-list-card__body">
        @unless ($taibahHomeCard)
        <div class="diploma-list-card__meta">
            @if ($course->duration_label)
                <span><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>{{ $course->duration_label }}</span>
            @endif
            @if ($hoursNumber && ! $hoursAlreadyInLabel)
                <span><i class="fa-solid fa-clock" aria-hidden="true"></i>{{ $hoursNumber }} {{ $isEn ? 'hours' : 'ساعة' }}</span>
            @endif
            @if ($course->city && ! $taibahHomeCard && ! $summaryMentionsPlace)
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $course->city }}</span>
            @endif
            @unless ($summaryMentionsDelivery)
                <span><i class="fa-solid {{ $deliveryIconClass }}" aria-hidden="true"></i>{{ $deliveryLabel }}</span>
            @endunless
            @unless ($taibahHomeCard || $course->installmentOffered())
                <span><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i>{{ $isEn ? 'Full payment' : 'سداد كامل' }}</span>
            @endunless
        </div>
        @endunless

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

        @if ($taibahHomeCard)
            <div class="diploma-list-card__facts" style="--facts: {{ $factCount }}">
                @if ($dateLabel)
                    <span>
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        <small>{{ $isEn ? 'Date' : 'التاريخ' }}</small>
                        <strong>{{ $dateLabel }}</strong>
                    </span>
                @endif
                @if ($hoursNumber)
                    <span>
                        <i class="fa-solid fa-clock" aria-hidden="true"></i>
                        <small>{{ $isEn ? 'Hours' : 'عدد الساعات' }}</small>
                        <strong>{{ $hoursNumber }} {{ $isEn ? 'hours' : 'ساعة' }}</strong>
                    </span>
                @endif
                <span>
                    <i class="fa-solid {{ $deliveryIconClass }}" aria-hidden="true"></i>
                    <small>{{ $isEn ? 'Attendance' : 'الحضور' }}</small>
                    <strong>{{ $attendanceLabel }}</strong>
                </span>
            </div>
        @endif

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
