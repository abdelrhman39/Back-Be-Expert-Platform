<?php

use App\Services\PlatformAnalyticsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'مؤشرات المنصة',
    'adminPageDesc' => 'الزيارات والدخول والتسجيل والتوزيع الجغرافي',
    'adminLayout' => 'dashboard',
])]
#[Title('مؤشرات المنصة | لوحة التحكم')]
class extends Component
{
    public int $days = 30;
    public array $analytics = [];

    public function mount(PlatformAnalyticsService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('analytics.view'), 403);
        $this->analytics = $service->dashboard($this->days);
    }

    public function setDays(int $days, PlatformAnalyticsService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('analytics.view'), 403);
        $this->days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $this->analytics = $service->dashboard($this->days);
        $this->dispatch('platform-analytics-updated', payload: $this->chartPayload());
    }

    private function chartPayload(): array
    {
        return [
            'series' => $this->analytics['series'],
            'devices' => $this->analytics['devices'],
            'countries' => $this->analytics['countries'],
        ];
    }
};
?>

@include('partials.admin.dashboard-start', [
    'dashSubnav' => 'platform-analytics',
    'dashHeader' => 'stats',
    'dashSidebarActive' => route('admin.dashboard'),
])

@php
    $kpis = $analytics['kpis'];
    $periodLabels = [7 => '7 أيام', 30 => '30 يوماً', 90 => '90 يوماً'];
@endphp

<div class="platform-analytics">
    <section class="analytics-hero">
        <div>
            <span class="analytics-hero__eyebrow">التحليلات الرقمية</span>
            <h1>مؤشرات المنصة</h1>
            <p>رؤية موحّدة لحركة الزوار، الجلسات، تسجيلات الدخول، الحسابات الجديدة، والمواقع الأكثر نشاطاً.</p>
            <span class="analytics-geo-status">
                <i class="fa-solid fa-location-dot"></i>
                {{ config('analytics.geo_provider') === 'none' ? 'التحديد الجغرافي معطّل' : 'مصدر الموقع: '.config('analytics.geo_provider') }}
            </span>
        </div>
        <div class="analytics-period" aria-label="الفترة الزمنية">
            @foreach ($periodLabels as $value => $label)
                <button type="button" wire:click="setDays({{ $value }})" @class(['is-active' => $days === $value])>{{ $label }}</button>
            @endforeach
        </div>
    </section>

    <section class="analytics-today">
        <span><i class="fa-solid fa-bolt"></i> اليوم حتى الآن</span>
        <div><strong>{{ number_format($analytics['today']['page_views']) }}</strong><small>مشاهدة</small></div>
        <div><strong>{{ number_format($analytics['today']['visits']) }}</strong><small>زيارة</small></div>
        <div><strong>{{ number_format($analytics['today']['logins']) }}</strong><small>دخول</small></div>
        <div><strong>{{ number_format($analytics['today']['registrations']) }}</strong><small>تسجيل</small></div>
    </section>

    <section class="analytics-kpis">
        @foreach ([
            ['key' => 'page_views', 'label' => 'مشاهدات الصفحات', 'icon' => 'fa-eye', 'suffix' => ''],
            ['key' => 'visits', 'label' => 'إجمالي الزيارات', 'icon' => 'fa-arrow-pointer', 'suffix' => ''],
            ['key' => 'unique_visitors', 'label' => 'الزوار الفريدون', 'icon' => 'fa-users', 'suffix' => ''],
            ['key' => 'logins', 'label' => 'تسجيلات الدخول', 'icon' => 'fa-right-to-bracket', 'suffix' => ''],
            ['key' => 'registrations', 'label' => 'الحسابات الجديدة', 'icon' => 'fa-user-plus', 'suffix' => ''],
            ['key' => 'conversion', 'label' => 'تحويل الزائر إلى مسجل', 'icon' => 'fa-chart-line', 'suffix' => '%'],
        ] as $card)
            @php($metric = $kpis[$card['key']])
            <article class="analytics-kpi">
                <span class="analytics-kpi__icon"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                <div>
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ number_format($metric['value'], $card['suffix'] ? 1 : 0) }}{{ $card['suffix'] }}</strong>
                    @if ($card['key'] !== 'conversion')
                        <small @class(['is-up' => $metric['change'] > 0, 'is-down' => $metric['change'] < 0])>
                            <i class="fa-solid {{ $metric['change'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            {{ abs($metric['change']) }}% عن الفترة السابقة
                        </small>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <section class="analytics-chart-grid">
        <article class="admin-crud-card analytics-chart analytics-chart--wide">
            <div class="admin-crud-card__head"><h2>حركة الزيارات والمشاهدات</h2><p class="admin-crud-card__meta">التغير اليومي خلال الفترة المحددة</p></div>
            <div class="analytics-chart__canvas" wire:ignore><canvas id="platformTrafficChart"></canvas></div>
        </article>
        <article class="admin-crud-card analytics-chart">
            <div class="admin-crud-card__head"><h2>الدخول والتسجيل</h2><p class="admin-crud-card__meta">مقارنة النشاط بالحسابات الجديدة</p></div>
            <div class="analytics-chart__canvas" wire:ignore><canvas id="platformEngagementChart"></canvas></div>
        </article>
        <article class="admin-crud-card analytics-chart">
            <div class="admin-crud-card__head"><h2>أنواع الأجهزة</h2><p class="admin-crud-card__meta">توزيع مشاهدات الصفحات</p></div>
            <div class="analytics-chart__canvas analytics-chart__canvas--donut" wire:ignore><canvas id="platformDevicesChart"></canvas></div>
        </article>
    </section>

    <section class="analytics-chart-grid">
        <article class="admin-crud-card analytics-chart analytics-chart--wide">
            <div class="admin-crud-card__head"><h2>الدول الأكثر زيارة</h2><p class="admin-crud-card__meta">حسب عدد الجلسات المميزة</p></div>
            @if ($analytics['countries'])
                <div class="analytics-chart__canvas" wire:ignore><canvas id="platformCountriesChart"></canvas></div>
            @else
                <div class="analytics-empty"><i class="fa-solid fa-earth-asia"></i><strong>بيانات الموقع ستظهر عند توفرها</strong><p>تُقرأ الدولة والمنطقة والمدينة من ترويسات Cloudflare أو Vercel أو CloudFront الموثوقة، دون حفظ عنوان IP الخام.</p></div>
            @endif
        </article>
        <article class="admin-crud-card analytics-ranking">
            <div class="admin-crud-card__head"><h2>المناطق الأعلى</h2></div>
            @forelse ($analytics['regions'] as $index => $row)
                <div class="analytics-rank"><b>{{ $index + 1 }}</b><span>{{ $row['label'] }}</span><strong>{{ number_format($row['visits']) }}</strong></div>
            @empty
                <p class="admin-detail-empty">لا توجد بيانات مناطق بعد.</p>
            @endforelse
        </article>
        <article class="admin-crud-card analytics-ranking">
            <div class="admin-crud-card__head"><h2>المدن الأعلى</h2></div>
            @forelse ($analytics['cities'] as $index => $row)
                <div class="analytics-rank"><b>{{ $index + 1 }}</b><span>{{ $row['label'] }}</span><strong>{{ number_format($row['visits']) }}</strong></div>
            @empty
                <p class="admin-detail-empty">لا توجد بيانات مدن بعد.</p>
            @endforelse
        </article>
    </section>

    <section class="analytics-table-grid">
        <article class="admin-crud-card">
            <div class="admin-crud-card__head"><h2>الصفحات الأكثر مشاهدة</h2></div>
            <div class="analytics-list">
                @forelse ($analytics['top_pages'] as $row)
                    <div><span dir="ltr">{{ $row['label'] }}</span><strong>{{ number_format($row['views']) }}</strong></div>
                @empty
                    <p class="admin-detail-empty">سيبدأ ظهور الصفحات بعد تسجيل الزيارات.</p>
                @endforelse
            </div>
        </article>
        <article class="admin-crud-card">
            <div class="admin-crud-card__head"><h2>مصادر الإحالة</h2></div>
            <div class="analytics-list">
                @forelse ($analytics['referrers'] as $row)
                    <div><span dir="ltr">{{ $row['label'] }}</span><strong>{{ number_format($row['visits']) }}</strong></div>
                @empty
                    <p class="admin-detail-empty">الزيارات المباشرة أو لا توجد إحالات بعد.</p>
                @endforelse
            </div>
        </article>
        <article class="admin-crud-card">
            <div class="admin-crud-card__head"><h2>المتصفحات</h2></div>
            <div class="analytics-list">
                @forelse ($analytics['browsers'] as $row)
                    <div><span>{{ $row['label'] }}</span><strong>{{ number_format($row['views']) }}</strong></div>
                @empty
                    <p class="admin-detail-empty">لا توجد بيانات بعد.</p>
                @endforelse
            </div>
        </article>
    </section>
</div>

@include('partials.admin.dashboard-end')

<style>
    .platform-analytics{display:flex;flex-direction:column;gap:1rem}.analytics-hero{display:flex;align-items:center;justify-content:space-between;gap:1.25rem;padding:1.35rem 1.5rem;border-radius:18px;background:radial-gradient(35rem 18rem at 105% -40%,rgba(59,130,246,.3),transparent 60%),linear-gradient(135deg,#0c3b28,#14532d 58%,#166534);color:#fff;box-shadow:0 16px 38px rgba(12,59,40,.24)}.analytics-hero__eyebrow{color:#86efac;font-size:.66rem;font-weight:900}.analytics-hero h1{margin:.2rem 0 .35rem;color:#fff;font-size:1.45rem}.analytics-hero p{margin:0;max-width:46rem;color:rgba(255,255,255,.82);font-size:.76rem;line-height:1.8}.analytics-geo-status{display:inline-flex;align-items:center;gap:.3rem;margin-top:.55rem;padding:.25rem .5rem;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.08);color:#d1fae5;font-size:.6rem;font-weight:800}.analytics-period{display:flex;flex-wrap:wrap;gap:.35rem;padding:.35rem;border:1px solid rgba(255,255,255,.18);border-radius:12px;background:rgba(255,255,255,.08)}.analytics-period button{padding:.45rem .65rem;border:0;border-radius:8px;background:transparent;color:#d1fae5;font:800 .68rem inherit;cursor:pointer}.analytics-period button.is-active{background:#fff;color:#166534;box-shadow:0 3px 10px rgba(0,0,0,.14)}.analytics-today{display:flex;align-items:center;gap:1rem;padding:.7rem 1rem;border:1px solid #dbeafe;border-radius:13px;background:#eff6ff}.analytics-today>span{display:flex;align-items:center;gap:.35rem;margin-inline-end:auto;color:#1e40af;font-size:.7rem;font-weight:900}.analytics-today>div{display:flex;align-items:baseline;gap:.25rem}.analytics-today strong{color:#0f172a;font-size:.88rem}.analytics-today small{color:#64748b;font-size:.6rem}.analytics-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.7rem}.analytics-kpi{display:flex;align-items:flex-start;gap:.65rem;padding:.85rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.analytics-kpi__icon{display:grid;place-items:center;flex:0 0 auto;width:2.35rem;height:2.35rem;border-radius:11px;background:#ecfdf5;color:#166534}.analytics-kpi>div{display:grid;gap:.12rem;min-width:0}.analytics-kpi>div>span{color:#64748b;font-size:.62rem;font-weight:700}.analytics-kpi strong{color:#0f172a;font-size:1.05rem}.analytics-kpi small{color:#94a3b8;font-size:.55rem;white-space:nowrap}.analytics-kpi small.is-up{color:#15803d}.analytics-kpi small.is-down{color:#b91c1c}.analytics-chart-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.analytics-chart-grid>.analytics-chart--wide{grid-column:1/-1}.analytics-chart,.analytics-ranking,.analytics-table-grid>.admin-crud-card{margin:0}.analytics-chart__canvas{position:relative;height:18rem;padding:1rem}.analytics-chart__canvas--donut{height:16rem}.analytics-empty{display:grid;place-items:center;text-align:center;min-height:15rem;padding:1rem}.analytics-empty i{margin-bottom:.5rem;color:#94a3b8;font-size:2rem}.analytics-empty strong{color:#334155;font-size:.85rem}.analytics-empty p{max-width:28rem;margin:.35rem 0 0;color:#64748b;font-size:.68rem;line-height:1.7}.analytics-rank{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:.6rem;padding:.62rem .85rem;border-bottom:1px solid #f1f5f9}.analytics-rank:last-child{border-bottom:0}.analytics-rank b{display:grid;place-items:center;width:1.5rem;height:1.5rem;border-radius:7px;background:#f1f5f9;color:#64748b;font-size:.6rem}.analytics-rank span{color:#334155;font-size:.72rem}.analytics-rank strong{color:#166534;font-size:.72rem}.analytics-table-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.analytics-list>div{display:flex;align-items:center;justify-content:space-between;gap:.7rem;padding:.62rem .85rem;border-bottom:1px solid #f1f5f9}.analytics-list>div:last-child{border-bottom:0}.analytics-list span{overflow:hidden;color:#475569;font-size:.68rem;text-overflow:ellipsis;white-space:nowrap}.analytics-list strong{color:#0f172a;font-size:.7rem}@media(max-width:1200px){.analytics-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:800px){.analytics-hero{align-items:flex-start;flex-direction:column}.analytics-today{flex-wrap:wrap}.analytics-today>span{width:100%}.analytics-chart-grid,.analytics-table-grid{grid-template-columns:1fr}.analytics-chart-grid>.analytics-chart--wide{grid-column:auto}}@media(max-width:560px){.analytics-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.analytics-period{width:100%}.analytics-period button{flex:1}.analytics-today{gap:.65rem}.analytics-chart__canvas{height:15rem}}
</style>

@script
<script>
    const analyticsCharts = {};
    const colors = { green: '#166534', lightGreen: '#22c55e', blue: '#2563eb', gold: '#b8943f', slate: '#64748b' };

    const destroyChart = (key) => {
        if (analyticsCharts[key]) analyticsCharts[key].destroy();
    };

    const renderPlatformAnalytics = (payload) => {
        if (typeof Chart === 'undefined' || !payload) return;
        const series = payload.series || {};

        const traffic = document.getElementById('platformTrafficChart');
        if (traffic) {
            destroyChart('traffic');
            analyticsCharts.traffic = new Chart(traffic, {
                type: 'line',
                data: { labels: series.labels || [], datasets: [
                    { label: 'مشاهدات الصفحات', data: series.pageViews || [], borderColor: colors.green, backgroundColor: 'rgba(22,101,52,.12)', fill: true, tension: .35 },
                    { label: 'الزيارات', data: series.visits || [], borderColor: colors.blue, backgroundColor: 'rgba(37,99,235,.08)', fill: true, tension: .35 },
                ]},
                options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }

        const engagement = document.getElementById('platformEngagementChart');
        if (engagement) {
            destroyChart('engagement');
            analyticsCharts.engagement = new Chart(engagement, {
                type: 'bar',
                data: { labels: series.labels || [], datasets: [
                    { label: 'تسجيلات الدخول', data: series.logins || [], backgroundColor: colors.blue, borderRadius: 5 },
                    { label: 'الحسابات الجديدة', data: series.registrations || [], backgroundColor: colors.gold, borderRadius: 5 },
                ]},
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }

        const devices = document.getElementById('platformDevicesChart');
        if (devices) {
            destroyChart('devices');
            const rows = payload.devices || [];
            analyticsCharts.devices = new Chart(devices, {
                type: 'doughnut',
                data: { labels: rows.map(row => ({ desktop: 'حاسوب', mobile: 'جوال', tablet: 'جهاز لوحي' }[row.label] || row.label)), datasets: [{ data: rows.map(row => row.views), backgroundColor: [colors.green, colors.blue, colors.gold, colors.slate], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '66%', plugins: { legend: { position: 'bottom' } } }
            });
        }

        const countries = document.getElementById('platformCountriesChart');
        if (countries) {
            destroyChart('countries');
            const rows = payload.countries || [];
            analyticsCharts.countries = new Chart(countries, {
                type: 'bar',
                data: { labels: rows.map(row => row.label), datasets: [{ label: 'الزيارات', data: rows.map(row => row.visits), backgroundColor: colors.green, borderRadius: 6 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }
    };

    renderPlatformAnalytics(@js([
        'series' => $analytics['series'],
        'devices' => $analytics['devices'],
        'countries' => $analytics['countries'],
    ]));

    $wire.on('platform-analytics-updated', (event) => {
        requestAnimationFrame(() => renderPlatformAnalytics(event.payload || event));
    });
</script>
@endscript
