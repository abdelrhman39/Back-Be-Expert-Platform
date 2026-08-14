<?php

require __DIR__.'/static-path-converter.php';

$loginPath = dirname(__DIR__).'/../New-Platform/ar/login/index.html';
$html = file_get_contents($loginPath);

$outDir = dirname(__DIR__).'/resources/views/partials/portal';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$header = static_extract_portal_header($html);
$footer = static_extract_portal_footer($html);

file_put_contents($outDir.'/header.blade.php', "@php(\$locale = app()->getLocale())\n".$header);
file_put_contents($outDir.'/footer.blade.php', "@php(\$locale = app()->getLocale())\n".$footer);

echo "Extracted portal shell: header ".strlen($header)."b, footer ".strlen($footer)."b\n";
