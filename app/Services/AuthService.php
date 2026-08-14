<?php

namespace App\Services;

use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function attempt(string $method, array $credentials, bool $remember): void
    {
        $user = $this->findUser($method, $credentials);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->recordFailedAttempt($user);

            throw ValidationException::withMessages([
                'credentials' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        if ($user->status !== 'active') {
            if (app(InstallmentDunningService::class)->userHasLoginLock($user)) {
                throw ValidationException::withMessages([
                    'credentials' => ['تم قفل الحساب مؤقتاً بسبب تأخر سداد الأقساط. يرجى التواصل مع الدعم أو السداد عبر قنوات التحصيل المتاحة.'],
                ]);
            }

            throw ValidationException::withMessages([
                'credentials' => ['الحساب غير مفعّل. يرجى التواصل مع الدعم.'],
            ]);
        }

        if ($user->locked_until?->isFuture()) {
            throw ValidationException::withMessages([
                'credentials' => ['الحساب مقفل مؤقتاً. حاول لاحقاً.'],
            ]);
        }

        Auth::guard('portal')->login($user, $remember);

        app(CartService::class)->mergeGuestCartOnLogin($user);
        app(WishlistService::class)->mergeGuestWishlistOnLogin($user);

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_method' => $method,
        ])->save();

        app(PlatformAnalyticsRecorder::class)->recordLogin($user, request(), 'portal');
    }

    public function findUser(string $method, array $credentials): ?User
    {
        return match ($method) {
            'national_id' => User::query()
                ->where('national_id', $credentials['national_id'])
                ->first(),
            'phone' => User::query()
                ->where('phone', PhoneNormalizer::toE164($credentials['phone']))
                ->first(),
            'email' => User::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($credentials['email']))])
                ->first(),
            default => null,
        };
    }

    protected function recordFailedAttempt(?User $user): void
    {
        if (! $user) {
            return;
        }

        $attempts = $user->failed_login_attempts + 1;
        $lockedUntil = $attempts >= 5 ? now()->addMinutes(15) : null;

        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'locked_until' => $lockedUntil,
        ])->save();
    }
}
