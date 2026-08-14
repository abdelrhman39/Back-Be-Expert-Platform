<?php

namespace App\Services\Zoom;

use App\Jobs\TransferZoomRecording;
use App\Models\SessionRecording;
use App\Models\ZoomMeeting;
use App\Services\SessionRecordingService;
use App\Support\ZoomSettings;
use Carbon\Carbon;

class ZoomRecordingSyncService
{
    public function __construct(private readonly ZoomApiClient $api) {}

    public function syncDueSessions(): int
    {
        if (! ZoomSettings::enabled() || ZoomSettings::recordingPolicy() === 'disabled') {
            return 0;
        }

        $meetings = ZoomMeeting::query()
            ->whereHas('session', fn ($query) => $query->whereDate('session_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('recordings_synced_at')
                ->orWhere('recordings_synced_at', '<', now()->subMinutes(ZoomSettings::syncInterval())))
            ->with('session.recording')
            ->limit(25)
            ->get();

        $count = 0;
        foreach ($meetings as $meeting) {
            if ($this->syncMeeting($meeting)) {
                $count++;
            }
        }

        return $count;
    }

    public function syncMeeting(ZoomMeeting $meeting): bool
    {
        $response = $this->api->get('/meetings/'.$meeting->meeting_id.'/recordings');
        $file = collect($response['recording_files'] ?? [])
            ->filter(fn ($item) => strtoupper((string) ($item['file_type'] ?? '')) === 'MP4'
                && ($item['status'] ?? 'completed') === 'completed')
            ->sortByDesc(fn ($item) => (int) ($item['file_size'] ?? 0))
            ->first();

        $meeting->update(['recordings_synced_at' => now(), 'last_synced_at' => now()]);
        if (! is_array($file)) {
            return false;
        }

        $destination = ZoomSettings::recordingDestination();
        $disk = match ($destination) {
            's3' => ZoomSettings::s3Disk(),
            'google' => ZoomSettings::googleDisk(),
            default => null,
        };
        $metadata = [
            'destination' => $destination,
            'transfer_status' => $destination === 'zoom' ? 'not_required' : 'pending',
            'meeting' => [
                'uuid' => $response['uuid'] ?? null,
                'recording_count' => $response['recording_count'] ?? null,
            ],
            'file' => $file,
        ];

        $recording = SessionRecording::query()->updateOrCreate(
            ['attendance_session_id' => $meeting->attendance_session_id],
            [
                'source' => 'zoom_cloud',
                'provider' => 'zoom',
                'external_recording_id' => $file['id'] ?? null,
                'recording_url' => $file['play_url'] ?? $response['share_url'] ?? null,
                'share_url' => $response['share_url'] ?? null,
                'play_url' => $file['play_url'] ?? null,
                'download_url' => $file['download_url'] ?? null,
                'recording_passcode' => $response['password'] ?? $response['recording_play_passcode'] ?? null,
                'storage_destination' => $destination,
                'storage_disk' => $disk,
                'duration_seconds' => isset($response['duration']) ? (int) round($response['duration'] * 60) : null,
                'file_size_bytes' => $file['file_size'] ?? null,
                'recorded_at' => isset($file['recording_start']) ? Carbon::parse($file['recording_start']) : now(),
                'synced_at' => now(),
                'provider_payload' => $metadata,
                'status' => 'available',
            ],
        );

        if ($destination !== 'zoom' && ! $recording->storage_path) {
            TransferZoomRecording::dispatch($recording->id);
        }

        app(SessionRecordingService::class)->maybeAutoPublish($recording);

        return true;
    }
}
