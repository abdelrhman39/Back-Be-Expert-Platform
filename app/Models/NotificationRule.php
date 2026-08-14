<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationRule extends Model
{
    protected $fillable = [
        'type',
        'name_ar',
        'name_en',
        'is_enabled',
        'trigger_kind',
        'offset_minutes',
        'channels',
        'audience',
        'quiet_hours_respect',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'channels' => 'array',
            'quiet_hours_respect' => 'boolean',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    /** @return array<int, string> */
    public function channelList(): array
    {
        return $this->channels ?: ['database'];
    }

    public function usesMail(): bool
    {
        return in_array('mail', $this->channelList(), true);
    }

    public function usesDatabase(): bool
    {
        return in_array('database', $this->channelList(), true);
    }
}
