@php
    $courseId = $courseId ?? null;
    $inWishlist = $courseId ? app(\App\Services\WishlistService::class)->isInWishlist($courseId) : false;
@endphp

@if ($courseId)
    <button type="button"
        class="course-card-actions__wish makeWishlist @if($inWishlist) active @endif"
        data-course_id="{{ $courseId }}"
        title="إضافة إلى المفضلة"
        aria-label="إضافة إلى المفضلة">
        @include('partials.catalog.heart-icon', ['active' => $inWishlist])
    </button>
@endif
