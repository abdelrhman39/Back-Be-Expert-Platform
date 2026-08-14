<?php

namespace App\Jobs;

use App\Models\SessionRecording;
use App\Services\Zoom\ZoomRecordingTransferService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TransferZoomRecording implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $timeout = 1800;

    public function __construct(public readonly int $recordingId) {}

    public function handle(ZoomRecordingTransferService $service): void
    {
        $recording = SessionRecording::query()->find($this->recordingId);

        if ($recording && ! $recording->storage_path) {
            $service->transfer($recording);
        }
    }
}
