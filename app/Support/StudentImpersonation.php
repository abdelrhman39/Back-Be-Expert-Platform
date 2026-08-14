<?php

namespace App\Support;

use App\Models\User;

class StudentImpersonation
{
    public static function can(?User $admin): bool
    {
        if (! $admin || ! AdminPermissions::can($admin, 'students.impersonate')) {
            return false;
        }

        $email = strtolower(trim((string) $admin->email));

        return in_array(
            $email,
            array_map('strtolower', config('student-impersonation.allowed_emails', [])),
            true
        );
    }
}
