<?php

function static_convert_admin_paths(string $content): string
{
    $content = preg_replace_callback(
        '/(?:\.\.\/)+assets\/([^"]+)/',
        static fn ($m) => "{{ static_asset('assets/" . $m[1] . "') }}",
        $content
    );

    $content = preg_replace_callback(
        '/\.\/css\/([^"]+)/',
        static fn ($m) => "{{ static_asset('admin/css/" . $m[1] . "') }}",
        $content
    );

    $content = preg_replace_callback(
        '/\.\/js\/([^"]+)/',
        static fn ($m) => "{{ static_asset('admin/js/" . $m[1] . "') }}",
        $content
    );

    $content = str_replace(
        'href="../index.html"',
        'href="{{ route(\'home\', [\'locale\' => \'ar\']) }}"',
        $content
    );

    $content = preg_replace_callback(
        '/href="([^"#?]+\.html)(#[^"]*)?"/',
        static function ($m) {
            $file = $m[1];
            $hash = $m[2] ?? '';

            if ($file === 'index.html') {
                return 'href="{{ route(\'admin.dashboard\') }}"' . ($hash ?: '');
            }

            return 'href="{{ legacy_page(\'admin/' . $file . '\') }}"' . ($hash ?: '');
        },
        $content
    );

    return $content;
}

function static_extract_admin_dashboard_body(string $html): string
{
    $startMarker = '<div class="admin-app admin-app--dashboard">';
    $endMarker = '<script src="./js/admin-auth.js">';

    $start = strpos($html, $startMarker);
    $end = strpos($html, $endMarker, $start !== false ? $start : 0);

    if ($start === false || $end === false) {
        throw new RuntimeException('Admin dashboard body not found');
    }

    return static_convert_admin_paths(trim(substr($html, $start, $end - $start)));
}
