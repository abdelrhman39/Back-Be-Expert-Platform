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
    $taibahCertificate = ($presentation ?? null) === 'taibah-certificate';
    if ($taibahCertificate && $briefText !== '') {
        $summary = preg_match('/^.*?[.!?؟]/u', $briefText, $summaryMatch)
            ? trim($summaryMatch[0])
            : $briefText;
    } else {
        $summary = $briefText !== '' ? \Illuminate\Support\Str::limit($briefText, 96) : null;
    }
    $usesBrandPoster = ! $course->hasCustomPoster();
    $scheduleMedia = $taibahCertificate && $usesBrandPoster && filled($course->duration_label);
    $durationPrimary = null;
    $durationSecondary = null;
    if ($scheduleMedia) {
        $stripMode = static function (?string $value): ?string {
            $value = trim(preg_replace('/\s*(حضورية|حضوري|عن بعد|in-person|online)\s*/iu', ' ', (string) $value) ?? '');
            $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

            return $value !== '' ? $value : null;
        };
        $durationParts = preg_split('/\s*\/\s*/u', trim((string) $course->duration_label), 2) ?: [];
        $durationPrimary = $stripMode($durationParts[0] ?? null);
        $durationSecondary = $stripMode($durationParts[1] ?? null);
    }
    $summaryMentionsPlace = $taibahCertificate && $summary && $course->city && str_contains($summary, (string) $course->city);
    $summaryMentionsDelivery = $taibahCertificate && $summary && preg_match('/عن بعد|حضو|online|in-person/iu', $summary);
    $inWishlist = app(\App\Services\WishlistService::class)->isInWishlist($course->id);
    $hours = $course->duration_hours ?: ($schedule['hours'] ?? null);
    $hoursNumber = null;
    if ($hours !== null && $hours !== '') {
        $hoursNumber = ((float) $hours == (int) $hours) ? (string) (int) $hours : (string) $hours;
    }
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
        'diploma-list-card__media--brand' => $usesBrandPoster && ! $scheduleMedia,
        'diploma-list-card__media--schedule' => $scheduleMedia,
    ]) href="{{ $showUrl }}">
        @if ($scheduleMedia)
            @if ($durationPrimary)
                <span class="diploma-list-card__schedule">
                    <strong>{{ $durationPrimary }}</strong>
                    @if ($durationSecondary)
                        <span>{{ $durationSecondary }}</span>
                    @endif
                </span>
            @endif
        @else
            <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" loading="lazy">
            @unless ($taibahCertificate)
                <span class="diploma-list-card__pill">{{ $typePill }}</span>
                @if ($course->is_featured)
                    <span class="diploma-list-card__pill diploma-list-card__pill--featured">{{ $isEn ? 'Featured' : 'مميز' }}</span>
                @endif
            @endunless
        @endif
    </a>

    <div class="diploma-list-card__body">
        <div class="diploma-list-card__meta">
            @unless ($scheduleMedia)
                @if ($course->duration_label)
                    <span><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>{{ $course->duration_label }}</span>
                @endif
                @if ($hoursNumber && ! $hoursAlreadyInLabel)
                    <span><i class="fa-solid fa-clock" aria-hidden="true"></i>{{ $hoursNumber }} {{ $isEn ? 'hours' : 'ساعة' }}</span>
                @endif
            @endunless
            @if ($course->city && ! $summaryMentionsPlace && (! $taibahCertificate || $course->normalizedDeliveryType() !== 'online'))
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $course->city }}</span>
            @endif
            @unless ($summaryMentionsDelivery)
                <span><i class="fa-solid {{ $deliveryIconClass }}" aria-hidden="true"></i>{{ $deliveryLabel }}</span>
            @endunless
            @unless ($taibahCertificate || $course->installmentOffered())
                <span><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i>{{ $isEn ? 'Full payment' : 'سداد كامل' }}</span>
            @endunless
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
