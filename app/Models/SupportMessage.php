<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'is_staff',
    ];

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorName(): string
    {
        if ($this->is_staff) {
            return $this->user?->displayName() ?? 'فريق الدعم';
        }

        return $this->user?->displayName()
            ?? $this->ticket?->contact_name
            ?? 'العميل';
    }
}
