@php
    use App\Support\CmsBlockLink;

    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $eyebrow = $data['eyebrow'] ?? ($isEn ? 'We are here to help' : 'نحن هنا لمساعدتكم');
    $buttonStyles = [
        'primary' => 'contact-cta contact-cta--primary',
        'outline-primary' => 'contact-cta contact-cta--outline',
        'outline-secondary' => 'contact-cta contact-cta--ghost',
        'secondary' => 'contact-cta contact-cta--ghost',
    ];
    $buttonIcons = [
        'support.faq' => 'fa-regular fa-circle-question',
        'support.ticket.new' => 'fa-regular fa-life-ring',
        'support.ticket.search' => 'fa-solid fa-magnifying-glass',
    ];
@endphp

<section class="contact-page-intro">
    <div class="container">
        <div class="contact-intro">
            @if (filled($eyebrow))
                <span class="contact-eyebrow">{{ $eyebrow }}</span>
            @endif
            @if (filled($data['title'] ?? null))
                <h2 class="contact-intro__title">{{ $data['title'] }}</h2>
            @endif
            @if (filled($data['body'] ?? null))
                <p class="contact-intro__body">{{ $data['body'] }}</p>
            @endif
            @if (! empty($data['buttons']))
                <div class="contact-intro__actions">
                    @foreach ($data['buttons'] as $button)
                        @php
                            $style = $buttonStyles[$button['style'] ?? 'primary'] ?? 'contact-cta contact-cta--primary';
                            $href = CmsBlockLink::href($button, $locale);
                            $icon = $buttonIcons[$button['link'] ?? ''] ?? 'fa-solid fa-arrow-left';
                        @endphp
                        <a class="{{ $style }}" href="{{ $href }}">
                            <i class="{{ $icon }}" aria-hidden="true"></i>
                            <span>{{ $button['label'] ?? '' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
