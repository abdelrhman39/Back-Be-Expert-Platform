@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
    $title = trim((string) ($data['title'] ?? ''));
    $body = trim((string) ($data['body'] ?? ''));
    $primaryLabel = trim((string) ($data['primary_label'] ?? ''));
    $secondaryLabel = trim((string) ($data['secondary_label'] ?? ''));
    $primaryUrl = cms_href($data['primary_url'] ?? 'courses.index');
    $secondaryUrl = cms_href($data['secondary_url'] ?? 'contact');
@endphp

<section class="np-cta np-cta--banner" aria-label="{{ $title !== '' ? $title : ($isEn ? 'Get started' : 'ابدأ الآن') }}">
    <div class="container">
        <div class="np-cta__panel">
            <div class="np-cta__copy">
                @if ($eyebrow !== '')
                    <p class="np-cta__eyebrow">{{ $eyebrow }}</p>
                @endif
                @if ($title !== '')
                    <h2 class="np-cta__title">{{ $title }}</h2>
                @endif
                @if ($body !== '')
                    <p class="np-cta__body">{{ $body }}</p>
                @endif
            </div>
            <div class="np-cta__actions">
                @if ($primaryLabel !== '')
                    <a class="np-cta__btn np-cta__btn--primary" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
                @endif
                @if ($secondaryLabel !== '')
                    <a class="np-cta__btn np-cta__btn--ghost" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
