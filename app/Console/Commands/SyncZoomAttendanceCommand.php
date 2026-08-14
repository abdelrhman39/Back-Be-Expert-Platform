<?php

namespace App\Console\Commands;

use App\Services\Zoom\ZoomAttendanceSyncService;
use Illuminate\Console\Command;

class SyncZoomAttendanceCommand extends Command
{
    protected $signature = 'zoom:sync-attendance';

    protected $description = 'Sync student attendance from completed Zoom meetings';

    public function handle(ZoomAttendanceSyncService $service): int
    {
        $count = $service->syncDueSessions();
        $this->info("Synced {$count} Zoom meeting(s).");

        return self::SUCCESS;
    }
}
