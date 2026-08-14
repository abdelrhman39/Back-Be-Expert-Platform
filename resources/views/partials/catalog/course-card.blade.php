@php($locale = app()->getLocale())
<article class="catalog-card">
    <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}" class="catalog-card__image-link">
        <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" class="catalog-card__image" loading="lazy">
        @if ($course->is_featured)
            <span class="catalog-card__badge">مميزة</span>
        @endif
    </a>
    <div class="catalog-card__body">
        <h3 class="catalog-card__title">
            <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}">{{ $course->displayTitle() }}</a>
        </h3>
        <div class="catalog-card__meta">
            <span>{{ $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري' }}</span>
            @if ($course->displayPrice())
                <strong>{{ $course->displayPrice() }}</strong>
            @endif
        </div>
        <div class="catalog-card__actions">
            <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}" class="btn btn-outline-primary btn-sm">التفاصيل</a>
            <button type="button"
                class="btn btn-primary btn-sm Add-to-Cart"
                data-course_id="{{ $course->id }}"
                data-single-type="{{ $course->delivery_type }}"
                data-price="{{ $course->displayPriceValue() ?? 0 }}"
                data-course-title="{{ $course->displayTitle() }}"
                data-course-image="{{ $course->image }}"
                data-course-slug="{{ $course->showSlug() }}">
                أضف للسلة
            </button>
        </div>
    </div>
</article>
