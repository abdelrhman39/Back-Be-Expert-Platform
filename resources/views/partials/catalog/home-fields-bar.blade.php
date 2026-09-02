@php
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $fields = collect($fields ?? []);
@endphp

@if ($fields->isNotEmpty())
    <nav class="lg-field-bar" aria-label="{{ $isEn ? 'Program fields' : 'المجالات التدريبية' }}">
        <div class="container">
            <div class="lg-field-bar__row">
                <p class="lg-field-bar__intro">
                    {{ $isEn ? 'What are you looking for?' : 'ما المجال الذي تبحث عنه؟' }}
                </p>
                @foreach ($fields as $field)
                    <a class="lg-field-bar__item" href="{{ $field->coursesIndexUrl($locale) }}">
                        @if ((int) ($field->courses_count ?? 0) > 0)
                            <span class="lg-field-bar__count">{{ $field->courses_count }}</span>
                        @endif
                        <span class="lg-field-bar__icon" aria-hidden="true">
                            <img src="{{ $field->iconUrl() }}" alt="">
                        </span>
                        <span class="lg-field-bar__label">{{ $field->displayTitle() }}</span>
                    </a>
                @endforeach
                <a class="lg-field-bar__item lg-field-bar__item--all" href="{{ route('courses.index', ['locale' => $locale]) }}">
                    <span class="lg-field-bar__icon" aria-hidden="true">
                        <i class="fa-solid fa-arrow-left"></i>
                    </span>
                    <span class="lg-field-bar__label">{{ $isEn ? 'All programs' : 'كل البرامج' }}</span>
                </a>
            </div>
        </div>
    </nav>
@endif
