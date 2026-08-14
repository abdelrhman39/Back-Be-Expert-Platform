@props(['status'])

@php
    $label = \App\Support\AcademicRequestOptions::statusLabel($status);
    $class = match ($status) {
        'processing' => 'admin-status-pill admin-status-pill--processing',
        'approved' => 'admin-badge admin-badge--success',
        'rejected' => 'admin-badge admin-badge--danger',
        default => 'admin-status-pill admin-status-pill--warning',
    };
@endphp

@if ($status === 'processing')
    <span @class([$class])>
        <span class="admin-status-pill__dot" aria-hidden="true"></span>
        {{ $label }}
    </span>
@else
    <span @class([$class])>{{ $label }}</span>
@endif
