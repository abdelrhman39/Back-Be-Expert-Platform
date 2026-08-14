<?php

use App\Services\AdminStatsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'مؤشرات الأداء الرئيسية',
    'adminPageDesc' => 'نظرة شاملة على المتدربين والبرامج والأداء التشغيلي',
    'adminLayout' => 'dashboard',
])]
#[Title('لوحة التحكم | مركز التعلم المستمر')]
class extends Component
{
    public array $stats = [];

    public function mount(AdminStatsService $stats): void
    {
        $this->stats = $stats->dashboard();
    }
};
?>

@include('partials.admin.dashboard-start', ['dashSubnav' => 'home', 'dashSidebarActive' => route('admin.dashboard')])

<div class="dash-grid dash-row-hero">
    <div class="dash-hero-card">
        <span>إجمالي المتدربين (أكاديمي)</span>
        <strong>{{ number_format($stats['students_total']) }}</strong>
    </div>
    <div class="dash-rates-card">
        <div class="dash-rate-item">
            <label>المتدربون النشطون</label>
            <span class="value">{{ number_format($stats['students_active']) }}</span>
            <div class="dash-rate-bar"><span style="width:{{ $stats['students_total'] ? min(100, round(($stats['students_active'] / max(1, $stats['students_total'])) * 100)) : 0 }}%"></span></div>
        </div>
        <div class="dash-rate-item">
            <label>البرامج النشطة</label>
            <span class="value">{{ number_format($stats['programs_active']) }}</span>
            <div class="dash-rate-bar"><span style="width:{{ min(100, $stats['programs_active'] * 10) }}%"></span></div>
        </div>
        <div class="dash-rate-item">
            <label>طلبات بانتظار الدفع</label>
            <span class="value">{{ number_format($stats['orders_pending']) }}</span>
            <div class="dash-rate-bar"><span style="width:{{ min(100, $stats['orders_pending'] * 5) }}%"></span></div>
        </div>
    </div>
</div>

<div class="dash-grid dash-row-stats" style="margin-top:1rem;">
    <div class="dash-mini-stat">
        <div class="dash-mini-stat__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
        </div>
        <div>
            <strong>{{ number_format($stats['catalog_courses']) }}</strong>
            <span><a href="{{ route('admin.catalog-courses') }}" class="dash-inline-link">دورات الكatalog</a></span>
        </div>
    </div>
    <div class="dash-mini-stat">
        <div class="dash-mini-stat__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <div>
            <strong>{{ number_format($stats['batches_active']) }}</strong>
            <span><a href="{{ route('admin.batches') }}" class="dash-inline-link">الدفعات الدراسية</a></span>
        </div>
    </div>
    <div class="dash-mini-stat">
        <div class="dash-mini-stat__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <strong>{{ number_format($stats['users_total']) }}</strong>
            <span><a href="{{ route('admin.users') }}" class="dash-inline-link">المستخدمون</a></span>
        </div>
    </div>
    <div class="dash-mini-stat">
        <div class="dash-mini-stat__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div>
            <strong>{{ number_format($stats['revenue_total'], 0) }} <small>ر.س</small></strong>
            <span><a href="{{ route('admin.financial') }}" class="dash-inline-link">إجمالي الطلبات</a></span>
        </div>
    </div>
</div>

<div class="dash-grid dash-row-charts" style="margin-top:1rem;">
    <div class="dash-panel">
        <h3 class="dash-panel__title">ملخص التشغيل</h3>
        <ul class="dash-hbar-list">
            <li>
                <div class="dash-hbar-head"><span>دورات منشورة</span><span>{{ $stats['catalog_courses'] }}</span></div>
                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:100%"></div></div>
            </li>
            <li>
                <div class="dash-hbar-head"><span>طلبات شراء</span><span>{{ $stats['orders_total'] }}</span></div>
                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:{{ $stats['orders_total'] ? 80 : 0 }}%"></div></div>
            </li>
            <li>
                <div class="dash-hbar-head"><span>إيراد محصّل</span><span>{{ number_format($stats['revenue_collected'], 0) }} ر.س</span></div>
                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:{{ $stats['revenue_total'] > 0 ? min(100, round(($stats['revenue_collected'] / $stats['revenue_total']) * 100)) : 0 }}%"></div></div>
            </li>
        </ul>
    </div>
    <div class="dash-panel">
        <h3 class="dash-panel__title">روابط سريعة</h3>
        <div class="dash-staff-grid">
            <a href="{{ route('admin.settings') }}" class="dash-kpi" style="text-decoration:none;color:inherit"><strong>⚙</strong><span>إعدادات المنصة</span></a>
            <a href="{{ route('admin.orders') }}" class="dash-kpi" style="text-decoration:none;color:inherit"><strong>🛒</strong><span>الطلبات</span></a>
            <a href="{{ route('admin.programs') }}" class="dash-kpi" style="text-decoration:none;color:inherit"><strong>📚</strong><span>البرامج</span></a>
            <a href="{{ route('admin.payment-settings') }}" class="dash-kpi" style="text-decoration:none;color:inherit"><strong>💳</strong><span>طرق الدفع</span></a>
        </div>
    </div>
</div>

@include('partials.admin.dashboard-end')
