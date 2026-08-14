<?php

use App\Models\AcademicSection;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Services\ExamGradingService;
use App\Services\InstructorService;
use App\Support\ExamAnswerPresenter;
use App\Support\ExamOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تصحيح الاختبار | لوحة المدرب')]
class extends Component
{
    public AcademicSection $section;
    public Exam $exam;
    public array $scores = [];
    public array $feedback = [];
    public string $flashMessage = '';
    public ?int $openAttemptId = null;

    public function mount(AcademicSection $section, Exam $exam, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $section);
        $instructors->authorizePermission(auth()->user(), 'instructor.exam_attempts.view');
        abort_unless($exam->section_id === $section->id, 404);

        $this->section = $section->load(['course']);
        $this->exam = $exam;
        $this->initializeGradeFields();

        $firstPending = $this->attempts->firstWhere('status', 'pending_grading')
            ?? $this->attempts->first();
        $this->openAttemptId = $firstPending?->id;
    }

    #[Computed]
    public function attempts()
    {
        return $this->exam->attempts()
            ->with(['student', 'answers' => fn ($query) => $query->orderBy('id')])
            ->whereIn('status', ['submitted', 'pending_grading', 'graded'])
            ->orderByDesc('submitted_at')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $selectedAttempts = $this->exam->selectedAttemptsFrom($this->attempts);

        return [
            'attempts' => $this->attempts->count(),
            'pending' => $this->attempts->where('status', 'pending_grading')->count(),
            'graded' => $this->attempts->where('status', 'graded')->count(),
            'average' => round((float) $selectedAttempts->avg('percentage'), 1),
            'passed' => $selectedAttempts->where('passed', true)->count(),
        ];
    }

    public function openAttempt(int $attemptId): void
    {
        $this->openAttemptId = $this->openAttemptId === $attemptId ? null : $attemptId;
    }

    public function gradeAnswer(int $answerId, InstructorService $instructors, ExamGradingService $grading): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.exam_attempts.grade');

        $answer = ExamAnswer::query()
            ->whereKey($answerId)
            ->whereHas('attempt', fn ($query) => $query->where('exam_id', $this->exam->id))
            ->firstOrFail();
        $max = (float) ($answer->question_snapshot['points'] ?? 0);

        $this->validate([
            "scores.{$answerId}" => ['required', 'numeric', 'min:0', 'max:'.$max],
            "feedback.{$answerId}" => ['nullable', 'string', 'max:10000'],
        ], [], [
            "scores.{$answerId}" => 'الدرجة',
            "feedback.{$answerId}" => 'التغذية الراجعة',
        ]);

        $grading->gradeAnswer(
            $answer,
            (float) $this->scores[$answerId],
            $this->feedback[$answerId] ?: null,
            auth()->user(),
        );

        unset($this->attempts, $this->stats);
        $this->flashMessage = 'تم حفظ التصحيح وإعادة احتساب نتيجة المحاولة.';
    }

    public function releaseResults(InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.exams.publish');

        $settings = $this->exam->settings ?? [];
        $settings['results_released'] = true;
        $this->exam->update(['settings' => $settings]);
        $this->exam->refresh();
        app(\App\Services\NotificationService::class)->notifyExamResultsReleased($this->exam);
        app(\App\Services\AuditLogService::class)->log(
            action: 'exam.results_released',
            descriptionAr: 'اعتماد نتائج اختبار «'.$this->exam->title.'»',
            group: 'exams',
            actor: auth()->user(),
            subject: $this->exam,
            subjectLabel: $this->exam->title,
        );
        $this->flashMessage = 'تم اعتماد نشر النتائج للطلاب.';
    }

    private function initializeGradeFields(): void
    {
        $answers = ExamAnswer::query()
            ->whereHas('attempt', fn ($query) => $query->where('exam_id', $this->exam->id))
            ->get();

        foreach ($answers as $answer) {
            $this->scores[$answer->id] = $answer->manual_score !== null ? (string) $answer->manual_score : '';
            $this->feedback[$answer->id] = $answer->grader_feedback ?? '';
        }
    }
};
?>

@php
    $locale = app()->getLocale();
    $selectedAttemptIds = $exam->selectedAttemptsFrom($this->attempts)->pluck('id');
    $breadcrumb = [
        ['href' => route('instructor.exams', ['locale' => $locale]), 'label' => 'الاختبارات'],
        ['label' => 'التصحيح'],
    ];
@endphp

@include('partials.instructor.shell-start', [
    'instructorActive' => 'exams',
    'instructorTitle' => 'تصحيح: '.$exam->title,
    'instructorBreadcrumb' => $breadcrumb,
])

<div class="portal-dashboard portal-dashboard--instructor exam-grading-page">
    @include('partials.instructor.page-hero', [
        'title' => $exam->title,
        'desc' => ($section->name).' — مراجعة المحاولات، عرض الاختيارات، وتصحيح الأسئلة اليدوية.',
        'icon' => 'fa-pen-to-square',
        'stats' => [
            ['value' => $this->stats['attempts'], 'label' => 'محاولة'],
            ['value' => $this->stats['pending'], 'label' => 'بانتظار'],
            ['value' => $this->stats['average'].'%', 'label' => 'المتوسط'],
        ],
        'actions' => [
            ['href' => route('instructor.exams.builder', ['locale' => $locale, 'section' => $section->id, 'exam' => $exam->id]), 'label' => 'بناء الأسئلة', 'icon' => 'fa-list-check', 'class' => 'btn-outline-primary'],
            ['href' => route('instructor.exams', ['locale' => $locale]), 'label' => 'العودة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ],
    ])

    @if ($exam->result_release === 'manual' && ! ($exam->settings['results_released'] ?? false))
        <div class="portal-pending-banner">
            <div class="portal-pending-banner__icon"><i class="fa-solid fa-bullhorn"></i></div>
            <div class="portal-pending-banner__text">
                <strong>النتائج لم تُعتمد بعد</strong>
                <span>بعد إنهاء التصحيح يمكنك نشر النتائج للطلاب.</span>
            </div>
            <button type="button" wire:click="releaseResults" wire:confirm="نشر النتائج المصححة للطلاب الآن؟" class="btn btn-sm btn-warning">اعتماد النتائج</button>
        </div>
    @endif

    <div class="portal-kpi-strip portal-kpi-strip--4">
        <div class="portal-kpi-v2 portal-kpi-v2--students"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-users"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['attempts'] }}</span><span class="portal-kpi-v2__label">محاولة</span></span></div>
        <div class="portal-kpi-v2 portal-kpi-v2--grades"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-hourglass-half"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['pending'] }}</span><span class="portal-kpi-v2__label">بانتظار التصحيح</span></span></div>
        <div class="portal-kpi-v2 portal-kpi-v2--week"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-chart-line"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['average'] }}%</span><span class="portal-kpi-v2__label">متوسط النتائج</span></span></div>
        <div class="portal-kpi-v2 portal-kpi-v2--sections"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-circle-check"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['passed'] }}</span><span class="portal-kpi-v2__label">اجتازوا</span></span></div>
    </div>

    @if ($flashMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact"><div class="portal-alert__content">{{ $flashMessage }}</div></div>
    @endif

    @forelse ($this->attempts as $attempt)
        @php $isOpen = $openAttemptId === $attempt->id; @endphp
        <section class="portal-panel exam-grade-attempt" wire:key="grade-attempt-{{ $attempt->id }}">
            <button type="button" class="exam-grade-attempt__toggle" wire:click="openAttempt({{ $attempt->id }})">
                <div class="exam-grade-attempt__identity">
                    <span class="exam-grade-attempt__avatar">{{ mb_substr($attempt->student?->name_ar ?: 'ط', 0, 1) }}</span>
                    <div>
                        <span class="exam-grade-attempt__meta">
                            المحاولة {{ $attempt->attempt_number }}
                            @if ($selectedAttemptIds->contains($attempt->id)) · النتيجة المعتمدة @endif
                        </span>
                        <h2>{{ $attempt->student?->name_ar }}</h2>
                        <p>{{ $attempt->student?->academic_id ?: '—' }} · سُلّمت {{ $attempt->submitted_at?->translatedFormat('d M Y H:i') }}</p>
                    </div>
                </div>
                <div class="exam-grade-attempt__result">
                    <span class="exam-grade-status exam-grade-status--{{ $attempt->status }}">
                        {{ $attempt->status === 'graded' ? 'تم التصحيح' : 'بانتظار التصحيح' }}
                    </span>
                    @if ($attempt->status === 'graded')
                        <strong>{{ $attempt->total_score }}/{{ $attempt->effectiveTotalPoints() }} · {{ $attempt->percentage }}%</strong>
                    @else
                        <strong>{{ $attempt->auto_score }} درجة تلقائية</strong>
                    @endif
                    <i class="fa-solid fa-chevron-{{ $isOpen ? 'up' : 'down' }}"></i>
                </div>
            </button>

            @if ($isOpen)
                <div class="exam-grade-answers">
                    @foreach ($attempt->answers as $index => $answer)
                        @php
                            $snapshot = $answer->question_snapshot ?? [];
                            $type = $snapshot['type'] ?? null;
                            $manual = in_array($type, ['essay', 'file_upload'], true);
                            $presented = ExamAnswerPresenter::present($answer);
                            $isCorrect = $answer->is_correct;
                            if ($isCorrect === null) {
                                $isCorrect = $presented['is_correct'];
                            }
                        @endphp
                        <article @class([
                            'exam-grade-answer',
                            'needs-grading' => $manual && $answer->manual_score === null,
                            'is-correct' => $isCorrect === true,
                            'is-wrong' => $isCorrect === false,
                        ]) wire:key="grade-answer-{{ $answer->id }}">
                            <header class="exam-grade-answer__head">
                                <div>
                                    <span class="exam-grade-answer__number">سؤال {{ $index + 1 }}</span>
                                    <span class="exam-grade-answer__type">{{ ExamOptions::questionTypeLabel($type) }} · {{ $snapshot['points'] ?? 0 }} درجة</span>
                                </div>
                                <div class="exam-grade-answer__badges">
                                    @if ($isCorrect === true)
                                        <span class="exam-review-status is-correct"><i class="fa-solid fa-circle-check"></i> صحيحة</span>
                                    @elseif ($isCorrect === false)
                                        <span class="exam-review-status is-wrong"><i class="fa-solid fa-circle-xmark"></i> خاطئة</span>
                                    @elseif ($manual)
                                        <span class="exam-review-status is-manual"><i class="fa-solid fa-user-pen"></i> تصحيح يدوي</span>
                                    @endif
                                    <strong>{{ $answer->effectiveScore() }} / {{ $snapshot['points'] ?? 0 }}</strong>
                                </div>
                            </header>

                            <h3 class="exam-grade-answer__prompt">{{ $snapshot['prompt'] ?? 'سؤال' }}</h3>

                            @if ($presented['is_choice'] && $presented['options'] !== [])
                                <div class="exam-grade-options">
                                    @foreach ($presented['options'] as $option)
                                        <div @class([
                                            'exam-grade-option',
                                            'is-selected' => $option['selected'],
                                            'is-correct-answer' => $option['correct'],
                                            'is-wrong-answer' => $option['selected'] && ! $option['correct'],
                                        ])>
                                            <i class="fa-regular {{ $option['selected'] ? 'fa-circle-dot' : 'fa-circle' }}"></i>
                                            <span class="exam-grade-option__key" dir="ltr">{{ $option['key'] }}</span>
                                            <span class="exam-grade-option__text">{{ $option['content'] }}</span>
                                            @if ($option['selected'])
                                                <small>إجابة الطالب</small>
                                            @endif
                                            @if ($option['correct'])
                                                <small class="is-correct-tag">الإجابة الصحيحة</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($type === 'file_upload')
                                <div class="exam-grade-file">
                                    <i class="fa-solid fa-paperclip"></i>
                                    @if ($answer->file_path)
                                        <a href="{{ route('instructor.exam-answers.file', ['locale' => $locale, 'answer' => $answer->id]) }}">{{ $answer->file_original_name ?: 'تحميل الملف' }}</a>
                                    @else
                                        <span>لم يرفع ملفاً</span>
                                    @endif
                                </div>
                            @else
                                <div class="exam-grade-text-answers">
                                    <div class="exam-grade-text-box">
                                        <span>إجابة الطالب</span>
                                        <p>{{ $presented['student_label'] !== '—' ? $presented['student_label'] : 'لم يُجب' }}</p>
                                    </div>
                                    @if ($presented['correct_label'] !== '—' && ! $manual)
                                        <div class="exam-grade-text-box is-model">
                                            <span>الإجابة الصحيحة / النموذجية</span>
                                            <p>{{ $presented['correct_label'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if (! empty($snapshot['explanation']))
                                <div class="exam-grade-explanation">
                                    <strong>شرح الإجابة</strong>
                                    <p>{{ $snapshot['explanation'] }}</p>
                                </div>
                            @endif

                            @if ($manual)
                                <form wire:submit="gradeAnswer({{ $answer->id }})" class="exam-grade-form">
                                    <label>
                                        <span>الدرجة / {{ $snapshot['points'] }}</span>
                                        <input type="number" min="0" max="{{ $snapshot['points'] }}" step=".01" wire:model="scores.{{ $answer->id }}">
                                        @error("scores.{$answer->id}")<small>{{ $message }}</small>@enderror
                                    </label>
                                    <label class="is-wide">
                                        <span>التغذية الراجعة للطالب</span>
                                        <textarea rows="2" wire:model="feedback.{{ $answer->id }}" placeholder="ملاحظة اختيارية للطالب"></textarea>
                                    </label>
                                    <button type="submit" class="btn btn-primary btn-sm">حفظ التصحيح</button>
                                </form>
                            @else
                                <div class="exam-auto-grade">
                                    <i class="fa-solid fa-bolt"></i>
                                    التصحيح الآلي:
                                    <strong>{{ $answer->auto_score }}/{{ $snapshot['points'] }}</strong>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <section class="portal-panel">
            <div class="portal-empty">
                <div class="portal-empty__icon"><i class="fa-solid fa-user-clock"></i></div>
                <p>لم يسلّم أي طالب محاولة بعد</p>
                <span class="portal-empty__hint">ستظهر المحاولات هنا بعد تسليم الطلاب للاختبار</span>
            </div>
        </section>
    @endforelse
</div>

@push('styles')
<style>
.portal-kpi-strip--4{grid-template-columns:repeat(4,minmax(0,1fr))}
.portal-dashboard--instructor .portal-kpi-v2--students{border-right-color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--grades{border-right-color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--week{border-right-color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--sections{border-right-color:#0d9488}
.portal-dashboard--instructor .portal-kpi-v2--students .portal-kpi-v2__icon{background:#eff6ff;color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--grades .portal-kpi-v2__icon{background:#fffbeb;color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--week .portal-kpi-v2__icon{background:#ecfdf5;color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--sections .portal-kpi-v2__icon{background:#f0fdfa;color:#0d9488}

.exam-grade-attempt{overflow:hidden;padding:0}
.exam-grade-attempt__toggle{width:100%;display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1rem 1.15rem;border:0;background:#fff;text-align:right;cursor:pointer}
.exam-grade-attempt__toggle:hover{background:#f8fafc}
.exam-grade-attempt__identity{display:flex;align-items:center;gap:.85rem}
.exam-grade-attempt__avatar{width:2.7rem;height:2.7rem;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff;font-weight:900;flex-shrink:0}
.exam-grade-attempt__meta{display:block;font-size:.68rem;color:#64748b;font-weight:700}
.exam-grade-attempt__identity h2{margin:.15rem 0;font-size:1rem;color:#0f172a}
.exam-grade-attempt__identity p{margin:0;font-size:.72rem;color:#64748b}
.exam-grade-attempt__result{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;justify-content:flex-end}
.exam-grade-attempt__result strong{font-size:.82rem;color:#0f172a}
.exam-grade-status{padding:.28rem .6rem;border-radius:999px;background:#fff7ed;color:#9a3412;font-size:.68rem;font-weight:900}
.exam-grade-status--graded{background:#dcfce7;color:#166534}

.exam-grade-answers{display:grid;gap:.85rem;padding:0 1.15rem 1.15rem;border-top:1px solid #eef2f6}
.exam-grade-answer{padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff;border-inline-start:4px solid #cbd5e1}
.exam-grade-answer.needs-grading{border-inline-start-color:#f59e0b;background:#fffbeb}
.exam-grade-answer.is-correct{border-inline-start-color:#16a34a}
.exam-grade-answer.is-wrong{border-inline-start-color:#dc2626}
.exam-grade-answer__head{display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem;margin-bottom:.65rem}
.exam-grade-answer__number{display:inline-block;font-size:.68rem;font-weight:900;color:#475569;margin-inline-end:.35rem}
.exam-grade-answer__type{font-size:.68rem;color:#64748b;font-weight:700}
.exam-grade-answer__badges{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap}
.exam-grade-answer__badges strong{font-size:.78rem;color:#334155}
.exam-review-status{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .5rem;border-radius:999px;font-size:.65rem;font-weight:900}
.exam-review-status.is-correct{background:#dcfce7;color:#166534}
.exam-review-status.is-wrong{background:#fee2e2;color:#b91c1c}
.exam-review-status.is-manual{background:#fef3c7;color:#92400e}
.exam-grade-answer__prompt{margin:0 0 .85rem;font-size:.92rem;line-height:1.75;color:#0f172a;font-weight:800}

.exam-grade-options{display:grid;gap:.45rem}
.exam-grade-option{display:flex;align-items:center;gap:.55rem;padding:.7rem .8rem;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;color:#475569;font-size:.82rem}
.exam-grade-option__key{display:inline-grid;place-items:center;min-width:1.6rem;height:1.6rem;padding:0 .35rem;border-radius:8px;background:#fff;border:1px solid #e2e8f0;font-size:.72rem;font-weight:900;color:#334155}
.exam-grade-option__text{flex:1;line-height:1.5}
.exam-grade-option small{margin-inline-start:auto;padding:.15rem .4rem;border-radius:999px;background:rgba(255,255,255,.85);font-size:.58rem;font-weight:900;white-space:nowrap}
.exam-grade-option.is-selected{border-color:#93c5fd;background:#eff6ff}
.exam-grade-option.is-correct-answer{border-color:#86efac;background:#f0fdf4;color:#166534}
.exam-grade-option.is-wrong-answer{border-color:#fca5a5;background:#fef2f2;color:#b91c1c}
.exam-grade-option .is-correct-tag{background:#dcfce7;color:#166534}

.exam-grade-text-answers{display:grid;gap:.55rem}
.exam-grade-text-box{padding:.75rem .85rem;border-radius:12px;background:#f8fafc;border:1px solid #e8eef3}
.exam-grade-text-box.is-model{background:#f0fdf4;border-color:#bbf7d0}
.exam-grade-text-box span{display:block;font-size:.65rem;font-weight:900;color:#64748b;margin-bottom:.3rem}
.exam-grade-text-box p{margin:0;font-size:.82rem;line-height:1.7;color:#1e293b;white-space:pre-wrap}
.exam-grade-file{display:flex;align-items:center;gap:.5rem;padding:.75rem;border-radius:12px;background:#f8fafc;border:1px dashed #cbd5e1}
.exam-grade-explanation{margin-top:.7rem;padding:.7rem .8rem;border-radius:12px;background:#fffbeb;color:#78350f}
.exam-grade-explanation strong{font-size:.68rem}.exam-grade-explanation p{margin:.25rem 0 0;font-size:.78rem;line-height:1.7}
.exam-grade-form{display:grid;grid-template-columns:8.5rem minmax(0,1fr) auto;align-items:end;gap:.65rem;margin-top:.85rem}
.exam-grade-form label{display:grid;gap:.3rem}
.exam-grade-form label span{font-size:.68rem;font-weight:800;color:#64748b}
.exam-grade-form input,.exam-grade-form textarea{padding:.65rem .75rem;border:1px solid #dbe4ee;border-radius:10px;font:inherit;font-size:.82rem;background:#fff}
.exam-grade-form small{color:#b91c1c;font-size:.68rem}
.exam-auto-grade{margin-top:.7rem;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .65rem;border-radius:999px;background:#ecfdf5;color:#166534;font-size:.72rem;font-weight:800}

@media(max-width:991.98px){.portal-kpi-strip--4{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){
    .exam-grade-attempt__toggle{align-items:flex-start;flex-direction:column}
    .exam-grade-attempt__result{justify-content:flex-start}
    .exam-grade-form{grid-template-columns:1fr}
    .exam-grade-form .is-wide{grid-column:auto}
    .exam-grade-option{flex-wrap:wrap}
    .exam-grade-option small{margin-inline-start:0}
}
</style>
@endpush

@include('partials.instructor.shell-end')
