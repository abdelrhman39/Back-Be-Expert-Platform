<?php

namespace App\Services\Zoom;

use App\Support\ZoomSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomApiClient
{
    public function accessToken(): string
    {
        if (! ZoomSettings::configured()) {
            throw new ZoomApiException('Zoom Server-to-Server OAuth is not configured.');
        }

        $key = 'zoom.oauth.'.hash('sha256', ZoomSettings::accountId().ZoomSettings::clientId());

        $cached = Cache::get($key);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()
            ->withBasicAuth((string) ZoomSettings::clientId(), (string) ZoomSettings::clientSecret())
            ->post((string) config('zoom.oauth_url'), [
                'grant_type' => 'account_credentials',
                'account_id' => ZoomSettings::accountId(),
            ]);

        $data = $this->decode($response, 'OAuth token');
        $token = $data['access_token'] ?? null;
        if (! is_string($token) || $token === '') {
            throw new ZoomApiException('Zoom OAuth response did not contain an access token.', $response->status());
        }

        $ttl = max(60, (int) ($data['expires_in'] ?? 3600) - (int) config('zoom.token_safety_seconds', 120));
        Cache::put($key, $token, $ttl);

        return $token;
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    public function post(string $path, array $body = []): array
    {
        return $this->request('post', $path, $body);
    }

    public function patch(string $path, array $body = []): array
    {
        return $this->request('patch', $path, $body);
    }

    public function delete(string $path, array $query = []): bool
    {
        $this->request('delete', $path, $query);

        return true;
    }

    public function downloadTo(string $url, string $destination): void
    {
        $response = Http::withToken($this->accessToken())
            ->timeout(600)
            ->withOptions(['sink' => $destination])
            ->get($url);

        if (! $response->successful()) {
            @unlink($destination);
            throw new ZoomApiException(
                "Zoom recording download failed with HTTP {$response->status()}.",
                $response->status(),
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function paginate(string $path, array $query = [], string $itemsKey = 'participants'): array
    {
        $items = [];
        $nextPageToken = null;

        do {
            $page = $this->get($path, array_filter([
                ...$query,
                'page_size' => $query['page_size'] ?? 300,
                'next_page_token' => $nextPageToken,
            ]));
            $items = array_merge($items, is_array($page[$itemsKey] ?? null) ? $page[$itemsKey] : []);
            $nextPageToken = $page['next_page_token'] ?? null;
        } while (is_string($nextPageToken) && $nextPageToken !== '');

        return $items;
    }

    private function request(string $method, string $path, array $data): array
    {
        $url = rtrim((string) config('zoom.base_url'), '/').'/'.ltrim($path, '/');

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $request = Http::withToken($this->accessToken())->acceptJson()->timeout(30);
            $response = $request->{$method}($url, $data);

            if (
                $attempt < 3
                && ($response->status() === 429 || $response->serverError())
            ) {
                $retryAfter = max(1, (int) $response->header('Retry-After', (string) $attempt));
                usleep(min($retryAfter, 5) * 1_000_000);

                continue;
            }

            return $this->decode($response, strtoupper($method).' '.$this->safePath($path));
        }

        throw new ZoomApiException('Zoom API request failed after retries.');
    }

    private function decode(Response $response, string $operation): array
    {
        if (! $response->successful()) {
            $code = $response->json('code');
            Log::warning('Zoom API request failed', [
                'operation' => $operation,
                'status' => $response->status(),
                'zoom_code' => $code,
            ]);

            throw new ZoomApiException(
                "Zoom API {$operation} failed with HTTP {$response->status()}.",
                $response->status(),
                is_array($response->json()) ? $response->json() : null,
            );
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    private function safePath(string $path): string
    {
        return explode('?', $path, 2)[0];
    }
}
