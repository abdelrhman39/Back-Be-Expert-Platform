<!DOCTYPE html>
<html class="no-js use-domain-a11y" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FAVICON) }}" type="image/x-icon">
    <title>{{ $title ?? 'تسجيل الدخول | مركز التعلم المستمر' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'سجّل الدخول باستخدام رقم الهوية أو رقم الجوال.' }}">

    <link rel="stylesheet" href="{{ static_asset('assets/all.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/vendor/fonts/google-fonts-local.css') }}">
    <link rel="stylesheet" media="all" href="{{ static_asset('assets/muneer.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/style.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/components.css') }}">
    <link rel="stylesheet" href="{{ static_asset('css/site-enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v=16">
    <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
    @include('partials.platform-theme')
    <link rel="stylesheet" href="{{ static_asset('assets/domain-a11y-panel.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-footer.css') }}?v=1">

    @livewireStyles
    @stack('styles')
</head>
<body class="portal-body d-flex flex-column min-vh-100">
    @include('partials.portal.header')

    {{ $slot }}
    @include('partials.page-help')

    @include('partials.footer')
    @include('partials.portal.login-extras')
    @include('partials.support.ai-chat-widget', ['mountClass' => 'ai-chat-portal-mount'])

    <script src="{{ static_asset('assets/portal-shell.js') }}" defer></script>
    <script src="{{ static_asset('assets/domain-a11y-panel.js') }}" defer></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
