<?php

use App\Support\RuntimeSettings;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminLayout' => 'app',
])]
class extends Component
{
    public string $section = '';

    /** @var array<string, mixed> */
    public array $values = [];

    public string $testEmail = '';

    public ?string $savedMessage = null;

    public ?string $errorMessage = null;

    public function mount(string $section): void
    {
        $this->section = $section;

        abort_unless(isset(RuntimeSettings::sections()[$section]), 404);
        abort_unless(RuntimeSettings::canManageSection(auth()->user(), $section), 403);

        foreach (RuntimeSettings::fieldsForSection($section) as $item) {
            $key = $item['key'];
            $meta = $item['meta'];
            $value = RuntimeSettings::get($key);

            if (($meta['secret'] ?? false) && RuntimeSettings::hasStored($key)) {
                $this->values[$key] = '';
            } else {
                $this->values[$key] = $meta['type'] === 'boolean'
                    ? (filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0')
                    : (string) ($value ?? '');
            }
        }

        $this->testEmail = auth()->user()?->email ?? '';
    }

    public function getSectionMetaProperty(): array
    {
        return RuntimeSettings::sections()[$this->section] ?? [];
    }

    public function getSnapshotProperty(): array
    {
        return RuntimeSettings::snapshotForSection($this->section);
    }

    public function save(): void
    {
        abort_unless(RuntimeSettings::canManageSection(auth()->user(), $this->section), 403);

        $rules = [];
        $attributes = [];

        foreach (RuntimeSettings::fieldsForSection($this->section) as $item) {
            $key = $item['key'];
            $type = $item['meta']['type'] ?? 'string';

            $rules["values.{$key}"] = match ($type) {
                'boolean' => ['nullable', 'in:0,1'],
                'integer' => ['nullable', 'integer'],
                'email' => ['nullable', 'email'],
                'url' => ['nullable', 'url'],
                'password' => ['nullable', 'string', 'max:500'],
                'select' => ['nullable', 'string', Rule::in(array_keys($item['meta']['options'] ?? []))],
                default => ['nullable', 'string', 'max:500'],
            };

            $attributes["values.{$key}"] = $item['meta']['label_ar'] ?? $key;
        }

        $this->validate($rules, [], $attributes);

        $toSave = [];

        foreach ($this->values as $envKey => $value) {
            $field = RuntimeSettings::field($envKey);

            if (($field['secret'] ?? false) && blank($value) && RuntimeSettings::hasStored($envKey)) {
                continue;
            }

            if ($field['type'] === 'boolean') {
                $toSave[$envKey] = $value === '1';
            } else {
                $toSave[$envKey] = $value;
            }
        }

        RuntimeSettings::setMany($toSave, auth()->user(), $this->section);
        RuntimeSettings::applyRuntimeConfig();

        $this->savedMessage = 'تم حفظ الإعدادات. بعض القيم قد تتطلب إعادة تشغيل الخادم.';
        $this->errorMessage = null;
    }

    public function resetToEnv(string $envKey): void
    {
        abort_unless(RuntimeSettings::canManageSection(auth()->user(), $this->section), 403);

        RuntimeSettings::clear($envKey);
        $value = env($envKey);
        $field = RuntimeSettings::field($envKey);

        $this->values[$envKey] = ($field['type'] ?? '') === 'boolean'
            ? (filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0')
            : (string) ($value ?? '');

        RuntimeSettings::applyRuntimeConfig();
        $this->savedMessage = "تمت إعادة {$envKey} لقيمة .env";
    }

    public function sendTestMail(): void
    {
        abort_unless($this->section === 'mail', 403);
        abort_unless(RuntimeSettings::canManageSection(auth()->user(), 'mail'), 403);

        $this->validate([
            'testEmail' => ['required', 'email'],
        ], [], ['testEmail' => 'بريد الاختبار']);

        try {
            RuntimeSettings::testMail($this->testEmail);
            $this->savedMessage = 'تم إرسال رسالة الاختبار إلى '.$this->testEmail;
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = 'فشل الإرسال: '.$e->getMessage();
        }
    }
};
?>

@php
    $meta = $this->sectionMeta;
    $pageTitle = $meta['label'] ?? 'إعدادات النظام';
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.system-settings'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.system-settings'), 'label' => 'إعدادات النظام'],
        ['label' => $pageTitle],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif
@if ($errorMessage)
    <div class="admin-alert admin-alert--danger is-visible">{{ $errorMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>{{ $pageTitle }}</h2>
        <p class="admin-crud-card__meta">{{ $meta['description'] ?? '' }}</p>
    </div>

    <div class="admin-form-grid admin-form-grid--2">
        @foreach ($this->snapshot as $envKey => $item)
            @php
                $fieldMeta = $item['meta'];
                $type = $fieldMeta['type'] ?? 'string';
                $source = $item['source'];
            @endphp
            <div @class(['admin-field', 'admin-field--wide' => in_array($type, ['password'])]) wire:key="field-{{ $envKey }}">
                <label for="env-{{ $envKey }}">
                    {{ $fieldMeta['label_ar'] }}
                    <code dir="ltr" class="sys-env-key">{{ $envKey }}</code>
                </label>

                @if ($type === 'boolean')
                    <select id="env-{{ $envKey }}" class="admin-control" wire:model="values.{{ $envKey }}">
                        <option value="1">نعم / مفعّل</option>
                        <option value="0">لا / معطّل</option>
                    </select>
                @elseif ($type === 'select')
                    <select id="env-{{ $envKey }}" class="admin-control" wire:model="values.{{ $envKey }}">
                        @foreach ($fieldMeta['options'] ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}">{{ $optLabel }}</option>
                        @endforeach
                    </select>
                @elseif ($type === 'password')
                    <input id="env-{{ $envKey }}" type="password" class="admin-control" wire:model="values.{{ $envKey }}" placeholder="{{ $item['has_stored_secret'] ? '●●●● محفوظ — اتركه فارغاً للإبقاء' : 'أدخل القيمة' }}" autocomplete="new-password">
                @elseif ($type === 'integer')
                    <input id="env-{{ $envKey }}" type="number" class="admin-control" wire:model="values.{{ $envKey }}">
                @else
                    <input id="env-{{ $envKey }}" type="{{ $type === 'email' ? 'email' : 'text' }}" class="admin-control" wire:model="values.{{ $envKey }}" @if($type === 'url') dir="ltr" @endif>
                @endif

                <div class="admin-field-hint">
                    @if ($fieldMeta['hint_ar'] ?? null)
                        {{ $fieldMeta['hint_ar'] }} ·
                    @endif
                    المصدر:
                    @if ($source === 'database')
                        <span class="admin-badge admin-badge--success">لوحة التحكم</span>
                    @else
                        <span class="admin-badge admin-badge--muted">.env</span> ({{ RuntimeSettings::envFallbackLabel($envKey) }})
                    @endif
                    @if ($fieldMeta['requires_restart'] ?? false)
                        · <span class="text-warning">يتطلب إعادة تشغيل</span>
                    @endif
                </div>

                @if ($source === 'database')
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm sys-reset-btn" wire:click="resetToEnv('{{ $envKey }}')">استخدام .env</button>
                @endif
                @error("values.{$envKey}")<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endforeach
    </div>

    <div class="admin-filter-actions" style="margin-top: 1rem;">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">حفظ الإعدادات</span>
            <span wire:loading wire:target="save">جاري الحفظ...</span>
        </button>
        <a href="{{ route('admin.system-settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة للمركز</a>
    </div>
</section>

@if ($section === 'mail')
    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>اختبار البريد</h2>
            <p class="admin-crud-card__meta">أرسل رسالة تجريبية للتحقق من إعدادات SMTP قبل تفعيل الإشعارات.</p>
        </div>
        <div class="admin-inline-field" style="max-width: 28rem;">
            <input type="email" class="admin-control" wire:model="testEmail" placeholder="test@example.com" dir="ltr">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="sendTestMail" wire:loading.attr="disabled">إرسال اختبار</button>
        </div>
        @error('testEmail')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
    </section>
@endif

@include('partials.admin.shell-end')

@push('styles')
<style>
    .admin-form-grid--2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    @media (max-width: 767.98px) { .admin-form-grid--2 { grid-template-columns: 1fr; } }
    .admin-field--wide { grid-column: 1 / -1; }
    .sys-env-key { font-size: 0.72rem; background: #f1f5f9; padding: 0.1rem 0.35rem; border-radius: 4px; margin-inline-start: 0.35rem; }
    .sys-reset-btn { margin-top: 0.35rem; }
    .admin-alert--danger { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
</style>
@endpush
