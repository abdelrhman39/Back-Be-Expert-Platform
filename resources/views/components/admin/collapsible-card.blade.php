@props([
    'title',
])

<details {{ $attributes->class(['admin-crud-card', 'admin-crud-card--collapsible']) }}>
    <summary class="admin-crud-card__head admin-crud-card__head--toggle">
        <div class="admin-crud-card__head-text">
            <h2>{{ $title }}</h2>
            @isset($meta)
                <div class="admin-crud-card__meta">{{ $meta }}</div>
            @endisset
        </div>
        <span class="admin-crud-card__toggle-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </span>
    </summary>
    <div class="admin-crud-card__body">
        {{ $slot }}
    </div>
</details>
