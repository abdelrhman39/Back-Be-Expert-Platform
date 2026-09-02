@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $items = collect($data['items'] ?? [])->values();
    $title = trim((string) ($data['title'] ?? ''));
    $lead = trim((string) ($data['lead'] ?? ''));
@endphp

<section id="how-it-works" class="np-steps" aria-label="{{ $title !== '' ? $title : ($isEn ? 'How it works' : 'كيف تبدأ') }}">
    <div class="container">
        @if ($title !== '' || $lead !== '')
            <div class="np-steps__intro">
                @if ($title !== '')
                    <h2 class="np-steps__title">{{ $title }}</h2>
                @endif
                @if ($lead !== '')
                    <p class="np-steps__lead">{{ $lead }}</p>
                @endif
            </div>
        @endif

        <ol class="np-steps__grid">
            @foreach ($items as $index => $item)
                @php
                    $step = $item['step'] ?? ($index + 1);
                    $itemTitle = trim((string) ($item['title'] ?? ''));
                    $itemBody = trim((string) ($item['body'] ?? ''));
                @endphp
                <li class="np-steps__card" data-aos="fade-up" data-aos-delay="{{ $index * 70 }}">
                    <span class="np-steps__num" aria-hidden="true">{{ str_pad((string) $step, 2, '0', STR_PAD_LEFT) }}</span>
                    @if ($itemTitle !== '')
                        <h3 class="np-steps__card-title">{{ $itemTitle }}</h3>
                    @endif
                    @if ($itemBody !== '')
                        <p class="np-steps__card-body">{{ $itemBody }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
