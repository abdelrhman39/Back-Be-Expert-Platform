@props(['status'])

@php
    $label = \App\Support\AcademicStudentOptions::academicStatusLabel($status);
    $class = match ($status) {
        'studying', 'graduated', 'eligible' => 'admin-badge admin-badge--success',
        'pending', 'expected' => 'admin-badge admin-badge--warn',
        'withdrawn', 'deferred', 'suspended' => 'admin-badge admin-badge--danger',
        default => 'admin-badge',
    };
@endphp

<span @class([$class])>{{ $label }}</span>
