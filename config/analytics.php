<?php

return [
    /*
    |--------------------------------------------------------------------------
    | First-party analytics retention
    |--------------------------------------------------------------------------
    |
    | Raw event rows are pseudonymous (no raw IP is stored) and are removed
    | after this period. Aggregated dashboard metrics use the retained rows.
    |
    */
    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 180),

    /*
    | Trust geo headers only when requests are forced through this provider.
    | Supported: none, cloudflare, vercel, cloudfront, appengine, custom.
    */
    'geo_provider' => env('ANALYTICS_GEO_PROVIDER', 'none'),

    /*
    | Separate analytics secret. The visitor hash rotates monthly and cannot
    | be correlated indefinitely. Set a dedicated random value in production.
    */
    'hash_salt' => env('ANALYTICS_HASH_SALT', hash('sha256', (string) env('APP_KEY').'|analytics')),
];
