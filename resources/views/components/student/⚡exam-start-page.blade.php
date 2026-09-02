<?php

use App\Models\Exam;
use App\Services\ExamAttemptService;
use App\Support\ExamOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تفاصيل الاختبار | مركز التعلم المستمر')]
class extends Component
{
    public Exam $exam;
    public string $accessCode = '';
    public string $selectedLanguage = 'ar';

    public function mount(Exam $exam): void
    {
        $student = auth()->user()?->academicStudent;
        abort_unless($student && $exam->studentCanAccess($student), 404);

        $this->exam = $exam->load(['section', 'course', 'parts.questions']);
        $this->selectedLanguage = $exam->resolveLanguage(null, auth()->user());
    }

    #[Computed]
    public function attempts()
    {
        return $this->exam->attempts()
            ->where('student_id', auth()->user()->academicStudent->id)
            ->orderByDesc('attempt_number')
            ->get();
    }

    public function start(ExamAttemptService $attempts): void
    {
        $student = auth()->user()->academicStudent;
        $attempt = $attempts->start(
            $this->exam,
            $student,
            $this->accessCode ?: null,
            request()->ip(),
            request()->userAgent(),
            $this->selectedLanguage,
        );

        $this->redirectRoute('exam-attempts.show', [
            'locale' => app()->getLocale(),
            'attempt' => $attempt->id,
        ], navigate: true);
    }
};
?>

@php
    $locale = app()->getLocale();
    $activeAttempt = $this->attempts->firstWhere('status', 'in_progress');
    $attemptsUsed = $this->attempts->count();
    $student = auth()->user()->academicStudent;
    $attemptLimit = $exam->attemptLimitFor($student);
    $selectedAttempt = $exam->selectAttemptFrom($this->attempts);
    $canAttempt = $exam->isAvailableFor($student) && ($activeAttempt || $attemptLimit === null || $attemptsUsed < $attemptLimit);
    $displayLanguage = $activeAttempt?->language ?? $exam->resolveLanguage($selectedLanguage, auth()->user());
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'exams', 'portalTitle' => 'تفاصيل الاختبار'])

<div class="portal-dashboard exam-start-page">
    <a href="{{ route('exams', ['locale' => $locale]) }}" class="portal-panel__link">← العودة إلى اختباراتي</a>

    @if (session('exam_message'))
        <div class="exam-start-flash" role="status">{{ session('exam_message') }}</div>
    @endif

    <section class="exam-start-hero">
        <div>
            <span>{{ ExamOptions::examTypes()[$exam->type] ?? $exam->type }}</span>
            <h1>{{ $exam->localizedTitle($displayLanguage) }}</h1>
            <p>{{ $exam->course?->name_ar }} · {{ $exam->section?->name }}</p>
        </div>
        <div class="exam-start-hero__status">{{ $canAttempt ? ($activeAttempt ? 'محاولة جارية' : 'متاح الآن') : 'غير متاح' }}</div>
    </section>

    <div class="exam-start-layout">
        <main>
            <section class="portal-panel">
                <div class="portal-panel__head"><h2 class="portal-panel__title">تعليمات الاختبار</h2></div>
                <div class="portal-panel__body portal-panel__body--padded exam-instructions">
                    {!! nl2br(e($exam->localizedInstructions($displayLanguage) ?: ($displayLanguage === 'en' ? 'Read each question carefully, save your answer, then submit the attempt when finished.' : 'اقرأ كل سؤال بعناية، واحفظ إجابتك قبل الانتقال ثم سلّم المحاولة عند الانتهاء.'))) !!}
                </div>
            </section>

            @if ($this->attempts->isNotEmpty())
                <section class="portal-panel">
                    <div class="portal-panel__head"><h2 class="portal-panel__title">المحاولات السابقة</h2></div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <div class="exam-attempt-history">
                            @foreach ($this->attempts as $attempt)
                                <article class="{{ $selectedAttempt?->id === $attempt->id ? 'is-selected' : '' }}">
                                    <span>المحاولة {{ $attempt->attempt_number }} @if($selectedAttempt?->id === $attempt->id)<em>النتيجة المعتمدة</em>@endif</span>
                                    <span>{{ match($attempt->status) { 'in_progress' => 'جارية', 'pending_grading' => 'بانتظار التصحيح', 'graded' => 'تم التصحيح', default => $attempt->status } }}</span>
                                    @if ($exam->resultsAreVisibleFor($attempt))
                                        <strong>{{ $attempt->total_score }}/{{ $attempt->effectiveTotalPoints() }} · {{ $attempt->percentage }}%</strong>
                                    @else
                                        <strong>—</strong>
                                    @endif
                                    @if ($exam->answersAreVisibleFor($attempt))
                                        <a href="{{ route('exam-attempts.review', ['locale' => $locale, 'attempt' => $attempt->id]) }}">
                                            <i class="fa-solid fa-list-check"></i> عرض التصحيح
                                        </a>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </main>

        <aside class="portal-panel exam-start-summary">
            <div class="portal-panel__head"><h2 class="portal-panel__title">ملخص الاختبار</h2></div>
            <div class="portal-panel__body portal-panel__body--padded">
                <dl>
                    <div><dt>عدد الأسئلة</dt><dd>{{ $exam->parts->sum(fn($part) => $part->questions->count()) }}</dd></div>
                    <div><dt>الدرجة</dt><dd>{{ $exam->total_points }}</dd></div>
                    <div><dt>المدة</dt><dd>{{ $exam->duration_minutes ? $exam->duration_minutes.' دقيقة' : 'غير محددة' }}</dd></div>
                    <div><dt>المحاولات</dt><dd>{{ $attemptsUsed }}/{{ $attemptLimit === null ? '∞' : $attemptLimit }}</dd></div>
                    <div><dt>احتساب النتيجة</dt><dd>{{ $exam->grade_selection === 'highest' ? 'أعلى درجة' : 'آخر محاولة' }}</dd></div>
                    <div><dt>درجة النجاح</dt><dd>{{ $exam->passing_percent }}%</dd></div>
                    @if ($exam->closes_at)<div><dt>آخر موعد</dt><dd>{{ $exam->closes_at->translatedFormat('d M Y H:i') }}</dd></div>@endif
                </dl>

                @if ($exam->require_access_code && ! $activeAttempt)
                    <label class="exam-access-code"><span>رمز دخول الاختبار</span><input type="password" wire:model="accessCode" dir="ltr" autocomplete="off">@error('accessCode')<small>{{ $message }}</small>@enderror</label>
                @endif
                @if ($exam->language_policy === 'student_choice' && ! $activeAttempt)
                    <label class="exam-access-code">
                        <span>لغة محاولة الاختبار / Exam language</span>
                        <select wire:model.live="selectedLanguage">
                            <option value="ar">العربية</option>
                            <option value="en">English</option>
                        </select>
                    </label>
                @else
                    <div class="exam-start-notice">لغة الاختبار: {{ $displayLanguage === 'en' ? 'English' : 'العربية' }}</div>
                @endif
                @error('exam')<div class="exam-start-error">{{ $message }}</div>@enderror

                @if ($canAttempt)
                    <button type="button" wire:click="start" wire:confirm="{{ $activeAttempt ? 'متابعة المحاولة الجارية؟' : 'سيبدأ احتساب الوقت فور الدخول. هل أنت مستعد؟' }}" class="btn btn-primary w-100" wire:loading.attr="disabled">
                        <i class="fa-solid fa-play"></i> {{ $activeAttempt ? 'متابعة الاختبار' : 'بدء المحاولة' }}
                    </button>
                @elseif ($exam->opens_at && now()->isBefore($exam->opens_at))
                    <div class="exam-start-notice">يفتح الاختبار {{ $exam->opens_at->translatedFormat('d M Y H:i') }}</div>
                @elseif ($attemptLimit !== null && $attemptsUsed >= $attemptLimit)
                    <div class="exam-start-notice">استنفدت جميع المحاولات المتاحة.</div>
                @else
                    <div class="exam-start-notice">الاختبار مغلق حالياً.</div>
                @endif
            </div>
        </aside>
    </div>
</div>

@push('styles')
<style>
    .exam-start-flash{padding:.75rem 1rem;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:#166534;font-size:.78rem;font-weight:800}.exam-start-hero{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.25rem 1.4rem;border-radius:16px;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff}.exam-start-hero span{font-size:.68rem;opacity:.8}.exam-start-hero h1{margin:.35rem 0 .2rem;color:#fff;font-size:1.4rem}.exam-start-hero p{margin:0;font-size:.78rem;opacity:.85}.exam-start-hero__status{padding:.4rem .65rem;border:1px solid rgba(255,255,255,.25);border-radius:999px;background:rgba(255,255,255,.1);font-size:.72rem;font-weight:900}.exam-start-layout{display:grid;grid-template-columns:minmax(0,1fr) 20rem;gap:1rem;align-items:start}.exam-start-layout main{display:flex;flex-direction:column;gap:1rem}.exam-instructions{font-size:.82rem;line-height:1.9;color:#334155}.exam-start-summary{position:sticky;top:1rem}.exam-start-summary dl{margin:0 0 1rem}.exam-start-summary dl>div{display:flex;justify-content:space-between;gap:.5rem;padding:.55rem 0;border-bottom:1px solid #f1f5f9;font-size:.74rem}.exam-start-summary dt{color:#64748b}.exam-start-summary dd{margin:0;font-weight:900;color:#0f172a}.exam-access-code{display:flex;flex-direction:column;gap:.3rem;margin-bottom:.75rem}.exam-access-code span{font-size:.7rem;font-weight:900}.exam-access-code input{padding:.6rem;border:1px solid #cbd5e1;border-radius:8px}.exam-access-code small,.exam-start-error{color:#b91c1c;font-size:.68rem}.exam-start-error,.exam-start-notice{padding:.6rem;border-radius:8px;background:#fef2f2;margin-bottom:.65rem}.exam-start-notice{background:#f8fafc;color:#64748b;font-size:.72rem}.exam-attempt-history article{display:grid;grid-template-columns:minmax(0,1fr) auto auto auto;align-items:center;gap:.5rem;padding:.6rem;border-bottom:1px solid #f1f5f9;font-size:.72rem}.exam-attempt-history article.is-selected{border-radius:8px;background:#f0fdf4;box-shadow:inset 3px 0 #16a34a}.exam-attempt-history article em{display:inline-flex;margin-inline-start:.3rem;padding:.1rem .35rem;border-radius:999px;background:#dcfce7;color:#166534;font-size:.52rem;font-style:normal;font-weight:900}.exam-attempt-history article>a{display:inline-flex;align-items:center;gap:.25rem;padding:.28rem .5rem;border-radius:7px;background:#166534;color:#fff;font-size:.62rem;font-weight:900}.exam-attempt-history article:last-child{border:0}@media(max-width:850px){.exam-start-layout{grid-template-columns:1fr}.exam-start-summary{position:static}}@media(max-width:560px){.exam-start-hero{align-items:flex-start;flex-direction:column}.exam-attempt-history article{grid-template-columns:1fr auto}.exam-attempt-history strong,.exam-attempt-history article>a{grid-column:2}}
</style>
@endpush

@include('partials.portal.shell-end')
