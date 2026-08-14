<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CatalogCourse;
use App\Services\CartService;
use App\Services\CourseEnrollmentService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(
        Request $request,
        CartService $cart,
        WishlistService $wishlist,
        CourseEnrollmentService $enrollment,
    ): JsonResponse {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:catalog_courses,id'],
            'course_type' => ['required', 'string', 'in:online,onsite,offline'],
            'training_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $course = CatalogCourse::query()
            ->whereKey($validated['course_id'])
            ->where('status', 'published')
            ->firstOrFail();
        $deliveryType = $enrollment->resolveDeliveryType($course, $validated['course_type']);
        $price = $enrollment->resolvePrice($course, $deliveryType);

        $result = $cart->toggleItem([
            'course_id' => $course->id,
            'delivery_type' => $deliveryType,
            'training_id' => isset($validated['training_id']) ? (int) $validated['training_id'] : null,
            'course_title' => $course->displayTitle(),
            'course_image' => $course->image,
            'course_slug' => $course->showSlug(),
            'price' => $price,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'action' => $result['action'],
            'in_cart' => $result['in_cart'],
            'cart_count' => $cart->count(),
            'wishlist_count' => $wishlist->count(),
        ]);
    }
}
