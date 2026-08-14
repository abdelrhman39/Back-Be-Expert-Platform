<?php

namespace App\Services\MicrosoftTeams;

use App\Models\MicrosoftTeamsConnection;
use App\Support\TeamsSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsGraphClient
{
    public function appAccessToken(): ?string
    {
        if (! TeamsSettings::isConfigured()) {
            return null;
        }

        $response = Http::asForm()->post($this->tokenUrl(), [
            'client_id' => TeamsSettings::clientId(),
            'client_secret' => TeamsSettings::clientSecret(),
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            Log::warning('Teams app token failed', ['body' => $response->json()]);

            return null;
        }

        return $response->json('access_token');
    }

    public function refreshUserToken(MicrosoftTeamsConnection $connection): ?string
    {
        if (! $connection->refresh_token || ! TeamsSettings::isConfigured()) {
            return null;
        }

        $response = Http::asForm()->post($this->tokenUrl(), [
            'client_id' => TeamsSettings::clientId(),
            'client_secret' => TeamsSettings::clientSecret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $connection->refresh_token,
            'scope' => TeamsSettings::studentScopes(),
        ]);

        if (! $response->successful()) {
            Log::warning('Teams user token refresh failed', ['user_id' => $connection->user_id]);

            return null;
        }

        $data = $response->json();
        $connection->update([
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? $connection->refresh_token,
            'token_expires_at' => now()->addSeconds((int) ($data['expires_in'] ?? 3600)),
        ]);

        return $data['access_token'] ?? null;
    }

    public function userAccessToken(MicrosoftTeamsConnection $connection): ?string
    {
        if (! $connection->access_token || $connection->isExpired()) {
            return $this->refreshUserToken($connection);
        }

        return $connection->access_token;
    }

    /** @return array<string, mixed>|null */
    public function get(string $path, ?string $token = null): ?array
    {
        $token ??= $this->appAccessToken();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get(config('teams.graph_base').$path);

        if (! $response->successful()) {
            Log::warning('Teams Graph GET failed', ['path' => $path, 'status' => $response->status()]);

            return null;
        }

        return $response->json();
    }

    /** @param array<string, mixed> $body @return array<string, mixed>|null */
    public function post(string $path, array $body, ?string $token = null): ?array
    {
        $token ??= $this->appAccessToken();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(config('teams.graph_base').$path, $body);

        if (! $response->successful()) {
            Log::warning('Teams Graph POST failed', ['path' => $path, 'status' => $response->status(), 'body' => $response->json()]);

            return null;
        }

        return $response->json();
    }

    protected function tokenUrl(): string
    {
        $tenant = TeamsSettings::tenantId() ?: 'common';

        return config('teams.authority')."/{$tenant}/oauth2/v2.0/token";
    }
}
