<?php

use App\Models\Fellowship;
use App\Services\FellowshipFormService;
use App\Support\FormFieldLibrary;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('حقول النموذج | لوحة التحكم')]
class extends Component
{
    public Fellowship $fellowship;

    /** @var list<array<string, mixed>> */
    public array $fields = [];

    /** @var list<string> */
    public array $allowedTypes = ['PDF', 'DOC', 'DOCX', 'JPG', 'PNG'];

    public int $maxSizeMb = 10;

    public int $maxFilesPerField = 1;

    public ?int $expandedIndex = null;

    public function mount(Fellowship $fellowship, FellowshipFormService $forms): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        $this->fellowship = $fellowship;
        $this->loadFromModel($forms);
    }

    protected function loadFromModel(FellowshipFormService $forms): void
    {
        $this->fields = $forms->resolveFields($this->fellowship);
        $settings = $forms->resolveFileUploadSettings($this->fellowship);

        $this->allowedTypes = $forms->parseAllowedTypes($settings['allowed_types'] ?? $this->allowedTypes);
        $this->maxSizeMb = (int) ($settings['max_size_mb'] ?? $this->maxSizeMb);
        $this->maxFilesPerField = (int) ($settings['max_files_per_field'] ?? $this->maxFilesPerField);
    }

    public function addFromLibrary(string $preset, FellowshipFormService $forms): void
    {
        $this->fields = $forms->addFromPreset($this->fields, $preset);
        $this->expandedIndex = count($this->fields) - 1;
    }

    public function addCustomField(): void
    {
        $this->fields[] = [
            'key' => 'custom_field_'.(count($this->fields) + 1),
            'label' => 'حقل مخصص',
            'type' => 'text',
            'required' => false,
        ];

        $this->expandedIndex = count($this->fields) - 1;
    }

    public function toggleExpand(int $index): void
    {
        $this->expandedIndex = $this->expandedIndex === $index ? null : $index;
    }

    public function removeField(int $index): void
    {
        if (! isset($this->fields[$index])) {
            return;
        }

        array_splice($this->fields, $index, 1);

        if ($this->expandedIndex === $index) {
            $this->expandedIndex = null;
        } elseif ($this->expandedIndex !== null && $this->expandedIndex > $index) {
            $this->expandedIndex--;
        }
    }

    /** @param  list<string>  $orderedKeys */
    public function reorderFields(array $orderedKeys, FellowshipFormService $forms): void
    {
        $map = collect($this->fields)->keyBy('key');
        $reordered = [];

        foreach ($orderedKeys as $key) {
            if ($map->has($key)) {
                $reordered[] = $map->get($key);
            }
        }

        foreach ($this->fields as $field) {
            if (! in_array($field['key'], $orderedKeys, true)) {
                $reordered[] = $field;
            }
        }

        $this->fields = $forms->normalizeFields($reordered);
    }

    public function save(FellowshipFormService $forms): void
    {
        $this->validate([
            'allowedTypes' => ['required', 'array', 'min:1'],
            'allowedTypes.*' => ['string', Rule::in(array_keys($forms->allowedFileTypeOptions()))],
            'maxSizeMb' => ['required', 'integer', 'min:1', 'max:100'],
            'maxFilesPerField' => ['required', 'integer', 'min:1', 'max:10'],
        ], [], [
            'allowedTypes' => 'أنواع الملفات المسموحة',
        ]);

        $this->fields = $forms->normalizeFields($this->fields);

        $forms->saveFields($this->fellowship, $this->fields, [
            'allowed_types' => $forms->formatAllowedTypes($this->allowedTypes),
            'max_size_mb' => $this->maxSizeMb,
            'max_files_per_field' => $this->maxFilesPerField,
        ]);

        $this->fellowship->refresh();
        session()->flash('admin_message', 'تم حفظ حقول النموذج.');
    }

    public function fieldOptionsText(array $field): string
    {
        $options = $field['options'] ?? [];

        if (! is_array($options) || $options === []) {
            return '';
        }

        $lines = [];

        foreach ($options as $value => $label) {
            $lines[] = $value.'|'.$label;
        }

        return implode("\n", $lines);
    }

    public function updateFieldOptions(int $index, string $raw): void
    {
        if (! isset($this->fields[$index])) {
            return;
        }

        $options = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_contains($line, '|')) {
                [$value, $label] = array_map('trim', explode('|', $line, 2));
                $options[$value] = $label;
            } else {
                $options[Str::snake($line)] = $line;
            }
        }

        $this->fields[$index]['options'] = $options !== [] ? $options : null;
    }
};
?>

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.fellowships'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.fellowships'), 'label' => 'برامج الزمالة'],
        ['label' => $fellowship->title_ar],
        ['label' => 'حقول النموذج'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <p class="admin-crud-card__meta" style="margin-bottom:.25rem;">برامج الزمالة › {{ $fellowship->title_ar }}</p>
            <h2>حقول نموذج التقديم</h2>
        </div>
        <div class="article-admin-head-actions">
            <a href="{{ route('fellowship.apply', ['locale' => 'ar', 'fellowship' => $fellowship->slug]) }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة النموذج</a>
            <a href="{{ route('admin.fellowships') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
        </div>
    </div>

    <div class="ff-page-layout">
        @include('partials.admin.fellowship-form-nav', ['fellowship' => $fellowship, 'active' => 'form-fields'])

        <div class="ff-page-main">
            <form wire:submit="save">
                <div class="ff-builder">
                    <div class="ff-panel">
                        <h3 class="ff-panel__title">حقول نموذج التقديم</h3>
                        <p class="ff-panel__hint">اسحب لإعادة الترتيب · انقر على الحقل لتوسيعه وتعديله</p>

                        <div id="ff-fields-list" class="ff-field-list" wire:ignore.self>
                            @forelse ($fields as $index => $field)
                                @php
                                    $isExpanded = $expandedIndex === $index;
                                    $isRequired = (bool) ($field['required'] ?? false);
                                @endphp
                                <div
                                    class="ff-field-card @if($isExpanded) is-expanded @endif"
                                    wire:key="ff-field-{{ $field['key'] }}-{{ $index }}"
                                    data-key="{{ $field['key'] }}"
                                >
                                    <div class="ff-field-card__head">
                                        <span class="ff-drag-handle" title="سحب">⠿</span>
                                        <button type="button" class="ff-field-card__summary" wire:click="toggleExpand({{ $index }})">
                                            <span class="ff-field-card__summary-label">{{ $field['label'] }}</span>
                                            <span class="ff-field-card__dot">·</span>
                                            <span class="ff-type-badge ff-type-badge--{{ $field['type'] }}">{{ FormFieldLibrary::typeLabel($field['type']) }}</span>
                                            <span class="ff-field-card__dot">·</span>
                                            <span @class(['sa-status', $isRequired ? 'sa-status--active' : 'sa-status--draft'])>
                                                {{ $isRequired ? 'Required' : 'Optional' }}
                                            </span>
                                        </button>
                                        <div class="ff-field-card__actions">
                                            <button type="button" class="ff-field-card__icon-btn ff-field-card__icon-btn--danger" wire:click="removeField({{ $index }})" wire:confirm="حذف هذا الحقل؟" title="حذف">🗑</button>
                                            <button type="button" class="ff-field-card__icon-btn" wire:click="toggleExpand({{ $index }})" title="{{ $isExpanded ? 'طي' : 'توسيع' }}">
                                                <span @class(['ff-field-card__chevron', 'is-open' => $isExpanded])>⌃</span>
                                            </button>
                                        </div>
                                    </div>

                                    @if ($isExpanded)
                                        <div class="ff-field-card__body">
                                            <div class="admin-filter-grid cms-admin-grid-2">
                                                <div class="admin-field">
                                                    <label>Label <span class="text-danger">*</span></label>
                                                    <input type="text" class="admin-control" wire:model.live="fields.{{ $index }}.label">
                                                </div>
                                                <div class="admin-field">
                                                    <label>Type <span class="text-danger">*</span></label>
                                                    <div class="ff-type-select ff-type-select--{{ $field['type'] }}">
                                                        <span class="ff-type-select__pill">{{ FormFieldLibrary::typeLabel($field['type']) }}</span>
                                                        <select class="ff-type-select__native" wire:model.live="fields.{{ $index }}.type">
                                                            <option value="text">Text</option>
                                                            <option value="email">Email</option>
                                                            <option value="tel">Phone</option>
                                                            <option value="textarea">Text (long)</option>
                                                            <option value="number">Number</option>
                                                            <option value="date">Date</option>
                                                            <option value="select">Dropdown</option>
                                                            <option value="radio">Radio</option>
                                                            <option value="checkbox">Checkbox</option>
                                                            <option value="file">File upload</option>
                                                        </select>
                                                        <span class="ff-type-select__arrow">▾</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="admin-field">
                                                <label class="ff-switch">
                                                    <input type="checkbox" wire:model.live="fields.{{ $index }}.required">
                                                    <span class="ff-switch__track"><span class="ff-switch__thumb"></span></span>
                                                    <span class="ff-switch__label">Required field</span>
                                                </label>
                                            </div>

                                            <div class="admin-filter-grid cms-admin-grid-2">
                                                <div class="admin-field">
                                                    <label>Key</label>
                                                    <input type="text" class="admin-control" wire:model.blur="fields.{{ $index }}.key" dir="ltr">
                                                </div>
                                                <div class="admin-field">
                                                    <label>Placeholder</label>
                                                    <input type="text" class="admin-control" wire:model.blur="fields.{{ $index }}.placeholder">
                                                </div>
                                            </div>

                                            @if (in_array($field['type'], ['select', 'radio'], true))
                                                <div class="admin-field">
                                                    <label>Options (value|label per line)</label>
                                                    <textarea
                                                        class="admin-control"
                                                        rows="3"
                                                        placeholder="option_1|خيار 1"
                                                        wire:change="updateFieldOptions({{ $index }}, $event.target.value)"
                                                    >{{ $this->fieldOptionsText($field) }}</textarea>
                                                </div>
                                            @endif

                                            <div class="admin-field">
                                                <label>Hint</label>
                                                <input type="text" class="admin-control" wire:model.blur="fields.{{ $index }}.hint">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="ff-empty">لا توجد حقول بعد. أضف حقولاً من المكتبة على اليمين.</div>
                            @endforelse
                        </div>

                        <button type="button" class="ff-add-custom" wire:click="addCustomField">+ إضافة حقل مخصص</button>
                    </div>

                    <aside class="ff-sidebar">
                        <div class="ff-panel">
                            <h3 class="ff-panel__title">مكتبة الحقول</h3>
                            @foreach (FormFieldLibrary::categories() as $category)
                                <div class="ff-library-group">
                                    <h4 class="ff-library-group__title">{{ $category['label'] }}</h4>
                                    <div class="ff-library-pills">
                                        @foreach ($category['fields'] as $preset)
                                            <button
                                                type="button"
                                                class="ff-library-pill"
                                                wire:click="addFromLibrary('{{ $preset['preset'] }}')"
                                            >
                                                <span>+</span> {{ $preset['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="ff-panel">
                            <h3 class="ff-panel__title">إعدادات رفع الملفات</h3>
                            <div class="admin-field">
                                <label>أنواع الملفات المسموحة</label>
                                @include('partials.admin.allowed-file-types-multiselect', [
                                    'selected' => $allowedTypes,
                                ])
                                @error('allowedTypes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                            </div>
                            <div class="admin-field">
                                <label>الحد الأقصى لحجم الملف</label>
                                <input type="number" class="admin-control" wire:model="maxSizeMb" min="1" max="100">
                                <p class="admin-field-hint">ميجابايت لكل ملف</p>
                            </div>
                            <div class="admin-field">
                                <label>الحد الأقصى للملفات لكل حقل</label>
                                <input type="number" class="admin-control" wire:model="maxFilesPerField" min="1" max="10">
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="ff-form-actions">
                    <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-fields-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/article-admin.css') }}">
@endpush

@script
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    function initFormFieldsSortable() {
        const list = document.getElementById('ff-fields-list');
        if (!list || list.dataset.sortableInit) {
            return;
        }

        list.dataset.sortableInit = '1';

        Sortable.create(list, {
            handle: '.ff-drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            draggable: '.ff-field-card',
            onEnd() {
                const keys = [...list.querySelectorAll('.ff-field-card')].map(el => el.dataset.key);
                $wire.reorderFields(keys);
            },
        });
    }

    initFormFieldsSortable();
    Livewire.hook('morph.updated', () => {
        const list = document.getElementById('ff-fields-list');
        if (list) {
            delete list.dataset.sortableInit;
        }
        initFormFieldsSortable();
    });
</script>
@endscript

@include('partials.admin.shell-end')
