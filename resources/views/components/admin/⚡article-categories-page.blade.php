<?php

use App\Models\ArticleCategory;
use App\Services\ArticleCategoryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تصنيفات المقالات | لوحة التحكم')]
class extends Component
{
    public ?int $editingId = null;

    public string $nameAr = '';

    public string $nameEn = '';

    public string $slug = '';

    public string $color = '#1b8354';

    public int $sortOrder = 0;

    public bool $isActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.manage'), 403);
    }

    #[Computed]
    public function categories()
    {
        return app(ArticleCategoryService::class)->all();
    }

    public function startEdit(int $id): void
    {
        $category = ArticleCategory::query()->findOrFail($id);

        $this->editingId = $category->id;
        $this->nameAr = $category->name_ar;
        $this->nameEn = $category->name_en ?? '';
        $this->slug = $category->slug;
        $this->color = $category->color;
        $this->sortOrder = $category->sort_order;
        $this->isActive = $category->is_active;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(ArticleCategoryService $categories): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.manage'), 403);
        $validated = $this->validate([
            'nameAr' => ['required', 'string', 'max:120'],
            'nameEn' => ['nullable', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:64'],
            'color' => ['required', 'string', 'max:16'],
            'sortOrder' => ['integer', 'min:0'],
            'isActive' => ['boolean'],
        ], [], [
            'nameAr' => 'الاسم (عربي)',
        ]);

        $category = $this->editingId
            ? ArticleCategory::query()->findOrFail($this->editingId)
            : null;

        $categories->save([
            'name_ar' => $validated['nameAr'],
            'name_en' => $validated['nameEn'] ?: null,
            'slug' => $validated['slug'] ?: $validated['nameAr'],
            'color' => $validated['color'],
            'sort_order' => $validated['sortOrder'],
            'is_active' => $this->isActive,
        ], $category);

        session()->flash('admin_message', $this->editingId ? 'تم تحديث التصنيف.' : 'تم إنشاء التصنيف.');
        $this->resetForm();
        unset($this->categories);
    }

    public function deleteCategory(int $id, ArticleCategoryService $categories): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.manage'), 403);
        $category = ArticleCategory::query()->findOrFail($id);
        $categories->delete($category);

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        session()->flash('admin_message', 'تم حذف التصنيف.');
        unset($this->categories);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nameAr = '';
        $this->nameEn = '';
        $this->slug = '';
        $this->color = '#1b8354';
        $this->sortOrder = 0;
        $this->isActive = true;
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.articles'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.articles'), 'label' => 'الأخبار والفعاليات'],
        ['label' => 'تصنيفات المقالات'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card article-admin-form">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $editingId ? 'تعديل تصنيف' : 'تصنيف جديد' }}</h2>
        <a href="{{ route('admin.articles') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة للمقالات</a>
    </div>

    <form wire:submit="save" class="admin-filter-grid cms-admin-grid-3" style="margin-bottom:1.25rem;">
        <div class="admin-field">
            <label>الاسم (عربي) *</label>
            <input type="text" class="admin-control" wire:model="nameAr">
            @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>الاسم (English)</label>
            <input type="text" class="admin-control" wire:model="nameEn" dir="ltr">
        </div>
        <div class="admin-field">
            <label>الرابط (slug)</label>
            <input type="text" class="admin-control" wire:model="slug" dir="ltr" placeholder="يُولَّد من الاسم">
        </div>
        <div class="admin-field">
            <label>اللون</label>
            <input type="color" class="admin-control" wire:model="color" style="height:2.5rem;padding:0.25rem;">
        </div>
        <div class="admin-field">
            <label>الترتيب</label>
            <input type="number" class="admin-control" wire:model="sortOrder" min="0">
        </div>
        <div class="admin-field">
            <label class="admin-check" style="margin-top:1.75rem;">
                <input type="checkbox" wire:model="isActive">
                <span>نشط</span>
            </label>
        </div>
        <div class="admin-field admin-field--wide" style="display:flex;gap:.5rem;align-items:flex-end;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">{{ $editingId ? 'تحديث' : 'إضافة' }}</button>
            @if ($editingId)
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelEdit">إلغاء</button>
            @endif
        </div>
    </form>
</section>

<section class="admin-crud-card article-cat-table">
    <div class="admin-crud-card__head">
        <h2>كل التصنيفات <span class="admin-crud-card__meta">— {{ $this->categories->count() }}</span></h2>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>اللون</th>
                    <th>الاسم</th>
                    <th>Slug</th>
                    <th>المقالات</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->categories as $category)
                    <tr wire:key="cat-{{ $category->id }}">
                        <td><span class="article-cat-color" style="background:{{ $category->color }}"></span></td>
                        <td>
                            <strong>{{ $category->name_ar }}</strong>
                            @if ($category->name_en)
                                <div class="admin-crud-card__meta" dir="ltr">{{ $category->name_en }}</div>
                            @endif
                        </td>
                        <td><code class="admin-code" dir="ltr">{{ $category->slug }}</code></td>
                        <td>{{ $category->articles()->count() }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td>{{ $category->is_active ? 'نشط' : 'معطّل' }}</td>
                        <td>
                            <div class="admin-row-actions">
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="startEdit({{ $category->id }})">تعديل</button>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteCategory({{ $category->id }})" wire:confirm="حذف هذا التصنيف؟">حذف</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:2rem">لا توجد تصنيفات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/article-admin.css') }}">
    <style>.admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;}</style>
@endpush

@include('partials.admin.shell-end')
