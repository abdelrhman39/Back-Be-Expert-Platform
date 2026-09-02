<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'تسجيل الدخول | لوحة تحكم مركز التعلم المستمر' }}</title>
    <link rel="icon" href="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FAVICON) }}" type="image/png">
    <link rel="stylesheet" href="{{ static_asset('assets/vendor/fonts/google-fonts-local.css') }}">
    <link rel="stylesheet" href="{{ static_asset('assets/all.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v=17">
    @include('partials.platform-theme')
    @livewireStyles
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
