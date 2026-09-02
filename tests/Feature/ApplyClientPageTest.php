<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyClientPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_client_apply_page_uses_professional_layout(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'ar', 'type' => 'client']));

        $response->assertOk();
        $response->assertSee('apply-hero', false);
        $response->assertSee('apply-paths', false);
        $response->assertSee('طلب تسجيل فرد');
        $response->assertSee('بيانات التواصل');
        $response->assertSee('البرنامج المطلوب');
        $response->assertSee('الاسم الرباعي');
        $response->assertDontSee('الامير مقرن');
        $response->assertSee(route('register', ['locale' => 'ar']), false);
        $response->assertSee(route('apply.form', ['locale' => 'ar', 'type' => 'company']), false);
    }

    public function test_english_client_apply_page_uses_translated_copy(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'en', 'type' => 'client']));

        $response->assertOk();
        $response->assertSee('Individual registration request');
        $response->assertSee('Contact details');
        $response->assertSee('Requested program');
        $response->assertSee('Full name');
        $response->assertDontSee('طلب تسجيل فرد');
    }
}
