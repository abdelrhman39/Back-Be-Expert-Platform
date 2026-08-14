@php
    use App\Services\InstallmentOverdueService;

    $suspended = auth()->check()
        && \App\Support\InstallmentSettings::suspensionEnabled()
        && app(InstallmentOverdueService::class)->userIsSuspendedForInstallments(auth()->user());
    $locale = app()->getLocale();
@endphp

@if ($suspended)
    <div class="portal-alert portal-alert--warn portal-alert--compact portal-installment-suspend-banner">
        <span class="portal-alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
        <div class="portal-alert__content">
            <strong>الالتحاق موقوف مؤقتاً</strong> — لديك أقساط متأخرة. 
            <a href="{{ route('installments', ['locale' => $locale]) }}" class="portal-alert__link">سدّد الآن لاستعادة الوصول</a>
        </div>
    </div>
@endif
