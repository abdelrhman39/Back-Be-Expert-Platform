<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class VisitorDialCode
{
    public static function iso(?Request $request = null): string
    {
        $request ??= request();
        $fromHeader = self::headerIso($request);

        if ($fromHeader) {
            return $fromHeader;
        }

        $fromIp = self::lookupIp((string) $request->ip());

        return $fromIp ?: CountryDialCodes::DEFAULT_ISO;
    }

    public static function headerIso(Request $request): ?string
    {
        $headers = [
            'CF-IPCountry',
            'X-Vercel-IP-Country',
            'CloudFront-Viewer-Country',
            'X-AppEngine-Country',
            'X-Geo-Country-Code',
            'X-Country-Code',
        ];

        foreach ($headers as $header) {
            $value = strtoupper(trim((string) $request->header($header)));

            if ($value === '' || in_array($value, ['XX', 'T1', 'ZZ'], true)) {
                continue;
            }

            if (CountryDialCodes::has($value)) {
                return $value;
            }
        }

        return null;
    }

    public static function lookupIp(string $ip): ?string
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        $cacheKey = 'visitor-dial-iso:'.md5($ip);

        $cached = Cache::get($cacheKey);

        if (is_string($cached) && CountryDialCodes::has($cached)) {
            return $cached;
        }

        try {
            $response = Http::timeout(1.5)
                ->acceptJson()
                ->get('https://api.country.is/'.$ip);

            $iso = strtoupper(trim((string) $response->json('country')));

            if (CountryDialCodes::has($iso)) {
                Cache::put($cacheKey, $iso, now()->addDay());

                return $iso;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }
}
