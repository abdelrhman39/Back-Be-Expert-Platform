@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $active = $active ?? '';
    $items = [
        ['key' => 'new', 'route' => 'support.ticket.new', 'label' => $isEn ? 'Create ticket' : 'إنشاء تذكرة', 'icon' => 'fa-solid fa-ticket'],
        ['key' => 'search', 'route' => 'support.ticket.search', 'label' => $isEn ? 'Find ticket' : 'البحث عن تذكرة', 'icon' => 'fa-solid fa-magnifying-glass'],
        ['key' => 'faq', 'route' => 'support.faq', 'label' => $isEn ? 'FAQ' : 'الأسئلة الشائعة', 'icon' => 'fa-solid fa-circle-question'],
        ['key' => 'contact', 'route' => 'support.contact', 'label' => $isEn ? 'Contact channels' : 'قنوات التواصل', 'icon' => 'fa-solid fa-headset'],
    ];
@endphp

<div class="support-subnav-wrap">
    <nav class="support-subnav" aria-label="{{ $isEn ? 'Support' : 'قسم الدعم' }}">
        @foreach ($items as $item)
            <a href="{{ route($item['route'], ['locale' => $locale]) }}"
                @class(['support-subnav__link', 'is-active' => $active === $item['key']])>
                <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
