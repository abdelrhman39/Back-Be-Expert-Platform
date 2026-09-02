<?php

use App\Models\AcademicRequest;
use App\Support\AcademicRequestOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تفاصيل الطلب | مركز التعلم المستمر')]
class extends Component
{
    public AcademicRequest $academicRequest;

    public function mount(AcademicRequest $academicRequest): void
    {
        abort_unless($academicRequest->belongsToUser(auth()->user()), 403);

        $this->academicRequest = $academicRequest;
    }
};
?>

@php
    $locale = app()->getLocale();
    $request = $academicRequest;
    $type = $request->type;
@endphp

@include('partials.portal.shell-start', [
    'portalActive' => 'user-requests',
    'portalTitle' => 'تفاصيل الطلب',
])

<div class="portal-dashboard portal-user-request-view-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ AcademicRequestOptions::studentSingularLabel($type) }}</h1>
            <p class="portal-orders-intro__desc" dir="ltr">#{{ $request->request_no }}</p>
        </div>
        <span class="portal-badge {{ AcademicRequestOptions::statusBadgeClass($request->status) }}">{{ $request->statusLabel() }}</span>
    </div>

    <div class="portal-ur-detail-card">
        <dl class="portal-ur-detail-list">
            <div class="portal-ur-detail-row">
                <dt>تاريخ التقديم</dt>
                <dd>{{ $request->submitted_at?->translatedFormat('d M Y · H:i') ?? '—' }}</dd>
            </div>
            <div class="portal-ur-detail-row">
                <dt>البرنامج</dt>
                <dd>{{ $request->program_name ?? '—' }}</dd>
            </div>

            @if ($type === 'program_change')
                <div class="portal-ur-detail-row">
                    <dt>البرنامج الحالي</dt>
                    <dd>{{ $request->payloadValue('current_program_full', $request->program_name) }}</dd>
                </div>
                <div class="portal-ur-detail-row">
                    <dt>البرنامج المطلوب</dt>
                    <dd>{{ $request->payloadValue('new_program_full', $request->payloadValue('new_program')) }}</dd>
                </div>
            @elseif ($type === 'withdrawal')
                <div class="portal-ur-detail-row">
                    <dt>طريقة الدفع</dt>
                    <dd>{{ $request->payloadValue('payment_method', '—') }}</dd>
                </div>
            @elseif ($type === 'semester_excuse')
                <div class="portal-ur-detail-row">
                    <dt>الفصل الدراسي</dt>
                    <dd>{{ $request->semester_label ?? '—' }}</dd>
                </div>
            @else
                @if ($request->semester_label)
                    <div class="portal-ur-detail-row">
                        <dt>فصل القبول</dt>
                        <dd>{{ $request->semester_label }}</dd>
                    </div>
                @endif
                @if ($request->payloadValue('target_semester'))
                    <div class="portal-ur-detail-row">
                        <dt>التأجيل إلى</dt>
                        <dd>{{ $request->payloadValue('target_semester') }}</dd>
                    </div>
                @endif
            @endif

            @if ($request->reason)
                <div class="portal-ur-detail-row">
                    <dt>السبب</dt>
                    <dd>{{ $request->reason }}</dd>
                </div>
            @endif

            @if ($request->admin_notes)
                <div class="portal-ur-detail-row portal-ur-detail-row--highlight">
                    <dt>ملاحظة الإدارة</dt>
                    <dd>{{ $request->admin_notes }}</dd>
                </div>
            @endif

            @if ($request->reviewed_at)
                <div class="portal-ur-detail-row">
                    <dt>آخر تحديث</dt>
                    <dd>{{ $request->reviewed_at->translatedFormat('d M Y · H:i') }}</dd>
                </div>
            @endif
        </dl>
    </div>

    <a href="{{ route('user-requests', ['locale' => $locale]) }}" class="portal-btn-secondary">← العودة لطلباتي</a>
</div>

@include('partials.portal.shell-end')
