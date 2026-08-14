<?php

use App\Models\AcademicRequest;
use App\Support\AcademicRequestOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('طلبات الطلاب | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public string $type = 'deferral';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $semester = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.view'), 403);

        $this->type = AcademicRequestOptions::typeFromRoute(request()->route()?->getName());
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSemester(): void
    {
        $this->resetPage();
    }

    public function approveRequest(int $requestId): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.review'), 403);

        $request = AcademicRequest::query()->where('type', $this->type)->findOrFail($requestId);

        if (! $request->canReview()) {
            session()->flash('admin_message', 'لا يمكن مراجعة هذا الطلب في حالته الحالية.');

            return;
        }

        $result = $request->approve(auth()->user());
        unset($this->requests, $this->typeCounts);

        $effects = $result['effects'] ?? [];
        $message = 'تمت الموافقة على الطلب.';
        if ($effects !== []) {
            $message .= ' '.implode(' ', $effects);
        }

        session()->flash('admin_message', $message);
    }

    public function rejectRequest(int $requestId): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.review'), 403);

        $request = AcademicRequest::query()->where('type', $this->type)->findOrFail($requestId);

        if (! $request->canReview()) {
            session()->flash('admin_message', 'لا يمكن مراجعة هذا الطلب في حالته الحالية.');

            return;
        }

        $result = $request->reject(auth()->user());
        unset($this->requests, $this->typeCounts);

        $effects = $result['effects'] ?? [];
        $message = 'تم رفض الطلب.';
        if ($effects !== []) {
            $message .= ' '.implode(' ', $effects);
        }

        session()->flash('admin_message', $message);
    }

    #[Computed]
    public function typeCounts(): array
    {
        $counts = AcademicRequest::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $result = [];
        foreach (AcademicRequestOptions::types() as $key => $meta) {
            $result[$key] = (int) ($counts[$key] ?? 0);
        }

        return $result;
    }

    #[Computed]
    public function requests()
    {
        return AcademicRequest::query()
            ->with('student')
            ->where('type', $this->type)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('student_name', 'like', '%'.$this->search.'%')
                    ->orWhere('student_national_id', 'like', '%'.$this->search.'%')
                    ->orWhere('request_no', 'like', '%'.$this->search.'%')
                    ->orWhere('program_name', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->semester, fn ($q) => $q->where('semester_key', $this->semester))
            ->latest('submitted_at')
            ->paginate(15);
    }
};
?>

@php
    $typeMeta = AcademicRequestOptions::types()[$type] ?? AcademicRequestOptions::types()['deferral'];
    $canReview = auth()->user()?->canAdmin('requests.review');
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route($typeMeta['route']),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => $typeMeta['label']],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-requests-hub">
    <div class="admin-requests-hub__head">
        <div>
            <h1 class="admin-requests-hub__title">طلبات الخدمات الأكاديمية</h1>
            <p class="admin-requests-hub__desc">مراجعة ومعالجة طلبات الطلاب — تأجيل، انسحاب، تغيير برنامج، واعتذار.</p>
        </div>
    </div>

    <div class="admin-requests-type-grid">
        @foreach (AcademicRequestOptions::types() as $typeKey => $meta)
            <a
                href="{{ route($meta['route']) }}"
                @class(['admin-requests-type-card', 'admin-requests-type-card--'.$meta['tone'], 'is-active' => $type === $typeKey])
            >
                <span class="admin-requests-type-card__count">{{ $this->typeCounts[$typeKey] ?? 0 }}</span>
                <span class="admin-requests-type-card__label">{{ $meta['label'] }}</span>
                <span class="admin-requests-type-card__desc">{{ $meta['description'] }}</span>
            </a>
        @endforeach
    </div>
</section>

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>
            بحث متقدم
            <span class="admin-crud-card__meta">— {{ $this->requests->total() }} {{ AcademicRequestOptions::unitLabel($type) }}</span>
        </h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field admin-field--wide">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="ابحث بالاسم، الهوية، رقم الطلب...">
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (AcademicRequestOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if (in_array($type, ['deferral', 'semester_excuse'], true))
            <div class="admin-field">
                <label>فصل القبول</label>
                <select class="admin-control" wire:model.live="semester">
                    <option value="">جميع الفصول</option>
                    @foreach (AcademicRequestOptions::semesters() as $sem)
                        <option value="{{ $sem['key'] }}">{{ $sem['label'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</section>

<section class="admin-crud-card admin-requests-table-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ AcademicRequestOptions::tableTitle($type) }}</h2>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-data-table admin-requests-table">
            <thead>
                <tr>
                    <th>#</th>
                    @if ($type === 'program_change')
                        <th>رقم الطلب</th>
                        <th>الاسم</th>
                        <th>البرنامج الحالي</th>
                        <th>البرنامج الجديد</th>
                    @elseif ($type === 'withdrawal')
                        <th>الطالب</th>
                        <th>البرنامج</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                    @elseif ($type === 'semester_excuse')
                        <th>الطالب</th>
                        <th>البرنامج</th>
                        <th>الفصل</th>
                        <th>المراجعة</th>
                        <th>الحالة</th>
                    @else
                        <th>الطالب</th>
                        <th>البرنامج</th>
                        <th>المراجعة</th>
                        <th>الحالة</th>
                    @endif
                    <th>التاريخ</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->requests as $index => $request)
                    <tr wire:key="request-row-{{ $request->id }}">
                        <td>{{ $this->requests->firstItem() + $index }}</td>

                        @if ($type === 'program_change')
                            <td>
                                <div class="admin-request-no-cell">
                                    <span class="admin-request-no-cell__no">{{ $request->request_no }}</span>
                                    @include('partials.admin.request-status-pill', ['status' => $request->status])
                                </div>
                            </td>
                            <td>
                                <div class="admin-student-cell">
                                    @if ($request->student)
                                        <a href="{{ route('admin.students.show', $request->student) }}" class="admin-student-cell__name admin-student-link">{{ $request->student_name }}</a>
                                    @else
                                        <span class="admin-student-cell__name">{{ $request->student_name }}</span>
                                    @endif
                                    <span class="admin-student-cell__id" dir="ltr">{{ $request->student_national_id ?? '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="admin-program-cell">
                                    <span class="admin-program-cell__name">{{ $request->payloadValue('current_program', $request->program_name) }}</span>
                                    <span class="admin-duration-pill">{{ $request->payloadValue('current_duration', '—') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="admin-program-cell">
                                    <span class="admin-program-cell__name">{{ $request->payloadValue('new_program', '—') }}</span>
                                    <span class="admin-duration-pill">{{ $request->payloadValue('new_duration', '—') }}</span>
                                </div>
                            </td>
                        @elseif ($type === 'withdrawal')
                            <td>
                                <div class="admin-student-cell">
                                    @if ($request->student)
                                        <a href="{{ route('admin.students.show', $request->student) }}" class="admin-student-cell__name admin-student-link">{{ $request->student_name }}</a>
                                    @else
                                        <span class="admin-student-cell__name">{{ $request->student_name }}</span>
                                    @endif
                                    <span class="admin-student-cell__id" dir="ltr">{{ $request->student_national_id ?? '—' }}</span>
                                </div>
                            </td>
                            <td>{{ $request->program_name ?? '—' }}</td>
                            <td>{{ $request->payloadValue('payment_method', '—') }}</td>
                            <td>@include('partials.admin.request-status-pill', ['status' => $request->status])</td>
                        @elseif ($type === 'semester_excuse')
                            <td>
                                <div class="admin-student-cell">
                                    @if ($request->student)
                                        <a href="{{ route('admin.students.show', $request->student) }}" class="admin-student-cell__name admin-student-link">{{ $request->student_name }}</a>
                                    @else
                                        <span class="admin-student-cell__name">{{ $request->student_name }}</span>
                                    @endif
                                    <span class="admin-student-cell__id" dir="ltr">{{ $request->student_national_id ?? '—' }}</span>
                                </div>
                            </td>
                            <td>{{ $request->program_name ?? '—' }}</td>
                            <td>{{ $request->semester_label ?? '—' }}</td>
                            <td>@include('partials.admin.request-review-pill', ['status' => $request->review_status])</td>
                            <td>@include('partials.admin.request-status-pill', ['status' => $request->status])</td>
                        @else
                            <td>
                                <div class="admin-student-cell">
                                    @if ($request->student)
                                        <a href="{{ route('admin.students.show', $request->student) }}" class="admin-student-cell__name admin-student-link">{{ $request->student_name }}</a>
                                    @else
                                        <span class="admin-student-cell__name">{{ $request->student_name }}</span>
                                    @endif
                                    <span class="admin-student-cell__id" dir="ltr">{{ $request->student_national_id ?? '—' }}</span>
                                </div>
                            </td>
                            <td>{{ $request->program_name ?? '—' }}</td>
                            <td>@include('partials.admin.request-review-pill', ['status' => $request->review_status])</td>
                            <td>@include('partials.admin.request-status-pill', ['status' => $request->status])</td>
                        @endif

                        <td>
                            <div class="admin-date-cell">
                                <span class="admin-date-cell__date">{{ $request->submitted_at?->format('Y-m-d') ?? '—' }}</span>
                                <span class="admin-date-cell__ago">{{ $request->submitted_at?->diffForHumans() ?? '' }}</span>
                            </div>
                        </td>
                        <td class="admin-table-actions">
                            @include('partials.admin.request-actions-menu', ['request' => $request, 'canReview' => $canReview])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="admin-table-empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                            <p>{{ AcademicRequestOptions::emptyMessage($type) }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->requests->hasPages())
        {{ $this->requests->links() }}
    @endif
</section>

@push('styles')
<style>
    .admin-requests-hub { padding: 1.25rem 1.35rem; }
    .admin-requests-hub__head { margin-bottom: 1rem; }
    .admin-requests-hub__title { margin: 0 0 0.25rem; font-size: 1.05rem; font-weight: 800; color: var(--sa-ink); }
    .admin-requests-hub__desc { margin: 0; font-size: 0.84rem; color: var(--sa-muted); }
    .admin-requests-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
        gap: 0.65rem;
    }
    .admin-requests-type-card {
        text-decoration: none;
        color: inherit;
        border: 1px solid var(--sa-border);
        border-radius: var(--radius-md);
        background: var(--surface-card);
        padding: 0.75rem 0.85rem;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }
    .admin-requests-type-card:hover { border-color: var(--sa-green); transform: translateY(-1px); }
    .admin-requests-type-card.is-active {
        border-color: var(--sa-green);
        box-shadow: 0 0 0 2px var(--sa-green-soft);
        background: var(--sa-green-soft);
    }
    .admin-requests-type-card__count {
        display: block;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--sa-green-dark);
        line-height: 1.1;
    }
    .admin-requests-type-card__label {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--sa-ink);
    }
    .admin-requests-type-card__desc {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.72rem;
        color: var(--sa-muted);
        line-height: 1.35;
    }
    .admin-actions-item--btn {
        border: none;
        background: transparent;
        width: 100%;
        text-align: inherit;
        cursor: pointer;
        font: inherit;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .admin-requests-table-card .admin-table-actions { min-width: 3rem; }
    .admin-request-effects {
        margin: 0;
        padding-inline-start: 1.1rem;
        color: var(--sa-ink);
        line-height: 1.55;
    }
    .admin-request-effects li + li { margin-top: 0.25rem; }
    /* Keep Livewire action menus visible when using position:fixed without reparenting */
    .admin-table-wrap { overflow-x: auto; overflow-y: visible; }
    .admin-actions-dropdown.is-fixed {
        z-index: 80;
        min-width: 11rem;
        background: #fff;
        border: 1px solid var(--sa-border);
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.14);
        padding: 0.35rem;
    }
</style>
@endpush

@include('partials.admin.shell-end')
