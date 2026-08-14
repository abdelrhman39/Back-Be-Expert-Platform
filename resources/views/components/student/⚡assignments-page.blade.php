<?php

use App\Services\AssignmentService;
use App\Support\AssignmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('واجباتي | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function assignments()
    {
        $student = auth()->user()?->academicStudent;
        $service = app(AssignmentService::class);

        return $service->forStudent($student)->map(function ($assignment) use ($service, $student) {
            $submission = $student ? $service->latestSubmission($assignment, $student) : null;
            $assignment->my_submission = $submission;
            $assignment->is_overdue = $assignment->isOverdue();
            $assignment->can_submit = $assignment->acceptsSubmissions()
                && (! $submission || ! in_array($submission->status, ['submitted', 'late', 'graded'], true));

            return $assignment;
        });
    }

    #[Computed]
    public function stats(): array
    {
        $items = $this->assignments;

        return [
            'total' => $items->count(),
            'pending' => $items->filter(fn ($a) => ! $a->my_submission || $a->my_submission->status === 'draft')->count(),
            'graded' => $items->filter(fn ($a) => $a->my_submission?->isGraded())->count(),
        ];
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'assignments', 'portalTitle' => 'واجباتي'])

<div class="portal-dashboard portal-assignments-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">واجباتي</h1>
            <p class="portal-orders-intro__desc">تابع الواجبات المطلوبة وسلّم إجاباتك قبل الموعد النهائي.</p>
        </div>
    </div>

    @if ($this->assignments->isEmpty())
        <div class="portal-panel">
            <div class="portal-empty">
                <div class="portal-empty__icon"><i class="fa-solid fa-file-pen"></i></div>
                <p>لا توجد واجبات منشورة حالياً</p>
            </div>
        </div>
    @else
        <div class="portal-kpi-strip portal-kpi-strip--learning">
            <div class="portal-kpi-v2 portal-kpi-v2--orders">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-list-check"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['total'] }}</span>
                    <span class="portal-kpi-v2__label">إجمالي</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--cart">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-clock"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['pending'] }}</span>
                    <span class="portal-kpi-v2__label">بانتظار التسليم</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--cert">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-star"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['graded'] }}</span>
                    <span class="portal-kpi-v2__label">مُقيَّمة</span>
                </span>
            </div>
        </div>

        <div class="portal-assignment-list">
            @foreach ($this->assignments as $assignment)
                @php
                    $sub = $assignment->my_submission;
                    $statusLabel = $sub
                        ? AssignmentOptions::submissionStatusLabel($sub->status)
                        : 'لم يُسلَّم';
                    $statusClass = $sub
                        ? match ($sub->status) {
                            'graded' => 'portal-assignment-card--graded',
                            'submitted', 'late' => 'portal-assignment-card--submitted',
                            default => 'portal-assignment-card--pending',
                        }
                        : ($assignment->is_overdue ? 'portal-assignment-card--overdue' : 'portal-assignment-card--pending');
                @endphp
                <article @class(['portal-assignment-card', $statusClass]) wire:key="assign-{{ $assignment->id }}">
                    <div class="portal-assignment-card__head">
                        <span class="portal-assignment-card__status">{{ $statusLabel }}</span>
                        @if ($assignment->due_at)
                            <time class="portal-assignment-card__due" dir="ltr">
                                {{ $assignment->due_at->translatedFormat('d M Y — H:i') }}
                            </time>
                        @endif
                    </div>
                    <h2 class="portal-assignment-card__title">{{ $assignment->title }}</h2>
                    @if ($assignment->session)
                        <p class="portal-assignment-card__meta"><i class="fa-solid fa-chalkboard"></i> {{ $assignment->session->displayTitle() }}</p>
                    @endif
                    @if ($sub?->isGraded())
                        <p class="portal-assignment-card__score">الدرجة: <strong>{{ $sub->finalScore() }}</strong> / {{ $assignment->max_score }}</p>
                    @endif
                    <div class="portal-assignment-card__actions">
                        <a href="{{ route('assignments.show', ['locale' => $locale, 'assignment' => $assignment->id]) }}" class="btn btn-primary btn-sm">
                            {{ $assignment->can_submit ? 'تسليم الواجب' : 'عرض التفاصيل' }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    .portal-assignment-list { display: flex; flex-direction: column; gap: 0.85rem; }
    .portal-assignment-card {
        background: #fff; border: 1px solid var(--sa-border); border-radius: var(--portal-radius);
        padding: 1rem 1.15rem; box-shadow: var(--portal-shadow);
    }
    .portal-assignment-card--overdue { border-color: rgba(220, 38, 38, 0.3); }
    .portal-assignment-card--graded { border-color: rgba(22, 93, 49, 0.25); background: linear-gradient(135deg,#fff,#f6fbf8); }
    .portal-assignment-card__head { display: flex; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.35rem; flex-wrap: wrap; }
    .portal-assignment-card__status { font-size: 0.72rem; font-weight: 700; color: var(--sa-muted); }
    .portal-assignment-card__due { font-size: 0.75rem; color: var(--sa-muted); }
    .portal-assignment-card__title { margin: 0 0 0.35rem; font-size: 1.05rem; font-weight: 800; }
    .portal-assignment-card__meta { margin: 0 0 0.5rem; font-size: 0.8rem; color: var(--sa-muted); }
    .portal-assignment-card__score { margin: 0 0 0.65rem; font-size: 0.88rem; }
</style>
@endpush
