<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentDunningExecution extends Model
{
    protected $fillable = [
        'policy_id',
        'step_id',
        'schedule_id',
        'contract_id',
        'status',
        'executed_at',
        'actions_applied',
        'message_sent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'actions_applied' => 'array',
            'message_sent' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(InstallmentDunningPolicy::class, 'policy_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(InstallmentDunningStep::class, 'step_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InstallmentSchedule::class, 'schedule_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(InstallmentContract::class, 'contract_id');
    }
}
