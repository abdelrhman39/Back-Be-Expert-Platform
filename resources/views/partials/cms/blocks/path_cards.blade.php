@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $items = collect($data['items'] ?? [])->values();
    $title = trim((string) ($data['title'] ?? ''));
    $lead = trim((string) ($data['lead'] ?? ''));
@endphp

<section class="np-paths" aria-label="{{ $title !== '' ? $title : ($isEn ? 'Who we serve' : 'لمن نقدّم خدماتنا') }}">
    <div class="container">
        @if ($title !== '' || $lead !== '')
            <div class="np-paths__intro">
                @if ($title !== '')
                    <h2 class="np-paths__title">{{ $title }}</h2>
                @endif
                @if ($lead !== '')
                    <p class="np-paths__lead">{{ $lead }}</p>
                @endif
            </div>
        @endif

        <div class="np-paths__grid">
            @foreach ($items as $index => $item)
                @php
                    $itemTitle = trim((string) ($item['title'] ?? ''));
                    $itemBody = trim((string) ($item['body'] ?? ''));
                    $cta = trim((string) ($item['cta_label'] ?? ($isEn ? 'Learn more' : 'اعرف المزيد')));
                    $rawUrl = trim((string) ($item['url'] ?? ''));
                    $href = $rawUrl === '' || $rawUrl === '#'
                        ? '#'
                        : (str_contains($rawUrl, '.') || str_starts_with($rawUrl, '/') || str_starts_with($rawUrl, 'http')
                            ? cms_href($rawUrl)
                            : url('/'.$locale.'/'.ltrim($rawUrl, '/')));
                    $icon = trim((string) ($item['icon'] ?? 'fa-solid fa-circle'));
                    $iconIsImage = str_contains($icon, '/') || str_ends_with(strtolower($icon), '.png') || str_ends_with(strtolower($icon), '.svg');
                @endphp
                <article class="np-paths__card" data-aos="fade-up" data-aos-delay="{{ $index * 70 }}">
                    <div class="np-paths__icon" aria-hidden="true">
                        @if ($iconIsImage)
                            <img src="{{ static_asset($icon) }}" alt="" width="28" height="28">
                        @else
                            <i class="{{ $icon }}"></i>
                        @endif
                    </div>
                    @if ($itemTitle !== '')
                        <h3 class="np-paths__card-title">{{ $itemTitle }}</h3>
                    @endif
                    @if ($itemBody !== '')
                        <p class="np-paths__card-body">{{ $itemBody }}</p>
                    @endif
                    @if ($href !== '#')
                        <a class="np-paths__link" href="{{ $href }}">
                            <span>{{ $cta }}</span>
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
