@php
    $locale = app()->getLocale();
    $priceValue = $course->displayPriceValue();
    $isAr = $locale === 'ar';
@endphp

<div class="col-lg-4 order-mob-1 course-show-enroll-col" data-course-enroll-root>
    <div class="course-enroll-mobile-bar" id="course-enroll-mobile-bar">
        <div class="course-enroll-mobile-bar__meta">
            <span class="course-enroll-mobile-bar__label">{{ $isAr ? 'المبلغ المستحق' : 'Amount due' }}</span>
            @if ($priceValue !== null)
                <strong class="course-enroll-mobile-bar__price">
                    {{ number_format($priceValue, 0) }}
                    @include('partials.catalog.sar-icon')
                </strong>
            @else
                <strong class="course-enroll-mobile-bar__price">{{ $isAr ? 'سجّل الآن' : 'Enroll now' }}</strong>
            @endif
        </div>
        <button type="button" class="course-enroll-mobile-bar__cta" data-course-enroll-open>
            {{ $isAr ? 'التسجيل والدفع' : 'Register & pay' }}
        </button>
    </div>

    <div class="course-enroll-sheet-backdrop" data-course-enroll-close hidden></div>

    <aside
        class="course-sidebar-enroll course-sidebar-enroll--focus sticky-top"
        id="course-enroll"
        data-course-enroll-sheet
        aria-labelledby="course-enroll-sheet-title"
    >
        <div class="course-enroll-sheet__chrome">
            <div class="course-enroll-sheet__grab" aria-hidden="true"></div>
            <div class="course-enroll-sheet__toolbar">
                <h2 class="course-enroll-sheet__title" id="course-enroll-sheet-title">
                    {{ $isAr ? 'التسجيل والدفع' : 'Registration & payment' }}
                </h2>
                <button
                    type="button"
                    class="course-enroll-sheet__close"
                    data-course-enroll-close
                    aria-label="{{ $isAr ? 'إغلاق' : 'Close' }}"
                >
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="course-enroll-panel">
            <livewire:catalog.course-enroll-page :course="$course" :compact="true" :key="'course-enroll-'.$course->id" />
        </div>

        <div class="course-sidebar-enroll__wish">
            <a href="javascript:void(0);" class="fav-icon makeWishlist" data-course_id="{{ $course->id }}" title="{{ $isAr ? 'إضافة إلى القائمة المفضلة' : 'Add to wishlist' }}">
                <i class="fa-regular fa-heart" aria-hidden="true"></i>
                <span>{{ $isAr ? 'أضف للمفضلة' : 'Add to wishlist' }}</span>
            </a>
        </div>
    </aside>
</div>
