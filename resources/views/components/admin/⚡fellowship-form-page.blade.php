<?php

use App\Models\Fellowship;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تفاصيل البرنامج | لوحة التحكم')]
class extends Component
{
    public Fellowship $fellowship;

    public string $titleAr = '';

    public string $titleEn = '';

    public string $slug = '';

    public string $descriptionAr = '';

    public string $descriptionEn = '';

    public string $status = 'open';

    public bool $applicationOpen = true;

    public int $sortOrder = 0;

    public function mount(Fellowship $fellowship): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        $this->fellowship = $fellowship;
        $this->titleAr = $fellowship->title_ar;
        $this->titleEn = $fellowship->title_en ?? '';
        $this->slug = $fellowship->slug;
        $this->descriptionAr = $fellowship->description_ar ?? '';
        $this->descriptionEn = $fellowship->description_en ?? '';
        $this->status = $fellowship->status;
        $this->applicationOpen = $fellowship->application_open;
        $this->sortOrder = $fellowship->sort_order;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'titleAr' => ['required', 'string', 'max:255'],
            'titleEn' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('fellowships', 'slug')->ignore($this->fellowship->id)],
            'descriptionAr' => ['nullable', 'string', 'max:5000'],
            'descriptionEn' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['open', 'closed'])],
            'applicationOpen' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
        ], [], [
            'titleAr' => 'العنوان (عربي)',
            'slug' => 'الرابط',
        ]);

        $this->fellowship->update([
            'title_ar' => $validated['titleAr'],
            'title_en' => $validated['titleEn'] ?: null,
            'slug' => $validated['slug'],
            'description_ar' => $validated['descriptionAr'] ?: null,
            'description_en' => $validated['descriptionEn'] ?: null,
            'status' => $validated['status'],
            'application_open' => $this->applicationOpen,
            'sort_order' => $this->sortOrder,
        ]);

        $this->fellowship->refresh();
        session()->flash('admin_message', 'تم حفظ تفاصيل البرنامج.');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.fellowships'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.fellowships'), 'label' => 'برامج الزمالة'],
        ['label' => $fellowship->title_ar],
        ['label' => 'تفاصيل النموذج'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <p class="admin-crud-card__meta" style="margin-bottom:.25rem;">برامج الزمالة › {{ $fellowship->title_ar }}</p>
            <h2>تفاصيل نموذج التقديم</h2>
        </div>
        <a href="{{ route('admin.fellowships') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>

    <div class="ff-page-layout">
        @include('partials.admin.fellowship-form-nav', ['fellowship' => $fellowship, 'active' => 'details'])

        <div class="ff-page-main">
            <form wire:submit="save">
                <div class="admin-filter-grid cms-admin-grid-2">
                    <div class="admin-field">
                        <label>عنوان البرنامج (عربي) *</label>
                        <input type="text" class="admin-control" wire:model="titleAr">
                        @error('titleAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label>عنوان البرنامج (English)</label>
                        <input type="text" class="admin-control" wire:model="titleEn" dir="ltr">
                    </div>
                    <div class="admin-field">
                        <label>الرابط (slug) *</label>
                        <input type="text" class="admin-control" wire:model="slug" dir="ltr">
                        @error('slug')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label>ترتيب العرض</label>
                        <input type="number" class="admin-control" wire:model="sortOrder" min="0">
                    </div>
                </div>
                <div class="admin-field">
                    <label>وصف البرنامج (عربي)</label>
                    <textarea class="admin-control" rows="4" wire:model="descriptionAr"></textarea>
                </div>
                <div class="admin-field">
                    <label>وصف البرنامج (English)</label>
                    <textarea class="admin-control" rows="4" wire:model="descriptionEn" dir="ltr"></textarea>
                </div>
                <div class="admin-filter-grid cms-admin-grid-2">
                    <div class="admin-field">
                        <label>حالة البرنامج</label>
                        <select class="admin-control" wire:model="status">
                            <option value="open">مفتوح</option>
                            <option value="closed">مغلق</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label class="admin-check" style="margin-top:1.75rem;">
                            <input type="checkbox" wire:model="applicationOpen">
                            <span>يقبل طلبات التقديم</span>
                        </label>
                    </div>
                </div>
                <div class="ff-form-actions">
                    <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ التفاصيل</button>
                    <a href="{{ route('admin.fellowships.form-fields', $fellowship) }}" class="admin-btn-secondary admin-btn-secondary--sm">الانتقال لحقول النموذج ←</a>
                </div>
            </form>
        </div>
    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-fields-admin.css') }}">
@endpush

@include('partials.admin.shell-end')
