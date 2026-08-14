<?php

namespace App\Console\Commands;

use App\Services\LectureReminderService;
use Illuminate\Console\Command;

class DispatchLectureRemindersCommand extends Command
{
    protected $signature = 'notifications:dispatch-lecture-reminders';

    protected $description = 'Send lecture reminder and live-now notifications to enrolled students';

    public function handle(LectureReminderService $service): int
    {
        $count = $service->dispatch();
        $this->info("Dispatched {$count} notification(s).");

        return self::SUCCESS;
    }
}
