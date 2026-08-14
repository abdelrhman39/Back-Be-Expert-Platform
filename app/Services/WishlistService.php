<?php

namespace App\Services;

use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    public function count(): int
    {
        return $this->baseQuery()->count();
    }

    public function items()
    {
        return $this->baseQuery()
            ->orderByDesc('created_at')
            ->get();
    }

    public function toggleItem(array $payload): array
    {
        $existing = $this->baseQuery()
            ->where('course_id', $payload['course_id'])
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'action' => 'removed',
                'in_wishlist' => false,
                'message' => 'تمت إزالة الدورة من المفضلة',
            ];
        }

        $attributes = [
            'course_id' => $payload['course_id'],
            'course_title' => $payload['course_title'] ?? null,
            'course_image' => $payload['course_image'] ?? null,
            'course_slug' => $payload['course_slug'] ?? null,
        ];

        if ($user = Auth::guard('portal')->user() ?? Auth::user()) {
            $attributes['user_id'] = $user->id;
            $attributes['session_id'] = null;
        } else {
            $attributes['user_id'] = null;
            $attributes['session_id'] = session()->getId();
        }

        WishlistItem::query()->create($attributes);

        return [
            'action' => 'added',
            'in_wishlist' => true,
            'message' => 'تمت إضافة الدورة إلى المفضلة',
        ];
    }

    public function removeItem(int $wishlistItemId): bool
    {
        return (bool) $this->baseQuery()->whereKey($wishlistItemId)->delete();
    }

    public function isInWishlist(int $courseId): bool
    {
        return $this->baseQuery()->where('course_id', $courseId)->exists();
    }

    public function mergeGuestWishlistOnLogin(User $user): void
    {
        $sessionId = session()->getId();

        $guestItems = WishlistItem::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $item) {
            WishlistItem::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $item->course_id,
                ],
                [
                    'session_id' => null,
                    'course_title' => $item->course_title,
                    'course_image' => $item->course_image,
                    'course_slug' => $item->course_slug,
                ],
            );
        }

        WishlistItem::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->delete();
    }

    private function baseQuery()
    {
        return $this->makeQuery();
    }

    private function makeQuery()
    {
        if ($user = Auth::guard('portal')->user() ?? Auth::user()) {
            return WishlistItem::query()->where('user_id', $user->id);
        }

        return WishlistItem::query()
            ->where('session_id', session()->getId())
            ->whereNull('user_id');
    }
}
