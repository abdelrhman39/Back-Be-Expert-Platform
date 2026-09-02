@foreach ($items as $item)
    @php
        $hasChildren = ! empty($item['children']) && count($item['children']) > 0;
        $href = $item['url'] ?? null;
        if (! filled($href)) {
            $href = $hasChildren ? '#' : 'javascript:void(0)';
        }
        $isActive = \App\Support\PublicNav::isActive($href, $item['children'] ?? []);
        $isExact = \App\Support\PublicNav::isExact($href);
    @endphp
    @if ($hasChildren)
        <li @class(['has-submenu', 'is-current' => $isActive])>
            <a href="{{ $href }}"
                class="site-nav__link"
                @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif
                @if ($href === '#') role="button" @endif
                aria-expanded="false"
                aria-haspopup="true">
                <span>{{ $item['label'] }}</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </a>
            <ul class="submenu site-nav__submenu">
                @include('partials.cms.menu-items', ['items' => $item['children']])
            </ul>
        </li>
    @else
        <li @class(['is-current' => $isActive])>
            <a href="{{ $href }}"
                class="nav-link site-nav__link"
                @if ($isExact) aria-current="page" @endif
                @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                <span>{{ $item['label'] }}</span>
            </a>
        </li>
    @endif
@endforeach
