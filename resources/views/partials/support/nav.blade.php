@php
    $locale = app()->getLocale();
    $active = $active ?? '';
    $items = [
        ['key' => 'new', 'route' => 'support.ticket.new', 'label' => 'إنشاء تذكرة'],
        ['key' => 'search', 'route' => 'support.ticket.search', 'label' => 'البحث عن تذكرة'],
        ['key' => 'faq', 'route' => 'support.faq', 'label' => 'الأسئلة الشائعة'],
        ['key' => 'contact', 'route' => 'support.contact', 'label' => 'قنوات التواصل'],
    ];
@endphp

<nav class="support-subnav" aria-label="قسم الدعم">
    @foreach ($items as $item)
        <a href="{{ route($item['route'], ['locale' => $locale]) }}"
            @class(['support-subnav__link', 'is-active' => $active === $item['key']])>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
