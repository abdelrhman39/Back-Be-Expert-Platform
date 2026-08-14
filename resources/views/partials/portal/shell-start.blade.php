@php
    use App\Support\PortalNavigation;

    $locale = app()->getLocale();
    $portalActive = $portalActive ?? 'profile';
    $portalTitle = $portalTitle ?? 'لوحة التحكم';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/portal-dashboard.css') }}?v=11">
@endpush

<div class="portal-root">
    <form id="logout-form" action="{{ route('logout', ['locale' => $locale]) }}" method="POST" class="d-none">@csrf</form>
    @include('partials.portal.impersonation-banner')
    <div class="portal-drawer-overlay" aria-hidden="true"></div>

    <header class="header new-header profile-header">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-lg header-nav">
                <div class="navbar-header">
                    <button
                        type="button"
                        class="portal-drawer-toggle"
                        aria-controls="portal-drawer"
                        aria-expanded="false"
                        aria-label="{{ app()->getLocale() === 'en' ? 'Open menu' : 'فتح القائمة' }}"
                    >
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                    </button>
                    <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo">
                        @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                            <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="">
                        @endif
                        @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_SECONDARY))
                            <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_SECONDARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_SECONDARY) }}" alt="">
                        @endif
                    </a>
                </div>
                @include('partials.header-toolbar')
            </nav>
        </div>
    </header>

    <div class="new-profile-wrapper">
        <div id="portal-drawer" class="new-sidebar theiaStickySidebar">
            <div class="theiaStickySidebar">
                @include('partials.portal.sidebar', ['portalActive' => $portalActive])
            </div>
        </div>

        <div class="dashboard-header">
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('profile', ['locale' => $locale]) }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $portalTitle }}</li>
                </ol>
            </nav>
        </div>

        <div class="new-design-content account-stats">
            <div class="container portal-page-content">
                @include('partials.portal.lecture-alert')
                @include('partials.portal.installment-suspend-banner')
