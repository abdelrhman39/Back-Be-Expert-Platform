@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $catalogUrl = route('courses.index', ['locale' => $locale]);
@endphp

@if ($relatedCourses->isNotEmpty())
    <section class="course-show-related atelier-catalog" aria-labelledby="course-show-related-title">
        <div class="course-show-related__head">
            <div>
                <p class="course-show-related__eyebrow">{{ $isEn ? 'Continue exploring' : 'واصل الاستكشاف' }}</p>
                <h2 class="course-show-related__title" id="course-show-related-title">{{ $isEn ? 'Related programs' : 'برامج ذات صلة' }}</h2>
            </div>
            <a class="course-show-related__all" href="{{ $catalogUrl }}">
                {{ $isEn ? 'View all programs' : 'عرض كل البرامج' }}
                <i class="fa-solid {{ $isEn ? 'fa-arrow-right' : 'fa-arrow-left' }}" aria-hidden="true"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach ($relatedCourses as $related)
                <div class="col-xl-4 col-md-6" wire:key="related-course-{{ $related->id }}">
                    @include('partials.catalog.course-card-diploma', ['course' => $related])
                </div>
            @endforeach
        </div>
    </section>
@endif
