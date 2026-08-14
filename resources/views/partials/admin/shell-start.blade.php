@php
    $shellLayout = $shellLayout ?? 'app';
    $shellSidebarActive = $shellSidebarActive ?? '';
    $shellActiveHeader = $shellActiveHeader ?? '';
    $dashSubnav = $dashSubnav ?? null;
    $shellHomeRoute = auth()->user()?->canAdmin('dashboard.view') ? route('admin.dashboard') : route('admin.crm');
@endphp

<div class="admin-app admin-app--dashboard">
    <div class="admin-sidebar-backdrop" id="admin-sidebar-backdrop" hidden></div>
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="القائمة الجانبية" wire:ignore>
        <div class="admin-sidebar__brand">
            <button type="button" class="admin-sidebar__close" id="admin-sidebar-close" aria-label="إغلاق القائمة">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
            <a href="{{ $shellHomeRoute }}" class="admin-sidebar__logo-link">
                <img
                    src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}"
                    class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}"
                    alt="{{ \App\Models\PlatformSetting::get('platform_name_ar', 'منصة مركز التعلم المستمر') }}"
                >
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
        @if ($shellLayout === 'app')
            <header class="admin-header admin-header--app">
                <button type="button" class="admin-sidebar-toggle" id="admin-sidebar-toggle" aria-label="فتح القائمة">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <button type="button" class="admin-sidebar-collapse" id="admin-sidebar-collapse" aria-label="طي القائمة الجانبية" aria-pressed="false" title="طي القائمة الجانبية">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M15 18l-6-6 6-6"/><path d="M21 4v16"/></svg>
                </button>
                <nav class="admin-breadcrumb" id="admin-breadcrumb" aria-label="مسار التنقل">
                    @if (! empty($shellBreadcrumb))
                        @foreach ($shellBreadcrumb as $i => $crumb)
                            @if (! empty($crumb['href']) && $i < count($shellBreadcrumb) - 1)
                                <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>
                            @else
                                <span class="admin-breadcrumb__current" aria-current="page">{{ $crumb['label'] }}</span>
                            @endif
                        @endforeach
                    @endif
                </nav>
                <nav class="admin-header__links admin-header__links--compact" aria-label="روابط عليا">
                    @canAdmin('dashboard.view')<a href="{{ route('admin.dashboard') }}">الرئيسية</a>@endcanAdmin
                    @canAdmin('finance.view')<a href="{{ route('admin.financial') }}">الإحصائيات</a>@endcanAdmin
                    @canAdmin('settings.view')<a href="{{ route('admin.settings') }}" @class(['is-active' => ($shellActiveHeader ?? '') === 'settings'])>الإعدادات</a>@endcanAdmin
                </nav>
                <div class="admin-header__tools">
                    <livewire:shared.admin-global-search wire:key="admin-global-search-app" />
                    @auth
                        <livewire:shared.notification-bell panel="admin" wire:key="admin-notif-bell" />
                    @endauth
                    <div class="admin-user-pill">
                        <div class="admin-avatar" id="admin-avatar">{{ mb_substr(auth()->user()?->displayName() ?? 'م', 0, 1) }}</div>
                        <span id="admin-user-name">{{ auth()->user()?->displayName() ?? 'مسؤول المنصة' }}</span>
                    </div>
                    <button type="button" class="admin-btn-outline admin-header__logout-app" id="admin-logout" aria-label="خروج">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        <span class="admin-logout-label">خروج</span>
                    </button>
                </div>
            </header>

            <div class="admin-content admin-content--app" data-admin-sidebar-active="{{ $shellSidebarActive }}">
        @else
            <header class="admin-header admin-header--dashboard">
                <div class="admin-header__cluster admin-header__cluster--start">
                    <button type="button" class="admin-sidebar-toggle" id="admin-sidebar-toggle" aria-label="فتح القائمة">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button type="button" class="admin-sidebar-collapse" id="admin-sidebar-collapse" aria-label="طي القائمة الجانبية" aria-pressed="false" title="طي القائمة الجانبية">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M15 18l-6-6 6-6"/><path d="M21 4v16"/></svg>
                    </button>
                    <div class="admin-user-pill">
                        <div class="admin-avatar" id="admin-avatar" aria-hidden="true">{{ mb_substr(auth()->user()?->displayName() ?? 'م', 0, 1) }}</div>
                        <div class="admin-user-pill__meta">
                            <span class="admin-user-pill__name" id="admin-user-name">{{ auth()->user()?->displayName() ?? 'مسؤول المنصة' }}</span>
                            <span class="admin-user-pill__role">لوحة التحكم</span>
                        </div>
                    </div>
                    @auth
                        <livewire:shared.notification-bell panel="admin" wire:key="admin-notif-bell-dash" />
                    @endauth
                </div>

                <div class="admin-header__cluster admin-header__cluster--search">
                    <livewire:shared.admin-global-search wire:key="admin-global-search-dash" />
                </div>

                <div class="admin-header__cluster admin-header__cluster--end">
                    <nav class="admin-header__links admin-header__links--pills" aria-label="روابط عليا">
                        @canAdmin('dashboard.view')<a href="{{ route('admin.dashboard') }}" @class(['is-active' => $shellActiveHeader === 'home'])>الرئيسية</a>@endcanAdmin
                        @canAdmin('finance.view')<a href="{{ route('admin.financial') }}" @class(['is-active' => $shellActiveHeader === 'stats'])>الإحصائيات</a>@endcanAdmin
                        @canAdmin('settings.view')<a href="{{ route('admin.settings') }}" @class(['is-active' => $shellActiveHeader === 'settings'])>الإعدادات</a>@endcanAdmin
                    </nav>
                    <button type="button" class="admin-header__logout" id="admin-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        <span>خروج</span>
                    </button>
                </div>
            </header>

            <nav class="admin-subnav" aria-label="أقسام لوحة المؤشرات">
                @include('partials.admin.subnav', ['activeSubnav' => $dashSubnav ?? 'home'])
            </nav>

            <div class="admin-content admin-content--dashboard"
                data-admin-sidebar-active="{{ $shellSidebarActive }}"
                @if($dashSubnav) data-admin-subnav="{{ $dashSubnav }}" @endif>
        @endif
