@props([
    'id',
    'label',
    'colorKey',
    'default',
])

<div class="admin-field">
    <label for="{{ $id }}">{{ $label }}</label>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        @if (strtolower($default) !== 'transparent')
            <input
                id="{{ $id }}Picker"
                type="color"
                class="admin-control"
                wire:model.live="themeColors.{{ $colorKey }}"
                style="width:52px; min-width:52px; height:38px; padding:2px; flex:0 0 52px;"
            >
        @endif
        <input
            id="{{ $id }}"
            type="text"
            class="admin-control"
            wire:model="themeColors.{{ $colorKey }}"
            dir="ltr"
            placeholder="{{ $default }}"
            style="flex:1;"
        >
    </div>
    <div class="admin-field-hint">الافتراضي: <code dir="ltr">{{ $default }}</code> — اكتب <code dir="ltr">transparent</code> للشفافية أو اتركه فارغاً للافتراضي</div>
    @error('themeColors.'.$colorKey)<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
</div>
