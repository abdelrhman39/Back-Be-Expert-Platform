<?php

use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Support\AttendanceOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('ملف الطالب | لوحة التحكم')]
class extends Component
{
    public AcademicStudent $student;

    #[Url(as: 'tab')]
    public string $activeTab = 'details';

    public function mount(AcademicStudent $student): void
    {
        $this->student = $student->load([
            'batch.program.levels',
            'section.course',
            'section.level',
            'section.schedule.staff',
            'user',
        ]);
    }

    #[Computed]
    public function academicSummary(): array
    {
        $courses = $this->programCourses;
        $program = $this->student->batch?->program;

        return [
            'courses_count' => $courses->count(),
            'total_hours' => (int) $courses->sum('credit_hours'),
            'levels_count' => $program?->levels->count() ?? $courses->pluck('level_id')->unique()->filter()->count(),
        ];
    }

    #[Computed]
    public function coursesByLevel()
    {
        return $this->programCourses->groupBy(fn ($course) => $course->level?->name_ar ?? 'بدون مستوى');
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['details', 'academic', 'payments', 'attendance'], true)) {
            $this->activeTab = $tab;
        }
    }

    #[Computed]
    public function programCourses()
    {
        return $this->student->programCourses();
    }

    #[Computed]
    public function orders()
    {
        return $this->student->ordersQuery()->latest()->limit(50)->get();
    }

    #[Computed]
    public function attendanceRecords()
    {
        return AttendanceRecord::query()
            ->where('student_id', $this->student->id)
            ->with(['session.section'])
            ->get()
            ->sortByDesc(fn (AttendanceRecord $record) => $record->session?->session_date)
            ->values();
    }

    #[Computed]
    public function attendanceSummary(): array
    {
        return AttendanceOptions::summarizeRecords($this->attendanceRecords);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.students'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.students'), 'label' => 'الطلاب المشتركين'],
        ['label' => $student->name_ar],
    ],
])

<div class="student-profile">
    @include('partials.admin.student-view-hero', ['student' => $student])

    <div class="student-profile-tabs-wrap">
        <div class="admin-view-tabs admin-view-tabs--scroll student-profile-tabs" role="tablist" aria-label="أقسام ملف الطالب">
            <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'details']) wire:click="setTab('details')" role="tab">بيانات المستخدم</button>
            <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'academic']) wire:click="setTab('academic')" role="tab">المقررات والدبلومات</button>
            <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'payments']) wire:click="setTab('payments')" role="tab">المدفوعات</button>
            @canAdmin('attendance.view')
                <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'attendance']) wire:click="setTab('attendance')" role="tab">حضور الطلاب</button>
            @endcanAdmin
        </div>
    </div>

    <div class="admin-view-panel is-active" role="tabpanel">
        @if ($activeTab === 'details')
            @include('partials.admin.student-tab-details', ['student' => $student])
        @elseif ($activeTab === 'academic')
            @include('partials.admin.student-tab-academic', [
                'student' => $student,
                'courses' => $this->programCourses,
                'coursesByLevel' => $this->coursesByLevel,
                'academicSummary' => $this->academicSummary,
            ])
        @elseif ($activeTab === 'payments')
            @include('partials.admin.student-tab-payments', ['student' => $student, 'orders' => $this->orders])
        @elseif ($activeTab === 'attendance' && auth()->user()?->canAdmin('attendance.view'))
            @include('partials.admin.student-tab-attendance', [
                'student' => $student,
                'attendanceRecords' => $this->attendanceRecords,
                'attendanceSummary' => $this->attendanceSummary,
            ])
        @endif
    </div>
</div>

@push('styles')
<style>
    .admin-content--app:has(.student-profile) {
        padding: 1rem 1.25rem 1.5rem;
        max-width: none;
    }
    .student-profile .admin-view-panel {
        padding: 1rem 0 0;
    }
    .admin-badge--warn { background: #fff7ed; color: #c2410c; }

    /* ——— تبويب حضور الطالب ——— */
    .student-attendance-tab {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .attendance-overview {
        padding: 1.25rem 1.35rem;
        margin-bottom: 0;
    }
    .attendance-overview__inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1.25rem 1.75rem;
    }
    .attendance-ring {
        --attendance-rate: 0;
        flex-shrink: 0;
        width: 7.5rem;
        height: 7.5rem;
        border-radius: 50%;
        background: conic-gradient(
            var(--ring-color, var(--sa-green)) calc(var(--attendance-rate) * 1%),
            #e8ecef calc(var(--attendance-rate) * 1%)
        );
        display: grid;
        place-items: center;
        position: relative;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
    .attendance-ring--good { --ring-color: var(--sa-green, #16a34a); }
    .attendance-ring--warn { --ring-color: #ea580c; }
    .attendance-ring--bad { --ring-color: #dc2626; }
    .attendance-ring__center {
        width: 5.75rem;
        height: 5.75rem;
        border-radius: 50%;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        z-index: 1;
    }
    .attendance-ring__value {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--sa-green-dark, #14532d);
        line-height: 1.1;
    }
    .attendance-ring__label {
        font-size: 0.62rem;
        color: var(--sa-muted, #64748b);
        font-weight: 600;
        margin-top: 0.15rem;
    }
    .attendance-overview__body {
        flex: 1;
        min-width: min(100%, 18rem);
    }
    .attendance-overview__title {
        margin: 0 0 0.25rem;
        font-size: 1rem;
        font-weight: 800;
        color: var(--sa-ink, #1e293b);
    }
    .attendance-overview__desc {
        margin: 0 0 0.85rem;
        font-size: 0.82rem;
        color: var(--sa-muted, #64748b);
    }
    .attendance-kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(7.5rem, 1fr));
        gap: 0.65rem;
        margin: 0;
    }
    .attendance-kpi-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        width: 100%;
        min-height: 5.75rem;
        padding: 0.75rem 0.5rem;
        border: 1px solid var(--sa-border, #e2e8f0);
        border-radius: var(--radius-md, 10px);
        background: #fff;
        cursor: default;
    }
    .attendance-kpi-pill .icon {
        margin: 0 auto 0.45rem;
        flex-shrink: 0;
    }
    .attendance-kpi-pill strong {
        display: block;
        width: 100%;
        text-align: center;
        font-size: 1.1rem;
        line-height: 1.2;
    }
    .attendance-kpi-pill span {
        display: block;
        width: 100%;
        text-align: center;
        margin-top: 0.2rem;
        line-height: 1.35;
        font-size: 0.72rem;
    }
    .attendance-context {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed var(--sa-border, #e2e8f0);
    }
    .attendance-context__chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: var(--sa-mist, #f8fafc);
        border: 1px solid var(--sa-border, #e2e8f0);
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--sa-ink, #334155);
    }
    .attendance-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--sa-muted, #64748b);
    }
    .attendance-empty svg { opacity: 0.45; }
    .attendance-log-card .student-profile-card__body { padding-top: 0.25rem; }
    .attendance-table-wrap { border-radius: var(--radius-md, 10px); overflow: hidden; }
    .attendance-table tbody tr:hover { background: var(--sa-mist, #f8fafc); }
    .attendance-table__row--present { border-inline-start: 3px solid var(--sa-green, #16a34a); }
    .attendance-table__row--late { border-inline-start: 3px solid #ea580c; }
    .attendance-table__row--absent { border-inline-start: 3px solid #dc2626; }
    .attendance-table__row--excused { border-inline-start: 3px solid #2563eb; }
    .attendance-badge { min-width: 4.5rem; text-align: center; }
    .admin-badge--info { background: #eff6ff; color: #1d4ed8; }
    .attendance-source {
        font-size: 0.78rem;
        color: var(--sa-muted, #64748b);
    }
    @media (max-width: 640px) {
        .attendance-overview__inner { flex-direction: column; align-items: stretch; }
        .attendance-ring { margin: 0 auto; }
    }
</style>
@endpush

@include('partials.admin.shell-end')
