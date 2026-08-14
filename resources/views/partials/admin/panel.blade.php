@if ($savedMessage ?? false)
    <div class="admin-alert admin-alert--info is-visible" role="status">{{ $savedMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>{{ $title }}</h2>
            @if (! empty($description))
                <p class="admin-crud-card__meta">{{ $description }}</p>
            @endif
        </div>
        @if (! empty($action))
            {!! $action !!}
        @endif
    </div>

    {{ $slot }}
</section>
