<?php

namespace Tests\Feature;

use App\Services\Zoom\ZoomApiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZoomApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config()->set([
            'zoom.account_id' => 'account-id',
            'zoom.client_id' => 'client-id',
            'zoom.client_secret' => 'client-secret',
            'zoom.oauth_url' => 'https://zoom.test/oauth/token',
            'zoom.base_url' => 'https://zoom.test/v2',
        ]);
    }

    public function test_it_fetches_and_caches_server_to_server_token(): void
    {
        Http::fake([
            'https://zoom.test/oauth/token' => Http::response([
                'access_token' => 'access-token',
                'expires_in' => 3600,
            ]),
        ]);

        $client = app(ZoomApiClient::class);
        $this->assertSame('access-token', $client->accessToken());
        $this->assertSame('access-token', $client->accessToken());

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://zoom.test/oauth/token'
            && $request['grant_type'] === 'account_credentials'
            && $request['account_id'] === 'account-id');
    }

    public function test_it_paginates_zoom_responses(): void
    {
        Http::fake([
            'https://zoom.test/oauth/token' => Http::response(['access_token' => 'token', 'expires_in' => 3600]),
            'https://zoom.test/v2/report*' => Http::sequence()
                ->push(['participants' => [['id' => 1]], 'next_page_token' => 'next'])
                ->push(['participants' => [['id' => 2]], 'next_page_token' => '']),
        ]);

        $items = app(ZoomApiClient::class)->paginate('/report', [], 'participants');

        $this->assertSame([['id' => 1], ['id' => 2]], $items);
    }
}
