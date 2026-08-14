<?php

use App\Models\User;
use App\Services\Reports\AdminReportService;
use App\Support\Reports\CsvExporter;
use App\Support\Reports\ReportFilter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('مركز التقارير | لوحة التحكم')]
class extends Component
{
    #[Url]
    public string $area = 'overview';

    #[Url]
    public string $preset = '30d';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public ?int $programId = null;

    #[Url]
    public ?int $batchId = null;

    #[Url]
    public string $status = '';

    public function mount(AdminReportService $reports): void
    {
        abort_unless(auth()->user()?->canAdmin('reports.view'), 403);

        $areas = collect($reports->areasFor(auth()->user()));
        if ($areas->isEmpty()) {
            abort(403);
        }

        if (! $areas->contains('id', $this->area)) {
            $this->area = $areas->first()['id'];
        }

        if ($this->from === '' && $this->to === '' && $this->preset === 'custom') {
            $this->preset = '30d';
        }
    }

    public function setArea(string $area): void
    {
        $this->area = $area;
        $this->status = '';
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
        if ($preset !== 'custom') {
            $this->from = '';
            $this->to = '';
        } else {
            $filter = $this->reportFilter;
            $this->from = $filter->from->format('Y-m-d');
            $this->to = $filter->to->format('Y-m-d');
        }
    }

    public function updatedProgramId(): void
    {
        $this->batchId = null;
    }

    public function clearFilters(): void
    {
        $this->preset = '30d';
        $this->from = '';
        $this->to = '';
        $this->programId = null;
        $this->batchId = null;
        $this->status = '';
    }

    public function export(AdminReportService $reports, CsvExporter $csv): mixed
    {
        abort_unless(auth()->user()?->canAdmin('reports.export') || auth()->user()?->canAdmin('reports.view'), 403);
        abort_unless($reports->canAccessArea(auth()->user(), $this->area), 403);

        $payload = $reports->build($this->area, $this->reportFilter);
        $export = $payload['export'];

        return $csv->download($export['filename'], $export['headers'], $export['rows']);
    }

    #[Computed]
    public function areas(): array
    {
        return app(AdminReportService::class)->areasFor(auth()->user());
    }

    #[Computed]
    public function reportFilter(): ReportFilter
    {
        return ReportFilter::fromInputs(
            preset: $this->preset,
            from: $this->from !== '' ? $this->from : null,
            to: $this->to !== '' ? $this->to : null,
            programId: $this->programId,
            batchId: $this->batchId,
            status: $this->status !== '' ? $this->status : null,
        );
    }

    #[Computed]
    public function report(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $reports = app(AdminReportService::class);
        abort_unless($reports->canAccessArea($user, $this->area), 403);

        return $reports->build($this->area, $this->reportFilter);
    }

    #[Computed]
    public function programs(): array
    {
        return app(AdminReportService::class)->programOptions();
    }

    #[Computed]
    public function batches(): array
    {
        return app(AdminReportService::class)->batchOptions($this->programId);
    }

    #[Computed]
    public function areaMeta(): array
    {
        return collect($this->areas)->firstWhere('id', $this->area) ?? [
            'id' => $this->area,
            'label' => $this->area,
            'description' => '',
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.reports'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'مركز التقارير'],
    ],
])

@php
    $report = $this->report;
    $filter = $this->reportFilter;
    $areaMeta = $this->areaMeta;
    $presets = config('admin-reports.presets', []);
@endphp

<div class="admin-reports">
    <section class="admin-crud-card">
        <div class="admin-crud-card__head admin-crud-card__head--split">
            <div>
                <h2>مركز التقارير</h2>
                <p class="admin-crud-card__meta">تقارير شاملة قابلة للفلترة عبر كل مجالات المنصة.</p>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <button type="button" class="admin-btn-secondary--sm" wire:click="clearFilters">إعادة ضبط الفلاتر</button>
                <button type="button" class="admin-btn-primary--sm" wire:click="export">تصدير CSV</button>
            </div>
        </div>

        <div class="admin-reports__tabs" role="tablist" aria-label="أقسام التقارير">
            @foreach ($this->areas as $tab)
                <button
                    type="button"
                    role="tab"
                    class="admin-reports__tab {{ $area === $tab['id'] ? 'is-active' : '' }}"
                    wire:click="setArea('{{ $tab['id'] }}')"
                    wire:key="area-{{ $tab['id'] }}"
                >{{ $tab['label'] }}</button>
            @endforeach
        </div>
    </section>

    <section class="admin-crud-card admin-crud-card--filter">
        <div class="admin-crud-card__head">
            <h2>{{ $areaMeta['label'] }}</h2>
            <p class="admin-crud-card__meta">{{ $areaMeta['description'] }} · الفترة: {{ $filter->label() }}</p>
        </div>

        <div class="admin-reports__presets">
            @foreach ($presets as $key => $meta)
                <button
                    type="button"
                    class="dash-status-pill {{ $preset === $key ? 'is-active' : '' }}"
                    wire:click="setPreset('{{ $key }}')"
                >{{ $meta['label'] }}</button>
            @endforeach
        </div>

        <div class="admin-filter-grid" style="margin-top:0.85rem;">
            @if ($preset === 'custom')
                <label class="admin-field">
                    <span>من تاريخ</span>
                    <input type="date" class="admin-control" wire:model.live="from">
                </label>
                <label class="admin-field">
                    <span>إلى تاريخ</span>
                    <input type="date" class="admin-control" wire:model.live="to">
                </label>
            @endif

            @if (in_array($area, ['overview', 'students', 'attendance', 'exams'], true))
                <label class="admin-field">
                    <span>البرنامج</span>
                    <select class="admin-control" wire:model.live="programId">
                        <option value="">كل البرامج</option>
                        @foreach ($this->programs as $program)
                            <option value="{{ $program['id'] }}">{{ $program['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span>الدفعة</span>
                    <select class="admin-control" wire:model.live="batchId">
                        <option value="">كل الدفعات</option>
                        @foreach ($this->batches as $batch)
                            <option value="{{ $batch['id'] }}">{{ $batch['label'] }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if (in_array($area, ['students', 'finance', 'support', 'applications', 'requests', 'catalog'], true))
                <label class="admin-field">
                    <span>الحالة</span>
                    <input type="text" class="admin-control" wire:model.live.debounce.400ms="status" placeholder="مثال: studying / paid / pending">
                </label>
            @endif
        </div>
    </section>

    <section class="admin-kpi-row admin-reports__kpis">
        @foreach ($report['kpis'] as $i => $kpi)
            <div class="admin-crud-card" wire:key="kpi-{{ $area }}-{{ $i }}">
                <span class="admin-crud-card__meta">{{ $kpi['label'] }}</span>
                <strong>{{ $kpi['value'] }}</strong>
                @if (! empty($kpi['hint']))
                    <span class="admin-crud-card__meta">{{ $kpi['hint'] }}</span>
                @endif
            </div>
        @endforeach
    </section>

    @foreach ($report['tables'] as $t => $table)
        <section class="admin-crud-card" wire:key="table-{{ $area }}-{{ $t }}">
            <div class="admin-crud-card__head">
                <h2>{{ $table['title'] }}</h2>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            @foreach ($table['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($table['rows'] as $r => $row)
                            <tr wire:key="row-{{ $area }}-{{ $t }}-{{ $r }}">
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ max(1, count($table['columns'])) }}">لا توجد بيانات ضمن الفلاتر الحالية.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>

<style>
    .admin-reports { display: flex; flex-direction: column; gap: 1rem; }
    .admin-reports__tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.85rem;
    }
    .admin-reports__tab {
        border: 1px solid var(--sa-border, #dbe3ea);
        background: #fff;
        color: var(--sa-ink, #123);
        border-radius: 999px;
        padding: 0.4rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
    }
    .admin-reports__tab.is-active {
        background: var(--sa-green, #0f766e);
        border-color: var(--sa-green, #0f766e);
        color: #fff;
    }
    .admin-reports__presets {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .admin-reports__kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10.5rem, 1fr));
        gap: 0.75rem;
    }
    .admin-reports__kpis .admin-crud-card {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .admin-reports__kpis strong {
        font-size: 1.25rem;
    }
</style>

@include('partials.admin.shell-end')
