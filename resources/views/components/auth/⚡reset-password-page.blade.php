<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('تعيين كلمة مرور جديدة | مركز التعلم المستمر')]
class extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        $status = Password::reset(
            [
                'email' => strtolower(trim($this->email)),
                'password' => $this->password,
                'password_confirmation' => $this->passwordConfirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        session()->flash('auth_message', 'تم تعيين كلمة المرور بنجاح.');

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
            <h1 class="h4 fw-bold text-center mb-2">كلمة مرور جديدة</h1>
            <p class="text-muted text-center small mb-4">أدخل كلمة المرور الجديدة لحسابك.</p>

            <form wire:submit="resetPassword" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="reset-email-field">البريد الإلكتروني</label>
                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="reset-email-field" wire:model="email" dir="ltr" autocomplete="email">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new-pass">كلمة المرور الجديدة</label>
                    <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="new-pass" wire:model="password" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="new-pass2">تأكيد كلمة المرور</label>
                    <input type="password" class="form-control form-control-lg" id="new-pass2" wire:model="passwordConfirmation" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="resetPassword">حفظ كلمة المرور</span>
                    <span wire:loading wire:target="resetPassword">جاري الحفظ…</span>
                </button>
            </form>

            <p class="text-center mt-4 mb-0 small text-muted">
                <a href="{{ route('login', ['locale' => $locale]) }}" class="fw-semibold text-primary">العودة لتسجيل الدخول</a>
            </p>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
@endpush
