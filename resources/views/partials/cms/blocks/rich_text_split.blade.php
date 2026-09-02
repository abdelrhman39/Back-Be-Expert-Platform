@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $paragraphs = array_values(array_filter($data['paragraphs'] ?? [], fn ($p) => filled($p)));
    $highlights = array_values(array_filter($data['highlights'] ?? [], fn ($h) => filled(is_array($h) ? ($h['text'] ?? '') : $h)));
    $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
    $title = trim((string) ($data['title'] ?? ''));
    $badge = trim((string) ($data['image_badge'] ?? ''));
    $primaryLabel = trim((string) ($data['primary_label'] ?? ''));
    $secondaryLabel = trim((string) ($data['secondary_label'] ?? ''));
    $primaryUrl = filled($data['primary_url'] ?? null) ? cms_href($data['primary_url']) : '';
    $secondaryUrl = filled($data['secondary_url'] ?? null) ? cms_href($data['secondary_url']) : '';
@endphp

<section class="about-intro">
    <div class="container">
        <div class="about-intro__grid">
            <div class="about-intro__media">
                <div class="about-intro__frame">
                    <img
                        src="{{ static_asset($data['image'] ?? platform_campus_path('entrance')) }}"
                        alt="{{ $title !== '' ? $title : ($isEn ? 'About the platform' : 'عن المنصة') }}"
                    >
                    @if ($badge !== '')
                        <span class="about-intro__badge">{{ $badge }}</span>
                    @endif
                </div>
            </div>
            <div class="about-intro__copy">
                @if ($eyebrow !== '')
                    <span class="about-eyebrow">{{ $eyebrow }}</span>
                @endif
                @if ($title !== '')
                    <h2 class="about-intro__title">{{ $title }}</h2>
                @endif
                @foreach ($paragraphs as $paragraph)
                    <p class="about-intro__body" dir="auto">{{ $paragraph }}</p>
                @endforeach
                @if ($highlights !== [])
                    <ul class="about-intro__highlights">
                        @foreach ($highlights as $highlight)
                            <li>
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                <span>{{ is_array($highlight) ? ($highlight['text'] ?? '') : $highlight }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if ($primaryLabel !== '' || $secondaryLabel !== '')
                    <div class="about-intro__actions">
                        @if ($primaryLabel !== '')
                            <a class="about-cta about-cta--primary" href="{{ $primaryUrl !== '' ? $primaryUrl : '#' }}">{{ $primaryLabel }}</a>
                        @endif
                        @if ($secondaryLabel !== '')
                            <a class="about-cta about-cta--ghost" href="{{ $secondaryUrl !== '' ? $secondaryUrl : '#' }}">{{ $secondaryLabel }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
