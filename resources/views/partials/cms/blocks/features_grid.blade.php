@php
    $data = $block['data'] ?? [];
    $items = $data['items'] ?? [];
@endphp

<section class="popular-section expert-section">
    <div class="container">
        @if ($data['title'] ?? null)
            <div class="expert-header">
                <div class="section-header text-center aos" data-aos="fade-up">
                    <h2><span>{{ $data['title'] }}</span></h2>
                </div>
            </div>
        @endif
        <div class="expert-wrapper">
            <div class="row gx-0 justify-content-center">
                @foreach ($items as $item)
                    <div class="col-lg-4 col-md-6 aos" data-aos="fade-up">
                        <div class="expert-item">
                            @if ($item['icon'] ?? null)
                                <div class="expert-icon">
                                    <img src="{{ static_asset($item['icon']) }}" alt="">
                                </div>
                            @endif
                            <div class="expert-info">
                                @if ($item['title'] ?? null)
                                    <h4>{{ $item['title'] }}</h4>
                                @endif
                                @if ($item['body'] ?? null)
                                    <p dir="auto">{{ $item['body'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
