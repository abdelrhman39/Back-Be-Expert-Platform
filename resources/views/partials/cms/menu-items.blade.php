@foreach ($items as $item)
    @if (! empty($item['children']) && count($item['children']) > 0)
        <li class="has-submenu">
            <a href="#"
                @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif
                role="button"
                aria-expanded="false">
                {{ $item['label'] }}
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </a>
            <ul class="submenu">
                @include('partials.cms.menu-items', ['items' => $item['children']])
            </ul>
        </li>
    @else
        <li>
            <a href="{{ $item['url'] ?? 'javascript:void(0)' }}"
                class="nav-link"
                @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
                {{ $item['label'] }}
            </a>
        </li>
    @endif
@endforeach
