@php
    use App\Support\CmsBlockLink;

    $data = $block['data'] ?? [];
    $items = array_values(array_filter($data['items'] ?? [], fn ($item) => $item['enabled'] ?? true));
@endphp

@if ($items !== [])
    <div class="contact-bottom">
        <div class="container">
            <div class="row g-4">
                @foreach ($items as $item)
                    @php
                        $kind = $item['kind'] ?? 'custom';
                        $label = $item['label'] ?? '';
                        $value = trim((string) ($item['value'] ?? ''));
                        $icon = $item['icon'] ?? '';
                        $iconType = $item['icon_type'] ?? 'image';
                        $digits = preg_replace('/\D+/', '', $value) ?? '';
                        $phoneDisplay = CmsBlockLink::phoneDisplay($value);
                    @endphp
                    <div class="col-xl-3 col-md-6 col-sm-6 d-flex">
                        <div class="contact-grid con-info w-100">
                            <div class="contact-content">
                                @if ($icon !== '')
                                    <div class="contact-icon">
                                        <span>
                                            @if ($iconType === 'fontawesome')
                                                <i class="{{ $icon }} fa-lg"></i>
                                            @else
                                                <img src="{{ static_asset($icon) }}" alt="">
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                <div @class(['contact-details', 'contact-details-address' => $kind === 'address'])>
                                    @if ($label !== '')
                                        <h6>{{ $label }}</h6>
                                    @endif

                                    @switch($kind)
                                        @case('email')
                                            @if ($value !== '')
                                                <p><a href="mailto:{{ $value }}" dir="ltr">{{ $value }}</a></p>
                                            @endif
                                            @break

                                        @case('phone')
                                            @if ($value !== '')
                                                <a href="tel:{{ $phoneDisplay }}" dir="ltr">{{ $phoneDisplay }}</a>
                                            @endif
                                            @break

                                        @case('whatsapp')
                                            @if ($value !== '')
                                                <a href="https://wa.me/{{ $digits }}" target="_blank" rel="noopener" dir="ltr">{{ $phoneDisplay ?: $value }}</a>
                                            @endif
                                            @break

                                        @case('address')
                                            @if ($value !== '')
                                                <p>{{ $value }}</p>
                                            @endif
                                            @break

                                        @default
                                            @if ($value !== '')
                                                @if (filled($item['link_url'] ?? null))
                                                    <a href="{{ $item['link_url'] }}" @if(str_starts_with($item['link_url'], 'http')) target="_blank" rel="noopener" @endif>{{ $value }}</a>
                                                @else
                                                    <p>{{ $value }}</p>
                                                @endif
                                            @endif
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
