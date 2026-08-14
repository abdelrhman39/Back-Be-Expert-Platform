@php
    $locale = app()->getLocale();
    $stats = app(\App\Services\CatalogCourseService::class)->contentStats($course);
    $hours = $stats['duration'] > 0 ? (int) round($stats['duration'] / 60) : null;
    $enrollUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]).'#course-enroll';
    $priceValue = $course->displayPriceValue();
@endphp
<div class="trainingCard gigs-grid fellowship-card tooltip-coursecard">
    <div class="gigs-img">
        <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}">
            <img src="{{ $course->posterUrl() }}" class="img-fluid" alt="{{ $course->displayTitle() }}" loading="lazy">
        </a>
    </div>
    <div class="gigs-content">
        <div class="gigs-info">
            <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}" class="badge bg-primary-light cardBadge">
                الشهادات الاحترافية
            </a>
            @if ($hours)
                <div class="gigs-card-footer justify-content-start gap-2 mb-0">
                    <div><span>{{ $hours }} ساعة</span></div>
                    @if ($stats['lessons'] > 0)
                        <div><span>{{ $stats['lessons'] }} درس</span></div>
                    @endif
                </div>
            @endif
        </div>
        <div class="gigs-title">
            <h3>
                <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}">
                    {{ $course->displayTitle() }}
                </a>
            </h3>
        </div>
        <div class="gigs-card-footer justify-content-start gap-3 align-items-center">
            <p class="badge d-flex flex-column mb-0" data-course_type="{{ $course->delivery_type }}">
                <span class="course_type">{{ $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري' }}</span>
                @if ($course->displayPrice())
                    <span class="course_price">{{ $course->displayPrice() }}</span>
                @endif
            </p>
            @if ($priceValue !== null)
                <a href="{{ $enrollUrl }}" class="btn btn-primary btn-sm">
                    التسجيل والدفع
                </a>
            @endif
        </div>
    </div>
</div>
