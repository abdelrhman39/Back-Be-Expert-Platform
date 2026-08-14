<?php

namespace Database\Seeders;

use App\Models\AcademicSchedule;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UpcomingLectureDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@domain.test')->first();
        $section = AcademicSection::query()->with('schedule')->whereHas('schedule')->first();

        if (! $section) {
            return;
        }

        $student = AcademicStudent::query()->where('section_id', $section->id)->first()
            ?? AcademicStudent::query()->where('batch_id', $section->batch_id)->first();

        if ($student && $user) {
            $student->update([
                'user_id' => $user->id,
                'email' => $user->email,
                'name_ar' => $user->displayName(),
            ]);
        }

        $meetingUrl = 'https://teams.microsoft.com/l/meetup-join/demo-lecture-domain';

        if ($section->schedule) {
            AcademicSchedule::query()->where('id', $section->schedule->id)->update([
                'meeting_url' => $meetingUrl,
            ]);
        }

        $today = Carbon::today();
        $now = Carbon::now();
        $start = $now->copy()->subMinutes(5);
        $end = $now->copy()->addHours(2);

        AttendanceSession::query()->updateOrCreate(
            [
                'section_id' => $section->id,
                'session_date' => $today->toDateString(),
            ],
            [
                'schedule_id' => $section->schedule?->id,
                'title' => 'محاضرة مباشرة — '.$section->subtitle,
                'time_start' => $start->format('H:i:s'),
                'time_end' => $end->format('H:i:s'),
                'meeting_url' => $meetingUrl,
                'status' => 'scheduled',
                'source' => 'manual',
                'published_at' => now(),
                'session_number' => 1,
            ],
        );
    }
}
