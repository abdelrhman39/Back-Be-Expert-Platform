@php
    $toastMessage = $message ?? null;
    $toastType = $type ?? 'info';
    $toastKey = $key ?? uniqid('toast-', true);
    $toastDuration = (int) ($duration ?? 6500);
    $toastTitle = $title ?? match ($toastType) {
        'success' => 'تم بنجاح',
        'warn', 'warning' => 'تنبيه',
        'error', 'danger' => 'تعذّر الإجراء',
        default => 'معلومة',
    };
    $toastIcon = match ($toastType) {
        'success' => 'fa-circle-check',
        'warn', 'warning' => 'fa-triangle-exclamation',
        'error', 'danger' => 'fa-circle-xmark',
        default => 'fa-circle-info',
    };
    $toastClass = match ($toastType) {
        'success' => 'success',
        'warn', 'warning' => 'warn',
        'error', 'danger' => 'error',
        default => 'info',
    };
@endphp

@if ($toastMessage)
    <div class="admin-toast-host" wire:key="admin-toast-{{ $toastKey }}" data-admin-toast-host>
        <div
            class="admin-toast admin-toast--{{ $toastClass }}"
            role="status"
            aria-live="polite"
            data-admin-toast
            data-duration="{{ $toastDuration }}"
            @if (! empty($dismissMethod))
                wire:click.stop
            @endif
        >
            <span class="admin-toast__icon" aria-hidden="true">
                <i class="fa-solid {{ $toastIcon }}"></i>
            </span>
            <div class="admin-toast__body">
                <p class="admin-toast__title">{{ $toastTitle }}</p>
                <p class="admin-toast__text">{{ $toastMessage }}</p>
            </div>
            <button
                type="button"
                class="admin-toast__close"
                aria-label="إغلاق الإشعار"
                data-admin-toast-dismiss
                @if (! empty($dismissMethod))
                    wire:click="{{ $dismissMethod }}"
                @else
                    onclick="window.adminToastDismiss && window.adminToastDismiss(this.closest('[data-admin-toast]'))"
                @endif
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
            <span class="admin-toast__bar" style="animation-duration: {{ $toastDuration }}ms;" aria-hidden="true"></span>
        </div>
    </div>
@endif
