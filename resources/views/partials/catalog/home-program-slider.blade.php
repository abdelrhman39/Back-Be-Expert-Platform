@php
    $items = collect($items ?? []);
    $label = $label ?? '';
    $isEn = app()->getLocale() === 'en';
@endphp

<div
    class="home-catalog-slider-wrap home-catalog-slider-wrap--cards"
    data-program-slider
>
    <div
        class="home-program-track"
        data-program-track
        @if ($label !== '') aria-label="{{ $label }}" @endif
    >
        @foreach ($items as $course)
            <div class="home-catalog-slide">
                @include('partials.catalog.course-card-diploma', ['course' => $course])
            </div>
        @endforeach
    </div>
    @if ($items->count() > 1)
        <div class="home-program-nav">
            <button type="button" class="home-program-nav__btn" data-program-prev aria-label="{{ $isEn ? 'Previous' : 'السابق' }}">
                <i class="fa-solid {{ $isEn ? 'fa-angle-left' : 'fa-angle-right' }}" aria-hidden="true"></i>
            </button>
            <button type="button" class="home-program-nav__btn" data-program-next aria-label="{{ $isEn ? 'Next' : 'التالي' }}">
                <i class="fa-solid {{ $isEn ? 'fa-angle-right' : 'fa-angle-left' }}" aria-hidden="true"></i>
            </button>
        </div>
    @endif
</div>
