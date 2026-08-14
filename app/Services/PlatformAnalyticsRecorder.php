<?php

namespace App\Services;

use App\Models\PlatformAnalyticsEvent;
use App\Models\User;
use App\Support\VisitorLocationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformAnalyticsRecorder
{
    private static ?bool $tableExists = null;

    public function recordPageView(Request $request): void
    {
        // Page-view analytics are intentionally anonymous. User identity is
        // retained only for explicit login and registration events.
        $this->record('page_view', $request);
    }

    public function recordLogin(User $user, ?Request $request = null, ?string $guard = null): void
    {
        $this->record('login', $request ?? request(), $user, ['guard' => $guard]);
    }

    public function recordRegistration(User $user, ?Request $request = null): void
    {
        $this->record('registration', $request ?? request(), $user);
    }

    public function isBot(Request $request): bool
    {
        $agent = strtolower((string) $request->userAgent());

        return $agent !== '' && preg_match(
            '/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|headless|lighthouse|uptimerobot/',
            $agent
        ) === 1;
    }

    private function record(
        string $eventType,
        Request $request,
        ?User $user = null,
        ?array $metadata = null,
    ): void {
        if (! $this->tableExists()) {
            return;
        }

        try {
            $now = now();
            $agent = (string) $request->userAgent();
            $location = app(VisitorLocationResolver::class)->resolve($request);
            $referrerHost = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
            $routeTemplate = $request->route()?->uri();

            PlatformAnalyticsEvent::query()->create([
                'event_type' => $eventType,
                'visit_id' => $this->visitId($request),
                'visitor_hash' => $this->visitorHash($request),
                'user_id' => $user?->id,
                'path' => mb_substr(
                    $routeTemplate ? '/'.ltrim($routeTemplate, '/') : '/'.ltrim($request->path(), '/'),
                    0,
                    500,
                ),
                'route_name' => mb_substr((string) $request->route()?->getName(), 0, 190) ?: null,
                'referrer_host' => is_string($referrerHost) ? mb_substr($referrerHost, 0, 190) : null,
                ...$location,
                ...$this->parseAgent($agent),
                'metadata' => array_filter($metadata ?? [], fn ($value) => $value !== null),
                'occurred_on' => $now->toDateString(),
                'occurred_at' => $now,
            ]);
        } catch (\Throwable $exception) {
            Log::debug('Platform analytics event was not recorded.', [
                'event_type' => $eventType,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function tableExists(): bool
    {
        return self::$tableExists ??= Schema::hasTable('platform_analytics_events');
    }

    private function visitId(Request $request): string
    {
        if (! $request->hasSession()) {
            return (string) Str::uuid();
        }

        $visitId = $request->session()->get('platform_analytics_visit_id');

        if (! is_string($visitId) || $visitId === '') {
            $visitId = (string) Str::uuid();
            $request->session()->put('platform_analytics_visit_id', $visitId);
        }

        return $visitId;
    }

    private function visitorHash(Request $request): string
    {
        $device = $this->parseAgent((string) $request->userAgent())['device_type'];
        $fingerprint = $this->truncateIp($request->ip()).'|'.$device;
        $monthlySalt = hash_hmac(
            'sha256',
            now()->format('Y-m'),
            (string) config('analytics.hash_salt'),
        );

        return hash_hmac('sha256', $fingerprint, $monthlySalt);
    }

    private function truncateIp(?string $ip): string
    {
        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'unknown';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts);
        }

        $packed = inet_pton($ip);

        return $packed === false ? 'unknown' : bin2hex(substr($packed, 0, 8));
    }

    /** @return array{device_type: string, browser: string, operating_system: string} */
    private function parseAgent(string $agent): array
    {
        $device = preg_match('/tablet|ipad/i', $agent)
            ? 'tablet'
            : (preg_match('/mobile|android|iphone|ipod/i', $agent) ? 'mobile' : 'desktop');

        $browser = match (true) {
            preg_match('/Edg\//i', $agent) === 1 => 'Edge',
            preg_match('/OPR\/|Opera/i', $agent) === 1 => 'Opera',
            preg_match('/Chrome\//i', $agent) === 1 => 'Chrome',
            preg_match('/Firefox\//i', $agent) === 1 => 'Firefox',
            preg_match('/Safari\//i', $agent) === 1 => 'Safari',
            default => 'أخرى',
        };

        $operatingSystem = match (true) {
            preg_match('/Windows/i', $agent) === 1 => 'Windows',
            preg_match('/Android/i', $agent) === 1 => 'Android',
            preg_match('/iPhone|iPad|iPod/i', $agent) === 1 => 'iOS',
            preg_match('/Mac OS|Macintosh/i', $agent) === 1 => 'macOS',
            preg_match('/Linux/i', $agent) === 1 => 'Linux',
            default => 'أخرى',
        };

        return [
            'device_type' => $device,
            'browser' => $browser,
            'operating_system' => $operatingSystem,
        ];
    }
}
