@php
    $options = $options ?? app(\App\Services\FellowshipFormService::class)->allowedFileTypeOptions();
    $model = $model ?? 'allowedTypes';
    $selected = $selected ?? [];
@endphp

<div
    class="ff-multiselect"
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        class="ff-multiselect__trigger"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="listbox"
    >
        @if (count($selected) > 0)
            <span class="ff-multiselect__tags">
                @foreach ($selected as $type)
                    <span class="ff-multiselect__tag" wire:key="ff-type-tag-{{ $type }}">{{ $type }}</span>
                @endforeach
            </span>
        @else
            <span class="ff-multiselect__placeholder">اختر أنواع الملفات…</span>
        @endif
        <span class="ff-multiselect__chevron" :class="{ 'is-open': open }">▾</span>
    </button>

    <div class="ff-multiselect__menu" x-show="open" x-cloak x-transition>
        @foreach ($options as $value => $label)
            <label class="ff-multiselect__option" wire:key="ff-type-opt-{{ $value }}">
                <input
                    type="checkbox"
                    class="ff-multiselect__checkbox"
                    wire:model.live="{{ $model }}"
                    value="{{ $value }}"
                >
                <span class="ff-multiselect__option-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

@once
    @push('styles')
        <style>[x-cloak]{display:none!important}</style>
    @endpush
@endonce
