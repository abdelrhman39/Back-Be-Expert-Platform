<?php

use App\Models\AcademicSection;
use App\Models\Exam;
use App\Models\ExamPartQuestion;
use App\Models\ExamQuestion;
use App\Services\ExamPublicationService;
use App\Services\InstructorService;
use App\Support\ExamOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('بناء الاختبار | لوحة المدرب')]
class extends Component
{
    public AcademicSection $section;
    public Exam $exam;
    public string $questionType = 'single_choice';
    public string $questionPrompt = '';
    public string $questionExplanation = '';
    public string $difficulty = 'medium';
    public string $points = '1';
    public array $options = [];
    public string $correctScalar = '';
    public string $structuredAnswer = '';
    public string $numericTolerance = '0';
    public string $flashMessage = '';

    public function mount(AcademicSection $section, Exam $exam, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $section);
        $instructors->authorizePermission(auth()->user(), 'instructor.exams.update');
        abort_unless($exam->section_id === $section->id, 404);

        $this->section = $section->load('course');
        $this->exam = $exam;
        $this->resetQuestionForm();
    }

    #[Computed]
    public function examParts()
    {
        return $this->exam->parts()
            ->with(['questions.options'])
            ->get();
    }

    #[Computed]
    public function bankQuestions()
    {
        $attachedIds = ExamPartQuestion::query()
            ->whereIn('exam_part_id', $this->exam->parts()->pluck('id'))
            ->pluck('question_id');

        return ExamQuestion::query()
            ->where('course_id', $this->exam->course_id)
            ->where('status', 'published')
            ->whereNotIn('id', $attachedIds)
            ->latest()
            ->limit(30)
            ->get();
    }

    #[Computed]
    public function builderStats(): array
    {
        $questionCount = (int) $this->examParts->sum(fn ($part) => $part->questions->count());

        return [
            'questions' => $questionCount,
            'points' => (float) $this->exam->total_points,
            'duration' => $this->exam->duration_minutes,
            'bank' => $this->bankQuestions->count(),
            'status' => ExamOptions::statusLabel($this->exam->status),
        ];
    }

    public function updatedQuestionType(): void
    {
        $this->options = $this->defaultOptions($this->questionType);
        $this->correctScalar = $this->questionType === 'true_false' ? 'true' : '';
        $this->structuredAnswer = '';
    }

    public function addOption(): void
    {
        $this->options[] = ['content' => '', 'correct' => false];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function saveQuestion(InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.questions.manage');

        $this->validate([
            'questionType' => ['required', Rule::in(array_keys(ExamOptions::questionTypes()))],
            'questionPrompt' => ['required', 'string', 'max:30000'],
            'questionExplanation' => ['nullable', 'string', 'max:20000'],
            'difficulty' => ['required', Rule::in(array_keys(ExamOptions::difficulties()))],
            'points' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'options' => ['array', 'max:50'],
            'options.*.content' => ['nullable', 'string', 'max:5000'],
            'correctScalar' => ['nullable', 'string', 'max:5000'],
            'structuredAnswer' => ['nullable', 'string', 'max:30000'],
            'numericTolerance' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'questionPrompt' => 'نص السؤال',
            'points' => 'درجة السؤال',
            'structuredAnswer' => 'نموذج الإجابة',
        ]);

        [$answerKey, $optionRows] = $this->buildAnswerDefinition();

        DB::transaction(function () use ($answerKey, $optionRows): void {
            $question = ExamQuestion::query()->create([
                'course_id' => $this->exam->course_id,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'type' => $this->questionType,
                'prompt' => $this->questionPrompt,
                'explanation' => $this->questionExplanation ?: null,
                'default_points' => $this->points,
                'difficulty' => $this->difficulty,
                'scope' => 'course',
                'status' => 'published',
                'answer_key' => $answerKey,
                'settings' => [
                    'case_sensitive' => false,
                    'blank_count' => count($answerKey['blanks'] ?? []),
                ],
                'published_at' => now(),
            ]);

            foreach ($optionRows as $index => $row) {
                $question->options()->create([
                    'option_key' => $row['key'],
                    'content' => $row['content'],
                    'is_correct' => $row['correct'] ?? false,
                    'weight' => $row['correct'] ?? false ? 1 : 0,
                    'match_data' => $row['match_data'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }

            $part = $this->exam->parts()->orderBy('sort_order')->firstOrFail();
            $part->questionLinks()->create([
                'question_id' => $question->id,
                'points' => $this->points,
                'sort_order' => $part->questionLinks()->max('sort_order') + 1,
                'is_required' => true,
            ]);

            $this->exam->refreshTotalPoints();
        });

        $this->exam->refresh();
        unset($this->examParts, $this->bankQuestions);
        $this->resetQuestionForm();
        $this->flashMessage = 'تمت إضافة السؤال إلى الاختبار وبنك الأسئلة.';
    }

    public function attachBankQuestion(int $questionId, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.questions.manage');

        $question = ExamQuestion::query()
            ->where('course_id', $this->exam->course_id)
            ->where('status', 'published')
            ->findOrFail($questionId);
        $part = $this->exam->parts()->orderBy('sort_order')->firstOrFail();

        $part->questionLinks()->firstOrCreate(
            ['question_id' => $question->id],
            [
                'points' => $question->default_points,
                'sort_order' => $part->questionLinks()->max('sort_order') + 1,
                'is_required' => true,
            ]
        );

        $this->exam->refreshTotalPoints();
        $this->exam->refresh();
        unset($this->examParts, $this->bankQuestions);
        $this->flashMessage = 'تمت إضافة السؤال من البنك.';
    }

    public function detachQuestion(int $linkId, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.exams.update');

        ExamPartQuestion::query()
            ->whereKey($linkId)
            ->whereIn('exam_part_id', $this->exam->parts()->pluck('id'))
            ->delete();

        $this->exam->refreshTotalPoints();
        $this->exam->refresh();
        unset($this->examParts, $this->bankQuestions);
        $this->flashMessage = 'تمت إزالة السؤال من الاختبار مع الاحتفاظ به في البنك.';
    }

    public function publish(InstructorService $instructors, ExamPublicationService $publications): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.exams.publish');
        $publication = $publications->publish($this->exam, auth()->user());
        $this->exam->refresh();
        $this->flashMessage = "تم نشر النسخة {$publication->version} وتثبيت محتواها للطلاب.";
    }

    private function resetQuestionForm(): void
    {
        $this->questionPrompt = '';
        $this->questionExplanation = '';
        $this->difficulty = 'medium';
        $this->points = '1';
        $this->correctScalar = $this->questionType === 'true_false' ? 'true' : '';
        $this->structuredAnswer = '';
        $this->numericTolerance = '0';
        $this->options = $this->defaultOptions($this->questionType);
    }

    private function defaultOptions(string $type): array
    {
        return in_array($type, ['single_choice', 'multiple_choice'], true)
            ? [
                ['content' => '', 'correct' => false],
                ['content' => '', 'correct' => false],
                ['content' => '', 'correct' => false],
                ['content' => '', 'correct' => false],
            ]
            : [];
    }

    /** @return array{0: array|null, 1: array<int, array>} */
    private function buildAnswerDefinition(): array
    {
        if (in_array($this->questionType, ['essay', 'file_upload'], true)) {
            return [null, []];
        }

        if (in_array($this->questionType, ['single_choice', 'multiple_choice'], true)) {
            $rows = collect($this->options)
                ->filter(fn ($option) => filled($option['content'] ?? null))
                ->values()
                ->map(fn ($option, $index) => [
                    'key' => chr(65 + $index),
                    'content' => trim($option['content']),
                    'correct' => $this->questionType === 'single_choice'
                        ? (string) $index === $this->correctScalar
                        : (bool) ($option['correct'] ?? false),
                ])
                ->all();
            $correct = collect($rows)->where('correct', true)->pluck('key')->values()->all();

            if (count($rows) < 2) {
                throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'أضف خيارين على الأقل.']);
            }
            if ($this->questionType === 'single_choice' && count($correct) !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'حدد إجابة صحيحة واحدة فقط.']);
            }
            if ($this->questionType === 'multiple_choice' && $correct === []) {
                throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'حدد إجابة صحيحة واحدة على الأقل.']);
            }

            return [[
                'correct' => $this->questionType === 'single_choice' ? $correct[0] : $correct,
            ], $rows];
        }

        if ($this->questionType === 'true_false') {
            return [['correct' => $this->correctScalar], [
                ['key' => 'true', 'content' => 'صح', 'correct' => $this->correctScalar === 'true'],
                ['key' => 'false', 'content' => 'خطأ', 'correct' => $this->correctScalar === 'false'],
            ]];
        }

        if ($this->questionType === 'numeric') {
            if (! is_numeric($this->correctScalar)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['correctScalar' => 'أدخل إجابة رقمية صحيحة.']);
            }

            return [[
                'value' => (float) $this->correctScalar,
                'tolerance' => (float) $this->numericTolerance,
            ], []];
        }

        $lines = collect(preg_split('/\r\n|\r|\n/', trim($this->structuredAnswer)) ?: [])
            ->filter(fn ($line) => filled(trim($line)))
            ->values();

        if ($lines->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['structuredAnswer' => 'أدخل نموذج الإجابة.']);
        }

        return match ($this->questionType) {
            'short_text' => [['accepted' => $lines->all()], []],
            'fill_blank' => [['blanks' => $lines->map(fn ($line) => array_map('trim', explode('|', $line)))->all()], []],
            'ordering' => [
                ['order' => $lines->keys()->map(fn ($index) => 'I'.($index + 1))->all()],
                $lines->map(fn ($line, $index) => ['key' => 'I'.($index + 1), 'content' => trim($line), 'correct' => true])->all(),
            ],
            'matching' => $this->buildMatchingDefinition($lines),
            default => [['accepted' => $lines->all()], []],
        };
    }

    private function buildMatchingDefinition($lines): array
    {
        $matches = [];
        $rows = [];

        foreach ($lines as $index => $line) {
            $pair = array_map('trim', preg_split('/=>|=|←|→/u', $line, 2) ?: []);
            if (count($pair) !== 2 || blank($pair[0]) || blank($pair[1])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'structuredAnswer' => 'اكتب كل مطابقة بصيغة: العنصر => الإجابة',
                ]);
            }

            $key = 'M'.($index + 1);
            $matches[$key] = $pair[1];
            $rows[] = ['key' => $key, 'content' => $pair[0], 'correct' => true, 'match_data' => ['target' => $pair[1]]];
        }

        return [['matches' => $matches], $rows];
    }
};
?>

@php
    $locale = app()->getLocale();
    $stats = $this->builderStats;
    $breadcrumb = [
        ['href' => route('instructor.exams', ['locale' => $locale]), 'label' => 'الاختبارات'],
        ['label' => 'بناء الأسئلة'],
    ];
@endphp

@include('partials.instructor.shell-start', [
    'instructorActive' => 'exams',
    'instructorTitle' => 'بناء: '.$exam->title,
    'instructorBreadcrumb' => $breadcrumb,
])

<div class="portal-dashboard portal-dashboard--instructor exam-builder-page">
    @include('partials.instructor.page-hero', [
        'title' => $exam->title,
        'desc' => ($section->name).' — أضف الأسئلة، حدّد الاختيارات الصحيحة، وانشر الاختبار للطلاب.',
        'icon' => 'fa-list-check',
        'stats' => [
            ['value' => $stats['questions'], 'label' => 'سؤال'],
            ['value' => $stats['points'], 'label' => 'درجة'],
            ['value' => $stats['duration'] ?: '∞', 'label' => 'دقيقة'],
        ],
        'actions' => array_filter([
            ['href' => route('instructor.exams.grading', ['locale' => $locale, 'section' => $section->id, 'exam' => $exam->id]), 'label' => 'التصحيح', 'icon' => 'fa-pen-to-square', 'class' => 'btn-outline-primary'],
            ['href' => route('instructor.exams.edit', ['locale' => $locale, 'section' => $section->id, 'exam' => $exam->id]), 'label' => 'الإعدادات', 'icon' => 'fa-sliders', 'class' => 'btn-light border'],
            ['href' => route('instructor.exams', ['locale' => $locale]), 'label' => 'العودة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ]),
    ])

    <div class="portal-kpi-strip portal-kpi-strip--4">
        <div class="portal-kpi-v2 portal-kpi-v2--sections"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-list-ol"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $stats['questions'] }}</span><span class="portal-kpi-v2__label">أسئلة في الاختبار</span></span></div>
        <div class="portal-kpi-v2 portal-kpi-v2--week"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-star"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $stats['points'] }}</span><span class="portal-kpi-v2__label">إجمالي الدرجات</span></span></div>
        <div class="portal-kpi-v2 portal-kpi-v2--students"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-database"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $stats['bank'] }}</span><span class="portal-kpi-v2__label">متاح من البنك</span></span></div>
        <div class="portal-kpi-v2 portal-kpi-v2--grades"><span class="portal-kpi-v2__icon"><i class="fa-solid fa-{{ $exam->status === 'published' ? 'circle-check' : 'file-pen' }}"></i></span><span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $stats['status'] }}</span><span class="portal-kpi-v2__label">حالة الاختبار</span></span></div>
    </div>

    @if ($flashMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact"><div class="portal-alert__content">{{ $flashMessage }}</div></div>
    @endif
    @error('publish')
        <div class="portal-alert portal-alert--danger portal-alert--compact"><div class="portal-alert__content">{{ $message }}</div></div>
    @enderror

    @if ($exam->status !== 'published')
        <div class="portal-pending-banner">
            <div class="portal-pending-banner__icon"><i class="fa-solid fa-bullhorn"></i></div>
            <div class="portal-pending-banner__text">
                <strong>الاختبار غير منشور بعد</strong>
                <span>بعد اكتمال الأسئلة يمكنك نشره ليصبح متاحاً للطلاب.</span>
            </div>
            <button type="button" wire:click="publish" wire:confirm="نشر الاختبار للطلاب الآن؟" class="btn btn-sm btn-warning">نشر الاختبار</button>
        </div>
    @else
        <div class="exam-builder-published">
            <i class="fa-solid fa-circle-check"></i>
            <span>الاختبار منشور ومتاح للطلاب حسب إعدادات الجدول والوصول.</span>
        </div>
    @endif

    <div class="exam-builder-layout">
        <section class="portal-panel exam-builder-canvas">
            <div class="portal-panel__head">
                <div>
                    <h2 class="portal-panel__title"><i class="fa-solid fa-diagram-project"></i> مخطط الأسئلة</h2>
                    <p class="exam-builder-sub">{{ $stats['questions'] }} سؤال · {{ $stats['points'] }} درجة</p>
                </div>
            </div>
            <div class="portal-panel__body">
            @foreach ($this->examParts as $part)
                <div class="exam-builder-part" wire:key="part-{{ $part->id }}">
                    <div class="exam-builder-part__title">
                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3>{{ $part->title }}</h3>
                            <small>{{ $part->questions->count() }} سؤال</small>
                        </div>
                    </div>

                    @forelse ($part->questions as $question)
                        @php
                            $answerKey = $question->answer_key ?? [];
                            $correctKeys = collect((array) ($answerKey['correct'] ?? []))->map(fn ($key) => (string) $key);
                            if ($question->type === 'true_false' && isset($answerKey['correct'])) {
                                $correctKeys = collect([(string) $answerKey['correct']]);
                            }
                        @endphp
                        <article class="exam-builder-q" wire:key="question-{{ $question->pivot->id }}" x-data="{ open: false }" :class="{ 'is-open': open }">
                            <div class="exam-builder-q__main">
                                <span class="exam-builder-q__num">{{ $loop->iteration }}</span>
                                <div class="exam-builder-q__body">
                                    <div class="exam-builder-q__meta">
                                        <span>{{ ExamOptions::questionTypeLabel($question->type) }}</span>
                                        <span class="diff-{{ $question->difficulty }}">{{ ExamOptions::difficulties()[$question->difficulty] ?? $question->difficulty }}</span>
                                    </div>
                                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($question->prompt), 200) }}</p>
                                </div>
                                <strong class="exam-builder-q__points">{{ $question->pivot->points }} <small>درجة</small></strong>
                                <div class="exam-builder-q__actions">
                                    <button type="button" class="exam-builder-icon" @click="open = !open" :title="open ? 'إخفاء التفاصيل' : 'عرض الخيارات'" :aria-expanded="open.toString()">
                                        <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    </button>
                                    <button type="button" wire:click="detachQuestion({{ $question->pivot->id }})" wire:confirm="إزالة السؤال من هذا الاختبار؟ سيبقى في بنك الأسئلة." class="exam-builder-icon is-danger" title="إزالة">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="exam-builder-q__details" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                                @if (in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true))
                                    <div class="exam-builder-preview-options">
                                        @forelse ($question->options as $option)
                                            @php($isCorrect = $option->is_correct || $correctKeys->contains((string) $option->option_key))
                                            <div @class(['exam-builder-preview-option', 'is-correct' => $isCorrect])>
                                                <span class="exam-builder-preview-option__key" dir="ltr">{{ $option->option_key }}</span>
                                                <span>{{ $option->content }}</span>
                                                @if ($isCorrect)<small>الإجابة الصحيحة</small>@endif
                                            </div>
                                        @empty
                                            <p class="exam-builder-empty-hint">لا توجد خيارات مسجّلة لهذا السؤال.</p>
                                        @endforelse
                                    </div>
                                @elseif ($question->type === 'numeric')
                                    <div class="exam-builder-preview-box">
                                        <span>القيمة الصحيحة</span>
                                        <strong>{{ $answerKey['value'] ?? '—' }}</strong>
                                        <small>هامش الخطأ ± {{ $answerKey['tolerance'] ?? 0 }}</small>
                                    </div>
                                @elseif (in_array($question->type, ['essay', 'file_upload'], true))
                                    <div class="exam-builder-preview-box is-manual">
                                        <i class="fa-solid fa-user-pen"></i>
                                        <span>يحتاج تصحيحاً يدوياً بعد تسليم الطلاب.</span>
                                    </div>
                                @else
                                    <div class="exam-builder-preview-box">
                                        <span>نموذج الإجابة</span>
                                        <p>{{ collect($answerKey['accepted'] ?? $answerKey['blanks'] ?? $answerKey['order'] ?? $answerKey['matches'] ?? [])->flatten()->take(6)->join(' · ') ?: '—' }}</p>
                                    </div>
                                @endif

                                @if ($question->explanation)
                                    <div class="exam-builder-preview-box is-explain">
                                        <span>شرح الإجابة</span>
                                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($question->explanation), 220) }}</p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="portal-empty portal-empty--compact">
                            <div class="portal-empty__icon"><i class="fa-solid fa-list-check"></i></div>
                            <p>لا توجد أسئلة بعد</p>
                            <span class="portal-empty__hint">أضف سؤالاً من النموذج الجانبي أو من بنك المقرر</span>
                        </div>
                    @endforelse
                </div>
            @endforeach
            </div>
        </section>

        <aside class="portal-panel exam-builder-editor">
            <div class="portal-panel__body">
            <header class="exam-builder-editor__head">
                <span class="exam-builder-editor__icon"><i class="fa-solid fa-circle-plus"></i></span>
                <div>
                    <span>سؤال جديد</span>
                    <h2>إنشاء سؤال</h2>
                </div>
            </header>
            <p class="exam-builder-editor__hint">يُحفظ في بنك أسئلة المقرر ويُضاف مباشرة لهذا الاختبار.</p>

            <form wire:submit="saveQuestion" class="exam-builder-form">
                <label class="exam-field">
                    <span>نوع السؤال</span>
                    <select wire:model.live="questionType">
                        @foreach (ExamOptions::questionTypes() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="exam-field">
                    <span>نص السؤال *</span>
                    <textarea rows="4" wire:model="questionPrompt" placeholder="اكتب نص السؤال بوضوح..."></textarea>
                    @error('questionPrompt')<small>{{ $message }}</small>@enderror
                </label>

                <div class="exam-builder-form__row">
                    <label class="exam-field">
                        <span>الصعوبة</span>
                        <select wire:model="difficulty">
                            @foreach (ExamOptions::difficulties() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="exam-field">
                        <span>الدرجة</span>
                        <input type="number" min=".01" step=".01" wire:model="points">
                        @error('points')<small>{{ $message }}</small>@enderror
                    </label>
                </div>

                @if (in_array($questionType, ['single_choice', 'multiple_choice'], true))
                    <div class="exam-options-editor">
                        <div class="exam-options-editor__head">
                            <strong>الخيارات</strong>
                            <span>{{ $questionType === 'single_choice' ? 'حدد إجابة صحيحة واحدة' : 'يمكن تحديد أكثر من إجابة صحيحة' }}</span>
                        </div>
                        @foreach ($options as $index => $option)
                            @php($letter = chr(65 + $index))
                            <label @class([
                                'exam-option-editor-row',
                                'is-correct' => $questionType === 'single_choice'
                                    ? (string) $correctScalar === (string) $index
                                    : (bool) ($option['correct'] ?? false),
                            ]) wire:key="option-{{ $index }}">
                                <span class="exam-option-editor-row__key" dir="ltr">{{ $letter }}</span>
                                @if ($questionType === 'single_choice')
                                    <input type="radio" wire:model.live="correctScalar" value="{{ $index }}" name="correct-option" class="exam-option-editor-row__check">
                                @else
                                    <input type="checkbox" wire:model.live="options.{{ $index }}.correct" class="exam-option-editor-row__check">
                                @endif
                                <input type="text" wire:model="options.{{ $index }}.content" placeholder="نص الخيار {{ $letter }}">
                                @if (count($options) > 2)
                                    <button type="button" wire:click="removeOption({{ $index }})" class="exam-option-editor-row__remove" title="حذف"><i class="fa-solid fa-trash"></i></button>
                                @endif
                            </label>
                        @endforeach
                        @error('options')<small class="exam-error">{{ $message }}</small>@enderror
                        <button type="button" wire:click="addOption" class="btn btn-sm btn-outline-primary exam-options-editor__add">
                            <i class="fa-solid fa-plus"></i> إضافة خيار
                        </button>
                    </div>
                @elseif ($questionType === 'true_false')
                    <div class="exam-tf-editor">
                        <span class="exam-options-editor__head"><strong>الإجابة الصحيحة</strong></span>
                        <div class="exam-tf-editor__choices">
                            <label @class(['exam-tf-choice', 'is-active' => $correctScalar === 'true'])>
                                <input type="radio" wire:model.live="correctScalar" value="true">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>صح</span>
                            </label>
                            <label @class(['exam-tf-choice', 'is-active' => $correctScalar === 'false'])>
                                <input type="radio" wire:model.live="correctScalar" value="false">
                                <i class="fa-solid fa-circle-xmark"></i>
                                <span>خطأ</span>
                            </label>
                        </div>
                    </div>
                @elseif ($questionType === 'numeric')
                    <div class="exam-builder-form__row">
                        <label class="exam-field"><span>القيمة الصحيحة</span><input type="number" step="any" wire:model="correctScalar">@error('correctScalar')<small>{{ $message }}</small>@enderror</label>
                        <label class="exam-field"><span>هامش الخطأ ±</span><input type="number" min="0" step="any" wire:model="numericTolerance"></label>
                    </div>
                @elseif (! in_array($questionType, ['essay', 'file_upload'], true))
                    <label class="exam-field">
                        <span>نموذج الإجابة</span>
                        <textarea rows="5" wire:model="structuredAnswer" placeholder="{{ match ($questionType) {
                            'short_text' => 'إجابة مقبولة في كل سطر',
                            'fill_blank' => 'كل فراغ في سطر، والبدائل تفصل بـ |',
                            'matching' => 'كل مطابقة: العنصر => الإجابة',
                            'ordering' => 'العناصر بالترتيب الصحيح، كل عنصر في سطر',
                            default => '',
                        } }}"></textarea>
                        @error('structuredAnswer')<small>{{ $message }}</small>@enderror
                    </label>
                @else
                    <div class="exam-manual-note"><i class="fa-solid fa-user-pen"></i> هذا النوع يحتاج تصحيحاً يدوياً من المدرب بعد التسليم.</div>
                @endif

                <label class="exam-field">
                    <span>شرح الإجابة (اختياري)</span>
                    <textarea rows="3" wire:model="questionExplanation" placeholder="يظهر للطالب حسب سياسة المراجعة"></textarea>
                </label>

                <button type="submit" class="btn btn-primary exam-builder-form__submit" wire:loading.attr="disabled">
                    <i class="fa-solid fa-plus"></i>
                    <span wire:loading.remove wire:target="saveQuestion">إضافة السؤال</span>
                    <span wire:loading wire:target="saveQuestion">جاري الحفظ...</span>
                </button>
            </form>
            </div>
        </aside>
    </div>

    @if ($this->bankQuestions->isNotEmpty())
        <section class="portal-panel">
            <div class="portal-panel__head">
                <div>
                    <h2 class="portal-panel__title"><i class="fa-solid fa-database"></i> بنك أسئلة المقرر</h2>
                    <p class="exam-builder-sub">أسئلة منشورة غير مضافة لهذا الاختبار — اضغط «إضافة» لإرفاقها.</p>
                </div>
            </div>
            <div class="portal-panel__body">
                <div class="exam-bank-grid">
                    @foreach ($this->bankQuestions as $question)
                        <article class="exam-bank-card" wire:key="bank-{{ $question->id }}">
                            <div>
                                <span>{{ ExamOptions::questionTypeLabel($question->type) }}</span>
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($question->prompt), 140) }}</p>
                            </div>
                            <button type="button" wire:click="attachBankQuestion({{ $question->id }})" class="btn btn-sm btn-outline-primary">إضافة</button>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>

@push('styles')
<style>
[x-cloak]{display:none!important}
.portal-kpi-strip--4{grid-template-columns:repeat(4,minmax(0,1fr))}
.portal-dashboard--instructor .portal-kpi-v2--students{border-right-color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--grades{border-right-color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--week{border-right-color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--sections{border-right-color:#0d9488}
.portal-dashboard--instructor .portal-kpi-v2--students .portal-kpi-v2__icon{background:#eff6ff;color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--grades .portal-kpi-v2__icon{background:#fffbeb;color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--week .portal-kpi-v2__icon{background:#ecfdf5;color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--sections .portal-kpi-v2__icon{background:#f0fdfa;color:#0d9488}

.exam-builder-sub{margin:.25rem 0 0;font-size:.72rem;color:#64748b}
.exam-builder-page .portal-panel__body{padding:1rem 1.15rem 1.15rem}
.exam-builder-published{display:flex;align-items:center;gap:.55rem;padding:.75rem 1rem;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:#166534;font-size:.78rem;font-weight:700}
.exam-builder-layout{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(21rem,.7fr);gap:1rem;align-items:start}

.exam-builder-part{margin-bottom:1rem}
.exam-builder-part__title{display:flex;align-items:center;gap:.7rem;margin-bottom:.7rem}
.exam-builder-part__title>span{display:grid;place-items:center;width:2.1rem;height:2.1rem;border-radius:10px;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff;font-size:.72rem;font-weight:900}
.exam-builder-part__title h3{margin:0;font-size:.9rem;color:#0f172a}
.exam-builder-part__title small{color:#64748b;font-size:.68rem}

.exam-builder-q{margin-bottom:.55rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff;overflow:hidden}
.exam-builder-q.is-open{border-color:#86efac;box-shadow:0 0 0 1px rgba(22,163,74,.08)}
.exam-builder-q__main{display:grid;grid-template-columns:2.1rem minmax(0,1fr) auto auto;align-items:center;gap:.7rem;padding:.8rem .9rem}
.exam-builder-q__num{display:grid;place-items:center;width:2.1rem;height:2.1rem;border-radius:10px;background:#ecfdf5;color:#166534;font-size:.72rem;font-weight:900}
.exam-builder-q__meta{display:flex;flex-wrap:wrap;gap:.35rem}
.exam-builder-q__meta span{padding:.15rem .45rem;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.62rem;font-weight:800}
.exam-builder-q__meta .diff-easy{background:#ecfdf5;color:#166534}
.exam-builder-q__meta .diff-medium{background:#eff6ff;color:#1d4ed8}
.exam-builder-q__meta .diff-hard{background:#fff7ed;color:#c2410c}
.exam-builder-q__meta .diff-expert{background:#fef2f2;color:#b91c1c}
.exam-builder-q__body p{margin:.35rem 0 0;color:#334155;font-size:.8rem;line-height:1.65}
.exam-builder-q__points{font-size:.78rem;color:#0f172a;white-space:nowrap}
.exam-builder-q__points small{color:#64748b;font-weight:700}
.exam-builder-q__actions{display:flex;gap:.3rem}
.exam-builder-icon{width:2rem;height:2rem;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#475569;display:grid;place-items:center}
.exam-builder-icon.is-danger{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
.exam-builder-q__details{padding:0 .9rem .9rem;border-top:1px solid #f1f5f9}
.exam-builder-preview-options{display:grid;gap:.4rem;padding-top:.75rem}
.exam-builder-preview-option{display:flex;align-items:center;gap:.55rem;padding:.65rem .75rem;border:1px solid #e2e8f0;border-radius:11px;background:#f8fafc;font-size:.78rem;color:#475569}
.exam-builder-preview-option__key{display:inline-grid;place-items:center;min-width:1.55rem;height:1.55rem;padding:0 .3rem;border-radius:8px;background:#fff;border:1px solid #e2e8f0;font-size:.68rem;font-weight:900}
.exam-builder-preview-option.is-correct{border-color:#86efac;background:#f0fdf4;color:#166534}
.exam-builder-preview-option small{margin-inline-start:auto;padding:.12rem .4rem;border-radius:999px;background:#dcfce7;font-size:.58rem;font-weight:900}
.exam-builder-preview-box{margin-top:.75rem;padding:.7rem .8rem;border-radius:11px;background:#f8fafc;border:1px solid #e8eef3}
.exam-builder-preview-box.is-manual{display:flex;align-items:center;gap:.45rem;background:#fff7ed;border-color:#fed7aa;color:#9a3412;font-size:.75rem}
.exam-builder-preview-box.is-explain{background:#fffbeb;border-color:#fde68a}
.exam-builder-preview-box span{display:block;font-size:.62rem;font-weight:900;color:#64748b;margin-bottom:.25rem}
.exam-builder-preview-box strong{font-size:.95rem;color:#0f172a}
.exam-builder-preview-box p,.exam-builder-preview-box small{margin:.2rem 0 0;font-size:.76rem;color:#334155;line-height:1.6}
.exam-builder-empty-hint{margin:.75rem 0 0;font-size:.72rem;color:#94a3b8}

.exam-builder-editor{position:sticky;top:1rem}
.exam-builder-editor__head{display:flex;align-items:center;gap:.7rem;margin-bottom:.35rem}
.exam-builder-editor__icon{width:2.5rem;height:2.5rem;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff}
.exam-builder-editor__head span{display:block;font-size:.65rem;font-weight:800;color:#64748b}
.exam-builder-editor__head h2{margin:0;font-size:1rem;color:#0f172a}
.exam-builder-editor__hint{margin:0 0 1rem;font-size:.72rem;color:#64748b}
.exam-builder-form{display:flex;flex-direction:column;gap:.75rem}
.exam-builder-form__row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
.exam-field{display:flex;flex-direction:column;gap:.3rem}
.exam-field>span{font-size:.7rem;font-weight:900;color:#334155}
.exam-field input,.exam-field select,.exam-field textarea{padding:.65rem .75rem;border:1px solid #dbe4ee;border-radius:10px;font:inherit;font-size:.8rem;background:#fff}
.exam-field small,.exam-error{color:#b91c1c;font-size:.66rem}

.exam-options-editor{display:flex;flex-direction:column;gap:.45rem;padding:.75rem;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}
.exam-options-editor__head{display:flex;flex-direction:column;gap:.1rem;margin-bottom:.2rem}
.exam-options-editor__head strong{font-size:.78rem;color:#0f172a}
.exam-options-editor__head span{font-size:.65rem;color:#64748b}
.exam-option-editor-row{display:grid;grid-template-columns:1.7rem auto minmax(0,1fr) auto;align-items:center;gap:.45rem;padding:.55rem .6rem;border:1px solid #e2e8f0;border-radius:11px;background:#fff;cursor:pointer}
.exam-option-editor-row.is-correct{border-color:#86efac;background:#f0fdf4}
.exam-option-editor-row__key{display:grid;place-items:center;width:1.55rem;height:1.55rem;border-radius:8px;background:#f1f5f9;font-size:.68rem;font-weight:900;color:#334155}
.exam-option-editor-row.is-correct .exam-option-editor-row__key{background:#dcfce7;color:#166534}
.exam-option-editor-row__check{accent-color:#1b8354}
.exam-option-editor-row input[type=text]{width:100%;padding:.5rem .6rem;border:1px solid #e2e8f0;border-radius:8px;font:inherit;font-size:.78rem;background:#fff}
.exam-option-editor-row__remove{border:0;background:transparent;color:#b91c1c;width:1.6rem;height:1.6rem}
.exam-options-editor__add{align-self:flex-start;margin-top:.15rem}

.exam-tf-editor{display:grid;gap:.45rem}
.exam-tf-editor__choices{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.exam-tf-choice{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.85rem .75rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer;font-weight:800;color:#475569}
.exam-tf-choice input{display:none}
.exam-tf-choice.is-active{border-color:#86efac;background:#f0fdf4;color:#166534}
.exam-manual-note{display:flex;align-items:center;gap:.45rem;padding:.75rem;border-radius:11px;background:#fff7ed;color:#9a3412;font-size:.74rem;border:1px solid #fed7aa}
.exam-builder-form__submit{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;width:100%;padding:.75rem 1rem}

.exam-bank-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(17rem,1fr));gap:.65rem}
.exam-bank-card{display:flex;align-items:center;justify-content:space-between;gap:.65rem;padding:.85rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}
.exam-bank-card span{display:inline-block;font-size:.62rem;font-weight:800;color:#64748b;margin-bottom:.25rem}
.exam-bank-card p{margin:0;font-size:.75rem;color:#334155;line-height:1.55}

@media(max-width:991.98px){.portal-kpi-strip--4{grid-template-columns:repeat(2,minmax(0,1fr))}.exam-builder-layout{grid-template-columns:1fr}.exam-builder-editor{position:static}}
@media(max-width:640px){
    .exam-builder-q__main{grid-template-columns:2.1rem minmax(0,1fr);gap:.55rem}
    .exam-builder-q__points,.exam-builder-q__actions{grid-column:2}
    .exam-builder-form__row,.exam-tf-editor__choices{grid-template-columns:1fr}
    .exam-option-editor-row{grid-template-columns:1.5rem auto minmax(0,1fr)}
}
</style>
@endpush

@include('partials.instructor.shell-end')
