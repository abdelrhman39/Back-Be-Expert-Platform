@props(['status'])

@php
    $label = \App\Support\AcademicRequestOptions::reviewStatusLabel($status);
    $class = $status === 'reviewed' ? 'admin-review-pill admin-review-pill--done' : 'admin-review-pill admin-review-pill--pending';
@endphp

<span @class([$class])>{{ $label }}</span>
