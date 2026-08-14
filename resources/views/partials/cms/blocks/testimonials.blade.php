@php
    $data = $block['data'] ?? [];
    $items = collect($data['items'] ?? [])->filter(fn ($item) => filled($item['quote'] ?? null) || filled($item['name'] ?? null))->values();
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/cms-blocks-front.css') }}?v=1">
    @endpush
@endonce

<section class="testimonial-section cms-testimonials">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if ($data['title'] ?? null)
                    <div class="section-header aos" data-aos="fade-up">
                        <h2>{{ $data['title'] }}</h2>
                    </div>
                @endif

                @if ($items->isEmpty())
                    <p class="cms-testimonials__empty">لا توجد آراء معروضة حالياً.</p>
                @elseif ($items->count() === 1)
                    @php $item = $items->first(); @endphp
                    <div class="cms-testimonials__single aos" data-aos="fade-up">
                        <article class="testimonial-item cms-testimonial-card">
                            <div class="cms-testimonial-card__quote-mark" aria-hidden="true">“</div>
                            @if ($item['quote'] ?? null)
                                <p class="cms-testimonial-card__quote">{{ $item['quote'] }}</p>
                            @endif
                            <div class="star-rate">
                                <span>
                                    @for ($i = 0; $i < max(1, min(5, (int) ($item['rating'] ?? 5))); $i++)
                                        <i class="fa-solid fa-star filled"></i>
                                    @endfor
                                </span>
                            </div>
                            <div class="testimonial-user">
                                @if ($item['avatar'] ?? null)
                                    <img src="{{ cms_media_url($item['avatar']) }}" alt="{{ $item['name'] ?? '' }}">
                                @endif
                                <div class="testimonial-info">
                                    @if ($item['name'] ?? null)
                                        <h6>{{ $item['name'] }}</h6>
                                    @endif
                                    @if ($item['role'] ?? null)
                                        <p>{{ $item['role'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @else
                    <div class="testimonial-slider owl-carousel owl-rtl cms-testimonials__slider">
                        @foreach ($items as $item)
                            <div class="aos" data-aos="fade-up">
                                <article class="testimonial-item cms-testimonial-card">
                                    <div class="cms-testimonial-card__quote-mark" aria-hidden="true">“</div>
                                    @if ($item['quote'] ?? null)
                                        <p class="cms-testimonial-card__quote">{{ $item['quote'] }}</p>
                                    @endif
                                    <div class="star-rate">
                                        <span>
                                            @for ($i = 0; $i < max(1, min(5, (int) ($item['rating'] ?? 5))); $i++)
                                                <i class="fa-solid fa-star filled"></i>
                                            @endfor
                                        </span>
                                    </div>
                                    <div class="testimonial-user">
                                        @if ($item['avatar'] ?? null)
                                            <img src="{{ cms_media_url($item['avatar']) }}" alt="{{ $item['name'] ?? '' }}">
                                        @endif
                                        <div class="testimonial-info">
                                            @if ($item['name'] ?? null)
                                                <h6>{{ $item['name'] }}</h6>
                                            @endif
                                            @if ($item['role'] ?? null)
                                                <p>{{ $item['role'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
