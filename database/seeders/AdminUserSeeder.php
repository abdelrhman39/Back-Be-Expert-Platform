<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@local.invalid'],
            [
                'name' => 'Platform Admin',
                'name_ar' => 'مسؤول المنصة',
                'password' => Hash::make('Admin@123'),
                'locale' => 'ar',
                'status' => 'active',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
