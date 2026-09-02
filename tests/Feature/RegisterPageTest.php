<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_register_page_uses_professional_layout(): void
    {
        $response = $this->get(route('register', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('auth-register', false);
        $response->assertSee('auth-stepper', false);
        $response->assertSee('التسجيل في البرامج المعتمدة');
        $response->assertSee('بيانات الهوية');
        $response->assertSee('حائل');
        $response->assertDontSee('الامير مقرن');
        $response->assertSee(route('apply.form', ['locale' => 'ar', 'type' => 'company']), false);
        $response->assertSee(route('cms.page', ['locale' => 'ar', 'slug' => 'privacy-policy']), false);
        $response->assertSee('auth-screen.css', false);
    }

    public function test_english_register_page_uses_translated_copy(): void
    {
        $response = $this->get(route('register', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Register for accredited programs');
        $response->assertSee('Identity');
        $response->assertSee('Hail');
        $response->assertDontSee('التسجيل في البرامج المعتمدة');
    }
}
