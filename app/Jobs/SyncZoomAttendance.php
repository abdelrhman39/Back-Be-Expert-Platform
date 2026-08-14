<?php

namespace App\Jobs;

use App\Models\ZoomMeeting;
use App\Services\Zoom\ZoomAttendanceSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncZoomAttendance implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [60, 180, 600, 1200];

    public function __construct(public readonly int $zoomMeetingId) {}

    public function handle(ZoomAttendanceSyncService $service): void
    {
        $meeting = ZoomMeeting::query()->find($this->zoomMeetingId);
        if ($meeting) {
            $service->syncMeeting($meeting);
        }
    }
}
