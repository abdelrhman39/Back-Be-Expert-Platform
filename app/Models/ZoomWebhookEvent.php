<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoomWebhookEvent extends Model
{
    protected $fillable = [
        'event_id', 'event_type', 'meeting_id', 'payload', 'status',
        'attempts', 'last_error', 'processed_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'processed_at' => 'datetime'];
    }
}
