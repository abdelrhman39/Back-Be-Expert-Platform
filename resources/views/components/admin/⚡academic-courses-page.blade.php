<?php

use App\Models\AcademicCourse;
use App\Models\AcademicProgram;
use App\Support\AcademicCourseOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('المقررات الدراسية | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $program = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'program']);
        $this->resetPage();
    }

    public function deleteCourse(int $courseId): void
    {
        abort_unless(auth()->user()?->canAdmin('courses.manage'), 403);
        AcademicCourse::query()->findOrFail($courseId)->delete();
        session()->flash('admin_message', 'تم حذف المقرر بنجاح.');
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar', 'code']);
    }

    #[Computed]
    public function courses()
    {
        return AcademicCourse::query()
            ->with(['program', 'level'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('symbol_ar', 'like', '%'.$this->search.'%');
            }))
            ->when($this->program, fn ($q) => $q->where('program_id', (int) $this->program))
            ->latest()
            ->paginate(15);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.academic-courses'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'المقررات الدراسية'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>بحث متقدم <span class="admin-crud-card__meta">— {{ $this->courses->total() }} مقرر</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="اسم المقرر أو الرمز">
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
        <div class="admin-filter-actions">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="resetFilters">إعادة تعيين</button>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>عرض كافة المقررات الدراسية</h2>
        <a href="{{ route('admin.academic-courses.create', $program ? ['program' => $program] : []) }}" class="admin-btn-primary admin-btn-primary--sm">+ مقرر جديد</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المقرر</th>
                    <th>رمز المقرر</th>
                    <th>البرنامج</th>
                    <th>الساعات</th>
                    <th>المستوى</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->courses as $course)
                    <tr wire:key="course-row-{{ $course->id }}">
                        <td>{{ $course->id }}</td>
                        <td>
                            <a href="{{ route('admin.academic-courses.show', $course) }}" class="dash-inline-link">{{ $course->name_ar }}</a>
                        </td>
                        <td>{{ $course->symbol_ar ?: '—' }}</td>
                        <td>
                            @if ($course->program)
                                <a href="{{ route('admin.programs.show', $course->program) }}" class="dash-inline-link">{{ $course->program->name_ar }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $course->credit_hours }}</td>
                        <td>{{ $course->displayLevel() }}</td>
                        <td>
                            <span @class([
                                'admin-badge',
                                'admin-badge--success' => $course->status === 'active',
                                'admin-badge--danger' => $course->status !== 'active',
                            ])>{{ AcademicCourseOptions::statusLabel($course->status) }}</span>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.academic-courses.show', $course) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                <a href="{{ route('admin.academic-courses.edit', $course) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm"
                                    wire:click="deleteCourse({{ $course->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذا المقرر؟">حذف</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:2rem">لا توجد مقررات. <a href="{{ route('admin.academic-courses.create') }}">أنشئ مقرراً جديداً</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->courses->hasPages())
        {{ $this->courses->links() }}
    @endif
</section>

@push('styles')
<style>.admin-row-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; }</style>
@endpush

@include('partials.admin.shell-end')
