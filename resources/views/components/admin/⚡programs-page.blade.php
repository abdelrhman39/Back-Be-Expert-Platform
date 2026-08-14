<?php

use App\Models\AcademicProgram;
use App\Support\AcademicProgramOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('البرامج الدراسية | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterStatus = '';

    public string $filterDuration = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDuration(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterStatus', 'filterDuration']);
        $this->resetPage();
    }

    public function deleteProgram(int $programId): void
    {
        abort_unless(auth()->user()?->canAdmin('programs.manage'), 403);
        $program = AcademicProgram::query()->findOrFail($programId);

        if ($program->batches()->exists()) {
            $this->addError('delete', 'لا يمكن حذف برنامج مرتبط بدفعات دراسية.');

            return;
        }

        $program->delete();
        session()->flash('admin_message', 'تم حذف البرنامج بنجاح.');
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('symbol', 'like', '%'.$this->search.'%');
            }))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDuration, fn ($q) => $q->where('duration_months', (int) $this->filterDuration))
            ->latest()
            ->paginate(12);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.programs'),
    'shellActiveHeader' => 'settings',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'البرامج الدراسية'],
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
        <h2>بحث متقدم <span class="admin-crud-card__meta">— نتائج البحث: {{ $this->programs->total() }} برنامج</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="ابحث باسم البرنامج أو الرمز">
        </div>
        <div class="admin-field">
            <label>نوع البرنامج</label>
            <select class="admin-control" wire:model.live="filterType">
                <option value="">الكل</option>
                @foreach (\App\Support\AcademicProgramOptions::types() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="filterStatus">
                <option value="">الكل</option>
                @foreach (\App\Support\AcademicProgramOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>مدة البرنامج</label>
            <select class="admin-control" wire:model.live="filterDuration">
                <option value="">الكل</option>
                @foreach (\App\Support\AcademicProgramOptions::durationMonthsOptions() as $months)
                    <option value="{{ $months }}">{{ $months }} شهر</option>
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
        <h2>عرض كافة البرامج الدراسية</h2>
        <a href="{{ route('admin.programs.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ برنامج جديد</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم البرنامج</th>
                    <th>اسم البرنامج في الشهادة</th>
                    <th>رمز البرنامج</th>
                    <th>مدة البرنامج</th>
                    <th>تاريخ البدء</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->programs as $program)
                    <tr>
                        <td>{{ $program->id }}</td>
                        <td>
                            <a href="{{ route('admin.programs.show', $program) }}" class="dash-inline-link">{{ $program->name_ar }}</a>
                        </td>
                        <td>{{ $program->name_on_certificate ?: $program->name_en ?: '—' }}</td>
                        <td>{{ $program->code }}</td>
                        <td>{{ $program->displayDuration() }}</td>
                        <td>{{ $program->start_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            <span @class([
                                'admin-status-badge',
                                'admin-status-badge--active' => $program->status === 'active',
                                'admin-status-badge--inactive' => $program->status === 'inactive',
                                'admin-status-badge--draft' => $program->status === 'draft',
                            ])>{{ \App\Support\AcademicProgramOptions::statusLabel($program->status) }}</span>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.programs.show', $program) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                <a href="{{ route('admin.programs.edit', $program) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm"
                                    wire:click="deleteProgram({{ $program->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذا البرنامج؟">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:2rem">لا توجد برامج. <a href="{{ route('admin.programs.create') }}">أنشئ برنامجاً جديداً</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->programs->hasPages())
        {{ $this->programs->links() }}
    @endif
</section>

@push('styles')
<style>
    .admin-row-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; }
    .admin-alert--error { display: block; background: var(--color-danger-bg); color: var(--color-danger-text); border: 1px solid var(--color-danger-border); padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; }
</style>
@endpush

@include('partials.admin.shell-end')
