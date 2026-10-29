<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /** @var list<string> */
    public const EMAILS = [
        'admin@local.invalid',
        'admin@example.com',
    ];

    public const PASSWORD = 'Admin@123';

    public function run(): void
    {
        foreach (self::EMAILS as $email) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Platform Admin',
                    'name_ar' => 'مسؤول المنصة',
                    'password' => self::PASSWORD,
                    'locale' => 'ar',
                    'status' => 'active',
                    'role' => 'admin',
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'email_verified_at' => now(),
                ]
            );

            AccessControl::grantAllPermissions($user);
        }
    }
}
