@php
    $data = $block['data'] ?? [];
    $logos = collect($data['logos'] ?? [])->filter(fn ($logo) => filled($logo['image'] ?? null))->values();
    $title = (string) ($data['title'] ?? '');
    $variant = str_contains($title, 'معتمد') || str_contains(strtolower($title), 'accredit')
        ? 'accredited'
        : 'partners';
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/cms-blocks-front.css') }}?v=3">
    @endpush
@endonce

<div class="client-slider-sec cms-logo-carousel lg-logo-band lg-logo-band--{{ $variant }}">
    <div class="container">
        @if ($title !== '')
            <div class="section-header aos" data-aos="fade-up">
                <h2>{{ $title }}</h2>
            </div>
        @endif

        @if ($logos->isEmpty())
            <p class="cms-logo-carousel__empty">لا توجد شعارات معروضة حالياً.</p>
        @else
            <div class="lg-logo-marquee" data-logo-marquee data-marquee-speed="{{ $variant === 'accredited' ? '36' : '42' }}" data-marquee-reverse="{{ $variant === 'accredited' ? 'true' : 'false' }}">
                <div class="lg-logo-marquee__viewport" dir="ltr">
                    <div class="lg-logo-marquee__track" data-logo-marquee-track>
                        <div class="lg-logo-marquee__group" data-logo-marquee-group>
                            @foreach ($logos as $logo)
                                <div class="lg-logo-marquee__item">
                                    <img src="{{ cms_media_url($logo['image'] ?? '') }}" alt="{{ $logo['alt'] ?? '' }}" loading="lazy">
                                </div>
                            @endforeach
                        </div>
                        <div class="lg-logo-marquee__group" aria-hidden="true">
                            @foreach ($logos as $logo)
                                <div class="lg-logo-marquee__item">
                                    <img src="{{ cms_media_url($logo['image'] ?? '') }}" alt="" loading="lazy">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
