<!DOCTYPE html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FAVICON) }}" type="image/x-icon">
    <link href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FAVICON) }}" rel="shortcut icon">

    <meta name="description" content="{{ $metaDescription ?? 'منصة مركز التعلم المستمر' }}">
    <title>{{ $title ?? 'منصة مركز التعلم المستمر' }}</title>

    <link rel="stylesheet" href="{{ static_asset('assets/all.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/vendor/fonts/google-fonts-local.css') }}">
    <link rel="stylesheet" media="all" href="{{ static_asset('assets/muneer.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/style.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/components.css') }}">
    <link rel="stylesheet" href="{{ static_asset('css/site-enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v=12">
    <link rel="stylesheet" href="{{ asset('css/notification-bell.css') }}?v=1">
    @include('partials.platform-theme')
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
<body>
    <!-- <a class="skip-to-content" href="#main-content">تخطي إلى المحتوى</a> -->

    <div class="loader-mainn d-none"><span class="page-loader"></span></div>

    <div id="main-content" class="main-wrapper">
        {{ $slot }}
        @include('partials.page-help')
        @include('partials.float-buttons')
        @include('partials.footer')
    </div>

    <script src="{{ static_asset('assets/all.js') }}"></script>
    <script src="{{ static_asset('assets/script.js') }}"></script>
    <script src="{{ static_asset('assets/domain-a11y-panel.js') }}" defer></script>
    <div class="sidebar-overlay"></div>
    <script src="{{ static_asset('assets/component.js') }}"></script>
    <script src="{{ static_asset('assets/sweetalert2-all.min.js') }}"></script>
    <script src="{{ static_asset('assets/toastr.min.js') }}"></script>
    <script src="{{ static_asset('assets/muneer.min.js') }}" async></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
