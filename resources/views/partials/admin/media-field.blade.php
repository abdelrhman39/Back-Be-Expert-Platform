{{--
  Reusable media library field for Livewire admin forms.

  @param string $wireModel   Livewire property path (e.g. posterImage or blocks.0.data.logos.0.image)
  @param string $label
  @param string|null $hint
  @param string|null $previewUrl  Resolved preview URL
  @param string $accept  image|file|any
  @param string|null $id
  @param string|null $placeholder
  @param bool $showPathInput  Show the raw path text input (default true)
  @param string|null $clearAction  Optional wire:click method to clear (e.g. removeCoverImage)
--}}
@php
    $wireModel = $wireModel ?? 'image';
    $label = $label ?? 'الملف';
    $hint = $hint ?? null;
    $previewUrl = $previewUrl ?? null;
    $accept = $accept ?? 'image';
    $id = $id ?? ('media-field-'.Str::slug($wireModel, '-'));
    $placeholder = $placeholder ?? '/storage/... أو assets/...';
    $showPathInput = $showPathInput ?? true;
    $clearAction = $clearAction ?? null;
    $pickerTitle = $accept === 'image' ? 'اختيار صورة — '.$label : 'اختيار ملف — '.$label;
@endphp

<div
    class="media-field"
    wire:key="media-field-{{ $id }}"
    x-data
    x-on:media-picker-selected.window="
        if ($event.detail.target === @js($wireModel)) {
            $wire.set(@js($wireModel), $event.detail.url);
        }
    "
>
    <div class="media-field__head">
        <label class="media-field__label" for="{{ $id }}">{{ $label }}</label>
        @if ($hint)
            <p class="media-field__hint">{{ $hint }}</p>
        @endif
    </div>

    <div class="media-field__body">
        <div class="media-field__preview">
            @if ($previewUrl)
                <img src="{{ $previewUrl }}" alt="" loading="lazy">
            @else
                <span class="media-field__placeholder" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5-5-7 7"/></svg>
                </span>
            @endif
        </div>

        <div class="media-field__controls">
            <div class="media-field__actions">
                <button
                    type="button"
                    class="admin-btn-primary admin-btn-primary--sm media-field__btn"
                    onclick="Livewire.dispatch('open-media-picker', { target: @js($wireModel), accept: @js($accept), title: @js($pickerTitle) })"
                >
                    <i class="fa-regular fa-images" aria-hidden="true"></i>
                    اختيار من المكتبة
                </button>
                <button
                    type="button"
                    class="admin-btn-secondary admin-btn-secondary--sm media-field__btn"
                    onclick="Livewire.dispatch('open-media-picker', { target: @js($wireModel), accept: @js($accept), title: @js($pickerTitle) })"
                >
                    <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                    رفع جديد
                </button>
                @if ($previewUrl)
                    @if ($clearAction)
                        <button type="button" class="media-field__clear" wire:click="{{ $clearAction }}">إزالة</button>
                    @else
                        <button type="button" class="media-field__clear" x-on:click="$wire.set(@js($wireModel), '')">إزالة</button>
                    @endif
                @endif
            </div>

            @if ($showPathInput)
                <input
                    id="{{ $id }}"
                    type="text"
                    class="admin-control media-field__path"
                    wire:model.live.debounce.400ms="{{ $wireModel }}"
                    dir="ltr"
                    placeholder="{{ $placeholder }}"
                >
                <p class="media-field__path-hint">يمكن أيضاً لصق مسار أو رابط يدوياً</p>
            @endif
        </div>
    </div>
</div>
