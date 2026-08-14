<?php

use App\Models\Fellowship;
use App\Services\FellowshipFormService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('إعدادات النموذج | لوحة التحكم')]
class extends Component
{
    public Fellowship $fellowship;

    /** @var list<string> */
    public array $allowedTypes = [];

    public int $maxSizeMb = 10;

    public int $maxFilesPerField = 1;

    public function mount(Fellowship $fellowship, FellowshipFormService $forms): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        $this->fellowship = $fellowship;
        $settings = $forms->resolveFileUploadSettings($fellowship);

        $this->allowedTypes = $forms->parseAllowedTypes($settings['allowed_types']);
        $this->maxSizeMb = (int) $settings['max_size_mb'];
        $this->maxFilesPerField = (int) $settings['max_files_per_field'];
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

        $forms->saveFields(
            $this->fellowship,
            $forms->resolveFields($this->fellowship),
            [
                'allowed_types' => $forms->formatAllowedTypes($this->allowedTypes),
                'max_size_mb' => $this->maxSizeMb,
                'max_files_per_field' => $this->maxFilesPerField,
            ],
        );

        session()->flash('admin_message', 'تم حفظ الإعدادات.');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.fellowships'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.fellowships'), 'label' => 'برامج الزمالة'],
        ['label' => $fellowship->title_ar],
        ['label' => 'الإعدادات'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <p class="admin-crud-card__meta" style="margin-bottom:.25rem;">برامج الزمالة › {{ $fellowship->title_ar }}</p>
            <h2>إعدادات النموذج</h2>
        </div>
        <a href="{{ route('admin.fellowships') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>

    <div class="ff-page-layout">
        @include('partials.admin.fellowship-form-nav', ['fellowship' => $fellowship, 'active' => 'settings'])

        <div class="ff-page-main">
            <form wire:submit="save" class="ff-panel" style="max-width:32rem;">
                <h3 class="ff-panel__title">إعدادات رفع الملفات</h3>
                <p class="ff-panel__hint">تُطبَّق على جميع حقول المرفقات في نموذج التقديم.</p>

                <div class="admin-field">
                    <label>أنواع الملفات المسموحة</label>
                    @include('partials.admin.allowed-file-types-multiselect', [
                        'selected' => $allowedTypes,
                    ])
                    @error('allowedTypes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label>الحد الأقصى لحجم الملف (ميجابايت)</label>
                    <input type="number" class="admin-control" wire:model="maxSizeMb" min="1" max="100">
                </div>
                <div class="admin-field">
                    <label>الحد الأقصى للملفات لكل حقل</label>
                    <input type="number" class="admin-control" wire:model="maxFilesPerField" min="1" max="10">
                </div>

                <div class="ff-form-actions" style="border-top:none;padding-top:0;margin-top:1rem;">
                    <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ الإعدادات</button>
                </div>
            </form>

            <div class="ff-panel" style="max-width:32rem;margin-top:1rem;">
                <h3 class="ff-panel__title">الإشعارات</h3>
                <p class="ff-panel__hint">إشعارات البريد عند استلام طلب جديد — قريباً.</p>
            </div>
        </div>
    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-fields-admin.css') }}">
@endpush

@include('partials.admin.shell-end')
