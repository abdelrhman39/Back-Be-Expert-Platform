<?php

namespace App\Console\Commands;

use App\Services\MicrosoftTeams\TeamsRecordingSyncService;
use App\Services\SessionRecordingService;
use Illuminate\Console\Command;

class SyncTeamsRecordingsCommand extends Command
{
    protected $signature = 'teams:sync-recordings';

    protected $description = 'Sync lecture recordings from Microsoft Teams via Graph API';

    public function handle(TeamsRecordingSyncService $sync, SessionRecordingService $recordings): int
    {
        $count = $sync->syncDueSessions();
        $expired = $recordings->expireDueRecordings();

        $this->info("Synced {$count} recording(s); expired {$expired}.");

        return self::SUCCESS;
    }
}
