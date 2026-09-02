@php
    $catalog = app(\App\Support\LegacyCourseCatalog::class);
    $meta = $catalog->resolveForItem($item);
    $locale = app()->getLocale();
    $t = fn (string $key) => \App\Support\PublicCopy::cart($key, $locale);
    $courseUrl = $meta['url'];
    $imagePath = resolve_poster_url($meta['image'] ?? null);
    $price = isset($item->price_snapshot) ? (float) $item->price_snapshot : (isset($item->price) ? (float) $item->price : null);
    $deliveryLabel = method_exists($item, 'deliveryLabel') ? $item->deliveryLabel() : null;
    $mode = $mode ?? 'cart';
    $isInteractive = in_array($mode, ['cart', 'wishlist'], true);
@endphp

<article class="commerce-list-item commerce-list-item--{{ $mode }}" wire:key="commerce-item-{{ $mode }}-{{ $item->id }}">
    <div class="commerce-list-row">
        <a href="{{ $courseUrl }}" class="commerce-list-thumb" title="{{ $t('view_details') }}">
            <img src="{{ $imagePath }}" alt="{{ $meta['title'] }}" loading="lazy">
        </a>

        <div class="commerce-list-body">
            <div class="commerce-list-meta">
                <span @class(['commerce-list-type', 'commerce-list-type--diploma' => $meta['is_diploma']])>
                    {{ $meta['type_label'] }}
                </span>
                @if ($deliveryLabel)
                    <span class="commerce-list-chip">{{ $deliveryLabel }}</span>
                @endif
            </div>

            <h4 class="commerce-list-title">
                <a href="{{ $courseUrl }}" class="commerce-list-title__link" title="{{ $t('view_details') }}">
                    {{ $meta['title'] }}
                    <i class="fa-solid fa-arrow-up-left commerce-list-title__hint" aria-hidden="true"></i>
                </a>
            </h4>
        </div>

        <div class="commerce-list-side">
            @if ($price !== null && in_array($mode, ['cart', 'readonly'], true))
                <div class="commerce-list-price" dir="ltr">
                    {{ number_format($price, 0) }}
                    <small>{{ $t('sar') }}</small>
                </div>
            @endif

            @if ($isInteractive)
                <div class="commerce-list-actions">
                    @include('components.commerce.remove-button', ['item' => $item, 'mode' => $mode])
                </div>
            @endif
        </div>
    </div>
</article>
