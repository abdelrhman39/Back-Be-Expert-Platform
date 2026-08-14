<?php

use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('المستويات الأكاديمية | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $program = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function deleteLevel(int $levelId): void
    {
        abort_unless(auth()->user()?->canAdmin('courses.manage'), 403);
        $level = AcademicLevel::query()->findOrFail($levelId);

        if ($level->courses()->exists()) {
            $this->addError('delete', 'لا يمكن حذف مستوى مرتبط بمقررات.');

            return;
        }

        $level->delete();
        session()->flash('admin_message', 'تم حذف المستوى بنجاح.');
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar']);
    }

    #[Computed]
    public function levels()
    {
        return AcademicLevel::query()
            ->with('program')
            ->withCount('courses')
            ->when($this->search, fn ($q) => $q->where('name_ar', 'like', '%'.$this->search.'%'))
            ->when($this->program, fn ($q) => $q->where('program_id', (int) $this->program))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('sort_order')
            ->paginate(15);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.levels'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'المستويات الأكاديمية'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif
@error('delete')
    <div class="admin-alert admin-alert--error is-visible">{{ $message }}</div>
@enderror

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>بحث متقدم <span class="admin-crud-card__meta">— {{ $this->levels->total() }} مستوى</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>عنوان المستوى</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="ابحث بعنوان المستوى">
        </div>
        <div class="admin-field">
            <label>البرنامج</label>
            <select class="admin-control" wire:model.live="program">
                <option value="">الكل</option>
                @foreach ($this->programs as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                <option value="active">مفعل</option>
                <option value="inactive">غير مفعل</option>
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>عرض كافة المستويات الدراسية</h2>
        <a href="{{ route('admin.levels.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ مستوى جديد</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المستوى</th>
                    <th>البرنامج</th>
                    <th>الترتيب</th>
                    <th>المقررات</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->levels as $level)
                    <tr>
                        <td>{{ $level->id }}</td>
                        <td>{{ $level->name_ar }}</td>
                        <td>
                            @if ($level->program)
                                <a href="{{ route('admin.programs.show', ['program' => $level->program, 'tab' => 'levels']) }}" class="dash-inline-link">{{ $level->program->name_ar }}</a>
                            @else — @endif
                        </td>
                        <td>{{ $level->sort_order }}</td>
                        <td>{{ $level->courses_count }}</td>
                        <td>
                            <span @class(['admin-badge', 'admin-badge--success' => $level->status === 'active', 'admin-badge--danger' => $level->status !== 'active'])>
                                {{ $level->status === 'active' ? 'مفعل' : 'غير مفعل' }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.levels.edit', $level) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteLevel({{ $level->id }})" wire:confirm="حذف هذا المستوى؟">حذف</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:2rem">لا توجد مستويات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->levels->hasPages())
        {{ $this->levels->links() }}
    @endif
</section>

@push('styles')
<style>
    .admin-row-actions { display:flex; flex-wrap:wrap; gap:0.35rem; }
    .admin-alert--error { display:block; background:var(--color-danger-bg); color:var(--color-danger-text); padding:0.75rem 1rem; border-radius:10px; margin-bottom:1rem; }
</style>
@endpush

@include('partials.admin.shell-end')
