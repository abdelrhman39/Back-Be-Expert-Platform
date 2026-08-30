<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\ZoxAgent\ZoxAgentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoxAgentWebhookController extends Controller
{
    public function __invoke(Request $request, ZoxAgentWebhookService $webhooks): JsonResponse
    {
        $raw = $request->getContent();
        $signature = (string) $request->header('X-ZoxAgent-Signature', '');
        $event = (string) $request->header('X-ZoxAgent-Event', '');

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['ok' => false, 'error' => 'invalid_json'], 400);
        }

        if ($event === '') {
            $event = (string) ($payload['event'] ?? '');
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $result = $webhooks->handle($event, $data, $raw, $signature);

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }
}
