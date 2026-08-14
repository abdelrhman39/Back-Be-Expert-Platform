@props([
    'id',
    'label',
    'hint' => null,
    'pathModel',
    'fileModel',
    'previewUrl',
    'settingKey',
    'visibleModel' => null,
])

<div class="admin-filter-grid" style="grid-template-columns: 1fr 180px; align-items: start; margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--sa-border);">
    <div>
        @if ($visibleModel)
            <div class="admin-field" style="margin-bottom: 0.75rem;">
                <label class="admin-check">
                    <input type="checkbox" wire:model="{{ $visibleModel }}">
                    <span>إظهار {{ $label }}</span>
                </label>
            </div>
        @endif
        <div class="admin-field">
            @include('partials.admin.media-field', [
                'wireModel' => $pathModel,
                'id' => $id,
                'label' => $label,
                'hint' => trim(($hint ? $hint.' · ' : '').\App\Support\LogoSettings::slotHint($settingKey)),
                'previewUrl' => $this->{$fileModel}
                    ? $this->{$fileModel}->temporaryUrl()
                    : $previewUrl,
                'placeholder' => \App\Support\LogoSettings::defaultPath($settingKey),
            ])
            @error($pathModel)<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror

            <div class="admin-field" style="margin-top: 0.75rem;">
                <label for="{{ $id }}File">أو رفع مباشر (بدون المكتبة)</label>
                <input id="{{ $id }}File" type="file" class="admin-control" wire:model="{{ $fileModel }}" accept="image/*">
                @error($fileModel)<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                <div wire:loading wire:target="{{ $fileModel }}" class="admin-field-hint">جاري تحميل الصورة...</div>
            </div>
        </div>
    </div>
    <div class="admin-field">
        <label>معاينة</label>
        <div style="border:1px solid var(--sa-border); border-radius:12px; padding:1rem; background:#fff; min-height:120px; display:flex; align-items:center; justify-content:center;">
            @if ($this->{$fileModel})
                <img src="{{ $this->{$fileModel}->temporaryUrl() }}" alt="" style="max-width:100%; max-height:100px; object-fit:contain;">
            @else
                <img src="{{ $previewUrl }}" alt="" style="max-width:100%; max-height:100px; object-fit:contain;">
            @endif
        </div>
    </div>
</div>
