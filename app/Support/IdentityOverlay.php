<?php

namespace App\Support;

class IdentityOverlay
{
    /** @var list<string> */
    private const FOREIGN_ASSET_BASENAMES = [
        'aou-logo-footer.png',
        'aou-logo.png',
        'aou-favicon.png',
        'aou-campus-aerial.jpg',
        'aou-campus-entrance.jpg',
        'aou-campus.jpg',
        'aou-campus.png',
    ];

    public static function enabled(): bool
    {
        return IdentityThemes::resolvedBranding() !== [];
    }

    public static function rewriteText(?string $text): string
    {
        if ($text === null || $text === '' || ! self::enabled()) {
            return (string) $text;
        }

        return strtr($text, self::textReplacements());
    }

    public static function rewriteAssetPath(?string $path): ?string
    {
        if ($path === null || $path === '' || ! self::enabled()) {
            return $path;
        }

        $normalized = str_replace('\\', '/', $path);
        $query = '';

        if (str_contains($normalized, '?')) {
            [$normalized, $query] = explode('?', $normalized, 2);
            $query = '?'.$query;
        }

        $basename = strtolower(basename($normalized));
        $replacement = self::assetReplacements()[$basename] ?? null;

        if (! $replacement) {
            return $path;
        }

        $directory = dirname($normalized);

        if ($directory === '.' || $directory === '') {
            return $replacement.$query;
        }

        return $directory.'/'.basename($replacement).$query;
    }

    public static function rewriteHtml(string $html): string
    {
        if ($html === '' || ! self::enabled()) {
            return $html;
        }

        $html = self::rewriteText($html);

        foreach (self::assetReplacements() as $from => $to) {
            $html = str_replace($from, basename($to), $html);
        }

        return $html;
    }

    public static function isForeignUniversityAsset(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        $basename = strtolower(basename(strtok(str_replace('\\', '/', $path), '?')));

        return in_array($basename, self::FOREIGN_ASSET_BASENAMES, true);
    }

    /** @return array<string, string> */
    protected static function textReplacements(): array
    {
        $orgAr = platform_org('ar');
        $orgEn = platform_org('en');

        $replacements = [
            'الجامعة العربية المفتوحة السعودية' => $orgAr,
            'الجامعة العربية المفتوحة' => $orgAr,
            'الجامعة العربيه المفتوحه' => $orgAr,
            'Arab Open University Saudi Arabia' => $orgEn,
            'Arab Open University' => $orgEn,
        ];

        if (IdentityThemes::activeKey() === 'taibah-green') {
            $replacements['Continuing Learning Center'] = 'Applied College';
            $replacements['يقدّمه مركز التعلم المستمر'] = 'تقدّمه الكلية التطبيقية';
            $replacements['يقدمه مركز التعلم المستمر'] = 'تقدمه الكلية التطبيقية';
            $replacements['المتابعة الأكاديمية في المركز'] = 'المتابعة الأكاديمية في الكلية';
            $replacements['مركز التعلم المستمر منصة التعليم المستمر'] = 'الكلية التطبيقية';
            $replacements['منصة التعليم المستمر'] = 'الكلية التطبيقية';
            $replacements['مركز التعلم المستمر'] = 'الكلية التطبيقية';
            $replacements['معهد البحوث والدراسات الاستشارية'] = 'معهد البحوث والاستشارات';
        }

        return $replacements;
    }

    /** @return array<string, string> */
    protected static function assetReplacements(): array
    {
        $branding = IdentityThemes::resolvedBranding();
        $logo = (string) ($branding['platform_logo_primary'] ?? 'assets/branding/kku-logo.png');
        $footer = (string) ($branding['platform_logo_footer'] ?? $logo);
        $favicon = (string) ($branding['platform_favicon'] ?? 'assets/branding/kku-favicon.png');
        $campus = IdentityThemes::campusPath('aerial') ?: 'assets/branding/kku-campus.jpg';
        $entrance = IdentityThemes::campusPath('entrance') ?: $campus;

        return [
            'aou-logo-footer.png' => $footer,
            'aou-logo.png' => $logo,
            'aou-favicon.png' => $favicon,
            'aou-campus-aerial.jpg' => $campus,
            'aou-campus-entrance.jpg' => $entrance,
            'aou-campus.jpg' => $campus,
            'aou-campus.png' => $campus,
        ];
    }
}
