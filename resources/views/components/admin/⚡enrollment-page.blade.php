<?php

use App\Models\AcademicBatch;
use App\Models\AcademicStudent;
use App\Services\AdminStatsService;
use App\Support\AcademicStudentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'التسجيل والالتحاق',
    'adminPageDesc' => 'متابعة المسجلين والمدفوعات وحالات الدراسة',
    'adminLayout' => 'dashboard',
])]
#[Title('التسجيل والالتحاق | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public array $stats = [];

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $batch = '';

    #[Url]
    public string $status = '';

    public function mount(AdminStatsService $stats): void
    {
        $this->stats = $stats->enrollment();
    }

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

    public function setStatusFilter(string $status): void
    {
        $this->status = $this->status === $status ? '' : $status;
        $this->resetPage();
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()->orderBy('name')->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function enrolledStudents()
    {
        return AcademicStudent::query()
            ->with(['batch.program', 'section'])
            ->whereNotIn('academic_status', ['graduated', 'eligible', 'expected'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->search.'%')
                    ->orWhere('national_id', 'like', '%'.$this->search.'%')
                    ->orWhere('mobile', 'like', '%'.$this->search.'%');
            }))
            ->when($this->batch, fn ($q) => $q->where('batch_id', (int) $this->batch))
            ->when($this->status, fn ($q) => $q->where('academic_status', $this->status))
            ->latest('joined_at')
            ->paginate(15);
    }
};
?>

@include('partials.admin.dashboard-start', ['dashSubnav' => 'enrollment', 'dashSidebarActive' => route('admin.enrollment')])

<div class="dash-grid dash-enroll-top">
    <div class="dash-hero-card">
        <span>إجمالي المسجلين</span>
        <strong>{{ number_format($stats['total_enrolled']) }}</strong>
        <div class="dash-hero-card__breakdown">
            <span>مؤكد: {{ number_format($stats['confirmed']) }}</span>
            <span>غير مؤكد: {{ number_format($stats['unconfirmed']) }}</span>
        </div>
    </div>
    <div class="dash-enroll-side-stats">
        <div class="dash-enroll-stat-lg"><span>المدفوعات الفعلية</span><strong class="currency">{{ number_format($stats['revenue_paid'], 0) }} ر.س</strong></div>
        <div class="dash-enroll-stat-lg"><span>المتدربون المفعلون</span><strong>{{ number_format($stats['active_trainees']) }}</strong></div>
        <div class="dash-enroll-stat-lg"><span>البرامج المفعلة</span><strong>{{ number_format($stats['active_programs']) }}</strong></div>
    </div>
</div>

<div class="dash-status-row dash-status-row--filter" style="margin-top:1rem;">
    @foreach ([
        'studying' => ['label' => 'طلاب يدرسون', 'count' => $stats['studying'], 'tone' => 'green'],
        'pending' => ['label' => 'بانتظار إكمال التسجيل', 'count' => $stats['pending_registration'], 'tone' => 'orange'],
        'withdrawn' => ['label' => 'طلاب منسحبون', 'count' => $stats['withdrawn'], 'tone' => 'red'],
        'deferred' => ['label' => 'طلاب مؤجلون', 'count' => $stats['deferred'], 'tone' => 'blue'],
        'suspended' => ['label' => 'طلاب متوقفون', 'count' => $stats['suspended'], 'tone' => 'gray'],
    ] as $statusKey => $pill)
        <button
            type="button"
            wire:click="setStatusFilter('{{ $statusKey }}')"
            @class([
                'dash-status-pill',
                'dash-status-pill--'.$pill['tone'],
                'is-active' => $status === $statusKey,
            ])
        >
            <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg></div>
            <strong>{{ $pill['count'] }}</strong><span>{{ $pill['label'] }}</span>
        </button>
    @endforeach
</div>

<section class="admin-crud-card admin-enrollment-list" style="margin-top:1rem;">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>
            قائمة المسجلين
            <span class="admin-crud-card__meta">— {{ $this->enrolledStudents->total() }} طالب</span>
        </h2>
        @canAdmin('students.manage')
            <a href="{{ route('admin.students.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ تسجيل طالب</a>
        @endcanAdmin
    </div>

    <div class="admin-filter-grid" style="margin-bottom:0.75rem;">
        <div class="admin-field admin-field--wide">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="الاسم، الرقم الأكاديمي، الهوية، الجوال...">
        </div>
        <div class="admin-field">
            <label>الدفعة</label>
            <select class="admin-control" wire:model.live="batch">
                <option value="">الكل</option>
                @foreach ($this->batches as $b)
                    <option value="{{ $b->id }}">{{ $b->code }} — {{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (AcademicStudentOptions::enrollmentStatuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>الرقم الأكاديمي</th>
                    <th>الدفعة / البرنامج</th>
                    <th>الشعبة</th>
                    <th>تاريخ الانضمام</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->enrolledStudents as $index => $student)
                    <tr wire:key="enroll-row-{{ $student->id }}">
                        <td>{{ $this->enrolledStudents->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('admin.students.show', $student) }}" class="dash-inline-link">{{ $student->name_ar }}</a>
                            @if ($student->mobile)
                                <span class="admin-table-sub" dir="ltr">{{ $student->mobile }}</span>
                            @endif
                        </td>
                        <td><code class="admin-code">{{ $student->academic_id ?? '—' }}</code></td>
                        <td>
                            @if ($student->batch)
                                <a href="{{ route('admin.batches.show', $student->batch) }}" class="dash-inline-link">{{ $student->batch->code }}</a>
                                @if ($student->batch->program)
                                    <span class="admin-table-sub">{{ $student->batch->program->name_ar }}</span>
                                @endif
                            @else — @endif
                        </td>
                        <td>
                            @if ($student->section)
                                <a href="{{ route('admin.sections.show', $student->section) }}" class="dash-inline-link">{{ $student->section->code }}</a>
                            @else — @endif
                        </td>
                        <td>
                            {{ $student->joined_at?->format('Y-m-d') ?? '—' }}
                            @if ($student->joined_at)
                                <span class="admin-table-sub">{{ $student->joined_at->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td>@include('partials.admin.student-status-badge', ['status' => $student->academic_status])</td>
                        <td>
                            <a href="{{ route('admin.students.show', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-table-empty-state">لا يوجد مسجلون مطابقون للبحث.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->enrolledStudents->hasPages())
        {{ $this->enrolledStudents->links() }}
    @endif

    <div class="admin-filter-actions" style="margin-top:1rem;">
        <a href="{{ route('admin.students') }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة كل الطلاب</a>
        <a href="{{ route('admin.batches') }}" class="admin-btn-secondary admin-btn-secondary--sm">الدفعات</a>
    </div>
</section>

@push('styles')
<style>
    .dash-status-row--filter {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr));
        gap: 0.75rem;
    }
    .dash-status-row--filter .dash-status-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        width: 100%;
        min-height: 6.5rem;
        border: 2px solid transparent;
        cursor: pointer;
        font: inherit;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .dash-status-row--filter .dash-status-pill .icon {
        margin: 0 auto 0.5rem;
        flex-shrink: 0;
    }
    .dash-status-row--filter .dash-status-pill strong {
        display: block;
        width: 100%;
        text-align: center;
        font-size: 1.15rem;
        line-height: 1.2;
    }
    .dash-status-row--filter .dash-status-pill span {
        display: block;
        width: 100%;
        text-align: center;
        margin-top: 0.25rem;
        line-height: 1.35;
    }
    .dash-status-row--filter .dash-status-pill.is-active {
        border-color: var(--sa-green);
        box-shadow: 0 0 0 2px var(--sa-green-soft);
    }
    .admin-enrollment-list { padding: 1rem 1.15rem; }
    .admin-table-sub { display: block; font-size: 0.72rem; color: var(--sa-muted); margin-top: 0.1rem; }
    .admin-badge--warn { background: #fff7ed; color: #c2410c; }
</style>
@endpush

@include('partials.admin.dashboard-end')
