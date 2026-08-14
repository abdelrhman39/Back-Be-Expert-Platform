<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'notification_rule_id',
        'notifiable_type',
        'notifiable_id',
        'subject_type',
        'subject_id',
        'channel',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(NotificationRule::class, 'notification_rule_id');
    }
}
