<?php

use App\Services\PhonePasswordResetService;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('نسيت كلمة المرور | مركز التعلم المستمر')]
class extends Component
{
    public string $email = '';

    public string $phone = '';

    public string $otp = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public bool $emailSent = false;

    public string $phoneStep = 'request';

    public bool $phoneOtpSent = false;

    public function sendEmailResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [], ['email' => 'البريد الإلكتروني']);

        Password::sendResetLink(['email' => strtolower(trim($this->email))]);

        $this->emailSent = true;
    }

    public function sendPhoneOtp(PhonePasswordResetService $reset): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'min:9'],
        ], [], ['phone' => 'رقم الجوال']);

        $reset->sendOtp($this->phone);

        $this->phoneOtpSent = true;
        $this->phoneStep = 'reset';
    }

    public function resetViaPhone(PhonePasswordResetService $reset): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'min:9'],
            'otp' => ['required', 'digits:6'],
            'newPassword' => ['required', 'string', 'min:8', 'same:newPasswordConfirmation'],
        ], [], [
            'phone' => 'رقم الجوال',
            'otp' => 'رمز التحقق',
            'newPassword' => 'كلمة المرور',
        ]);

        if (! $reset->verifyOtp($this->phone, $this->otp)) {
            $this->addError('otp', 'رمز التحقق غير صحيح أو منتهي الصلاحية.');

            return;
        }

        $reset->resetPassword($this->phone, $this->newPassword);

        session()->flash('auth_message', 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');

        $locale = request()->route('locale') ?? 'ar';

        $this->redirect(route('login', ['locale' => $locale]));
    }
};
?>

@php
    $locale = app()->getLocale();
@endphp

<div class="portal-bg-pattern py-5 d-flex align-items-center">
    <div class="container" style="max-width: 460px;">
        <div class="portal-card card border-0 p-4 p-md-5">
            <h1 class="h4 fw-bold text-center mb-2">إعادة تعيين كلمة المرور</h1>
            <p class="text-muted text-center small mb-4">أدخل بريدك الإلكتروني أو رقم الجوال المسجّل لاستعادة الوصول.</p>

            <div class="portal-tabs" role="tablist">
                <button type="button" class="portal-tab is-active" role="tab" aria-selected="true" data-portal-tab="email">عبر البريد</button>
                <button type="button" class="portal-tab" role="tab" aria-selected="false" data-portal-tab="phone">عبر الجوال</button>
            </div>

            <div class="portal-panel" data-portal-panel="email">
                @if ($emailSent)
                    <div class="alert alert-success small mb-0">
                        إذا كان البريد مسجّلاً لدينا، ستصلك رسالة تحتوي رابط إعادة التعيين خلال دقائق.
                    </div>
                @else
                    <form wire:submit="sendEmailResetLink" novalidate>
                        <div class="mb-4">
                            <label class="form-label" for="reset-email">البريد الإلكتروني</label>
                            <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="reset-email" wire:model="email" dir="ltr" autocomplete="email" placeholder="example@email.com">
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendEmailResetLink">إرسال الرابط</span>
                            <span wire:loading wire:target="sendEmailResetLink">جاري الإرسال…</span>
                        </button>
                    </form>
                @endif
            </div>

            <div class="portal-panel" data-portal-panel="phone" hidden>
                @if ($phoneStep === 'request')
                    <form wire:submit="sendPhoneOtp" novalidate>
                        <label class="form-label" for="reset-mobile">رقم الجوال</label>
                        <div class="input-group input-group-lg mb-4">
                            <span class="input-group-text">🇸🇦 +966</span>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="reset-mobile" wire:model="phone" inputmode="numeric" placeholder="5xxxxxxxx">
                        </div>
                        @error('phone')<div class="invalid-feedback d-block mb-3">{{ $message }}</div>@enderror
                        <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendPhoneOtp">إرسال الرمز</span>
                            <span wire:loading wire:target="sendPhoneOtp">جاري الإرسال…</span>
                        </button>
                        @if (app()->environment('local'))
                            <p class="form-text small mt-2 mb-0">بيئة التطوير: راجع سجل Laravel للرمز.</p>
                        @endif
                    </form>
                @else
                    <form wire:submit="resetViaPhone" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="reset-otp">رمز التحقق</label>
                            <input type="text" class="form-control form-control-lg @error('otp') is-invalid @enderror" id="reset-otp" wire:model="otp" inputmode="numeric" maxlength="6" placeholder="000000" dir="ltr">
                            @error('otp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reset-new-pass">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control form-control-lg @error('newPassword') is-invalid @enderror" id="reset-new-pass" wire:model="newPassword" autocomplete="new-password">
                            @error('newPassword')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="reset-new-pass2">تأكيد كلمة المرور</label>
                            <input type="password" class="form-control form-control-lg" id="reset-new-pass2" wire:model="newPasswordConfirmation" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="resetViaPhone">حفظ كلمة المرور</span>
                            <span wire:loading wire:target="resetViaPhone">جاري الحفظ…</span>
                        </button>
                    </form>
                @endif
            </div>

            <p class="text-center mt-4 mb-0 small text-muted">
                تذكرت كلمة المرور؟
                <a href="{{ route('login', ['locale' => $locale]) }}" class="fw-semibold text-primary">تسجيل الدخول</a>
            </p>
            <p class="text-center mt-2 mb-0 small">
                <a href="{{ route('register', ['locale' => $locale]) }}" class="text-muted">إنشاء حساب جديد</a>
            </p>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
@endpush

@push('scripts')
    <script src="{{ static_asset('assets/portal-shell.js') }}" defer></script>
@endpush
