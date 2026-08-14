<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SyncZoomAttendance;
use App\Jobs\SyncZoomRecording;
use App\Models\ZoomMeeting;
use App\Models\ZoomWebhookEvent;
use App\Support\ZoomSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoomWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        if (! $this->validSignature(
            (string) $request->header('x-zm-request-timestamp'),
            (string) $request->header('x-zm-signature'),
            $rawBody,
        )) {
            return response()->json(['message' => 'Invalid Zoom signature.'], 401);
        }

        $payload = $request->json()->all();
        if (($payload['event'] ?? null) === 'endpoint.url_validation') {
            $plainToken = (string) data_get($payload, 'payload.plainToken');

            return response()->json([
                'plainToken' => $plainToken,
                'encryptedToken' => hash_hmac('sha256', $plainToken, (string) ZoomSettings::webhookSecret()),
            ]);
        }

        $eventId = (string) ($request->header('x-zm-trackingid')
            ?: data_get($payload, 'payload.object.uuid')
            ?: hash('sha256', $rawBody));
        $eventId = ($payload['event'] ?? 'unknown').':'.$eventId.':'.($payload['event_ts'] ?? '');
        $meetingId = (string) (data_get($payload, 'payload.object.id') ?: '');

        $event = ZoomWebhookEvent::query()->firstOrCreate(
            ['event_id' => $eventId],
            [
                'event_type' => (string) ($payload['event'] ?? 'unknown'),
                'meeting_id' => $meetingId ?: null,
                'payload' => $payload,
            ],
        );
        if (! $event->wasRecentlyCreated) {
            return response()->json(['status' => 'duplicate']);
        }

        $meeting = ZoomMeeting::query()
            ->where('meeting_id', $meetingId)
            ->orWhere('meeting_uuid', (string) data_get($payload, 'payload.object.uuid'))
            ->first();

        if ($meeting && $event->event_type === 'meeting.ended') {
            $meeting->update(['status' => 'ended', 'last_synced_at' => now()]);
            SyncZoomAttendance::dispatch($meeting->id);
        }
        if ($meeting && $event->event_type === 'recording.completed') {
            SyncZoomRecording::dispatch($meeting->id);
        }

        $event->update([
            'status' => $meeting ? 'dispatched' : 'ignored',
            'attempts' => 1,
            'processed_at' => now(),
        ]);

        return response()->json(['status' => 'accepted']);
    }

    public function validSignature(string $timestamp, string $signature, string $rawBody): bool
    {
        $secret = ZoomSettings::webhookSecret();
        if (! $secret || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > (int) config('zoom.webhook_tolerance_seconds', 300)) {
            return false;
        }

        $expected = 'v0='.hash_hmac('sha256', "v0:{$timestamp}:{$rawBody}", $secret);

        return hash_equals($expected, $signature);
    }
}
