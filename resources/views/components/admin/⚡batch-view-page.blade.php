<?php

use App\Models\AcademicBatch;
use App\Support\AcademicBatchOptions;
use App\Support\AcademicStudentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('عرض الدفعة | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public AcademicBatch $batch;

    #[Url(as: 'tab')]
    public string $activeTab = 'details';

    public string $studentSearch = '';

    public int $studentsPerPage = 15;

    public function mount(AcademicBatch $batch): void
    {
        $batch->refreshStudentsCount();
        $this->batch = $batch->fresh(['program', 'sections.program', 'sections.course']);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['details', 'students', 'sections'], true)) {
            $this->activeTab = $tab;
            if ($tab === 'students') {
                $this->resetPage('studentsPage');
            }
        }
    }

    public function updatedStudentSearch(): void
    {
        $this->resetPage('studentsPage');
    }

    public function updatedStudentsPerPage(): void
    {
        $this->resetPage('studentsPage');
    }

    #[Computed]
    public function paginatedStudents()
    {
        return $this->batch->students()
            ->when($this->studentSearch, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->studentSearch.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->studentSearch.'%')
                    ->orWhere('national_id', 'like', '%'.$this->studentSearch.'%')
                    ->orWhere('mobile', 'like', '%'.$this->studentSearch.'%');
            }))
            ->latest('joined_at')
            ->paginate($this->studentsPerPage, pageName: 'studentsPage');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.batches'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.batches'), 'label' => 'الدفعات الدراسية'],
        ['label' => $batch->name],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-program-stats">
    <div class="admin-program-stats__grid">
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $batch->students_count }}</span>
            <span class="admin-program-stat__label">مسجلون</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $batch->capacity ?: '—' }}</span>
            <span class="admin-program-stat__label">السعة</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $batch->availableSeats() ?? '—' }}</span>
            <span class="admin-program-stat__label">مقاعد متاحة</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $batch->sections->count() }}</span>
            <span class="admin-program-stat__label">شعب</span>
        </div>
    </div>
    <div class="admin-program-stats__actions">
        <a href="{{ route('admin.batches.edit', $batch) }}" class="admin-btn-primary admin-btn-primary--sm">تعديل الدفعة</a>
        <a href="{{ route('admin.students.create', ['batch' => $batch->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">+ إضافة طالب</a>
        @if ($batch->program)
            <a href="{{ route('admin.programs.show', ['program' => $batch->program, 'tab' => 'batches']) }}" class="admin-btn-secondary admin-btn-secondary--sm">البرنامج</a>
        @endif
        <a href="{{ route('admin.batches') }}" class="admin-btn-secondary admin-btn-secondary--sm">القائمة</a>
    </div>
</section>

<section class="admin-crud-card admin-view-card">
    <header class="admin-batch-view-hero">
        @include('partials.admin.batch-view-header', ['batch' => $batch])
    </header>

    <div class="admin-view-tabs" role="tablist" aria-label="أقسام الدفعة">
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'details']) wire:click="setTab('details')">التفاصيل</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'students']) wire:click="setTab('students')">الطلاب ({{ $batch->students_count }})</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'sections']) wire:click="setTab('sections')">الشعب ({{ $batch->sections->count() }})</button>
    </div>

    @if ($activeTab === 'details')
        <div class="admin-view-panel is-active">
            @include('partials.admin.batch-detail-sections', ['batch' => $batch])
        </div>
    @elseif ($activeTab === 'students')
        <div class="admin-view-panel is-active">
            <div class="admin-batch-students-summary">
                <p>يعرض الجدول <strong>{{ $this->paginatedStudents->total() }}</strong> طالباً مسجلاً فعلياً في هذه الدفعة.
                    @if ($batch->capacity)
                        السعة القصوى <strong>{{ $batch->capacity }}</strong> — متبقٍ <strong>{{ $batch->availableSeats() }}</strong> مقعد.
                    @endif
                </p>
            </div>
            <div class="admin-table-toolbar">
                <div class="admin-field" style="min-width:220px;margin:0;">
                    <input type="search" class="admin-control" wire:model.live.debounce.300ms="studentSearch" placeholder="بحث بالاسم، الرقم الأكاديمي، الهوية...">
                </div>
                <label class="admin-table-toolbar__label">
                    عدد الصفوف
                    <select class="admin-control admin-control--inline" wire:model.live="studentsPerPage">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </label>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الرقم الأكاديمي</th>
                            <th>الاسم</th>
                            <th>رقم الهوية</th>
                            <th>الجوال</th>
                            <th>البريد</th>
                            <th>المدينة</th>
                            <th>الحالة</th>
                            <th><span class="visually-hidden">إجراءات</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->paginatedStudents as $index => $student)
                            <tr wire:key="batch-student-{{ $student->id }}">
                                <td>{{ $this->paginatedStudents->firstItem() + $index }}</td>
                                <td><code class="admin-code">{{ $student->academic_id ?? '—' }}</code></td>
                                <td>
                                    <a href="{{ route('admin.students.show', $student) }}" class="dash-inline-link">{{ $student->name_ar }}</a>
                                </td>
                                <td dir="ltr">{{ $student->national_id ?? '—' }}</td>
                                <td dir="ltr">{{ $student->mobile ?? '—' }}</td>
                                <td dir="ltr">{{ $student->email ?? '—' }}</td>
                                <td>{{ $student->city ?? '—' }}</td>
                                <td>
                                    <span @class([
                                        'admin-badge',
                                        'admin-badge--success' => $student->academic_status === 'studying',
                                        'admin-badge--warn' => $student->academic_status === 'pending',
                                        'admin-badge--danger' => in_array($student->academic_status, ['withdrawn', 'deferred'], true),
                                    ])>{{ $student->study_status ?: AcademicStudentOptions::academicStatusLabel($student->academic_status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.students.show', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" style="text-align:center;padding:1.5rem">لا يوجد طلاب مسجلون في هذه الدفعة. <a href="{{ route('admin.students.create', ['batch' => $batch->id]) }}">أضف طالباً</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $this->paginatedStudents->links() }}
            <div class="admin-filter-actions" style="margin-top:1rem;">
                <a href="{{ route('admin.students', ['batch' => $batch->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة كل الطلاب</a>
                <a href="{{ route('admin.students.create', ['batch' => $batch->id]) }}" class="admin-btn-primary admin-btn-primary--sm">+ طالب جديد</a>
            </div>
        </div>
    @else
        <div class="admin-view-panel is-active">
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>الرمز</th>
                            <th>اسم الشعبة</th>
                            <th>المقرر</th>
                            <th>الطلاب</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batch->sections as $section)
                            <tr>
                                <td><a href="{{ route('admin.sections.show', $section) }}" class="dash-inline-link">{{ $section->code }}</a></td>
                                <td>{{ $section->name }}</td>
                                <td>{{ $section->course?->name_ar ?? $section->subtitle ?? '—' }}</td>
                                <td>{{ $section->students_count }}</td>
                                <td>{{ \App\Support\AcademicSectionOptions::statusLabel($section->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;padding:1.5rem">لا توجد شعب. <a href="{{ route('admin.sections.create', ['batch' => $batch->id]) }}">أضف شعبة</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-filter-actions" style="margin-top:1rem;">
                <a href="{{ route('admin.sections.create', ['batch' => $batch->id]) }}" class="admin-btn-primary admin-btn-primary--sm">+ شعبة جديدة</a>
            </div>
        </div>
    @endif
</section>

@include('partials.admin.view-hero-styles')

@push('styles')
<style>
    .admin-program-stats { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .admin-program-stats__grid { display:flex; flex-wrap:wrap; gap:0.75rem; }
    .admin-program-stat { min-width:5.5rem; padding:0.65rem 1rem; border-radius:var(--radius-md); background:var(--sa-mist); border:1px solid var(--sa-border); text-align:center; }
    .admin-program-stat__value { display:block; font-size:1rem; font-weight:800; color:var(--sa-green-dark); }
    .admin-program-stat__label { font-size:0.75rem; color:var(--sa-muted); }
    .admin-program-stats__actions { display:flex; flex-wrap:wrap; gap:0.5rem; }
    .admin-table-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.75rem; margin-bottom:0.75rem; }
    .admin-table-toolbar__label { display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; color:var(--sa-muted); }
    .admin-batch-students-summary { margin-bottom:0.75rem; padding:0.75rem 1rem; background:var(--sa-green-soft); border-radius:var(--radius-md); font-size:0.88rem; color:var(--sa-ink); }
    .admin-badge--warn { background:#fff7ed; color:#c2410c; }
</style>
@endpush

@include('partials.admin.shell-end')
