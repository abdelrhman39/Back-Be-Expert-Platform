<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginCountryDialTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_selects_dial_code_from_country_header(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'EG'])
            ->get(route('login', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('+20', false)
            ->assertSee('مصر')
            ->assertSee('السعودية')
            ->assertSee('الولايات المتحدة');
    }

    public function test_login_page_defaults_to_saudi_dial_for_local_requests(): void
    {
        $this->get(route('login', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('+966', false)
            ->assertSee('السعودية');
    }
}
