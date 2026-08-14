<?php

use App\Models\RegistrationApplication;
use App\Support\RegistrationApplicationOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('طلبات التسجيل | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public string $type = 'client';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.view'), 403);

        $this->type = RegistrationApplicationOptions::typeFromRoute(request()->route()?->getName());
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function typeCounts(): array
    {
        $counts = RegistrationApplication::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $result = [];
        foreach (RegistrationApplicationOptions::types() as $key => $meta) {
            $result[$key] = (int) ($counts[$key] ?? 0);
        }

        return $result;
    }

    #[Computed]
    public function statusCounts(): array
    {
        $counts = RegistrationApplication::query()
            ->where('type', $this->type)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = [];
        foreach (RegistrationApplicationOptions::statuses() as $key => $label) {
            $result[$key] = (int) ($counts[$key] ?? 0);
        }

        return $result;
    }

    #[Computed]
    public function pendingCount(): int
    {
        return RegistrationApplication::query()
            ->where('type', $this->type)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
    }

    #[Computed]
    public function applications()
    {
        return RegistrationApplication::query()
            ->where('type', $this->type)
            ->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('application_no', 'like', $term)
                        ->orWhere('applicant_name', 'like', $term)
                        ->orWhere('applicant_email', 'like', $term)
                        ->orWhere('applicant_phone', 'like', $term)
                        ->orWhere('payload', 'like', $term);
                });
            })
            ->when(filled($this->status), fn ($q) => $q->where('status', $this->status))
            ->latest('submitted_at')
            ->paginate(15);
    }
};
?>

@php
    $typeMeta = RegistrationApplicationOptions::types()[$type];
    $typeCounts = $this->typeCounts;
    $statusCounts = $this->statusCounts;
    $isInstructor = $type === 'instructor';
    $publicApplyUrl = match ($type) {
        'fellowship' => route('admin.fellowships'),
        default => route('apply.form', ['locale' => 'ar', 'type' => $type]),
    };
@endphp

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route($typeMeta['route']),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => $typeMeta['label']],
    ],
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-applications.css') }}?v=1">
@endpush

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="apps-hub-hero {{ $isInstructor ? 'apps-hub-hero--instructor' : '' }}">
    <div>
        <span class="apps-hub-hero__eyebrow">طلبات التسجيل الخارجية</span>
        <h1>{{ $typeMeta['label'] }}</h1>
        <p>
            @if ($isInstructor)
                مراجعة طلبات انضمام المدربين، قبول المؤهلين، وإنشاء حساب بوابة المدرب تلقائياً عند الموافقة.
            @else
                إدارة طلبات «{{ $typeMeta['label'] }}» الواردة من الموقع العام — {{ $this->pendingCount }} قيد المعالجة حالياً.
            @endif
        </p>
    </div>
    <div class="apps-hub-hero__actions">
        <a href="{{ $publicApplyUrl }}" class="admin-btn-secondary" target="_blank" rel="noopener">نموذج التقديم العام</a>
        @if ($isInstructor && auth()->user()?->canAdmin('staff.manage'))
            <a href="{{ route('admin.staff.members') }}" class="admin-btn-primary">كوادر المدربين</a>
        @endif
    </div>
</section>

<div class="apps-hub-kpis">
    @foreach (RegistrationApplicationOptions::statuses() as $statusKey => $statusLabel)
        <button
            type="button"
            class="apps-hub-kpi {{ $status === $statusKey ? 'is-active' : '' }}"
            wire:click="$set('status', '{{ $status === $statusKey ? '' : $statusKey }}')"
        >
            <strong>{{ $statusCounts[$statusKey] ?? 0 }}</strong>
            <span>{{ $statusLabel }}</span>
        </button>
    @endforeach
    <div class="apps-hub-kpi apps-hub-kpi--muted">
        <strong>{{ array_sum($statusCounts) }}</strong>
        <span>الإجمالي</span>
    </div>
</div>

<div class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>{{ $typeMeta['label'] }}</h2>
            <p class="admin-crud-card__desc">
                @if ($this->pendingCount > 0)
                    {{ $this->pendingCount }} طلب يحتاج مراجعة
                @else
                    لا توجد طلبات معلّقة حالياً
                @endif
            </p>
        </div>
    </div>

    <div class="admin-request-type-tabs">
        @foreach (RegistrationApplicationOptions::types() as $key => $meta)
            <a href="{{ route($meta['route']) }}"
               class="admin-request-type-tab {{ $key === $type ? 'is-active' : '' }}">
                {{ $meta['label'] }}
                <span class="admin-request-type-tab__count">{{ $typeCounts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="admin-filter-bar">
        <div class="admin-field admin-field--inline">
            <input type="search" class="admin-control" placeholder="بحث بالاسم، البريد، الجوال، أو رقم الطلب…" wire:model.live.debounce.300ms="search">
        </div>
        <div class="admin-field admin-field--inline">
            <select class="admin-control" wire:model.live="status">
                <option value="">كل الحالات</option>
                @foreach (RegistrationApplicationOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>المتقدّم</th>
                    @if ($isInstructor)
                        <th>التخصص</th>
                        <th>المسمى</th>
                        <th>الجوال</th>
                    @else
                        <th>البريد</th>
                    @endif
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->applications as $application)
                    <tr>
                        <td dir="ltr"><code>{{ $application->application_no }}</code></td>
                        <td>
                            <div class="apps-hub-applicant">
                                <strong>{{ $application->applicant_name }}</strong>
                                @if ($isInstructor)
                                    <span dir="ltr">{{ $application->applicant_email }}</span>
                                @endif
                            </div>
                        </td>
                        @if ($isInstructor)
                            <td>{{ $application->payloadValue('specialization') ?: '—' }}</td>
                            <td>{{ $application->payloadValue('job_title') ?: '—' }}</td>
                            <td dir="ltr">{{ $application->applicant_phone ?: '—' }}</td>
                        @else
                            <td dir="ltr">{{ $application->applicant_email }}</td>
                        @endif
                        <td>
                            <span class="admin-badge admin-badge--{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ $application->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            {{ $application->submitted_at?->format('Y-m-d') ?? '—' }}
                            @if ($application->submitted_at)
                                <div class="apps-hub-ago">{{ $application->submitted_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.applications.show', ['application' => $application]) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isInstructor ? 8 : 6 }}" class="admin-table-empty">
                            <div class="apps-hub-empty">
                                <strong>لا توجد طلبات مطابقة</strong>
                                <span>يمكنك مشاركة رابط نموذج التقديم أو توسيع نطاق البحث.</span>
                                <a href="{{ $publicApplyUrl }}" target="_blank" rel="noopener">فتح نموذج التقديم</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->applications->links() }}
</div>

@include('partials.admin.shell-end')
