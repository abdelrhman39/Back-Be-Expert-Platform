<?php

use App\Models\AcademicSection;
use App\Services\AcademicSessionService;
use App\Services\InstructorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('شعبة | لوحة المدرب')]
class extends Component
{
    public AcademicSection $section;

    public string $courseSummary = '';

    public string $courseTargetGroup = '';

    public string $flashMessage = '';

    public function mount(AcademicSection $section, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $section);
        $this->section = $section->load(['course', 'program', 'batch', 'schedule.staff'])->loadCount('students');
        $this->courseSummary = $this->section->course?->summary ?? '';
        $this->courseTargetGroup = $this->section->course?->target_group ?? '';
    }

    #[Computed]
    public function sessions()
    {
        return app(InstructorService::class)->sessionsForSection($this->section);
    }

    public function sessionState($session): string
    {
        return app(AcademicSessionService::class)->resolveTiming($session)['state'];
    }

    public function saveCourseDetails(InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $this->section);
        $instructors->authorizePermission(auth()->user(), 'instructor.content.update');

        abort_unless($this->section->course, 404);

        $validated = $this->validate([
            'courseSummary' => ['nullable', 'string', 'max:5000'],
            'courseTargetGroup' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'courseSummary' => 'وصف المقرر',
            'courseTargetGroup' => 'الفئة المستهدفة',
        ]);

        $this->section->course->update([
            'summary' => $validated['courseSummary'] ?: null,
            'target_group' => $validated['courseTargetGroup'] ?: null,
        ]);

        $this->section->load('course');
        $this->flashMessage = 'تم تحديث تفاصيل المقرر بنجاح.';
    }
};
?>

@php
    $locale = app()->getLocale();
    $breadcrumb = [
        ['href' => route('instructor.sections', ['locale' => $locale]), 'label' => 'شعبي'],
        ['href' => route('instructor.sections.show', ['locale' => $locale, 'section' => $section->id]), 'label' => $section->name],
    ];
@endphp

@include('partials.instructor.shell-start', [
    'instructorActive' => 'sections',
    'instructorTitle' => $section->name,
    'instructorBreadcrumb' => $breadcrumb,
])

<div class="portal-dashboard portal-instructor-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ $section->name }}</h1>
            <p class="portal-orders-intro__desc">{{ $section->course?->name_ar ?? $section->subtitle }} — {{ $section->displaySemester() }}</p>
        </div>
        <div class="portal-inst-hero-actions">
            @canInstructor('instructor.sections.view_all_students')
                <a href="{{ route('instructor.sections.roster', ['locale' => $locale, 'section' => $section->id]) }}" class="portal-btn portal-btn--secondary portal-btn--sm">
                    <i class="fa-solid fa-users"></i> قائمة الطلاب
                </a>
            @endcanInstructor
            @canInstructor('instructor.exams.create')
                <a href="{{ route('instructor.exams.create', ['locale' => $locale, 'section' => $section->id]) }}" class="portal-btn portal-btn--secondary portal-btn--sm">
                    <i class="fa-solid fa-file-circle-plus"></i> اختبار جديد
                </a>
            @endcanInstructor
            @canInstructor('instructor.teams.join_link.view')
                @if ($section->schedule?->meeting_url)
                    <a href="{{ $section->schedule->meeting_url }}" target="_blank" rel="noopener" class="portal-btn portal-btn--primary portal-btn--sm">
                        <i class="fa-solid fa-video"></i> رابط Teams
                    </a>
                @endif
            @endcanInstructor
        </div>
    </div>

    <div class="portal-inst-quick-nav">
        @canInstructor('instructor.sections.view_all_students')
            <a href="{{ route('instructor.sections.roster', ['locale' => $locale, 'section' => $section->id]) }}"><i class="fa-solid fa-id-card"></i> الطلاب</a>
        @endcanInstructor
        @canInstructor('instructor.attendance.view')
            <a href="{{ route('instructor.attendance', ['locale' => $locale]) }}"><i class="fa-solid fa-user-check"></i> الحضور</a>
        @endcanInstructor
        @canInstructor('instructor.assignments.grade')
            <a href="{{ route('instructor.assignments', ['locale' => $locale]) }}"><i class="fa-solid fa-clipboard-check"></i> التصحيح</a>
        @endcanInstructor
        @canInstructor('instructor.exams.view')
            <a href="{{ route('instructor.exams', ['locale' => $locale]) }}"><i class="fa-solid fa-file-circle-check"></i> الاختبارات</a>
        @endcanInstructor
    </div>

    <div class="portal-inst-kpis portal-inst-kpis--compact">
        <div class="portal-inst-kpi">
            <span class="portal-inst-kpi__value">{{ $section->students_count }}</span>
            <span class="portal-inst-kpi__label">طلاب</span>
        </div>
        <div class="portal-inst-kpi">
            <span class="portal-inst-kpi__value">{{ $this->sessions->count() }}</span>
            <span class="portal-inst-kpi__label">حصص</span>
        </div>
        <div class="portal-inst-kpi">
            <span class="portal-inst-kpi__value">{{ $section->schedule?->displayTrainer() ?? '—' }}</span>
            <span class="portal-inst-kpi__label">المدرب</span>
        </div>
    </div>

    @if ($flashMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact" wire:key="course-flash">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ $flashMessage }}</div>
        </div>
    @endif

    @if ($section->course)
        <section class="portal-inst-section">
            <header class="portal-inst-section__head">
                <h2>بطاقة المقرر</h2>
                <p>المعلومات التعريفية التي تظهر ضمن الخطة الأكاديمية للطلاب.</p>
            </header>

            @canInstructor('instructor.content.update')
                <form wire:submit="saveCourseDetails" class="portal-inst-course-form">
                    <div class="portal-inst-course-form__field">
                        <label for="course-summary">وصف المقرر</label>
                        <textarea id="course-summary" wire:model="courseSummary" rows="4" maxlength="5000" placeholder="ملخص واضح لأهداف ومحتوى المقرر"></textarea>
                        @error('courseSummary')<small class="portal-inst-course-form__error">{{ $message }}</small>@enderror
                    </div>
                    <div class="portal-inst-course-form__field">
                        <label for="course-target-group">الفئة المستهدفة</label>
                        <textarea id="course-target-group" wire:model="courseTargetGroup" rows="3" maxlength="1000" placeholder="المتطلبات أو الفئة المناسبة لهذا المقرر"></textarea>
                        @error('courseTargetGroup')<small class="portal-inst-course-form__error">{{ $message }}</small>@enderror
                    </div>
                    <div class="portal-inst-course-form__actions">
                        <span><i class="fa-solid fa-shield-halved"></i> لا يمكن تعديل الكود أو الساعات المعتمدة إلا من الإدارة الأكاديمية.</span>
                        <button type="submit" class="portal-btn portal-btn--primary portal-btn--sm" wire:loading.attr="disabled">
                            <i class="fa-solid fa-floppy-disk"></i> حفظ التفاصيل
                        </button>
                    </div>
                </form>
            @else
                <div class="portal-inst-course-readonly">
                    <div><strong>الوصف</strong><p>{{ $section->course->summary ?: 'لا يوجد وصف مسجل.' }}</p></div>
                    <div><strong>الفئة المستهدفة</strong><p>{{ $section->course->target_group ?: 'غير محددة.' }}</p></div>
                </div>
            @endcanInstructor
        </section>
    @endif

    <section class="portal-inst-section">
        <header class="portal-inst-section__head">
            <h2>حصص الشعبة</h2>
            <p>افتح مركز إدارة الحصة لرفع المرفقات، نشر الواجبات، ونشر التسجيلات.</p>
        </header>

        @if ($this->sessions->isEmpty())
            <div class="portal-alert portal-alert--info portal-alert--compact">
                <span class="portal-alert__icon"><i class="fa-solid fa-calendar"></i></span>
                <div class="portal-alert__content">لا توجد حصص مسجّلة لهذه الشعبة بعد.</div>
            </div>
        @else
            <div class="portal-inst-table-wrap">
                <table class="portal-inst-table">
                    <thead>
                        <tr>
                            <th>الحصة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>مرفقات</th>
                            <th>واجبات</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->sessions as $session)
                            @php $state = $this->sessionState($session); @endphp
                            <tr wire:key="sess-{{ $session->id }}">
                                <td><strong>{{ $session->displayTitle() }}</strong></td>
                                <td dir="ltr">{{ $session->session_date->format('Y-m-d') }}</td>
                                <td><span class="portal-inst-badge portal-inst-badge--{{ $state }}">{{ match($state) { 'live' => 'مباشر', 'completed' => 'منتهية', 'upcoming' => 'قادمة', default => $session->status } }}</span></td>
                                <td>{{ $session->materials_count }}</td>
                                <td>{{ $session->assignments_count }}</td>
                                <td>
                                    <a href="{{ route('instructor.sessions.show', ['locale' => $locale, 'section' => $section->id, 'session' => $session->id]) }}" class="portal-btn portal-btn--secondary portal-btn--sm">إدارة</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

@push('styles')
<style>
    .portal-inst-course-form{display:flex;flex-direction:column;gap:.85rem}
    .portal-inst-course-form__field{display:flex;flex-direction:column;gap:.35rem}
    .portal-inst-course-form__field label{color:#1e293b;font-size:.78rem;font-weight:900}
    .portal-inst-course-form__field textarea{width:100%;padding:.7rem .8rem;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font:inherit;font-size:.82rem;line-height:1.7;resize:vertical}
    .portal-inst-course-form__field textarea:focus{outline:0;border-color:#1b8354;box-shadow:0 0 0 3px rgba(27,131,84,.1)}
    .portal-inst-course-form__error{color:#b91c1c;font-size:.7rem;font-weight:700}
    .portal-inst-course-form__actions{display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .portal-inst-course-form__actions>span{color:#64748b;font-size:.7rem}
    .portal-inst-course-readonly{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    .portal-inst-course-readonly>div{padding:.8rem;border-radius:10px;background:#f8fafc}
    .portal-inst-course-readonly strong{color:#334155;font-size:.75rem}
    .portal-inst-course-readonly p{margin:.35rem 0 0;color:#64748b;font-size:.8rem;line-height:1.7}
    .portal-inst-hero-actions{display:flex;flex-wrap:wrap;gap:.5rem}
    .portal-inst-quick-nav{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
    .portal-inst-quick-nav a{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem .85rem;border-radius:999px;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;font-size:.78rem;font-weight:800;text-decoration:none}
    .portal-inst-quick-nav a:hover{background:#ccfbf1}
    @media(max-width:640px){.portal-inst-course-form__actions{align-items:flex-start;flex-direction:column}.portal-inst-course-readonly{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.instructor.shell-end')
