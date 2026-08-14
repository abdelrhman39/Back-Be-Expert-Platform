<?php

use App\Services\InstructorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تصحيح الواجبات | لوحة المدرب')]
class extends Component
{
    public function mount(InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.assignments.grade');
    }

    #[Computed]
    public function submissions()
    {
        return app(InstructorService::class)->pendingAssignmentSubmissionsFor(auth()->user(), 100);
    }

    #[Computed]
    public function examAttempts()
    {
        return app(InstructorService::class)->pendingExamAttemptsFor(auth()->user(), 50);
    }
};
?>

@php
    $locale = app()->getLocale();
    $assignCount = $this->submissions->count();
    $examCount = $this->examAttempts->count();
@endphp

@include('partials.instructor.shell-start', ['instructorActive' => 'assignments', 'instructorTitle' => 'تصحيح الواجبات'])

<div class="portal-dashboard portal-dashboard--instructor">
    @include('partials.instructor.page-hero', [
        'title' => 'صندوق التصحيح',
        'desc' => 'راجع تسليمات الواجبات ومحاولات الاختبار التي تحتاج تصحيحاً يدوياً.',
        'icon' => 'fa-clipboard-check',
        'stats' => [
            ['value' => $assignCount, 'label' => 'واجبات'],
            ['value' => $examCount, 'label' => 'اختبارات'],
            ['value' => $assignCount + $examCount, 'label' => 'الإجمالي'],
        ],
        'actions' => [
            ['href' => route('instructor.exams', ['locale' => $locale]), 'label' => 'صفحة الاختبارات', 'icon' => 'fa-file-circle-check', 'class' => 'btn-outline-primary'],
            ['href' => route('instructor.dashboard', ['locale' => $locale]), 'label' => 'العودة للوحة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ],
    ])

    <div class="portal-kpi-strip portal-kpi-strip--2">
        <div class="portal-kpi-v2 portal-kpi-v2--grades">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-clipboard-list"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $assignCount }}</span>
                <span class="portal-kpi-v2__label">واجبات بانتظار التصحيح</span>
            </span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--exams">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-file-pen"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $examCount }}</span>
                <span class="portal-kpi-v2__label">اختبارات بانتظار التصحيح</span>
            </span>
        </div>
    </div>

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-clipboard-list"></i> تسليمات الواجبات</h2>
            <span class="portal-panel__meta">{{ $assignCount }}</span>
        </div>
        <div class="portal-panel__body">
            @if ($this->submissions->isEmpty())
                <div class="portal-empty portal-empty--compact">
                    <div class="portal-empty__icon"><i class="fa-solid fa-circle-check"></i></div>
                    <p>لا توجد واجبات بانتظار التصحيح</p>
                </div>
            @else
                <div class="portal-order-list">
                    @foreach ($this->submissions as $submission)
                        @php
                            $assignment = $submission->assignment;
                            $href = $assignment?->attendance_session_id
                                ? route('instructor.sessions.show', ['locale' => $locale, 'section' => $assignment->section_id, 'session' => $assignment->attendance_session_id])
                                : route('instructor.sections.show', ['locale' => $locale, 'section' => $assignment->section_id]);
                        @endphp
                        <a href="{{ $href }}" class="portal-order-item" wire:key="sub-{{ $submission->id }}">
                            <span class="portal-order-item__icon"><i class="fa-solid fa-file-lines"></i></span>
                            <span class="portal-order-item__main">
                                <span class="portal-order-item__ref">{{ $submission->student?->name_ar ?: 'طالب' }}</span>
                                <span class="portal-order-item__date">{{ $assignment?->title }} · {{ $assignment?->section?->name }}</span>
                            </span>
                            <span class="portal-inst-badge {{ $submission->status === 'late' ? 'portal-inst-badge--att-late' : 'portal-inst-badge--upcoming' }}">
                                {{ $submission->status === 'late' ? 'متأخر' : 'مُسلَّم' }}
                            </span>
                            <span class="portal-order-item__amount">{{ $submission->submitted_at?->format('Y/m/d') ?: '—' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-file-pen"></i> محاولات اختبار للتصحيح اليدوي</h2>
            <span class="portal-panel__meta">{{ $examCount }}</span>
        </div>
        <div class="portal-panel__body">
            @if ($this->examAttempts->isEmpty())
                <div class="portal-empty portal-empty--compact">
                    <div class="portal-empty__icon"><i class="fa-solid fa-circle-check"></i></div>
                    <p>لا توجد اختبارات بانتظار التصحيح اليدوي</p>
                </div>
            @else
                <div class="portal-order-list">
                    @foreach ($this->examAttempts as $attempt)
                        <a href="{{ route('instructor.exams.grading', ['locale' => $locale, 'section' => $attempt->exam->section_id, 'exam' => $attempt->exam_id]) }}" class="portal-order-item" wire:key="ex-{{ $attempt->id }}">
                            <span class="portal-order-item__icon"><i class="fa-solid fa-file-circle-question"></i></span>
                            <span class="portal-order-item__main">
                                <span class="portal-order-item__ref">{{ $attempt->student?->name_ar ?: 'طالب' }}</span>
                                <span class="portal-order-item__date">{{ $attempt->exam?->title }} · {{ $attempt->exam?->section?->name }}</span>
                            </span>
                            <span class="portal-inst-badge portal-inst-badge--att-late">يحتاج تصحيحاً</span>
                            <span class="portal-order-item__amount">{{ $attempt->submitted_at?->format('Y/m/d') ?: '—' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>

@push('styles')
<style>
.portal-hero--page .portal-hero__banner--compact{min-height:auto}
.portal-hero--page .portal-hero__eyebrow{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.78);margin-bottom:.35rem}
.portal-hero--page .portal-hero__body--compact{margin-top:-1.1rem;padding-top:0;padding-bottom:1rem}
.portal-hero--page .portal-hero__actions--start{justify-content:flex-start}
.portal-panel__meta{font-size:.78rem;font-weight:800;color:#0f766e;background:#ecfdf5;padding:.3rem .7rem;border-radius:999px}
.portal-kpi-strip--2{grid-template-columns:repeat(2,minmax(0,1fr))}
.portal-dashboard--instructor .portal-kpi-v2--grades{border-right-color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--exams{border-right-color:#7c3aed}
.portal-dashboard--instructor .portal-kpi-v2--grades .portal-kpi-v2__icon{background:#fffbeb;color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--exams .portal-kpi-v2__icon{background:#f5f3ff;color:#7c3aed}
@media(max-width:575.98px){.portal-kpi-strip--2{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.instructor.shell-end')
