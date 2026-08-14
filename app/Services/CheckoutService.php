<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Support\MoyasarSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    public function createOrderFromCart(User $user, string $paymentMethod): Order
    {
        $items = $this->cart->refreshPrices();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['السلة فارغة.'],
            ]);
        }

        $total = (float) $items->sum('price_snapshot');
        $isFree = $total <= 0;

        return DB::transaction(function () use ($user, $items, $total, $paymentMethod, $isFree) {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'reference' => $this->generateReference(),
                'total' => $total,
                'currency' => MoyasarSettings::currency(),
                'status' => $isFree ? 'paid' : 'pending_payment',
                'payment_method' => $paymentMethod,
                'gateway' => $isFree
                    ? 'free'
                    : (\App\Support\PaymentMethods::isBnplGateway($paymentMethod) ? $paymentMethod : null),
                'paid_at' => $isFree ? now() : null,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'course_id' => $item->course_id,
                    'training_id' => $item->training_id,
                    'delivery_type' => $item->delivery_type,
                    'price' => $item->price_snapshot,
                    'course_title' => $item->course_title,
                    'course_image' => $item->course_image,
                ]);
            }

            $this->cart->clear();

            $order = $order->load('items');

            if ($isFree) {
                app(EnrollmentService::class)->syncFromOrder($order);
            }

            return $order;
        });
    }

    private function generateReference(): string
    {
        do {
            $reference = 'BX-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
