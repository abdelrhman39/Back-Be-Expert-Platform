@props(['role'])

@php
    use App\Support\UserOptions;

    $tone = match ($role) {
        'admin' => 'admin-user-role--admin',
        'instructor' => 'admin-user-role--staff',
        default => 'admin-user-role--student',
    };
@endphp

<span @class(['admin-user-role', $tone])>{{ UserOptions::roleLabel($role) }}</span>
