<?php

use App\Services\AcademicSessionService;
use App\Services\InstructorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('الحضور | لوحة المدرب')]
class extends Component
{
    public function mount(InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.attendance.view');
    }

    #[Computed]
    public function sessions()
    {
        return app(InstructorService::class)->recentSessionsForAttendance(auth()->user(), 30);
    }

    #[Computed]
    public function todaySessions()
    {
        return app(InstructorService::class)->todaySessionsFor(auth()->user());
    }

    #[Computed]
    public function summary(): array
    {
        $sessions = $this->sessions;

        return [
            'sessions' => $sessions->count(),
            'present' => (int) $sessions->sum('present_count'),
            'absent' => (int) $sessions->sum('absent_count'),
            'today' => $this->todaySessions->count(),
        ];
    }

    public function sessionState($session): string
    {
        return app(AcademicSessionService::class)->resolveTiming($session)['state'];
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.instructor.shell-start', ['instructorActive' => 'attendance', 'instructorTitle' => 'الحضور'])

<div class="portal-dashboard portal-dashboard--instructor">
    @include('partials.instructor.page-hero', [
        'title' => 'متابعة الحضور',
        'desc' => 'راجع حضور حصصك الأخيرة وافتح مركز الحصة لتسجيل أو مزامنة الحضور.',
        'icon' => 'fa-user-check',
        'stats' => [
            ['value' => $this->summary['today'], 'label' => 'حصص اليوم'],
            ['value' => $this->summary['present'], 'label' => 'حضور مسجّل'],
            ['value' => $this->summary['absent'], 'label' => 'غياب'],
        ],
        'actions' => [
            ['href' => route('instructor.sections', ['locale' => $locale]), 'label' => 'شعبي', 'icon' => 'fa-users-rectangle', 'class' => 'btn-outline-primary'],
            ['href' => route('instructor.dashboard', ['locale' => $locale]), 'label' => 'العودة للوحة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ],
    ])

    <div class="portal-kpi-strip portal-kpi-strip--4">
        <div class="portal-kpi-v2 portal-kpi-v2--week">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-sun"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->summary['today'] }}</span><span class="portal-kpi-v2__label">حصص اليوم</span></span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--sections">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-calendar"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->summary['sessions'] }}</span><span class="portal-kpi-v2__label">حصص أخيرة</span></span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--students">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-user-check"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->summary['present'] }}</span><span class="portal-kpi-v2__label">حاضر / متأخر</span></span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--live">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-user-xmark"></i></span>
            <span class="portal-kpi-v2__body"><span class="portal-kpi-v2__value">{{ $this->summary['absent'] }}</span><span class="portal-kpi-v2__label">غائب</span></span>
        </div>
    </div>

    @if ($this->todaySessions->isNotEmpty())
        <section class="portal-panel">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-sun"></i> حصص اليوم</h2>
            </div>
            <div class="portal-panel__body">
                <div class="portal-order-list">
                    @foreach ($this->todaySessions as $session)
                        @php $state = $this->sessionState($session); @endphp
                        <a href="{{ route('instructor.sessions.show', ['locale' => $locale, 'section' => $session->section_id, 'session' => $session->id]) }}" class="portal-order-item" wire:key="att-today-{{ $session->id }}">
                            <span class="portal-order-item__icon"><i class="fa-solid fa-chalkboard"></i></span>
                            <span class="portal-order-item__main">
                                <span class="portal-order-item__ref">{{ $session->displayTitle() }}</span>
                                <span class="portal-order-item__date">{{ $session->section?->name }}</span>
                            </span>
                            <span class="portal-inst-badge portal-inst-badge--{{ $state }}">{{ match($state) { 'live' => 'مباشر', 'upcoming' => 'قادمة', 'completed' => 'منتهية', default => 'مجدولة' } }}</span>
                            <span class="portal-order-item__amount">تسجيل ←</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-chart-column"></i> ملخص الحضور للحصص الأخيرة</h2>
            <span class="portal-panel__meta">{{ $this->summary['sessions'] }}</span>
        </div>
        <div class="portal-panel__body">
            @if ($this->sessions->isEmpty())
                <div class="portal-empty">
                    <div class="portal-empty__icon"><i class="fa-solid fa-calendar-xmark"></i></div>
                    <p>لا توجد حصص سابقة لعرض الحضور</p>
                    <span class="portal-empty__hint">ستظهر هنا إحصاءات الحضور بعد انعقاد الحصص</span>
                </div>
            @else
                <div class="portal-inst-table-wrap">
                    <table class="portal-inst-table">
                        <thead>
                            <tr>
                                <th>الحصة</th>
                                <th>الشعبة</th>
                                <th>التاريخ</th>
                                <th>حاضر/متأخر</th>
                                <th>غائب</th>
                                <th>بعذر</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->sessions as $session)
                                <tr wire:key="att-{{ $session->id }}">
                                    <td><strong>{{ $session->displayTitle() }}</strong></td>
                                    <td>{{ $session->section?->name }}</td>
                                    <td dir="ltr">{{ $session->session_date->format('Y-m-d') }}</td>
                                    <td><span class="portal-inst-badge portal-inst-badge--att-present">{{ $session->present_count }}</span></td>
                                    <td><span class="portal-inst-badge portal-inst-badge--att-absent">{{ $session->absent_count }}</span></td>
                                    <td><span class="portal-inst-badge portal-inst-badge--att-excused">{{ $session->excused_count }}</span></td>
                                    <td>
                                        <a href="{{ route('instructor.sessions.show', ['locale' => $locale, 'section' => $session->section_id, 'session' => $session->id]) }}" class="btn btn-outline-secondary btn-sm">إدارة</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>

@push('styles')
<style>
.portal-hero--page .portal-hero__banner--compact{min-height:auto}
.portal-hero--page .portal-hero__eyebrow{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.78);margin-bottom:.35rem}
.portal-hero--page .portal-hero__body--compact{margin-top:-1.1rem;padding-top:0;padding-bottom:1rem}
.portal-hero--page .portal-hero__actions--start{justify-content:flex-start}
.portal-panel__meta{font-size:.78rem;font-weight:800;color:#0f766e;background:#ecfdf5;padding:.3rem .7rem;border-radius:999px}
.portal-kpi-strip--4{grid-template-columns:repeat(4,minmax(0,1fr))}
.portal-dashboard--instructor .portal-kpi-v2--week{border-right-color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--sections{border-right-color:#0d9488}
.portal-dashboard--instructor .portal-kpi-v2--students{border-right-color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--live{border-right-color:#dc2626}
.portal-dashboard--instructor .portal-kpi-v2--week .portal-kpi-v2__icon{background:#ecfdf5;color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--sections .portal-kpi-v2__icon{background:#f0fdfa;color:#0d9488}
.portal-dashboard--instructor .portal-kpi-v2--students .portal-kpi-v2__icon{background:#eff6ff;color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--live .portal-kpi-v2__icon{background:#fef2f2;color:#dc2626}
@media(max-width:991.98px){.portal-kpi-strip--4{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endpush

@include('partials.instructor.shell-end')
