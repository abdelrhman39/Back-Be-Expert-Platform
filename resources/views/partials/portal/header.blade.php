@php($locale = app()->getLocale())
<header class="portal-header"><div class="portal-header__inner">
            <a class="portal-logo" href="{{ route('home', ['locale' => $locale]) }}">
                <img src="{{ static_asset('assets/vendor/images/site-favicon.png') }}" alt="" width="48" height="48">
                <span>{{ platform_name() }}</span>
            </a>
            <nav class="portal-nav portal-nav--main" aria-label="التنقل الرئيسي">
                <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                <a href="{{ route('home', ['locale' => $locale]) }}#about">من نحن</a>
                <a href="{{ route('courses.index', ['locale' => $locale]) }}">برامج الدبلومات والدورات</a>
                <a href="{{ route('contact', ['locale' => $locale]) }}">إفادة العمل</a>
                <a href="{{ legacy_page('ar/support/contact/index.html') }}">تواصل معنا</a>
            </nav>
            <div class="portal-actions align-items-center gap-2">
                <a class="small text-decoration-none" href="{{ legacy_page('en/login/index.html') }}" hreflang="en">English</a>
                <a class="portal-btn-outline is-active" href="{{ route('home', ['locale' => $locale]) }}">تسجيل الدخول</a>
                <a class="portal-btn-solid" href="{{ legacy_page('ar/register/index.html') }}">تسجيل جديد</a>
            </div>
        </div></header>