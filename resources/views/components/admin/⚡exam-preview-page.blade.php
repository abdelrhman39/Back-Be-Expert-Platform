<?php

use App\Models\Exam;
use App\Models\ExamAccommodation;
use App\Models\ExamAttempt;
use App\Models\ExamCandidate;
use App\Services\ExamPublicationService;
use App\Services\ExamReadinessService;
use App\Support\ExamOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('معاينة وجاهزية الاختبار | لوحة التحكم')]
class extends Component
{
    public Exam $exam;
    public string $flashMessage = '';
    public string $candidateSearch = '';

    public function mount(Exam $exam): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $this->exam = $exam->load(['section', 'course']);
    }

    #[Computed]
    public function readiness(): array
    {
        return app(ExamReadinessService::class)->inspect($this->exam);
    }

    #[Computed]
    public function previewQuestions()
    {
        $questions = collect();

        foreach ($this->readiness['blueprint']['parts'] ?? [] as $part) {
            foreach ($part['fixed'] ?? [] as $item) {
                $snapshot = $item['snapshot'];
                $snapshot['part_title'] = $part['title'];
                $questions->push($snapshot);
            }

            $pool = $part['pool'] ?? [];

            foreach (collect($pool['items'] ?? [])->take((int) ($pool['draw_count'] ?? 0)) as $item) {
                $snapshot = $item['snapshot'];
                $snapshot['part_title'] = $part['title'];
                $questions->push($snapshot);
            }
        }

        return $questions->values();
    }

    #[Computed]
    public function candidateAccess()
    {
        $candidates = ExamCandidate::query()
            ->with(['student', 'user'])
            ->where('exam_id', $this->exam->id)
            ->where('status', 'eligible')
            ->when($this->candidateSearch, fn ($query) => $query->where(function ($nested) {
                $nested->where('student_name', 'like', '%'.$this->candidateSearch.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->candidateSearch.'%');
            }))
            ->orderBy('student_name')
            ->limit(100)
            ->get();
        $studentIds = $candidates->pluck('student_id');
        $attemptCounts = ExamAttempt::query()
            ->where('exam_id', $this->exam->id)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');
        $accommodations = ExamAccommodation::query()
            ->where('exam_id', $this->exam->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        return $candidates->map(fn (ExamCandidate $candidate) => [
            'id' => $candidate->id,
            'student_id' => $candidate->student_id,
            'name' => $candidate->student_name,
            'academic_id' => $candidate->academic_id,
            'attempts' => (int) ($attemptCounts[$candidate->student_id] ?? 0),
            'accommodation' => $accommodations->get($candidate->student_id),
        ]);
    }

    public function grantExtraAttempt(int $studentId): void
    {
        $this->authorizeCandidate($studentId);
        $accommodation = ExamAccommodation::query()->firstOrCreate(
            ['exam_id' => $this->exam->id, 'student_id' => $studentId],
            ['approved_by' => auth()->id()]
        );
        $nextExtra = min(255, (int) $accommodation->extra_attempts + 1);
        $accommodation->update([
            'extra_attempts' => $nextExtra,
            'unlimited_attempts' => $nextExtra >= 255,
            'override_exam_availability' => true,
            'opens_at' => now(),
            'closes_at' => null,
            'approved_by' => auth()->id(),
        ]);
        unset($this->candidateAccess);
        $this->flashMessage = 'تم فتح محاولة إضافية للطالب فوراً.';
    }

    public function toggleUnlimitedAttempts(int $studentId): void
    {
        $this->authorizeCandidate($studentId);
        $accommodation = ExamAccommodation::query()->firstOrCreate(
            ['exam_id' => $this->exam->id, 'student_id' => $studentId],
            ['approved_by' => auth()->id()]
        );
        $enable = ! $accommodation->unlimited_attempts;
        $accommodation->update([
            'unlimited_attempts' => $enable,
            'override_exam_availability' => $enable || $accommodation->override_exam_availability,
            'opens_at' => $enable ? now() : $accommodation->opens_at,
            'closes_at' => $enable ? null : $accommodation->closes_at,
            'approved_by' => auth()->id(),
        ]);
        unset($this->candidateAccess);
        $this->flashMessage = $enable ? 'تم تفعيل المحاولات غير المحدودة للطالب.' : 'تم إيقاف المحاولات غير المحدودة.';
    }

    public function revokeIndividualReopen(int $studentId): void
    {
        $this->authorizeCandidate($studentId);
        ExamAccommodation::query()
            ->where('exam_id', $this->exam->id)
            ->where('student_id', $studentId)
            ->update([
                'unlimited_attempts' => false,
                'override_exam_availability' => false,
                'opens_at' => null,
                'closes_at' => null,
                'approved_by' => auth()->id(),
            ]);
        unset($this->candidateAccess);
        $this->flashMessage = 'تم إيقاف إعادة الفتح الفردية للطالب.';
    }

    public function publish(ExamPublicationService $publications): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $publication = $publications->publish($this->exam, auth()->user());
        $this->exam->refresh();
        unset($this->readiness, $this->previewQuestions);
        $this->flashMessage = "تم نشر النسخة {$publication->version} وتثبيت محتواها بنجاح.";
    }

    private function authorizeCandidate(int $studentId): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        abort_unless($this->exam->candidates()
            ->where('student_id', $studentId)
            ->where('status', 'eligible')
            ->exists(), 404);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.exams'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.exams'), 'label' => 'الاختبارات'],
        ['href' => route('admin.exams.builder', $exam), 'label' => 'بناء الاختبار'],
        ['label' => 'المعاينة والجاهزية'],
    ],
])

@php($report = $this->readiness)

<div class="exam-preview-hero">
    <div>
        <span><i class="fa-solid fa-shield-check"></i> المراجعة النهائية قبل النشر</span>
        <h1>{{ $exam->title }}</h1>
        <p>{{ $exam->course?->name_ar }} · {{ $exam->section?->name }}</p>
    </div>
    <div class="exam-preview-hero__actions">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-preview-help'))" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-regular fa-circle-question"></i> شرح هذه الصفحة</button>
        <a href="{{ route('admin.exams.integrity', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-shield-halved"></i> مراقبة النزاهة</a>
        <a href="{{ route('admin.exams.builder', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-arrow-right"></i> العودة للأسئلة</a>
        <a href="{{ route('admin.exams.edit', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-sliders"></i> الإعدادات</a>
        <button type="button" wire:click="publish" wire:confirm="نشر نسخة ثابتة من الاختبار وإشعار الطلاب المرشحين؟" wire:loading.attr="disabled" class="admin-btn-primary admin-btn-primary--sm" @disabled(!$report['ready'])>
            <i class="fa-solid fa-paper-plane"></i> {{ $exam->status === 'published' ? 'نشر نسخة جديدة' : 'نشر الاختبار' }}
        </button>
    </div>
</div>

<div class="exam-preview-help" x-data="{ open: false }" x-show="open" x-cloak x-transition.opacity @open-preview-help.window="open=true" @keydown.escape.window="open=false" @click.self="open=false">
    <div class="exam-preview-help__dialog">
        <header><div><span>دليل الجزء الجديد</span><h2>المعاينة وفحص جاهزية الاختبار</h2></div><button type="button" @click="open=false" aria-label="إغلاق">×</button></header>
        <div class="exam-preview-help__content">
            <article><i class="fa-solid fa-gauge-high"></i><div><h3>درجة الجاهزية</h3><p>تلخص سلامة الإعدادات والأسئلة والإجابات والمواعيد والدرجات. وجود خطأ أحمر يمنع النشر حتى تتم معالجته.</p></div></article>
            <article><i class="fa-solid fa-eye"></i><div><h3>معاينة الطالب</h3><p>تعرض نموذجاً تمثيلياً لطريقة ظهور الأسئلة وحقول الإجابة دون إنشاء محاولة حقيقية أو حفظ بيانات.</p></div></article>
            <article><i class="fa-solid fa-triangle-exclamation"></i><div><h3>الأخطاء والتنبيهات</h3><p>الأخطاء يجب إصلاحها، أما التنبيهات فهي ملاحظات مهمة مثل وجود تصحيح يدوي أو عدم وجود طلاب مرشحين.</p></div></article>
            <article><i class="fa-solid fa-lock"></i><div><h3>نسخة نشر ثابتة</h3><p>عند النشر تُحفظ نسخة مشفرة من المحتوى والدرجات والإعدادات. تعديلاتك اللاحقة لا تغيّر المحاولات المرتبطة بها.</p></div></article>
            <article><i class="fa-solid fa-code-branch"></i><div><h3>نشر نسخة جديدة</h3><p>بعد تعديل اختبار منشور، افحصه مجدداً ثم انشر نسخة جديدة. المحاولات القديمة تبقى مرتبطة بنسختها الأصلية.</p></div></article>
            <article><i class="fa-solid fa-user-clock"></i><div><h3>استثناءات المحاولات</h3><p>أسفل الصفحة يمكنك فتح محاولة إضافية لطالب محدد، أو منحه محاولات غير محدودة، أو إلغاء إعادة الفتح دون التأثير على بقية الطلاب.</p></div></article>
        </div>
        <footer><strong>المسار الصحيح:</strong><span>راجع التقرير ← أصلح الأخطاء ← افحص نموذج الطالب ← انشر الاختبار.</span></footer>
    </div>
</div>

@if($flashMessage)<div class="admin-alert admin-alert--success is-visible">{{ $flashMessage }}</div>@endif
@error('publish')<div class="admin-alert admin-alert--danger is-visible">{{ $message }}</div>@enderror

<div class="exam-preview-layout">
    <aside class="exam-readiness">
        <div @class(['exam-readiness__score','is-ready'=>$report['ready'],'has-errors'=>!$report['ready']])>
            <div class="exam-readiness__ring" style="--score:{{ $report['score'] }}"><strong>{{ $report['score'] }}%</strong></div>
            <div><span>درجة الجاهزية</span><h2>{{ $report['ready'] ? 'جاهز للنشر' : 'يحتاج مراجعة' }}</h2><p>{{ $report['errors_count'] }} أخطاء · {{ $report['warnings_count'] }} تنبيهات</p></div>
        </div>

        <div class="exam-readiness__stats">
            <div><strong>{{ $report['question_count'] }}</strong><span>سؤال للطالب</span></div>
            <div><strong>{{ $report['total_points'] }}</strong><span>درجة</span></div>
            <div><strong>{{ $report['candidate_count'] }}</strong><span>طالب مرشح</span></div>
            <div><strong>{{ $report['manual_count'] }}</strong><span>تصحيح يدوي</span></div>
        </div>

        <div class="exam-readiness__issues">
            <h3>نتيجة الفحص</h3>
            @forelse($report['issues'] as $issue)
                <article class="is-{{ $issue['severity'] }}">
                    <i class="fa-solid {{ $issue['severity']==='error' ? 'fa-circle-xmark' : 'fa-triangle-exclamation' }}"></i>
                    <div><strong>{{ $issue['title'] }}</strong><p>{{ $issue['detail'] }}</p></div>
                </article>
            @empty
                <article class="is-success"><i class="fa-solid fa-circle-check"></i><div><strong>اكتمل الفحص بنجاح</strong><p>لم يتم العثور على أخطاء أو تنبيهات تمنع النشر.</p></div></article>
            @endforelse
        </div>

        @if($exam->latestPublication())
            <div class="exam-readiness__publication">
                <i class="fa-solid fa-lock"></i>
                <div><strong>آخر نسخة ثابتة: {{ $exam->latestPublication()->version }}</strong><span>{{ $exam->latestPublication()->published_at?->format('Y/m/d H:i') }}</span></div>
            </div>
        @endif
    </aside>

    <main class="exam-student-preview">
        <header class="exam-student-preview__head">
            <div><span>معاينة شاشة الطالب</span><h2>{{ $exam->title }}</h2><p>{{ $exam->instructions ?: 'لا توجد تعليمات إضافية لهذا الاختبار.' }}</p></div>
            <div><strong>{{ $exam->duration_minutes ?: '∞' }}</strong><small>دقيقة</small></div>
        </header>

        <div class="exam-student-preview__notice"><i class="fa-solid fa-eye"></i><span>هذه معاينة تمثيلية. المجموعة العشوائية قد تعرض أسئلة مختلفة لكل طالب عند بدء المحاولة.</span></div>

        <div class="exam-student-preview__questions">
            @forelse($this->previewQuestions as $question)
                <article>
                    <div class="exam-preview-question__top">
                        <span class="exam-preview-question__number">{{ $loop->iteration }}</span>
                        <div><small>{{ $question['part_title'] }} · {{ ExamOptions::questionTypeLabel($question['type']) }}</small><h3>{{ $question['prompt'] }}</h3></div>
                        <strong>{{ $question['points'] }} درجة</strong>
                    </div>

                    @if(in_array($question['type'], ['single_choice','multiple_choice','true_false'], true))
                        <div class="exam-preview-options">
                            @foreach($question['options'] as $option)
                                <label><input type="{{ $question['type']==='multiple_choice' ? 'checkbox' : 'radio' }}" disabled><span>{{ $option['content'] }}</span></label>
                            @endforeach
                        </div>
                    @elseif($question['type']==='fill_blank')
                        <div class="exam-preview-inputs">@for($i=0;$i<(int)($question['settings']['blank_count']??1);$i++)<input disabled placeholder="إجابة الفراغ {{ $i+1 }}">@endfor</div>
                    @elseif($question['type']==='matching')
                        <div class="exam-preview-inputs">@foreach($question['options'] as $option)<label><span>{{ $option['content'] }}</span><select disabled><option>اختر المطابقة</option></select></label>@endforeach</div>
                    @elseif($question['type']==='ordering')
                        <div class="exam-preview-order">@foreach($question['options'] as $option)<span><i class="fa-solid fa-grip-vertical"></i>{{ $option['content'] }}</span>@endforeach</div>
                    @elseif($question['type']==='essay' || $question['type']==='short_text')
                        <textarea disabled rows="4" placeholder="يكتب الطالب إجابته هنا..."></textarea>
                    @elseif($question['type']==='file_upload')
                        <div class="exam-preview-upload"><i class="fa-solid fa-cloud-arrow-up"></i><span>رفع ملف الإجابة</span></div>
                    @else
                        <input disabled type="text" placeholder="إجابة الطالب">
                    @endif
                </article>
            @empty
                <div class="admin-exam-empty"><i class="fa-solid fa-file-circle-xmark"></i><strong>لا توجد أسئلة للمعاينة</strong></div>
            @endforelse
        </div>
    </main>
</div>

<section class="admin-crud-card exam-candidate-access">
    <header class="exam-candidate-access__head">
        <div><span><i class="fa-solid fa-user-clock"></i> استثناءات المحاولات</span><h2>إعادة فتح الاختبار لطالب</h2><p>امنح محاولة إضافية أو افتح محاولات غير محدودة دون التأثير على بقية الطلاب.</p></div>
        <label><i class="fa-solid fa-magnifying-glass"></i><input type="search" wire:model.live.debounce.300ms="candidateSearch" placeholder="اسم الطالب أو الرقم الأكاديمي"></label>
    </header>
    <div class="exam-candidate-access__table">
        @forelse($this->candidateAccess as $row)
            @php($accommodation = $row['accommodation'])
            <article wire:key="exam-candidate-access-{{ $row['student_id'] }}">
                <div class="exam-candidate-access__student"><span>{{ mb_substr($row['name'], 0, 1) }}</span><div><strong>{{ $row['name'] }}</strong><small>{{ $row['academic_id'] ?: 'دون رقم أكاديمي' }}</small></div></div>
                <div class="exam-candidate-access__usage"><strong>{{ $row['attempts'] }}</strong><span>محاولة منفذة</span></div>
                <div class="exam-candidate-access__state">
                    @if($accommodation?->unlimited_attempts)
                        <span class="is-unlimited"><i class="fa-solid fa-infinity"></i> محاولات غير محدودة</span>
                    @elseif($accommodation?->override_exam_availability)
                        <span class="is-open"><i class="fa-solid fa-lock-open"></i> مفتوح فردياً · +{{ $accommodation->extra_attempts }}</span>
                    @else
                        <span><i class="fa-solid fa-user-lock"></i> الإعدادات العامة</span>
                    @endif
                </div>
                <div class="exam-candidate-access__actions">
                    <button type="button" wire:click="grantExtraAttempt({{ $row['student_id'] }})" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-rotate-right"></i> محاولة إضافية</button>
                    <button type="button" wire:click="toggleUnlimitedAttempts({{ $row['student_id'] }})" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-infinity"></i> {{ $accommodation?->unlimited_attempts ? 'إيقاف غير المحدود' : 'فتح بلا نهاية' }}</button>
                    @if($accommodation?->override_exam_availability)
                        <button type="button" wire:click="revokeIndividualReopen({{ $row['student_id'] }})" wire:confirm="إيقاف إعادة الفتح الفردية لهذا الطالب؟" class="exam-candidate-access__revoke" title="إيقاف إعادة الفتح"><i class="fa-solid fa-xmark"></i></button>
                    @endif
                </div>
            </article>
        @empty
            <div class="exam-candidate-access__empty"><i class="fa-solid fa-users-slash"></i><span>لا يوجد طلاب مرشحون بعد. انشر الاختبار أولاً لتثبيت قائمة المرشحين.</span></div>
        @endforelse
    </div>
</section>

@push('styles')
<style>
    .exam-preview-hero{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;padding:1.15rem 1.3rem;border-radius:17px;background:linear-gradient(135deg,#103d2c,#1b7650);color:#fff;box-shadow:0 14px 30px rgba(15,81,50,.15)}.exam-preview-hero>div>span{color:#a7e8c3;font-size:.65rem;font-weight:900}.exam-preview-hero h1{margin:.25rem 0;color:#fff;font-size:1.25rem}.exam-preview-hero p{margin:0;color:#c6ded1;font-size:.68rem}.exam-preview-hero__actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.4rem}.exam-preview-hero .admin-btn-secondary{border-color:rgba(255,255,255,.25);background:rgba(255,255,255,.1);color:#fff}
    .exam-preview-layout{display:grid;grid-template-columns:20rem minmax(0,1fr);gap:1rem;align-items:start}.exam-readiness{position:sticky;top:1rem;display:flex;flex-direction:column;gap:.7rem}.exam-readiness__score{display:flex;align-items:center;gap:.75rem;padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.exam-readiness__ring{--color:#dc2626;display:grid;place-items:center;width:4.2rem;height:4.2rem;border-radius:50%;background:conic-gradient(var(--color) calc(var(--score)*1%),#e2e8f0 0);position:relative}.is-ready .exam-readiness__ring{--color:#16a34a}.exam-readiness__ring:after{content:"";position:absolute;inset:5px;border-radius:50%;background:#fff}.exam-readiness__ring strong{z-index:1;color:#334155;font-size:.85rem}.exam-readiness__score span{color:#64748b;font-size:.57rem}.exam-readiness__score h2{margin:.1rem 0;font-size:.85rem}.exam-readiness__score p{margin:0;color:#64748b;font-size:.58rem}
    .exam-readiness__stats{display:grid;grid-template-columns:1fr 1fr;gap:.45rem}.exam-readiness__stats>div{display:flex;align-items:center;flex-direction:column;padding:.65rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff}.exam-readiness__stats strong{color:#166534;font-size:1rem}.exam-readiness__stats span{color:#64748b;font-size:.56rem}.exam-readiness__issues{padding:.85rem;border:1px solid #e2e8f0;border-radius:13px;background:#fff}.exam-readiness__issues h3{margin:0 0 .6rem;font-size:.77rem}.exam-readiness__issues article{display:flex;align-items:flex-start;gap:.45rem;margin-top:.4rem;padding:.55rem;border-radius:8px;font-size:.62rem}.exam-readiness__issues article.is-error{background:#fef2f2;color:#991b1b}.exam-readiness__issues article.is-warning{background:#fff7ed;color:#9a3412}.exam-readiness__issues article.is-success{background:#f0fdf4;color:#166534}.exam-readiness__issues article i{margin-top:.12rem}.exam-readiness__issues article strong{font-size:.64rem}.exam-readiness__issues article p{margin:.15rem 0 0;line-height:1.55}.exam-readiness__publication{display:flex;align-items:center;gap:.5rem;padding:.7rem;border:1px solid #bbf7d0;border-radius:10px;background:#f0fdf4;color:#166534}.exam-readiness__publication div{display:flex;flex-direction:column}.exam-readiness__publication strong{font-size:.63rem}.exam-readiness__publication span{font-size:.55rem}
    .exam-student-preview{overflow:hidden;border:1px solid #e2e8f0;border-radius:15px;background:#f8fafc;box-shadow:0 10px 25px rgba(15,23,42,.05)}.exam-student-preview__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.25rem;background:#fff;border-bottom:1px solid #e2e8f0}.exam-student-preview__head span{color:#168251;font-size:.58rem;font-weight:900}.exam-student-preview__head h2{margin:.15rem 0;font-size:1rem}.exam-student-preview__head p{margin:0;color:#64748b;font-size:.63rem}.exam-student-preview__head>div:last-child{display:flex;align-items:center;flex-direction:column;padding:.45rem .75rem;border-radius:10px;background:#ecfdf5;color:#166534}.exam-student-preview__head strong{font-size:1.1rem}.exam-student-preview__head small{font-size:.52rem}.exam-student-preview__notice{display:flex;align-items:center;gap:.4rem;margin:.75rem;padding:.55rem .65rem;border-radius:8px;background:#eff6ff;color:#1e40af;font-size:.61rem}.exam-student-preview__questions{display:flex;flex-direction:column;gap:.7rem;padding:.75rem}.exam-student-preview__questions>article{padding:1rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.exam-preview-question__top{display:grid;grid-template-columns:auto 1fr auto;align-items:start;gap:.65rem}.exam-preview-question__number{display:grid;place-items:center;width:1.8rem;height:1.8rem;border-radius:8px;background:#166534;color:#fff;font-size:.7rem;font-weight:900}.exam-preview-question__top small{color:#64748b;font-size:.55rem}.exam-preview-question__top h3{margin:.25rem 0;color:#1e293b;font-size:.78rem;line-height:1.7}.exam-preview-question__top>strong{padding:.25rem .4rem;border-radius:7px;background:#fffbeb;color:#92400e;font-size:.62rem;white-space:nowrap}
    .exam-preview-options{display:grid;grid-template-columns:1fr 1fr;gap:.45rem;margin-top:.7rem}.exam-preview-options label{display:flex;align-items:center;gap:.4rem;padding:.55rem;border:1px solid #e2e8f0;border-radius:8px;color:#475569;font-size:.65rem}.exam-preview-inputs{display:flex;flex-direction:column;gap:.4rem;margin-top:.7rem}.exam-preview-inputs label{display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:.5rem;color:#475569;font-size:.64rem}.exam-preview-inputs input,.exam-preview-inputs select,.exam-student-preview textarea,.exam-student-preview__questions>article>input{width:100%;padding:.55rem;border:1px solid #d8e0dc;border-radius:8px;background:#f8fafc;font-size:.63rem}.exam-preview-order{display:flex;flex-direction:column;gap:.35rem;margin-top:.7rem}.exam-preview-order span{display:flex;align-items:center;gap:.4rem;padding:.5rem;border:1px solid #e2e8f0;border-radius:8px;color:#475569;font-size:.64rem}.exam-preview-order i{color:#94a3b8}.exam-student-preview textarea,.exam-student-preview__questions>article>input{margin-top:.7rem}.exam-preview-upload{display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.3rem;margin-top:.7rem;padding:1rem;border:1px dashed #86b99d;border-radius:9px;color:#166534;font-size:.64rem}.exam-preview-upload i{font-size:1.2rem}
    .exam-preview-help[x-cloak]{display:none!important}.exam-preview-help{position:fixed;z-index:10000;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(7,23,16,.72);backdrop-filter:blur(5px)}.exam-preview-help__dialog{width:min(42rem,95vw);overflow:hidden;border-radius:16px;background:#fff;box-shadow:0 28px 70px rgba(0,0,0,.3)}.exam-preview-help header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.15rem;background:linear-gradient(135deg,#0d3324,#196044);color:#fff}.exam-preview-help header span{color:#8fe0b4;font-size:.58rem;font-weight:900}.exam-preview-help header h2{margin:.15rem 0 0;color:#fff;font-size:1rem}.exam-preview-help header button{width:2rem;height:2rem;border:1px solid rgba(255,255,255,.2);border-radius:8px;background:rgba(255,255,255,.08);color:#fff;font-size:1.2rem}.exam-preview-help__content{display:grid;grid-template-columns:1fr 1fr;gap:.55rem;padding:1rem}.exam-preview-help__content article{display:flex;align-items:flex-start;gap:.55rem;padding:.7rem;border:1px solid #e2e8f0;border-radius:10px}.exam-preview-help__content article>i{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:8px;background:#ecfdf5;color:#166534}.exam-preview-help__content h3{margin:0;color:#334155;font-size:.7rem}.exam-preview-help__content p{margin:.2rem 0 0;color:#64748b;font-size:.61rem;line-height:1.65}.exam-preview-help footer{display:flex;gap:.4rem;padding:.75rem 1rem;background:#f8fafc;color:#475569;font-size:.63rem}.exam-preview-help footer strong{color:#166534}
    .exam-candidate-access{margin-top:1rem;padding:0;overflow:hidden}.exam-candidate-access__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.15rem;border-bottom:1px solid #e2e8f0;background:linear-gradient(180deg,#fff,#f8fafc)}.exam-candidate-access__head>div>span{color:#168251;font-size:.58rem;font-weight:900}.exam-candidate-access__head h2{margin:.15rem 0;font-size:.95rem}.exam-candidate-access__head p{margin:0;color:#64748b;font-size:.62rem}.exam-candidate-access__head label{display:flex;align-items:center;gap:.4rem;width:min(20rem,100%);padding:.55rem .65rem;border:1px solid #d8e0dc;border-radius:9px;background:#fff;color:#94a3b8}.exam-candidate-access__head input{width:100%;border:0;outline:0;font-size:.65rem}.exam-candidate-access__table{display:flex;flex-direction:column}.exam-candidate-access__table>article{display:grid;grid-template-columns:minmax(13rem,1.2fr) 7rem minmax(10rem,.8fr) auto;align-items:center;gap:.7rem;padding:.7rem 1rem;border-bottom:1px solid #f1f5f9}.exam-candidate-access__student{display:flex;align-items:center;gap:.55rem}.exam-candidate-access__student>span{display:grid;place-items:center;width:2.2rem;height:2.2rem;border-radius:9px;background:#ecfdf5;color:#166534;font-weight:900}.exam-candidate-access__student div{display:flex;flex-direction:column}.exam-candidate-access__student strong{font-size:.68rem}.exam-candidate-access__student small{color:#64748b;font-size:.55rem}.exam-candidate-access__usage{display:flex;align-items:center;flex-direction:column}.exam-candidate-access__usage strong{color:#334155}.exam-candidate-access__usage span{color:#64748b;font-size:.53rem}.exam-candidate-access__state>span{display:inline-flex;align-items:center;gap:.25rem;padding:.28rem .45rem;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.55rem;font-weight:800}.exam-candidate-access__state .is-open{background:#eff6ff;color:#1d4ed8}.exam-candidate-access__state .is-unlimited{background:#dcfce7;color:#166534}.exam-candidate-access__actions{display:flex;justify-content:flex-end;gap:.35rem}.exam-candidate-access__revoke{display:grid;place-items:center;width:1.8rem;height:1.8rem;border:0;border-radius:7px;background:#fef2f2;color:#b91c1c}.exam-candidate-access__empty{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:1.5rem;color:#64748b;font-size:.65rem}
    @media(max-width:1100px){.exam-candidate-access__table>article{grid-template-columns:1fr auto}.exam-candidate-access__state,.exam-candidate-access__actions{grid-column:1/-1}.exam-candidate-access__actions{justify-content:flex-start}}
    @media(max-width:1000px){.exam-preview-layout{grid-template-columns:1fr}.exam-readiness{position:static}.exam-readiness__stats{grid-template-columns:repeat(4,1fr)}}@media(max-width:650px){.exam-preview-hero,.exam-student-preview__head,.exam-candidate-access__head{align-items:flex-start;flex-direction:column}.exam-preview-hero__actions{justify-content:flex-start}.exam-readiness__stats,.exam-preview-options,.exam-preview-help__content{grid-template-columns:1fr}.exam-preview-question__top{grid-template-columns:auto 1fr}.exam-preview-question__top>strong{grid-column:2}.exam-preview-help{padding:.4rem}.exam-preview-help__dialog{max-height:95vh;overflow-y:auto}.exam-preview-help footer{flex-direction:column}.exam-candidate-access__table>article{grid-template-columns:1fr}.exam-candidate-access__state,.exam-candidate-access__actions{grid-column:1}.exam-candidate-access__actions{flex-wrap:wrap}}
</style>
@endpush

@include('partials.admin.shell-end')
