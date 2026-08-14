<?php

require __DIR__.'/static-path-converter.php';

$indexPath = dirname(__DIR__).'/../New-Platform/index.html';
$html = file_get_contents($indexPath);

if (! preg_match('/<!-- Header -->(.*?)<!-- \/Header -->/s', $html, $headerMatch)) {
    fwrite(STDERR, "Header not found\n");
    exit(1);
}

if (! preg_match('/<!-- \/Header -->(.*?)<a href="https:\/\/wa\.me\/\+966543406744"/s', $html, $homeMatch)) {
    fwrite(STDERR, "Home content not found\n");
    exit(1);
}

if (! preg_match('/(<a href="https:\/\/wa\.me\/\+966543406744".*?<\/footer>)/s', $html, $footerMatch)) {
    fwrite(STDERR, "Footer block not found\n");
    exit(1);
}

$header = static_convert_paths(trim($headerMatch[1]));
$home = static_convert_paths(trim($homeMatch[1]));
$footerBlock = static_convert_paths(trim($footerMatch[1]));

$floatButtons = '';
if (preg_match('/^(<a href="https:\/\/wa\.me.*?<\/a>\s*<a href="[^"]+" class="float-mail".*?<\/a>)/s', $footerBlock, $floatMatch)) {
    $floatButtons = trim($floatMatch[1]);
    $footerBlock = trim(substr($footerBlock, strlen($floatMatch[1])));
}

$outDir = dirname(__DIR__).'/resources/views/partials';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

file_put_contents($outDir.'/header.blade.php', "@php(\$locale = app()->getLocale())\n".$header);
file_put_contents($outDir.'/home-static.blade.php', $home);
file_put_contents($outDir.'/float-buttons.blade.php', "@php(\$locale = app()->getLocale())\n".$floatButtons);
file_put_contents($outDir.'/footer.blade.php', "@php(\$locale = app()->getLocale())\n".$footerBlock."\n\n<style>\n    .vision-logo { max-height: 100px; }\n    @media (max-width: 768px) { .vision-logo { max-height: 80px; } }\n    .footer-logos { flex-wrap: wrap; }\n</style>\n");

echo "Extracted: header ".strlen($header)."b, home ".strlen($home)."b, footer ".strlen($footerBlock)."b\n";
