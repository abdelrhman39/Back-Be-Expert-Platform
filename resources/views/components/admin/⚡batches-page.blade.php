<?php

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Support\AcademicBatchOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الدفعات الدراسية | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $searchName = '';

    #[Url]
    public string $searchCode = '';

    #[Url]
    public string $filterProgram = '';

    #[Url]
    public string $filterSemester = '';

    public function updatedSearchName(): void
    {
        $this->resetPage();
    }

    public function updatedSearchCode(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProgram(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSemester(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['searchName', 'searchCode', 'filterProgram', 'filterSemester']);
        $this->resetPage();
    }

    public function deleteBatch(int $batchId): void
    {
        abort_unless(auth()->user()?->canAdmin('batches.manage'), 403);
        $batch = AcademicBatch::query()->findOrFail($batchId);

        if ($batch->students()->exists()) {
            $this->addError('delete', 'لا يمكن حذف دفعة مرتبطة بطلاب.');

            return;
        }

        $batch->delete();
        session()->flash('admin_message', 'تم حذف الدفعة بنجاح.');
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar', 'code']);
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()
            ->with('program')
            ->when($this->searchName, fn ($q) => $q->where('name', 'like', '%'.$this->searchName.'%'))
            ->when($this->searchCode, fn ($q) => $q->where('code', 'like', '%'.$this->searchCode.'%'))
            ->when($this->filterProgram, fn ($q) => $q->where('program_id', (int) $this->filterProgram))
            ->when($this->filterSemester, fn ($q) => $q->where('semester_key', $this->filterSemester))
            ->latest()
            ->paginate(10);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.batches'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الدفعات الدراسية'],
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
        <h2>بحث متقدم <span class="admin-crud-card__meta">— {{ $this->batches->total() }} دفعة</span></h2>
    </div>
    <div class="admin-filter-grid admin-filter-grid--batches">
        <div class="admin-field">
            <label>اسم الدفعة</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="searchName" placeholder="ابحث باسم الدفعة">
        </div>
        <div class="admin-field">
            <label>كود الدفعة</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="searchCode" placeholder="مثال: 251010" dir="ltr">
        </div>
        <div class="admin-field">
            <label>البرنامج</label>
            <select class="admin-control" wire:model.live="filterProgram">
                <option value="">الكل</option>
                @foreach ($this->programs as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>فصل القبول</label>
            <select class="admin-control" wire:model.live="filterSemester">
                <option value="">الكل</option>
                @foreach (\App\Support\AcademicBatchOptions::semesters() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-filter-actions">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="resetFilters">إعادة تعيين</button>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>عرض كافة الدفعات</h2>
        <a href="{{ route('admin.batches.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ دفعة جديدة</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الدفعة</th>
                    <th>كود الدفعة</th>
                    <th>البرنامج</th>
                    <th>فصل القبول</th>
                    <th>عدد الطلاب</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->batches as $index => $batch)
                    <tr wire:key="batch-{{ $batch->id }}">
                        <td>{{ $this->batches->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('admin.batches.show', $batch) }}" class="dash-inline-link">{{ $batch->name }}</a>
                        </td>
                        <td><code class="admin-code">{{ $batch->code }}</code></td>
                        <td>
                            @if ($batch->program)
                                <div class="admin-program-cell">
                                    <span class="admin-program-cell__name">{{ $batch->program->name_ar }}</span>
                                    <span class="admin-duration-pill">{{ $batch->program->displayDuration() }}</span>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $batch->displaySemester() }}</td>
                        <td><span class="admin-count-pill">{{ $batch->students_count }}</span></td>
                        <td>
                            <span @class([
                                'admin-badge',
                                'admin-badge--success' => $batch->status === 'active',
                                'admin-badge--warn' => $batch->status === 'planned',
                                'admin-badge--danger' => in_array($batch->status, ['closed', 'inactive'], true),
                            ])>{{ AcademicBatchOptions::statusLabel($batch->status) }}</span>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.batches.show', $batch) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                <a href="{{ route('admin.batches.edit', $batch) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                @if ($batch->program)
                                    <a href="{{ route('admin.programs.show', $batch->program) }}" class="admin-btn-secondary admin-btn-secondary--sm">البرنامج</a>
                                @endif
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm"
                                    wire:click="deleteBatch({{ $batch->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذه الدفعة؟">حذف</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:2rem">لا توجد دفعات. <a href="{{ route('admin.batches.create') }}">أنشئ دفعة جديدة</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->batches->hasPages())
        {{ $this->batches->links() }}
    @endif
</section>

@push('styles')
<style>
    .admin-row-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; }
    .admin-program-cell { display: flex; flex-direction: column; gap: 0.2rem; }
    .admin-duration-pill, .admin-count-pill { display: inline-block; font-size: 0.72rem; padding: 0.15rem 0.45rem; border-radius: 999px; background: var(--sa-green-soft); color: var(--sa-green-dark); width: fit-content; }
    .admin-badge--warn { background: #fff7ed; color: #c2410c; }
    .admin-alert--error { display: block; background: var(--color-danger-bg); color: var(--color-danger-text); border: 1px solid var(--color-danger-border); padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; }
</style>
@endpush

@include('partials.admin.shell-end')
