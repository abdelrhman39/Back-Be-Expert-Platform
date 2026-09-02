@php
    $data = $block['data'] ?? [];
    $items = array_values($data['items'] ?? []);
    $title = trim((string) ($data['title'] ?? ''));
    $lead = trim((string) ($data['lead'] ?? ''));
    $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
    $count = count($items);
    $isHome = ($block['id'] ?? '') === 'platform_features';
    $iconMap = $isHome
        ? [
            '1853132469069541.png' => 'fa-solid fa-certificate',
            '1853132752703292.png' => 'fa-solid fa-layer-group',
            '1853133034226589.png' => 'fa-solid fa-chalkboard-user',
            '1853133514491196.png' => 'fa-solid fa-display',
            '1853382791824256.png' => 'fa-solid fa-lightbulb',
        ]
        : [
            '1853132469069541.png' => 'fa-solid fa-building-columns',
            '1853132752703292.png' => 'fa-solid fa-certificate',
            '1853133034226589.png' => 'fa-solid fa-chalkboard-user',
            '1853133514491196.png' => 'fa-solid fa-display',
            '1853382791824256.png' => 'fa-solid fa-briefcase',
        ];
    $fallbackFa = $isHome
        ? [
            'fa-solid fa-certificate',
            'fa-solid fa-layer-group',
            'fa-solid fa-chalkboard-user',
            'fa-solid fa-display',
            'fa-solid fa-lightbulb',
        ]
        : [
            'fa-solid fa-building-columns',
            'fa-solid fa-certificate',
            'fa-solid fa-chalkboard-user',
            'fa-solid fa-display',
            'fa-solid fa-briefcase',
        ];
@endphp

<section @class(['cms-features', 'cms-features--home' => $isHome]) @if ($title !== '') aria-labelledby="cms-features-title" @endif>
    <div class="container">
        @if ($title !== '' || $lead !== '' || $eyebrow !== '')
            <div class="cms-features__intro">
                @if ($eyebrow !== '')
                    <p class="cms-features__eyebrow">{{ $eyebrow }}</p>
                @endif
                @if ($title !== '')
                    <h2 id="cms-features-title" class="cms-features__title">{{ $title }}</h2>
                @endif
                @if ($lead !== '')
                    <p class="cms-features__lead">{{ $lead }}</p>
                @endif
            </div>
        @endif

        <div class="cms-features__grid" data-count="{{ $count }}">
            @foreach ($items as $index => $item)
                @php
                    $itemTitle = trim((string) ($item['title'] ?? ''));
                    $itemBody = trim((string) ($item['body'] ?? ''));
                    $rawIcon = trim((string) ($item['icon'] ?? ''));
                    $basename = strtolower(basename(str_replace('\\', '/', $rawIcon)));
                    $mappedFa = $iconMap[$basename] ?? null;
                    $isFa = $mappedFa !== null
                        || str_starts_with($rawIcon, 'fa-')
                        || str_contains($rawIcon, ' fa-');
                    $faClass = $mappedFa ?? ($isFa ? $rawIcon : ($fallbackFa[$index] ?? 'fa-solid fa-circle-check'));
                    $isImage = ! $isFa && $rawIcon !== '' && (
                        str_contains($rawIcon, '/')
                        || str_ends_with($basename, '.png')
                        || str_ends_with($basename, '.svg')
                        || str_ends_with($basename, '.jpg')
                        || str_ends_with($basename, '.webp')
                    );
                    $featured = $isHome && $index === 0;
                @endphp
                <article @class(['cms-features__card', 'cms-features__card--featured' => $featured]) data-aos="fade-up" data-aos-delay="{{ $index * 70 }}">
                    @if ($featured)
                        <span class="cms-features__glow" aria-hidden="true"></span>
                    @endif
                    <div class="cms-features__top">
                        <span class="cms-features__index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="cms-features__icon" aria-hidden="true">
                            @if ($isImage)
                                <img src="{{ static_asset($rawIcon) }}" alt="">
                            @else
                                <i class="{{ $faClass }}"></i>
                            @endif
                        </div>
                    </div>
                    <div class="cms-features__copy">
                        @if ($itemTitle !== '')
                            <h3 class="cms-features__card-title">{{ $itemTitle }}</h3>
                        @endif
                        @if ($itemBody !== '')
                            <p class="cms-features__card-body" dir="auto">{{ $itemBody }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
