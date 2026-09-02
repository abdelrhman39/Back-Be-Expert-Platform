@php
    $items = collect($block['data']['items'] ?? [])->values();
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $sectionTitle = $block['data']['title'] ?? null;
    $sectionLead = $block['data']['lead'] ?? null;
    $icons = ['fa-bullseye', 'fa-lightbulb', 'fa-flag'];
@endphp

<section class="np-mvg" aria-label="{{ $sectionTitle ?: ($isEn ? 'Mission, vision and goals' : 'المهمة والرؤية والأهداف') }}">
    <div class="container">
        @if ($sectionTitle || $sectionLead)
            <div class="np-mvg__intro">
                @if ($sectionTitle)
                    <h2 class="np-mvg__title">{{ $sectionTitle }}</h2>
                @endif
                @if ($sectionLead)
                    <p class="np-mvg__lead">{{ $sectionLead }}</p>
                @endif
            </div>
        @endif

        <div class="np-mvg__grid">
            @foreach ($items as $index => $item)
                @php
                    $title = $item['title'] ?? '';
                    $body = $item['body'] ?? '';
                    $iconPath = $item['icon'] ?? null;
                    $fallbackIcon = $icons[$index % count($icons)];
                    $iconIsFa = is_string($iconPath) && str_starts_with($iconPath, 'fa-');
                    $iconIsImage = is_string($iconPath) && $iconPath !== '' && ! $iconIsFa;
                @endphp
                <article class="np-mvg__card" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                    <div class="np-mvg__icon" aria-hidden="true">
                        @if ($iconIsFa)
                            <i class="{{ $iconPath }}"></i>
                        @elseif ($iconIsImage)
                            <img src="{{ static_asset($iconPath) }}" alt="" width="56" height="56" loading="lazy">
                        @else
                            <i class="fa-solid {{ $fallbackIcon }}"></i>
                        @endif
                    </div>
                    @if ($title !== '')
                        <h3 class="np-mvg__card-title">{{ $title }}</h3>
                    @endif
                    @if ($body !== '')
                        <p class="np-mvg__card-body" dir="auto">{{ $body }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
