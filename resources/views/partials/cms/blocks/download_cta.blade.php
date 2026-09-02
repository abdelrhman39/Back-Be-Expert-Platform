@php
    $data = $block['data'] ?? [];
    $fileUrl = $data['file_url'] ?? '';
    $href = $fileUrl === '' ? '#' : (str_starts_with($fileUrl, 'http') ? $fileUrl : static_asset($fileUrl));
    $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
@endphp

<section class="about-profile">
    <div class="container">
        <div class="about-profile__panel">
            <div class="about-profile__visual" aria-hidden="true">
                <i class="fa-regular fa-file-pdf"></i>
            </div>
            <div class="about-profile__copy">
                @if ($eyebrow !== '')
                    <span class="about-eyebrow about-eyebrow--light">{{ $eyebrow }}</span>
                @endif
                @if ($data['title'] ?? null)
                    <h2 class="about-profile__title">{{ $data['title'] }}</h2>
                @endif
                @if ($data['description'] ?? null)
                    <p class="about-profile__body">{{ $data['description'] }}</p>
                @endif
            </div>
            @if ($data['button_label'] ?? null)
                <a href="{{ $href }}" target="_blank" rel="noopener" class="about-cta about-cta--on-dark">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    <span>{{ $data['button_label'] }}</span>
                </a>
            @endif
        </div>
    </div>
</section>
