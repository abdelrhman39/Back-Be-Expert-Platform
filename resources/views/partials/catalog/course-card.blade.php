@php
    $locale = app()->getLocale();
    $showUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $priceValue = $course->displayPriceValue();
    $displayPrice = $priceValue !== null ? number_format($priceValue, 0) : null;
@endphp
<article class="atelier-program catalog-card">
    <a href="{{ $showUrl }}" class="atelier-program__media catalog-card__image-link">
        <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" class="catalog-card__image" loading="lazy">
        <span class="atelier-program__shade" aria-hidden="true"></span>
        @if ($course->is_featured)
            <span class="catalog-card__badge">مميزة</span>
        @endif
    </a>
    @if ($displayPrice)
        <div class="atelier-program__price">{{ $displayPrice }}<small>ر.س</small></div>
    @endif
    <div class="atelier-program__body catalog-card__body">
        <h3 class="atelier-program__title catalog-card__title">
            <a href="{{ $showUrl }}">{{ $course->displayTitle() }}</a>
        </h3>
        <div class="atelier-program__meta catalog-card__meta">
            <span>{{ $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري' }}</span>
        </div>
        <div class="catalog-card__actions">
            <a href="{{ $showUrl }}" class="btn btn-outline-primary btn-sm">التفاصيل</a>
            <button type="button"
                class="btn btn-primary btn-sm Add-to-Cart"
                data-course_id="{{ $course->id }}"
                data-single-type="{{ $course->delivery_type }}"
                data-price="{{ $priceValue ?? 0 }}"
                data-course-title="{{ $course->displayTitle() }}"
                data-course-image="{{ $course->image }}"
                data-course-slug="{{ $course->showSlug() }}">
                أضف للسلة
            </button>
        </div>
    </div>
</article>
