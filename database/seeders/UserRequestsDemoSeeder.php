<?php

namespace Database\Seeders;

use App\Models\AcademicStudent;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserRequestsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@domain.test')->first();

        if (! $user) {
            return;
        }

        $student = AcademicStudent::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('national_id', $user->national_id)
            ->first()
            ?? AcademicStudent::query()->orderBy('id')->first();

        if (! $student) {
            return;
        }

        $student->update([
            'user_id' => $user->id,
            'email' => $user->email,
            'national_id' => $user->national_id ?? $student->national_id,
            'name_ar' => $user->name_ar ?? $student->name_ar,
        ]);
    }
}
