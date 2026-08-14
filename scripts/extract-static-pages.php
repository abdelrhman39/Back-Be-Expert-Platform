<?php

require __DIR__.'/static-path-converter.php';

$pages = [
    'about' => '../New-Platform/about.html',
    'contact' => '../New-Platform/contact.html',
    'courses' => '../New-Platform/courses.html',
    'cart' => '../New-Platform/ar/cart.html',
    'wishlist' => '../New-Platform/ar/wishlist.html',
    'profile' => '../New-Platform/profile.html',
    'learning-list' => '../New-Platform/learning-list.html',
];

$outDir = dirname(__DIR__).'/resources/views/partials/pages';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

foreach ($pages as $slug => $relativePath) {
    $path = dirname(__DIR__).'/'.$relativePath;
    $html = file_get_contents($path);
    $body = in_array($slug, ['profile', 'learning-list'], true)
        ? static_extract_user_page_body($html)
        : static_extract_page_body($html);
    $outFile = $outDir.'/'.$slug.'-static.blade.php';
    file_put_contents($outFile, "@php(\$locale = app()->getLocale())\n".$body);
    echo "Extracted {$slug}: ".strlen($body)." bytes -> {$outFile}\n";
}
