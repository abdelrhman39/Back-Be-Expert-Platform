@php
    $colClass = 'col-md-'.($field['col'] ?? 6);
    $inputId = 'apply-'.$field['key'];
@endphp

<div class="{{ $colClass }}">
    <div class="form-group apply-form-field mb-3">
        <label class="form-label" for="{{ $inputId }}">
            {{ $field['label'] }}
            @if ($field['required'] ?? false)
                <span class="text-danger">*</span>
            @endif
        </label>

        @if ($field['type'] === 'textarea')
            <textarea
                id="{{ $inputId }}"
                class="form-control @error('formData.'.$field['key']) is-invalid @enderror"
                rows="{{ $field['rows'] ?? 3 }}"
                placeholder="{{ $field['placeholder'] ?? '' }}"
                wire:model="formData.{{ $field['key'] }}"
            ></textarea>
        @elseif ($field['type'] === 'select')
            <select
                id="{{ $inputId }}"
                class="form-select @error('formData.'.$field['key']) is-invalid @enderror"
                wire:model="formData.{{ $field['key'] }}"
            >
                <option value="">اختر</option>
                @foreach (($field['options'] ?? []) as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        @elseif ($field['type'] === 'radio')
            <div class="d-flex gap-3 flex-wrap">
                @foreach (($field['options'] ?? []) as $value => $label)
                    <label class="form-check-label d-flex align-items-center gap-2">
                        <input
                            type="radio"
                            class="form-check-input"
                            wire:model="formData.{{ $field['key'] }}"
                            value="{{ $value }}"
                        >
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        @elseif ($field['type'] === 'checkbox')
            <label class="form-check-label d-flex align-items-center gap-2">
                <input type="checkbox" class="form-check-input" wire:model="formData.{{ $field['key'] }}">
                <span>{{ $field['checkbox_label'] ?? 'نعم' }}</span>
            </label>
        @elseif ($field['type'] === 'file')
            <input
                id="{{ $inputId }}"
                type="file"
                class="form-control @error('uploads.'.$field['key']) is-invalid @enderror"
                wire:model="uploads.{{ $field['key'] }}"
            >
            <div wire:loading wire:target="uploads.{{ $field['key'] }}" class="apply-form-field__hint">جاري رفع الملف…</div>
        @else
            <input
                id="{{ $inputId }}"
                type="{{ $field['type'] }}"
                class="form-control @error('formData.'.$field['key']) is-invalid @enderror"
                @if (in_array($field['type'], ['email', 'tel'], true)) dir="ltr" @endif
                placeholder="{{ $field['placeholder'] ?? '' }}"
                @isset($field['step']) step="{{ $field['step'] }}" @endisset
                @isset($field['min']) min="{{ $field['min'] }}" @endisset
                @isset($field['max']) max="{{ $field['max'] }}" @endisset
                wire:model="formData.{{ $field['key'] }}"
            >
        @endif

        @if (! empty($field['hint']))
            <p class="apply-form-field__hint">{{ $field['hint'] }}</p>
        @endif

        @error('formData.'.$field['key'])
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error('uploads.'.$field['key'])
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
