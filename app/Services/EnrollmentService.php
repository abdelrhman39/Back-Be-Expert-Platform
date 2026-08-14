<?php

namespace App\Services;

use App\Models\CatalogEnrollment;
use App\Models\Order;
use App\Models\User;

class EnrollmentService
{
    public function syncFromOrder(Order $order): void
    {
        if ($order->status !== 'paid') {
            return;
        }

        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->course_id) {
                continue;
            }

            CatalogEnrollment::query()->firstOrCreate(
                [
                    'user_id' => $order->user_id,
                    'course_id' => $item->course_id,
                    'order_item_id' => $item->id,
                ],
                [
                    'order_id' => $order->id,
                    'delivery_type' => $item->delivery_type ?? 'online',
                    'status' => 'active',
                    'progress_percent' => 0,
                    'enrolled_at' => $order->paid_at ?? now(),
                ],
            );
        }
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, CatalogEnrollment> */
    public function forUser(User $user)
    {
        return CatalogEnrollment::query()
            ->with(['course', 'orderItem'])
            ->where('user_id', $user->id)
            ->latest('enrolled_at')
            ->get();
    }

    /** @return array{total: int, active: int, completed: int, avg_progress: float} */
    public function statsForUser(User $user): array
    {
        $enrollments = CatalogEnrollment::query()->where('user_id', $user->id)->get();

        return [
            'total' => $enrollments->count(),
            'active' => $enrollments->where('status', 'active')->count(),
            'completed' => $enrollments->where('status', 'completed')->count(),
            'avg_progress' => $enrollments->isEmpty()
                ? 0.0
                : round($enrollments->avg('progress_percent'), 1),
        ];
    }

    public function syncAllPaidOrders(): int
    {
        $count = 0;

        Order::query()
            ->where('status', 'paid')
            ->with('items')
            ->orderBy('id')
            ->each(function (Order $order) use (&$count) {
                $before = CatalogEnrollment::query()->where('order_id', $order->id)->count();
                $this->syncFromOrder($order);
                $after = CatalogEnrollment::query()->where('order_id', $order->id)->count();

                if ($after > $before) {
                    $count += ($after - $before);
                }
            });

        return $count;
    }
}
