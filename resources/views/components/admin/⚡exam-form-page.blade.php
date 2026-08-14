<?php

use App\Models\AcademicSection;
use App\Models\Exam;
use App\Support\ExamOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('اختبار | لوحة التحكم')]
class extends Component
{
    public ?int $examId = null;
    public string $sectionId = '';
    public string $title = '';
    public string $titleEn = '';
    public string $instructions = '';
    public string $instructionsEn = '';
    public string $languagePolicy = 'ar_only';
    public string $type = 'exam';
    public string $opensAt = '';
    public string $closesAt = '';
    public string $durationMinutes = '60';
    public string $attemptPolicy = 'single';
    public int $maxAttempts = 1;
    public string $gradeSelection = 'highest';
    public string $passingPercent = '60';
    public bool $shuffleQuestions = true;
    public bool $shuffleOptions = true;
    public bool $oneQuestionPerPage = false;
    public bool $allowBackNavigation = true;
    public bool $requireAccessCode = false;
    public bool $hasStoredAccessCode = false;
    public string $accessCode = '';
    public string $resultRelease = 'after_grading';
    public string $reviewPolicy = 'score_only';

    public function mount(?Exam $exam = null): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        if (! $exam?->exists) {
            return;
        }

        $this->examId = $exam->id;
        $this->sectionId = (string) $exam->section_id;
        $this->title = $exam->title;
        $this->titleEn = $exam->title_en ?? '';
        $this->instructions = $exam->instructions ?? '';
        $this->instructionsEn = $exam->instructions_en ?? '';
        $this->languagePolicy = $exam->language_policy ?? 'ar_only';
        $this->type = $exam->type;
        $this->opensAt = $exam->opens_at?->format('Y-m-d\TH:i') ?? '';
        $this->closesAt = $exam->closes_at?->format('Y-m-d\TH:i') ?? '';
        $this->durationMinutes = $exam->duration_minutes ? (string) $exam->duration_minutes : '';
        $this->attemptPolicy = $exam->attempt_policy
            ?? ($exam->max_attempts > 1 ? 'limited' : 'single');
        $this->maxAttempts = $exam->max_attempts;
        $this->gradeSelection = $exam->grade_selection ?? 'highest';
        $this->passingPercent = (string) $exam->passing_percent;
        $this->shuffleQuestions = $exam->shuffle_questions;
        $this->shuffleOptions = $exam->shuffle_options;
        $this->oneQuestionPerPage = $exam->one_question_per_page;
        $this->allowBackNavigation = $exam->allow_back_navigation;
        $this->requireAccessCode = $exam->require_access_code;
        $this->hasStoredAccessCode = filled($exam->access_code_hash);
        $this->resultRelease = $exam->result_release;
        $this->reviewPolicy = $exam->review_policy;
    }

    #[Computed]
    public function sections()
    {
        return AcademicSection::query()
            ->with(['course', 'program', 'batch'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);

        $rules = [
            'sectionId' => ['required', 'integer', 'exists:academic_sections,id'],
            'title' => [Rule::requiredIf(in_array($this->languagePolicy, ['ar_only', 'student_locale', 'student_choice'], true)), 'nullable', 'string', 'max:255'],
            'titleEn' => [Rule::requiredIf(in_array($this->languagePolicy, ['en_only', 'student_locale', 'student_choice'], true)), 'nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:20000'],
            'instructionsEn' => ['nullable', 'string', 'max:20000'],
            'languagePolicy' => ['required', Rule::in(array_keys(ExamOptions::languagePolicies()))],
            'type' => ['required', Rule::in(array_keys(ExamOptions::examTypes()))],
            'opensAt' => ['nullable', 'date'],
            'closesAt' => ['nullable', 'date', 'after:opensAt'],
            'durationMinutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'attemptPolicy' => ['required', Rule::in(array_keys(ExamOptions::attemptPolicies()))],
            'maxAttempts' => [
                Rule::excludeIf($this->attemptPolicy !== 'limited'),
                'required',
                'integer',
                'min:2',
                'max:20',
            ],
            'gradeSelection' => ['required', Rule::in(array_keys(ExamOptions::gradeSelectionPolicies()))],
            'passingPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'resultRelease' => ['required', Rule::in(array_keys(ExamOptions::resultReleasePolicies()))],
            'reviewPolicy' => ['required', Rule::in(array_keys(ExamOptions::reviewPolicies()))],
        ];

        if ($this->requireAccessCode && (! $this->hasStoredAccessCode || $this->accessCode !== '')) {
            $rules['accessCode'] = ['required', 'string', 'min:4', 'max:100'];
        }

        $validated = $this->validate($rules, [], [
            'sectionId' => 'الشعبة',
            'title' => 'عنوان الاختبار',
            'titleEn' => 'عنوان الاختبار بالإنجليزية',
            'opensAt' => 'موعد الفتح',
            'closesAt' => 'موعد الإغلاق',
            'durationMinutes' => 'المدة',
            'attemptPolicy' => 'سياسة المحاولات',
            'maxAttempts' => 'عدد المحاولات',
            'gradeSelection' => 'طريقة اعتماد الدرجة',
            'accessCode' => 'رمز الدخول',
        ]);
        $section = AcademicSection::query()->with('course')->findOrFail($validated['sectionId']);
        $existing = $this->examId ? Exam::query()->findOrFail($this->examId) : null;

        if ($existing && $existing->attempts()->exists() && $existing->section_id !== $section->id) {
            $this->addError('sectionId', 'لا يمكن تغيير شعبة اختبار بدأت محاولاته.');
            return;
        }

        $oldValues = $existing?->only(['section_id', 'title', 'status', 'opens_at', 'closes_at']);

        $exam = DB::transaction(function () use ($validated, $section, $existing) {
            $exam = $existing ?? new Exam;
            $exam->fill([
                'section_id' => $section->id,
                'course_id' => $section->course_id,
                'created_by' => $exam->created_by ?: auth()->id(),
                'title' => $validated['title'] ?: $validated['titleEn'],
                'title_en' => $validated['titleEn'] ?: null,
                'instructions' => $validated['instructions'] ?: null,
                'instructions_en' => $validated['instructionsEn'] ?: null,
                'type' => $validated['type'],
                'language_policy' => $validated['languagePolicy'],
                'opens_at' => $validated['opensAt'] ?: null,
                'closes_at' => $validated['closesAt'] ?: null,
                'duration_minutes' => $validated['durationMinutes'] !== '' ? $validated['durationMinutes'] : null,
                'max_attempts' => match ($validated['attemptPolicy']) {
                    'single' => 1,
                    'limited' => $validated['maxAttempts'],
                    default => max(1, $this->maxAttempts),
                },
                'attempt_policy' => $validated['attemptPolicy'],
                'grade_selection' => $validated['gradeSelection'],
                'passing_percent' => $validated['passingPercent'],
                'shuffle_questions' => $this->shuffleQuestions,
                'shuffle_options' => $this->shuffleOptions,
                'one_question_per_page' => $this->oneQuestionPerPage,
                'allow_back_navigation' => $this->allowBackNavigation,
                'require_access_code' => $this->requireAccessCode,
                'result_release' => $validated['resultRelease'],
                'review_policy' => $validated['reviewPolicy'],
            ]);

            if (! $this->requireAccessCode) {
                $exam->access_code_hash = null;
            } elseif ($this->accessCode !== '') {
                $exam->access_code_hash = Hash::make($this->accessCode);
            }

            $exam->save();

            if ($exam->parts()->doesntExist()) {
                $exam->parts()->create([
                    'title' => 'أسئلة الاختبار',
                    'sort_order' => 1,
                    'shuffle_questions' => $this->shuffleQuestions,
                ]);
            }

            return $exam;
        });

        app(\App\Services\AuditLogService::class)->log(
            action: $existing ? 'exam.updated' : 'exam.created',
            descriptionAr: ($existing ? 'تحديث' : 'إنشاء').' اختبار «'.$exam->title.'» من لوحة الإدارة',
            group: 'exams',
            subject: $exam,
            subjectLabel: $exam->title,
            oldValues: $oldValues,
            newValues: $exam->only(['section_id', 'title', 'status', 'opens_at', 'closes_at']),
        );

        session()->flash('admin_message', $existing ? 'تم تحديث الاختبار.' : 'تم إنشاء الاختبار. أضف الأسئلة الآن.');
        $this->redirectRoute('admin.exams.builder', $exam, navigate: true);
    }
};
?>

@php($pageTitle = $examId ? 'تعديل الاختبار' : 'إنشاء اختبار جديد')

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.exams'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.exams'), 'label' => 'الاختبارات'],
        ['label' => $pageTitle],
    ],
])

<div class="admin-page-header">
    <div><h1>{{ $pageTitle }}</h1><p>حدد الشعبة، مواعيد الاختبار، سياسات المحاولة والنتائج.</p></div>
    <a href="{{ route('admin.exams') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
</div>

<form wire:submit="save">
    <section class="admin-crud-card">
        <div class="admin-crud-card__head"><h2>البيانات الأساسية</h2></div>
        <div class="admin-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
            <label class="admin-field admin-exam-wide"><span>الشعبة الدراسية *</span><select class="admin-control" wire:model="sectionId"><option value="">اختر الشعبة</option>@foreach($this->sections as $section)<option value="{{ $section->id }}">{{ $section->name }} — {{ $section->course?->name_ar }} — {{ $section->batch?->name }}</option>@endforeach</select>@error('sectionId')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
            <label class="admin-field admin-exam-wide"><span>سياسة لغة الاختبار *</span><select class="admin-control" wire:model.live="languagePolicy">@foreach(ExamOptions::languagePolicies() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><small class="admin-field-hint is-visible">يمكن فرض العربية أو الإنجليزية على الجميع، أو السماح بالتحديد حسب لغة الطالب.</small></label>
            <label class="admin-field"><span>عنوان الاختبار بالعربية {{ $languagePolicy !== 'en_only' ? '*' : '' }}</span><input type="text" class="admin-control" wire:model="title" dir="rtl">@error('title')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
            <label class="admin-field"><span>Exam title in English {{ $languagePolicy !== 'ar_only' ? '*' : '' }}</span><input type="text" class="admin-control" wire:model="titleEn" dir="ltr">@error('titleEn')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
            <label class="admin-field"><span>نوع الاختبار</span><select class="admin-control" wire:model="type">@foreach(ExamOptions::examTypes() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="admin-field"><span>درجة النجاح %</span><input type="number" min="0" max="100" step=".01" class="admin-control" wire:model="passingPercent"></label>
            <label class="admin-field"><span>التعليمات بالعربية</span><textarea rows="5" class="admin-control" wire:model="instructions" dir="rtl"></textarea></label>
            <label class="admin-field"><span>Instructions in English</span><textarea rows="5" class="admin-control" wire:model="instructionsEn" dir="ltr"></textarea></label>
            <label class="admin-field"><span>يفتح في</span><input type="datetime-local" class="admin-control" wire:model="opensAt">@error('opensAt')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
            <label class="admin-field"><span>يغلق في</span><input type="datetime-local" class="admin-control" wire:model="closesAt">@error('closesAt')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
            <label class="admin-field"><span>المدة بالدقائق</span><input type="number" min="1" max="1440" class="admin-control" wire:model="durationMinutes" placeholder="بدون حد"></label>
            <label class="admin-field"><span>إعلان النتيجة</span><select class="admin-control" wire:model="resultRelease">@foreach(ExamOptions::resultReleasePolicies() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="admin-field">
                <span>ما الذي يمكن للطالب رؤيته بعد التصحيح؟</span>
                <select class="admin-control" wire:model="reviewPolicy">@foreach(ExamOptions::reviewPolicies() as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                <small class="admin-field-hint is-visible">اختر «عرض التصحيح» للسماح بمقارنة إجابة الطالب بالإجابة الصحيحة مع شرح السؤال وملاحظة المصحح.</small>
            </label>
        </div>
    </section>

    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>سياسة المحاولات والدرجة المعتمدة</h2>
            <p class="admin-crud-card__meta">حدد عدد مرات دخول الطالب للاختبار، ثم اختر أي نتيجة تُعتمد في سجله النهائي.</p>
        </div>
        <div class="exam-attempt-policy">
            <div class="exam-attempt-policy__group">
                <span class="exam-attempt-policy__label"><i class="fa-solid fa-rotate"></i> عدد المحاولات المتاحة</span>
                <div class="exam-policy-options exam-policy-options--3">
                    @foreach (ExamOptions::attemptPolicies() as $value => $label)
                        <label class="{{ $attemptPolicy === $value ? 'is-active' : '' }}">
                            <input type="radio" wire:model.live="attemptPolicy" value="{{ $value }}">
                            <i class="fa-solid {{ match($value) { 'single' => 'fa-1', 'limited' => 'fa-list-ol', default => 'fa-infinity' } }}"></i>
                            <span>
                                <strong>{{ $label }}</strong>
                                <small>{{ match($value) { 'single' => 'لا يمكن للطالب الإعادة بعد التسليم', 'limited' => 'تحدد رقماً من 2 إلى 20 محاولة', default => 'يمكن الإعادة طوال فترة إتاحة الاختبار' } }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('attemptPolicy')<small class="admin-field-error">{{ $message }}</small>@enderror
                @if ($attemptPolicy === 'limited')
                    <label class="admin-field exam-attempt-count">
                        <span>الحد الأقصى للمحاولات *</span>
                        <input type="number" min="2" max="20" class="admin-control" wire:model="maxAttempts">
                        <small class="admin-field-hint is-visible">يشمل هذا الرقم المحاولة الأولى. ويمكن منح طالب محدد محاولات إضافية من التسهيلات الفردية.</small>
                        @error('maxAttempts')<small class="admin-field-error">{{ $message }}</small>@enderror
                    </label>
                @endif
            </div>

            <div class="exam-attempt-policy__group">
                <span class="exam-attempt-policy__label"><i class="fa-solid fa-ranking-star"></i> النتيجة النهائية المعتمدة</span>
                <div class="exam-policy-options exam-policy-options--2">
                    @foreach (ExamOptions::gradeSelectionPolicies() as $value => $label)
                        <label class="{{ $gradeSelection === $value ? 'is-active' : '' }}">
                            <input type="radio" wire:model.live="gradeSelection" value="{{ $value }}">
                            <i class="fa-solid {{ $value === 'highest' ? 'fa-arrow-trend-up' : 'fa-clock-rotate-left' }}"></i>
                            <span>
                                <strong>{{ $label }}</strong>
                                <small>{{ $value === 'highest' ? 'تُعتمد أفضل نسبة حصل عليها الطالب من جميع المحاولات المصححة' : 'تُعتمد نتيجة أحدث محاولة مصححة حتى لو كانت أقل من السابقة' }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('gradeSelection')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="exam-policy-summary">
                <i class="fa-solid fa-circle-info"></i>
                <span>
                    كل المحاولات تبقى محفوظة للمراجعة والتدقيق، لكن النتيجة المعتمدة للطالب ستكون
                    <strong>{{ $gradeSelection === 'highest' ? 'أعلى درجة' : 'آخر محاولة' }}</strong>.
                </span>
            </div>
        </div>

        <div class="admin-crud-card__head exam-controls-head"><h2>ضوابط تنفيذ المحاولة</h2></div>
        <div class="admin-exam-switches">
            <label><input type="checkbox" wire:model="shuffleQuestions"> خلط الأسئلة</label>
            <label><input type="checkbox" wire:model="shuffleOptions"> خلط الخيارات</label>
            <label><input type="checkbox" wire:model="oneQuestionPerPage"> سؤال واحد في الصفحة</label>
            <label><input type="checkbox" wire:model="allowBackNavigation"> السماح بالعودة</label>
            <label><input type="checkbox" wire:model.live="requireAccessCode"> يتطلب رمز دخول</label>
        </div>
        @if($requireAccessCode)
            <label class="admin-field" style="max-width:28rem"><span>{{ $examId ? 'رمز جديد (اختياري)' : 'رمز الدخول *' }}</span><input type="text" class="admin-control" wire:model="accessCode" dir="ltr">@error('accessCode')<small class="admin-field-error">{{ $message }}</small>@enderror</label>
        @endif
    </section>

    <div class="admin-filter-actions">
        <a href="{{ route('admin.exams') }}" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</a>
        <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">حفظ والمتابعة إلى الأسئلة</button>
    </div>
</form>

@push('styles')
<style>
    .admin-exam-wide{grid-column:1/-1}.admin-exam-switches{display:flex;flex-wrap:wrap;gap:.6rem}.admin-exam-switches label{padding:.55rem .7rem;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;font-size:.74rem;font-weight:800}.admin-exam-switches input{accent-color:#1b8354}
    .exam-attempt-policy{display:grid;gap:1rem;padding:1rem}.exam-attempt-policy__group{display:grid;gap:.55rem}.exam-attempt-policy__label{display:flex;align-items:center;gap:.4rem;color:#334155;font-size:.7rem;font-weight:900}.exam-attempt-policy__label i{color:#1b8354}.exam-policy-options{display:grid;gap:.65rem}.exam-policy-options--3{grid-template-columns:repeat(3,minmax(0,1fr))}.exam-policy-options--2{grid-template-columns:repeat(2,minmax(0,1fr))}.exam-policy-options label{display:flex;align-items:flex-start;gap:.65rem;padding:.8rem;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}.exam-policy-options label:hover{border-color:#86efac}.exam-policy-options input{position:absolute;opacity:0}.exam-policy-options label>i{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:9px;background:#e2e8f0;color:#64748b;font-size:.72rem}.exam-policy-options label>span{display:grid;gap:.15rem}.exam-policy-options strong{color:#334155;font-size:.67rem}.exam-policy-options small{color:#94a3b8;font-size:.55rem;line-height:1.6}.exam-policy-options label.is-active{border-color:#16a34a;background:#f0fdf4;box-shadow:0 0 0 3px rgba(22,163,74,.1)}.exam-policy-options label.is-active>i{background:#16a34a;color:#fff}.exam-policy-options label.is-active strong{color:#166534}.exam-attempt-count{max-width:22rem;padding:.7rem;border:1px dashed #bbf7d0;border-radius:11px;background:#f7fdf9}.exam-policy-summary{display:flex;align-items:flex-start;gap:.5rem;padding:.7rem .8rem;border:1px solid #dbeafe;border-radius:11px;background:#eff6ff;color:#1e40af;font-size:.62rem;line-height:1.7}.exam-policy-summary i{margin-top:.15rem}.exam-controls-head{margin-top:.15rem;border-top:1px solid #e2e8f0}.exam-controls-head h2{font-size:.82rem!important}
    @media(max-width:700px){.admin-filter-grid{grid-template-columns:1fr!important}.admin-exam-wide{grid-column:auto}.exam-policy-options--3,.exam-policy-options--2{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.admin.shell-end')
