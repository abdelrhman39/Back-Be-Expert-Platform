<?php

use App\Models\AcademicCourse;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تفاصيل المقرر | منصة مركز التعلم المستمر')]
class extends Component
{
    public AcademicCourse $course;

    public function mount(AcademicCourse $course): void
    {
        $student = auth()->user()?->academicStudent()->with('batch')->first();

        abort_unless(
            $student
            && $student->batch?->program_id
            && (int) $course->program_id === (int) $student->batch->program_id,
            404
        );

        $this->course = $course->load(['level', 'program']);
    }

    #[Computed]
    public function student()
    {
        return auth()->user()?->academicStudent()->with('section.course')->first();
    }

    #[Computed]
    public function isCurrentCourse(): bool
    {
        return (int) $this->student?->section?->course_id === (int) $this->course->id;
    }

    #[Computed]
    public function sessions()
    {
        if (! $this->isCurrentCourse || ! $this->student?->section_id) {
            return collect();
        }

        $service = app(AcademicSessionService::class);

        return AttendanceSession::query()
            ->with(['publishedMaterials', 'section.schedule'])
            ->where('section_id', $this->student->section_id)
            ->whereIn('status', ['scheduled', 'completed'])
            ->whereNotNull('published_at')
            ->orderByRaw('CASE WHEN session_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('session_number')
            ->orderBy('session_date')
            ->orderBy('time_start')
            ->get()
            ->map(function (AttendanceSession $session) use ($service) {
                $timing = $service->resolveTiming($session);
                $session->computed_state = $timing['state'];
                $session->computed_starts_at = $timing['starts_at'];

                return $session;
            });
    }
};
?>

@php
    $locale = app()->getLocale();
    $isCurrent = $this->isCurrentCourse;
    $completedCount = $this->sessions->where('computed_state', 'completed')->count();
    $materialsCount = $this->sessions->sum(fn ($session) => $session->publishedMaterials->count());
@endphp

@include('partials.portal.shell-start', [
    'portalActive' => 'academic-curriculum',
    'portalTitle' => 'تفاصيل المقرر',
])

<div class="portal-dashboard course-detail-page">
    <a href="{{ route('academic-curriculum', ['locale' => $locale]) }}" class="portal-panel__link">← العودة إلى منهج البرنامج</a>

    <section class="course-detail-hero">
        <div class="course-detail-hero__copy">
            <div class="course-detail-hero__badges">
                <code dir="ltr">{{ $course->code }}</code>
                @if ($course->level)<span>{{ $course->level->name_ar }}</span>@endif
                <span @class(['course-detail-hero__state', 'is-current' => $isCurrent])>
                    {{ $isCurrent ? 'المقرر الحالي — تدرسه الآن' : 'ضمن الخطة الدراسية' }}
                </span>
            </div>
            <h1>{{ $course->name_ar }}</h1>
            @if ($course->summary)
                <p>{{ strip_tags($course->summary) }}</p>
            @endif
        </div>
        <div class="course-detail-hero__facts">
            <div><strong>{{ (int) $course->credit_hours }}</strong><span>ساعة معتمدة</span></div>
            @if ($course->symbol_ar)<div><strong>{{ $course->symbol_ar }}</strong><span>رمز المقرر</span></div>@endif
            <div><strong>{{ $course->program?->name_ar ? 'دبلوم' : '—' }}</strong><span>{{ $course->program?->name_ar }}</span></div>
        </div>
    </section>

    @if ($isCurrent)
        <section class="course-detail-kpis">
            <article><span class="course-detail-kpi__icon"><i class="fa-solid fa-chalkboard-user"></i></span><strong>{{ $this->sessions->count() }}</strong><small>حصة منشورة</small></article>
            <article><span class="course-detail-kpi__icon"><i class="fa-solid fa-circle-check"></i></span><strong>{{ $completedCount }}</strong><small>حصة منتهية</small></article>
            <article><span class="course-detail-kpi__icon"><i class="fa-solid fa-paperclip"></i></span><strong>{{ $materialsCount }}</strong><small>مرفق تعليمي</small></article>
        </section>

        <section class="portal-panel">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title">حصص هذا المقرر</h2>
                <a href="{{ route('sessions', ['locale' => $locale]) }}" class="portal-panel__link">كل حصصي</a>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                @if ($this->sessions->isEmpty())
                    <div class="portal-empty">
                        <div class="portal-empty__icon"><i class="fa-solid fa-calendar-xmark"></i></div>
                        <p>لا توجد حصص منشورة لهذا المقرر بعد.</p>
                    </div>
                @else
                    <div class="course-detail-sessions">
                        @foreach ($this->sessions as $session)
                            <a
                                href="{{ route('sessions.show', ['locale' => $locale, 'session' => $session->id]) }}"
                                @class(['course-detail-session', 'is-live' => $session->computed_state === 'live', 'is-completed' => $session->computed_state === 'completed'])
                                wire:key="course-session-{{ $session->id }}"
                            >
                                <span class="course-detail-session__number">{{ $session->session_number ?: '•' }}</span>
                                <span class="course-detail-session__body">
                                    <strong>{{ $session->displayTitle() }}</strong>
                                    <small>
                                        {{ $session->session_date->translatedFormat('d M Y') }}
                                        @if ($session->computed_starts_at) · {{ $session->computed_starts_at->format('H:i') }} @endif
                                        @if ($session->publishedMaterials->isNotEmpty()) · {{ $session->publishedMaterials->count() }} مرفق @endif
                                    </small>
                                    @if ($session->description)
                                        <em class="course-detail-session__desc">{{ \Illuminate\Support\Str::limit(strip_tags($session->description), 120) }}</em>
                                    @endif
                                </span>
                                <span @class(['course-detail-session__state', 'is-live' => $session->computed_state === 'live', 'is-completed' => $session->computed_state === 'completed'])>
                                    {{ match($session->computed_state) { 'live' => 'جارية الآن', 'upcoming' => 'قادمة', 'completed' => 'منتهية', default => 'مجدولة' } }}
                                </span>
                                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="portal-panel course-detail-locked">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            <h2>هذا المقرر ضمن خطتك الدراسية</h2>
            <p>ستظهر حصصه ومحتواه هنا عند انتقالك إلى الشعبة الخاصة به وفق تقدمك في البرنامج.</p>
        </section>
    @endif
</div>

<style>
    .course-detail-page{display:flex;flex-direction:column;gap:1rem}
    .course-detail-hero{display:flex;align-items:stretch;justify-content:space-between;gap:1.25rem;padding:1.4rem 1.5rem;border-radius:18px;background:linear-gradient(135deg,#0f5132 0%,#1b8354 62%,#b8943f 170%);color:#fff;box-shadow:0 14px 32px rgba(15,81,50,.18)}
    .course-detail-hero__copy{max-width:44rem}
    .course-detail-hero__badges{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.65rem}
    .course-detail-hero__badges code,.course-detail-hero__badges span{padding:.3rem .6rem;border:1px solid rgba(255,255,255,.24);border-radius:999px;background:rgba(255,255,255,.1);color:#fff;font-size:.68rem;font-weight:800}
    .course-detail-hero__state.is-current{border-color:rgba(134,239,172,.6);background:rgba(22,163,74,.35)}
    .course-detail-hero h1{margin:0 0 .5rem;color:#fff;font-size:clamp(1.3rem,2.4vw,1.9rem);font-weight:900}
    .course-detail-hero p{margin:0;font-size:.85rem;line-height:1.9;color:rgba(255,255,255,.86)}
    .course-detail-hero__facts{display:flex;flex-direction:column;justify-content:center;gap:.5rem;min-width:11rem}
    .course-detail-hero__facts>div{display:flex;align-items:baseline;gap:.45rem;padding:.5rem .7rem;border:1px solid rgba(255,255,255,.18);border-radius:11px;background:rgba(255,255,255,.08)}
    .course-detail-hero__facts strong{font-size:.95rem}
    .course-detail-hero__facts span{font-size:.66rem;opacity:.85}
    .course-detail-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
    .course-detail-kpis article{display:grid;grid-template-columns:auto 1fr;grid-template-rows:auto auto;column-gap:.75rem;padding:1rem;border:1px solid #e2e8f0;border-radius:15px;background:#fff}
    .course-detail-kpi__icon{grid-row:1/3;display:grid;place-items:center;width:2.55rem;height:2.55rem;border-radius:12px;background:#ecfdf5;color:#166534}
    .course-detail-kpis strong{font-size:1.15rem;line-height:1.15;color:#0f172a}
    .course-detail-kpis small{margin-top:.15rem;color:#64748b;font-size:.7rem;font-weight:700}
    .course-detail-sessions{display:flex;flex-direction:column}
    .course-detail-session{display:grid;grid-template-columns:auto minmax(0,1fr) auto auto;align-items:center;gap:.8rem;padding:.8rem .5rem;border-bottom:1px solid #f1f5f9;color:inherit;transition:background .15s}
    .course-detail-session:last-child{border-bottom:0}
    .course-detail-session:hover{background:#f8fafc;border-radius:10px}
    .course-detail-session__number{display:grid;place-items:center;width:2rem;height:2rem;border-radius:10px;background:#f1f5f9;color:#334155;font-size:.72rem;font-weight:900}
    .course-detail-session.is-live .course-detail-session__number{background:#fee2e2;color:#b91c1c}
    .course-detail-session.is-completed .course-detail-session__number{background:#dcfce7;color:#166534}
    .course-detail-session__body{display:grid;gap:.15rem}
    .course-detail-session__body strong{color:#0f172a;font-size:.82rem}
    .course-detail-session__body small{color:#64748b;font-size:.68rem}
    .course-detail-session__desc{display:block;margin-top:.2rem;color:#64748b;font-size:.7rem;font-style:normal;line-height:1.55}
    .course-detail-session__state{padding:.22rem .5rem;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.62rem;font-weight:900;white-space:nowrap}
    .course-detail-session__state.is-live{background:#fee2e2;color:#b91c1c}
    .course-detail-session__state.is-completed{background:#dcfce7;color:#166534}
    .course-detail-session>i{color:#cbd5e1;font-size:.7rem}
    .course-detail-locked{text-align:center;padding:2.4rem 1rem}
    .course-detail-locked>i{font-size:2rem;color:#b8943f;margin-bottom:.75rem}
    .course-detail-locked h2{margin:0 0 .45rem;font-size:1.05rem;color:#0f172a}
    .course-detail-locked p{margin:0 auto;max-width:32rem;color:#64748b;font-size:.82rem;line-height:1.8}
    @media(max-width:800px){.course-detail-hero{flex-direction:column}.course-detail-hero__facts{flex-direction:row;flex-wrap:wrap;min-width:0}.course-detail-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:560px){.course-detail-kpis{grid-template-columns:1fr}.course-detail-session{grid-template-columns:auto minmax(0,1fr) auto}.course-detail-session>i{display:none}}
</style>

@include('partials.portal.shell-end')
