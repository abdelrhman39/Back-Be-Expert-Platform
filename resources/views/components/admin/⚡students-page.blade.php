<?php

use App\Models\AcademicBatch;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Support\AcademicProgramOptions;
use App\Support\AcademicStudentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الطلاب | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $batch = '';

    #[Url]
    public string $status = '';

    /** Program track: '' | diploma | certificate */
    #[Url(as: 'track')]
    public string $track = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBatch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedTrack(): void
    {
        $this->resetPage();
    }

    public function setTrack(string $track): void
    {
        $allowed = array_merge([''], array_keys(AcademicProgramOptions::studentTracks()));

        if (! in_array($track, $allowed, true)) {
            return;
        }

        $this->track = $track;
        $this->resetPage();
    }

    public function deleteStudent(int $studentId): void
    {
        abort_unless(auth()->user()?->canAdmin('students.manage'), 403);

        $student = AcademicStudent::query()->findOrFail($studentId);
        $sectionId = $student->section_id;
        $batchId = $student->batch_id;
        $student->delete();

        if ($sectionId) {
            AcademicSection::query()->find($sectionId)?->refreshStudentsCount();
        }
        if ($batchId) {
            AcademicBatch::query()->find($batchId)?->refreshStudentsCount();
        }

        session()->flash('admin_message', 'تم حذف الطالب.');
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()
            ->with('program:id,name_ar,type,code')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'program_id']);
    }

    #[Computed]
    public function trackCounts(): array
    {
        $base = AcademicStudent::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->search.'%')
                    ->orWhere('national_id', 'like', '%'.$this->search.'%');
            }))
            ->when($this->batch, fn ($q) => $q->where('batch_id', (int) $this->batch))
            ->when($this->status, fn ($q) => $q->where('academic_status', $this->status));

        $counts = ['all' => (clone $base)->count()];

        foreach (array_keys(AcademicProgramOptions::studentTracks()) as $type) {
            $counts[$type] = (clone $base)
                ->whereHas('batch.program', fn ($q) => $q->where('type', $type))
                ->count();
        }

        return $counts;
    }

    #[Computed]
    public function students()
    {
        return AcademicStudent::query()
            ->with(['batch.program', 'section', 'user'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->search.'%')
                    ->orWhere('national_id', 'like', '%'.$this->search.'%');
            }))
            ->when($this->batch, fn ($q) => $q->where('batch_id', (int) $this->batch))
            ->when($this->status, fn ($q) => $q->where('academic_status', $this->status))
            ->when(
                $this->track !== '' && array_key_exists($this->track, AcademicProgramOptions::studentTracks()),
                fn ($q) => $q->whereHas('batch.program', fn ($q) => $q->where('type', $this->track))
            )
            ->latest('joined_at')
            ->paginate(20);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.students'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الطلاب المشتركين'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>بحث متقدم <span class="admin-crud-card__meta">— {{ $this->students->total() }} طالب</span></h2>
    </div>

    <div class="students-track-filter" role="tablist" aria-label="مسار البرنامج">
        <button
            type="button"
            role="tab"
            @class(['students-track-filter__chip', 'is-active' => $track === ''])
            wire:click="setTrack('')"
            title="جميع الطلاب"
        >
            الكل
            <span class="students-track-filter__count">{{ $this->trackCounts['all'] ?? 0 }}</span>
        </button>
        @foreach (AcademicProgramOptions::studentTracks() as $value => $meta)
            <button
                type="button"
                role="tab"
                @class(['students-track-filter__chip', 'is-active' => $track === $value, 'students-track-filter__chip--'.$value])
                wire:click="setTrack('{{ $value }}')"
                title="{{ $meta['hint'] }}"
            >
                {{ $meta['label'] }}
                <span class="students-track-filter__count">{{ $this->trackCounts[$value] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="الاسم، الرقم الأكاديمي، الهوية">
        </div>
        <div class="admin-field">
            <label>الدفعة</label>
            <select class="admin-control" wire:model.live="batch">
                <option value="">الكل</option>
                @foreach ($this->batches as $b)
                    <option value="{{ $b->id }}">
                        {{ $b->name }}
                        @if ($b->program)
                            — {{ AcademicProgramOptions::typeLabel($b->program->type) }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (AcademicStudentOptions::academicStatuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>الطلاب المشتركين</h2>
        @canAdmin('students.manage')
            <a href="{{ route('admin.students.create', $batch ? ['batch' => $batch] : []) }}" class="admin-btn-primary admin-btn-primary--sm">+ طالب جديد</a>
        @endcanAdmin
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الرقم الأكاديمي</th>
                    <th>رقم الهوية</th>
                    <th>المسار</th>
                    <th>الدفعة</th>
                    <th>الشعبة</th>
                    <th>الجوال</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->students as $student)
                    @php
                        $program = $student->batch?->program;
                        $programType = $program?->type;
                    @endphp
                    <tr wire:key="student-row-{{ $student->id }}">
                        <td>{{ $student->id }}</td>
                        <td><a href="{{ route('admin.students.show', $student) }}" class="dash-inline-link">{{ $student->name_ar }}</a></td>
                        <td><code class="admin-code">{{ $student->academic_id ?? '—' }}</code></td>
                        <td dir="ltr">{{ $student->national_id ?? '—' }}</td>
                        <td>
                            @if ($programType)
                                <span @class([
                                    'students-track-badge',
                                    'students-track-badge--diploma' => $programType === 'diploma',
                                    'students-track-badge--certificate' => $programType === 'certificate',
                                ])>
                                    {{ AcademicProgramOptions::typeLabel($programType) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if ($student->batch)
                                <a href="{{ route('admin.batches.show', $student->batch) }}" class="dash-inline-link">{{ $student->batch->code }}</a>
                            @else — @endif
                        </td>
                        <td>
                            @if ($student->section)
                                <a href="{{ route('admin.sections.show', $student->section) }}" class="dash-inline-link">{{ $student->section->code }}</a>
                            @else — @endif
                        </td>
                        <td dir="ltr">{{ $student->mobile ?? '—' }}</td>
                        <td>{{ $student->study_status ?? AcademicStudentOptions::academicStatusLabel($student->academic_status) }}</td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.students.show', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                @canImpersonateStudent
                                    @if ($student->canBeImpersonated())
                                        <form method="post" action="{{ route('admin.students.impersonate', $student) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="admin-btn-primary admin-btn-primary--sm" title="فتح بوابة الطالب دون تسجيل خروج من لوحة التحكم">دخول كطالب</button>
                                        </form>
                                    @else
                                        <button
                                            type="button"
                                            class="admin-btn-secondary admin-btn-secondary--sm is-disabled"
                                            disabled
                                            title="{{ $student->impersonationBlockReason() }}"
                                        >دخول كطالب</button>
                                    @endif
                                @endcanImpersonateStudent
                                @canAdmin('students.manage')
                                    <a href="{{ route('admin.students.edit', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteStudent({{ $student->id }})" wire:confirm="حذف هذا الطالب؟">حذف</button>
                                @endcanAdmin
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align:center;padding:2rem">لا يوجد طلاب.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->students->hasPages())
        {{ $this->students->links() }}
    @endif
</section>

@push('styles')
<style>
.admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
.admin-row-actions .is-disabled{opacity:.45;cursor:not-allowed}
.students-track-filter{display:flex;flex-wrap:wrap;gap:.5rem;margin:0 0 1rem}
.students-track-filter__chip{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .85rem;border:1px solid #cbd5e1;border-radius:999px;background:#fff;color:#0f172a;font-size:.875rem;font-weight:600;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}
.students-track-filter__chip:hover{border-color:#94a3b8}
.students-track-filter__chip.is-active{border-color:#0f766e;background:#ecfdf5;color:#115e59;box-shadow:0 0 0 3px rgba(15,118,110,.12)}
.students-track-filter__chip--certificate.is-active{border-color:#0369a1;background:#e0f2fe;color:#075985;box-shadow:0 0 0 3px rgba(3,105,161,.12)}
.students-track-filter__count{display:inline-flex;min-width:1.4rem;justify-content:center;padding:0 .35rem;border-radius:999px;background:rgba(15,23,42,.08);font-size:.75rem;font-weight:700}
.students-track-filter__chip.is-active .students-track-filter__count{background:rgba(15,118,110,.15)}
.students-track-filter__chip--certificate.is-active .students-track-filter__count{background:rgba(3,105,161,.15)}
.students-track-badge{display:inline-flex;padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:700;background:#f1f5f9;color:#334155}
.students-track-badge--diploma{background:#ecfdf5;color:#115e59}
.students-track-badge--certificate{background:#e0f2fe;color:#075985}
</style>
@endpush

@include('partials.admin.shell-end')
