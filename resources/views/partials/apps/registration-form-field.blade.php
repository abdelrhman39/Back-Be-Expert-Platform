@php
    $colClass = 'col-md-'.($field['col'] ?? 6);
    $inputId = 'apply-'.$field['key'];
    $locale = app()->getLocale();
    $choose = \App\Support\PublicCopy::apply('choose', $locale);
    $isTel = ($field['type'] ?? '') === 'tel';
    $isRadio = ($field['type'] ?? '') === 'radio';
@endphp

<div class="{{ $colClass }}">
    <div class="form-group apply-form-field mb-3">
        @if (! $isRadio)
            <label class="form-label" for="{{ $inputId }}">
                {{ $field['label'] }}
                @if ($field['required'] ?? false)
                    <span class="text-danger">*</span>
                @endif
            </label>
        @else
            <span class="form-label d-block">
                {{ $field['label'] }}
                @if ($field['required'] ?? false)
                    <span class="text-danger">*</span>
                @endif
            </span>
        @endif

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
                <option value="">{{ $choose }}</option>
                @foreach (($field['options'] ?? []) as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        @elseif ($isRadio)
            <div class="apply-choice-grid">
                @foreach (($field['options'] ?? []) as $value => $label)
                    <label class="apply-choice">
                        <input type="radio" wire:model="formData.{{ $field['key'] }}" value="{{ $value }}">
                        <span>{{ $label }}</span>
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
                @isset($field['accept']) accept="{{ $field['accept'] }}" @endisset
                wire:model="uploads.{{ $field['key'] }}"
            >
            <div wire:loading wire:target="uploads.{{ $field['key'] }}" class="apply-form-field__hint">{{ \App\Support\PublicCopy::apply('uploading', $locale) }}</div>
        @elseif ($isTel)
            <div class="apply-tel input-group">
                <span class="input-group-text apply-tel__prefix" dir="ltr">🇸🇦 +966</span>
                <input
                    id="{{ $inputId }}"
                    type="tel"
                    class="form-control @error('formData.'.$field['key']) is-invalid @enderror"
                    dir="ltr"
                    placeholder="{{ $field['placeholder'] ?? '5xxxxxxxx' }}"
                    wire:model="formData.{{ $field['key'] }}"
                >
            </div>
        @else
            <input
                id="{{ $inputId }}"
                type="{{ $field['type'] }}"
                class="form-control @error('formData.'.$field['key']) is-invalid @enderror"
                @if (($field['type'] ?? '') === 'email') dir="ltr" @endif
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
