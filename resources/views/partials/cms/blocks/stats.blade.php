@php
    $data = $block['data'] ?? [];
    $items = $data['items'] ?? [];
@endphp

<section class="counterSec">
    <div class="container">
        @if ($data['platform_name'] ?? null)
            <div class="section-header aos" data-aos="fade-up">
                <h2>
                    @if ($data['title_prefix'] ?? null)
                        {{ $data['title_prefix'] }}
                    @endif
                    <span class="site_name">{{ $data['platform_name'] }}</span>
                    @if ($data['title_suffix'] ?? null)
                        {{ $data['title_suffix'] }}
                    @endif
                </h2>
            </div>
        @elseif ($data['title'] ?? null)
            <div class="section-header aos" data-aos="fade-up">
                <h2>{{ $data['title'] }}</h2>
            </div>
        @endif
        <div class="counter-wrap">
            <div class="row">
                @foreach ($items as $item)
                    <div class="col-lg-3 col-sm-6">
                        <div class="counter-item mb-4">
                            <h6 class="mb-1 d-flex align-items-center justify-content-center">
                                @if ($item['icon'] ?? null)
                                    <i class="{{ $item['icon'] }} me-2"></i>
                                @endif
                                {{ $item['label'] ?? '' }}
                            </h6>
                            <h3 class="display-6">
                                <span class="counter animated fadeInDownBig">{{ $item['value'] ?? 0 }}</span>{{ $item['suffix'] ?? '' }}
                            </h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
