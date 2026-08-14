@php
    $activeSubnav = $activeSubnav ?? 'home';
@endphp

<ul class="admin-subnav__list" data-server-rendered="true">
    @foreach (config('admin.subnav', []) as $item)
        <li>
            <a href="{{ route($item['route']) }}"
                data-subnav-id="{{ $item['id'] }}"
                @class(['is-active' => $activeSubnav === $item['id']])>
                @switch($item['id'])
                    @case('financial')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 6-8"/></svg>
                        @break
                    @case('enrollment')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        @break
                    @case('graduates')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
                        @break
                    @case('staff')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        @break
                    @default
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                @endswitch
                {{ $item['label'] }}
            </a>
        </li>
    @endforeach
</ul>
