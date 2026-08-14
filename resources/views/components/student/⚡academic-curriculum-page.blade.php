<?php

use App\Models\AcademicCourse;
use App\Models\AcademicScheduleDocument;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use App\Support\AcademicProgramOptions;
use App\Support\AcademicStudentOptions;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('منهج البرنامج | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function student()
    {
        return auth()->user()?->academicStudent()
            ->with([
                'batch.program',
                'section.course.level',
            ])
            ->first();
    }

    #[Computed]
    public function scheduleDocuments(): Collection
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        return AcademicScheduleDocument::query()
            ->with('batch:id,name')
            ->forStudent($student)
            ->ordered()
            ->get();
    }

    #[Computed]
    public function featuredSchedule(): ?AcademicScheduleDocument
    {
        return $this->scheduleDocuments->firstWhere('is_featured', true)
            ?? $this->scheduleDocuments->first();
    }

    #[Computed]
    public function diplomaSessions(): Collection
    {
        $service = app(AcademicSessionService::class);
        $sessions = $service->forStudent($this->student);

        return $sessions->map(function (AttendanceSession $session) use ($service) {
            $timing = $service->resolveTiming($session);
            $session->computed_state = $timing['state'];
            $session->computed_starts_at = $timing['starts_at'];

            return $session;
        });
    }

    #[Computed]
    public function courses(): Collection
    {
        $programId = $this->student?->batch?->program_id;

        if (! $programId) {
            return collect();
        }

        return AcademicCourse::query()
            ->with('level')
            ->where('program_id', $programId)
            ->where('status', 'active')
            ->orderByRaw('CASE WHEN level_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy(
                \App\Models\AcademicLevel::query()
                    ->select('sort_order')
                    ->whereColumn('academic_levels.id', 'academic_courses.level_id')
                    ->limit(1)
            )
            ->orderBy('code')
            ->get();
    }

    #[Computed]
    public function coursesByLevel(): Collection
    {
        return $this->courses->groupBy(
            fn (AcademicCourse $course) => $course->level?->name_ar ?: 'مقررات عامة'
        );
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'courses' => $this->courses->count(),
            'hours' => (int) $this->courses->sum('credit_hours'),
            'levels' => $this->courses
                ->pluck('level_id')
                ->filter()
                ->unique()
                ->count(),
            'sessions' => $this->diplomaSessions->count(),
        ];
    }
};
?>

@include('partials.portal.shell-start', [
    'portalActive' => 'academic-curriculum',
    'portalTitle' => 'منهج البرنامج',
])

@php
    $student = $this->student;
    $program = $student?->batch?->program;
    $currentCourseId = $student?->section?->course_id;
@endphp

<div class="portal-dashboard curriculum-page">
    <section class="curriculum-hero">
        <div class="curriculum-hero__copy">
            <span class="curriculum-hero__eyebrow">الخطة الدراسية</span>
            <h1>{{ $program?->name_ar ?? 'منهج البرنامج' }}</h1>
            <p>
                استعرض جميع مستويات ومقررات البرنامج، وعدد الساعات، والمقرر الحالي المرتبط بشعبتك.
            </p>
        </div>

        @if ($program)
            <div class="curriculum-hero__meta">
                <span>{{ AcademicProgramOptions::typeLabel($program->type) }}</span>
                @if ($program->code)
                    <code dir="ltr">{{ $program->code }}</code>
                @endif
                <span>{{ AcademicStudentOptions::academicStatusLabel($student?->academic_status) }}</span>
            </div>
        @endif
    </section>

    @if (! $student || ! $program)
        <section class="portal-panel curriculum-empty">
            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
            <h2>لا يوجد برنامج أكاديمي مرتبط بحسابك</h2>
            <p>سيظهر المنهج هنا بعد اعتماد تسجيلك وربطك بالدفعة الأكاديمية.</p>
        </section>
    @else
        @if ($this->scheduleDocuments->isNotEmpty())
            @php($featured = $this->featuredSchedule)
            <section class="curriculum-schedule" aria-label="الجدول الدراسي">
                <header class="curriculum-schedule__head">
                    <div>
                        <span class="curriculum-schedule__eyebrow">الجدول الدراسي</span>
                        <h2>{{ $featured?->title ?? 'جدول البرنامج' }}</h2>
                        @if ($featured?->description)
                            <p>{{ $featured->description }}</p>
                        @else
                            <p>اطّلع على الجدول المعتمد لدبلومك وحمّل الملف للاحتفاظ به.</p>
                        @endif
                    </div>
                    @if ($featured)
                        <a href="{{ $featured->url() }}" target="_blank" rel="noopener" class="curriculum-schedule__cta">
                            <i class="fa-solid fa-file-arrow-down"></i>
                            {{ $featured->isPdf() ? 'فتح الجدول PDF' : ($featured->isImage() ? 'عرض الصورة' : 'تحميل الملف') }}
                        </a>
                    @endif
                </header>

                @if ($featured?->isImage())
                    <a href="{{ $featured->url() }}" target="_blank" rel="noopener" class="curriculum-schedule__preview">
                        <img src="{{ $featured->url() }}" alt="{{ $featured->title }}" loading="lazy">
                    </a>
                @endif

                @if ($this->scheduleDocuments->count() > 1)
                    <div class="curriculum-schedule__list">
                        @foreach ($this->scheduleDocuments as $document)
                            <a href="{{ $document->url() }}" target="_blank" rel="noopener" class="curriculum-schedule__item" wire:key="schedule-doc-{{ $document->id }}">
                                <span class="curriculum-schedule__icon">
                                    <i class="fa-solid {{ $document->isPdf() ? 'fa-file-pdf' : ($document->isImage() ? 'fa-image' : 'fa-file') }}"></i>
                                </span>
                                <span class="curriculum-schedule__meta">
                                    <strong>{{ $document->title }}</strong>
                                    <small>
                                        {{ $document->humanSize() }}
                                        @if ($document->batch_id)
                                            · {{ $document->batch?->name ?? 'دفعتك' }}
                                        @else
                                            · كل الدفعات
                                        @endif
                                        @if ($document->is_featured)
                                            · رئيسي
                                        @endif
                                    </small>
                                </span>
                                <span class="curriculum-schedule__open">عرض <i class="fa-solid fa-chevron-left"></i></span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($this->diplomaSessions->isNotEmpty())
            @php($locale = app()->getLocale())
            <section class="curriculum-sessions" aria-label="حصص الدبلوم">
                <header class="curriculum-sessions__head">
                    <div>
                        <span class="curriculum-sessions__eyebrow">محتوى الدبلوم</span>
                        <h2>الحصص الدراسية</h2>
                        <p>الحصص المنشورة لمقررك الحالي — افتح الحصة للاطلاع على المحتوى والانضمام.</p>
                    </div>
                    <a href="{{ route('sessions', ['locale' => $locale]) }}" class="curriculum-sessions__cta">كل حصصي</a>
                </header>
                <div class="curriculum-sessions__list">
                    @foreach ($this->diplomaSessions->take(8) as $session)
                        <a
                            href="{{ route('sessions.show', ['locale' => $locale, 'session' => $session->id]) }}"
                            @class(['curriculum-session', 'is-live' => $session->computed_state === 'live', 'is-completed' => $session->computed_state === 'completed'])
                            wire:key="curriculum-session-{{ $session->id }}"
                        >
                            <span class="curriculum-session__number">{{ $session->session_number ?: '•' }}</span>
                            <span class="curriculum-session__body">
                                <strong>{{ $session->displayTitle() }}</strong>
                                <small>
                                    {{ $session->session_date->translatedFormat('d M Y') }}
                                    @if ($session->computed_starts_at) · {{ $session->computed_starts_at->format('H:i') }} @endif
                                    @if ($session->section?->course?->name_ar) · {{ $session->section->course->name_ar }} @endif
                                </small>
                                @if ($session->description)
                                    <em>{{ \Illuminate\Support\Str::limit(strip_tags($session->description), 110) }}</em>
                                @endif
                            </span>
                            <span @class(['curriculum-session__state', 'is-live' => $session->computed_state === 'live', 'is-completed' => $session->computed_state === 'completed'])>
                                {{ match($session->computed_state) { 'live' => 'جارية الآن', 'upcoming' => 'قادمة', 'completed' => 'منتهية', default => 'مجدولة' } }}
                            </span>
                        </a>
                    @endforeach
                </div>
                @if ($this->diplomaSessions->count() > 8)
                    <div class="curriculum-sessions__more">
                        <a href="{{ route('sessions', ['locale' => $locale]) }}">عرض باقي الحصص ({{ $this->diplomaSessions->count() }})</a>
                    </div>
                @endif
            </section>
        @endif

        <section class="curriculum-kpis" aria-label="ملخص المنهج">
            <article class="curriculum-kpi">
                <span class="curriculum-kpi__icon"><i class="fa-solid fa-book-open"></i></span>
                <strong>{{ $this->summary['courses'] }}</strong>
                <small>مقرر دراسي</small>
            </article>
            <article class="curriculum-kpi">
                <span class="curriculum-kpi__icon"><i class="fa-regular fa-clock"></i></span>
                <strong>{{ $this->summary['hours'] }}</strong>
                <small>ساعة معتمدة</small>
            </article>
            <article class="curriculum-kpi">
                <span class="curriculum-kpi__icon"><i class="fa-solid fa-chalkboard-user"></i></span>
                <strong>{{ $this->summary['sessions'] }}</strong>
                <small>حصة منشورة</small>
            </article>
            <article class="curriculum-kpi curriculum-kpi--current">
                <span class="curriculum-kpi__icon"><i class="fa-solid fa-location-dot"></i></span>
                <strong>{{ $student->section?->course?->code ?? '—' }}</strong>
                <small>المقرر الحالي</small>
            </article>
        </section>

        @if ($this->courses->isEmpty())
            <section class="portal-panel curriculum-empty">
                <i class="fa-solid fa-book" aria-hidden="true"></i>
                <h2>لم تُضف مقررات إلى هذا البرنامج بعد</h2>
                <p>ستظهر المقررات تلقائياً عند اعتماد الخطة الدراسية من الإدارة الأكاديمية.</p>
            </section>
        @else
            <div class="curriculum-levels">
                @foreach ($this->coursesByLevel as $levelName => $levelCourses)
                    <section class="curriculum-level" wire:key="curriculum-level-{{ md5($levelName) }}">
                        <header class="curriculum-level__head">
                            <div>
                                <span class="curriculum-level__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h2>{{ $levelName }}</h2>
                            </div>
                            <p>{{ $levelCourses->count() }} مقرر · {{ (int) $levelCourses->sum('credit_hours') }} ساعة</p>
                        </header>

                        <div class="curriculum-courses">
                            @foreach ($levelCourses as $course)
                                @php($isCurrent = (int) $currentCourseId === (int) $course->id)
                                <a
                                    href="{{ route('academic-curriculum.course', ['locale' => app()->getLocale(), 'course' => $course->id]) }}"
                                    @class(['curriculum-course', 'is-current' => $isCurrent])
                                    wire:key="curriculum-course-{{ $course->id }}"
                                >
                                    <div class="curriculum-course__code">
                                        <code dir="ltr">{{ $course->code }}</code>
                                        @if ($isCurrent)
                                            <span><i class="fa-solid fa-circle-play"></i> تدرس الآن</span>
                                        @endif
                                    </div>

                                    <div class="curriculum-course__body">
                                        <h3>{{ $course->name_ar }}</h3>
                                        @if ($course->summary)
                                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($course->summary), 150) }}</p>
                                        @endif
                                        <div class="curriculum-course__details">
                                            @if ($course->symbol_ar)
                                                <span><i class="fa-solid fa-hashtag"></i> {{ $course->symbol_ar }}</span>
                                            @endif
                                            <span><i class="fa-regular fa-clock"></i> {{ (int) $course->credit_hours }} ساعة</span>
                                            @if ($isCurrent && $this->diplomaSessions->isNotEmpty())
                                                <span><i class="fa-solid fa-chalkboard-user"></i> {{ $this->diplomaSessions->count() }} حصة</span>
                                            @endif
                                        </div>
                                    </div>

                                    <span class="curriculum-course__side">
                                        <span @class(['curriculum-course__state', 'is-current' => $isCurrent])>
                                            {{ $isCurrent ? 'المقرر الحالي' : 'ضمن الخطة' }}
                                        </span>
                                        <span class="curriculum-course__open">عرض التفاصيل <i class="fa-solid fa-chevron-left"></i></span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    @endif
</div>

@push('styles')
<style>
    .curriculum-page{display:flex;flex-direction:column;gap:1rem}
    .curriculum-schedule{display:flex;flex-direction:column;gap:.9rem;padding:1.15rem 1.25rem;border:1px solid #dbe7de;border-radius:16px;background:linear-gradient(180deg,#f7fbf8 0%,#fff 55%);box-shadow:0 10px 24px rgba(15,81,50,.06)}
    .curriculum-schedule__head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem}
    .curriculum-schedule__eyebrow{display:block;margin-bottom:.25rem;color:#166534;font-size:.72rem;font-weight:900}
    .curriculum-schedule__head h2{margin:0 0 .35rem;font-size:1.05rem;font-weight:900;color:#0f172a}
    .curriculum-schedule__head p{margin:0;max-width:42rem;color:#64748b;font-size:.82rem;line-height:1.7}
    .curriculum-schedule__cta{display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1rem;border-radius:12px;background:#166534;color:#fff;font-size:.8rem;font-weight:800;white-space:nowrap;text-decoration:none}
    .curriculum-schedule__cta:hover{background:#14532d;color:#fff}
    .curriculum-schedule__preview{display:block;overflow:hidden;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
    .curriculum-schedule__preview img{display:block;width:100%;max-height:320px;object-fit:contain;background:#f8fafc}
    .curriculum-schedule__list{display:flex;flex-direction:column;gap:.45rem}
    .curriculum-schedule__item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:.8rem;padding:.75rem .85rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:inherit;text-decoration:none;transition:background .15s,border-color .15s}
    .curriculum-schedule__item:hover{background:#f8fafc;border-color:#bbf7d0}
    .curriculum-schedule__icon{width:2.3rem;height:2.3rem;display:grid;place-items:center;border-radius:10px;background:#ecfdf5;color:#166534}
    .curriculum-schedule__meta strong{display:block;font-size:.86rem;font-weight:900;color:#0f172a}
    .curriculum-schedule__meta small{display:block;margin-top:.15rem;color:#64748b;font-size:.7rem;font-weight:700}
    .curriculum-schedule__open{display:inline-flex;align-items:center;gap:.25rem;color:#94a3b8;font-size:.68rem;font-weight:900}
    .curriculum-sessions{display:flex;flex-direction:column;gap:.85rem;padding:1.15rem 1.25rem;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.04)}
    .curriculum-sessions__head{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem}
    .curriculum-sessions__eyebrow{display:block;margin-bottom:.25rem;color:#166534;font-size:.72rem;font-weight:900}
    .curriculum-sessions__head h2{margin:0 0 .35rem;font-size:1.05rem;font-weight:900;color:#0f172a}
    .curriculum-sessions__head p{margin:0;max-width:40rem;color:#64748b;font-size:.82rem;line-height:1.7}
    .curriculum-sessions__cta{display:inline-flex;align-items:center;padding:.55rem .9rem;border-radius:11px;background:#166534;color:#fff;font-size:.78rem;font-weight:800;text-decoration:none;white-space:nowrap}
    .curriculum-sessions__list{display:flex;flex-direction:column}
    .curriculum-session{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:.85rem;padding:.8rem .35rem;border-bottom:1px solid #f1f5f9;color:inherit;text-decoration:none;transition:background .15s}
    .curriculum-session:last-child{border-bottom:0}
    .curriculum-session:hover{background:#f8fafc;border-radius:10px}
    .curriculum-session__number{display:grid;place-items:center;width:2.1rem;height:2.1rem;border-radius:10px;background:#f1f5f9;color:#334155;font-size:.72rem;font-weight:900}
    .curriculum-session.is-live .curriculum-session__number{background:#fee2e2;color:#b91c1c}
    .curriculum-session.is-completed .curriculum-session__number{background:#dcfce7;color:#166534}
    .curriculum-session__body{display:grid;gap:.12rem;min-width:0}
    .curriculum-session__body strong{color:#0f172a;font-size:.86rem;font-weight:900}
    .curriculum-session__body small{color:#64748b;font-size:.7rem;font-weight:700}
    .curriculum-session__body em{display:block;margin-top:.15rem;color:#64748b;font-size:.72rem;font-style:normal;line-height:1.55}
    .curriculum-session__state{padding:.22rem .5rem;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.62rem;font-weight:900;white-space:nowrap}
    .curriculum-session__state.is-live{background:#fee2e2;color:#b91c1c}
    .curriculum-session__state.is-completed{background:#dcfce7;color:#166534}
    .curriculum-sessions__more{padding-top:.25rem}
    .curriculum-sessions__more a{color:#166534;font-size:.78rem;font-weight:800;text-decoration:none}
    .curriculum-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:1.25rem;padding:1.4rem 1.5rem;border-radius:18px;background:linear-gradient(135deg,#0f5132 0%,#1b8354 62%,#b8943f 160%);color:#fff;box-shadow:0 14px 32px rgba(15,81,50,.18)}
    .curriculum-hero__copy{max-width:48rem}
    .curriculum-hero__eyebrow{display:block;margin-bottom:.4rem;font-size:.76rem;font-weight:800;opacity:.8}
    .curriculum-hero h1{margin:0 0 .5rem;font-size:clamp(1.35rem,2.5vw,2rem);font-weight:900;color:#fff}
    .curriculum-hero p{margin:0;line-height:1.8;font-size:.9rem;color:rgba(255,255,255,.86)}
    .curriculum-hero__meta{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.45rem}
    .curriculum-hero__meta span,.curriculum-hero__meta code{padding:.35rem .65rem;border:1px solid rgba(255,255,255,.22);border-radius:999px;background:rgba(255,255,255,.1);color:#fff;font-size:.72rem;font-weight:800}
    .curriculum-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}
    .curriculum-kpi{display:grid;grid-template-columns:auto 1fr;grid-template-rows:auto auto;column-gap:.75rem;padding:1rem;border:1px solid #e2e8f0;border-radius:15px;background:#fff}
    .curriculum-kpi__icon{grid-row:1/3;width:2.55rem;height:2.55rem;display:grid;place-items:center;border-radius:12px;background:#ecfdf5;color:#166534}
    .curriculum-kpi strong{font-size:1.2rem;line-height:1.1;color:#0f172a}
    .curriculum-kpi small{margin-top:.15rem;color:#64748b;font-size:.72rem;font-weight:700}
    .curriculum-kpi--current .curriculum-kpi__icon{background:#fffbeb;color:#a16207}
    .curriculum-levels{display:flex;flex-direction:column;gap:1rem}
    .curriculum-level{overflow:hidden;border:1px solid #e2e8f0;border-radius:16px;background:#fff}
    .curriculum-level__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.95rem 1rem;background:#f8fafc;border-bottom:1px solid #e2e8f0}
    .curriculum-level__head>div{display:flex;align-items:center;gap:.65rem}
    .curriculum-level__index{display:grid;place-items:center;width:2rem;height:2rem;border-radius:10px;background:#166534;color:#fff;font-size:.72rem;font-weight:900}
    .curriculum-level__head h2{margin:0;font-size:1rem;font-weight:900;color:#0f172a}
    .curriculum-level__head p{margin:0;color:#64748b;font-size:.76rem;font-weight:700}
    .curriculum-courses{display:flex;flex-direction:column}
    .curriculum-course{display:grid;grid-template-columns:8rem minmax(0,1fr) auto;align-items:center;gap:1rem;padding:1rem;border-bottom:1px solid #f1f5f9;color:inherit;cursor:pointer;transition:background .15s,box-shadow .15s}
    .curriculum-course:last-child{border-bottom:0}
    .curriculum-course:hover{background:#f8fafc}
    .curriculum-course:hover .curriculum-course__open{color:#166534}
    .curriculum-course.is-current{background:linear-gradient(90deg,#f0fdf4 0%,#fff 72%);box-shadow:inset -4px 0 #16a34a}
    .curriculum-course.is-current:hover{background:linear-gradient(90deg,#dcfce7 0%,#f8fafc 72%)}
    .curriculum-course__code{display:flex;flex-direction:column;align-items:flex-start;gap:.4rem}
    .curriculum-course__code code{padding:.28rem .5rem;border-radius:8px;background:#f1f5f9;color:#334155;font-weight:800}
    .curriculum-course__code span{font-size:.67rem;font-weight:900;color:#15803d}
    .curriculum-course__body h3{margin:0 0 .3rem;font-size:.92rem;font-weight:900;color:#0f172a}
    .curriculum-course__body p{margin:0 0 .45rem;color:#64748b;font-size:.77rem;line-height:1.65}
    .curriculum-course__details{display:flex;flex-wrap:wrap;gap:.75rem;color:#64748b;font-size:.7rem;font-weight:700}
    .curriculum-course__side{display:flex;flex-direction:column;align-items:flex-end;gap:.4rem}
    .curriculum-course__state{padding:.28rem .55rem;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.68rem;font-weight:900;white-space:nowrap}
    .curriculum-course__state.is-current{background:#dcfce7;color:#166534}
    .curriculum-course__open{display:inline-flex;align-items:center;gap:.3rem;color:#94a3b8;font-size:.66rem;font-weight:900;white-space:nowrap;transition:color .15s}
    .curriculum-course__open i{font-size:.58rem}
    .curriculum-empty{text-align:center;padding:2.5rem 1rem}
    .curriculum-empty>i{font-size:2rem;color:#94a3b8;margin-bottom:.75rem}
    .curriculum-empty h2{margin:0 0 .45rem;font-size:1.05rem;color:#0f172a}
    .curriculum-empty p{margin:0;color:#64748b;font-size:.84rem}
    @media(max-width:900px){.curriculum-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.curriculum-course{grid-template-columns:6rem minmax(0,1fr)}.curriculum-course__side{grid-column:2;flex-direction:row;align-items:center;justify-content:flex-start}}
    @media(max-width:640px){.curriculum-hero{align-items:flex-start;flex-direction:column}.curriculum-hero__meta{justify-content:flex-start}.curriculum-schedule__head,.curriculum-sessions__head{align-items:flex-start;flex-direction:column}.curriculum-schedule__cta,.curriculum-sessions__cta{width:100%;justify-content:center}.curriculum-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.curriculum-kpi{grid-template-columns:auto 1fr;padding:.8rem}.curriculum-level__head{align-items:flex-start;flex-direction:column}.curriculum-course{grid-template-columns:1fr}.curriculum-course__code{flex-direction:row;align-items:center}.curriculum-course__side{grid-column:auto;justify-content:space-between;width:100%}}
    @media(max-width:400px){.curriculum-kpis{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.portal.shell-end')
