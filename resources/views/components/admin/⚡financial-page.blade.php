<?php

use App\Services\AdminStatsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'التحليلات المالية',
    'adminPageDesc' => 'إيرادات، مدفوعات، وطرق السداد',
    'adminLayout' => 'dashboard',
])]
#[Title('التحليلات المالية | لوحة التحكم')]
class extends Component
{
    public array $stats = [];

    public function mount(AdminStatsService $stats): void
    {
        $this->stats = $stats->financial();
    }
};
?>

@include('partials.admin.dashboard-start', ['dashSubnav' => 'financial', 'dashSidebarActive' => route('admin.financial')])

<div class="dash-grid dash-fin-top">
    <div class="dash-hero-card dash-hero-card--finance">
        <span>صافي الإيرادات</span>
        <strong>{{ number_format($stats['revenue_collected'], 0) }} <small style="font-size:0.55em;font-weight:600">ر.س</small></strong>
        <p class="dash-hero-card__sub">الإيراد قبل التحصيل: {{ number_format($stats['revenue_total'], 0) }} ر.س — معلّق: {{ number_format($stats['revenue_pending'] ?? 0, 0) }} ر.س</p>
    </div>
    <div class="dash-fin-mini-stack">
        <div class="dash-fin-mini"><span>عدد الطلبات</span><strong>{{ $stats['orders_total'] }}</strong></div>
        <div class="dash-fin-mini"><span>طلبات مدفوعة</span><strong>{{ $stats['orders_paid_count'] ?? 0 }}</strong></div>
        <div class="dash-fin-mini"><span>المشترون</span><strong>{{ $stats['unique_buyers'] ?? 0 }}</strong></div>
    </div>
</div>

<div class="dash-panel" style="margin-top:1rem;">
    <div class="dash-section-head">
        <h3>الإيرادات والمدفوعات</h3>
        <p>نظرة على حركة السداد من طلبات المنصة</p>
    </div>
    <div class="dash-revenue-grid">
        <div class="dash-revenue-card">
            <div class="dash-revenue-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
            <div>
                <span class="label">إجمالي الطلبات</span>
                <strong>{{ number_format($stats['revenue_total'], 0) }} ر.س</strong>
                <span class="hint">{{ $stats['orders_total'] }} عملية</span>
            </div>
        </div>
        <div class="dash-revenue-card">
            <div class="dash-revenue-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div>
            <div>
                <span class="label">المحصّل</span>
                <strong>{{ number_format($stats['revenue_collected'], 0) }} ر.س</strong>
                <span class="hint">طلبات مدفوعة</span>
            </div>
        </div>
        <div class="dash-revenue-card">
            <div class="dash-revenue-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg></div>
            <div>
                <span class="label">بانتظار الدفع</span>
                <strong>{{ number_format($stats['revenue_pending'] ?? 0, 0) }} ر.س</strong>
                <span class="hint">{{ $stats['orders_pending'] }} طلب</span>
            </div>
        </div>
    </div>
</div>

<div class="dash-panel" style="margin-top:1rem;">
    <div class="dash-section-head">
        <h3>توزيع طرق الدفع</h3>
        <p>معرفة كل قناة دفع من إجمالي الطلبات</p>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead><tr><th>الطريقة</th><th>عدد الطلبات</th><th>الإجمالي</th></tr></thead>
            <tbody>
                @forelse ($stats['payment_methods'] ?? [] as $row)
                    <tr>
                        <td>{{ \App\Support\PaymentMethods::label($row->payment_method ?? 'unknown') }}</td>
                        <td>{{ $row->count }}</td>
                        <td>{{ number_format((float) $row->total, 0) }} ر.س</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--sa-muted)">لا توجد بيانات مالية بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-filter-actions" style="margin-top:1rem;">
        <a href="{{ route('admin.orders') }}" class="admin-btn-primary admin-btn-primary--sm">عرض كل الطلبات</a>
        <a href="{{ route('admin.payment-settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">إعدادات الدفع</a>
    </div>
</div>

@include('partials.admin.dashboard-end')
