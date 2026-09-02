@php
    $locale = app()->getLocale();
    $headerMenuItems = app(\App\Services\CmsMenuService::class)->tree('header_main');
@endphp
<header @class(['header', 'new-header', 'fixed', 'profile-header', 'site-header--auth' => portal_authenticated()])>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg header-nav" aria-label="{{ app()->getLocale() === 'en' ? 'Primary' : 'القائمة الرئيسية' }}">
            <div class="navbar-header">
                <a id="mobile_btn" href="javascript:void(0);" aria-controls="site-main-menu" aria-expanded="false">
                    <span class="bar-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                    <span class="visually-hidden">{{ app()->getLocale() === 'en' ? 'Open menu' : 'فتح القائمة' }}</span>
                </a>
                <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo">
                    @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="{{ platform_org() }}">
                    @endif
                    @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_SECONDARY))
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_SECONDARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_SECONDARY) }}" alt="">
                    @endif
                </a>
                <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo-small">
                    @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="{{ platform_org() }}">
                    @endif
                </a>
            </div>
            <div class="main-menu-wrapper" id="site-main-menu">
                <div class="menu-header">
                    <a href="{{ route('home', ['locale' => $locale]) }}" class="menu-logo">
                        @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                            <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="{{ platform_org() }}">
                        @endif
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <i class="fas fa-times" aria-hidden="true"></i>
                        <span class="visually-hidden">{{ app()->getLocale() === 'en' ? 'Close menu' : 'إغلاق القائمة' }}</span>
                    </a>
                </div>
                <ul class="main-nav navbar-nav site-nav">
                    @if ($headerMenuItems->isNotEmpty())
                        @include('partials.cms.menu-items', ['items' => $headerMenuItems])
                    @else
                        <li><a href="{{ route('home', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('home') }}</a></li>
                        <li><a href="{{ route('about', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('about') }}</a></li>
                        <li><a href="{{ route('courses.index', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('programs') }}</a></li>
                        <li><a href="{{ route('register', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('register') }}</a></li>
                        <li><a href="{{ route('contact', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('contact') }}</a></li>
                    @endif
                    @guest
                        <li class="nav-item responsive-link">
                            <a href="{{ route('login', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('login') }}</a>
                        </li>
                    @else
                        <li class="nav-item responsive-link">
                            <a href="{{ route('profile', ['locale' => $locale]) }}" class="nav-link">{{ public_copy('dashboard') }}</a>
                        </li>
                    @endguest
                </ul>
            </div>

            @include('partials.header-toolbar')
        </nav>
    </div>
    <div class="sidebar-overlay"></div>
</header>
