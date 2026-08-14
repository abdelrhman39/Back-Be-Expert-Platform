<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        protected AuditLogService $audit,
    ) {}

    /** @return Collection<int, RefundRequest> */
    public function forUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return RefundRequest::query()
            ->with('order')
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    public function request(User $user, Order $order, string $reason): RefundRequest
    {
        if ($order->user_id !== $user->id) {
            throw ValidationException::withMessages(['order' => 'الطلب غير مرتبط بحسابك.']);
        }

        if ($order->status !== 'paid') {
            throw ValidationException::withMessages(['order' => 'يمكن طلب الاسترداد للطلبات المدفوعة فقط.']);
        }

        $hasOpen = RefundRequest::query()
            ->where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasOpen) {
            throw ValidationException::withMessages(['order' => 'يوجد طلب استرداد مفتوح لهذا الطلب.']);
        }

        $refund = RefundRequest::query()->create([
            'reference_no' => $this->nextReference(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $order->total,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        $this->audit->log(
            action: 'refund.requested',
            descriptionAr: 'طلب استرداد جديد '.$refund->reference_no.' للطلب '.$order->reference,
            group: 'refunds',
            actor: $user,
            subject: $refund,
            subjectLabel: $refund->reference_no,
            newValues: ['amount' => (string) $refund->amount, 'order' => $order->reference],
        );

        return $refund;
    }

    public function approve(RefundRequest $refund, User $reviewer, ?string $notes = null): RefundRequest
    {
        $oldStatus = $refund->status;

        $refund->update([
            'status' => 'approved',
            'admin_notes' => $notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->audit->log(
            action: 'refund.approved',
            descriptionAr: 'الموافقة على طلب الاسترداد '.$refund->reference_no,
            group: 'refunds',
            actor: $reviewer,
            subject: $refund,
            subjectLabel: $refund->reference_no,
            oldValues: ['status' => $oldStatus],
            newValues: array_filter([
                'status' => 'approved',
                'admin_notes' => $notes,
            ]),
        );

        return $refund->fresh();
    }

    public function reject(RefundRequest $refund, User $reviewer, string $reason): RefundRequest
    {
        $oldStatus = $refund->status;

        $refund->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->audit->log(
            action: 'refund.rejected',
            descriptionAr: 'رفض طلب الاسترداد '.$refund->reference_no,
            group: 'refunds',
            actor: $reviewer,
            subject: $refund,
            subjectLabel: $refund->reference_no,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'rejected', 'admin_notes' => $reason],
        );

        return $refund->fresh();
    }

    public function markProcessed(RefundRequest $refund, User $reviewer): RefundRequest
    {
        $refund->loadMissing('order');
        $oldStatus = $refund->status;
        $oldOrderStatus = $refund->order?->status;

        $refund->update([
            'status' => 'processed',
            'processed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => $refund->reviewed_at ?? now(),
        ]);

        if ($refund->order) {
            $refund->order->update(['status' => 'refunded']);
        }

        $this->audit->log(
            action: 'refund.processed',
            descriptionAr: 'تنفيذ الاسترداد '.$refund->reference_no,
            group: 'refunds',
            actor: $reviewer,
            subject: $refund,
            subjectLabel: $refund->reference_no,
            oldValues: array_filter([
                'status' => $oldStatus,
                'order_status' => $oldOrderStatus,
            ]),
            newValues: [
                'status' => 'processed',
                'order_status' => 'refunded',
            ],
        );

        return $refund->fresh();
    }

    /** @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, RefundRequest> */
    public function adminList(?string $status = null, ?string $search = null, int $perPage = 20)
    {
        return RefundRequest::query()
            ->with(['user', 'order'])
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(filled($search), function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($o) => $o->where('reference', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name_ar', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function openForOrder(Order $order): ?RefundRequest
    {
        return RefundRequest::query()
            ->where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved', 'processed'])
            ->latest()
            ->first();
    }

    protected function nextReference(): string
    {
        do {
            $ref = 'RF-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (RefundRequest::query()->where('reference_no', $ref)->exists());

        return $ref;
    }
}
