<?php

use App\Services\AdminStatsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'الكوادر الأكاديمية',
    'adminPageDesc' => 'توزيع الكادر والعبء التدريبي',
    'adminLayout' => 'dashboard',
])]
#[Title('الكوادر الأكاديمية | لوحة التحكم')]
class extends Component
{
    public array $stats = [];

    public function mount(AdminStatsService $stats): void
    {
        $this->stats = $stats->staff();
    }
};
?>

@include('partials.admin.dashboard-start', ['dashSubnav' => 'staff', 'dashSidebarActive' => route('admin.staff')])

<section class="staff-hub-intro">
    <div>
        <span>إدارة دورة حياة المدرب</span>
        <h1>من الحساب والصلاحيات إلى البرنامج ولوحة التدريس</h1>
        <p>أنشئ حساب المدرب، أسند له شعبة داخل برنامج، ثم استخدم الدخول المباشر للتحقق من المحتوى والطلاب والاختبارات.</p>
    </div>
    <div class="admin-row-actions">
        <a href="{{ route('admin.staff.members') }}" class="admin-btn-secondary">إدارة الكوادر</a>
        @canAdmin('staff.manage')
            <a href="{{ route('admin.staff.create') }}" class="admin-btn-primary">إضافة مدرب</a>
        @endcanAdmin
    </div>
</section>

<div class="staff-readiness-grid">
    <div><strong>{{ $stats['portal_accounts'] }}</strong><span>حسابات بوابة مرتبطة</span></div>
    <div><strong>{{ $stats['portal_ready'] }}</strong><span>جاهزون للدخول المباشر</span></div>
    <div><strong>{{ $stats['unassigned'] }}</strong><span>بحاجة إلى إسناد شعبة</span></div>
</div>

<div class="dash-grid dash-fin-top">
    <div class="dash-hero-card dash-hero-card--finance">
        <span>إجمالي مكافآت هيئة التدريس</span>
        <strong>{{ number_format($stats['compensation_total'], 0) }} <small style="font-size:0.55em;font-weight:600">ر.س</small></strong>
        <p class="dash-hero-card__sub">الأعضاء النشطون: {{ $stats['staff_active'] }} — إجمالي الساعات: {{ $stats['hours_total'] }}</p>
    </div>
    <div class="dash-side-rates">
        <div class="dash-progress-row">
            <div class="head"><span>متوسط العبء (مقررات)</span><span>{{ $stats['avg_courses'] }}</span></div>
            <div class="dash-rate-bar"><span style="width:{{ min(100, $stats['avg_courses'] * 20) }}%"></span></div>
        </div>
        <div class="dash-progress-row">
            <div class="head"><span>متوسط العبء (ساعات)</span><span>{{ $stats['avg_hours'] }}</span></div>
            <div class="dash-rate-bar dash-rate-bar--orange"><span style="width:{{ min(100, $stats['avg_hours'] * 5) }}%"></span></div>
        </div>
        <div class="dash-progress-row">
            <div class="head"><span>إجمالي الكادر</span><span>{{ $stats['staff_total'] }}</span></div>
        </div>
    </div>
</div>

<h3 class="dash-block-title">توزيع الكادر</h3>
<div class="dash-grid dash-row-gender" style="margin-top:0.5rem;">
    <div class="dash-gender-card">
        <h4>الكادر التدريبي</h4>
        <div class="dash-gender-legend"><span>ذكر {{ $stats['male_pct'] }}%</span><span>أنثى {{ $stats['female_pct'] }}%</span></div>
        <div class="dash-gender-bar"><span class="male" style="width:{{ $stats['male_pct'] }}%"></span><span class="female" style="width:{{ $stats['female_pct'] }}%"></span></div>
        <div class="dash-gender-stats"><span class="male-t">{{ $stats['male_count'] }} ذكر</span><span class="female-t">{{ $stats['female_count'] }} أنثى</span></div>
    </div>
    <div class="dash-gender-card">
        <h4>مؤشرات العبء</h4>
        <div class="dash-staff-grid">
            <div class="dash-kpi"><strong>{{ $stats['avg_courses'] }}</strong><span>متوسط المقررات</span></div>
            <div class="dash-kpi"><strong>{{ $stats['avg_hours'] }}</strong><span>متوسط الساعات</span></div>
            <div class="dash-kpi"><strong>{{ $stats['hours_total'] }}</strong><span>إجمالي الساعات</span></div>
        </div>
    </div>
</div>

<div class="dash-panel" style="margin-top:1rem;">
    <div class="dash-panel__head" style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;margin-bottom:0.75rem;">
        <h3 class="dash-panel__title" style="margin:0;">قائمة الكوادر</h3>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('admin.staff.members') }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة الأعضاء</a>
            @canAdmin('staff.manage')
                <a href="{{ route('admin.staff.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ عضو جديد</a>
            @endcanAdmin
        </div>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>التخصص</th>
                    <th>البرنامج</th>
                    <th>جاهزية الدخول</th>
                    <th>الساعات/أسبوع</th>
                    <th>المكافآت</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stats['staff_list'] as $member)
                    <tr>
                        <td>
                            {{ $member->name_ar }}
                            @canAdmin('staff.manage')
                                <div class="admin-row-actions" style="margin-top:0.35rem;">
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                </div>
                            @endcanAdmin
                        </td>
                        <td>{{ $member->specialty ?? '—' }}</td>
                        <td>{{ $member->schedules->pluck('section.program.name_ar')->filter()->unique()->join('، ') ?: 'غير مسند' }}</td>
                        <td>
                            @if ($member->canBeImpersonated())
                                <span class="admin-badge admin-badge--success">جاهز</span>
                                @canAdmin('staff.impersonate')
                                    <form method="post" action="{{ route('admin.staff.impersonate', $member) }}" style="display:inline-flex;margin-inline-start:.35rem">
                                        @csrf
                                        <button type="submit" class="admin-btn-primary admin-btn-primary--sm">دخول</button>
                                    </form>
                                @endcanAdmin
                            @else
                                <span class="admin-badge admin-badge--warn" title="{{ $member->impersonationBlockReason() }}">غير جاهز</span>
                            @endif
                        </td>
                        <td>{{ $member->hours_per_week }}</td>
                        <td>{{ number_format((float) $member->compensation_total, 0) }} ر.س</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:1.5rem;color:var(--sa-muted)">لا يوجد كادر مسجل.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.admin.dashboard-end')

@push('styles')
<style>
    .staff-hub-intro{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.35rem;margin-bottom:1rem;border-radius:18px;background:linear-gradient(135deg,#123b2a,#1b8354);color:#fff}
    .staff-hub-intro span{font-size:.7rem;font-weight:900;opacity:.78}.staff-hub-intro h1{margin:.25rem 0;font-size:1.3rem}.staff-hub-intro p{margin:0;max-width:720px;font-size:.78rem;opacity:.85}
    .staff-readiness-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1rem}.staff-readiness-grid>div{display:flex;flex-direction:column;padding:.9rem 1rem;border:1px solid #dbe7e0;border-radius:14px;background:#fff}.staff-readiness-grid strong{font-size:1.35rem;color:#145a38}.staff-readiness-grid span{font-size:.72rem;color:#64748b;font-weight:700}
    @media(max-width:760px){.staff-hub-intro{align-items:flex-start;flex-direction:column}.staff-readiness-grid{grid-template-columns:1fr}}
</style>
@endpush
