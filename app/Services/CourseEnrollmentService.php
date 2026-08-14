<?php

namespace App\Services;

use App\Models\CatalogCourse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CourseEnrollmentService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly GuestAccountService $guestAccounts,
    ) {}

    public function enroll(CatalogCourse $course, string $name, string $email, string $phone, string $deliveryType): User
    {
        abort_unless($course->status === 'published', 404);

        $deliveryType = $this->resolveDeliveryType($course, $deliveryType);
        $price = $this->resolvePrice($course, $deliveryType);

        if ($price === null) {
            throw ValidationException::withMessages([
                'deliveryType' => 'لا يتوفر سعر لهذا البرنامج.',
            ]);
        }

        $authUser = Auth::guard('portal')->user() ?? Auth::guard('web')->user();
        $createdPassword = null;

        if ($authUser instanceof User) {
            $user = $this->guestAccounts->syncAuthenticatedForCheckout($authUser, $name, $phone);
        } else {
            $result = $this->guestAccounts->registerAndLogin([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
            ], sendCredentialsEmail: false);

            $user = $result['user'];
            $createdPassword = $result['created'] ? $result['password'] : null;
        }

        $this->cart->clear();
        $this->cart->addItem([
            'course_id' => $course->id,
            'delivery_type' => $deliveryType,
            'price' => $price,
            'course_title' => $course->displayTitle(),
            'course_image' => $course->image,
            'course_slug' => $course->showSlug(),
        ]);

        if ($createdPassword) {
            $this->guestAccounts->sendCredentialsEmail(
                $user,
                $createdPassword,
                $this->guestAccounts->cartSnapshot(),
            );
        }

        return $user;
    }

    public function resolveDeliveryType(CatalogCourse $course, string $deliveryType): string
    {
        $deliveryType = $deliveryType === 'offline' ? 'onsite' : $deliveryType;
        $available = $course->availableDeliveryTypes();

        if ($available === []) {
            return $course->allowsOnsite() ? 'onsite' : 'online';
        }

        if (count($available) === 1) {
            return $available[0];
        }

        if (! in_array($deliveryType, $available, true)) {
            throw ValidationException::withMessages([
                'deliveryType' => 'نوع التدريب المحدد غير متاح لهذا البرنامج.',
            ]);
        }

        return $deliveryType;
    }

    public function resolvePrice(CatalogCourse $course, string $deliveryType): ?float
    {
        $deliveryType = $this->resolveDeliveryType($course, $deliveryType);

        $price = $deliveryType === 'online'
            ? $course->price_online
            : $course->price_onsite;

        if ($price === null) {
            $price = $course->price_online ?? $course->price_onsite;
        }

        return $price !== null ? (float) $price : null;
    }
}
