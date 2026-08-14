<?php

use App\Models\AcademicProgram;
use App\Support\AcademicCourseOptions;
use App\Support\AcademicProgramOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('عرض البرنامج | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public AcademicProgram $program;

    #[Url(as: 'tab')]
    public string $activeTab = 'details';

    public int $coursesPerPage = 10;

    public function mount(AcademicProgram $program): void
    {
        $this->program = $program->load([
            'batches',
            'levels' => fn ($q) => $q->withCount('courses'),
            'courses.level',
        ]);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['details', 'levels', 'courses', 'batches'], true)) {
            return;
        }

        $this->activeTab = $tab;

        if ($tab === 'courses') {
            $this->resetPage('coursesPage');
        }
    }

    public function updatedCoursesPerPage(): void
    {
        $this->resetPage('coursesPage');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'levels' => $this->program->levels->count(),
            'courses' => $this->program->courses->count(),
            'batches' => $this->program->batches->count(),
            'hours' => (int) $this->program->courses->sum('credit_hours'),
        ];
    }

    #[Computed]
    public function paginatedCourses()
    {
        return $this->program->courses()
            ->with('level')
            ->orderBy('code')
            ->paginate($this->coursesPerPage, pageName: 'coursesPage');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.programs'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.programs'), 'label' => 'البرامج الدراسية'],
        ['label' => $program->name_ar],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-program-stats">
    <div class="admin-program-stats__grid">
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $this->stats['levels'] }}</span>
            <span class="admin-program-stat__label">مستويات</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $this->stats['courses'] }}</span>
            <span class="admin-program-stat__label">مقررات</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $this->stats['batches'] }}</span>
            <span class="admin-program-stat__label">دفعات</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $this->stats['hours'] }}</span>
            <span class="admin-program-stat__label">ساعات معتمدة</span>
        </div>
    </div>
    <div class="admin-program-stats__actions">
        <a href="{{ route('admin.programs.edit', $program) }}" class="admin-btn-primary admin-btn-primary--sm">تعديل البرنامج</a>
        <a href="{{ route('admin.academic-courses.create', ['program' => $program->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">+ مقرر جديد</a>
        <a href="{{ route('admin.programs') }}" class="admin-btn-secondary admin-btn-secondary--sm">القائمة</a>
    </div>
</section>

<section class="admin-crud-card admin-view-card">
    <header class="admin-batch-view-hero">
        @include('partials.admin.program-view-header', ['program' => $program, 'stats' => $this->stats])
    </header>

    <div class="admin-view-tabs" role="tablist" aria-label="أقسام البرنامج">
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'details']) wire:click="setTab('details')" role="tab">التفاصيل</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'levels']) wire:click="setTab('levels')" role="tab">المستويات ({{ $program->levels->count() }})</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'courses']) wire:click="setTab('courses')" role="tab">المقررات ({{ $program->courses->count() }})</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'batches']) wire:click="setTab('batches')" role="tab">الدفعات ({{ $program->batches->count() }})</button>
    </div>

    @if ($activeTab === 'details')
        <div class="admin-view-panel is-active" role="tabpanel">
            @include('partials.admin.program-detail-sections', ['program' => $program])
        </div>
    @elseif ($activeTab === 'levels')
        <div class="admin-view-panel is-active" role="tabpanel">
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المستوى الدراسي</th>
                            <th>المقررات</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($program->levels as $level)
                            <tr>
                                <td>{{ $level->sort_order }}</td>
                                <td>{{ $level->name_ar }}</td>
                                <td>{{ $level->courses_count }}</td>
                                <td>
                                    <span @class([
                                        'admin-badge',
                                        'admin-badge--success' => $level->status === 'active',
                                        'admin-badge--danger' => $level->status !== 'active',
                                    ])>{{ $level->status === 'active' ? 'مفعل' : 'غير مفعل' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;padding:1.5rem">لا توجد مستويات لهذا البرنامج.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-filter-actions" style="margin-top:1rem;">
                <a href="{{ route('admin.levels') }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة المستويات</a>
            </div>
        </div>
    @elseif ($activeTab === 'courses')
        <div class="admin-view-panel is-active" role="tabpanel">
            <div class="admin-table-toolbar">
                <label class="admin-table-toolbar__label">
                    عدد الصفوف
                    <select class="admin-control admin-control--inline" wire:model.live="coursesPerPage">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                </label>
                <a href="{{ route('admin.academic-courses.create', ['program' => $program->id]) }}" class="admin-btn-primary admin-btn-primary--sm">+ إضافة مقرر</a>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المقرر</th>
                            <th>رمز المقرر</th>
                            <th>كود المقرر</th>
                            <th>المستوى الدراسي</th>
                            <th>الساعات</th>
                            <th>الحالة</th>
                            <th><span class="visually-hidden">إجراءات</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->paginatedCourses as $index => $course)
                            <tr wire:key="course-{{ $course->id }}">
                                <td>{{ $this->paginatedCourses->firstItem() + $index }}</td>
                                <td>{{ $course->name_ar }}</td>
                                <td>{{ $course->symbol_ar ?: '—' }}</td>
                                <td><code class="admin-code">{{ $course->code }}</code></td>
                                <td>{{ $course->displayLevel() }}</td>
                                <td>{{ $course->credit_hours }}</td>
                                <td>
                                    <span @class([
                                        'admin-badge',
                                        'admin-badge--success' => $course->status === 'active',
                                        'admin-badge--danger' => $course->status !== 'active',
                                    ])>{{ AcademicCourseOptions::statusLabel($course->status) }}</span>
                                </td>
                                <td class="admin-table-actions">
                                    <a href="{{ route('admin.academic-courses.show', $course) }}" class="admin-view-eye" aria-label="عرض {{ $course->name_ar }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center;padding:1.5rem">لا توجد مقررات. <a href="{{ route('admin.academic-courses.create', ['program' => $program->id]) }}">أضف مقرراً</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($this->paginatedCourses->hasPages())
                {{ $this->paginatedCourses->links() }}
            @endif
            <div class="admin-filter-actions" style="margin-top:1rem;">
                <a href="{{ route('admin.academic-courses', ['program' => $program->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">كل المقررات</a>
            </div>
        </div>
    @else
        <div class="admin-view-panel is-active" role="tabpanel">
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>الرمز</th>
                            <th>اسم الدفعة</th>
                            <th>الفصل</th>
                            <th>الطلاب</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($program->batches as $batch)
                            <tr>
                                <td><a href="{{ route('admin.batches.show', $batch) }}" class="dash-inline-link">{{ $batch->code }}</a></td>
                                <td><a href="{{ route('admin.batches.show', $batch) }}" class="dash-inline-link">{{ $batch->name }}</a></td>
                                <td>{{ $batch->displaySemester() }}</td>
                                <td>{{ $batch->students_count }}</td>
                                <td>{{ \App\Support\AcademicBatchOptions::statusLabel($batch->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;padding:1.5rem">لا توجد دفعات مرتبطة بهذا البرنامج.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-filter-actions" style="margin-top:1rem;">
                <a href="{{ route('admin.batches') }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة الدفعات</a>
            </div>
        </div>
    @endif
</section>

@include('partials.admin.view-hero-styles')

@push('styles')
<style>
    .admin-program-stats { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
    .admin-program-stats__grid { display: flex; flex-wrap: wrap; gap: 0.75rem; }
    .admin-program-stat { min-width: 5.5rem; padding: 0.65rem 1rem; border-radius: var(--radius-md); background: var(--sa-mist); border: 1px solid var(--sa-border); text-align: center; }
    .admin-program-stat__value { display: block; font-size: 1.25rem; font-weight: 800; color: var(--sa-green-dark); }
    .admin-program-stat__label { font-size: 0.75rem; color: var(--sa-muted); }
    .admin-program-stats__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .admin-table-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem; }
    .admin-table-toolbar__label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--sa-muted); }
</style>
@endpush

@include('partials.admin.shell-end')
