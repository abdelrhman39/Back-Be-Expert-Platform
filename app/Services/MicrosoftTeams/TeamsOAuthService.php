<?php

namespace App\Services\MicrosoftTeams;

use App\Models\MicrosoftTeamsConnection;
use App\Models\User;
use App\Support\TeamsSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TeamsOAuthService
{
    public function __construct(protected TeamsGraphClient $graph) {}

    public function authorizationUrl(User $user): string
    {
        $state = encrypt([
            'user_id' => $user->id,
            'nonce' => Str::random(32),
            'ts' => now()->timestamp,
        ]);

        $params = http_build_query([
            'client_id' => TeamsSettings::clientId(),
            'response_type' => 'code',
            'redirect_uri' => TeamsSettings::redirectUri(),
            'response_mode' => 'query',
            'scope' => TeamsSettings::studentScopes(),
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        $tenant = TeamsSettings::tenantId() ?: 'common';

        return config('teams.authority')."/{$tenant}/oauth2/v2.0/authorize?{$params}";
    }

    /** @return array{ok: bool, message?: string} */
    public function handleCallback(string $code, string $state): array
    {
        if (! TeamsSettings::isConfigured()) {
            return ['ok' => false, 'message' => 'Teams integration is not configured.'];
        }

        try {
            $payload = decrypt($state);
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'Invalid OAuth state.'];
        }

        $user = User::query()->find($payload['user_id'] ?? 0);

        if (! $user) {
            return ['ok' => false, 'message' => 'User not found.'];
        }

        $tenant = TeamsSettings::tenantId() ?: 'common';
        $response = Http::asForm()->post(
            config('teams.authority')."/{$tenant}/oauth2/v2.0/token",
            [
                'client_id' => TeamsSettings::clientId(),
                'client_secret' => TeamsSettings::clientSecret(),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => TeamsSettings::redirectUri(),
                'scope' => TeamsSettings::studentScopes(),
            ],
        );

        if (! $response->successful()) {
            return ['ok' => false, 'message' => 'Failed to exchange authorization code.'];
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;

        if (! $accessToken) {
            return ['ok' => false, 'message' => 'No access token received.'];
        }

        $profile = Http::withToken($accessToken)->get(config('teams.graph_base').'/me')->json();

        if (! $profile || empty($profile['id'])) {
            return ['ok' => false, 'message' => 'Could not read Microsoft profile.'];
        }

        $microsoftEmail = strtolower($profile['mail'] ?? $profile['userPrincipalName'] ?? '');

        MicrosoftTeamsConnection::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'microsoft_id' => $profile['id'],
                'microsoft_email' => $microsoftEmail,
                'display_name' => $profile['displayName'] ?? null,
                'access_token' => $accessToken,
                'refresh_token' => $data['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds((int) ($data['expires_in'] ?? 3600)),
                'tenant_id' => TeamsSettings::tenantId(),
                'connected_at' => now(),
            ],
        );

        return ['ok' => true];
    }

    public function disconnect(User $user): void
    {
        MicrosoftTeamsConnection::query()->where('user_id', $user->id)->delete();
    }
}
