<?php

namespace App\Console\Commands;

use App\Services\AcademicSessionService;
use Illuminate\Console\Command;

class GenerateUpcomingSessionsCommand extends Command
{
    protected $signature = 'sessions:generate-upcoming {--weeks=4 : Number of weeks ahead}';

    protected $description = 'Generate upcoming lecture sessions from section schedules';

    public function handle(AcademicSessionService $service): int
    {
        $weeks = max(1, (int) $this->option('weeks'));
        $count = $service->generateUpcomingForAllSections($weeks);

        $this->info("Created {$count} session(s) for the next {$weeks} week(s).");

        return self::SUCCESS;
    }
}
