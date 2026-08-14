@php
    $title = $title ?? '';
    $desc = $desc ?? '';
    $icon = $icon ?? 'fa-chalkboard-user';
    $stats = $stats ?? [];
    $actions = $actions ?? [];
@endphp

<section class="portal-hero portal-hero--v2 portal-hero--page">
    <div class="portal-hero__banner portal-hero__banner--compact">
        <div class="portal-hero__banner-content">
            <div class="portal-hero__welcome">
                <span class="portal-hero__eyebrow"><i class="fa-solid {{ $icon }}"></i> لوحة المدرب</span>
                <span class="portal-hero__greeting">{{ $title }}</span>
                @if ($desc !== '')
                    <p class="portal-hero__tagline">{{ $desc }}</p>
                @endif
            </div>
            @if ($stats !== [])
                <div class="portal-hero__banner-stats">
                    @foreach ($stats as $stat)
                        <div class="portal-banner-stat">
                            <span class="portal-banner-stat__value">{{ $stat['value'] }}</span>
                            <span class="portal-banner-stat__label">{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="portal-hero__orbs" aria-hidden="true"><span></span><span></span><span></span></div>
    </div>
    @if ($actions !== [])
        <div class="portal-hero__body portal-hero__body--compact">
            <div class="portal-hero__actions portal-hero__actions--start">
                @foreach ($actions as $action)
                    <a href="{{ $action['href'] }}" @class(['btn', $action['class'] ?? 'btn-primary'])>
                        @if (! empty($action['icon']))<i class="fa-solid {{ $action['icon'] }}"></i>@endif
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>
