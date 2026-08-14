@php
    use App\Support\InstructorNavigation;

    $locale = app()->getLocale();
    $user = auth()->user();
    $instructorActive = $instructorActive ?? 'dashboard';
    $instructorTitle = $instructorTitle ?? 'لوحة المدرب';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/portal-dashboard.css') }}?v=10">
@endpush

<div class="portal-root portal-root--instructor">
    @include('partials.instructor.impersonation-banner')
    <form id="logout-form" action="{{ route('logout', ['locale' => $locale]) }}" method="POST" class="d-none">@csrf</form>
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
                <ul class="nav header-navbar-rht">
                    <li class="nav-item">
                        <a hreflang="{{ $locale === 'ar' ? 'en' : 'ar' }}" class="btn px-2" href="{{ url('/'.($locale === 'ar' ? 'en' : 'ar').'/instructor') }}">
                            {{ $locale === 'ar' ? 'EN' : 'AR' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <livewire:shared.notification-bell panel="instructor" wire:key="instructor-notif-bell" />
                    </li>
                    <li class="nav-item dropdowns has-arrow logged-item">
                        <a href="javascript:void(0)" class="nav-link toggle">
                            <span class="log-user dropdown-toggle">
                                <span class="users-img">
                                    <span class="portal-avatar rounded-circle">{{ $user?->initials() }}</span>
                                </span>
                                <div class="d-flex flex-column">
                                    <span class="user-text">{{ $user?->displayName() }}</span>
                                    <span class="user-role">مدرب</span>
                                </div>
                            </span>
                        </a>
                        <div class="dropdown-menu list-group">
                            <a class="dropdown-item" href="{{ route('instructor.dashboard', ['locale' => $locale]) }}">لوحة المدرب</a>
                            <a class="dropdown-item" href="{{ route('instructor.settings', ['locale' => $locale]) }}">الإعدادات</a>
                            <a class="dropdown-item log-out" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">تسجيل الخروج</a>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="new-profile-wrapper">
        <div id="portal-drawer" class="new-sidebar theiaStickySidebar">
            <div class="theiaStickySidebar">
                @include('partials.instructor.sidebar', ['instructorActive' => $instructorActive])
            </div>
        </div>

        <div class="dashboard-header">
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard', ['locale' => $locale]) }}">لوحة التحكم</a></li>
                    @if (! empty($instructorBreadcrumb))
                        @foreach ($instructorBreadcrumb as $crumb)
                            <li class="breadcrumb-item @if(empty($crumb['href'])) active @endif" @if(empty($crumb['href'])) aria-current="page" @endif>
                                @if (! empty($crumb['href']))
                                    <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
                                @else
                                    {{ $crumb['label'] }}
                                @endif
                            </li>
                        @endforeach
                    @else
                        <li class="breadcrumb-item active" aria-current="page">{{ $instructorTitle }}</li>
                    @endif
                </ol>
            </nav>
        </div>

        <div class="new-design-content account-stats">
            <div class="container portal-page-content">
