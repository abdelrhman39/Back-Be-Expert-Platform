<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'reference_code',
        'subject',
        'category',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_national_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'reference_code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function contactDisplayName(): string
    {
        return $this->contact_name ?: $this->user?->displayName() ?: '—';
    }
}
