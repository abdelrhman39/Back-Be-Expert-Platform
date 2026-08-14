<?php

namespace App\Models;

use App\Support\InstallmentOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentSchedule extends Model
{
    protected $fillable = [
        'contract_id',
        'sequence',
        'label',
        'amount',
        'percent',
        'due_date',
        'status',
        'paid_at',
        'order_id',
        'late_fee_amount',
        'waived_by',
        'waived_at',
        'admin_notes',
        'reminder_sent_at',
        'reminder_offsets_sent',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percent' => 'decimal:2',
            'late_fee_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'waived_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'reminder_offsets_sent' => 'array',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(InstallmentContract::class, 'contract_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class, 'schedule_id');
    }

    public function statusLabel(): string
    {
        return InstallmentOptions::scheduleStatusLabel($this->displayStatus());
    }

    public function displayStatus(): string
    {
        if ($this->status === 'pending' && $this->due_date->isPast()) {
            return 'overdue';
        }

        return $this->status;
    }

    public function isPayable(): bool
    {
        return in_array($this->displayStatus(), ['pending', 'overdue'], true);
    }

    public function totalDue(): float
    {
        return (float) $this->amount + (float) $this->late_fee_amount;
    }
}
