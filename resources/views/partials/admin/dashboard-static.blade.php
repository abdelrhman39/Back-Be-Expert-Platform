<div class="admin-app admin-app--dashboard">
        <div class="admin-sidebar-backdrop" id="admin-sidebar-backdrop" hidden></div>
        <aside class="admin-sidebar" id="admin-sidebar" aria-label="القائمة الجانبية">
            <div class="admin-sidebar__brand">
                <button type="button" class="admin-sidebar__close" id="admin-sidebar-close" aria-label="إغلاق القائمة">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__logo-link">
                    <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="{{ \App\Models\PlatformSetting::get('platform_name_ar', 'منصة مركز التعلم المستمر') }}">
                </a>
                <p class="admin-sidebar__org">{{ \App\Models\PlatformSetting::get('platform_name_ar', 'منصة مركز التعلم المستمر') }}</p>
                <p class="admin-sidebar__org-sub">{{ \App\Models\PlatformSetting::get('platform_org_ar', 'جامعة الامير مقرن') }}</p>
            </div>
            <div class="admin-sidebar__scroll">
                <ul class="admin-side-nav"></ul>
            </div>
            <div class="admin-sidebar__foot">
                <a href="{{ route('home', ['locale' => 'ar']) }}">← الموقع العام</a>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-header">
                <button type="button" class="admin-sidebar-toggle" id="admin-sidebar-toggle" aria-label="فتح القائمة">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="admin-header__start">
                    <div class="admin-user-pill">
                        <div class="admin-avatar" id="admin-avatar">م</div>
                        <span id="admin-user-name">مسؤول المنصة</span>
                    </div>
                    <form class="admin-search" role="search" onsubmit="return false;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
                        <input type="search" placeholder="بحث في اللوحة..." aria-label="بحث">
                    </form>
                </div>
                <nav class="admin-header__links" aria-label="روابط عليا">
                    <a href="{{ route('admin.dashboard') }}" class="is-active">الرئيسية</a>
                    <a href="{{ route('admin.dashboard') }}">الإحصائيات</a>
                    <a href="{{ route('admin.payment-settings') }}">إعدادات الدفع</a>
                    <a href="#">المشروعات</a>
                </nav>
                <button type="button" class="admin-btn-outline" id="admin-logout">تسجيل الخروج</button>
            </header>

            <nav class="admin-subnav" aria-label="أقسام لوحة المؤشرات">
                <ul class="admin-subnav__list"></ul>
            </nav>

            <div class="admin-content admin-content--dashboard">
                <!-- صف 1: إجمالي + معدلات -->
                <div class="dash-grid dash-row-hero">
                    <div class="dash-hero-card">
                        <span>إجمالي المتدربين</span>
                        <strong>2,277</strong>
                    </div>
                    <div class="dash-rates-card">
                        <div class="dash-rate-item">
                            <label>معدل التخرج</label>
                            <span class="value">0%</span>
                            <div class="dash-rate-bar"><span style="width:0%"></span></div>
                        </div>
                        <div class="dash-rate-item">
                            <label>معدل الاحتفاظ</label>
                            <span class="value">96.8%</span>
                            <div class="dash-rate-bar"><span style="width:96.8%"></span></div>
                        </div>
                        <div class="dash-rate-item">
                            <label>معدل الحضور</label>
                            <span class="value">0%</span>
                            <div class="dash-rate-bar"><span style="width:0%"></span></div>
                        </div>
                    </div>
                </div>

                <!-- صف 2: بطاقات ملخص -->
                <div class="dash-grid dash-row-stats" style="margin-top:1rem;">
                    <div class="dash-mini-stat">
                        <div class="dash-mini-stat__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
                        </div>
                        <div>
                            <strong>14</strong>
                            <span><a href="{{ legacy_page('admin/programs.html') }}" class="dash-inline-link">البرامج النشطة</a></span>
                        </div>
                    </div>
                    <div class="dash-mini-stat">
                        <div class="dash-mini-stat__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        </div>
                        <div>
                            <strong>22</strong>
                            <span>الدفعات الأكاديمية</span>
                        </div>
                    </div>
                    <div class="dash-mini-stat">
                        <div class="dash-mini-stat__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                        </div>
                        <div>
                            <strong>1:104</strong>
                            <span>نسبة المتدرب / المدرب</span>
                        </div>
                    </div>
                    <div class="dash-mini-stat">
                        <div class="dash-mini-stat__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                        </div>
                        <div>
                            <strong>2,130</strong>
                            <span>المتدربون النشطون</span>
                        </div>
                    </div>
                </div>

                <!-- صف 3: رسوم بيانية -->
                <div class="dash-grid dash-row-charts" style="margin-top:1rem;">
                    <div class="dash-panel">
                        <h3 class="dash-panel__title">نمو التسجيل بين الفصول الدراسية</h3>
                        <div class="dash-chart-wrap">
                            <canvas id="chart-enrollment" aria-label="رسم نمو التسجيل"></canvas>
                        </div>
                    </div>
                    <div class="dash-panel">
                        <h3 class="dash-panel__title">توزيع الحالة الأكاديمية</h3>
                        <div class="dash-chart-wrap dash-chart-wrap--donut">
                            <canvas id="chart-status" aria-label="رسم توزيع الحالة"></canvas>
                            <div class="dash-donut-center">
                                <strong>2,277</strong>
                                <span>الإجمالي</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- صف 4: البرامج الأكثر تسجيلاً -->
                <div class="dash-grid dash-row-programs" style="margin-top:1rem;">
                    <div class="dash-panel">
                        <h3 class="dash-panel__title">أبرز البرامج بعدد المسجلين</h3>
                        <ul class="dash-hbar-list">
                            <li>
                                <div class="dash-hbar-head"><span>دبلوم الأمن والسلامة</span><span>412</span></div>
                                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:100%"></div></div>
                            </li>
                            <li>
                                <div class="dash-hbar-head"><span>دبلوم إدارة الأعمال</span><span>358</span></div>
                                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:87%"></div></div>
                            </li>
                            <li>
                                <div class="dash-hbar-head"><span>ممارس الذكاء الاصطناعي AWS</span><span>296</span></div>
                                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:72%"></div></div>
                            </li>
                            <li>
                                <div class="dash-hbar-head"><span>شهادة إدارة المشاريع PMP</span><span>241</span></div>
                                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:58%"></div></div>
                            </li>
                            <li>
                                <div class="dash-hbar-head"><span>دبلوم المحاسبة</span><span>198</span></div>
                                <div class="dash-hbar-track"><div class="dash-hbar-fill" style="width:48%"></div></div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- صف 5: مؤشرات المتدربين -->
                <div class="dash-grid dash-row-kpis" style="margin-top:1rem;">
                    <div class="dash-kpi"><strong>152</strong><span>متوسط المتدربين لكل برنامج</span></div>
                    <div class="dash-kpi"><strong>9</strong><span>متوسط المتدربين لكل شعبة</span></div>
                    <div class="dash-kpi"><strong>—</strong><span>متوسط عمر المتدرب</span></div>
                    <div class="dash-kpi"><strong>0</strong><span>المتوقع تخرجهم</span></div>
                    <div class="dash-kpi"><strong>0</strong><span>شهادات قيد المراجعة</span></div>
                    <div class="dash-kpi"><strong>0</strong><span>شهادات معتمدة</span></div>
                    <div class="dash-kpi"><strong>14</strong><span>برامج مفتوحة للتسجيل</span></div>
                    <div class="dash-kpi"><strong>22</strong><span>دفعات نشطة</span></div>
                </div>

                <!-- صف 6: التوزيع حسب الجنس -->
                <div class="dash-grid dash-row-gender" style="margin-top:1rem;">
                    <div class="dash-gender-card">
                        <h4>المتدربون</h4>
                        <div class="dash-gender-legend"><span>ذكر 81%</span><span>أنثى 19%</span></div>
                        <div class="dash-gender-bar"><span class="male" style="width:81%"></span><span class="female" style="width:19%"></span></div>
                        <div class="dash-gender-stats"><span class="male-t">1,833 ذكر</span><span class="female-t">444 أنثى</span></div>
                    </div>
                    <div class="dash-gender-card">
                        <h4>الكادر التدريبي</h4>
                        <div class="dash-gender-legend"><span>ذكر 82%</span><span>أنثى 18%</span></div>
                        <div class="dash-gender-bar"><span class="male" style="width:82%"></span><span class="female" style="width:18%"></span></div>
                        <div class="dash-gender-stats"><span class="male-t">9 ذكر</span><span class="female-t">2 أنثى</span></div>
                    </div>
                    <div class="dash-gender-card">
                        <h4>الموظفون</h4>
                        <div class="dash-gender-legend"><span>ذكر 0%</span><span>أنثى 100%</span></div>
                        <div class="dash-gender-bar"><span class="male" style="width:0%"></span><span class="female" style="width:100%"></span></div>
                        <div class="dash-gender-stats"><span class="male-t">0 ذكر</span><span class="female-t">4 أنثى</span></div>
                    </div>
                </div>

                <!-- صف 7: الكادر والعمليات -->
                <div class="dash-grid dash-row-bottom" style="margin-top:1rem;">
                    <div class="dash-panel">
                        <p class="dash-section-title">الكادر التدريبي</p>
                        <div class="dash-staff-grid">
                            <div class="dash-kpi"><strong>11</strong><span>إجمالي الكادر</span></div>
                            <div class="dash-kpi"><strong>5.05</strong><span>متوسط العبء (مقررات)</span></div>
                            <div class="dash-kpi"><strong>10.32</strong><span>متوسط العبء (ساعات)</span></div>
                        </div>
                    </div>
                    <div class="dash-panel">
                        <p class="dash-section-title">العمليات</p>
                        <div class="dash-ops-grid">
                            <div class="dash-kpi"><strong>4</strong><span>إجمالي الموظفين</span></div>
                            <div class="dash-kpi"><strong>6</strong><span>الوحدات / الأقسام</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>