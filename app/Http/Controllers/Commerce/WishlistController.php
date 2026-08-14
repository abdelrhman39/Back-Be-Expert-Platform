<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request, WishlistService $wishlist, CartService $cart): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'min:1'],
            'course_title' => ['nullable', 'string', 'max:255'],
            'course_image' => ['nullable', 'string', 'max:500'],
            'course_slug' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $wishlist->toggleItem([
            'course_id' => (int) $validated['course_id'],
            'course_title' => $validated['course_title'] ?? null,
            'course_image' => $validated['course_image'] ?? null,
            'course_slug' => $validated['course_slug'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'action' => $result['action'],
            'in_wishlist' => $result['in_wishlist'],
            'cart_count' => $cart->count(),
            'wishlist_count' => $wishlist->count(),
        ]);
    }
}
