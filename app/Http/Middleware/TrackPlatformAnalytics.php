<?php

namespace App\Http\Middleware;

use App\Services\PlatformAnalyticsRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Illuminate\Support\defer;

class TrackPlatformAnalytics
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            defer(fn () => app(PlatformAnalyticsRecorder::class)->recordPageView($request));
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('content-type'));

        if (! str_contains($contentType, 'text/html')) {
            return false;
        }

        if ($request->is(
            'admin',
            'admin/*',
            'livewire-*',
            'livewire-*/*',
            'webhooks/*',
            'up',
            '*.css',
            '*.js',
            '*.map',
            '*.json',
            'storage/*',
            'new-platform/*',
        )) {
            return false;
        }

        return ! app(PlatformAnalyticsRecorder::class)->isBot($request);
    }
}
