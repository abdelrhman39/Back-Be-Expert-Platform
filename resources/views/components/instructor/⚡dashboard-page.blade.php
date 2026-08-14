<?php

use App\Services\AcademicSessionService;
use App\Services\InstructorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('لوحة المدرب | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function staff()
    {
        return app(InstructorService::class)->resolveStaff(auth()->user());
    }

    #[Computed]
    public function sections()
    {
        return app(InstructorService::class)->sectionsFor(auth()->user());
    }

    #[Computed]
    public function stats(): array
    {
        return app(InstructorService::class)->dashboardStats(auth()->user());
    }

    #[Computed]
    public function todaySessions()
    {
        return app(InstructorService::class)->todaySessionsFor(auth()->user());
    }

    #[Computed]
    public function upcomingSessions()
    {
        return app(InstructorService::class)->upcomingSessionsFor(auth()->user(), 5);
    }

    #[Computed]
    public function pendingAssignments()
    {
        return app(InstructorService::class)->pendingAssignmentSubmissionsFor(auth()->user(), 5);
    }

    #[Computed]
    public function pendingExams()
    {
        return app(InstructorService::class)->pendingExamAttemptsFor(auth()->user(), 5);
    }

    #[Computed]
    public function greeting(): string
    {
        $hour = (int) now()->format('H');

        if ($hour < 12) {
            return 'صباح الخير';
        }

        if ($hour < 17) {
            return 'مساء الخير';
        }

        return 'مساء النور';
    }

    public function sessionState($session): string
    {
        return app(AcademicSessionService::class)->resolveTiming($session)['state'];
    }
};
?>

@php
    $locale = app()->getLocale();
    $staff = $this->staff;
    $user = auth()->user();
    $pendingTotal = $this->stats['pending_grades'] + $this->stats['pending_exams'];
@endphp

@include('partials.instructor.shell-start', ['instructorActive' => 'dashboard', 'instructorTitle' => 'نظرة عامة'])

<div class="portal-dashboard portal-dashboard--profile portal-dashboard--instructor">
    <section class="portal-hero portal-hero--v2">
        <div class="portal-hero__banner">
            <div class="portal-hero__banner-content">
                <div class="portal-hero__welcome">
                    <span class="portal-hero__greeting">{{ $this->greeting }}، {{ $staff?->name_ar ?? $user->displayName() }}</span>
                    <p class="portal-hero__tagline">مرحباً بك في لوحة المدرب — تابع شعبك، الحصص، والتصحيح من مكان واحد.</p>
                </div>
                <div class="portal-hero__banner-stats">
                    <div class="portal-banner-stat">
                        <span class="portal-banner-stat__value">{{ $this->stats['sections'] }}</span>
                        <span class="portal-banner-stat__label">شعب نشطة</span>
                    </div>
                    <div class="portal-banner-stat">
                        <span class="portal-banner-stat__value">{{ $this->stats['students'] }}</span>
                        <span class="portal-banner-stat__label">طلاب</span>
                    </div>
                    <div class="portal-banner-stat">
                        <span class="portal-banner-stat__value">{{ $this->stats['today'] }}</span>
                        <span class="portal-banner-stat__label">حصص اليوم</span>
                    </div>
                </div>
            </div>
            <div class="portal-hero__orbs" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
        </div>

        <div class="portal-hero__body">
            <div class="portal-hero__profile-row">
                <span class="portal-hero__avatar">{{ $user?->initials() }}</span>
                <div class="portal-hero__identity">
                    <h1 class="portal-hero__name">{{ $staff?->name_ar ?? $user->displayName() }}</h1>
                    <div class="portal-hero__badges">
                        <span class="portal-badge portal-badge--role"><i class="fa-solid fa-chalkboard-user"></i> مدرب</span>
                        @if ($staff?->specialty)
                            <span class="portal-badge portal-badge--status">{{ $staff->specialty }}</span>
                        @endif
                        @if ($staff?->permission_preset)
                            <span class="portal-badge portal-badge--muted">{{ \App\Support\InstructorPermissions::presetLabels()[$staff->permission_preset] ?? $staff->permission_preset }}</span>
                        @endif
                    </div>
                    <div class="portal-hero__chips">
                        @if ($user?->email)
                            <span class="portal-chip"><i class="fa fa-envelope"></i> {{ $user->email }}</span>
                        @endif
                        @if ($user?->phone)
                            <span class="portal-chip" dir="ltr"><i class="fa fa-phone"></i> {{ $user->phone }}</span>
                        @endif
                        <span class="portal-chip"><i class="fa fa-calendar"></i> {{ now()->translatedFormat('l d F Y') }}</span>
                    </div>
                </div>
                <div class="portal-hero__actions">
                    @canInstructor('instructor.sections.view')
                        <a href="{{ route('instructor.sections', ['locale' => $locale]) }}" class="btn btn-primary">
                            <i class="fa-solid fa-users-rectangle"></i> شعبي
                        </a>
                    @endcanInstructor
                    @canInstructor('instructor.exams.create')
                        <a href="{{ route('instructor.exams', ['locale' => $locale]) }}" class="btn btn-outline-primary">
                            <i class="fa-solid fa-plus"></i> اختبار جديد
                        </a>
                    @endcanInstructor
                    @canInstructor('instructor.profile.update')
                        <a href="{{ route('instructor.settings', ['locale' => $locale]) }}" class="btn btn-light border">الإعدادات</a>
                    @endcanInstructor
                </div>
            </div>
        </div>
    </section>

    @if ($pendingTotal > 0)
        <div class="portal-pending-banner">
            <div class="portal-pending-banner__icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="portal-pending-banner__text">
                <strong>{{ $pendingTotal }} عنصر بانتظار التصحيح</strong>
                <span>{{ $this->stats['pending_grades'] }} واجب · {{ $this->stats['pending_exams'] }} اختبار</span>
            </div>
            <a href="{{ route('instructor.assignments', ['locale' => $locale]) }}" class="btn btn-sm btn-warning">فتح صندوق التصحيح</a>
        </div>
    @endif

    <div class="portal-kpi-strip">
        <a href="{{ route('instructor.sections', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--sections">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-users-rectangle"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['sections'] }}</span>
                <span class="portal-kpi-v2__label">شعب نشطة</span>
            </span>
        </a>
        <a href="{{ route('instructor.sections', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--students">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-user-graduate"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['students'] }}</span>
                <span class="portal-kpi-v2__label">طلاب مسجّلون</span>
            </span>
        </a>
        <div class="portal-kpi-v2 portal-kpi-v2--live">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-tower-broadcast"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['live_now'] }}</span>
                <span class="portal-kpi-v2__label">حصص مباشرة الآن</span>
            </span>
        </div>
        <a href="{{ route('instructor.assignments', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--grades">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-clipboard-list"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['pending_grades'] }}</span>
                <span class="portal-kpi-v2__label">واجبات للتصحيح</span>
            </span>
        </a>
        <a href="{{ route('instructor.exams', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--exams">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-file-circle-check"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['pending_exams'] }}</span>
                <span class="portal-kpi-v2__label">اختبارات للتصحيح</span>
            </span>
        </a>
        <a href="{{ route('instructor.attendance', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--week">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-calendar-week"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['sessions_week'] }}</span>
                <span class="portal-kpi-v2__label">حصص هذا الأسبوع</span>
            </span>
        </a>
    </div>

    @if ($this->todaySessions->isNotEmpty())
        <section class="portal-panel">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-sun"></i> حصص اليوم</h2>
                <a href="{{ route('instructor.attendance', ['locale' => $locale]) }}" class="portal-panel__link">سجل الحضور <i class="fa-solid fa-arrow-left-long"></i></a>
            </div>
            <div class="portal-panel__body">
                <div class="portal-inst-today-grid">
                    @foreach ($this->todaySessions as $session)
                        @php $state = $this->sessionState($session); @endphp
                        <article class="portal-inst-today-card @if($state === 'live') is-live @endif" wire:key="today-{{ $session->id }}">
                            <div class="portal-inst-today-card__top">
                                <span class="portal-inst-badge portal-inst-badge--{{ $state }}">{{ match($state) { 'live' => 'مباشر الآن', 'upcoming' => 'قادمة', 'completed' => 'منتهية', default => 'مجدولة' } }}</span>
                                <span dir="ltr">{{ \Illuminate\Support\Str::of($session->time_start)->substr(0, 5) }}–{{ \Illuminate\Support\Str::of($session->time_end)->substr(0, 5) }}</span>
                            </div>
                            <h3>{{ $session->displayTitle() }}</h3>
                            <p>{{ $session->section?->name }}</p>
                            <a href="{{ route('instructor.sessions.show', ['locale' => $locale, 'section' => $session->section_id, 'session' => $session->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-door-open"></i> فتح مركز الحصة
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="portal-dashboard-grid portal-dashboard-grid--wide">
        <div class="portal-main-col">
            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-calendar-days"></i> الحصص القادمة</h2>
                    <a href="{{ route('instructor.attendance', ['locale' => $locale]) }}" class="portal-panel__link">سجل الحضور <i class="fa-solid fa-arrow-left-long"></i></a>
                </div>
                <div class="portal-panel__body">
                    @if ($this->upcomingSessions->isEmpty())
                        <div class="portal-empty">
                            <div class="portal-empty__icon"><i class="fa-solid fa-calendar"></i></div>
                            <p>لا توجد حصص قادمة حالياً</p>
                            <span class="portal-empty__hint">ستظهر هنا أقرب الحصص المسندة إلى شعبك</span>
                        </div>
                    @else
                        <div class="portal-order-list">
                            @foreach ($this->upcomingSessions as $session)
                                @php $state = $this->sessionState($session); @endphp
                                <a href="{{ route('instructor.sessions.show', ['locale' => $locale, 'section' => $session->section_id, 'session' => $session->id]) }}" class="portal-order-item" wire:key="up-{{ $session->id }}">
                                    <span class="portal-order-item__icon"><i class="fa-solid fa-chalkboard"></i></span>
                                    <span class="portal-order-item__main">
                                        <span class="portal-order-item__ref">{{ $session->displayTitle() }}</span>
                                        <span class="portal-order-item__date">{{ $session->section?->name }} · {{ $session->session_date->translatedFormat('d M Y') }}</span>
                                    </span>
                                    <span class="portal-inst-badge portal-inst-badge--{{ $state }}">{{ match($state) { 'live' => 'مباشر', 'upcoming' => 'قادمة', default => 'مجدولة' } }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-users-rectangle"></i> شعبي الدراسية</h2>
                    <a href="{{ route('instructor.sections', ['locale' => $locale]) }}" class="portal-panel__link">إدارة الشعب <i class="fa-solid fa-arrow-left-long"></i></a>
                </div>
                <div class="portal-panel__body">
                    @if ($this->sections->isEmpty())
                        <div class="portal-empty">
                            <div class="portal-empty__icon"><i class="fa-solid fa-users-slash"></i></div>
                            <p>لم تُربط بأي شعبة بعد</p>
                            <span class="portal-empty__hint">تواصل مع الإدارة لربط حسابك بجدول تدريبي</span>
                        </div>
                    @else
                        <div class="portal-inst-sec-grid">
                            @foreach ($this->sections as $section)
                                <div wire:key="sec-{{ $section->id }}">
                                    @include('partials.instructor.section-card', ['section' => $section, 'locale' => $locale])
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <aside class="portal-side-col">
            <div class="portal-widget portal-widget--academic">
                <div class="portal-widget__head">
                    <span class="portal-widget__head-icon"><i class="fa-solid fa-bolt"></i></span>
                    <h3 class="portal-widget__title">اختصارات سريعة</h3>
                </div>
                <div class="portal-academic-list portal-inst-shortcuts">
                    @canInstructor('instructor.sections.view')
                        <a href="{{ route('instructor.sections', ['locale' => $locale]) }}" class="portal-academic-item portal-academic-item--link">
                            <span class="portal-academic-item__label"><i class="fa-solid fa-users-rectangle"></i> شعبي</span>
                            <strong>{{ $this->stats['sections'] }}</strong>
                        </a>
                    @endcanInstructor
                    @canInstructor('instructor.assignments.grade')
                        <a href="{{ route('instructor.assignments', ['locale' => $locale]) }}" class="portal-academic-item portal-academic-item--link">
                            <span class="portal-academic-item__label"><i class="fa-solid fa-clipboard-check"></i> التصحيح</span>
                            <strong>{{ $pendingTotal }}</strong>
                        </a>
                    @endcanInstructor
                    @canInstructor('instructor.attendance.view')
                        <a href="{{ route('instructor.attendance', ['locale' => $locale]) }}" class="portal-academic-item portal-academic-item--link">
                            <span class="portal-academic-item__label"><i class="fa-solid fa-user-check"></i> الحضور</span>
                            <strong>{{ $this->stats['today'] }}</strong>
                        </a>
                    @endcanInstructor
                    @canInstructor('instructor.exams.view')
                        <a href="{{ route('instructor.exams', ['locale' => $locale]) }}" class="portal-academic-item portal-academic-item--link">
                            <span class="portal-academic-item__label"><i class="fa-solid fa-file-circle-check"></i> الاختبارات</span>
                            <strong>{{ $this->stats['pending_exams'] }}</strong>
                        </a>
                    @endcanInstructor
                </div>
            </div>

            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-inbox"></i> طابور التصحيح</h2>
                    <a href="{{ route('instructor.assignments', ['locale' => $locale]) }}" class="portal-panel__link">عرض الكل <i class="fa-solid fa-arrow-left-long"></i></a>
                </div>
                <div class="portal-panel__body">
                    @if ($this->pendingAssignments->isEmpty() && $this->pendingExams->isEmpty())
                        <div class="portal-empty portal-empty--compact">
                            <div class="portal-empty__icon"><i class="fa-solid fa-circle-check"></i></div>
                            <p>لا يوجد شيء بانتظار التصحيح</p>
                        </div>
                    @else
                        <div class="portal-inst-queue">
                            @foreach ($this->pendingAssignments as $submission)
                                @php
                                    $assignment = $submission->assignment;
                                    $queueHref = $assignment?->attendance_session_id
                                        ? route('instructor.sessions.show', ['locale' => $locale, 'section' => $assignment->section_id, 'session' => $assignment->attendance_session_id])
                                        : route('instructor.assignments', ['locale' => $locale]);
                                @endphp
                                <a class="portal-inst-queue__item" href="{{ $queueHref }}" wire:key="asub-{{ $submission->id }}">
                                    <span class="portal-inst-queue__tag">واجب</span>
                                    <div>
                                        <strong>{{ $submission->student?->name_ar ?: 'طالب' }}</strong>
                                        <small>{{ $assignment?->title }} · {{ $assignment?->section?->name }}</small>
                                    </div>
                                </a>
                            @endforeach
                            @foreach ($this->pendingExams as $attempt)
                                <a class="portal-inst-queue__item" href="{{ route('instructor.exams.grading', ['locale' => $locale, 'section' => $attempt->exam->section_id, 'exam' => $attempt->exam_id]) }}" wire:key="exat-{{ $attempt->id }}">
                                    <span class="portal-inst-queue__tag is-exam">اختبار</span>
                                    <div>
                                        <strong>{{ $attempt->student?->name_ar ?: 'طالب' }}</strong>
                                        <small>{{ $attempt->exam?->title }} · {{ $attempt->exam?->section?->name }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>

@push('styles')
<style>
.portal-dashboard--instructor .portal-kpi-v2--sections { border-right-color: #0d9488; }
.portal-dashboard--instructor .portal-kpi-v2--students { border-right-color: #2563eb; }
.portal-dashboard--instructor .portal-kpi-v2--live { border-right-color: #dc2626; }
.portal-dashboard--instructor .portal-kpi-v2--grades { border-right-color: #d97706; }
.portal-dashboard--instructor .portal-kpi-v2--exams { border-right-color: #7c3aed; }
.portal-dashboard--instructor .portal-kpi-v2--week { border-right-color: #059669; }
.portal-dashboard--instructor .portal-kpi-v2--sections .portal-kpi-v2__icon { background:#f0fdfa; color:#0d9488; }
.portal-dashboard--instructor .portal-kpi-v2--students .portal-kpi-v2__icon { background:#eff6ff; color:#2563eb; }
.portal-dashboard--instructor .portal-kpi-v2--live .portal-kpi-v2__icon { background:#fef2f2; color:#dc2626; }
.portal-dashboard--instructor .portal-kpi-v2--grades .portal-kpi-v2__icon { background:#fffbeb; color:#d97706; }
.portal-dashboard--instructor .portal-kpi-v2--exams .portal-kpi-v2__icon { background:#f5f3ff; color:#7c3aed; }
.portal-dashboard--instructor .portal-kpi-v2--week .portal-kpi-v2__icon { background:#ecfdf5; color:#059669; }
.portal-dashboard--instructor .portal-kpi-strip{grid-template-columns:repeat(6,minmax(0,1fr))}
@media(max-width:1199.98px){.portal-dashboard--instructor .portal-kpi-strip{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:575.98px){.portal-dashboard--instructor .portal-kpi-strip{grid-template-columns:repeat(2,minmax(0,1fr))}}
.portal-empty--compact{padding:1.25rem .75rem}
.portal-academic-item--link{text-decoration:none;color:inherit;transition:background .15s ease}
.portal-academic-item--link:hover{background:#f0fdfa}
.portal-inst-shortcuts .portal-academic-item__label{display:inline-flex;align-items:center;gap:.4rem}
.portal-inst-today-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem}
.portal-inst-today-card{display:grid;gap:.55rem;padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
.portal-inst-today-card.is-live{border-color:#fca5a5;background:linear-gradient(180deg,#fff,#fef2f2)}
.portal-inst-today-card__top{display:flex;justify-content:space-between;gap:.5rem;align-items:center;font-size:.78rem;color:#64748b}
.portal-inst-today-card h3{margin:0;font-size:.95rem;color:#0f172a}
.portal-inst-today-card p{margin:0;font-size:.8rem;color:#64748b}
.portal-inst-queue{display:grid;gap:.5rem}
.portal-inst-queue__item{display:flex;align-items:flex-start;gap:.65rem;padding:.75rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;text-decoration:none;color:inherit}
.portal-inst-queue__item:hover{border-color:#14b8a6}
.portal-inst-queue__item strong{display:block;font-size:.84rem;color:#0f172a}
.portal-inst-queue__item small{display:block;margin-top:.15rem;font-size:.72rem;color:#64748b}
.portal-inst-queue__tag{flex-shrink:0;padding:.2rem .45rem;border-radius:999px;background:#ecfdf5;color:#0f766e;font-size:.65rem;font-weight:800}
.portal-inst-queue__tag.is-exam{background:#eff6ff;color:#1d4ed8}
.portal-root--instructor .portal-sidebar-profile__role{background:#ccfbf1;color:#0f766e}
</style>
@endpush

@include('partials.instructor.shell-end')
