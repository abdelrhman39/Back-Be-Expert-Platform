@foreach ($items as $item)
    <li>
        <a href="{{ $item['url'] ?? '#' }}"
            @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener" @endif>
            {{ $item['label'] }}
        </a>
    </li>
@endforeach
