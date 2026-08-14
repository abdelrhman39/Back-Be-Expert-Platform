<?php

use App\Models\AcademicStudent;
use App\Models\InstallmentContract;
use App\Models\InstallmentPlanTemplate;
use App\Models\User;
use App\Services\InstallmentContractService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('عقود التقسيط | لوحة التحكم')]
class extends Component
{
    #[Url]
    public string $status = 'all';

    public bool $showCreate = false;

    public ?int $studentId = null;

    public ?int $templateId = null;

    public string $totalAmount = '';

    public string $startsAt = '';

    public string $title = '';

    public string $adminNotes = '';

    public ?string $flashMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.view'), 403);
        $this->startsAt = now()->format('Y-m-d');
    }

    public function createContract(InstallmentContractService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'studentId' => ['required', 'exists:academic_students,id'],
            'templateId' => ['required', 'exists:installment_plan_templates,id'],
            'totalAmount' => ['required', 'numeric', 'min:1'],
            'startsAt' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
        ], [], [
            'studentId' => 'الطالب',
            'templateId' => 'خطة التقسيط',
            'totalAmount' => 'المبلغ الإجمالي',
        ]);

        $student = AcademicStudent::query()->with('user')->findOrFail($this->studentId);
        $user = $student->user ?? User::query()->where('email', $student->email)->first();

        if (! $user) {
            $this->addError('studentId', 'الطالب غير مربوط بحساب مستخدم.');

            return;
        }

        $template = InstallmentPlanTemplate::query()->findOrFail($this->templateId);

        $contract = $service->createFromTemplate(
            studentUser: $user,
            template: $template,
            totalAmount: (float) $this->totalAmount,
            academicStudent: $student,
            startsAt: \Carbon\Carbon::parse($this->startsAt),
            creator: auth()->user(),
            title: $this->title ?: null,
            adminNotes: $this->adminNotes ?: null,
        );

        $this->reset(['showCreate', 'studentId', 'templateId', 'totalAmount', 'title', 'adminNotes']);
        $this->flashMessage = 'تم إنشاء العقد '.$contract->contract_no;
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-contracts'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'عقود التقسيط'],
    ],
])

@if ($flashMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $flashMessage }}</div>
@endif

@php
    $contracts = InstallmentContract::query()
        ->with(['user', 'student', 'template'])
        ->when($status !== 'all', fn ($q) => $q->where('status', $status))
        ->orderByDesc('created_at')
        ->paginate(20);
    $stats = [
        'active' => InstallmentContract::query()->where('status', 'active')->count(),
        'completed' => InstallmentContract::query()->where('status', 'completed')->count(),
        'total_due' => \App\Models\InstallmentSchedule::query()->whereIn('status', ['pending', 'overdue'])->sum('amount'),
        'collected' => InstallmentContract::query()->sum('paid_amount'),
    ];
@endphp

<div class="admin-kpi-row" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0.75rem;margin-bottom:1rem;">
    <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">عقود نشطة</span><strong style="font-size:1.4rem;">{{ $stats['active'] }}</strong></div>
    <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">مكتملة</span><strong style="font-size:1.4rem;">{{ $stats['completed'] }}</strong></div>
    <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">محصّل</span><strong style="font-size:1.4rem;">{{ number_format($stats['collected'], 0) }} ر.س</strong></div>
    <div class="admin-crud-card" style="padding:1rem;"><span class="admin-crud-card__meta">متبقي على الأقساط</span><strong style="font-size:1.4rem;">{{ number_format($stats['total_due'], 0) }} ر.س</strong></div>
</div>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--split">
        <div>
            <h2>عقود تقسيط الطلاب</h2>
            <p class="admin-crud-card__meta">متابعة الأقساط والمتبقي والسداد.</p>
        </div>
        @canAdmin('installments.manage')
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="$toggle('showCreate')">{{ $showCreate ? 'إلغاء' : 'عقد جديد' }}</button>
        @endcanAdmin
    </div>

    @if ($showCreate)
        <div class="admin-crud-card admin-crud-card--filter" style="margin-bottom:1rem;">
            <div class="admin-form-grid admin-form-grid--2">
                <div class="admin-field">
                    <label>الطالب *</label>
                    <select class="admin-control" wire:model="studentId">
                        <option value="">— اختر —</option>
                        @foreach (AcademicStudent::query()->with('user')->orderBy('name_ar')->limit(200)->get() as $s)
                            <option value="{{ $s->id }}">{{ $s->name_ar }} ({{ $s->academic_id }})</option>
                        @endforeach
                    </select>
                    @error('studentId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label>خطة التقسيط *</label>
                    <select class="admin-control" wire:model="templateId">
                        <option value="">— اختر —</option>
                        @foreach (InstallmentPlanTemplate::query()->where('is_active', true)->orderBy('name_ar')->get() as $t)
                            <option value="{{ $t->id }}">{{ $t->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('templateId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label>المبلغ الإجمالي (ر.س) *</label>
                    <input type="number" class="admin-control" wire:model="totalAmount" min="1" step="0.01">
                    @error('totalAmount')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label>تاريخ البداية *</label>
                    <input type="date" class="admin-control" wire:model="startsAt">
                </div>
                <div class="admin-field admin-field--wide">
                    <label>عنوان العقد (اختياري)</label>
                    <input type="text" class="admin-control" wire:model="title">
                </div>
                <div class="admin-field admin-field--wide">
                    <label>ملاحظات إدارية</label>
                    <textarea class="admin-control" rows="2" wire:model="adminNotes"></textarea>
                </div>
            </div>
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="createContract">إنشاء العقد والجدول</button>
        </div>
    @endif

    <div class="admin-filter-actions" style="margin-bottom:0.75rem;">
        @foreach (['all' => 'الكل'] + \App\Support\InstallmentOptions::contractStatuses() as $key => $label)
            <button type="button" @class(['admin-btn-secondary admin-btn-secondary--sm', 'is-active' => $status === $key]) wire:click="$set('status', '{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>

    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>العقد</th>
                    <th>الطالب</th>
                    <th>الإجمالي</th>
                    <th>المسدّد</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contracts as $contract)
                    <tr wire:key="c-{{ $contract->id }}">
                        <td>
                            <strong>{{ $contract->contract_no }}</strong>
                            <div class="admin-crud-card__meta">{{ $contract->title }}</div>
                        </td>
                        <td>{{ $contract->student?->name_ar ?? $contract->user?->displayName() }}</td>
                        <td>{{ number_format($contract->total_amount, 2) }}</td>
                        <td>{{ number_format($contract->paid_amount, 2) }}</td>
                        <td>{{ number_format($contract->remaining_balance, 2) }}</td>
                        <td><span class="admin-badge">{{ $contract->statusLabel() }}</span></td>
                        <td><a href="{{ route('admin.installment-contracts.show', $contract) }}" class="admin-btn-secondary admin-btn-secondary--sm">التفاصيل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">لا توجد عقود.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $contracts->links() }}
</section>

@include('partials.admin.shell-end')
