<?php

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Support\AcademicSectionOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الشعب الدراسية | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $program = '';

    #[Url]
    public string $batch = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function updatedBatch(): void
    {
        $this->resetPage();
    }

    public function deleteSection(int $sectionId): void
    {
        abort_unless(auth()->user()?->canAdmin('sections.manage'), 403);
        AcademicSection::query()->findOrFail($sectionId)->delete();
        session()->flash('admin_message', 'تم حذف الشعبة.');
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar']);
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()->orderBy('name')->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function sections()
    {
        return AcademicSection::query()
            ->with(['program', 'batch', 'course'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            }))
            ->when($this->program, fn ($q) => $q->where('program_id', (int) $this->program))
            ->when($this->batch, fn ($q) => $q->where('batch_id', (int) $this->batch))
            ->latest()
            ->paginate(12);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.sections'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الشعب الدراسية'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>بحث متقدم <span class="admin-crud-card__meta">— {{ $this->sections->total() }} شعبة</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="اسم الشعبة أو الرمز">
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
            <label>الدفعة</label>
            <select class="admin-control" wire:model.live="batch">
                <option value="">الكل</option>
                @foreach ($this->batches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>عرض كافة الشعب الدراسية</h2>
        <a href="{{ route('admin.sections.create', $batch ? ['batch' => $batch] : []) }}" class="admin-btn-primary admin-btn-primary--sm">+ شعبة جديدة</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الشعبة</th>
                    <th>الرمز</th>
                    <th>البرنامج</th>
                    <th>الدفعة</th>
                    <th>المقرر</th>
                    <th>الطلاب</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->sections as $section)
                    <tr>
                        <td>{{ $section->id }}</td>
                        <td><a href="{{ route('admin.sections.show', $section) }}" class="dash-inline-link">{{ $section->name }}</a></td>
                        <td><code class="admin-code">{{ $section->code }}</code></td>
                        <td>{{ $section->program?->name_ar ?? '—' }}</td>
                        <td>{{ $section->batch?->code ?? '—' }}</td>
                        <td>{{ $section->course?->name_ar ?? $section->subtitle ?? '—' }}</td>
                        <td>{{ $section->students_count }}</td>
                        <td>
                            <span @class(['admin-badge', 'admin-badge--success' => $section->status === 'active', 'admin-badge--danger' => $section->status !== 'active'])>
                                {{ AcademicSectionOptions::statusLabel($section->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.sections.show', $section) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                <a href="{{ route('admin.sections.edit', $section) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteSection({{ $section->id }})" wire:confirm="حذف الشعبة؟">حذف</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;padding:2rem">لا توجد شعب.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->sections->hasPages())
        {{ $this->sections->links() }}
    @endif
</section>

@push('styles')
<style>.admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;}</style>
@endpush

@include('partials.admin.shell-end')
