<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class MicrosoftTeamsConnection extends Model
{
    protected $fillable = [
        'user_id',
        'microsoft_id',
        'microsoft_email',
        'display_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'tenant_id',
        'connected_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function isExpired(): bool
    {
        return ! $this->token_expires_at || $this->token_expires_at->isPast();
    }

    public function displayLabel(): string
    {
        return $this->display_name ?: $this->microsoft_email;
    }
}
