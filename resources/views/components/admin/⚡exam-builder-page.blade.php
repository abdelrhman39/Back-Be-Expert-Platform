<?php

use App\Models\Exam;
use App\Models\ExamPartQuestion;
use App\Models\ExamQuestion;
use App\Services\ExamQuestionAuthoringService;
use App\Services\ExamQuestionBankService;
use App\Services\ExamPublicationService;
use App\Support\ExamOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('بناء الاختبار | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    public Exam $exam;
    public string $questionType = 'single_choice';
    public string $questionPrompt = '';
    public string $questionPromptEn = '';
    public string $questionExplanation = '';
    public string $questionExplanationEn = '';
    public string $difficulty = 'medium';
    public string $points = '1';
    public array $options = [];
    public string $correctScalar = '';
    public string $structuredAnswer = '';
    public string $structuredAnswerEn = '';
    public string $numericTolerance = '0';
    public string $flashMessage = '';
    public ?int $editingLinkId = null;
    public string $questionCategoryId = '';
    public string $questionTags = '';
    public string $bankSearch = '';
    public string $bankCategoryId = '';
    public string $bankType = '';
    public string $bankDifficulty = '';
    public string $newCategoryName = '';
    public string $newCategoryParentId = '';
    public string $randomCategoryId = '';
    public string $randomType = '';
    public string $randomDifficulty = '';
    public string $randomCount = '5';
    public string $randomPoints = '1';
    public $importFile = null;

    public function mount(Exam $exam): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $this->exam = $exam->load(['section', 'course']);
        $this->resetQuestionForm();
        $pool = $this->exam->parts()->orderBy('sort_order')->first()?->pool_filters ?? [];
        $this->randomCategoryId = filled($pool['category_id'] ?? null) ? (string) $pool['category_id'] : '';
        $this->randomType = (string) ($pool['type'] ?? '');
        $this->randomDifficulty = (string) ($pool['difficulty'] ?? '');
        $this->randomCount = (string) ($this->exam->parts()->orderBy('sort_order')->first()?->questions_to_draw ?? 5);
        $this->randomPoints = (string) ($pool['points_per_question'] ?? 1);
    }

    #[Computed]
    public function parts()
    {
        return $this->exam->parts()->with('questions.options')->get();
    }

    #[Computed]
    public function bankQuestions()
    {
        return app(ExamQuestionBankService::class)
            ->bankQuery(
                $this->exam,
                search: $this->bankSearch,
                categoryId: $this->bankCategoryId !== '' ? (int) $this->bankCategoryId : null,
                type: $this->bankType,
                difficulty: $this->bankDifficulty,
            )
            ->latest()
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function questionCategories()
    {
        return app(ExamQuestionBankService::class)->categoriesForCourse($this->exam->course_id);
    }

    #[Computed]
    public function poolCandidateCount(): int
    {
        return app(ExamQuestionBankService::class)
            ->bankQuery(
                $this->exam,
                categoryId: $this->randomCategoryId !== '' ? (int) $this->randomCategoryId : null,
                type: $this->randomType,
                difficulty: $this->randomDifficulty,
            )
            ->count();
    }

    #[Computed]
    public function examQuestionCount(): int
    {
        return $this->parts->sum(function ($part): int {
            $fixed = $part->questions->count();
            $hasAdvancedPool = $part->questions_to_draw && ! empty($part->pool_filters['question_ids'] ?? []);

            return $fixed + ($hasAdvancedPool ? (int) $part->questions_to_draw : 0);
        });
    }

    public function updatedBankSearch(): void
    {
        unset($this->bankQuestions);
    }

    public function updatedBankCategoryId(): void
    {
        unset($this->bankQuestions);
    }

    public function updatedBankType(): void
    {
        unset($this->bankQuestions);
    }

    public function updatedBankDifficulty(): void
    {
        unset($this->bankQuestions);
    }

    public function updatedRandomCategoryId(): void
    {
        unset($this->poolCandidateCount);
    }

    public function updatedRandomType(): void
    {
        unset($this->poolCandidateCount);
    }

    public function updatedRandomDifficulty(): void
    {
        unset($this->poolCandidateCount);
    }

    public function updatedQuestionType(): void
    {
        $this->resetQuestionForm(false);
        $this->resetValidation();
    }

    public function updatedCorrectScalar(): void
    {
        $this->resetValidation(['options', 'correctScalar']);
    }

    public function updatedOptions(): void
    {
        $this->resetValidation('options');
    }

    public function updatedQuestionPrompt(): void
    {
        $this->resetValidation('questionPrompt');
    }

    public function addOption(): void
    {
        $this->options[] = ['content' => '', 'content_en' => '', 'correct' => false];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function saveQuestion(ExamQuestionAuthoringService $authoring): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        $this->validate([
            'questionType' => ['required', Rule::in(array_keys(ExamOptions::questionTypes()))],
            'questionPrompt' => [Rule::requiredIf($this->exam->language_policy !== 'en_only'), 'nullable', 'string', 'max:30000'],
            'questionPromptEn' => [Rule::requiredIf($this->exam->language_policy !== 'ar_only'), 'nullable', 'string', 'max:30000'],
            'questionExplanation' => ['nullable', 'string', 'max:20000'],
            'questionExplanationEn' => ['nullable', 'string', 'max:20000'],
            'difficulty' => ['required', Rule::in(array_keys(ExamOptions::difficulties()))],
            'points' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'options' => ['array', 'max:50'],
            'options.*.content' => ['nullable', 'string', 'max:5000'],
            'options.*.content_en' => ['nullable', 'string', 'max:5000'],
            'correctScalar' => ['nullable', 'string', 'max:5000'],
            'structuredAnswer' => ['nullable', 'string', 'max:30000'],
            'numericTolerance' => ['nullable', 'numeric', 'min:0'],
            'questionCategoryId' => [
                'nullable',
                Rule::exists('exam_question_categories', 'id')->where(
                    fn ($query) => $query->whereNull('course_id')->orWhere('course_id', $this->exam->course_id)
                ),
            ],
            'questionTags' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'questionPrompt' => 'نص السؤال',
            'points' => 'الدرجة',
            'structuredAnswer' => 'نموذج الإجابة',
        ]);

        $link = $this->editingLinkId
            ? ExamPartQuestion::query()
                ->whereKey($this->editingLinkId)
                ->whereIn('exam_part_id', $this->exam->parts()->pluck('id'))
                ->firstOrFail()
            : null;
        $oldValues = $link?->question?->only(['type', 'prompt', 'difficulty', 'default_points']);

        $arguments = [
            'exam' => $this->exam,
            'actor' => auth()->user(),
            'type' => $this->questionType,
            'prompt' => $this->questionPrompt ?: $this->questionPromptEn,
            'promptEn' => $this->questionPromptEn ?: null,
            'explanation' => $this->questionExplanation ?: null,
            'explanationEn' => $this->questionExplanationEn ?: null,
            'difficulty' => $this->difficulty,
            'points' => (float) $this->points,
            'options' => collect($this->options)->map(fn (array $option) => [
                ...$option,
                'content' => $option['content'] ?: ($option['content_en'] ?? ''),
            ])->all(),
            'correctScalar' => $this->correctScalar !== '' ? $this->correctScalar : null,
            'structuredAnswer' => $this->structuredAnswer ?: null,
            'structuredAnswerEn' => $this->structuredAnswerEn ?: null,
            'numericTolerance' => (float) $this->numericTolerance,
            'categoryId' => $this->questionCategoryId !== '' ? (int) $this->questionCategoryId : null,
            'tags' => collect(explode(',', $this->questionTags))
                ->map(fn ($tag) => trim($tag))
                ->filter()
                ->unique()
                ->take(20)
                ->values()
                ->all(),
        ];
        $question = $link
            ? $authoring->updateAttached(...array_merge(['link' => $link], $arguments))
            : $authoring->createAndAttach(...$arguments);

        app(\App\Services\AuditLogService::class)->log(
            action: $link ? 'exam.question_updated' : 'exam.question_created',
            descriptionAr: ($link ? 'تعديل' : 'إضافة').' سؤال في اختبار «'.$this->exam->title.'»',
            group: 'exams',
            subject: $question,
            subjectLabel: \Illuminate\Support\Str::limit(strip_tags($question->prompt), 100),
            oldValues: $oldValues,
            newValues: ['exam_id' => $this->exam->id, 'type' => $question->type, 'points' => $this->points],
        );

        $this->exam->refresh();
        unset($this->parts, $this->bankQuestions);
        $this->resetQuestionForm();
        $this->flashMessage = $link
            ? 'تم تحديث السؤال مع الحفاظ على نسخ المحاولات السابقة.'
            : 'تمت إضافة السؤال وحفظه في بنك المقرر.';
    }

    public function editQuestion(int $linkId): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        $link = ExamPartQuestion::query()
            ->with('question.options')
            ->whereKey($linkId)
            ->whereIn('exam_part_id', $this->exam->parts()->pluck('id'))
            ->firstOrFail();
        $question = $link->question;
        $key = $question->answer_key ?? [];

        $this->editingLinkId = $link->id;
        $this->questionType = $question->type;
        $this->questionPrompt = $question->prompt;
        $this->questionPromptEn = $question->prompt_en ?? '';
        $this->questionExplanation = $question->explanation ?? '';
        $this->questionExplanationEn = $question->explanation_en ?? '';
        $this->difficulty = $question->difficulty;
        $this->points = (string) $link->points;
        $this->questionCategoryId = $question->category_id ? (string) $question->category_id : '';
        $this->questionTags = implode(', ', $question->tags ?? []);
        $this->options = $question->options->map(fn ($option) => [
            'content' => $option->content,
            'content_en' => $option->content_en ?? '',
            'correct' => $option->is_correct,
        ])->values()->all();
        $this->correctScalar = '';
        $this->structuredAnswer = '';
        $this->structuredAnswerEn = '';
        $this->numericTolerance = '0';

        if ($question->type === 'single_choice') {
            $correctKey = (string) ($key['correct'] ?? '');
            $this->correctScalar = (string) $question->options->search(
                fn ($option) => $option->option_key === $correctKey
            );
        } elseif ($question->type === 'true_false') {
            $this->correctScalar = (string) ($key['correct'] ?? 'true');
        } elseif ($question->type === 'numeric') {
            $this->correctScalar = (string) ($key['value'] ?? '');
            $this->numericTolerance = (string) ($key['tolerance'] ?? 0);
        } elseif ($question->type === 'short_text') {
            $this->structuredAnswer = implode("\n", $key['accepted'] ?? []);
        } elseif ($question->type === 'fill_blank') {
            $this->structuredAnswer = collect($key['blanks'] ?? [])
                ->map(fn ($accepted) => implode('|', (array) $accepted))
                ->implode("\n");
        } elseif ($question->type === 'matching') {
            $this->structuredAnswer = $question->options
                ->map(fn ($option) => $option->content.' => '.($option->match_data['target'] ?? ''))
                ->implode("\n");
            $this->structuredAnswerEn = $question->options
                ->filter(fn ($option) => filled($option->content_en))
                ->map(fn ($option) => $option->content_en.' => '.($option->match_data_en['target'] ?? ''))
                ->implode("\n");
        } elseif ($question->type === 'ordering') {
            $optionsByKey = $question->options->keyBy('option_key');
            $this->structuredAnswer = collect($key['order'] ?? [])
                ->map(fn ($optionKey) => $optionsByKey->get($optionKey)?->content)
                ->filter()
                ->implode("\n");
            $this->structuredAnswerEn = collect($key['order'] ?? [])
                ->map(fn ($optionKey) => $optionsByKey->get($optionKey)?->content_en)
                ->filter()
                ->implode("\n");
        }

        $this->resetValidation();
        $this->dispatch('exam-editor-opened');
    }

    public function cancelEditing(): void
    {
        $this->resetQuestionForm();
        $this->resetValidation();
    }

    public function reorderQuestions(int $partId, array $linkIds): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        $part = $this->exam->parts()->whereKey($partId)->firstOrFail();
        $expected = $part->questionLinks()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
        $provided = collect($linkIds)->map(fn ($id) => (int) $id)->unique()->sort()->values();

        abort_unless($expected->all() === $provided->all(), 422);

        DB::transaction(function () use ($part, $linkIds): void {
            foreach (array_values($linkIds) as $index => $linkId) {
                $part->questionLinks()->whereKey($linkId)->update(['sort_order' => $index + 1]);
            }
        });

        unset($this->parts);
        $this->flashMessage = 'تم حفظ ترتيب الأسئلة.';
    }

    public function attachQuestion(int $questionId, ExamQuestionBankService $bank): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        $question = ExamQuestion::query()
            ->where('course_id', $this->exam->course_id)
            ->where('status', 'published')
            ->findOrFail($questionId);
        $part = $this->exam->parts()->orderBy('sort_order')->firstOrFail();
        $pool = $part->pool_filters ?? [];

        DB::transaction(function () use ($part, $question, $pool, $bank): void {
            $part->questionLinks()->firstOrCreate(
                ['question_id' => $question->id],
                [
                    'points' => $question->default_points,
                    'sort_order' => $part->questionLinks()->max('sort_order') + 1,
                    'is_required' => true,
                ]
            );

            if ($part->questions_to_draw && ! empty($pool['question_ids'])) {
                $bank->configureRandomPool(
                    $this->exam,
                    $part,
                    auth()->user(),
                    (int) $part->questions_to_draw,
                    (float) ($pool['points_per_question'] ?? 1),
                    filled($pool['category_id'] ?? null) ? (int) $pool['category_id'] : null,
                    (string) ($pool['type'] ?? ''),
                    (string) ($pool['difficulty'] ?? ''),
                );
            } else {
                $this->exam->refreshTotalPoints();
            }
        });

        $this->exam->refresh();
        unset($this->parts, $this->bankQuestions, $this->poolCandidateCount);
        $this->flashMessage = 'تمت إضافة السؤال من البنك.';
    }

    public function createCategory(ExamQuestionBankService $bank): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:160'],
            'newCategoryParentId' => [
                'nullable',
                Rule::exists('exam_question_categories', 'id')->where(
                    fn ($query) => $query->whereNull('course_id')->orWhere('course_id', $this->exam->course_id)
                ),
            ],
        ], [], ['newCategoryName' => 'اسم التصنيف']);

        $category = $bank->createCategory(
            $this->exam,
            $this->newCategoryName,
            $this->newCategoryParentId !== '' ? (int) $this->newCategoryParentId : null,
        );
        $this->questionCategoryId = (string) $category->id;
        $this->newCategoryName = '';
        $this->newCategoryParentId = '';
        unset($this->questionCategories, $this->bankQuestions, $this->poolCandidateCount);
        $this->flashMessage = 'تم إنشاء التصنيف وأصبح جاهزاً لتنظيم الأسئلة.';
    }

    public function saveRandomPool(ExamQuestionBankService $bank): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $this->validate([
            'randomCategoryId' => ['nullable', 'integer'],
            'randomType' => ['nullable', Rule::in(array_keys(ExamOptions::questionTypes()))],
            'randomDifficulty' => ['nullable', Rule::in(array_keys(ExamOptions::difficulties()))],
            'randomCount' => ['required', 'integer', 'min:1', 'max:500'],
            'randomPoints' => ['required', 'numeric', 'min:0.01', 'max:10000'],
        ]);

        $part = $this->exam->parts()->orderBy('sort_order')->firstOrFail();
        $available = $bank->configureRandomPool(
            $this->exam,
            $part,
            auth()->user(),
            (int) $this->randomCount,
            (float) $this->randomPoints,
            $this->randomCategoryId !== '' ? (int) $this->randomCategoryId : null,
            $this->randomType,
            $this->randomDifficulty,
        );
        $this->exam->refresh();
        unset($this->parts, $this->poolCandidateCount);
        $this->flashMessage = "تم حفظ المجموعة العشوائية: اختيار {$this->randomCount} من {$available} سؤال مطابق.";
    }

    public function disableRandomPool(ExamQuestionBankService $bank): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $part = $this->exam->parts()->orderBy('sort_order')->firstOrFail();
        $bank->disableRandomPool($this->exam, $part);
        $this->exam->refresh();
        unset($this->parts, $this->poolCandidateCount);
        $this->flashMessage = 'تم إيقاف المجموعة العشوائية؛ الأسئلة الثابتة لم تتأثر.';
    }

    public function importQuestions(
        ExamQuestionBankService $bank,
        ExamQuestionAuthoringService $authoring,
    ): void {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [], ['importFile' => 'ملف CSV']);

        $count = $bank->importCsv($this->exam, auth()->user(), $this->importFile, $authoring);
        $this->importFile = null;
        unset($this->bankQuestions, $this->questionCategories, $this->poolCandidateCount);
        $this->flashMessage = "تم استيراد {$count} سؤال إلى بنك المقرر بنجاح.";
    }

    public function exportQuestions(ExamQuestionBankService $bank)
    {
        abort_unless(auth()->user()?->canAdmin('exams.view'), 403);
        $exam = $this->exam;

        return response()->streamDownload(function () use ($bank, $exam): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, [
                'type', 'prompt', 'explanation', 'difficulty', 'points', 'category',
                'tags', 'options', 'correct_answer', 'structured_answer', 'numeric_tolerance',
            ]);

            foreach ($bank->exportRows($exam) as $row) {
                fputcsv($stream, array_map(function ($value) {
                    $value = (string) ($value ?? '');

                    return preg_match('/^[=+\-@]/u', $value) ? "'".$value : $value;
                }, $row));
            }

            fclose($stream);
        }, 'question-bank-exam-'.$exam->id.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function detachQuestion(int $linkId): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        ExamPartQuestion::query()
            ->whereKey($linkId)
            ->whereIn('exam_part_id', $this->exam->parts()->pluck('id'))
            ->delete();
        $this->exam->refreshTotalPoints();
        $this->exam->refresh();
        unset($this->parts, $this->bankQuestions);
        $this->flashMessage = 'تمت إزالة السؤال من الاختبار مع إبقائه في البنك.';
    }

    public function publish(ExamPublicationService $publications): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $publication = $publications->publish($this->exam, auth()->user());
        $this->exam->refresh();
        $this->flashMessage = "تم نشر النسخة {$publication->version} وتثبيت محتواها وإشعار الطلاب.";
    }

    private function resetQuestionForm(bool $clearPrompt = true): void
    {
        if ($clearPrompt) {
            $this->editingLinkId = null;
            $this->questionPrompt = '';
            $this->questionPromptEn = '';
            $this->questionExplanation = '';
            $this->questionExplanationEn = '';
            $this->questionCategoryId = '';
            $this->questionTags = '';
        }

        $this->difficulty = 'medium';
        $this->points = '1';
        $this->correctScalar = $this->questionType === 'true_false' ? 'true' : '';
        $this->structuredAnswer = '';
        $this->structuredAnswerEn = '';
        $this->numericTolerance = '0';
        $this->options = in_array($this->questionType, ['single_choice', 'multiple_choice'], true)
            ? array_fill(0, 4, ['content' => '', 'content_en' => '', 'correct' => false])
            : [];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.exams'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.exams'), 'label' => 'الاختبارات'],
        ['label' => 'بناء الاختبار'],
    ],
])

<div class="admin-exam-builder-hero">
    <div class="admin-exam-builder-hero__main">
        <span class="admin-exam-builder-hero__eyebrow"><i class="fa-solid fa-wand-magic-sparkles"></i> مساحة بناء الاختبار</span>
        <h1>{{ $exam->title }}</h1>
        <p>{{ $exam->section?->name }} <i></i> {{ $exam->course?->name_ar }}</p>
        <div class="admin-exam-builder-hero__stats">
            <span><strong>{{ $this->examQuestionCount }}</strong> سؤال في نموذج الطالب</span>
            <span><strong>{{ $exam->total_points }}</strong> درجة</span>
            <span><strong>{{ $exam->duration_minutes ?: '∞' }}</strong> دقيقة</span>
            <span><strong>{{ match($exam->attempt_policy) { 'unlimited' => '∞', 'limited' => (string) $exam->max_attempts, default => '1' } }}</strong> محاولة</span>
            <span><strong>{{ $exam->grade_selection === 'latest' ? 'آخر محاولة' : 'أعلى درجة' }}</strong></span>
        </div>
    </div>
    <div class="admin-exam-builder-actions">
        <button type="button" class="admin-exam-focus-btn" @click="window.dispatchEvent(new CustomEvent('open-exam-help', { detail: { section: 'overview' } }))" title="دليل بناء الاختبار">
            <i class="fa-regular fa-circle-question"></i><span>دليل الاستخدام</span>
        </button>
        <button type="button" class="admin-exam-focus-btn" onclick="document.getElementById('admin-sidebar-collapse')?.click()" title="توسيع مساحة العمل">
            <i class="fa-solid fa-expand"></i><span>وضع التركيز</span>
        </button>
        <a href="{{ route('admin.exams.preview', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-eye"></i> المعاينة والفحص</a>
        <a href="{{ route('admin.exams.integrity', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-shield-halved"></i> النزاهة</a>
        <a href="{{ route('admin.exams.edit', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm">الإعدادات</a>
        @if($exam->status !== 'published')<button type="button" wire:click="publish" class="admin-btn-primary admin-btn-primary--sm">نشر الاختبار</button>@else<span class="admin-badge admin-badge--success">منشور</span>@endif
    </div>
</div>

@if($flashMessage)<div class="admin-alert admin-alert--success is-visible">{{ $flashMessage }}</div>@endif
@error('publish')<div class="admin-alert admin-alert--danger is-visible">{{ $message }}</div>@enderror

<div class="admin-exam-builder-layout">
    <section class="admin-crud-card admin-exam-canvas">
        <div class="admin-crud-card__head admin-exam-canvas__head">
            <div><h2>مخطط الأسئلة</h2><p>اسحب المقبض لتغيير الترتيب؛ يتم الحفظ تلقائياً عند الإفلات.</p></div>
            <span class="admin-exam-live-status"><i class="fa-solid fa-cloud-arrow-up"></i> ترتيب محفوظ</span>
        </div>
        @foreach($this->parts as $part)
            <div
                class="admin-exam-part"
                wire:key="admin-part-{{ $part->id }}"
                x-data="{
                    dragged: null,
                    start(event) {
                        this.dragged = event.target.closest('[data-question-link-id]');
                        if (!this.dragged) return;
                        this.dragged.classList.add('is-dragging');
                        event.dataTransfer.effectAllowed = 'move';
                    },
                    move(event) {
                        const target = event.currentTarget;
                        if (!this.dragged || target === this.dragged) return;
                        const rect = target.getBoundingClientRect();
                        target.parentNode.insertBefore(this.dragged, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling);
                    },
                    finish(partId) {
                        if (!this.dragged) return;
                        this.dragged.classList.remove('is-dragging');
                        const ids = Array.from(this.$refs.list.querySelectorAll('[data-question-link-id]')).map(item => Number(item.dataset.questionLinkId));
                        this.dragged = null;
                        $wire.reorderQuestions(partId, ids);
                    }
                }"
            >
                <div class="admin-exam-part__title"><span>{{ str_pad((string)$loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><h3>{{ $part->title }}</h3><small>{{ $part->questions->count() }} ثابت@if($part->questions_to_draw && !empty($part->pool_filters['question_ids'] ?? [])) + {{ $part->questions_to_draw }} عشوائي@endif</small></div></div>
                <div class="admin-exam-sortable" x-ref="list">
                @forelse($part->questions as $question)
                    <article
                        class="admin-exam-question"
                        wire:key="admin-exam-question-{{ $question->pivot->id }}"
                        data-question-link-id="{{ $question->pivot->id }}"
                        @dragover.prevent="move($event)"
                        x-data="{ open: false }"
                        :class="{ 'is-expanded': open }"
                    >
                        <button type="button" class="admin-exam-drag-handle" draggable="true" @dragstart="start($event)" @dragend="finish({{ $part->id }})" title="اسحب لتغيير الترتيب" aria-label="اسحب لتغيير ترتيب السؤال">
                            <i class="fa-solid fa-grip-vertical"></i>
                        </button>
                        <span class="admin-exam-question__number">{{ $loop->iteration }}</span>
                        <div class="admin-exam-question__content">
                            <div class="admin-exam-question__meta">
                                <span>{{ ExamOptions::questionTypeLabel($question->type) }}</span>
                                <span class="difficulty-{{ $question->difficulty }}">{{ ExamOptions::difficulties()[$question->difficulty] ?? $question->difficulty }}</span>
                            </div>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($question->prompt), 220) }}</p>
                        </div>
                        <strong class="admin-exam-question__points">{{ $question->pivot->points }} <small>درجة</small></strong>
                        <div class="admin-exam-question__actions">
                            <button type="button" @click="open = !open" class="admin-exam-preview" :class="{ 'is-active': open }" :title="open ? 'إخفاء تفاصيل السؤال' : 'عرض تفاصيل السؤال'" :aria-expanded="open.toString()">
                                <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                            <button type="button" wire:click="editQuestion({{ $question->pivot->id }})" class="admin-exam-edit" title="تعديل السؤال"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" wire:click="detachQuestion({{ $question->pivot->id }})" wire:confirm="إزالة السؤال من هذا الاختبار؟ سيبقى محفوظاً في بنك الأسئلة." class="admin-exam-remove" title="إزالة السؤال"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <div class="admin-exam-question-details" x-show="open" x-transition.opacity.duration.180ms x-cloak>
                            @include('partials.admin.exam-question-details', ['question' => $question])
                        </div>
                    </article>
                @empty
                    <div class="admin-exam-empty"><i class="fa-solid fa-list-check"></i><strong>ابدأ بإضافة أول سؤال</strong><p>استخدم محرر السؤال أو اختر سؤالاً جاهزاً من البنك.</p></div>
                @endforelse
                </div>
            </div>
        @endforeach
    </section>

    <section @class(['admin-crud-card', 'admin-exam-question-form', 'is-editing' => $editingLinkId]) id="admin-exam-question-editor">
        <div class="admin-exam-editor__head">
            <div class="admin-exam-editor__icon"><i class="fa-solid {{ $editingLinkId ? 'fa-pen-to-square' : 'fa-circle-plus' }}"></i></div>
            <div><span>{{ $editingLinkId ? 'وضع التعديل' : 'سؤال جديد' }}</span><h2>{{ $editingLinkId ? 'تعديل السؤال' : 'إنشاء سؤال' }}</h2></div>
            @if($editingLinkId)<button type="button" wire:click="cancelEditing" class="admin-exam-editor__close" title="إلغاء التعديل">×</button>@endif
        </div>
        @if($editingLinkId && $exam->attempts()->exists())
            <div class="admin-exam-version-note"><i class="fa-solid fa-shield-halved"></i><span>التعديل سيطبق على المحاولات الجديدة فقط؛ المحاولات السابقة محفوظة بنسختها الأصلية.</span></div>
        @endif
        <form wire:submit="saveQuestion">
            @if($errors->hasAny(['questionPrompt','points','options','correctScalar','structuredAnswer','numericTolerance']))
                <div class="admin-exam-validation-summary" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div><strong>تعذر حفظ السؤال</strong><span>راجع الحقول المحددة باللون الأحمر ثم حاول مرة أخرى.</span></div>
                </div>
            @endif
            <div class="admin-exam-form-row">
                <label class="admin-field">
                    <span>تصنيف السؤال</span>
                    <select class="admin-control" wire:model="questionCategoryId">
                        <option value="">بدون تصنيف</option>
                        @foreach($this->questionCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->parent_id ? '↳ ' : '' }}{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span>وسوم البحث</span>
                    <input type="text" class="admin-control" wire:model="questionTags" placeholder="مثال: الوحدة الأولى، أساسيات">
                </label>
            </div>
            <label @class(['admin-field','has-error'=>$errors->has('questionType')])><span>نوع السؤال</span><select @class(['admin-control','is-invalid'=>$errors->has('questionType')]) wire:model.live="questionType">@foreach(ExamOptions::questionTypes() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <div class="admin-exam-form-row">
                <label @class(['admin-field','has-error'=>$errors->has('questionPrompt')])><span>نص السؤال بالعربية {{ $exam->language_policy !== 'en_only' ? '*' : '' }}</span><textarea @class(['admin-control','is-invalid'=>$errors->has('questionPrompt')]) rows="4" wire:model="questionPrompt" dir="rtl"></textarea>@error('questionPrompt')<small class="admin-field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>@enderror</label>
                <label @class(['admin-field','has-error'=>$errors->has('questionPromptEn')])><span>Question in English {{ $exam->language_policy !== 'ar_only' ? '*' : '' }}</span><textarea @class(['admin-control','is-invalid'=>$errors->has('questionPromptEn')]) rows="4" wire:model="questionPromptEn" dir="ltr"></textarea>@error('questionPromptEn')<small class="admin-field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>@enderror</label>
            </div>
            <div class="admin-exam-form-row"><label class="admin-field"><span>الصعوبة</span><select class="admin-control" wire:model="difficulty">@foreach(ExamOptions::difficulties() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label><label @class(['admin-field','has-error'=>$errors->has('points')])><span>الدرجة</span><input type="number" @class(['admin-control','is-invalid'=>$errors->has('points')]) min=".01" step=".01" wire:model="points">@error('points')<small class="admin-field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>@enderror</label></div>

            @if(in_array($questionType,['single_choice','multiple_choice']))
                <div @class(['admin-exam-options','has-error'=>$errors->has('options') || $errors->has('correctScalar')])>
                    <strong>الخيارات — حدد الصحيح</strong>
                    @foreach($options as $index=>$option)
                        <div wire:key="admin-option-{{ $index }}">
                            @if($questionType==='single_choice')<input type="radio" wire:model.live="correctScalar" value="{{ $index }}" name="admin-correct-option">@else<input type="checkbox" wire:model.live="options.{{ $index }}.correct">@endif
                            <span class="admin-exam-option-languages"><input type="text" class="admin-control" wire:model="options.{{ $index }}.content" placeholder="الخيار {{ $index+1 }}" dir="rtl"><input type="text" class="admin-control" wire:model="options.{{ $index }}.content_en" placeholder="Option {{ $index+1 }}" dir="ltr"></span>
                            @if(count($options)>2)<button type="button" wire:click="removeOption({{ $index }})">×</button>@endif
                        </div>
                    @endforeach
                    @error('options')<small class="admin-field-error admin-exam-options__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>@enderror
                    @error('correctScalar')<small class="admin-field-error admin-exam-options__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>@enderror
                    <button type="button" wire:click="addOption" class="admin-btn-secondary admin-btn-secondary--sm">+ خيار</button>
                </div>
            @elseif($questionType==='true_false')
                <label @class(['admin-field','has-error'=>$errors->has('correctScalar')])><span>الإجابة الصحيحة</span><select @class(['admin-control','is-invalid'=>$errors->has('correctScalar')]) wire:model="correctScalar"><option value="true">صح</option><option value="false">خطأ</option></select></label>
            @elseif($questionType==='numeric')
                <div class="admin-exam-form-row"><label @class(['admin-field','has-error'=>$errors->has('correctScalar')])><span>القيمة الصحيحة</span><input type="number" step="any" @class(['admin-control','is-invalid'=>$errors->has('correctScalar')]) wire:model="correctScalar">@error('correctScalar')<small class="admin-field-error">{{ $message }}</small>@enderror</label><label @class(['admin-field','has-error'=>$errors->has('numericTolerance')])><span>هامش الخطأ ±</span><input type="number" min="0" step="any" @class(['admin-control','is-invalid'=>$errors->has('numericTolerance')]) wire:model="numericTolerance"></label></div>
            @elseif(!in_array($questionType,['essay','file_upload']))
                <label @class(['admin-field','has-error'=>$errors->has('structuredAnswer')])><span>نموذج الإجابة</span><textarea @class(['admin-control','is-invalid'=>$errors->has('structuredAnswer')]) rows="5" wire:model="structuredAnswer" placeholder="{{ match($questionType){'short_text'=>'كل إجابة مقبولة في سطر','fill_blank'=>'كل فراغ في سطر والبدائل بـ |','matching'=>'العنصر => الإجابة','ordering'=>'العناصر بالترتيب الصحيح',default=>''} }}"></textarea>@error('structuredAnswer')<small class="admin-field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</small>@enderror</label>
                @if($exam->language_policy !== 'ar_only')
                    <label class="admin-field"><span>English answer model</span><textarea class="admin-control" rows="5" wire:model="structuredAnswerEn" dir="ltr" placeholder="Use the same line order as the Arabic answer model"></textarea></label>
                @endif
            @else
                <div class="admin-alert admin-alert--warning is-visible">هذا السؤال يحتاج تصحيحاً يدوياً.</div>
            @endif

            <div class="admin-exam-form-row"><label class="admin-field"><span>شرح الإجابة بالعربية</span><textarea class="admin-control" rows="3" wire:model="questionExplanation" dir="rtl"></textarea></label><label class="admin-field"><span>Answer explanation in English</span><textarea class="admin-control" rows="3" wire:model="questionExplanationEn" dir="ltr"></textarea></label></div>
            <div class="admin-exam-editor__footer">
                @if($editingLinkId)<button type="button" wire:click="cancelEditing" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</button>@endif
                <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled"><i class="fa-solid fa-floppy-disk"></i> {{ $editingLinkId ? 'حفظ التعديلات' : 'إضافة السؤال' }}</button>
            </div>
        </form>
    </section>
</div>

@include('partials.admin.exam-question-bank-workspace')
@include('partials.admin.exam-builder-help')

@push('styles')
<style>
    .admin-exam-builder-hero{display:flex;align-items:stretch;justify-content:space-between;gap:1.25rem;margin-bottom:1rem;padding:1.25rem 1.4rem;border-radius:18px;background:linear-gradient(135deg,#103d2c 0%,#176b47 68%,#258761 130%);color:#fff;box-shadow:0 16px 34px rgba(15,81,50,.18)}
    .admin-exam-builder-hero__eyebrow{display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:800;color:#c9f4dd}
    .admin-exam-builder-hero h1{margin:.35rem 0 .25rem;font-size:clamp(1.2rem,2vw,1.65rem);color:#fff}
    .admin-exam-builder-hero p{display:flex;align-items:center;gap:.45rem;margin:0;color:rgba(255,255,255,.75);font-size:.76rem}
    .admin-exam-builder-hero p i{display:block;width:4px;height:4px;border-radius:50%;background:#d1a94b}
    .admin-exam-builder-hero__stats{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.8rem}
    .admin-exam-builder-hero__stats span{padding:.32rem .55rem;border:1px solid rgba(255,255,255,.16);border-radius:8px;background:rgba(255,255,255,.08);font-size:.67rem;color:rgba(255,255,255,.78)}
    .admin-exam-builder-hero__stats strong{color:#fff}
    .admin-exam-builder-actions{display:flex;align-items:center;justify-content:flex-end;align-content:center;flex-wrap:wrap;gap:.5rem;max-width:23rem}
    .admin-exam-focus-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .65rem;border:1px solid rgba(255,255,255,.25);border-radius:9px;background:rgba(255,255,255,.1);color:#fff;font-size:.7rem;font-weight:800;cursor:pointer}
    .admin-exam-focus-btn:hover{background:rgba(255,255,255,.2)}
    .admin-exam-builder-layout{display:grid;grid-template-columns:minmax(0,1fr) clamp(22rem,31vw,28rem);gap:1rem;align-items:start}
    .admin-exam-canvas{padding:0;overflow:hidden}
    .admin-exam-canvas__head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.1rem;border-bottom:1px solid #e2e8f0;background:linear-gradient(180deg,#fff,#f8fafc)}
    .admin-exam-canvas__head h2{margin:0;font-size:.95rem}.admin-exam-canvas__head p{margin:.2rem 0 0;color:#64748b;font-size:.68rem}
    .admin-exam-live-status{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .5rem;border-radius:999px;background:#ecfdf5;color:#166534;font-size:.63rem;font-weight:800}
    .admin-exam-part{padding:1rem}
    .admin-exam-part__title{display:flex;align-items:center;gap:.6rem;margin-bottom:.7rem}
    .admin-exam-part__title>span{display:grid;place-items:center;width:2rem;height:2rem;border-radius:9px;background:#123b2a;color:#fff;font-size:.68rem;font-weight:900}
    .admin-exam-part__title h3{margin:0;font-size:.82rem}.admin-exam-part__title small{color:#64748b;font-size:.62rem}
    .admin-exam-sortable{display:flex;flex-direction:column;gap:.55rem;min-height:3rem}
    .admin-exam-question{display:grid;grid-template-columns:auto 2rem minmax(0,1fr) auto auto;align-items:center;gap:.65rem;padding:.75rem .8rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;box-shadow:0 2px 8px rgba(15,23,42,.025);transition:transform .16s ease,border-color .16s ease,box-shadow .16s ease,opacity .16s ease}
    .admin-exam-question:hover{border-color:#b8d8c6;box-shadow:0 6px 18px rgba(15,81,50,.07);transform:translateY(-1px)}
    .admin-exam-question.is-expanded{border-color:#86b99d;box-shadow:0 9px 24px rgba(15,81,50,.09);transform:none}
    .admin-exam-question.is-dragging{opacity:.52;border:1px dashed #1b8354;background:#f0fdf4;box-shadow:0 12px 26px rgba(15,81,50,.14);transform:scale(.99)}
    .admin-exam-drag-handle{display:grid;place-items:center;width:1.8rem;height:2.4rem;border:0;border-radius:7px;background:transparent;color:#94a3b8;cursor:grab}
    .admin-exam-drag-handle:hover{background:#f1f5f9;color:#166534}.admin-exam-drag-handle:active{cursor:grabbing}
    .admin-exam-question__number{display:grid;place-items:center;width:1.8rem;height:1.8rem;border-radius:8px;background:#ecfdf5;color:#166534;font-size:.7rem;font-weight:900}
    .admin-exam-question__content{min-width:0}.admin-exam-question__meta{display:flex;flex-wrap:wrap;gap:.35rem}
    .admin-exam-question__meta span{padding:.16rem .38rem;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.58rem;font-weight:800}
    .admin-exam-question__meta .difficulty-easy{background:#ecfdf5;color:#166534}.admin-exam-question__meta .difficulty-hard,.admin-exam-question__meta .difficulty-expert{background:#fff7ed;color:#9a3412}
    .admin-exam-question__content p{margin:.3rem 0 0;color:#1e293b;font-size:.76rem;font-weight:700;line-height:1.65}
    .admin-exam-question__points{display:flex;align-items:baseline;gap:.2rem;padding:.3rem .45rem;border-radius:8px;background:#fffbeb;color:#92400e;font-size:.76rem;white-space:nowrap}.admin-exam-question__points small{font-size:.55rem}
    .admin-exam-question__actions{display:flex;gap:.3rem}.admin-exam-question__actions button{display:grid;place-items:center;width:1.9rem;height:1.9rem;border:0;border-radius:7px;cursor:pointer}
    .admin-exam-preview{background:#f1f5f9;color:#475569}.admin-exam-preview.is-active{background:#dcfce7;color:#166534}.admin-exam-edit{background:#eff6ff;color:#1d4ed8}.admin-exam-remove{background:#fef2f2;color:#b91c1c}
    [x-cloak]{display:none!important}
    .admin-exam-question-details{grid-column:1/-1;margin:.15rem -.1rem -.1rem;padding:.9rem 1rem .25rem;border-top:1px solid #e2e8f0;background:linear-gradient(180deg,#fbfdfc,#fff);border-radius:0 0 10px 10px}
    .admin-exam-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .admin-exam-detail-block{padding:.7rem;border:1px solid #e7eee9;border-radius:10px;background:#fff}
    .admin-exam-detail-block--prompt,.admin-exam-detail-block--explanation,.admin-exam-detail-footer{grid-column:1/-1}
    .admin-exam-detail-label{display:flex;align-items:center;gap:.35rem;margin-bottom:.5rem;color:#64748b;font-size:.62rem;font-weight:900}
    .admin-exam-detail-label i{color:#1b8354}.admin-exam-detail-prompt{color:#17251e;font-size:.78rem;font-weight:700;line-height:1.9}
    .admin-exam-detail-options{display:flex;flex-direction:column;gap:.35rem}.admin-exam-detail-options>div{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:.45rem;padding:.4rem .5rem;border:1px solid #e2e8f0;border-radius:8px;color:#475569;font-size:.7rem}
    .admin-exam-detail-options>div.is-correct{border-color:#86efac;background:#f0fdf4;color:#166534}.admin-exam-detail-options>div strong{font-size:.6rem}
    .admin-exam-detail-option-key{display:grid;place-items:center;width:1.45rem;height:1.45rem;border-radius:6px;background:#f1f5f9;font-size:.6rem;font-weight:900}.is-correct .admin-exam-detail-option-key{background:#dcfce7}
    .admin-exam-detail-tags{display:flex;flex-wrap:wrap;gap:.35rem}.admin-exam-detail-tags span{padding:.28rem .5rem;border-radius:999px;background:#ecfdf5;color:#166534;font-size:.66rem;font-weight:800}
    .admin-exam-detail-structured{display:flex;flex-direction:column;gap:.35rem}.admin-exam-detail-structured>div{display:flex;align-items:center;gap:.5rem;padding:.4rem .5rem;border-radius:7px;background:#f8fafc;font-size:.68rem}.admin-exam-detail-structured strong{color:#334155}.admin-exam-detail-structured span{color:#166534}
    .admin-exam-detail-order{margin:0;padding-inline-start:1.4rem;color:#334155;font-size:.7rem;line-height:1.9}.admin-exam-detail-order li::marker{color:#1b8354;font-weight:900}
    .admin-exam-detail-numeric{display:flex;align-items:center;gap:.7rem}.admin-exam-detail-numeric strong{padding:.3rem .55rem;border-radius:8px;background:#ecfdf5;color:#166534;font-size:1rem}.admin-exam-detail-numeric span{color:#64748b;font-size:.66rem}
    .admin-exam-detail-manual{padding:.55rem;border-radius:8px;background:#fff7ed;color:#9a3412;font-size:.68rem}
    .admin-exam-detail-block--explanation p{margin:0;color:#475569;font-size:.7rem;line-height:1.8}
    .admin-exam-detail-footer{display:flex;flex-wrap:wrap;gap:.75rem;padding:.45rem .15rem 0;color:#64748b;font-size:.6rem}.admin-exam-detail-footer span{display:flex;align-items:center;gap:.25rem}
    .admin-exam-empty{display:flex;align-items:center;flex-direction:column;padding:2.5rem 1rem;border:1px dashed #cbd5e1;border-radius:12px;text-align:center;color:#64748b}.admin-exam-empty i{font-size:1.7rem;color:#94a3b8}.admin-exam-empty strong{margin-top:.6rem;color:#334155}.admin-exam-empty p{margin:.25rem 0 0;font-size:.7rem}
    .admin-exam-question-form{position:sticky;top:1rem;padding:0;overflow:hidden;border-color:#d6e5dc;box-shadow:0 10px 26px rgba(15,81,50,.07)}
    .admin-exam-question-form.is-editing{border-color:#93c5fd;box-shadow:0 10px 30px rgba(37,99,235,.1)}
    .admin-exam-editor__head{display:flex;align-items:center;gap:.65rem;padding:.9rem 1rem;border-bottom:1px solid #e2e8f0;background:linear-gradient(135deg,#f0fdf4,#fff)}
    .is-editing .admin-exam-editor__head{background:linear-gradient(135deg,#eff6ff,#fff)}
    .admin-exam-editor__icon{display:grid;place-items:center;width:2.35rem;height:2.35rem;border-radius:10px;background:#166534;color:#fff}.is-editing .admin-exam-editor__icon{background:#2563eb}
    .admin-exam-editor__head span{color:#64748b;font-size:.58rem;font-weight:800}.admin-exam-editor__head h2{margin:.1rem 0 0;font-size:.9rem}
    .admin-exam-editor__close{margin-inline-start:auto;border:0;background:#f1f5f9;color:#475569;width:1.8rem;height:1.8rem;border-radius:7px;font-size:1.1rem}
    .admin-exam-version-note{display:flex;align-items:flex-start;gap:.45rem;margin:.7rem .8rem 0;padding:.6rem;border-radius:8px;background:#eff6ff;color:#1e40af;font-size:.64rem;line-height:1.55}
    .admin-exam-question-form form{display:flex;flex-direction:column;gap:.7rem;padding:.9rem 1rem}
    .admin-exam-validation-summary{display:flex;align-items:flex-start;gap:.55rem;padding:.7rem .75rem;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#991b1b}
    .admin-exam-validation-summary>i{margin-top:.15rem}.admin-exam-validation-summary div{display:flex;flex-direction:column;gap:.12rem}.admin-exam-validation-summary strong{font-size:.73rem}.admin-exam-validation-summary span{font-size:.64rem}
    .admin-exam-question-form .admin-field.has-error>span{color:#b91c1c}
    .admin-exam-question-form .admin-control.is-invalid{border-color:#dc2626!important;background:#fff7f7!important;box-shadow:0 0 0 3px rgba(220,38,38,.09)!important}
    .admin-exam-question-form .admin-field-error{display:flex;align-items:center;gap:.25rem;margin-top:.18rem;color:#b91c1c;font-size:.64rem;font-weight:800}
    .admin-exam-form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
    .admin-exam-options{display:flex;flex-direction:column;gap:.4rem;padding:.65rem;border-radius:10px;background:#f8fafc}
    .admin-exam-options.has-error{border:1px solid #dc2626;background:#fff7f7;box-shadow:0 0 0 3px rgba(220,38,38,.08)}
    .admin-exam-options.has-error>strong{color:#b91c1c}
    .admin-exam-options.has-error>div:has(input[type=radio]:checked),.admin-exam-options.has-error>div:has(input[type=checkbox]:checked){padding:.2rem;border-radius:7px;background:#fee2e2}
    .admin-exam-options__error{padding:.4rem .5rem;border-radius:7px;background:#fee2e2}
    .admin-exam-options>strong{font-size:.7rem}.admin-exam-options>div{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:.4rem}
    .admin-exam-option-languages{display:grid;grid-template-columns:1fr 1fr;gap:.4rem}
    .admin-exam-options input[type=radio],.admin-exam-options input[type=checkbox]{accent-color:#1b8354}.admin-exam-options>div button{border:0;background:transparent;color:#b91c1c}
    .admin-exam-editor__footer{display:flex;justify-content:flex-end;gap:.45rem;padding-top:.65rem;border-top:1px solid #e2e8f0}
    .admin-exam-bank-workspace{margin-top:1rem;padding:0;overflow:hidden}
    .admin-exam-bank-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.25rem;background:linear-gradient(135deg,#102f24,#18583e);color:#fff}
    .admin-exam-bank-head h2{margin:.18rem 0;font-size:1.05rem;color:#fff}.admin-exam-bank-head p{margin:0;color:#bad3c5;font-size:.68rem}.admin-exam-bank-eyebrow{font-size:.6rem;font-weight:900;color:#8fe0b4}
    .admin-exam-bank-actions{display:flex;gap:.45rem}.admin-exam-bank-head .admin-btn-secondary{border-color:rgba(255,255,255,.25);background:rgba(255,255,255,.1);color:#fff}
    .admin-exam-bank-tabs{display:flex;gap:.25rem;padding:.55rem 1rem;border-bottom:1px solid #e2e8f0;background:#f8fafc}.admin-exam-bank-tabs button{display:flex;align-items:center;gap:.35rem;padding:.5rem .7rem;border:0;border-radius:8px;background:transparent;color:#64748b;font-size:.68rem;font-weight:900;cursor:pointer}.admin-exam-bank-tabs button.is-active{background:#dcfce7;color:#166534;box-shadow:inset 0 0 0 1px #bbf7d0}
    .admin-exam-bank-filters{display:grid;grid-template-columns:minmax(15rem,1.6fr) repeat(3,minmax(9rem,1fr));gap:.55rem;padding:1rem 1rem .6rem}.admin-exam-bank-filters select,.admin-exam-bank-search{height:2.35rem;border:1px solid #d7e0da;border-radius:9px;background:#fff;color:#334155;font-size:.67rem}.admin-exam-bank-filters select{padding:0 .55rem}
    .admin-exam-bank-search{display:flex;align-items:center;gap:.4rem;padding:0 .65rem}.admin-exam-bank-search i{color:#94a3b8}.admin-exam-bank-search input{width:100%;border:0;outline:0;background:transparent;font:inherit;color:inherit}
    .admin-exam-bank-summary{display:flex;justify-content:space-between;gap:.7rem;padding:.2rem 1rem .7rem;color:#64748b;font-size:.62rem}.admin-exam-bank-summary strong{color:#166534}.admin-exam-bank-summary small{font-size:.6rem}
    .admin-exam-bank{display:grid;grid-template-columns:repeat(auto-fit,minmax(22rem,1fr));gap:.6rem;padding:0 1rem 1rem}.admin-exam-bank article{display:flex;align-items:center;justify-content:space-between;gap:.8rem;padding:.8rem;border:1px solid #e2e8f0;border-radius:11px;background:#fff;transition:.15s}.admin-exam-bank article:hover{border-color:#8bc4a3;box-shadow:0 6px 16px rgba(15,81,50,.06)}
    .admin-exam-bank-card__main{min-width:0}.admin-exam-bank-card__meta{display:flex;flex-wrap:wrap;gap:.3rem}.admin-exam-bank-card__meta span{padding:.15rem .35rem;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.56rem;font-weight:800}.admin-exam-bank-card__meta .difficulty-easy{background:#ecfdf5;color:#166534}.admin-exam-bank-card__meta .difficulty-hard,.admin-exam-bank-card__meta .difficulty-expert{background:#fff7ed;color:#9a3412}
    .admin-exam-bank-card__main p{margin:.35rem 0;color:#1e293b;font-size:.72rem;font-weight:700;line-height:1.6}.admin-exam-bank-card__tags{display:flex;flex-wrap:wrap;gap:.25rem}.admin-exam-bank-card__tags small{color:#2d7a54;font-size:.54rem}
    .admin-exam-bank-card__side{display:flex;align-items:flex-end;flex-direction:column;gap:.45rem;white-space:nowrap}.admin-exam-bank-card__side>strong{color:#92400e;font-size:.68rem}.admin-exam-bank-card__side>strong small{font-size:.52rem}
    .admin-exam-bank-empty{grid-column:1/-1;display:flex;align-items:center;flex-direction:column;padding:2rem;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b}.admin-exam-bank-empty i{margin-bottom:.5rem;font-size:1.5rem}.admin-exam-bank-empty strong{color:#334155}.admin-exam-bank-empty span{margin-top:.2rem;font-size:.64rem}
    .admin-exam-pool-panel{padding:1rem}.admin-exam-pool-intro{display:flex;align-items:center;gap:.75rem;margin-bottom:.8rem;padding:.8rem;border:1px solid #d8eee1;border-radius:12px;background:#f3fcf6}.admin-exam-pool-icon,.admin-exam-import-card__icon{display:grid;place-items:center;flex:0 0 auto;width:2.5rem;height:2.5rem;border-radius:10px;background:#166534;color:#fff}.admin-exam-pool-intro h3,.admin-exam-import-layout h3{margin:0;font-size:.83rem}.admin-exam-pool-intro p,.admin-exam-import-layout p{margin:.2rem 0 0;color:#64748b;font-size:.63rem;line-height:1.6}.admin-exam-pool-active{margin-inline-start:auto;padding:.3rem .5rem;border-radius:999px;background:#dcfce7;color:#166534;font-size:.6rem;font-weight:900;white-space:nowrap}
    .admin-exam-pool-form{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem}.admin-exam-pool-preview{display:flex;align-items:center;justify-content:center;flex-direction:column;padding:.55rem;border:1px dashed #86b99d;border-radius:10px;background:#fbfffc;color:#64748b;font-size:.6rem}.admin-exam-pool-preview strong{color:#166534;font-size:1.35rem}.admin-exam-pool-preview small{font-size:.54rem}.admin-exam-pool-footer{grid-column:1/-1;display:flex;justify-content:flex-end;gap:.45rem;padding-top:.65rem;border-top:1px solid #e2e8f0}.admin-exam-danger-btn{color:#b91c1c!important}
    .admin-exam-import-layout{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;padding:1rem}.admin-exam-import-card,.admin-exam-category-card{display:flex;align-items:flex-start;flex-direction:column;gap:.65rem;padding:1rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.admin-exam-file-drop{display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.35rem;width:100%;min-height:7rem;border:1px dashed #86b99d;border-radius:11px;background:#f8fdf9;color:#166534;cursor:pointer}.admin-exam-file-drop input{display:none}.admin-exam-file-drop i{font-size:1.5rem}.admin-exam-file-drop span{font-size:.65rem;font-weight:800}.admin-exam-import-hint{color:#64748b;font-size:.57rem}
    .admin-exam-category-card__head{display:flex;align-items:center;gap:.65rem}.admin-exam-category-card .admin-field{width:100%}.admin-exam-category-list{display:flex;flex-wrap:wrap;gap:.35rem;width:100%;padding-top:.65rem;border-top:1px solid #e2e8f0}.admin-exam-category-list>span{display:flex;align-items:center;gap:.25rem;padding:.28rem .45rem;border-radius:7px;background:#f1f5f9;color:#334155;font-size:.6rem;font-weight:800}.admin-exam-category-list>span.is-child{margin-inline-start:.6rem;background:#f8fafc;color:#64748b}.admin-exam-category-list span small{display:grid;place-items:center;min-width:1rem;height:1rem;border-radius:999px;background:#fff;color:#166534;font-size:.5rem}
    .admin-exam-help{position:fixed;z-index:10000;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(7,23,16,.72);backdrop-filter:blur(5px)}
    .admin-exam-help__dialog{display:flex;flex-direction:column;width:min(68rem,96vw);height:min(47rem,92vh);overflow:hidden;border:1px solid rgba(255,255,255,.25);border-radius:18px;background:#fff;box-shadow:0 30px 80px rgba(0,0,0,.32)}
    .admin-exam-help__head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.15rem;background:linear-gradient(135deg,#0d3324,#196044);color:#fff}.admin-exam-help__head-icon{display:grid;place-items:center;flex:0 0 auto;width:2.7rem;height:2.7rem;border:1px solid rgba(255,255,255,.2);border-radius:11px;background:rgba(255,255,255,.1);color:#d0f5df}.admin-exam-help__head span{color:#8fe0b4;font-size:.57rem;font-weight:900}.admin-exam-help__head h2{margin:.1rem 0;color:#fff;font-size:1rem}.admin-exam-help__head p{margin:0;color:#bcd7c9;font-size:.62rem}.admin-exam-help__close{margin-inline-start:auto;width:2rem;height:2rem;border:1px solid rgba(255,255,255,.2);border-radius:8px;background:rgba(255,255,255,.08);color:#fff;font-size:1.25rem;cursor:pointer}
    .admin-exam-help__body{display:grid;grid-template-columns:13.5rem minmax(0,1fr);min-height:0;flex:1}.admin-exam-help__nav{display:flex;flex-direction:column;gap:.3rem;padding:.8rem;border-inline-end:1px solid #e2e8f0;background:#f8fafc}.admin-exam-help__nav button{display:grid;grid-template-columns:1.3rem 1fr auto;align-items:center;gap:.4rem;padding:.65rem .6rem;border:0;border-radius:9px;background:transparent;color:#64748b;text-align:start;font-size:.67rem;font-weight:800;cursor:pointer}.admin-exam-help__nav button i{text-align:center}.admin-exam-help__nav button b{padding:.12rem .3rem;border-radius:999px;background:#fef3c7;color:#92400e;font-size:.45rem}.admin-exam-help__nav button:hover{background:#eef5f0;color:#166534}.admin-exam-help__nav button.is-active{background:#dcfce7;color:#14532d;box-shadow:inset 3px 0 #16a34a}
    .admin-exam-help__content{overflow-y:auto;padding:1.25rem 1.4rem}.admin-exam-help__content section{max-width:48rem}.admin-exam-help__eyebrow{color:#168251;font-size:.6rem;font-weight:900}.admin-exam-help__content h3{margin:.25rem 0 .4rem;color:#17251e;font-size:1.15rem}.admin-exam-help__lead{margin:0 0 1rem;color:#64748b;font-size:.72rem;line-height:1.9}
    .admin-exam-help__steps{display:grid;grid-template-columns:1fr 1fr;gap:.55rem}.admin-exam-help__steps>div{display:flex;align-items:flex-start;gap:.55rem;padding:.7rem;border:1px solid #e2e8f0;border-radius:10px}.admin-exam-help__steps b{display:grid;place-items:center;flex:0 0 auto;width:1.65rem;height:1.65rem;border-radius:8px;background:#166534;color:#fff;font-size:.65rem}.admin-exam-help__steps span,.admin-exam-help__legend span{display:flex;flex-direction:column;gap:.15rem}.admin-exam-help__steps strong,.admin-exam-help__legend strong{color:#334155;font-size:.69rem}.admin-exam-help__steps small,.admin-exam-help__legend small{color:#64748b;font-size:.6rem;line-height:1.6}
    .admin-exam-help__note{display:flex;align-items:flex-start;gap:.5rem;margin-top:.8rem;padding:.7rem;border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;color:#1e40af;font-size:.65rem;line-height:1.7}.admin-exam-help__note>i{margin-top:.15rem}.admin-exam-help__note--gold{border-color:#fde68a;background:#fffbeb;color:#854d0e}.admin-exam-help__note--warning{border-color:#fed7aa;background:#fff7ed;color:#9a3412}
    .admin-exam-help__cards{display:grid;grid-template-columns:1fr 1fr;gap:.55rem}.admin-exam-help__cards article{padding:.75rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff}.admin-exam-help__cards article>i{display:grid;place-items:center;width:2rem;height:2rem;margin-bottom:.45rem;border-radius:8px;background:#ecfdf5;color:#166534}.admin-exam-help__cards h4,.admin-exam-help__feature h4{margin:0;color:#334155;font-size:.72rem}.admin-exam-help__cards p,.admin-exam-help__feature p{margin:.2rem 0 0;color:#64748b;font-size:.62rem;line-height:1.7}
    .admin-exam-help__how{margin-top:.75rem;padding:.75rem;border-radius:10px;background:#f8fafc}.admin-exam-help__how>strong{font-size:.7rem;color:#334155}.admin-exam-help__how ol{margin:.4rem 0 0;padding-inline-start:1.2rem;color:#475569;font-size:.65rem;line-height:1.9}.admin-exam-help__how li::marker{color:#168251;font-weight:900}
    .admin-exam-help__legend{display:grid;grid-template-columns:1fr 1fr;gap:.55rem}.admin-exam-help__legend>div{display:flex;gap:.55rem;padding:.75rem;border:1px solid #e2e8f0;border-radius:10px}.admin-exam-help__legend>div>i{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:8px;background:#f1f5f9;color:#166534}
    .admin-exam-help__feature{display:flex;align-items:flex-start;gap:.65rem;margin-bottom:.55rem;padding:.75rem;border:1px solid #e2e8f0;border-radius:10px}.admin-exam-help__feature-icon{display:grid;place-items:center;flex:0 0 auto;width:2.2rem;height:2.2rem;border-radius:9px;background:#ecfdf5;color:#166534}
    .admin-exam-help__example{display:flex;align-items:flex-start;gap:.55rem;margin-top:.7rem;padding:.75rem;border:1px dashed #86b99d;border-radius:10px;background:#f6fcf8;color:#475569;font-size:.65rem;line-height:1.7}.admin-exam-help__example strong{padding:.15rem .4rem;border-radius:6px;background:#166534;color:#fff;font-size:.57rem;white-space:nowrap}
    .admin-exam-help__checklist{display:flex;flex-direction:column;gap:.45rem}.admin-exam-help__checklist label{display:flex;align-items:center;gap:.5rem;padding:.65rem;border:1px solid #e2e8f0;border-radius:9px;color:#475569;font-size:.66rem}.admin-exam-help__checklist i{display:grid;place-items:center;width:1.3rem;height:1.3rem;border-radius:50%;background:#dcfce7;color:#166534;font-size:.55rem}
    @media(max-width:1100px){.admin-exam-builder-layout{grid-template-columns:1fr}.admin-exam-question-form{position:static}.admin-exam-bank-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.admin-exam-pool-form{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:700px){.admin-exam-builder-hero,.admin-exam-bank-head{align-items:flex-start;flex-direction:column}.admin-exam-builder-actions{justify-content:flex-start}.admin-exam-form-row,.admin-exam-bank-filters,.admin-exam-pool-form,.admin-exam-import-layout{grid-template-columns:1fr}.admin-exam-question{grid-template-columns:auto 2rem 1fr}.admin-exam-question__points,.admin-exam-question__actions{grid-column:3}.admin-exam-detail-grid{grid-template-columns:1fr}.admin-exam-detail-block{grid-column:1}.admin-exam-bank-tabs{overflow-x:auto}.admin-exam-bank-tabs button{white-space:nowrap}.admin-exam-bank{grid-template-columns:1fr}.admin-exam-bank-summary{flex-direction:column}.admin-exam-pool-footer{grid-column:1}.admin-exam-help{padding:.35rem}.admin-exam-help__dialog{width:100%;height:97vh}.admin-exam-help__head p{display:none}.admin-exam-help__body{grid-template-columns:1fr}.admin-exam-help__nav{overflow-x:auto;flex-direction:row;border-inline-end:0;border-bottom:1px solid #e2e8f0}.admin-exam-help__nav button{grid-template-columns:auto auto auto;white-space:nowrap}.admin-exam-help__content{padding:1rem}.admin-exam-help__steps,.admin-exam-help__cards,.admin-exam-help__legend{grid-template-columns:1fr}}
</style>
@endpush

@script
<script>
    $wire.on('exam-editor-opened', () => {
        requestAnimationFrame(() => {
            const editor = document.getElementById('admin-exam-question-editor');
            if (!editor) return;
            editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            window.setTimeout(() => editor.querySelector('textarea')?.focus(), 350);
        });
    });
</script>
@endscript

@include('partials.admin.shell-end')
