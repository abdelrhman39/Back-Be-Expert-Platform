<?php

use App\Models\PlatformSetting;
use App\Models\User;
use App\Support\AdminPermissions;
use App\Support\LogoSettings;
use App\Support\PosterSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

if (! function_exists('static_asset')) {
    /** Asset from New-Platform static mirror (public/new-platform junction). */
    function static_asset(string $path): string
    {
        return asset('new-platform/'.ltrim($path, './'));
    }
}

if (! function_exists('platform_campus_path')) {
    /** Relative campus photo path used by CMS blocks and public banners. */
    function platform_campus_path(string $which = 'aerial'): string
    {
        return $which === 'entrance'
            ? 'assets/branding/aou-campus-entrance.jpg'
            : 'assets/branding/aou-campus-aerial.jpg';
    }
}

if (! function_exists('platform_campus_gallery')) {
    /** Ordered campus stills for the homepage hero slider. */
    function platform_campus_gallery(): array
    {
        return [
            platform_campus_path('aerial'),
            platform_campus_path('entrance'),
        ];
    }
}

if (! function_exists('platform_campus_video_path')) {
    /** Optional campus motion clip when the file exists in branding assets. */
    function platform_campus_video_path(): ?string
    {
        foreach (['assets/branding/aou-campus.mp4', 'assets/branding/aou-campus.webm'] as $relative) {
            if (is_file(public_path('new-platform/'.$relative))) {
                return $relative;
            }
        }

        return null;
    }
}

if (! function_exists('cms_media_url')) {
    /**
     * Resolve a CMS media path: absolute URL, /storage/…, or New-Platform static asset.
     */
    function cms_media_url(?string $path, string $fallback = ''): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $fallback;
        }

        if (
            str_starts_with(strtolower($path), 'http://')
            || str_starts_with(strtolower($path), 'https://')
            || str_starts_with(strtolower($path), 'data:')
        ) {
            return $path;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return asset(ltrim($path, '/'));
        }

        return static_asset(ltrim($path, './'));
    }
}

if (! function_exists('legacy_page')) {
    /**
     * @deprecated Prefer cms_href() or named routes. Kept only for rare portal leftovers.
     */
    function legacy_page(string $page): string
    {
        return cms_href($page);
    }
}

if (! function_exists('cms_href')) {
    /**
     * Resolve a CMS-editable link: route name, absolute URL, or site path.
     * Does not map to the static HTML mirror.
     */
    function cms_href(?string $link, ?string $locale = null): string
    {
        $link = trim((string) $link);

        if ($link === '' || $link === '#') {
            return '#';
        }

        // Fast path for absolute/external links (avoid regex delimiter issues).
        if (
            str_starts_with(strtolower($link), 'http://')
            || str_starts_with(strtolower($link), 'https://')
            || str_starts_with(strtolower($link), 'mailto:')
            || str_starts_with(strtolower($link), 'tel:')
            || str_starts_with($link, '/')
            || str_starts_with($link, '#')
        ) {
            return $link;
        }

        $locale ??= app()->getLocale();

        if (Route::has($link)) {
            try {
                return route($link, ['locale' => $locale]);
            } catch (Throwable) {
                return route($link);
            }
        }

        // Old .html paths — no longer served from the static mirror.
        if (str_ends_with(strtolower($link), '.html')) {
            return '#';
        }

        return url('/'.ltrim($link, '/'));
    }
}

if (! function_exists('default_poster_url')) {
    /** Default poster / cover image when no custom image is set. */
    function default_poster_url(): string
    {
        return PosterSettings::url();
    }
}

if (! function_exists('resolve_poster_url')) {
    /** Resolve a custom poster path or fall back to the platform default. */
    function resolve_poster_url(?string $path): string
    {
        return PosterSettings::resolve($path);
    }
}

if (! function_exists('platform_logo_url')) {
    /** Resolve a platform logo/favicon setting to a public URL. */
    function platform_logo_url(string $key): string
    {
        return LogoSettings::url($key);
    }
}

if (! function_exists('resolve_logo_url')) {
    /** Resolve a custom logo path or fall back to the platform default for the given key. */
    function resolve_logo_url(?string $path, string $key): string
    {
        return LogoSettings::resolve($path, $key);
    }
}

if (! function_exists('portal_user')) {
    /** Authenticated student-portal user (portal guard, or legacy web session). */
    function portal_user(): ?User
    {
        if (Auth::guard('portal')->check()) {
            return Auth::guard('portal')->user();
        }

        $webUser = Auth::guard('web')->user();

        if ($webUser && ! AdminPermissions::canAccessAdmin($webUser)) {
            return $webUser;
        }

        return null;
    }
}

if (! function_exists('portal_authenticated')) {
    function portal_authenticated(): bool
    {
        return portal_user() !== null;
    }
}

if (! function_exists('platform_name')) {
    /** Display name of the platform (locale-aware). */
    function platform_name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en') {
            return PlatformSetting::get(
                'platform_name_en',
                'Continuing Learning Center'
            ) ?: 'Continuing Learning Center';
        }

        return PlatformSetting::get(
            'platform_name_ar',
            'مركز التعلم المستمر'
        ) ?: 'مركز التعلم المستمر';
    }
}

if (! function_exists('platform_org')) {
    /** Owning organization / university name (locale-aware). */
    function platform_org(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en') {
            return PlatformSetting::get('platform_org_en', 'Arab Open University')
                ?: 'Arab Open University';
        }

        return PlatformSetting::get('platform_org_ar', 'الجامعة العربية المفتوحة')
            ?: 'الجامعة العربية المفتوحة';
    }
}

if (! function_exists('cms_text')) {
    function cms_text(?string $text, ?string $locale = null): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return \App\Support\Utf8Text::interpolate($text, $locale);
    }
}

if (! function_exists('cms_text_deep')) {
    function cms_text_deep(mixed $value, ?string $locale = null): mixed
    {
        return \App\Support\Utf8Text::deep($value, $locale);
    }
}

if (! function_exists('public_copy')) {
    /** Locale-aware public chrome string (header / toolbar / fallbacks). */
    function public_copy(string $key, ?string $locale = null): string
    {
        return \App\Support\PublicCopy::chrome($key, $locale);
    }
}

if (! function_exists('platform_title')) {
    /** Build a page title with the platform name suffix. */
    function platform_title(string $pageTitle): string
    {
        return trim($pageTitle).' | '.platform_name();
    }
}
