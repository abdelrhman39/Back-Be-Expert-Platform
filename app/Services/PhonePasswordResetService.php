<?php

namespace App\Services;

use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhonePasswordResetService
{
    private const CACHE_PREFIX = 'pwd_reset_otp:';

    private const VERIFIED_PREFIX = 'pwd_reset_verified:';

    public function sendOtp(string $phone): bool
    {
        $e164 = PhoneNormalizer::toE164($phone);
        $user = User::query()->where('phone', $e164)->first();

        if (! $user) {
            return false;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(self::CACHE_PREFIX.$e164, $otp, now()->addMinutes(10));

        if (app()->environment('local')) {
            Log::info("Password reset OTP for {$e164}: {$otp}");
        }

        return true;
    }

    public function verifyOtp(string $phone, string $otp): bool
    {
        $e164 = PhoneNormalizer::toE164($phone);
        $cached = Cache::get(self::CACHE_PREFIX.$e164);

        if (! $cached || ! hash_equals((string) $cached, trim($otp))) {
            return false;
        }

        Cache::forget(self::CACHE_PREFIX.$e164);
        Cache::put(self::VERIFIED_PREFIX.$e164, Str::random(40), now()->addMinutes(15));

        return true;
    }

    public function resetPassword(string $phone, string $password): void
    {
        $e164 = PhoneNormalizer::toE164($phone);

        if (! Cache::pull(self::VERIFIED_PREFIX.$e164)) {
            throw ValidationException::withMessages([
                'otp' => ['انتهت صلاحية التحقق. أعد إرسال الرمز.'],
            ]);
        }

        $user = User::query()->where('phone', $e164)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['لم يتم العثور على حساب مرتبط بهذا الرقم.'],
            ]);
        }

        $user->update(['password' => $password]);
    }
}
