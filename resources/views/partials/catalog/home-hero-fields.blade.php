@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $fields = collect($popularFields ?? [])->values();
@endphp

@if ($fields->isNotEmpty())
    <div class="tu-fields-rail" aria-label="{{ $isEn ? 'Training fields' : 'المجالات' }}">
        <div class="tu-fields-rail__track">
            @foreach ([0, 1] as $copy)
                <div class="tu-fields-rail__set" @if ($copy === 1) aria-hidden="true" @endif>
                    @foreach ($fields as $field)
                        <a class="tu-fields-rail__item" href="{{ $field->coursesIndexUrl($locale) }}" @if ($copy === 1) tabindex="-1" @endif>
                            <span class="tu-fields-rail__icon" aria-hidden="true">
                                <img src="{{ $field->iconUrl() }}" alt="">
                            </span>
                            <strong>{{ $field->displayTitle() }}</strong>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endif
