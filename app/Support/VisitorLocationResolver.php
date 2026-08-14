<?php

namespace App\Support;

use Illuminate\Http\Request;

class VisitorLocationResolver
{
    /** @return array{country_code: ?string, country_name: ?string, region: ?string, city: ?string} */
    public function resolve(Request $request): array
    {
        $headers = match (config('analytics.geo_provider', 'none')) {
            'cloudflare' => [
                'country_code' => ['CF-IPCountry'],
                'country_name' => [],
                'region' => [],
                'city' => [],
            ],
            'vercel' => [
                'country_code' => ['X-Vercel-IP-Country'],
                'country_name' => [],
                'region' => ['X-Vercel-IP-Country-Region'],
                'city' => ['X-Vercel-IP-City'],
            ],
            'cloudfront' => [
                'country_code' => ['CloudFront-Viewer-Country'],
                'country_name' => [],
                'region' => ['CloudFront-Viewer-Country-Region-Name', 'CloudFront-Viewer-Country-Region'],
                'city' => ['CloudFront-Viewer-City'],
            ],
            'appengine' => [
                'country_code' => ['X-AppEngine-Country'],
                'country_name' => [],
                'region' => ['X-AppEngine-Region'],
                'city' => ['X-AppEngine-City'],
            ],
            'custom' => [
                'country_code' => ['X-Geo-Country-Code'],
                'country_name' => ['X-Geo-Country'],
                'region' => ['X-Geo-Region'],
                'city' => ['X-Geo-City'],
            ],
            default => [
                'country_code' => [],
                'country_name' => [],
                'region' => [],
                'city' => [],
            ],
        };

        $countryCode = $this->firstHeader($request, $headers['country_code']);
        $countryName = $this->firstHeader($request, $headers['country_name']);
        $region = $this->firstHeader($request, $headers['region']);
        $city = $this->firstHeader($request, $headers['city']);

        $countryCode = $countryCode ? strtoupper(substr($countryCode, 0, 8)) : null;

        return [
            'country_code' => $countryCode,
            'country_name' => $countryName ?: $countryCode,
            'region' => $region,
            'city' => $city,
        ];
    }

    private function firstHeader(Request $request, array $names): ?string
    {
        foreach ($names as $name) {
            $value = $request->header($name);

            if (is_string($value) && trim($value) !== '') {
                return mb_substr(rawurldecode(trim($value)), 0, 160);
            }
        }

        return null;
    }
}
