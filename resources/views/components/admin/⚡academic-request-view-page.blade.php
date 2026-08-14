<?php

use App\Models\AcademicRequest;
use App\Support\AcademicRequestOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('عرض الطلب | لوحة التحكم')]
class extends Component
{
    public AcademicRequest $academicRequest;

    public string $adminNotes = '';

    public function mount(AcademicRequest $academicRequest): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.view'), 403);

        $this->academicRequest = $academicRequest->load(['student', 'program', 'reviewer']);
        $this->adminNotes = $academicRequest->admin_notes ?? '';
    }

    public function approve(): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.review'), 403);

        if (! $this->academicRequest->canReview()) {
            session()->flash('admin_message', 'لا يمكن مراجعة هذا الطلب في حالته الحالية.');

            return;
        }

        $result = $this->academicRequest->approve(auth()->user(), $this->adminNotes ?: null);
        $this->academicRequest = $result['request'];

        $effects = $result['effects'] ?? [];
        $message = 'تمت الموافقة على الطلب.';
        if ($effects !== []) {
            $message .= ' '.implode(' ', $effects);
        }

        session()->flash('admin_message', $message);
    }

    public function reject(): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.review'), 403);

        if (! $this->academicRequest->canReview()) {
            session()->flash('admin_message', 'لا يمكن مراجعة هذا الطلب في حالته الحالية.');

            return;
        }

        $result = $this->academicRequest->reject(auth()->user(), $this->adminNotes ?: null);
        $this->academicRequest = $result['request'];

        $effects = $result['effects'] ?? [];
        $message = 'تم رفض الطلب.';
        if ($effects !== []) {
            $message .= ' '.implode(' ', $effects);
        }

        session()->flash('admin_message', $message);
    }

    public function markProcessing(): void
    {
        abort_unless(auth()->user()?->canAdmin('requests.review'), 403);

        $result = $this->academicRequest->markProcessing(auth()->user());
        $this->academicRequest = $result['request'];
        session()->flash('admin_message', 'تم تحديث حالة الطلب إلى «جاري العمل عليه».');
    }
};
?>

@php
    $request = $academicRequest;
    $type = $request->type;
    $canReview = auth()->user()?->canAdmin('requests.review') && $request->canReview();
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route(AcademicRequestOptions::types()[$type]['route'] ?? 'admin.requests.deferral'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => $request->listRoute(), 'label' => AcademicRequestOptions::typeLabel($type)],
        ['label' => 'طلب #'.$request->id],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<div class="admin-crud-card admin-request-detail-card">
    <div class="admin-request-detail-card__head">
        <h1>{{ AcademicRequestOptions::viewTitle($type) }}</h1>
    </div>

    <div class="admin-request-detail-card__body">
        <dl class="admin-request-detail-list">
            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">تاريخ الإضافة</dt>
                <dd class="admin-request-detail-row__value">
                    @if ($request->submitted_at)
                        <time datetime="{{ $request->submitted_at->toIso8601String() }}">{{ $request->submitted_at->format('Y-m-d H:i:s') }}</time>
                        <span class="admin-request-detail-foot__ago">— {{ $request->submitted_at->diffForHumans() }}</span>
                    @else
                        —
                    @endif
                </dd>
            </div>

            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">رقم الطلب</dt>
                <dd class="admin-request-detail-row__value"><strong>{{ $request->request_no }}</strong></dd>
            </div>

            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">اسم الطالب</dt>
                <dd class="admin-request-detail-row__value">
                    @if ($request->student)
                        <a href="{{ route('admin.students.show', $request->student) }}" class="admin-student-link">{{ $request->student_name }}</a>
                    @else
                        {{ $request->student_name }}
                    @endif
                    @if ($request->student_national_id)
                        <span dir="ltr" class="admin-request-detail-foot__ago"> — {{ $request->student_national_id }}</span>
                    @endif
                </dd>
            </div>

            @if ($type === 'program_change')
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">البرنامج الحالي</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->payloadValue('current_program_full', $request->program_name) }}</dd>
                </div>
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">البرنامج الجديد</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->payloadValue('new_program_full', $request->payloadValue('new_program')) }}</dd>
                </div>
            @elseif ($type === 'withdrawal')
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">البرنامج</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->program_name ?? '—' }}</dd>
                </div>
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">طريقة الدفع</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->payloadValue('payment_method', '—') }}</dd>
                </div>
            @elseif ($type === 'semester_excuse')
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">البرنامج</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->payloadValue('program_full', $request->program_name) }}</dd>
                </div>
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">الفصل الدراسي</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->semester_label ?? '—' }}</dd>
                </div>
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">حالة المراجعة</dt>
                    <dd class="admin-request-detail-row__value">@include('partials.admin.request-review-pill', ['status' => $request->review_status])</dd>
                </div>
            @else
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">البرنامج</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->program_name ?? '—' }}</dd>
                </div>
                @if ($request->semester_label)
                    <div class="admin-request-detail-row">
                        <dt class="admin-request-detail-row__label">فصل القبول</dt>
                        <dd class="admin-request-detail-row__value">{{ $request->semester_label }}</dd>
                    </div>
                @endif
                @if ($request->payloadValue('target_semester'))
                    <div class="admin-request-detail-row">
                        <dt class="admin-request-detail-row__label">الفصل المطلوب التأجيل إليه</dt>
                        <dd class="admin-request-detail-row__value">{{ $request->payloadValue('target_semester') }}</dd>
                    </div>
                @endif
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">حالة المراجعة</dt>
                    <dd class="admin-request-detail-row__value">@include('partials.admin.request-review-pill', ['status' => $request->review_status])</dd>
                </div>
            @endif

            @if ($request->reason)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">السبب</dt>
                    <dd class="admin-request-detail-row__value">
                        <p class="admin-request-detail-text">{{ $request->reason }}</p>
                    </dd>
                </div>
            @endif

            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">حالة الطلب</dt>
                <dd class="admin-request-detail-row__value">@include('partials.admin.request-status-pill', ['status' => $request->status])</dd>
            </div>

            @if ($request->reviewer)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">آخر مراجع</dt>
                    <dd class="admin-request-detail-row__value">
                        {{ $request->reviewer->displayName() }}
                        @if ($request->reviewed_at)
                            <span class="admin-request-detail-foot__ago">— {{ $request->reviewed_at->format('Y-m-d H:i') }}</span>
                        @endif
                    </dd>
                </div>
            @endif

            @if ($request->payloadValue('decision_effects'))
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">ما تم تنفيذه</dt>
                    <dd class="admin-request-detail-row__value">
                        <ul class="admin-request-effects">
                            @foreach ((array) $request->payloadValue('decision_effects') as $effect)
                                <li>{{ $effect }}</li>
                            @endforeach
                        </ul>
                        @if ($request->payloadValue('decision_applied_at'))
                            <span class="admin-request-detail-foot__ago">{{ $request->payloadValue('decision_applied_at') }}</span>
                        @endif
                    </dd>
                </div>
            @endif

            @if ($type === 'program_change' || $type === 'semester_excuse')
                <div class="admin-request-detail-row admin-request-detail-row--signature">
                    <dt class="admin-request-detail-row__label">صورة التوقيع</dt>
                    <dd class="admin-request-detail-row__value">
                        <div class="admin-signature-box">
                            <svg class="admin-signature-svg" viewBox="0 0 520 140" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="توقيع الطالب">
                                <path d="M42 77 C110 20, 150 120, 220 65 S320 30, 400 70 S470 100, 500 55" fill="none" stroke="#222" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M170 92 Q240 35, 310 82" fill="none" stroke="#222" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <p class="admin-signature-box__hint">توقيع إلكتروني للطالب — {{ $request->student_name }}</p>
                        </div>
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    @if ($canReview || $request->admin_notes)
        <div class="admin-request-detail-card__foot">
            @if ($canReview)
                <div class="admin-field" style="margin-bottom: 1rem;">
                    <label for="admin-notes">ملاحظات الإدارة</label>
                    <textarea id="admin-notes" class="admin-control" rows="3" wire:model="adminNotes" placeholder="ملاحظات اختيارية عند الموافقة أو الرفض..."></textarea>
                </div>
                <div class="admin-request-detail-foot__actions" style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                    @if ($request->status === 'pending')
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="markProcessing">بدء المعالجة</button>
                    @endif
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="approve" wire:confirm="تأكيد الموافقة على الطلب؟">موافقة</button>
                    <button type="button" class="admin-btn-outline admin-btn-outline--danger admin-btn-secondary--sm" wire:click="reject" wire:confirm="تأكيد رفض الطلب؟">رفض</button>
                </div>
            @elseif ($request->admin_notes)
                <div class="admin-request-detail-foot__label">ملاحظات الإدارة</div>
                <div class="admin-request-detail-foot__value admin-request-detail-text">{{ $request->admin_notes }}</div>
            @endif
        </div>
    @endif
</div>

<p class="admin-request-detail-back">
    <a href="{{ $request->listRoute() }}" class="admin-btn-secondary admin-btn-secondary--sm">← العودة إلى {{ AcademicRequestOptions::typeLabel($type) }}</a>
</p>

@include('partials.admin.shell-end')
