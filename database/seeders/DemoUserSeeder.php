<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'demo@domain.test'],
            [
                'name' => 'Demo Student',
                'name_ar' => 'طالب تجريبي',
                'national_id' => '1234567890',
                'phone' => PhoneNormalizer::toE164('512345678'),
                'password' => Hash::make('password'),
                'locale' => 'ar',
                'status' => 'active',
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );
    }
}
