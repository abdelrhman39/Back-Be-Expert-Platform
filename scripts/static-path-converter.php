<?php

function static_convert_paths(string $content): string
{
    $content = preg_replace_callback(
        '/(?:\.\.\/)+assets\/([^"]+)/',
        static fn ($m) => "{{ static_asset('assets/" . $m[1] . "') }}",
        $content
    );

    $content = preg_replace_callback(
        '/\.\/css\/([^"]+)/',
        static fn ($m) => "{{ static_asset('css/" . $m[1] . "') }}",
        $content
    );

    $content = preg_replace_callback(
        '/\.\.\/css\/([^"]+)/',
        static fn ($m) => "{{ static_asset('css/" . $m[1] . "') }}",
        $content
    );

    $content = preg_replace_callback(
        '/src="\.\/assets\/([^"]+)"/',
        static fn ($m) => 'src="{{ static_asset(\'assets/' . $m[1] . '\') }}"',
        $content
    );

    $content = preg_replace_callback(
        '/href="\.\/assets\/([^"]+)"/',
        static fn ($m) => 'href="{{ static_asset(\'assets/' . $m[1] . '\') }}"',
        $content
    );

    $content = preg_replace_callback(
        '/src="assets\/([^"]+)"/',
        static fn ($m) => 'src="{{ static_asset(\'assets/' . $m[1] . '\') }}"',
        $content
    );

    $content = preg_replace(
        '/<a id="menu_close" class="menu-close" href="[^"]*">\s*<i class="fas fa-times"><\/i><\/a>/',
        '<a id="menu_close" class="menu-close" href="javascript:void(0);"> <i class="fas fa-times"></i></a>',
        $content
    );

    $locale = '$locale';

    $routes = [
        'index.html' => "{{ route('home', ['locale' => $locale]) }}",
        './index.html' => "{{ route('home', ['locale' => $locale]) }}",
        '../../index.html' => "{{ route('home', ['locale' => $locale]) }}",
        '../../index.html#about' => "{{ route('home', ['locale' => $locale]) }}#about",
        'about.html' => "{{ route('about', ['locale' => $locale]) }}",
        './about.html' => "{{ route('about', ['locale' => $locale]) }}",
        'contact.html' => "{{ route('contact', ['locale' => $locale]) }}",
        './contact.html' => "{{ route('contact', ['locale' => $locale]) }}",
        '../../contact.html' => "{{ route('contact', ['locale' => $locale]) }}",
        'courses.html' => "{{ route('courses.index', ['locale' => $locale]) }}",
        './courses.html' => "{{ route('courses.index', ['locale' => $locale]) }}",
        '../../courses.html' => "{{ route('courses.index', ['locale' => $locale]) }}",
        'login.html' => "{{ route('login', ['locale' => $locale]) }}",
        './login.html' => "{{ route('login', ['locale' => $locale]) }}",
        './ar/login/index.html' => "{{ route('login', ['locale' => $locale]) }}",
        'profile.html' => "{{ route('profile', ['locale' => $locale]) }}",
        './profile.html' => "{{ route('profile', ['locale' => $locale]) }}",
        'learning-list.html' => "{{ route('learning-list', ['locale' => $locale]) }}",
        './learning-list.html' => "{{ route('learning-list', ['locale' => $locale]) }}",
        './ar/cart' => "{{ route('cart', ['locale' => $locale]) }}",
        './ar/wishlist' => "{{ route('wishlist', ['locale' => $locale]) }}",
        'ar/cart.html' => "{{ route('cart', ['locale' => $locale]) }}",
        'ar/wishlist.html' => "{{ route('wishlist', ['locale' => $locale]) }}",
        '../courses.html' => "{{ route('courses.index', ['locale' => $locale]) }}",
        '../ar/login/index.html' => "{{ route('login', ['locale' => $locale]) }}",
        '../ar/register/index.html' => "{{ legacy_page('ar/register/index.html') }}",
        './en' => "{{ legacy_page('en/index.html') }}",
        './ar/fellowships' => "{{ legacy_page('ar/fellowships.html') }}",
        './ar/client-request' => "{{ legacy_page('ar/client-request.html') }}",
        './ar/company-request' => "{{ legacy_page('ar/company-request.html') }}",
        './ar/marketer-request' => "{{ legacy_page('ar/marketer-request.html') }}",
        './ar/cooperative-training' => "{{ legacy_page('cooperative-training.html') }}",
        './ar/instructor-request' => "{{ legacy_page('ar/instructor-request.html') }}",
        './ar/employee-request' => "{{ legacy_page('ar/employee-request.html') }}",
        './ar/job-seeker-request' => "{{ legacy_page('ar/job-seeker-request.html') }}",
        './ar/statment' => "{{ legacy_page('ar/statment.html') }}",
        'certificate-verify.html' => "{{ legacy_page('certificate-verify.html') }}",
        'contact.html#contact-us-Form' => "{{ route('contact', ['locale' => $locale]) }}#contact-us-Form",
        '../register/index.html' => "{{ legacy_page('ar/register/index.html') }}",
        '../password/reset/index.html' => "{{ legacy_page('ar/password/reset/index.html') }}",
        '../support/contact/index.html' => "{{ legacy_page('ar/support/contact/index.html') }}",
        '../support/faq/index.html' => "{{ legacy_page('ar/support/faq/index.html') }}",
        '../support/ticket/new/index.html' => "{{ legacy_page('ar/support/ticket/new/index.html') }}",
        '../../en/login/index.html' => "{{ legacy_page('en/login/index.html') }}",
    ];

    foreach ($routes as $static => $blade) {
        $content = str_replace('href="' . $static . '"', 'href="' . $blade . '"', $content);
    }

    $content = preg_replace_callback(
        '/href="\.\/ar\/([^"#?]+)(#[^"]*)?"/',
        static function ($m) use ($locale) {
            $page = $m[1];
            $hash = $m[2] ?? '';

            $laravel = [
                'cart' => "{{ route('cart', ['locale' => $locale]) }}",
                'wishlist' => "{{ route('wishlist', ['locale' => $locale]) }}",
            ];

            if (isset($laravel[$page])) {
                return 'href="' . $laravel[$page] . ($hash ?: '') . '"';
            }

            return 'href="{{ legacy_page(\'ar/' . $page . '.html\') }}"' . ($hash ?: '');
        },
        $content
    );

    $content = preg_replace_callback(
        '/href="([^"#?]+\.html)(#[^"]*)?"/',
        static function ($m) use ($locale) {
            $file = $m[1];
            $hash = $m[2] ?? '';

            $laravel = [
                'about.html' => "{{ route('about', ['locale' => $locale]) }}",
                'contact.html' => "{{ route('contact', ['locale' => $locale]) }}",
                'courses.html' => "{{ route('courses.index', ['locale' => $locale]) }}",
                'index.html' => "{{ route('home', ['locale' => $locale]) }}",
                'login.html' => "{{ route('login', ['locale' => $locale]) }}",
                'profile.html' => "{{ route('profile', ['locale' => $locale]) }}",
                'learning-list.html' => "{{ route('learning-list', ['locale' => $locale]) }}",
                'cart.html' => "{{ route('cart', ['locale' => $locale]) }}",
                'wishlist.html' => "{{ route('wishlist', ['locale' => $locale]) }}",
            ];

            if (isset($laravel[$file])) {
                return 'href="' . $laravel[$file] . ($hash ?: '') . '"';
            }

            return 'href="{{ legacy_page(\'' . $file . '\') }}' . ($hash ?: '') . '"';
        },
        $content
    );

    $content = str_replace('<span id="cartCount">0</span>', '<span id="cartCount">{{ $cartCount ?? 0 }}</span>', $content);
    $content = str_replace('<span id="wichCount">0</span>', '<span id="wichCount">{{ $wishlistCount ?? 0 }}</span>', $content);

    return $content;
}

function static_fix_logout_forms(string $content): string
{
    return preg_replace(
        '/<form id="logout-form" action="#" onsubmit="return false;" method="POST" style="display: none;">\s*<input type="hidden" name="_token"[^>]*>\s*<\/form>\s*/',
        '',
        $content
    );
}

function static_extract_page_body(string $html): string
{
    if (! preg_match('/<!-- \/Header -->(.*?)<a href="https:\/\/wa\.me\/\+966543406744"/s', $html, $match)) {
        throw new RuntimeException('Page body not found');
    }

    return static_fix_logout_forms(static_convert_paths(trim($match[1])));
}

function static_extract_user_page_body(string $html): string
{
    if (! preg_match('/<!-- Header -->(.*?)<a href="https:\/\/wa\.me\/\+966543406744"/s', $html, $match)) {
        throw new RuntimeException('User page body not found');
    }

    return static_fix_logout_forms(static_convert_paths(trim($match[1])));
}

function static_convert_portal_paths(string $content): string
{
    $content = static_convert_paths($content);
    $locale = '$locale';

    $portalRoutes = [
        './index.html' => "{{ route('login', ['locale' => $locale]) }}",
    ];

    foreach ($portalRoutes as $static => $blade) {
        $content = str_replace('href="' . $static . '"', 'href="' . $blade . '"', $content);
    }

    return $content;
}

function static_extract_portal_header(string $html): string
{
    if (! preg_match('/<header class="portal-header">(.*?)<\/header>/s', $html, $match)) {
        throw new RuntimeException('Portal header not found');
    }

    return static_convert_portal_paths('<header class="portal-header">'.trim($match[1]).'</header>');
}

function static_extract_portal_footer(string $html): string
{
    if (! preg_match('/(<footer class="portal-footer">.*?<a class="portal-fab".*?<\/a>)/s', $html, $match)) {
        throw new RuntimeException('Portal footer block not found');
    }

    return static_convert_portal_paths(trim($match[1]));
}
