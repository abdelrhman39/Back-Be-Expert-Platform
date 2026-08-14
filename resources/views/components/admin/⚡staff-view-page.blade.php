<?php

use App\Models\AcademicSchedule;
use App\Models\AcademicStaff;
use App\Support\AcademicStaffOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('ملف عضو الكادر | لوحة التحكم')]
class extends Component
{
    public AcademicStaff $staff;

    public function mount(AcademicStaff $staff): void
    {
        $this->staff = $staff;
    }

    #[Computed]
    public function schedules()
    {
        return AcademicSchedule::query()
            ->with(['section.course', 'section.batch', 'level'])
            ->where('staff_id', $this->staff->id)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $schedules = $this->schedules;

        return [
            'sessions' => $schedules->count(),
            'sections' => $schedules->pluck('section_id')->unique()->filter()->count(),
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.staff.members'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.staff'), 'label' => 'الكوادر الأكاديمية'],
        ['href' => route('admin.staff.members'), 'label' => 'إدارة الأعضاء'],
        ['label' => $staff->name_ar],
    ],
])

@error('staff')
    <div class="admin-alert admin-alert--error is-visible">{{ $message }}</div>
@enderror

<div class="student-profile">
    <header class="student-profile-hero">
        <div class="student-profile-hero__bar">
            <div class="student-profile-hero__start">
                <div class="student-profile-avatar" aria-hidden="true">{{ mb_substr($staff->name_ar, 0, 1) }}</div>
                <div class="student-profile-hero__titles">
                    <p class="student-profile-hero__eyebrow">عضو كادر · #{{ $staff->id }}</p>
                    <h1 class="student-profile-hero__name">{{ $staff->name_ar }}</h1>
                    @if ($staff->name_en)
                        <p class="student-profile-hero__name-en" dir="ltr">{{ $staff->name_en }}</p>
                    @endif
                </div>
            </div>

            <dl class="student-profile-hero__stats">
                <div class="student-profile-hero__stat">
                    <dt>الدور</dt>
                    <dd>{{ AcademicStaffOptions::roleLabel($staff->role) }}</dd>
                </div>
                <div class="student-profile-hero__stat">
                    <dt>التخصص</dt>
                    <dd>{{ $staff->specialty ?? '—' }}</dd>
                </div>
                <div class="student-profile-hero__stat">
                    <dt>المقررات</dt>
                    <dd>{{ $staff->courses_count }}</dd>
                </div>
            </dl>

            <div class="student-profile-hero__end">
                <div class="student-profile-hero__badges">
                    <span @class([
                        'admin-badge',
                        'admin-badge--success' => $staff->status === 'active',
                        'admin-badge--warn' => $staff->status === 'on_leave',
                        'admin-badge--danger' => $staff->status === 'inactive',
                    ])>
                        {{ AcademicStaffOptions::statusLabel($staff->status) }}
                    </span>
                    @if ($staff->gender)
                        <span class="admin-badge">{{ $staff->gender }}</span>
                    @endif
                </div>
                <div class="student-profile-hero__actions">
                    @canAdmin('staff.impersonate')
                        @if ($staff->canBeImpersonated())
                            <form method="post" action="{{ route('admin.staff.impersonate', $staff) }}">
                                @csrf
                                <button type="submit" class="admin-btn-primary admin-btn-primary--sm">
                                    <i class="fa-solid fa-right-to-bracket"></i> دخول كمدرب
                                </button>
                            </form>
                        @else
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" disabled title="{{ $staff->impersonationBlockReason() }}">
                                دخول كمدرب غير متاح
                            </button>
                        @endif
                    @endcanAdmin
                    @canAdmin('staff.manage')
                        <a href="{{ route('admin.staff.edit', $staff) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل البيانات</a>
                    @endcanAdmin
                    <a href="{{ route('admin.staff.members') }}" class="admin-btn-primary admin-btn-primary--sm">كافة الأعضاء</a>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-dashboard-grid" style="margin-top:1rem;">
        <section class="admin-crud-card">
            <div class="admin-crud-card__head"><h2>البيانات الأساسية</h2></div>
            <dl class="admin-detail-grid">
                <div><dt>الدور</dt><dd>{{ AcademicStaffOptions::roleLabel($staff->role) }}</dd></div>
                <div><dt>التخصص</dt><dd>{{ $staff->specialty ?? '—' }}</dd></div>
                <div><dt>عدد المقررات</dt><dd>{{ $staff->courses_count }}</dd></div>
                <div><dt>ساعات/أسبوع</dt><dd>{{ $staff->hours_per_week }}</dd></div>
                <div><dt>إجمالي المكافآت</dt><dd>{{ number_format((float) $staff->compensation_total, 0) }} ر.س</dd></div>
                <div><dt>الحالة</dt><dd>{{ AcademicStaffOptions::statusLabel($staff->status) }}</dd></div>
                <div><dt>حساب البوابة</dt><dd>{{ $staff->user?->email ?? 'غير مرتبط' }}</dd></div>
                <div><dt>إمكانية الدخول المباشر</dt><dd>{{ $staff->impersonationBlockReason() ?? 'جاهز' }}</dd></div>
            </dl>
        </section>

        <section class="admin-crud-card">
            <div class="admin-crud-card__head">
                <h2>الجداول الدراسية <span class="admin-crud-card__meta">— {{ $this->stats['sessions'] }} حصة</span></h2>
            </div>
            @if ($this->schedules->isEmpty())
                <p class="admin-crud-card__meta" style="padding:1rem;">لا توجد حصص مرتبطة بهذا العضو.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>اليوم</th>
                                <th>الوقت</th>
                                <th>المقرر / الشعبة</th>
                                <th>الفترة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->day_of_week ?? '—' }}</td>
                                    <td dir="ltr">{{ $schedule->time_start }} – {{ $schedule->time_end }}</td>
                                    <td>
                                        {{ $schedule->section?->course?->name_ar ?? $schedule->trainer_name ?? '—' }}
                                        @if ($schedule->section?->name)
                                            <span class="admin-table-sub">{{ $schedule->section->name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->period ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>

@push('styles')
<style>
    .admin-detail-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:0.85rem 1.25rem; padding:0 0.25rem 0.5rem; }
    .admin-detail-grid dt { font-size:0.68rem; font-weight:700; color:var(--sa-muted,#5c6b64); margin-bottom:0.15rem; }
    .admin-detail-grid dd { margin:0; font-size:0.85rem; font-weight:700; color:var(--sa-ink,#1a1a1a); }
</style>
@endpush

@include('partials.admin.shell-end')
