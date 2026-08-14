@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $title = trim((string) ($data['title'] ?? ''));
    $subtitleLines = collect($data['subtitle_lines'] ?? [])
        ->map(fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $lead = filled($data['subtitle'] ?? null)
        ? trim((string) $data['subtitle'])
        : $subtitleLines->implode($isEn ? ' — ' : ' · ');
    $image = static_asset($data['image'] ?? 'assets/1857921787411122.jpeg');
    $searchEnabled = (bool) ($data['search_enabled'] ?? false);
    $coursesUrl = route('courses.index', ['locale' => $locale]);
@endphp

<section class="np-home-hero" aria-label="{{ $isEn ? 'Homepage hero' : 'مقدمة الصفحة الرئيسية' }}">
    <div class="np-home-hero__media" aria-hidden="true">
        <img src="{{ $image }}" alt="" class="np-home-hero__image" width="1920" height="1080" decoding="async" fetchpriority="high">
        <div class="np-home-hero__shade"></div>
        <div class="np-home-hero__glow"></div>
    </div>

    <div class="np-home-hero__content">
        <div class="container np-home-hero__container">
            <div class="np-home-hero__copy">
                @if ($title !== '')
                    <h1 class="np-home-hero__brand">{{ $title }}</h1>
                @endif
                @if ($lead !== '')
                    <p class="np-home-hero__lead">{{ $lead }}</p>
                @endif
            </div>

            @if ($searchEnabled)
                <form class="np-home-hero__search" action="{{ $coursesUrl }}" method="get" role="search">
                    <div class="np-home-hero__field">
                        <label for="np-home-hero-field">{{ $isEn ? 'Field' : 'المجال' }}</label>
                        <div class="np-home-hero__control">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            <select id="np-home-hero-field" name="fields[]">
                                <option value="">{{ $isEn ? 'All fields' : 'كل المجالات' }}</option>
                                @foreach ($popularFields ?? collect() as $field)
                                    <option value="{{ $field->id }}">{{ $field->displayTitle() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="np-home-hero__field np-home-hero__field--grow">
                        <label for="np-home-hero-q">{{ $isEn ? 'Search' : 'البحث' }}</label>
                        <div class="np-home-hero__control">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input
                                id="np-home-hero-q"
                                type="search"
                                name="q"
                                placeholder="{{ $isEn ? 'Search courses and diplomas…' : 'ابحث عن دورة أو دبلوم…' }}"
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <div class="np-home-hero__actions">
                        <button type="submit" class="np-home-hero__submit">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <span>{{ $isEn ? 'Search' : 'بحث' }}</span>
                        </button>
                    </div>
                </form>
            @endif

            <div class="np-home-hero__cta">
                <a href="{{ $coursesUrl }}" class="np-home-hero__link">
                    <span>{{ $isEn ? 'Browse all courses' : 'تصفح جميع الدورات' }}</span>
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>
