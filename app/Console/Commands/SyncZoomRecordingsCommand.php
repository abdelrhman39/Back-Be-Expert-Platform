<?php

namespace App\Console\Commands;

use App\Services\Zoom\ZoomRecordingSyncService;
use Illuminate\Console\Command;

class SyncZoomRecordingsCommand extends Command
{
    protected $signature = 'zoom:sync-recordings';

    protected $description = 'Sync cloud recordings from completed Zoom meetings';

    public function handle(ZoomRecordingSyncService $service): int
    {
        $count = $service->syncDueSessions();
        $this->info("Synced {$count} Zoom recording(s).");

        return self::SUCCESS;
    }
}
