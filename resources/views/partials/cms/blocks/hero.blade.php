@php
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $data = $block['data'] ?? [];
    $title = trim((string) ($data['title'] ?? ''));
    $subtitleLines = collect($data['subtitle_lines'] ?? [])
        ->map(fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $legacySubtitle = trim((string) ($data['subtitle'] ?? ''));
    $heroSlides = collect($data['gallery'] ?? [])
        ->prepend($data['image'] ?? platform_campus_path('aerial'))
        ->merge(platform_campus_gallery())
        ->map(fn ($path) => trim((string) $path))
        ->filter()
        ->unique()
        ->map(fn ($path) => cms_media_url($path))
        ->filter()
        ->unique()
        ->values();
    if ($heroSlides->isEmpty()) {
        $heroSlides = collect([static_asset(platform_campus_path('aerial'))]);
    }
    $showcaseImage = cms_media_url(
        $data['showcase_image'] ?? platform_campus_path('entrance'),
        static_asset(platform_campus_path('entrance'))
    );
    $panelSlides = collect([
            $data['showcase_image'] ?? platform_campus_path('entrance'),
            platform_campus_path('entrance'),
            platform_campus_path('aerial'),
        ])
        ->map(fn ($path) => trim((string) $path))
        ->filter()
        ->unique()
        ->map(fn ($path) => cms_media_url($path))
        ->filter()
        ->unique()
        ->values();
    $cmsVideo = trim((string) ($data['showcase_video'] ?? ''));
    $campusVideo = platform_campus_video_path();
    $showcaseVideo = $cmsVideo !== ''
        ? cms_media_url($cmsVideo)
        : ($campusVideo ? static_asset($campusVideo) : '');
    $showcaseVideoType = str_ends_with(strtolower(parse_url($showcaseVideo, PHP_URL_PATH) ?: $showcaseVideo), '.webm')
        ? 'video/webm'
        : 'video/mp4';
    $searchEnabled = (bool) ($data['search_enabled'] ?? false);
    $coursesUrl = route('courses.index', ['locale' => $locale]);
    $aboutUrl = route('about', ['locale' => $locale]);
    $howUrl = '#how-it-works';
    $eyebrow = platform_org();
    $heroMetrics = collect($heroMetrics ?? []);
    if ($heroMetrics->isEmpty()) {
        $heroMetrics = collect(app(\App\Services\HomePageService::class)->heroMetrics($locale));
    }
@endphp

<section class="np-home-hero np-home-hero--logistics" aria-label="{{ $isEn ? 'Homepage hero' : 'مقدمة الصفحة الرئيسية' }}">
    <div class="np-home-hero__media" aria-hidden="true">
        <div
            class="np-home-hero__slider np-home-hero__slider--backdrop"
            data-hero-slider
            data-hero-interval="8000"
        >
            @foreach ($heroSlides as $slideSrc)
                <div class="np-home-hero__slide{{ $loop->first ? ' is-active' : '' }}" data-hero-slide>
                    <img
                        src="{{ $slideSrc }}"
                        alt=""
                        class="np-home-hero__image"
                        width="1920"
                        height="1080"
                        decoding="async"
                        @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                    >
                </div>
            @endforeach
        </div>
        <div class="np-home-hero__shade"></div>
    </div>

    <div class="np-home-hero__content">
        <div class="container np-home-hero__container">
            <div class="np-home-hero__main">
            <div class="np-home-hero__copy">
                @if ($eyebrow !== '')
                    <span class="np-home-hero__eyebrow">{{ $eyebrow }}</span>
                @endif
                @if ($title !== '')
                    <h1 class="np-home-hero__brand">{{ $title }}</h1>
                @endif
                @if ($subtitleLines->isNotEmpty())
                    <p class="np-home-hero__lead">
                        @foreach ($subtitleLines as $line)
                            <span class="np-home-hero__lead-line">{{ $line }}</span>
                        @endforeach
                    </p>
                @elseif ($legacySubtitle !== '')
                    <p class="np-home-hero__lead">{{ $legacySubtitle }}</p>
                @endif
                <p class="np-home-hero__trust">{{ $isEn ? 'Accredited programs for individuals, companies, and institutions.' : 'برامج معتمدة للأفراد والشركات والجهات، بتجربة تسجيل واضحة.' }}</p>
            </div>

            <div class="np-home-hero__buttons">
                <a href="{{ $coursesUrl }}" class="np-home-hero__btn np-home-hero__btn--solid">
                    {{ $isEn ? 'Browse programs' : 'تصفح البرامج' }}
                </a>
                <a href="{{ $howUrl }}" class="np-home-hero__btn np-home-hero__btn--ghost">
                    <span class="np-home-hero__play" aria-hidden="true">
                        <i class="fa-solid fa-play"></i>
                    </span>
                    {{ $isEn ? 'How it works?' : 'كيف تبدأ؟' }}
                </a>
            </div>

            @if ($searchEnabled)
                <form class="np-home-hero__search" action="{{ $coursesUrl }}" method="get" role="search">
                    <div class="np-home-hero__field">
                        <label class="visually-hidden" for="np-home-hero-field">{{ $isEn ? 'Field' : 'المجال' }}</label>
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
                        <label class="visually-hidden" for="np-home-hero-q">{{ $isEn ? 'Search' : 'البحث' }}</label>
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
                            <span>{{ $isEn ? 'Search' : 'بحث' }}</span>
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            @endif
            </div>

            <aside class="np-home-hero__aside">
                <article class="np-home-hero__panel">
                    <a
                        class="np-home-hero__panel-media{{ $showcaseVideo !== '' ? ' np-home-hero__panel-media--video' : ' np-home-hero__panel-media--reel' }}"
                        href="{{ $aboutUrl }}"
                        aria-label="{{ $isEn ? 'About the platform' : 'تعرّف على المنصة' }}"
                    >
                        @if ($showcaseVideo !== '')
                            <video
                                class="np-home-hero__panel-video"
                                data-hero-video
                                autoplay
                                muted
                                loop
                                playsinline
                                preload="metadata"
                                poster="{{ $showcaseImage }}"
                            >
                                <source src="{{ $showcaseVideo }}" type="{{ $showcaseVideoType }}">
                            </video>
                        @else
                            <div
                                class="np-home-hero__slider np-home-hero__slider--panel"
                                data-hero-slider
                                data-hero-interval="5600"
                            >
                                @foreach ($panelSlides as $slideSrc)
                                    <div class="np-home-hero__slide{{ $loop->first ? ' is-active' : '' }}" data-hero-slide>
                                        <img
                                            src="{{ $slideSrc }}"
                                            alt=""
                                            width="1024"
                                            height="679"
                                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                        >
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </a>
                    <div class="np-home-hero__panel-body">
                        <p class="np-home-hero__panel-kicker">{{ $isEn ? 'University campus' : 'حرم الجامعة' }}</p>
                        <h2 class="np-home-hero__panel-title">{{ platform_org() }}</h2>
                        @if ($heroMetrics->isNotEmpty())
                            <ul class="np-home-hero__facts">
                                @foreach ($heroMetrics as $metric)
                                    <li>
                                        <i class="{{ $metric['icon'] ?? 'fa-solid fa-circle-check' }}" aria-hidden="true"></i>
                                        <span>
                                            @if (filled($metric['value'] ?? null))
                                                <strong>{{ $metric['value'] }}{{ $metric['suffix'] ?? '' }}</strong>
                                            @endif
                                            {{ $metric['label'] ?? '' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <a class="np-home-hero__panel-cta" href="{{ $aboutUrl }}">
                            {{ $isEn ? 'About the platform' : 'تعرّف على المنصة' }}
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
            </aside>
        </div>
    </div>
</section>

@include('partials.catalog.home-fields-bar', ['fields' => $popularFields ?? collect()])
