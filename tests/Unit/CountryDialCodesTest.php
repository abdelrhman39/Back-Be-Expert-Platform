<?php

namespace Tests\Unit;

use App\Support\CountryDialCodes;
use App\Support\PhoneNormalizer;
use App\Support\VisitorDialCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CountryDialCodesTest extends TestCase
{
    public function test_catalog_includes_major_calling_codes(): void
    {
        $this->assertGreaterThanOrEqual(180, count(CountryDialCodes::all()));
        $this->assertSame('966', CountryDialCodes::find('SA')['dial']);
        $this->assertSame('20', CountryDialCodes::find('EG')['dial']);
        $this->assertSame('971', CountryDialCodes::find('AE')['dial']);
        $this->assertSame('1', CountryDialCodes::find('US')['dial']);
        $this->assertTrue(CountryDialCodes::has('KW'));
    }

    public function test_normalizer_uses_selected_dial_code(): void
    {
        $this->assertSame('+966512345678', PhoneNormalizer::toE164('512345678'));
        $this->assertSame('+966512345678', PhoneNormalizer::toE164('0512345678', 'SA'));
        $this->assertSame('+201001234567', PhoneNormalizer::toE164('1001234567', 'EG'));
        $this->assertSame('+971501234567', PhoneNormalizer::toE164('+971501234567', 'SA'));
    }

    public function test_visitor_dial_uses_cdn_country_header(): void
    {
        Http::fake();

        $request = Request::create('https://kku.platformuni.com/ar/login', 'GET');
        $request->headers->set('CF-IPCountry', 'eg');

        $this->assertSame('EG', VisitorDialCode::iso($request));
        Http::assertNothingSent();
    }

    public function test_visitor_dial_looks_up_public_ip_when_headers_missing(): void
    {
        Http::fake([
            'api.country.is/*' => Http::response(['ip' => '8.8.8.8', 'country' => 'US'], 200),
        ]);

        $request = Request::create('https://kku.platformuni.com/ar/login', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
        ]);

        $this->assertSame('US', VisitorDialCode::iso($request));
    }

    public function test_private_ip_falls_back_to_saudi_arabia(): void
    {
        Http::fake();

        $request = Request::create('https://kku.platformuni.com/ar/login', 'GET', server: [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $this->assertSame('SA', VisitorDialCode::iso($request));
        Http::assertNothingSent();
    }
}
