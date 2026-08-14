<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentDunningStep extends Model
{
    protected $fillable = [
        'policy_id',
        'sort_order',
        'name',
        'admin_notes',
        'enabled',
        'trigger_offset_days',
        'trigger_hour',
        'actions',
        'email_enabled',
        'email_subject',
        'email_body',
        'channels',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'actions' => 'array',
            'channels' => 'array',
            'trigger_offset_days' => 'integer',
            'trigger_hour' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InstallmentDunningPolicy::class, 'policy_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(InstallmentDunningExecution::class, 'step_id');
    }

    /** @return list<string> */
    public function actionList(): array
    {
        return array_values(array_filter($this->actions ?? []));
    }

    /** @return list<string> */
    public function channelList(): array
    {
        $channels = $this->channels ?? ['mail', 'database'];

        return array_values(array_filter($channels));
    }

    public function triggerLabel(): string
    {
        $days = (int) $this->trigger_offset_days;
        $hour = $this->trigger_hour;

        if ($days < 0) {
            $label = 'قبل الاستحقاق بـ '.abs($days).' يوم';
        } elseif ($days === 0) {
            $label = 'يوم الاستحقاق / عند التأخر';
        } else {
            $label = 'بعد الاستحقاق بـ '.$days.' يوم';
        }

        if ($hour !== null) {
            $label .= ' · الساعة '.sprintf('%02d:00', $hour);
        }

        return $label;
    }
}
