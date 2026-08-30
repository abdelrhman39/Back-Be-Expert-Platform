<?php

namespace App\Console\Commands;

use App\Services\ZoxAgent\ZoxAgentMeetingService;
use App\Support\ZoxAgentSettings;
use Illuminate\Console\Command;

class SyncZoxAgentMeetingsCommand extends Command
{
    protected $signature = 'zoxagent:sync-meetings';

    protected $description = 'Start/end due ZoxAgent lecture rooms and sync attendance + recordings';

    public function handle(ZoxAgentMeetingService $meetings): int
    {
        if (! ZoxAgentSettings::enabled()) {
            $this->comment('ZoxAgent is disabled or not configured.');

            return self::SUCCESS;
        }

        $started = $meetings->startDueSessions();
        $ended = $meetings->endDueRooms();
        $synced = $meetings->syncDueAttendance();
        $recordings = $meetings->syncDueRecordings();

        $this->info("Started {$started} room(s). Ended {$ended}. Synced attendance {$synced}. Pulled recordings {$recordings}.");

        return self::SUCCESS;
    }
}
