<?php

namespace App\Services\Zoom;

use App\Models\SessionRecording;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ZoomRecordingTransferService
{
    public function __construct(private readonly ZoomApiClient $api) {}

    public function transfer(SessionRecording $recording): bool
    {
        if (! in_array($recording->storage_destination, ['s3', 'google'], true)) {
            return false;
        }

        if (! $recording->download_url || ! $recording->storage_disk) {
            return false;
        }

        $metadata = $recording->provider_payload ?? [];
        $temp = tempnam(sys_get_temp_dir(), 'zoom-recording-');

        if ($temp === false) {
            throw new RuntimeException('Unable to create a temporary recording file.');
        }

        try {
            $metadata['transfer_status'] = 'downloading';
            $recording->update(['provider_payload' => $metadata]);
            $this->api->downloadTo($recording->download_url, $temp);

            $path = sprintf(
                'zoom-recordings/%s/session-%d-%s.mp4',
                now()->format('Y/m'),
                $recording->attendance_session_id,
                $recording->external_recording_id ?: $recording->id,
            );
            $stream = fopen($temp, 'rb');

            if (! is_resource($stream)) {
                throw new RuntimeException('Unable to open the temporary recording file.');
            }

            try {
                $written = Storage::disk($recording->storage_disk)->put($path, $stream, [
                    'visibility' => 'private',
                    'ContentType' => 'video/mp4',
                ]);
            } finally {
                fclose($stream);
            }

            if (! $written) {
                throw new RuntimeException('The cloud storage adapter rejected the recording.');
            }

            $metadata['transfer_status'] = 'completed';
            $metadata['transferred_at'] = now()->toIso8601String();
            unset($metadata['transfer_error'], $metadata['note']);

            $recording->update([
                'storage_path' => $path,
                'provider_payload' => $metadata,
            ]);

            return true;
        } catch (\Throwable $e) {
            $metadata['transfer_status'] = 'failed';
            $metadata['transfer_error'] = $e->getMessage();
            $recording->update(['provider_payload' => $metadata]);

            throw $e;
        } finally {
            @unlink($temp);
        }
    }
}
