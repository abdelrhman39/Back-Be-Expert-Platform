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
    'adminPageTitle' => 'الخريجون',
    'adminPageDesc' => 'مؤشرات التخرج والشهادات ومسار الإصدار',
    'adminLayout' => 'dashboard',
])]
#[Title('الخريجون | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public array $stats = [];

    #[Url(as: 'tab')]
    public string $pipelineTab = 'graduated';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $batch = '';

    public function mount(AdminStatsService $stats): void
    {
        $this->stats = $stats->graduates();
    }

    public function setPipelineTab(string $tab): void
    {
        if (in_array($tab, ['graduated', 'eligible', 'expected', 'all'], true)) {
            $this->pipelineTab = $tab;
            $this->resetPage();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBatch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()->orderBy('name')->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function pipelineStudents()
    {
        return AcademicStudent::query()
            ->with(['batch.program', 'section'])
            ->when($this->pipelineTab === 'all', fn ($q) => $q->whereIn('academic_status', array_keys(AcademicStudentOptions::graduationStatuses())))
            ->when($this->pipelineTab !== 'all', fn ($q) => $q->where('academic_status', $this->pipelineTab))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->search.'%')
                    ->orWhere('national_id', 'like', '%'.$this->search.'%');
            }))
            ->when($this->batch, fn ($q) => $q->where('batch_id', (int) $this->batch))
            ->latest('graduated_at')
            ->latest('joined_at')
            ->paginate(15);
    }
};
?>

@include('partials.admin.dashboard-start', ['dashSubnav' => 'graduates', 'dashSidebarActive' => route('admin.graduates')])

<div class="dash-grid dash-fin-top">
    <div class="dash-hero-card">
        <span>الطلاب الخريجون (الشهادات الصادرة)</span>
        <strong>{{ number_format($stats['graduated_total']) }}</strong>
        <div class="dash-hero-card__stats">
            <span>المتوقعون للتخرج: {{ $stats['expected_graduation'] }}</span>
            <span>المؤهلون للتخرج: {{ $stats['eligible_graduation'] }}</span>
            <span>إجمالي الشهادات: {{ $stats['certificates_issued'] }}</span>
        </div>
    </div>
    <div class="dash-side-rates">
        <div class="dash-progress-row">
            <div class="head"><span>نسبة إنجاز المراجعة</span><span>{{ $stats['review_pct'] }}%</span></div>
            <div class="dash-rate-bar"><span style="width:{{ $stats['review_pct'] }}%"></span></div>
        </div>
        <div class="dash-progress-row">
            <div class="head"><span>نسبة إنجاز الاعتماد</span><span>{{ $stats['approval_pct'] }}%</span></div>
            <div class="dash-rate-bar"><span style="width:{{ $stats['approval_pct'] }}%"></span></div>
        </div>
        <div class="dash-progress-row">
            <div class="head"><span>نسبة إنجاز الإصدار</span><span>{{ $stats['issue_pct'] }}%</span></div>
            <div class="dash-rate-bar"><span style="width:{{ $stats['issue_pct'] }}%"></span></div>
        </div>
    </div>
</div>

<h3 class="dash-block-title">مسار التخرج</h3>
<div class="dash-metric-grid admin-grad-pipeline-tabs">
    @foreach ([
        'expected' => ['label' => 'متوقع التخرج', 'count' => $stats['expected_graduation'], 'hint' => 'في الفصل الحالي', 'icon' => 'teal'],
        'eligible' => ['label' => 'مؤهل للتخرج', 'count' => $stats['eligible_graduation'], 'hint' => 'استوفى المتطلبات', 'icon' => 'green'],
        'graduated' => ['label' => 'خريجون', 'count' => $stats['graduated_total'], 'hint' => 'تم إصدار الشهادة', 'icon' => 'blue'],
    ] as $tabKey => $metric)
        <button
            type="button"
            wire:click="setPipelineTab('{{ $tabKey }}')"
            @class(['dash-metric-card', 'admin-grad-pipeline-tabs__item', 'is-active' => $pipelineTab === $tabKey])
        >
            <div @class(['dash-metric-card__icon', 'dash-metric-card__icon--'.$metric['icon']])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
            </div>
            <div><span class="label">{{ $metric['label'] }}</span><strong>{{ $metric['count'] }}</strong><span class="hint">{{ $metric['hint'] }}</span></div>
        </button>
    @endforeach
    <button
        type="button"
        wire:click="setPipelineTab('all')"
        @class(['dash-metric-card', 'admin-grad-pipeline-tabs__item', 'is-active' => $pipelineTab === 'all'])
    >
        <div class="dash-metric-card__icon dash-metric-card__icon--purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div><span class="label">كل مسار التخرج</span><strong>{{ $stats['graduated_total'] + $stats['eligible_graduation'] + $stats['expected_graduation'] }}</strong><span class="hint">عرض موحّد</span></div>
    </button>
</div>

<section class="admin-crud-card admin-graduates-list" style="margin-top:1rem;">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>
            @if ($pipelineTab === 'all')
                سجل مسار التخرج
            @else
                {{ AcademicStudentOptions::graduationStatuses()[$pipelineTab] ?? 'الخريجون' }}
            @endif
            <span class="admin-crud-card__meta">— {{ $this->pipelineStudents->total() }} طالب</span>
        </h2>
    </div>

    <div class="admin-filter-grid" style="margin-bottom:0.75rem;grid-template-columns:2fr 1fr;">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="الاسم، الرقم الأكاديمي، الهوية...">
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
    </div>

    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>الرقم الأكاديمي</th>
                    <th>البرنامج</th>
                    <th>الدفعة</th>
                    <th>تاريخ التخرج</th>
                    <th>الحالة</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->pipelineStudents as $index => $student)
                    <tr wire:key="grad-row-{{ $student->id }}">
                        <td>{{ $this->pipelineStudents->firstItem() + $index }}</td>
                        <td><a href="{{ route('admin.students.show', $student) }}" class="dash-inline-link">{{ $student->name_ar }}</a></td>
                        <td><code class="admin-code">{{ $student->academic_id ?? '—' }}</code></td>
                        <td>{{ $student->batch?->program?->name_ar ?? '—' }}</td>
                        <td>
                            @if ($student->batch)
                                <a href="{{ route('admin.batches.show', $student->batch) }}" class="dash-inline-link">{{ $student->batch->code }}</a>
                            @else — @endif
                        </td>
                        <td>
                            @if ($student->graduated_at)
                                {{ $student->graduated_at->format('Y-m-d') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>@include('partials.admin.student-status-badge', ['status' => $student->academic_status])</td>
                        <td>
                            <a href="{{ route('admin.students.show', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-table-empty-state">لا يوجد طلاب في هذا المسار.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->pipelineStudents->hasPages())
        {{ $this->pipelineStudents->links() }}
    @endif
</section>

@push('styles')
<style>
    .admin-grad-pipeline-tabs { gap: 0.65rem; }
    .admin-grad-pipeline-tabs__item {
        border: 2px solid transparent;
        cursor: pointer;
        font: inherit;
        text-align: inherit;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .admin-grad-pipeline-tabs__item.is-active {
        border-color: var(--sa-green);
        box-shadow: 0 0 0 2px var(--sa-green-soft);
    }
    .admin-graduates-list { padding: 1rem 1.15rem; }
    .admin-badge--warn { background: #fff7ed; color: #c2410c; }
</style>
@endpush

@include('partials.admin.dashboard-end')
