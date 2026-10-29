@php
    $fields = collect($popularFields ?? []);
@endphp

<section class="tu-hero" aria-label="{{ $isEn ? 'Homepage hero' : 'مقدمة الصفحة الرئيسية' }}">
    <div class="tu-hero__banner">
        <div class="tu-hero__banner-inner">
            @if ($eyebrow !== '')
                <p class="tu-hero__kicker">{{ $eyebrow }}</p>
            @endif
            @if ($title !== '')
                <h1 class="tu-hero__title">{{ $title }}</h1>
            @endif
            @if ($subtitleLines->isNotEmpty())
                <p class="tu-hero__lead">
                    @foreach ($subtitleLines as $line)
                        <span>{{ $line }}</span>
                    @endforeach
                </p>
            @elseif ($legacySubtitle !== '')
                <p class="tu-hero__lead">{{ $legacySubtitle }}</p>
            @endif

            @if ($searchEnabled)
                <form class="tu-hero__search" action="{{ $coursesUrl }}" method="get" role="search">
                    <label class="visually-hidden" for="tu-hero-field">{{ $isEn ? 'Field' : 'المجال' }}</label>
                    <select id="tu-hero-field" name="fields[]">
                        <option value="">{{ $isEn ? 'All fields' : 'كل المجالات' }}</option>
                        @foreach ($fields as $field)
                            <option value="{{ $field->id }}">{{ $field->displayTitle() }}</option>
                        @endforeach
                    </select>
                    <label class="visually-hidden" for="tu-hero-q">{{ $isEn ? 'Search' : 'البحث' }}</label>
                    <input id="tu-hero-q" type="search" name="q" placeholder="{{ $isEn ? 'Search a course or diploma' : 'ابحث عن دورة أو دبلوم' }}" autocomplete="off">
                    <button type="submit">{{ $isEn ? 'Search' : 'بحث' }}</button>
                </form>
            @endif

            <div class="tu-hero__actions">
                <a class="tu-hero__btn" href="{{ $coursesUrl }}">{{ $isEn ? 'Browse programs' : 'تصفح البرامج' }}</a>
                <a class="tu-hero__textlink" href="{{ $howUrl }}">{{ $isEn ? 'How it works' : 'كيف تبدأ؟' }}</a>
            </div>

            @if ($heroMetrics->isNotEmpty())
                <ul class="tu-hero__stats">
                    @foreach ($heroMetrics as $metric)
                        <li>
                            @if (filled($metric['value'] ?? null))
                                <strong>{{ $metric['value'] }}{{ $metric['suffix'] ?? '' }}</strong>
                            @endif
                            <span>{{ $metric['label'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="tu-hero__sheet">
        <a class="tu-hero__campus" href="{{ $aboutUrl }}">
            <img src="{{ $showcaseImage }}" alt="{{ $isEn ? 'University campus' : 'حرم الجامعة' }}" width="1600" height="640">
            <span>{{ $isEn ? 'The campus' : 'حرم الجامعة' }}</span>
        </a>

        @if ($fields->isNotEmpty())
            <nav class="tu-hero__fields" aria-label="{{ $isEn ? 'Program fields' : 'المجالات التدريبية' }}">
                <p>{{ $isEn ? 'Choose a field' : 'اختر المجال' }}</p>
                <div class="tu-hero__field-grid">
                    @foreach ($fields as $field)
                        <a href="{{ $field->coursesIndexUrl($locale) }}">
                            <span>{{ $field->displayTitle() }}</span>
                            @if ((int) ($field->courses_count ?? 0) > 0)
                                <b>{{ $field->courses_count }}</b>
                            @endif
                        </a>
                    @endforeach
                    <a class="is-all" href="{{ $coursesUrl }}">
                        <span>{{ $isEn ? 'All programs' : 'كل البرامج' }}</span>
                    </a>
                </div>
            </nav>
        @endif
    </div>
</section>
