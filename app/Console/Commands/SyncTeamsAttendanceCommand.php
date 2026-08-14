<?php

namespace App\Console\Commands;

use App\Services\MicrosoftTeams\TeamsAttendanceSyncService;
use Illuminate\Console\Command;

class SyncTeamsAttendanceCommand extends Command
{
    protected $signature = 'teams:sync-attendance';

    protected $description = 'Sync student attendance from Microsoft Teams meeting reports';

    public function handle(TeamsAttendanceSyncService $service): int
    {
        $count = $service->syncDueSessions();
        $this->info("Synced {$count} session(s) from Teams.");

        return self::SUCCESS;
    }
}
