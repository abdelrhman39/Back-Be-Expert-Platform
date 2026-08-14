<?php

use App\Models\RegistrationApplication;
use App\Services\RegistrationApplicationService;
use App\Support\RegistrationApplicationOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('عرض طلب التسجيل | لوحة التحكم')]
class extends Component
{
    public RegistrationApplication $application;

    public string $adminNotes = '';

    public function mount(RegistrationApplication $application): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.view'), 403);

        $this->application = $application->load(['user.academicStaff', 'reviewer', 'fellowship']);
        $this->adminNotes = $application->admin_notes ?? '';
    }

    public function markUnderReview(RegistrationApplicationService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        $service->markUnderReview($this->application, auth()->user());
        $this->application->refresh()->load(['user.academicStaff', 'reviewer']);
        session()->flash('admin_message', 'تم تحديث الحالة إلى «قيد المراجعة».');
    }

    public function approve(RegistrationApplicationService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        if (! $this->application->canReview()) {
            return;
        }

        $service->approve($this->application, auth()->user(), $this->adminNotes ?: null);
        $this->application->refresh()->load(['user.academicStaff', 'reviewer']);

        $message = 'تم قبول الطلب.';

        if ($this->application->type === 'instructor') {
            $message = 'تم قبول طلب المدرب وإنشاء/تحديث حساب بوابة المدرب. إن كان الحساب جديداً فقد أُرسل رابط تعيين كلمة المرور إلى بريده.';
        }

        session()->flash('admin_message', $message);
    }

    public function reject(RegistrationApplicationService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        if (! $this->application->canReview()) {
            return;
        }

        $service->reject($this->application, auth()->user(), $this->adminNotes ?: null);
        $this->application->refresh()->load(['user.academicStaff', 'reviewer']);
        session()->flash('admin_message', 'تم رفض الطلب.');
    }
};
?>

@php
    use App\Services\FellowshipFormService;

    $request = $application;
    $type = $request->type;
    $isInstructor = $type === 'instructor';
    $canReview = auth()->user()?->canAdmin('applications.review') && $request->canReview();
    $fields = $request->fellowship
        ? app(FellowshipFormService::class)->resolveFields($request->fellowship)
        : RegistrationApplicationOptions::fieldsFor($type);
    $staff = $request->user?->academicStaff;
    $sections = RegistrationApplicationOptions::types()[$type]['sections'] ?? null;
    $groupedFields = $sections
        ? collect($fields)->groupBy(fn ($field) => $field['section'] ?? 'default')
        : collect(['default' => $fields]);
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route(RegistrationApplicationOptions::types()[$type]['route']),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => $request->listRoute(), 'label' => RegistrationApplicationOptions::typeLabel($type)],
        ['label' => $request->application_no],
    ],
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-applications.css') }}?v=1">
@endpush

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="apps-detail-hero {{ $isInstructor ? 'apps-detail-hero--instructor' : '' }}">
    <div>
        <span class="apps-detail-hero__eyebrow">{{ RegistrationApplicationOptions::typeLabel($type) }}</span>
        <h1>{{ $request->applicant_name }}</h1>
        <p dir="ltr">{{ $request->applicant_email }}@if ($request->applicant_phone) · {{ $request->applicant_phone }}@endif</p>
        @if ($isInstructor && $request->payloadValue('specialization'))
            <p class="apps-detail-hero__specialty">{{ $request->payloadValue('specialization') }}
                @if ($request->payloadValue('job_title'))
                    <span>· {{ $request->payloadValue('job_title') }}</span>
                @endif
            </p>
        @endif
    </div>
    <div class="apps-detail-hero__meta">
        <span class="admin-badge admin-badge--{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
            {{ $request->statusLabel() }}
        </span>
        <code dir="ltr">{{ $request->application_no }}</code>
    </div>
</section>

@if ($isInstructor && $canReview)
    <div class="apps-detail-callout">
        <strong>عند القبول</strong>
        <span>يُنشأ حساب مدرب مرتبط ببوابة المدربين، ويُضاف إلى الكادر الأكاديمي بحزمة صلاحيات «مدرب — أساسي». إن لم يكن للمتقدّم حساب سابق يُرسل رابط تعيين كلمة المرور إلى بريده.</span>
    </div>
@endif

@if ($isInstructor && $request->status === 'approved' && $staff)
    <div class="apps-detail-callout apps-detail-callout--success">
        <strong>تم ربط المدرب بالكوادر</strong>
        <span>{{ $staff->name_ar }} — {{ $staff->specialty ?: 'بدون تخصص مسجّل' }}</span>
        @canAdmin('staff.manage')
            <a href="{{ route('admin.staff.edit', ['staff' => $staff->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">فتح ملف الكادر</a>
        @endcanAdmin
    </div>
@endif

<div class="admin-crud-card admin-request-detail-card">
    <div class="admin-request-detail-card__head">
        <h2>تفاصيل الطلب</h2>
        <a href="{{ $request->listRoute() }}" class="admin-btn-secondary admin-btn-secondary--sm">عودة للقائمة</a>
    </div>

    <div class="admin-request-detail-card__body">
        <dl class="admin-request-detail-list">
            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">رقم الطلب</dt>
                <dd class="admin-request-detail-row__value" dir="ltr"><code>{{ $request->application_no }}</code></dd>
            </div>
            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">تاريخ التقديم</dt>
                <dd class="admin-request-detail-row__value">
                    {{ $request->submitted_at?->format('Y-m-d H:i:s') ?? '—' }}
                    @if ($request->submitted_at)
                        <span class="admin-request-detail-foot__ago">— {{ $request->submitted_at->diffForHumans() }}</span>
                    @endif
                </dd>
            </div>
            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">المتقدّم</dt>
                <dd class="admin-request-detail-row__value">{{ $request->applicant_name }}</dd>
            </div>
            <div class="admin-request-detail-row">
                <dt class="admin-request-detail-row__label">البريد</dt>
                <dd class="admin-request-detail-row__value" dir="ltr">{{ $request->applicant_email }}</dd>
            </div>
            @if ($request->applicant_phone)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">الجوال</dt>
                    <dd class="admin-request-detail-row__value" dir="ltr">{{ $request->applicant_phone }}</dd>
                </div>
            @endif
            @if ($request->course_name)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">البرنامج المطلوب</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->course_name }}</dd>
                </div>
            @endif
            @if ($request->fellowship)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">برنامج الزمالة</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->fellowship->title_ar }}</dd>
                </div>
            @endif
            @if ($request->approved_role)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">الدور عند القبول</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->approved_role === 'instructor' ? 'مدرب' : 'طالب' }}</dd>
                </div>
            @endif
            @if ($request->reviewer)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">المراجع</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->reviewer->displayName() }}</dd>
                </div>
            @endif
            @if ($request->user)
                <div class="admin-request-detail-row">
                    <dt class="admin-request-detail-row__label">حساب المنصة</dt>
                    <dd class="admin-request-detail-row__value">{{ $request->user->displayName() }} ({{ $request->user->email }})</dd>
                </div>
            @endif
        </dl>

        @if ($request->payload)
            @if ($sections)
                @foreach ($sections as $sectionKey => $sectionTitle)
                    @php $sectionFields = $groupedFields->get($sectionKey, collect()); @endphp
                    @if ($sectionFields->isNotEmpty())
                        <h3 class="admin-form-section__title mt-4">{{ $sectionTitle }}</h3>
                        <dl class="admin-request-detail-list">
                            @foreach ($sectionFields as $field)
                                @if (($field['type'] ?? '') === 'file')
                                    @continue
                                @endif
                                @php $val = $request->payloadValue($field['key']); @endphp
                                @if (filled($val))
                                    <div class="admin-request-detail-row">
                                        <dt class="admin-request-detail-row__label">{{ $field['label'] }}</dt>
                                        <dd class="admin-request-detail-row__value">
                                            @if (isset($field['options'][$val]))
                                                {{ $field['options'][$val] }}
                                            @else
                                                {{ is_array($val) ? implode(', ', $val) : $val }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif
                @endforeach
            @else
                <h3 class="admin-form-section__title mt-4">بيانات النموذج</h3>
                <dl class="admin-request-detail-list">
                    @foreach ($fields as $field)
                        @if (($field['type'] ?? '') === 'file')
                            @continue
                        @endif
                        @php $val = $request->payloadValue($field['key']); @endphp
                        @if (filled($val))
                            <div class="admin-request-detail-row">
                                <dt class="admin-request-detail-row__label">{{ $field['label'] }}</dt>
                                <dd class="admin-request-detail-row__value">
                                    @if (isset($field['options'][$val]))
                                        {{ $field['options'][$val] }}
                                    @else
                                        {{ is_array($val) ? implode(', ', $val) : $val }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            @endif
        @endif

        @if ($request->attachments)
            <h3 class="admin-form-section__title mt-4">المرفقات</h3>
            <ul class="admin-request-attachments">
                @foreach ($request->attachments as $key => $meta)
                    @php
                        $fieldLabel = collect($fields)->firstWhere('key', $key)['label'] ?? $key;
                    @endphp
                    <li>
                        <span class="apps-attachment-label">{{ $fieldLabel }}</span>
                        <a href="{{ route('admin.applications.attachment', ['application' => $request, 'key' => $key]) }}" target="_blank">
                            {{ $meta['original'] ?? $key }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($canReview)
            <div class="admin-field mt-4">
                <label>ملاحظات الإدارة</label>
                <textarea class="admin-control" rows="3" wire:model="adminNotes" placeholder="ملاحظات اختيارية تظهر في سجل المراجعة…"></textarea>
            </div>

            <div class="admin-request-detail-actions">
                <button type="button" class="admin-btn-secondary" wire:click="markUnderReview">قيد المراجعة</button>
                <button type="button" class="admin-btn-primary" wire:click="approve" wire:confirm="{{ $isInstructor ? 'قبول الطلب وإنشاء/تحديث حساب المدرب؟' : 'تأكيد قبول الطلب؟' }}">
                    {{ $isInstructor ? 'قبول وإنشاء حساب مدرب' : 'قبول' }}
                </button>
                <button type="button" class="admin-btn-danger" wire:click="reject" wire:confirm="تأكيد رفض الطلب؟">رفض</button>
            </div>
        @elseif ($request->admin_notes)
            <div class="admin-field mt-4">
                <label>ملاحظات الإدارة</label>
                <p class="admin-control-static">{{ $request->admin_notes }}</p>
            </div>
        @endif
    </div>
</div>

@include('partials.admin.shell-end')
