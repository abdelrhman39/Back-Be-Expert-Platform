<?php

use App\Models\User;
use App\Services\PlatformAnalyticsRecorder;
use App\Support\AdminPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin-guest')]
#[Title('تسجيل الدخول | لوحة تحكم مركز التعلم المستمر')]
class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check() && AdminPermissions::canAccessAdmin(Auth::user())) {
            $this->redirect($this->landingRoute(Auth::user()));
        }
    }

    public function login(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->addError('credentials', 'البريد الإلكتروني أو كلمة المرور غير صحيحة.');

            return;
        }

        if ($user->status !== 'active') {
            $this->addError('credentials', 'الحساب غير مفعّل.');

            return;
        }

        if ($user->isLocked()) {
            $this->addError('credentials', 'الحساب مقفل مؤقتاً. حاول لاحقاً أو تواصل مع مدير النظام.');

            return;
        }

        if (! AdminPermissions::canAccessAdmin($user)) {
            $this->addError('credentials', 'هذا الحساب لا يملك صلاحية دخول لوحة الإدارة.');

            return;
        }

        Auth::login($user, $this->remember);

        $user->forceFill(['last_login_at' => now()])->save();
        app(PlatformAnalyticsRecorder::class)->recordLogin($user, request(), 'web');

        $this->redirect($this->landingRoute($user));
    }

    private function landingRoute(User $user): string
    {
        return $user->canAdmin('dashboard.view')
            ? route('admin.dashboard')
            : route('admin.crm');
    }
};
?>

<div class="admin-login-page">
<aside class="admin-login-brand" aria-hidden="false">
    <div class="brand-logos">
        @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
        <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_FOOTER) }}" class="{{ \App\Support\LogoSettings::cssClass(\App\Support\LogoSettings::KEY_FOOTER) }}" alt="{{ platform_name('ar') }}">
        @endif
    </div>
    <h1>لوحة تحكم المنصة</h1>
    <p>إدارة البرامج التدريبية، المتدربين، الطلبات، والمحتوى — {{ platform_name('ar') }} · {{ platform_org('ar') }}.</p>
    <ul>
        <li>واجهة عربية كاملة (RTL)</li>
        <li>صلاحيات إدارية آمنة</li>
        <li>متابعة التسجيلات والدورات</li>
    </ul>
</aside>

<main class="admin-login-main">
    <div class="admin-login-card">
        <div class="admin-login-card__top">
            <h2>تسجيل الدخول</h2>
            <p>أدخل بيانات حساب المسؤول للمتابعة</p>
        </div>

        @error('credentials')
            <div class="admin-alert admin-alert--error" role="alert">{{ $message }}</div>
        @enderror

        <form wire:submit="login" novalidate>
            <div class="admin-field">
                <label for="admin-email">البريد الإلكتروني</label>
                <div class="admin-input-wrap">
                    <svg class="admin-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 6h16v12H4V6z"/><path d="m4 7 8 6 8-6"/>
                    </svg>
                    <input type="email" id="admin-email" wire:model="email" class="admin-input @error('email') is-invalid @enderror" placeholder="admin@example.com" autocomplete="username" required>
                </div>
                @error('email')<span class="admin-field-hint" style="color:#b42318">{{ $message }}</span>@enderror
            </div>

            <div class="admin-field">
                <label for="admin-password">كلمة المرور</label>
                <div class="admin-input-wrap">
                    <svg class="admin-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 118 0v3"/>
                    </svg>
                    <input type="password" id="admin-password" wire:model="password" class="admin-input @error('password') is-invalid @enderror" placeholder="••••••••" autocomplete="current-password" required>
                </div>
                @error('password')<span class="admin-field-hint" style="color:#b42318">{{ $message }}</span>@enderror
            </div>

            <div class="admin-login-options">
                <label class="admin-check">
                    <input type="checkbox" wire:model="remember">
                    تذكرني
                </label>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" wire:loading.attr="disabled">
                <span wire:loading wire:target="login" class="spinner" aria-hidden="true"></span>
                <span class="btn-text" wire:loading.remove wire:target="login">دخول لوحة التحكم</span>
                <span class="btn-text" wire:loading wire:target="login">جاري الدخول…</span>
            </button>
        </form>

        <p class="admin-demo-note">
            بيئة تجريبية: استخدم <code>admin@local.invalid</code> وكلمة المرور <code>Admin@123</code>
        </p>

        <div class="admin-login-footer">
            <a href="{{ route('home', ['locale' => 'ar']) }}">← العودة إلى الموقع</a>
        </div>
    </div>
</main>
</div>
