<?php

use App\Models\Certificate;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('التحقق من الشهادة | مركز التعلم المستمر')]
class extends Component
{
    public string $code = '';

    public ?Certificate $certificate = null;

    public bool $searched = false;

    public function mount(?string $code = null): void
    {
        $prefill = $code ?: request()->query('code', '');

        if (filled($prefill)) {
            $this->code = trim((string) $prefill);
            $this->verify();
        }
    }

    public function layout(): string
    {
        return auth()->check() ? 'layouts.app-user' : 'layouts.app-inner';
    }

    public function verify(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:64'],
        ], [], ['code' => 'رمز الشهادة']);

        $this->certificate = Certificate::query()
            ->where(function ($query): void {
                $query->where('code', trim($this->code))
                    ->orWhere('verify_token', trim($this->code));
            })
            ->first();

        $this->searched = true;
    }
};
?>

@php($locale = app()->getLocale())

@if (auth()->check())
    @include('partials.portal.shell-start', ['portalActive' => 'certificate-verify', 'portalTitle' => 'التحقق من الشهادة'])
    <div class="portal-dashboard">
        <div class="portal-panel p-4 p-md-5 mx-auto" style="max-width:560px">
            <h1 class="portal-commerce-intro__title text-center mb-2">التحقق من الشهادة</h1>
            <p class="text-muted text-center small mb-4">أدخل رمز الشهادة المطبوع على الشهادة للتحقق من صحتها.</p>
            <form wire:submit="verify">
                <div class="mb-3">
                    <label class="form-label" for="cert-code">رمز الشهادة</label>
                    <input type="text" id="cert-code" class="form-control form-control-lg @error('code') is-invalid @enderror" wire:model="code" dir="ltr" placeholder="BE-26101501">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">تحقق</button>
            </form>
            @include('partials.certificates.verification-result')
        </div>
    </div>
    @include('partials.portal.shell-end')
@else
    <div class="portal-bg-pattern py-5">
        <div class="container" style="max-width: 560px;">
            <div class="portal-card card border-0 p-4 p-md-5">
                <h1 class="h4 fw-bold text-center mb-2">التحقق من الشهادة</h1>
                <p class="text-muted text-center small mb-4">أدخل رمز الشهادة المطبوع على الشهادة للتحقق من صحتها.</p>
                <form wire:submit="verify">
                    <div class="mb-3">
                        <label class="form-label" for="cert-code">رمز الشهادة</label>
                        <input type="text" id="cert-code" class="form-control form-control-lg @error('code') is-invalid @enderror" wire:model="code" dir="ltr" placeholder="BE-26101501">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">تحقق</button>
                </form>
                @include('partials.certificates.verification-result')
            </div>
        </div>
    </div>
    @push('styles')
        <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
    @endpush
@endif
