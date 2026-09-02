<!DOCTYPE html>
<html class="no-js use-domain-a11y" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-identity-theme="{{ \App\Support\IdentityThemes::activeKey() }}" style="--header-height: 85px; --footer-height: 406px;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FAVICON) }}" type="image/x-icon">
    <link href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FAVICON) }}" rel="shortcut icon">

    <meta name="description" content="{{ $metaDescription ?? platform_name().' — '.platform_org() }}">
    <title>{{ $title ?? platform_name().' | '.platform_org() }}</title>

    <!-- all CSS — same order as index.html -->
    <link rel="stylesheet" href="{{ static_asset('assets/all.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/vendor/fonts/google-fonts-local.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/style.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/components.css') }}">
    <link rel="stylesheet" href="{{ static_asset('css/site-enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v=15">
    <link rel="stylesheet" href="{{ asset('css/notification-bell.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/home-hero.css') }}?v=12">
    <link rel="stylesheet" href="{{ asset('css/home-mvg.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/home-catalog-slider.css') }}?v=11">
    <link rel="stylesheet" href="{{ asset('css/home-diplomas.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/home-identity-blocks.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/catalog-public.css') }}?v=7">
    <link rel="stylesheet" href="{{ static_asset('assets/domain-a11y-panel.css') }}">
    @include('partials.platform-theme')
    <link rel="stylesheet" href="{{ asset('css/public-atelier.css') }}?v=16">
    <link rel="stylesheet" href="{{ asset('css/home-features.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/site-footer.css') }}?v=1">
    <style id="theia-sticky-sidebar-stylesheet-TSS">
        .theiaStickySidebar:after {
            content: "";
            display: table;
            clear: both;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>
<body data-aos-easing="ease" data-aos-duration="1200" data-aos-delay="0">
    <!-- <a class="skip-to-content" href="#main-content">تخطي إلى المحتوى</a> -->

    <div class="loader-mainn d-none"><span class="page-loader"></span></div>

    <div id="main-content" class="main-wrapper">
        @if (portal_authenticated())
            <form id="logout-form" action="{{ route('logout', ['locale' => app()->getLocale()]) }}" method="POST" class="d-none">@csrf</form>
        @endif
        @include('partials.header')

        {{ $slot }}
        @include('partials.page-help')

        @include('partials.float-buttons')
        @include('partials.footer')
    </div>

    <script src="{{ static_asset('assets/all.js') }}"></script>
    <script src="{{ static_asset('assets/script.js') }}?v=2"></script>
    <script src="{{ static_asset('assets/domain-a11y-panel.js') }}" defer></script>
    <div class="sidebar-overlay"></div>
    <script src="{{ static_asset('assets/component.js') }}"></script>
    <script src="{{ static_asset('assets/sweetalert2-all.min.js') }}"></script>
    <script src="{{ static_asset('assets/toastr.min.js') }}"></script>
    <script src="{{ asset('js/home-hero.js') }}?v=1" defer></script>
    <script src="{{ asset('js/home-catalog-slider.js') }}?v=7" defer></script>
    <script src="{{ asset('js/logo-marquee.js') }}?v=1" defer></script>

    @livewireScripts
    @include('partials.commerce-bridge')
    @stack('scripts')
</body>
</html>
