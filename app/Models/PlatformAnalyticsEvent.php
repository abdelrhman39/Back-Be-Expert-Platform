<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type',
        'visit_id',
        'visitor_hash',
        'user_id',
        'path',
        'route_name',
        'referrer_host',
        'country_code',
        'country_name',
        'region',
        'city',
        'device_type',
        'browser',
        'operating_system',
        'metadata',
        'occurred_on',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_on' => 'date',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
