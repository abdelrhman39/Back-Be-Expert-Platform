<?php

namespace Tests\Feature;

use App\Http\Controllers\Webhooks\ZoomWebhookController;
use Tests\TestCase;

class ZoomWebhookSignatureTest extends TestCase
{
    public function test_it_accepts_a_current_valid_signature_and_rejects_tampering(): void
    {
        config()->set('zoom.webhook_secret', 'webhook-secret');
        $timestamp = (string) time();
        $body = '{"event":"meeting.ended"}';
        $signature = 'v0='.hash_hmac('sha256', "v0:{$timestamp}:{$body}", 'webhook-secret');
        $controller = app(ZoomWebhookController::class);

        $this->assertTrue($controller->validSignature($timestamp, $signature, $body));
        $this->assertFalse($controller->validSignature($timestamp, $signature, $body.'x'));
        $this->assertFalse($controller->validSignature((string) (time() - 600), $signature, $body));
    }

    public function test_url_validation_returns_zoom_hmac(): void
    {
        config()->set('zoom.webhook_secret', 'webhook-secret');
        $timestamp = (string) time();
        $body = json_encode([
            'event' => 'endpoint.url_validation',
            'payload' => ['plainToken' => 'plain-token'],
        ], JSON_THROW_ON_ERROR);
        $signature = 'v0='.hash_hmac('sha256', "v0:{$timestamp}:{$body}", 'webhook-secret');

        $response = $this->call('POST', '/webhooks/zoom', [], [], [], [
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk()->assertJson([
            'plainToken' => 'plain-token',
            'encryptedToken' => hash_hmac('sha256', 'plain-token', 'webhook-secret'),
        ]);
    }
}
