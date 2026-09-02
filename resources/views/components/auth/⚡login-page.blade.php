<?php

use App\Services\AuthService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('تسجيل الدخول | مركز التعلم المستمر')]
class extends Component
{
    public string $national_id = '';

    public string $phone = '';

    public string $email = '';

    public string $password_id = '';

    public string $password_phone = '';

    public string $password_email = '';

    public bool $remember_id = false;

    public bool $remember_phone = false;

    public bool $remember_email = false;

    public function loginByNationalId(AuthService $auth): void
    {
        $validated = $this->validate([
            'national_id' => ['required', 'digits:10'],
            'password_id' => ['required', 'string'],
        ], [], [
            'national_id' => 'رقم الهوية',
            'password_id' => 'كلمة السر',
        ]);

        try {
            $auth->attempt('national_id', [
                'national_id' => $validated['national_id'],
                'password' => $validated['password_id'],
            ], $this->remember_id);
        } catch (ValidationException $exception) {
            $this->addError('credentials', $exception->errors()['credentials'][0] ?? 'بيانات الدخول غير صحيحة.');

            return;
        }

        $locale = request()->route('locale') ?? 'ar';

        $this->redirect(route('profile', ['locale' => $locale]));
    }

    public function loginByPhone(AuthService $auth): void
    {
        $validated = $this->validate([
            'phone' => ['required', 'string', 'min:9'],
            'password_phone' => ['required', 'string'],
        ], [], [
            'phone' => 'رقم الجوال',
            'password_phone' => 'كلمة السر',
        ]);

        try {
            $auth->attempt('phone', [
                'phone' => $validated['phone'],
                'password' => $validated['password_phone'],
            ], $this->remember_phone);
        } catch (ValidationException $exception) {
            $this->addError('credentials', $exception->errors()['credentials'][0] ?? 'بيانات الدخول غير صحيحة.');

            return;
        }

        $locale = request()->route('locale') ?? 'ar';

        $this->redirect(route('profile', ['locale' => $locale]));
    }

    public function loginByEmail(AuthService $auth): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password_email' => ['required', 'string'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password_email' => 'كلمة السر',
        ]);

        try {
            $auth->attempt('email', [
                'email' => $validated['email'],
                'password' => $validated['password_email'],
            ], $this->remember_email);
        } catch (ValidationException $exception) {
            $this->addError('credentials', $exception->errors()['credentials'][0] ?? 'بيانات الدخول غير صحيحة.');

            return;
        }

        $locale = request()->route('locale') ?? 'ar';

        $this->redirect(route('profile', ['locale' => $locale]));
    }
};
?>

<div class="portal-bg-pattern auth-screen py-4 py-lg-5">
    <div class="container auth-container">
        <div class="auth-card portal-card card border-0 overflow-hidden">
            <div class="row g-0">

                {{-- اللوحة الترحيبية --}}
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="auth-side h-100">
                        <div class="auth-side__glow auth-side__glow--1"></div>
                        <div class="auth-side__glow auth-side__glow--2"></div>
                        <div class="auth-side__content">
                            @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                                <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="" class="auth-side__logo">
                            @endif
                            <h2 class="auth-side__title">مرحباً بعودتك</h2>
                            <p class="auth-side__text">سجّل دخولك لمتابعة رحلتك التعليمية في مركز التعلم المستمر.</p>

                            <ul class="auth-side__features">
                                <li>
                                    <span class="auth-side__icon"><i class="fa-solid fa-graduation-cap"></i></span>
                                    <span>دورات ودبلومات معتمدة</span>
                                </li>
                                <li>
                                    <span class="auth-side__icon"><i class="fa-solid fa-certificate"></i></span>
                                    <span>شهادات موثّقة قابلة للتحقق</span>
                                </li>
                                <li>
                                    <span class="auth-side__icon"><i class="fa-solid fa-clock"></i></span>
                                    <span>تعلّم في أي وقت ومن أي مكان</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- نموذج الدخول --}}
                <div class="col-lg-7">
                    <div class="auth-form-pane p-4 p-md-5">
                        <h1 class="h4 fw-bold mb-1">تسجيل الدخول إلى الحساب</h1>
                        <p class="text-muted small mb-4">يمكنك استخدام رقم الهوية الوطنية أو رقم الجوال للوصول إلى خدماتنا.</p>

                        @error('credentials')
                            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small" role="alert">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        @if (session('auth_message'))
                            <div class="alert alert-success d-flex align-items-center gap-2 py-2 small" role="alert">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>{{ session('auth_message') }}</span>
                            </div>
                        @endif

                        <div class="portal-tabs auth-tabs" role="tablist">
                            <button type="button" class="portal-tab is-active" role="tab" aria-selected="true" data-portal-tab="id">
                                <i class="fa-regular fa-id-card ms-1"></i> رقم الهوية
                            </button>
                            <button type="button" class="portal-tab" role="tab" aria-selected="false" data-portal-tab="mobile">
                                <i class="fa-solid fa-mobile-screen ms-1"></i> رقم الجوال
                            </button>
                            <button type="button" class="portal-tab" role="tab" aria-selected="false" data-portal-tab="email">
                                <i class="fa-regular fa-envelope ms-1"></i> البريد الإلكتروني
                            </button>
                        </div>

                        <div class="portal-panel" data-portal-panel="id" id="panel-id">
                            <form wire:submit="loginByNationalId" novalidate>
                                <div class="mb-3">
                                    <label class="form-label auth-label" for="login-id">رقم الهوية</label>
                                    <div class="auth-field input-group input-group-lg">
                                        <span class="input-group-text auth-field__icon"><i class="fa-regular fa-id-card"></i></span>
                                        <input type="text" class="form-control @error('national_id') is-invalid @enderror" id="login-id" wire:model="national_id" inputmode="numeric" maxlength="10" autocomplete="username" placeholder="أدخل رقم الهوية (10 أرقام)">
                                    </div>
                                    @error('national_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label auth-label" for="login-pass-id">كلمة السر</label>
                                    <div class="auth-field input-group input-group-lg">
                                        <span class="input-group-text auth-field__icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control @error('password_id') is-invalid @enderror" id="login-pass-id" wire:model="password_id" autocomplete="current-password" placeholder="••••••••">
                                        <button type="button" class="btn auth-field__toggle" id="toggle-pass-id" data-password-toggle="login-pass-id" aria-label="إظهار كلمة المرور" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    @error('password_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-id" wire:model="remember_id">
                                        <label class="form-check-label small" for="remember-id">تذكرني</label>
                                    </div>
                                    <a href="{{ route('password.request', ['locale' => app()->getLocale()]) }}" class="small text-primary fw-semibold text-decoration-none">نسيت كلمة السر؟</a>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 auth-submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="loginByNationalId"><i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>تسجيل الدخول</span>
                                    <span wire:loading wire:target="loginByNationalId"><span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>جاري الدخول…</span>
                                </button>
                            </form>
                        </div>

                        <div class="portal-panel" data-portal-panel="mobile" id="panel-mobile" hidden>
                            <form wire:submit="loginByPhone" novalidate>
                                <div class="mb-3">
                                    <label class="form-label auth-label" for="login-mobile">رقم الجوال</label>
                                    <div class="auth-field input-group input-group-lg">
                                        <span class="input-group-text auth-field__prefix" dir="ltr">🇸🇦 +966</span>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="login-mobile" wire:model="phone" inputmode="numeric" autocomplete="tel" placeholder="5xxxxxxxx">
                                    </div>
                                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label auth-label" for="login-pass-m">كلمة السر</label>
                                    <div class="auth-field input-group input-group-lg">
                                        <span class="input-group-text auth-field__icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control @error('password_phone') is-invalid @enderror" id="login-pass-m" wire:model="password_phone" autocomplete="current-password" placeholder="••••••••">
                                        <button type="button" class="btn auth-field__toggle" data-password-toggle="login-pass-m" aria-label="إظهار كلمة المرور" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    @error('password_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-m" wire:model="remember_phone">
                                        <label class="form-check-label small" for="remember-m">تذكرني</label>
                                    </div>
                                    <a href="{{ route('password.request', ['locale' => app()->getLocale()]) }}" class="small text-primary fw-semibold text-decoration-none">نسيت كلمة السر؟</a>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 auth-submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="loginByPhone"><i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>تسجيل الدخول</span>
                                    <span wire:loading wire:target="loginByPhone"><span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>جاري الدخول…</span>
                                </button>
                            </form>
                        </div>

                        <div class="portal-panel" data-portal-panel="email" id="panel-email" hidden>
                            <form wire:submit="loginByEmail" novalidate>
                                <div class="mb-3">
                                    <label class="form-label auth-label" for="login-email">البريد الإلكتروني</label>
                                    <div class="auth-field input-group input-group-lg">
                                        <span class="input-group-text auth-field__icon"><i class="fa-regular fa-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="login-email" wire:model="email" inputmode="email" autocomplete="email" dir="ltr" placeholder="name@example.com">
                                    </div>
                                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label auth-label" for="login-pass-e">كلمة السر</label>
                                    <div class="auth-field input-group input-group-lg">
                                        <span class="input-group-text auth-field__icon"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" class="form-control @error('password_email') is-invalid @enderror" id="login-pass-e" wire:model="password_email" autocomplete="current-password" placeholder="••••••••">
                                        <button type="button" class="btn auth-field__toggle" data-password-toggle="login-pass-e" aria-label="إظهار كلمة المرور" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    @error('password_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-e" wire:model="remember_email">
                                        <label class="form-check-label small" for="remember-e">تذكرني</label>
                                    </div>
                                    <a href="{{ route('password.request', ['locale' => app()->getLocale()]) }}" class="small text-primary fw-semibold text-decoration-none">نسيت كلمة السر؟</a>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 auth-submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="loginByEmail"><i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>تسجيل الدخول</span>
                                    <span wire:loading wire:target="loginByEmail"><span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>جاري الدخول…</span>
                                </button>
                            </form>
                        </div>

                        <div class="auth-divider my-4"><span>أو</span></div>

                        <p class="text-center mb-0 small text-muted">
                            ليس لديك حساب بعد؟
                            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="fw-semibold text-primary text-decoration-none">إنشاء حساب جديد</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-screen.css') }}?v=1">
@endpush

@push('scripts')
    <script src="{{ static_asset('assets/portal-shell.js') }}" defer></script>
@endpush
