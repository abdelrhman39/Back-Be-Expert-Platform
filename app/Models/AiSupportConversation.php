<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AiSupportConversation extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'locale',
        'audience',
        'ip_hash',
        'user_agent',
        'page_url',
        'status',
        'message_count',
        'last_message_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'last_message_at' => 'datetime',
            'message_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $conversation): void {
            if (! filled($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiSupportMessage::class, 'conversation_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
