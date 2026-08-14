@php
    $locale = app()->getLocale();
    $headerMenuItems = app(\App\Services\CmsMenuService::class)->tree('header_main');
@endphp
<header @class(['header', 'new-header', 'fixed', 'profile-header', 'site-header--auth' => portal_authenticated()])>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg header-nav">
            <div class="navbar-header">
                <a id="mobile_btn" href="javascript:void(0);">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>
                <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo">
                    @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="">
                    @endif
                    @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_SECONDARY))
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_SECONDARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_SECONDARY) }}" alt="">
                    @endif
                </a>
                <a href="{{ route('home', ['locale' => $locale]) }}" class="navbar-brand logo-small">
                    @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="">
                    @endif
                </a>
            </div>
            <div class="main-menu-wrapper">
                <div class="menu-header">
                    <a href="{{ route('home', ['locale' => $locale]) }}" class="menu-logo">
                        @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                            <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="">
                        @endif
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);"> <i class="fas fa-times"></i></a>
                </div>
                <ul class="main-nav navbar-nav">
                    @if ($headerMenuItems->isNotEmpty())
                        @include('partials.cms.menu-items', ['items' => $headerMenuItems])
                    @else
                        <li><a href="{{ route('home', ['locale' => $locale]) }}" class="nav-link">الرئيسية</a></li>
                        <li><a href="{{ route('about', ['locale' => $locale]) }}" class="nav-link">عن المنصة</a></li>
                        <li><a href="{{ route('courses.index', ['locale' => $locale]) }}" class="nav-link">البرامج</a></li>
                        <li><a href="{{ route('register', ['locale' => $locale]) }}" class="nav-link">التسجيل</a></li>
                        <li><a href="{{ route('contact', ['locale' => $locale]) }}" class="nav-link">تواصل معنا</a></li>
                    @endif
                    @guest
                        <li class="nav-item responsive-link">
                            <a href="{{ route('login', ['locale' => $locale]) }}" class="nav-link">الدخول</a>
                        </li>
                    @else
                        <li class="nav-item responsive-link">
                            <a href="{{ route('profile', ['locale' => $locale]) }}" class="nav-link">لوحة التحكم</a>
                        </li>
                    @endguest
                </ul>
            </div>

            @include('partials.header-toolbar')
        </nav>
    </div>
</header>
