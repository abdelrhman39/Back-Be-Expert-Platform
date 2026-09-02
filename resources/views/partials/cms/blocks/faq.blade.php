@php
    $data = $block['data'] ?? [];
    $items = $data['items'] ?? [];
@endphp

<section class="atelier-faq">
    <div class="container">
        @if ($data['title'] ?? null)
            <div class="section-header aos" data-aos="fade-up">
                <h2>{{ $data['title'] }}</h2>
            </div>
        @elseif ($data['platform_name'] ?? null)
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
        @endif
        <div class="chapter-accordion accordion">
            @foreach ($items as $index => $item)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button @class(['accordion-button', 'collapsed' => $index > 0]) type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $block['id'] ?? 'block' }}-{{ $index }}">
                            <i class="fas fa-question-circle mx-2"></i>
                            {{ $item['question'] ?? '' }}
                        </button>
                    </h2>
                    <div id="faq-{{ $block['id'] ?? 'block' }}-{{ $index }}" @class(['accordion-collapse', 'collapse', 'show' => $index === 0])>
                        <div class="accordion-body">{{ $item['answer'] ?? '' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
