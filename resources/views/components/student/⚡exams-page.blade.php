<?php

use App\Models\Exam;
use App\Support\ExamOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('اختباراتي | مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function student()
    {
        return auth()->user()?->academicStudent;
    }

    #[Computed]
    public function exams()
    {
        if (! $this->student) {
            return collect();
        }

        return Exam::query()
            ->with(['section', 'course', 'accommodations' => fn ($query) => $query
                ->where('student_id', $this->student->id), 'attempts' => fn ($query) => $query
                ->where('student_id', $this->student->id)
                ->orderByDesc('attempt_number')])
            ->whereHas('candidates', fn ($query) => $query
                ->where('student_id', $this->student->id)
                ->where('status', 'eligible'))
            ->where(function ($query) {
                $query->where('status', 'published')
                    ->orWhere(function ($nested) {
                        $nested->where('status', 'closed')
                            ->whereHas('accommodations', fn ($accommodation) => $accommodation
                                ->where('student_id', $this->student->id)
                                ->where('override_exam_availability', true));
                    })
                    ->orWhere(function ($nested) {
                        $nested->whereIn('status', ['closed', 'archived'])
                            ->whereHas('attempts', fn ($attempt) => $attempt
                                ->where('student_id', $this->student->id));
                    });
            })
            ->orderByRaw('CASE WHEN closes_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('closes_at')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => $this->exams->count(),
            'available' => $this->exams->filter(fn ($exam) => $exam->isAvailableFor($this->student))->count(),
            'completed' => $this->exams->filter(
                fn ($exam) => $exam->attempts->contains(fn ($attempt) => in_array($attempt->status, ['graded', 'pending_grading'], true))
            )->count(),
        ];
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'exams', 'portalTitle' => 'اختباراتي'])

<div class="portal-dashboard portal-exams-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">اختباراتي</h1>
            <p class="portal-orders-intro__desc">تابع الاختبارات المتاحة، المحاولات والنتائج المنشورة.</p>
        </div>
    </div>

    <div class="portal-kpi-strip portal-kpi-strip--learning">
        <div class="portal-kpi-v2"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-list-check"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['total'] }}</span><span class="portal-kpi-v2__label">إجمالي</span></span></div>
        <div class="portal-kpi-v2"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-play"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['available'] }}</span><span class="portal-kpi-v2__label">متاح الآن</span></span></div>
        <div class="portal-kpi-v2"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-circle-check"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['completed'] }}</span><span class="portal-kpi-v2__label">مكتمل</span></span></div>
    </div>

    @if ($this->exams->isEmpty())
        <section class="portal-panel"><div class="portal-empty"><div class="portal-empty__icon"><i class="fa-solid fa-file-circle-question"></i></div><p>لا توجد اختبارات منشورة لشعبتك حالياً.</p></div></section>
    @else
        <div class="student-exam-list">
            @foreach ($this->exams as $exam)
                @php
                    $latest = $exam->attempts->first();
                    $selected = $exam->selectAttemptFrom($exam->attempts);
                    $available = $exam->isAvailableFor($this->student);
                    $attemptsUsed = $exam->attempts->count();
                    $attemptLimit = $exam->attemptLimitFor($this->student);
                    $displayLanguage = $latest?->language ?? $exam->resolveLanguage(null, auth()->user());
                @endphp
                <article @class(['student-exam-card', 'is-available' => $available]) wire:key="student-exam-{{ $exam->id }}">
                    <div class="student-exam-card__icon"><i class="fa-solid fa-file-circle-check"></i></div>
                    <div class="student-exam-card__body">
                        <div class="student-exam-card__labels">
                            <span>{{ ExamOptions::examTypes()[$exam->type] ?? $exam->type }}</span>
                            <span>{{ $exam->language_policy === 'student_choice' ? 'العربية / English' : ($displayLanguage === 'en' ? 'English' : 'العربية') }}</span>
                            @if ($latest)
                                <span class="is-status">{{ match($latest->status) { 'in_progress' => 'محاولة جارية', 'pending_grading' => 'بانتظار التصحيح', 'graded' => 'تم التصحيح', default => $latest->status } }}</span>
                            @endif
                        </div>
                        <h2>{{ $exam->localizedTitle($displayLanguage) }}</h2>
                        <p>{{ $exam->course?->name_ar }} · {{ $exam->section?->name }}</p>
                        <div class="student-exam-card__meta">
                            <span><i class="fa-regular fa-clock"></i> {{ $exam->duration_minutes ? $exam->duration_minutes.' دقيقة' : 'بدون حد زمني' }}</span>
                            <span><i class="fa-solid fa-star"></i> {{ $exam->total_points }} درجة</span>
                            <span><i class="fa-solid fa-rotate"></i> {{ $attemptsUsed }}/{{ $attemptLimit === null ? '∞' : $attemptLimit }} محاولة</span>
                            @if ($exam->closes_at)<span><i class="fa-regular fa-calendar-xmark"></i> يغلق {{ $exam->closes_at->translatedFormat('d M Y H:i') }}</span>@endif
                        </div>
                    </div>
                    <div class="student-exam-card__action">
                        @if ($selected && $exam->resultsAreVisibleFor($selected))
                            <strong title="{{ $exam->grade_selection === 'highest' ? 'أعلى درجة معتمدة' : 'درجة آخر محاولة معتمدة' }}">{{ $selected->percentage }}%</strong>
                        @endif
                        <a href="{{ route('exams.show', ['locale' => $locale, 'exam' => $exam->id]) }}" class="btn btn-primary btn-sm">{{ $latest?->status === 'in_progress' ? 'متابعة' : 'التفاصيل' }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
    .student-exam-list{display:flex;flex-direction:column;gap:.75rem}.student-exam-card{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:1rem;padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.student-exam-card.is-available{border-right:4px solid #16a34a}.student-exam-card__icon{display:grid;place-items:center;width:3rem;height:3rem;border-radius:12px;background:#ecfdf5;color:#166534;font-size:1.2rem}.student-exam-card__labels{display:flex;gap:.35rem}.student-exam-card__labels span{font-size:.65rem;color:#64748b}.student-exam-card__labels .is-status{padding:.18rem .4rem;border-radius:999px;background:#f1f5f9;color:#334155;font-weight:800}.student-exam-card h2{margin:.3rem 0 .2rem;font-size:1rem}.student-exam-card p{margin:0;color:#64748b;font-size:.75rem}.student-exam-card__meta{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:.55rem;color:#64748b;font-size:.68rem}.student-exam-card__action{display:flex;align-items:center;gap:.65rem}.student-exam-card__action strong{color:#166534}@media(max-width:700px){.student-exam-card{grid-template-columns:auto 1fr}.student-exam-card__action{grid-column:2;justify-content:flex-end}}
</style>
@endpush

@include('partials.portal.shell-end')
