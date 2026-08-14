<?php

namespace Database\Seeders;

use App\Models\AcademicStaff;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AcademicSchedulesSeeder::class,
            AttendanceDemoSeeder::class,
        ]);

        $user = User::query()->updateOrCreate(
            ['email' => 'instructor@domain.test'],
            [
                'name' => 'Sara Instructor',
                'name_ar' => 'أ. سارة العنزي',
                'national_id' => '1099887766',
                'phone' => PhoneNormalizer::toE164('512345679'),
                'password' => Hash::make('password'),
                'locale' => 'ar',
                'status' => 'active',
                'role' => 'instructor',
                'email_verified_at' => now(),
            ]
        );

        $staff = AcademicStaff::query()->where('name_ar', 'أ. سارة العنزي')->first();

        if ($staff) {
            $staff->update([
                'user_id' => $user->id,
                'permission_preset' => 'instructor.lead',
            ]);
        }
    }
}
