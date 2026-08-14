@php
    $data = $block['data'] ?? [];
    $logos = collect($data['logos'] ?? [])->filter(fn ($logo) => filled($logo['image'] ?? null))->values();
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/cms-blocks-front.css') }}?v=1">
    @endpush
@endonce

<div class="client-slider-sec cms-logo-carousel">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if ($data['title'] ?? null)
                    <div class="section-header aos" data-aos="fade-up">
                        <h2>{{ $data['title'] }}</h2>
                    </div>
                @endif

                @if ($logos->isEmpty())
                    <p class="cms-logo-carousel__empty">لا توجد شعارات معروضة حالياً.</p>
                @elseif ($logos->count() <= 4)
                    <div class="cms-logo-carousel__grid aos" data-aos="fade-up">
                        @foreach ($logos as $logo)
                            <div class="client-logo cms-logo-carousel__item">
                                <img src="{{ cms_media_url($logo['image'] ?? '') }}" class="w-auto" alt="{{ $logo['alt'] ?? '' }}" loading="lazy">
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="clients-slider owl-carousel owl-rtl cms-logo-carousel__slider">
                        @foreach ($logos as $logo)
                            <div class="client-logo cms-logo-carousel__item">
                                <img src="{{ cms_media_url($logo['image'] ?? '') }}" class="w-auto" alt="{{ $logo['alt'] ?? '' }}" loading="lazy">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
