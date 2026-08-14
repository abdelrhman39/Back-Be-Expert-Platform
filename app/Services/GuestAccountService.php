<?php

namespace App\Services;

use App\Mail\AccountCredentialsMail;
use App\Models\User;
use App\Support\AdminPermissions;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GuestAccountService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly WishlistService $wishlist,
    ) {}

    /**
     * Update profile fields for an already-authenticated user during checkout/enrollment.
     */
    public function syncAuthenticatedForCheckout(User $user, string $name, string $phone): User
    {
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => 'هذا الحساب غير نشط. تواصل مع الدعم.',
            ]);
        }

        $name = trim($name);
        $phone = PhoneNormalizer::toE164($phone);
        $updates = [];

        if ($name !== '' && ! filled($user->name)) {
            $updates['name'] = $name;
            $updates['name_ar'] = $user->name_ar ?: $name;
        }

        if (! filled($user->phone)) {
            $this->assertPhoneAvailable($phone, $user);
            $updates['phone'] = $phone;
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        if (! Auth::guard('portal')->check() && ! AdminPermissions::canAccessAdmin($user)) {
            $this->loginPortal($user);
        }

        return $user->fresh();
    }

    /**
     * @param  array{name: string, email: string, phone: string}  $data
     * @return array{user: User, created: bool, password: ?string}
     */
    public function registerAndLogin(array $data, bool $sendCredentialsEmail = true): array
    {
        $name = trim($data['name']);
        $email = strtolower(trim($data['email']));
        $phone = PhoneNormalizer::toE164($data['phone']);
        $cartSnapshot = $this->cartSnapshot();

        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            if ($existing->status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => 'هذا الحساب غير نشط. تواصل مع الدعم.',
                ]);
            }

            if ($existing->phone && $existing->phone !== $phone) {
                throw ValidationException::withMessages([
                    'email' => 'البريد مسجّل مسبقاً. سجّل الدخول للمتابعة أو استخدم بريداً آخر.',
                ]);
            }

            if (! filled($existing->phone)) {
                $this->assertPhoneAvailable($phone, $existing);
            }

            $existing->update([
                'name' => $existing->name ?: $name,
                'name_ar' => $existing->name_ar ?: $name,
                'phone' => $existing->phone ?: $phone,
            ]);

            $this->loginPortal($existing);

            return [
                'user' => $existing->fresh(),
                'created' => false,
                'password' => null,
            ];
        }

        $this->assertPhoneAvailable($phone);

        $plainPassword = $this->generateReadablePassword();

        $user = User::query()->create([
            'name' => $name,
            'name_ar' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $plainPassword,
            'locale' => app()->getLocale(),
            'status' => 'active',
            'role' => 'student',
        ]);

        $this->loginPortal($user);

        if ($sendCredentialsEmail) {
            $this->sendCredentialsEmail($user, $plainPassword, $cartSnapshot);
        }

        return [
            'user' => $user->fresh(),
            'created' => true,
            'password' => $plainPassword,
        ];
    }

    /**
     * @param  array{lines: array<int, array{title: string, price: float|null}>, total: float}|null  $cartSnapshot
     */
    public function sendCredentialsEmail(User $user, string $plainPassword, ?array $cartSnapshot = null): void
    {
        if (! filled($user->email)) {
            return;
        }

        $locale = $user->locale ?: app()->getLocale();
        $items = $cartSnapshot ?? $this->cartSnapshot();

        try {
            Mail::to($user->email)->send(new AccountCredentialsMail(
                user: $user,
                plainPassword: $plainPassword,
                cartItems: $items['lines'],
                cartTotal: $items['total'],
                loginUrl: route('login', ['locale' => $locale]),
                checkoutUrl: $items['lines'] !== []
                    ? route('checkout', ['locale' => $locale])
                    : '',
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function generateReadablePassword(int $length = 10): string
    {
        $length = max(8, min(16, $length));

        return Str::password(
            length: $length,
            letters: true,
            numbers: true,
            symbols: false,
            spaces: false,
        );
    }

    protected function assertPhoneAvailable(string $phone, ?User $except = null): void
    {
        $query = User::query()->where('phone', $phone);

        if ($except) {
            $query->where('id', '!=', $except->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الجوال مسجّل مسبقاً لحساب آخر. سجّل الدخول بالحساب المرتبط بهذا الرقم أو استخدم رقماً آخر.',
            ]);
        }
    }

    protected function loginPortal(User $user): void
    {
        Auth::guard('portal')->login($user, remember: true);
        Auth::shouldUse('portal');
        $this->cart->mergeGuestCartOnLogin($user);
        $this->wishlist->mergeGuestWishlistOnLogin($user);
    }

    /** @return array{lines: array<int, array{title: string, price: float|null}>, total: float} */
    public function cartSnapshot(): array
    {
        $items = $this->cart->items();

        $lines = $items->map(fn ($item) => [
            'title' => (string) ($item->course_title ?: 'دورة تدريبية'),
            'price' => $item->price_snapshot !== null ? (float) $item->price_snapshot : null,
        ])->values()->all();

        return [
            'lines' => $lines,
            'total' => (float) $this->cart->total(),
        ];
    }
}
