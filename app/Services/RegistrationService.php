<?php

namespace App\Services;

use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegistrationService
{
    public function register(array $data): User
    {
        $phone = PhoneNormalizer::toE164($data['phone']);
        $email = strtolower(trim($data['email']));

        $user = User::query()->create([
            'name' => $data['name_ar'],
            'name_ar' => $data['name_ar'],
            'email' => $email,
            'phone' => $phone,
            'national_id' => $data['national_id'],
            'password' => $data['password'],
            'locale' => app()->getLocale(),
            'status' => 'active',
            'role' => 'student',
        ]);

        Auth::guard('portal')->login($user);

        app(CartService::class)->mergeGuestCartOnLogin($user);
        app(WishlistService::class)->mergeGuestWishlistOnLogin($user);
        app(PlatformAnalyticsRecorder::class)->recordRegistration($user, request());
        if (Schema::hasTable('crm_contacts')) {
            try {
                app(CrmContactSyncService::class)->syncUser($user);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $user;
    }

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'digits:10', 'regex:/^[12]\d{9}$/', 'unique:users,national_id'],
            'phone' => ['required', 'string', 'min:9'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public static function attributeNames(): array
    {
        return [
            'name_ar' => 'الاسم',
            'national_id' => 'رقم الهوية',
            'phone' => 'رقم الجوال',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'terms' => 'الشروط والأحكام',
        ];
    }

    public static function validatePasswordNotMatchingIdentity(array $data): void
    {
        if ($data['password'] === $data['national_id']) {
            throw ValidationException::withMessages([
                'password' => ['كلمة المرور يجب ألا تطابق رقم الهوية.'],
            ]);
        }

        $phoneDigits = preg_replace('/\D+/', '', $data['phone']) ?? '';

        if ($phoneDigits !== '' && str_contains($data['password'], $phoneDigits)) {
            throw ValidationException::withMessages([
                'password' => ['كلمة المرور يجب ألا تحتوي على رقم الجوال.'],
            ]);
        }
    }
}
