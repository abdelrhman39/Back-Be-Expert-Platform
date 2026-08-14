<?php

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use App\Services\ExamIntegrityService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app-user')]
#[Title('أداء الاختبار | منصة مركز التعلم المستمر')]
class extends Component
{
    use WithFileUploads;

    public ExamAttempt $attempt;
    public int $currentIndex = 0;
    public array $answers = [];
    public array $textAnswers = [];
    public array $flagged = [];
    public $answerFile = null;
    public string $saveState = '';

    public function mount(ExamAttempt $attempt, ExamAttemptService $attempts): void
    {
        $student = auth()->user()?->academicStudent;
        abort_unless($student && $attempt->student_id === $student->id, 404);

        if ($attempt->status !== 'in_progress') {
            $this->redirectRoute('exams.show', [
                'locale' => app()->getLocale(),
                'exam' => $attempt->exam_id,
            ], navigate: true);
            return;
        }

        if ($attempt->isExpired()) {
            $attempts->submit($attempt, 'time_expired');
            $this->redirectRoute('exams.show', [
                'locale' => app()->getLocale(),
                'exam' => $attempt->exam_id,
            ], navigate: true);
            return;
        }

        $this->attempt = $attempt->load(['exam.course', 'answers']);

        foreach ($this->attempt->answers as $answer) {
            $this->answers[$answer->question_id] = $answer->answer ?? $this->emptyAnswerFor($answer->question_snapshot);
            $this->textAnswers[$answer->question_id] = $answer->answer_text ?? '';
            $this->flagged[$answer->question_id] = $answer->is_flagged;
        }
    }

    #[Computed]
    public function questions()
    {
        return collect($this->attempt->question_snapshot ?? []);
    }

    #[Computed]
    public function currentQuestion(): ?array
    {
        return $this->questions->get($this->currentIndex);
    }

    #[Computed]
    public function answeredIds(): array
    {
        return $this->attempt->answers()
            ->whereIn('status', ['answered', 'graded', 'pending_grading'])
            ->pluck('question_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function saveCurrent(ExamAttemptService $attempts): void
    {
        $question = $this->currentQuestion;
        if (! $question) {
            return;
        }

        $questionId = (int) $question['id'];
        $isText = in_array($question['type'], ['short_text', 'essay'], true);

        $attempts->saveAnswer(
            $this->attempt,
            $questionId,
            $isText ? null : ($this->answers[$questionId] ?? null),
            $isText ? ($this->textAnswers[$questionId] ?? null) : null,
            (bool) ($this->flagged[$questionId] ?? false),
        );

        $this->attempt->refresh();
        unset($this->answeredIds);
        $this->saveState = 'تم الحفظ '.now()->format('H:i:s');
    }

    public function uploadAnswerFile(ExamAttemptService $attempts): void
    {
        $question = $this->currentQuestion;
        abort_unless($question && $question['type'] === 'file_upload', 422);

        $this->validate([
            'answerFile' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip'],
        ], [], ['answerFile' => 'ملف الإجابة']);

        $answer = ExamAnswer::query()
            ->where('attempt_id', $this->attempt->id)
            ->where('question_id', $question['id'])
            ->firstOrFail();

        if ($answer->file_path) {
            Storage::disk('local')->delete($answer->file_path);
        }

        $path = $this->answerFile->store("exams/attempts/{$this->attempt->id}", 'local');
        $answer->update([
            'file_path' => $path,
            'file_original_name' => $this->answerFile->getClientOriginalName(),
        ]);

        $this->answerFile = null;
        $attempts->saveAnswer(
            $this->attempt,
            (int) $question['id'],
            null,
            null,
            (bool) ($this->flagged[$question['id']] ?? false),
        );
        unset($this->answeredIds);
        $this->saveState = 'تم رفع الملف وحفظه.';
    }

    public function goTo(int $index, ExamAttemptService $attempts): void
    {
        if ($index < 0 || $index >= $this->questions->count()) {
            return;
        }

        $this->saveCurrent($attempts);
        $this->currentIndex = $index;
        $this->answerFile = null;
    }

    public function toggleFlag(): void
    {
        $questionId = (int) ($this->currentQuestion['id'] ?? 0);
        if ($questionId) {
            $this->flagged[$questionId] = ! ($this->flagged[$questionId] ?? false);
        }
    }

    public function tick(ExamAttemptService $attempts): void
    {
        $this->attempt->refresh();

        if ($this->attempt->isActive() && $this->attempt->isExpired()) {
            $attempts->submit($this->attempt, 'time_expired');
            session()->flash('exam_message', 'انتهى الوقت وتم تسليم المحاولة تلقائياً.');
            $this->redirectRoute('exams.show', [
                'locale' => app()->getLocale(),
                'exam' => $this->attempt->exam_id,
            ], navigate: true);
        }
    }

    public function submit(ExamAttemptService $attempts): void
    {
        $this->saveCurrent($attempts);
        $attempts->submit($this->attempt, 'student_submit');
        session()->flash('exam_message', 'تم تسليم الاختبار بنجاح.');
        $this->redirectRoute('exams.show', [
            'locale' => app()->getLocale(),
            'exam' => $this->attempt->exam_id,
        ], navigate: true);
    }

    public function recordIntegrityEvent(string $eventType, ExamIntegrityService $integrity): void
    {
        $integrity->record($this->attempt, $eventType, [
            'question_index' => $this->currentIndex,
            'question_id' => $this->currentQuestion['id'] ?? null,
        ], request()->ip());
        $this->attempt->refresh();
    }

    private function emptyAnswerFor(array $question): array
    {
        return match ($question['type'] ?? null) {
            'multiple_choice' => ['selected' => []],
            'fill_blank' => ['blanks' => array_fill(0, (int) ($question['settings']['blank_count'] ?? 1), '')],
            'matching' => ['matches' => []],
            'ordering' => ['order' => []],
            default => ['value' => null],
        };
    }

    public function hasLocalResponse(array $question): bool
    {
        $questionId = (int) ($question['id'] ?? 0);
        $answer = $this->answers[$questionId] ?? [];

        return match ($question['type'] ?? null) {
            'short_text', 'essay' => filled($this->textAnswers[$questionId] ?? null),
            'multiple_choice' => ! empty($answer['selected'] ?? []),
            'fill_blank' => collect($answer['blanks'] ?? [])->isNotEmpty()
                && collect($answer['blanks'] ?? [])->every(fn ($value) => filled($value)),
            'matching' => count(array_filter($answer['matches'] ?? [], fn ($value) => filled($value)))
                >= count($question['options'] ?? []),
            'ordering' => count(array_filter($answer['order'] ?? [], fn ($value) => filled($value)))
                >= count($question['options'] ?? []),
            'file_upload' => $this->attempt->answers
                ->firstWhere('question_id', $questionId)?->file_path !== null,
            default => filled($answer['value'] ?? null),
        };
    }
};
?>

@php
    $question = $this->currentQuestion;
    $isEnglish = $attempt->language === 'en';
    if ($question && $isEnglish) {
        $question['prompt'] = ($question['prompt_en'] ?? null) ?: $question['prompt'];
        $question['explanation'] = $question['explanation_en'] ?? $question['explanation'] ?? null;
        $question['options'] = collect($question['options'] ?? [])->map(function (array $option): array {
            $option['content'] = ($option['content_en'] ?? null) ?: $option['content'];
            return $option;
        })->all();
        $question['matching_targets'] = ($question['matching_targets_en'] ?? null) ?: ($question['matching_targets'] ?? []);
    }
    $questionId = (int) ($question['id'] ?? 0);
    $remaining = $attempt->remainingSeconds();
    $answeredIds = $this->answeredIds;
    $answeredCount = $this->questions->filter(
        fn ($item) => in_array((int) $item['id'], $answeredIds, true) || $this->hasLocalResponse($item)
    )->count();
    $durationSeconds = (int) ($attempt->settings_snapshot['duration_minutes'] ?? 0) * 60;
@endphp

<div class="exam-runner" wire:poll.5s="tick" dir="{{ $isEnglish ? 'ltr' : 'rtl' }}">
    <header class="exam-runner__header">
        <div class="exam-runner__brand">
            <span>{{ $isEnglish ? 'Online exam' : 'اختبار إلكتروني' }}</span>
            <h1>{{ $attempt->effectiveExamTitle() }}</h1>
        </div>
        <div class="exam-runner__header-meta">
            <span class="exam-runner__save"><i class="fa-solid fa-cloud-arrow-up"></i> {{ $saveState ?: ($isEnglish ? 'Answers are saved automatically' : 'الحفظ تلقائي عند التنقل') }}</span>
            @if ($remaining !== null)
                <span @class(['exam-runner__timer', 'is-danger' => $remaining <= 300]) data-expires-at="{{ $attempt->expires_at->toIso8601String() }}" data-duration-seconds="{{ $durationSeconds }}" role="timer" aria-live="polite">
                    <i class="exam-runner__timer-ring"><em></em></i>
                    <span><small>{{ $isEnglish ? 'Time remaining' : 'الوقت المتبقي' }}</small><b>{{ gmdate('H:i:s', $remaining) }}</b></span>
                </span>
            @endif
        </div>
    </header>

    <div class="exam-runner__body">
        <aside class="exam-runner__nav">
            <div class="exam-runner__progress">
                <div><strong>{{ $answeredCount }}</strong><span>من {{ $this->questions->count() }} مجاب</span></div>
                <progress max="{{ $this->questions->count() }}" value="{{ $answeredCount }}"></progress>
            </div>
            <div class="exam-runner__numbers">
                @foreach ($this->questions as $index => $item)
                    @php($isAnswered = in_array((int) $item['id'], $answeredIds, true) || $this->hasLocalResponse($item))
                    <button
                        type="button"
                        wire:click="goTo({{ $index }})"
                        @class([
                            'is-current' => $index === $currentIndex,
                            'is-answered' => $isAnswered,
                            'is-unanswered' => ! $isAnswered,
                            'is-flagged' => $flagged[$item['id']] ?? false,
                        ])
                    >{{ $index + 1 }}</button>
                @endforeach
            </div>
            <div class="exam-runner__legend"><span><i class="is-unanswered"></i> غير مجاب</span><span><i class="is-answered"></i> مجاب</span><span><i class="is-flagged"></i> للمراجعة</span></div>
        </aside>

        <main class="exam-runner__main">
            @if ($question)
                <section class="exam-question" wire:key="attempt-question-{{ $questionId }}">
                    <header class="exam-question__head">
                        <div><span>{{ $isEnglish ? 'Question' : 'السؤال' }} {{ $currentIndex + 1 }} {{ $isEnglish ? 'of' : 'من' }} {{ $this->questions->count() }}</span><strong>{{ $question['points'] }} {{ $isEnglish ? 'points' : 'درجة' }}</strong></div>
                        <button type="button" wire:click="toggleFlag" @class(['is-active' => $flagged[$questionId] ?? false])><i class="fa-solid fa-flag"></i> {{ $isEnglish ? 'Review' : 'للمراجعة' }}</button>
                    </header>
                    @if ($question['part_title'] ?? null)<span class="exam-question__part">{{ $question['part_title'] }}</span>@endif
                    <div class="exam-question__prompt">{!! nl2br(e($question['prompt'])) !!}</div>

                    <div class="exam-question__answer">
                        @if (in_array($question['type'], ['single_choice', 'true_false']))
                            <div class="exam-choice-list">
                                @foreach ($question['options'] as $option)
                                    <label><input type="radio" wire:model.live="answers.{{ $questionId }}.value" value="{{ $option['key'] }}"><span class="exam-choice-key">{{ $loop->iteration }}</span><span>{{ $option['content'] }}</span></label>
                                @endforeach
                            </div>
                        @elseif ($question['type'] === 'multiple_choice')
                            <div class="exam-choice-list">
                                @foreach ($question['options'] as $option)
                                    <label><input type="checkbox" wire:model.live="answers.{{ $questionId }}.selected" value="{{ $option['key'] }}"><span class="exam-choice-key">{{ $loop->iteration }}</span><span>{{ $option['content'] }}</span></label>
                                @endforeach
                            </div>
                        @elseif (in_array($question['type'], ['short_text', 'essay']))
                            <textarea wire:model.live.debounce.400ms="textAnswers.{{ $questionId }}" rows="{{ $question['type'] === 'essay' ? 10 : 4 }}" placeholder="{{ $isEnglish ? 'Type your answer here...' : 'اكتب إجابتك هنا...' }}"></textarea>
                        @elseif ($question['type'] === 'fill_blank')
                            <div class="exam-blank-list">
                                @foreach (($answers[$questionId]['blanks'] ?? ['']) as $blankIndex => $blank)
                                    <label><span>{{ $isEnglish ? 'Blank' : 'الفراغ' }} {{ $blankIndex + 1 }}</span><input type="text" wire:model.live.debounce.300ms="answers.{{ $questionId }}.blanks.{{ $blankIndex }}"></label>
                                @endforeach
                            </div>
                        @elseif ($question['type'] === 'numeric')
                            <input class="exam-numeric-input" type="number" step="any" wire:model.live.debounce.300ms="answers.{{ $questionId }}.value" placeholder="{{ $isEnglish ? 'Enter a numeric value' : 'أدخل القيمة الرقمية' }}">
                        @elseif ($question['type'] === 'matching')
                            <div class="exam-matching-list">
                                @foreach ($question['options'] as $option)
                                    <label><span>{{ $option['content'] }}</span><select wire:model.live="answers.{{ $questionId }}.matches.{{ $option['key'] }}"><option value="">{{ $isEnglish ? 'Select match' : 'اختر المطابقة' }}</option>@foreach(($question['matching_targets'] ?? []) as $target)<option value="{{ $target }}">{{ $target }}</option>@endforeach</select></label>
                                @endforeach
                            </div>
                        @elseif ($question['type'] === 'ordering')
                            <div class="exam-ordering-list">
                                @foreach ($question['options'] as $position => $unused)
                                    <label><span>{{ $isEnglish ? 'Position' : 'الترتيب' }} {{ $position + 1 }}</span><select wire:model.live="answers.{{ $questionId }}.order.{{ $position }}"><option value="">{{ $isEnglish ? 'Select item' : 'اختر العنصر' }}</option>@foreach($question['options'] as $option)<option value="{{ $option['key'] }}">{{ $option['content'] }}</option>@endforeach</select></label>
                                @endforeach
                            </div>
                        @elseif ($question['type'] === 'file_upload')
                            <div class="exam-file-answer">
                                @php($storedFile = $attempt->answers->firstWhere('question_id', $questionId)?->file_original_name)
                                @if ($storedFile)<div class="exam-file-answer__stored"><i class="fa-solid fa-file-circle-check"></i> {{ $storedFile }}</div>@endif
                                <input type="file" wire:model="answerFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                                @error('answerFile')<small>{{ $message }}</small>@enderror
                                <button type="button" wire:click="uploadAnswerFile" class="btn btn-outline-primary btn-sm" wire:loading.attr="disabled">رفع وحفظ الملف</button>
                            </div>
                        @endif
                    </div>
                </section>

                <footer class="exam-runner__actions">
                    <button type="button" wire:click="goTo({{ $currentIndex - 1 }})" class="btn btn-outline-secondary" @disabled($currentIndex === 0)>السابق</button>
                    <button type="button" wire:click="saveCurrent" class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> حفظ</button>
                    @if ($currentIndex < $this->questions->count() - 1)
                        <button type="button" wire:click="goTo({{ $currentIndex + 1 }})" class="btn btn-primary">حفظ والتالي</button>
                    @else
                        <button type="button" wire:click="submit" wire:confirm="هل أنت متأكد من تسليم الاختبار؟ لن تتمكن من تعديل الإجابات بعد التسليم." class="btn btn-success">تسليم الاختبار</button>
                    @endif
                </footer>
            @endif
        </main>
    </div>
</div>

@push('styles')
<style>
    body{background:#f4f7f5}.exam-runner{min-height:100vh;direction:rtl}.exam-runner__header{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.8rem 1.5rem;background:#fff;border-bottom:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(15,23,42,.05)}.exam-runner__brand span{font-size:.62rem;color:#64748b}.exam-runner__brand h1{margin:.1rem 0 0;font-size:1rem;color:#0f172a}.exam-runner__header-meta{display:flex;align-items:center;gap:.7rem}.exam-runner__save{font-size:.67rem;color:#64748b}.exam-runner__timer{--timer-color:#16a34a;--timer-progress:100;display:flex;align-items:center;gap:.5rem;padding:.35rem .6rem;border:1px solid #bbf7d0;border-radius:11px;background:#f0fdf4;color:#166534;direction:ltr;transition:.25s}.exam-runner__timer>span{display:flex;align-items:flex-start;flex-direction:column}.exam-runner__timer small{font-size:.48rem;opacity:.75}.exam-runner__timer b{font-size:.85rem;letter-spacing:.04em}.exam-runner__timer-ring{display:grid;place-items:center;width:1.8rem;height:1.8rem;border-radius:50%;background:conic-gradient(var(--timer-color) calc(var(--timer-progress)*1%),#dbe7df 0);transform:rotate(-90deg)}.exam-runner__timer-ring em{width:1.15rem;height:1.15rem;border-radius:50%;background:#fff}.exam-runner__timer.is-danger{--timer-color:#dc2626;border-color:#fecaca;background:#fef2f2;color:#b91c1c;animation:timerPulse 1.2s ease-in-out infinite}.exam-runner__timer.is-critical{animation-duration:.65s}@keyframes timerPulse{50%{box-shadow:0 0 0 5px rgba(220,38,38,.1)}}.exam-runner__body{display:grid;grid-template-columns:15rem minmax(0,1fr);max-width:90rem;margin:0 auto}.exam-runner__nav{position:sticky;top:4.2rem;height:calc(100vh - 4.2rem);padding:1rem;background:#fff;border-left:1px solid #e2e8f0}.exam-runner__progress>div{display:flex;justify-content:space-between;font-size:.7rem}.exam-runner__progress progress{width:100%;height:.4rem;accent-color:#16a34a}.exam-runner__numbers{display:grid;grid-template-columns:repeat(5,1fr);gap:.35rem;margin-top:1rem}.exam-runner__numbers button{aspect-ratio:1;border:1px solid #fecaca;border-radius:7px;background:#fee2e2;color:#b91c1c;font-size:.7rem;font-weight:900;cursor:pointer;transition:.15s}.exam-runner__numbers button:hover{transform:translateY(-1px)}.exam-runner__numbers button.is-answered{border-color:#86efac;background:#dcfce7;color:#166534}.exam-runner__numbers button.is-current{outline:2px solid #2563eb;outline-offset:2px}.exam-runner__numbers button.is-flagged{box-shadow:inset 0 -3px #f59e0b}.exam-runner__legend{display:flex;flex-direction:column;gap:.35rem;margin-top:1rem;color:#64748b;font-size:.65rem}.exam-runner__legend i{display:inline-block;width:.65rem;height:.65rem;border-radius:3px}.exam-runner__legend .is-unanswered{background:#fee2e2}.exam-runner__legend .is-answered{background:#dcfce7}.exam-runner__legend .is-flagged{background:#fef3c7}.exam-runner__main{padding:1.25rem;max-width:65rem;width:100%;margin:0 auto}.exam-question{padding:1.25rem;border:1px solid #e2e8f0;border-radius:16px;background:#fff;min-height:28rem}.exam-question__head{display:flex;justify-content:space-between;gap:1rem;padding-bottom:.8rem;border-bottom:1px solid #f1f5f9}.exam-question__head>div{display:flex;gap:.65rem;font-size:.72rem}.exam-question__head button{border:0;background:#f8fafc;color:#64748b;border-radius:8px;padding:.35rem .55rem;font-size:.68rem}.exam-question__head button.is-active{background:#fff7ed;color:#c2410c}.exam-question__part{display:inline-block;margin-top:.8rem;font-size:.65rem;color:#64748b}.exam-question__prompt{margin:1rem 0 1.25rem;color:#0f172a;font-size:1rem;font-weight:700;line-height:1.9}.exam-question__answer textarea,.exam-numeric-input,.exam-blank-list input,.exam-matching-list select,.exam-ordering-list select{width:100%;padding:.7rem;border:1px solid #cbd5e1;border-radius:9px;font:inherit;font-size:.8rem}.exam-choice-list{display:flex;flex-direction:column;gap:.55rem}.exam-choice-list label{display:grid;grid-template-columns:auto auto 1fr;align-items:center;gap:.65rem;padding:.75rem;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer}.exam-choice-list label:has(input:checked){border-color:#16a34a;background:#f0fdf4}.exam-choice-list input{accent-color:#16a34a}.exam-choice-key{display:grid;place-items:center;width:1.7rem;height:1.7rem;border-radius:7px;background:#f1f5f9;font-size:.68rem}.exam-blank-list,.exam-matching-list,.exam-ordering-list{display:flex;flex-direction:column;gap:.65rem}.exam-blank-list label,.exam-matching-list label,.exam-ordering-list label{display:grid;grid-template-columns:8rem 1fr;align-items:center;gap:.65rem;font-size:.72rem}.exam-file-answer{display:flex;flex-direction:column;gap:.65rem;padding:1rem;border:2px dashed #cbd5e1;border-radius:12px}.exam-file-answer__stored{color:#166534;font-size:.75rem}.exam-file-answer small{color:#b91c1c}.exam-runner__actions{display:flex;justify-content:space-between;gap:.55rem;padding:1rem 0}@media(max-width:800px){.exam-runner__header{padding:.7rem}.exam-runner__save{display:none}.exam-runner__body{grid-template-columns:1fr}.exam-runner__nav{position:static;height:auto;border-left:0;border-bottom:1px solid #e2e8f0}.exam-runner__numbers{grid-template-columns:repeat(10,1fr)}.exam-runner__main{padding:.7rem}.exam-question{padding:.85rem}.exam-blank-list label,.exam-matching-list label,.exam-ordering-list label{grid-template-columns:1fr}.exam-runner__actions{flex-wrap:wrap}}@media(max-width:480px){.exam-runner__numbers{grid-template-columns:repeat(7,1fr)}.exam-runner__brand h1{max-width:10rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}}
</style>
@endpush

@script
<script>
    const report = (event) => $wire.recordIntegrityEvent(event);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) report('page_hidden');
    });
    document.addEventListener('copy', () => report('copy_attempt'));
    document.addEventListener('paste', () => report('paste_attempt'));
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) report('fullscreen_exit');
    });
    const timer = document.querySelector('[data-expires-at]');
    if (timer) {
        const target = new Date(timer.dataset.expiresAt).getTime();
        const totalSeconds = Math.max(1, Number(timer.dataset.durationSeconds) || Math.floor((target - Date.now()) / 1000));
        let expiryReported = false;
        const updateTimer = () => {
            const remaining = Math.max(0, Math.floor((target - Date.now()) / 1000));
            const h = String(Math.floor(remaining / 3600)).padStart(2, '0');
            const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');
            const value = timer.querySelector('b');
            if (value) value.textContent = `${h}:${m}:${s}`;
            timer.style.setProperty('--timer-progress', Math.min(100, Math.max(0, (remaining / totalSeconds) * 100)));
            timer.classList.toggle('is-danger', remaining <= 300);
            timer.classList.toggle('is-critical', remaining <= 60);

            if (remaining === 0 && !expiryReported) {
                expiryReported = true;
                $wire.tick();
            }
        };
        updateTimer();
        setInterval(updateTimer, 1000);
    }
</script>
@endscript
