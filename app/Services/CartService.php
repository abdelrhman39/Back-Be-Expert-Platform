<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CatalogCourse;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function resolveCart(): Cart
    {
        $user = Auth::guard('portal')->user() ?? Auth::user();

        if ($user) {
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null],
            );
        }

        return Cart::firstOrCreate(
            ['session_id' => $this->sessionId(), 'user_id' => null],
            ['session_id' => $this->sessionId()],
        );
    }

    public function count(): int
    {
        return $this->resolveCart()->items()->count();
    }

    public function items()
    {
        return $this->resolveCart()
            ->items()
            ->orderByDesc('created_at')
            ->get();
    }

    public function total(): float
    {
        return (float) $this->resolveCart()->items()->sum('price_snapshot');
    }

    /**
     * Rebuild all commercial fields from published catalog data.
     *
     * @return Collection<int, CartItem>
     */
    public function refreshPrices(): Collection
    {
        $items = $this->items();
        $courses = CatalogCourse::query()
            ->whereIn('id', $items->pluck('course_id'))
            ->where('status', 'published')
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $course = $courses->get($item->course_id);
            $price = match ($item->delivery_type) {
                'online' => $course?->price_online,
                'onsite' => $course?->price_onsite,
                default => null,
            };

            if (! $course || $price === null) {
                throw ValidationException::withMessages([
                    'cart' => 'إحدى الدورات في السلة لم تعد متاحة بطريقة التدريب المختارة.',
                ]);
            }

            $item->update([
                'price_snapshot' => $price,
                'course_title' => $course->displayTitle(),
                'course_image' => $course->image,
                'course_slug' => $course->showSlug(),
            ]);
        }

        return $items->map->fresh();
    }

    public function toggleItem(array $payload): array
    {
        $cart = $this->resolveCart();

        $existing = $cart->items()
            ->where('course_id', $payload['course_id'])
            ->where('delivery_type', $payload['delivery_type'])
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'action' => 'removed',
                'in_cart' => false,
                'message' => 'تمت إزالة الدورة من السلة',
            ];
        }

        $this->addItem($payload);

        return [
            'action' => 'added',
            'in_cart' => true,
            'message' => 'تمت إضافة الدورة إلى السلة',
        ];
    }

    public function addItem(array $payload): void
    {
        $cart = $this->resolveCart();

        $cart->items()->updateOrCreate(
            [
                'course_id' => $payload['course_id'],
                'delivery_type' => $payload['delivery_type'],
            ],
            [
                'training_id' => $payload['training_id'] ?? null,
                'price_snapshot' => $payload['price'] ?? 0,
                'course_title' => $payload['course_title'] ?? null,
                'course_image' => $payload['course_image'] ?? null,
                'course_slug' => $payload['course_slug'] ?? null,
            ],
        );
    }

    public function removeItem(int $cartItemId): bool
    {
        $cart = $this->resolveCart();

        return (bool) $cart->items()->whereKey($cartItemId)->delete();
    }

    public function removeByCourse(int $courseId, string $deliveryType): bool
    {
        $cart = $this->resolveCart();

        return (bool) $cart->items()
            ->where('course_id', $courseId)
            ->where('delivery_type', $deliveryType)
            ->delete();
    }

    public function clear(): void
    {
        $cart = $this->resolveCart();
        $cart->items()->delete();
    }

    public function isInCart(int $courseId, ?string $deliveryType = null): bool
    {
        $query = $this->resolveCart()->items()->where('course_id', $courseId);

        if ($deliveryType !== null) {
            $query->where('delivery_type', $deliveryType);
        }

        return $query->exists();
    }

    public function mergeGuestCartOnLogin(User $user): void
    {
        $sessionId = $this->sessionId();

        $guestCart = Cart::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['session_id' => null],
        );

        foreach ($guestCart->items as $item) {
            $userCart->items()->updateOrCreate(
                [
                    'course_id' => $item->course_id,
                    'delivery_type' => $item->delivery_type,
                ],
                [
                    'training_id' => $item->training_id,
                    'price_snapshot' => $item->price_snapshot,
                    'course_title' => $item->course_title,
                    'course_image' => $item->course_image,
                    'course_slug' => $item->course_slug,
                ],
            );
        }

        $guestCart->items()->delete();
        $guestCart->delete();
    }

    private function sessionId(): string
    {
        return session()->getId();
    }
}
