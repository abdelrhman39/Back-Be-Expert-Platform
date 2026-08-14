<?php

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('مراجعة الاختبار | منصة مركز التعلم المستمر')]
class extends Component
{
    public ExamAttempt $attempt;

    public function mount(ExamAttempt $attempt): void
    {
        $student = auth()->user()?->academicStudent;
        abort_unless($student && $attempt->student_id === $student->id, 404);

        $attempt->load(['exam.course', 'answers']);
        abort_unless($attempt->exam->answersAreVisibleFor($attempt), 403);

        $this->attempt = $attempt;
    }

    #[Computed]
    public function reviewItems()
    {
        $showCorrections = $this->attempt->exam->correctionsAreVisibleFor($this->attempt);
        $isEnglish = $this->attempt->language === 'en';

        return $this->attempt->answers
            ->sortBy(fn (ExamAnswer $answer) => collect($this->attempt->question_snapshot)
                ->search(fn (array $question) => (int) $question['id'] === $answer->question_id))
            ->values()
            ->map(function (ExamAnswer $answer, int $index) use ($showCorrections, $isEnglish): array {
                $question = $answer->question_snapshot ?? [];
                $options = collect($question['options'] ?? [])->map(function (array $option) use ($isEnglish): array {
                    return [
                        'key' => (string) $option['key'],
                        'content' => $isEnglish && filled($option['content_en'] ?? null)
                            ? $option['content_en']
                            : $option['content'],
                    ];
                })->values();
                $selectedKeys = $this->selectedKeys($answer, $question);
                $correctKeys = $showCorrections ? $this->correctKeys($answer, $question) : [];

                return [
                    'number' => $index + 1,
                    'type' => $question['type'] ?? '',
                    'prompt' => $isEnglish && filled($question['prompt_en'] ?? null)
                        ? $question['prompt_en']
                        : ($question['prompt'] ?? 'السؤال'),
                    'points' => (float) ($question['points'] ?? 0),
                    'score' => $answer->effectiveScore(),
                    'is_correct' => $answer->is_correct,
                    'options' => $options->map(fn (array $option) => [
                        ...$option,
                        'selected' => in_array($option['key'], $selectedKeys, true),
                        'correct' => in_array($option['key'], $correctKeys, true),
                    ])->all(),
                    'student_answer' => $this->formatStudentAnswer($answer, $question, $options),
                    'correct_answer' => $showCorrections
                        ? $this->formatCorrectAnswer($answer, $question, $options)
                        : null,
                    'explanation' => $showCorrections
                        ? ($isEnglish && filled($question['explanation_en'] ?? null)
                            ? $question['explanation_en']
                            : ($question['explanation'] ?? null))
                        : null,
                    'feedback' => $showCorrections ? $answer->grader_feedback : null,
                ];
            });
    }

    private function selectedKeys(ExamAnswer $answer, array $question): array
    {
        return match ($question['type'] ?? null) {
            'single_choice', 'true_false' => array_values(array_filter([
                (string) ($answer->answer['value'] ?? ''),
            ])),
            'multiple_choice' => array_values(array_map('strval', $answer->answer['selected'] ?? [])),
            default => [],
        };
    }

    private function correctKeys(ExamAnswer $answer, array $question): array
    {
        $key = $answer->grading_key ?? [];

        return match ($question['type'] ?? null) {
            'single_choice', 'true_false' => array_values(array_filter([
                (string) ($key['correct'] ?? $key['value'] ?? ''),
            ])),
            'multiple_choice' => array_values(array_map('strval', $key['correct'] ?? [])),
            default => [],
        };
    }

    private function formatStudentAnswer(ExamAnswer $answer, array $question, $options): string
    {
        $optionLabel = fn (mixed $key): string => (string) (
            $options->firstWhere('key', (string) $key)['content'] ?? $key
        );

        return match ($question['type'] ?? null) {
            'single_choice', 'true_false' => $optionLabel($answer->answer['value'] ?? ''),
            'multiple_choice' => collect($answer->answer['selected'] ?? [])->map($optionLabel)->join('، '),
            'short_text', 'essay' => (string) ($answer->answer_text ?? ''),
            'fill_blank' => collect($answer->answer['blanks'] ?? [])->map(fn ($value, $index) => ($index + 1).'. '.$value)->join(' | '),
            'matching' => collect($answer->answer['matches'] ?? [])->map(fn ($value, $key) => $optionLabel($key).' ← '.$value)->join(' | '),
            'ordering' => collect($answer->answer['order'] ?? [])->map($optionLabel)->join(' ← '),
            'numeric' => (string) ($answer->answer['value'] ?? ''),
            'file_upload' => (string) ($answer->file_original_name ?? ''),
            default => (string) ($answer->answer['value'] ?? ''),
        } ?: 'لم يُجب الطالب';
    }

    private function formatCorrectAnswer(ExamAnswer $answer, array $question, $options): string
    {
        $key = $answer->grading_key ?? [];
        $optionLabel = fn (mixed $value): string => (string) (
            $options->firstWhere('key', (string) $value)['content'] ?? $value
        );

        return match ($question['type'] ?? null) {
            'single_choice', 'true_false' => $optionLabel($key['correct'] ?? $key['value'] ?? ''),
            'multiple_choice' => collect($key['correct'] ?? [])->map($optionLabel)->join('، '),
            'short_text' => collect($key['accepted'] ?? [])->join('، '),
            'fill_blank' => collect($key['blanks'] ?? [])->map(
                fn ($values, $index) => ($index + 1).'. '.collect(is_array($values) ? $values : [$values])->join(' / ')
            )->join(' | '),
            'matching' => collect($key['matches'] ?? [])->map(fn ($value, $option) => $optionLabel($option).' ← '.$value)->join(' | '),
            'ordering' => collect($key['order'] ?? [])->map($optionLabel)->join(' ← '),
            'numeric' => (string) ($key['value'] ?? '').((float) ($key['tolerance'] ?? 0) > 0 ? ' ± '.$key['tolerance'] : ''),
            'essay', 'file_upload' => 'يُقيّم هذا السؤال يدوياً وفق ملاحظات المصحح.',
            default => '',
        } ?: 'لا توجد إجابة نموذجية مسجلة.';
    }
};
?>

@php
    $exam = $attempt->exam;
    $showCorrections = $exam->correctionsAreVisibleFor($attempt);
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'exams', 'portalTitle' => 'مراجعة الاختبار'])

<div class="portal-dashboard exam-review-page">
    <a href="{{ route('exams.show', ['locale' => app()->getLocale(), 'exam' => $exam->id]) }}" class="portal-panel__link">← العودة إلى تفاصيل الاختبار</a>

    <section class="exam-review-hero">
        <div>
            <span>المحاولة {{ $attempt->attempt_number }}</span>
            <h1>{{ $attempt->effectiveExamTitle() }}</h1>
            <p>{{ $exam->course?->name_ar }}</p>
        </div>
        <div @class(['exam-review-result', 'is-passed' => $attempt->passed, 'is-failed' => ! $attempt->passed])>
            <strong>{{ $attempt->percentage }}%</strong>
            <span>{{ $attempt->passed ? 'ناجح' : 'لم يجتز' }}</span>
        </div>
    </section>

    <div class="exam-review-summary">
        <article><span>الدرجة</span><strong>{{ $attempt->total_score }} / {{ $attempt->effectiveTotalPoints() }}</strong></article>
        @if ($showCorrections)
            <article><span>الإجابات الصحيحة</span><strong>{{ $attempt->answers->where('is_correct', true)->count() }}</strong></article>
            <article><span>الإجابات الخاطئة</span><strong>{{ $attempt->answers->where('is_correct', false)->count() }}</strong></article>
        @else
            <article><span>الأسئلة المجابة</span><strong>{{ $attempt->answers->whereNotIn('status', ['unanswered'])->count() }}</strong></article>
            <article><span>إجمالي الأسئلة</span><strong>{{ $attempt->answers->count() }}</strong></article>
        @endif
        <article><span>تاريخ التسليم</span><strong>{{ $attempt->submitted_at?->translatedFormat('d M Y، H:i') }}</strong></article>
    </div>

    @unless ($showCorrections)
        <div class="exam-review-notice">
            <i class="fa-solid fa-eye"></i>
            تعرض هذه الصفحة إجاباتك ودرجاتها فقط. لم يمنحك المدرب صلاحية رؤية الإجابات النموذجية.
        </div>
    @endunless

    <div class="exam-review-list">
        @foreach ($this->reviewItems as $item)
            <article @class([
                'exam-review-card',
                'is-correct' => $showCorrections && $item['is_correct'] === true,
                'is-wrong' => $showCorrections && $item['is_correct'] === false,
                'is-manual' => $item['is_correct'] === null,
            ]) wire:key="review-answer-{{ $loop->index }}">
                <header>
                    <div>
                        <span class="exam-review-number">السؤال {{ $item['number'] }}</span>
                        @if ($showCorrections)
                            <span @class([
                                'exam-review-status',
                                'is-correct' => $item['is_correct'] === true,
                                'is-wrong' => $item['is_correct'] === false,
                                'is-manual' => $item['is_correct'] === null,
                            ])>
                                <i class="fa-solid {{ $item['is_correct'] === true ? 'fa-circle-check' : ($item['is_correct'] === false ? 'fa-circle-xmark' : 'fa-user-pen') }}"></i>
                                {{ $item['is_correct'] === true ? 'إجابة صحيحة' : ($item['is_correct'] === false ? 'إجابة خاطئة' : 'تصحيح يدوي') }}
                            </span>
                        @endif
                    </div>
                    <strong>{{ $item['score'] }} / {{ $item['points'] }}</strong>
                </header>

                <h2>{{ $item['prompt'] }}</h2>

                @if (in_array($item['type'], ['single_choice', 'multiple_choice', 'true_false'], true))
                    <div class="exam-review-options">
                        @foreach ($item['options'] as $option)
                            <div @class([
                                'is-selected' => $option['selected'],
                                'is-correct-answer' => $showCorrections && $option['correct'],
                                'is-wrong-answer' => $showCorrections && $option['selected'] && ! $option['correct'],
                            ])>
                                <i class="fa-regular {{ $option['selected'] ? 'fa-circle-dot' : 'fa-circle' }}"></i>
                                <span>{{ $option['content'] }}</span>
                                @if ($option['selected'])<small>إجابتك</small>@endif
                                @if ($showCorrections && $option['correct'])<small>الإجابة الصحيحة</small>@endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="exam-review-answer">
                        <span>إجابتك</span>
                        <p>{{ $item['student_answer'] }}</p>
                    </div>
                    @if ($showCorrections)
                        <div class="exam-review-answer is-model">
                            <span>الإجابة الصحيحة / النموذجية</span>
                            <p>{{ $item['correct_answer'] }}</p>
                        </div>
                    @endif
                @endif

                @if ($showCorrections && $item['explanation'])
                    <div class="exam-review-explanation"><strong>شرح الإجابة</strong><p>{{ $item['explanation'] }}</p></div>
                @endif
                @if ($showCorrections && $item['feedback'])
                    <div class="exam-review-feedback"><strong>ملاحظة المصحح</strong><p>{{ $item['feedback'] }}</p></div>
                @endif
            </article>
        @endforeach
    </div>
</div>

<style>
    .exam-review-page{display:flex;flex-direction:column;gap:1rem}.exam-review-hero{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.3rem 1.5rem;border-radius:17px;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff}.exam-review-hero span{font-size:.68rem;opacity:.8}.exam-review-hero h1{margin:.25rem 0;color:#fff;font-size:1.35rem}.exam-review-hero p{margin:0;font-size:.75rem;opacity:.85}.exam-review-result{display:grid;place-items:center;min-width:6rem;padding:.75rem;border:1px solid rgba(255,255,255,.2);border-radius:14px;background:rgba(255,255,255,.1)}.exam-review-result strong{font-size:1.35rem}.exam-review-result span{font-weight:900}.exam-review-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.7rem}.exam-review-summary article{display:grid;gap:.25rem;padding:.85rem 1rem;border:1px solid #e2e8f0;border-radius:13px;background:#fff}.exam-review-summary span{color:#64748b;font-size:.67rem}.exam-review-summary strong{color:#0f172a;font-size:.82rem}.exam-review-notice{display:flex;align-items:center;gap:.55rem;padding:.8rem 1rem;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff;color:#1e40af;font-size:.72rem}.exam-review-list{display:grid;gap:.9rem}.exam-review-card{overflow:hidden;padding:1rem 1.1rem;border:1px solid #e2e8f0;border-radius:15px;background:#fff}.exam-review-card.is-correct{border-inline-start:4px solid #16a34a}.exam-review-card.is-wrong{border-inline-start:4px solid #dc2626}.exam-review-card>header{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding-bottom:.7rem;border-bottom:1px solid #f1f5f9}.exam-review-card>header>div{display:flex;align-items:center;gap:.45rem}.exam-review-number{font-size:.7rem;font-weight:900;color:#475569}.exam-review-status{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .45rem;border-radius:999px;font-size:.62rem;font-weight:900}.exam-review-status.is-correct{background:#dcfce7;color:#166534}.exam-review-status.is-wrong{background:#fee2e2;color:#b91c1c}.exam-review-status.is-manual{background:#fef3c7;color:#92400e}.exam-review-card>header>strong{font-size:.72rem;color:#334155}.exam-review-card h2{margin:.85rem 0;font-size:.9rem;line-height:1.8;color:#0f172a}.exam-review-options{display:grid;gap:.45rem}.exam-review-options>div{display:flex;align-items:center;gap:.55rem;padding:.65rem .75rem;border:1px solid #e2e8f0;border-radius:10px;color:#475569;font-size:.75rem}.exam-review-options>div.is-selected{border-color:#93c5fd;background:#eff6ff}.exam-review-options>div.is-correct-answer{border-color:#86efac;background:#f0fdf4;color:#166534}.exam-review-options>div.is-wrong-answer{border-color:#fca5a5;background:#fef2f2;color:#b91c1c}.exam-review-options small{margin-inline-start:auto;padding:.12rem .35rem;border-radius:999px;background:rgba(255,255,255,.7);font-size:.54rem;font-weight:900}.exam-review-answer{padding:.7rem .8rem;border-radius:10px;background:#f8fafc}.exam-review-answer.is-model{margin-top:.55rem;background:#f0fdf4}.exam-review-answer span{color:#64748b;font-size:.62rem;font-weight:900}.exam-review-answer p{margin:.3rem 0 0;color:#1e293b;font-size:.76rem;white-space:pre-wrap}.exam-review-explanation,.exam-review-feedback{margin-top:.65rem;padding:.7rem .8rem;border-radius:10px;background:#fffbeb;color:#78350f}.exam-review-feedback{background:#f5f3ff;color:#5b21b6}.exam-review-explanation strong,.exam-review-feedback strong{font-size:.65rem}.exam-review-explanation p,.exam-review-feedback p{margin:.25rem 0 0;font-size:.72rem;line-height:1.7}@media(max-width:800px){.exam-review-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.exam-review-hero{align-items:flex-start;flex-direction:column}.exam-review-summary{grid-template-columns:1fr}.exam-review-card>header{align-items:flex-start}}
</style>

@include('partials.portal.shell-end')
