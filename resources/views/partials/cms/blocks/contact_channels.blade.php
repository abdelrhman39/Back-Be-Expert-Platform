@php
    use App\Support\CmsBlockLink;

    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $items = array_values(array_filter($data['items'] ?? [], fn ($item) => $item['enabled'] ?? true));

    $actionLabels = [
        'email' => $isEn ? 'Send an email' : 'إرسال رسالة',
        'phone' => $isEn ? 'Call now' : 'اتصل الآن',
        'whatsapp' => $isEn ? 'Open WhatsApp' : 'محادثة واتساب',
        'address' => $isEn ? 'Campus location' : 'المقر الرئيسي',
        'custom' => $isEn ? 'Open link' : 'فتح الرابط',
    ];
@endphp

@if ($items !== [])
    <section class="contact-channels">
        <div class="container">
            <div class="contact-channels__grid">
                @foreach ($items as $item)
                    @php
                        $kind = $item['kind'] ?? 'custom';
                        $label = $item['label'] ?? '';
                        $value = trim((string) ($item['value'] ?? ''));
                        $icon = $item['icon'] ?? '';
                        $iconType = $item['icon_type'] ?? 'image';
                        $digits = preg_replace('/\D+/', '', $value) ?? '';
                        $phoneDisplay = CmsBlockLink::phoneDisplay($value);
                        $href = match ($kind) {
                            'email' => $value !== '' ? 'mailto:'.$value : '',
                            'phone' => $phoneDisplay !== '' ? 'tel:'.$phoneDisplay : '',
                            'whatsapp' => $digits !== '' ? 'https://wa.me/'.$digits : '',
                            'custom' => (string) ($item['link_url'] ?? ''),
                            default => '',
                        };
                        $isExternal = $kind === 'whatsapp'
                            || ($kind === 'custom' && str_starts_with($href, 'http'));
                        $displayValue = match ($kind) {
                            'phone', 'whatsapp' => $phoneDisplay !== '' ? $phoneDisplay : $value,
                            default => $value,
                        };
                    @endphp

                    @if ($href !== '')
                        <a
                            class="contact-channel contact-channel--{{ $kind }}"
                            href="{{ $href }}"
                            @if ($isExternal) target="_blank" rel="noopener" @endif
                        >
                    @else
                        <div class="contact-channel contact-channel--{{ $kind }}">
                    @endif
                            <div class="contact-channel__icon" aria-hidden="true">
                                @if ($icon !== '' && $iconType === 'fontawesome')
                                    <i class="{{ $icon }}"></i>
                                @elseif ($icon !== '')
                                    <img src="{{ static_asset($icon) }}" alt="">
                                @else
                                    <i class="fa-solid fa-location-dot"></i>
                                @endif
                            </div>
                            <div class="contact-channel__body">
                                @if ($label !== '')
                                    <h3 class="contact-channel__label">{{ $label }}</h3>
                                @endif
                                @if ($displayValue !== '')
                                    <p class="contact-channel__value" @if (in_array($kind, ['email', 'phone', 'whatsapp'], true)) dir="ltr" @endif>{{ $displayValue }}</p>
                                @endif
                                @if ($href !== '')
                                    <span class="contact-channel__action">
                                        {{ $actionLabels[$kind] ?? $actionLabels['custom'] }}
                                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                                    </span>
                                @endif
                            </div>
                    @if ($href !== '')
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
