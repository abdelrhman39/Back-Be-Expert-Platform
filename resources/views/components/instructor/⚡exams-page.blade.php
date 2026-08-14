<?php

use App\Models\Exam;
use App\Services\InstructorService;
use App\Support\ExamOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('الاختبارات | لوحة المدرب')]
class extends Component
{
    #[Computed]
    public function sections()
    {
        return app(InstructorService::class)->sectionsFor(auth()->user());
    }

    #[Computed]
    public function exams()
    {
        return Exam::query()
            ->with(['section', 'course'])
            ->withCount(['attempts', 'attempts as pending_grading_count' => fn ($query) => $query->where('status', 'pending_grading')])
            ->whereIn('section_id', $this->sections->pluck('id'))
            ->latest()
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => $this->exams->count(),
            'published' => $this->exams->where('status', 'published')->count(),
            'attempts' => (int) $this->exams->sum('attempts_count'),
            'pending' => (int) $this->exams->sum('pending_grading_count'),
        ];
    }
};
?>

@php($locale = app()->getLocale())

@include('partials.instructor.shell-start', [
    'instructorActive' => 'exams',
    'instructorTitle' => 'الاختبارات',
])

<div class="portal-dashboard portal-dashboard--instructor">
    @include('partials.instructor.page-hero', [
        'title' => 'الاختبارات وبنك الأسئلة',
        'desc' => 'أنشئ اختبارات مرنة، أدر الأسئلة والمحاولات، وتابع التصحيح والنتائج.',
        'icon' => 'fa-file-circle-check',
        'stats' => [
            ['value' => $this->stats['total'], 'label' => 'اختبارات'],
            ['value' => $this->stats['published'], 'label' => 'منشور'],
            ['value' => $this->stats['pending'], 'label' => 'للتصحيح'],
        ],
        'actions' => array_filter([
            auth()->user()?->canInstructor('instructor.exams.create') && $this->sections->isNotEmpty()
                ? ['href' => route('instructor.exams.create', ['locale' => $locale, 'section' => $this->sections->first()->id]), 'label' => 'اختبار جديد', 'icon' => 'fa-plus', 'class' => 'btn-primary']
                : null,
            ['href' => route('instructor.assignments', ['locale' => $locale]), 'label' => 'صندوق التصحيح', 'icon' => 'fa-clipboard-check', 'class' => 'btn-outline-primary'],
        ]),
    ])

    <div class="portal-kpi-strip portal-kpi-strip--4">
        <div class="portal-kpi-v2 portal-kpi-v2--sections">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-files"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['total'] }}</span><span class="portal-kpi-v2__label">إجمالي الاختبارات</span></span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--week">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-bullhorn"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['published'] }}</span><span class="portal-kpi-v2__label">منشور</span></span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--students">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-users"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['attempts'] }}</span><span class="portal-kpi-v2__label">محاولة طلابية</span></span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--grades">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-pen-to-square"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->stats['pending'] }}</span><span class="portal-kpi-v2__label">بانتظار التصحيح</span></span>
        </div>
    </div>

    @if ($this->sections->isEmpty())
        <div class="portal-alert portal-alert--warn portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-info"></i></span>
            <div class="portal-alert__content">لا يمكن إنشاء اختبار قبل إسناد شعبة دراسية إلى حسابك.</div>
        </div>
    @elseif ($this->exams->isEmpty())
        <section class="portal-panel">
            <div class="portal-panel__body">
                <div class="portal-empty">
                    <div class="portal-empty__icon"><i class="fa-solid fa-file-circle-question"></i></div>
                    <p>لم تنشئ اختبارات بعد</p>
                    <span class="portal-empty__hint">ابدأ باختيار الشعبة ثم أضف أقسام الاختبار وأسئلته.</span>
                    <div class="portal-inst-exam-section-actions">
                        @foreach ($this->sections as $section)
                            <a href="{{ route('instructor.exams.create', ['locale' => $locale, 'section' => $section->id]) }}" class="btn btn-outline-secondary btn-sm">
                                {{ $section->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="portal-panel">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-layer-group"></i> اختبارات الشعب</h2>
                <span class="portal-panel__meta">{{ $this->stats['total'] }}</span>
            </div>
            <div class="portal-panel__body">
                <div class="portal-inst-exam-grid">
                    @foreach ($this->exams as $exam)
                        <article class="portal-inst-exam-card" wire:key="exam-{{ $exam->id }}">
                            <div class="portal-inst-exam-card__media">
                                <span class="portal-inst-exam-card__type">{{ ExamOptions::examTypes()[$exam->type] ?? $exam->type }}</span>
                                <span class="portal-inst-exam-card__status portal-inst-exam-card__status--{{ $exam->status }}">{{ ExamOptions::statusLabel($exam->status) }}</span>
                            </div>
                            <div class="portal-inst-exam-card__body">
                                <h3>{{ $exam->title }}</h3>
                                <p>{{ $exam->section?->name }} · {{ $exam->course?->name_ar }}</p>
                                <div class="portal-inst-exam-card__stats">
                                    <span><strong>{{ $exam->total_points }}</strong> درجة</span>
                                    <span><strong>{{ $exam->duration_minutes ?: '∞' }}</strong> دقيقة</span>
                                    <span><strong>{{ $exam->attempts_count }}</strong> محاولة</span>
                                    @if ($exam->pending_grading_count)
                                        <span class="is-warn"><strong>{{ $exam->pending_grading_count }}</strong> للتصحيح</span>
                                    @endif
                                </div>
                                <div class="portal-inst-exam-card__actions">
                                    <a href="{{ route('instructor.exams.builder', ['locale' => $locale, 'section' => $exam->section_id, 'exam' => $exam->id]) }}" class="btn btn-primary btn-sm">بناء الأسئلة</a>
                                    <a href="{{ route('instructor.exams.edit', ['locale' => $locale, 'section' => $exam->section_id, 'exam' => $exam->id]) }}" class="btn btn-outline-secondary btn-sm">الإعدادات</a>
                                    <a href="{{ route('instructor.exams.grading', ['locale' => $locale, 'section' => $exam->section_id, 'exam' => $exam->id]) }}" class="btn btn-outline-secondary btn-sm">التصحيح</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>

@push('styles')
<style>
.portal-hero--page .portal-hero__banner--compact{min-height:auto}
.portal-hero--page .portal-hero__eyebrow{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.78);margin-bottom:.35rem}
.portal-hero--page .portal-hero__body--compact{margin-top:-1.1rem;padding-top:0;padding-bottom:1rem}
.portal-hero--page .portal-hero__actions--start{justify-content:flex-start}
.portal-panel__meta{font-size:.78rem;font-weight:800;color:#0f766e;background:#ecfdf5;padding:.3rem .7rem;border-radius:999px}
.portal-kpi-strip--4{grid-template-columns:repeat(4,minmax(0,1fr))}
.portal-dashboard--instructor .portal-kpi-v2--sections{border-right-color:#0d9488}
.portal-dashboard--instructor .portal-kpi-v2--week{border-right-color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--students{border-right-color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--grades{border-right-color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--sections .portal-kpi-v2__icon{background:#f0fdfa;color:#0d9488}
.portal-dashboard--instructor .portal-kpi-v2--week .portal-kpi-v2__icon{background:#ecfdf5;color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--students .portal-kpi-v2__icon{background:#eff6ff;color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--grades .portal-kpi-v2__icon{background:#fffbeb;color:#d97706}
.portal-inst-exam-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem}
.portal-inst-exam-card{display:flex;flex-direction:column;background:#fff;border:1px solid rgba(27,131,84,.14);border-radius:16px;overflow:hidden;box-shadow:0 4px 18px rgba(15,81,50,.06);transition:transform .18s ease,box-shadow .18s ease}
.portal-inst-exam-card:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(15,81,50,.12)}
.portal-inst-exam-card__media{display:flex;justify-content:space-between;align-items:center;gap:.5rem;padding:.85rem 1rem;background:linear-gradient(135deg,#0f5132 0%,#1b8354 70%,#b8943f 145%);color:#fff}
.portal-inst-exam-card__type{font-size:.7rem;font-weight:800;opacity:.92}
.portal-inst-exam-card__status{font-size:.68rem;font-weight:900;padding:.25rem .55rem;border-radius:999px;background:rgba(255,255,255,.18)}
.portal-inst-exam-card__status--published{background:#dcfce7;color:#166534}
.portal-inst-exam-card__status--closed{background:#fee2e2;color:#991b1b}
.portal-inst-exam-card__status--draft{background:#f1f5f9;color:#475569}
.portal-inst-exam-card__body{display:grid;gap:.55rem;padding:1rem}
.portal-inst-exam-card__body h3{margin:0;font-size:1rem;font-weight:900;color:#0f172a;line-height:1.4}
.portal-inst-exam-card__body>p{margin:0;color:#64748b;font-size:.78rem}
.portal-inst-exam-card__stats{display:flex;flex-wrap:wrap;gap:.4rem}
.portal-inst-exam-card__stats span{padding:.28rem .5rem;border-radius:999px;background:#f8fafc;border:1px solid #e8eef3;color:#64748b;font-size:.7rem;font-weight:700}
.portal-inst-exam-card__stats strong{color:#0f172a}
.portal-inst-exam-card__stats .is-warn{background:#fff7ed;border-color:#fed7aa;color:#9a3412}
.portal-inst-exam-card__actions,.portal-inst-exam-section-actions{display:flex;flex-wrap:wrap;gap:.4rem}
.portal-inst-exam-section-actions{justify-content:center;margin-top:.85rem}
@media(max-width:991.98px){.portal-kpi-strip--4{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endpush

@include('partials.instructor.shell-end')
