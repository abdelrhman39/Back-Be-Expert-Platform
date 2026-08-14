<?php

namespace App\Models;

use App\Support\PaymentMethods;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'total',
        'currency',
        'status',
        'payment_method',
        'payment_ref',
        'gateway',
        'gateway_payment_id',
        'paid_at',
        'installment_schedule_id',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function installmentSchedule(): BelongsTo
    {
        return $this->belongsTo(InstallmentSchedule::class);
    }

    public function isInstallmentPayment(): bool
    {
        return $this->installment_schedule_id !== null;
    }

    public function needsOnlinePayment(): bool
    {
        return $this->status === 'pending_payment'
            && PaymentMethods::usesMoyasar($this->payment_method ?? '');
    }

    public function isAwaitingBankTransfer(): bool
    {
        return $this->status === 'pending_payment'
            && PaymentMethods::isOffline($this->payment_method ?? '');
    }
}
