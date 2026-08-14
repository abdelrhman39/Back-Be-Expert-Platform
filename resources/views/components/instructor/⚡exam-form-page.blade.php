<?php

use App\Models\AcademicSection;
use App\Models\Exam;
use App\Services\InstructorService;
use App\Support\ExamOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('إعداد اختبار | لوحة المدرب')]
class extends Component
{
    public AcademicSection $section;

    public ?int $examId = null;

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

    public function mount(AcademicSection $section, ?Exam $exam = null, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $section);
        $instructors->authorizePermission(
            auth()->user(),
            $exam?->exists ? 'instructor.exams.update' : 'instructor.exams.create'
        );

        if ($exam?->exists) {
            abort_unless($exam->section_id === $section->id, 404);
        }

        $this->section = $section->load(['course', 'program']);

        if (! $exam?->exists) {
            return;
        }

        $this->examId = $exam->id;
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

    public function save(InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(
            auth()->user(),
            $this->examId ? 'instructor.exams.update' : 'instructor.exams.create'
        );

        $rules = [
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
            'shuffleQuestions' => ['boolean'],
            'shuffleOptions' => ['boolean'],
            'oneQuestionPerPage' => ['boolean'],
            'allowBackNavigation' => ['boolean'],
            'requireAccessCode' => ['boolean'],
            'resultRelease' => ['required', Rule::in(array_keys(ExamOptions::resultReleasePolicies()))],
            'reviewPolicy' => ['required', Rule::in(array_keys(ExamOptions::reviewPolicies()))],
        ];

        if ($this->requireAccessCode && (! $this->hasStoredAccessCode || $this->accessCode !== '')) {
            $rules['accessCode'] = ['required', 'string', 'min:4', 'max:100'];
        }

        $validated = $this->validate($rules, [], [
            'title' => 'عنوان الاختبار',
            'titleEn' => 'عنوان الاختبار بالإنجليزية',
            'opensAt' => 'موعد الفتح',
            'closesAt' => 'موعد الإغلاق',
            'durationMinutes' => 'مدة الاختبار',
            'attemptPolicy' => 'سياسة المحاولات',
            'maxAttempts' => 'عدد المحاولات',
            'gradeSelection' => 'طريقة اعتماد الدرجة',
            'accessCode' => 'رمز الدخول',
        ]);

        $exam = DB::transaction(function () use ($validated) {
            $exam = $this->examId
                ? Exam::query()->where('section_id', $this->section->id)->findOrFail($this->examId)
                : new Exam;

            $exam->fill([
                'section_id' => $this->section->id,
                'course_id' => $this->section->course_id,
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
                'shuffle_questions' => $validated['shuffleQuestions'],
                'shuffle_options' => $validated['shuffleOptions'],
                'one_question_per_page' => $validated['oneQuestionPerPage'],
                'allow_back_navigation' => $validated['allowBackNavigation'],
                'require_access_code' => $validated['requireAccessCode'],
                'result_release' => $validated['resultRelease'],
                'review_policy' => $validated['reviewPolicy'],
            ]);

            if (! $validated['requireAccessCode']) {
                $exam->access_code_hash = null;
            } elseif ($this->accessCode !== '') {
                $exam->access_code_hash = Hash::make($this->accessCode);
            }

            $exam->save();

            if ($exam->parts()->doesntExist()) {
                $exam->parts()->create([
                    'title' => 'أسئلة الاختبار',
                    'sort_order' => 1,
                    'shuffle_questions' => $exam->shuffle_questions,
                ]);
            }

            return $exam;
        });

        session()->flash('exam_message', $this->examId ? 'تم تحديث إعدادات الاختبار.' : 'تم إنشاء الاختبار. أضف الأسئلة الآن.');
        $this->redirectRoute('instructor.exams.builder', [
            'locale' => app()->getLocale(),
            'section' => $this->section->id,
            'exam' => $exam->id,
        ], navigate: true);
    }
};
?>

@include('partials.instructor.shell-start', [
    'instructorActive' => 'exams',
    'instructorTitle' => $examId ? 'إعدادات الاختبار' : 'اختبار جديد',
])

<div class="portal-dashboard portal-instructor-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ $examId ? 'تعديل إعدادات الاختبار' : 'إنشاء اختبار جديد' }}</h1>
            <p class="portal-orders-intro__desc">{{ $section->name }} · {{ $section->course?->name_ar }}</p>
        </div>
    </div>

    <form wire:submit="save" class="portal-inst-section exam-form">
        <header class="portal-inst-section__head">
            <h2>البيانات الأساسية</h2>
            <p>يمكن تعديل هذه الإعدادات قبل النشر، مع الحفاظ على المحاولات السابقة.</p>
        </header>

        <div class="exam-form__grid">
            <label class="exam-field exam-field--wide"><span>سياسة لغة الاختبار *</span><select wire:model.live="languagePolicy">@foreach(ExamOptions::languagePolicies() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><small>يمكن فرض لغة واحدة أو اتباع لغة الطالب أو ترك الاختيار له.</small></label>
            <label class="exam-field"><span>العنوان بالعربية {{ $languagePolicy !== 'en_only' ? '*' : '' }}</span><input type="text" wire:model="title" dir="rtl">@error('title')<small>{{ $message }}</small>@enderror</label>
            <label class="exam-field"><span>English title {{ $languagePolicy !== 'ar_only' ? '*' : '' }}</span><input type="text" wire:model="titleEn" dir="ltr">@error('titleEn')<small>{{ $message }}</small>@enderror</label>
            <label class="exam-field"><span>نوع التقييم</span><select wire:model="type">@foreach(ExamOptions::examTypes() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="exam-field"><span>درجة النجاح %</span><input type="number" min="0" max="100" step=".01" wire:model="passingPercent"></label>
            <label class="exam-field"><span>التعليمات بالعربية</span><textarea rows="5" wire:model="instructions" dir="rtl"></textarea></label>
            <label class="exam-field"><span>Instructions in English</span><textarea rows="5" wire:model="instructionsEn" dir="ltr"></textarea></label>
            <label class="exam-field"><span>يفتح في</span><input type="datetime-local" wire:model="opensAt">@error('opensAt')<small>{{ $message }}</small>@enderror</label>
            <label class="exam-field"><span>يغلق في</span><input type="datetime-local" wire:model="closesAt">@error('closesAt')<small>{{ $message }}</small>@enderror</label>
            <label class="exam-field"><span>المدة بالدقائق</span><input type="number" min="1" max="1440" wire:model="durationMinutes" placeholder="بدون حد"></label>
            <label class="exam-field"><span>إعلان النتيجة</span><select wire:model="resultRelease">@foreach(ExamOptions::resultReleasePolicies() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
            <label class="exam-field">
                <span>ما الذي يمكن للطالب رؤيته بعد التصحيح؟</span>
                <select wire:model="reviewPolicy">@foreach(ExamOptions::reviewPolicies() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                <small class="exam-field-hint">اختر «عرض التصحيح» للسماح بمقارنة إجابة الطالب بالإجابة الصحيحة مع شرح السؤال وملاحظة المصحح.</small>
            </label>
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
                @error('attemptPolicy')<small>{{ $message }}</small>@enderror
                @if ($attemptPolicy === 'limited')
                    <label class="exam-field exam-attempt-count">
                        <span>الحد الأقصى للمحاولات *</span>
                        <input type="number" min="2" max="20" wire:model="maxAttempts">
                        <small class="exam-field-hint">يشمل هذا الرقم المحاولة الأولى.</small>
                        @error('maxAttempts')<small>{{ $message }}</small>@enderror
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
                                <small>{{ $value === 'highest' ? 'تُعتمد أفضل نسبة من جميع المحاولات المصححة' : 'تُعتمد نتيجة أحدث محاولة مصححة' }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('gradeSelection')<small>{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="exam-switches">
            <label><input type="checkbox" wire:model="shuffleQuestions"> ترتيب الأسئلة عشوائياً</label>
            <label><input type="checkbox" wire:model="shuffleOptions"> ترتيب الخيارات عشوائياً</label>
            <label><input type="checkbox" wire:model="oneQuestionPerPage"> سؤال واحد في الصفحة</label>
            <label><input type="checkbox" wire:model="allowBackNavigation"> السماح بالعودة للأسئلة</label>
            <label><input type="checkbox" wire:model.live="requireAccessCode"> يتطلب رمز دخول</label>
        </div>

        @if ($requireAccessCode)
            <label class="exam-field exam-field--code">
                <span>{{ $examId ? 'رمز دخول جديد (اتركه فارغاً للإبقاء على الحالي)' : 'رمز الدخول *' }}</span>
                <input type="text" wire:model="accessCode" dir="ltr" autocomplete="off">
                @error('accessCode')<small>{{ $message }}</small>@enderror
            </label>
        @endif

        <div class="exam-form__actions">
            <a href="{{ route('instructor.exams', ['locale' => app()->getLocale()]) }}" class="portal-btn portal-btn--secondary">إلغاء</a>
            <button type="submit" class="portal-btn portal-btn--primary" wire:loading.attr="disabled"><i class="fa-solid fa-arrow-left"></i> حفظ والمتابعة لبناء الأسئلة</button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .exam-form__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}.exam-field{display:flex;flex-direction:column;gap:.35rem}.exam-field--wide{grid-column:1/-1}.exam-field>span{font-size:.75rem;font-weight:900;color:#334155}.exam-field input,.exam-field select,.exam-field textarea{width:100%;padding:.65rem .75rem;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#0f172a;font:inherit;font-size:.8rem}.exam-field input:focus,.exam-field select:focus,.exam-field textarea:focus{outline:0;border-color:#1b8354;box-shadow:0 0 0 3px rgba(27,131,84,.1)}.exam-field small{color:#b91c1c;font-size:.7rem}.exam-field-hint{color:#64748b!important}.exam-switches{display:flex;flex-wrap:wrap;gap:.55rem;margin:1rem 0}.exam-switches label{padding:.5rem .65rem;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;font-size:.72rem;font-weight:800}.exam-switches input{accent-color:#1b8354}.exam-field--code{max-width:25rem}.exam-form__actions{display:flex;justify-content:flex-end;gap:.55rem;margin-top:1.2rem;padding-top:1rem;border-top:1px solid #e2e8f0}
    .exam-attempt-policy{display:grid;gap:1rem;margin:1rem 0;padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc}.exam-attempt-policy__group{display:grid;gap:.55rem}.exam-attempt-policy__label{display:flex;align-items:center;gap:.4rem;color:#334155;font-size:.72rem;font-weight:900}.exam-attempt-policy__label i{color:#1b8354}.exam-policy-options{display:grid;gap:.65rem}.exam-policy-options--3{grid-template-columns:repeat(3,minmax(0,1fr))}.exam-policy-options--2{grid-template-columns:repeat(2,minmax(0,1fr))}.exam-policy-options label{display:flex;align-items:flex-start;gap:.65rem;padding:.8rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer}.exam-policy-options input{position:absolute;opacity:0}.exam-policy-options label>i{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:9px;background:#e2e8f0;color:#64748b;font-size:.72rem}.exam-policy-options label>span{display:grid;gap:.15rem}.exam-policy-options strong{color:#334155;font-size:.67rem}.exam-policy-options small{color:#94a3b8;font-size:.55rem;line-height:1.6}.exam-policy-options label.is-active{border-color:#16a34a;background:#f0fdf4;box-shadow:0 0 0 3px rgba(22,163,74,.1)}.exam-policy-options label.is-active>i{background:#16a34a;color:#fff}.exam-attempt-count{max-width:22rem;padding:.7rem;border:1px dashed #bbf7d0;border-radius:11px;background:#f7fdf9}
    @media(max-width:700px){.exam-form__grid{grid-template-columns:1fr}.exam-field--wide{grid-column:auto}.exam-policy-options--3,.exam-policy-options--2{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.instructor.shell-end')
