<?php

require __DIR__.'/static-admin-converter.php';

$html = file_get_contents(dirname(__DIR__).'/../New-Platform/admin/index.html');
$body = static_extract_admin_dashboard_body($html);

$outDir = dirname(__DIR__).'/resources/views/partials/admin';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

file_put_contents($outDir.'/dashboard-static.blade.php', $body);

echo 'Extracted admin dashboard: '.strlen($body)." bytes\n";
