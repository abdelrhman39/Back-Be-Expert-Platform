<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyCompanyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_company_apply_page_uses_professional_layout(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'ar', 'type' => 'company']));

        $response->assertOk();
        $response->assertSee('apply-hero', false);
        $response->assertSee('apply-paths', false);
        $response->assertSee('طلب تسجيل جهة');
        $response->assertSee('بيانات الجهة');
        $response->assertSee('مسؤول التواصل');
        $response->assertSee('احتياج التدريب');
        $response->assertSee('اسم الجهة');
        $response->assertSee('ماذا نقدّم للجهات؟');
        $response->assertSee('تواصل مع فريق البرامج');
        $response->assertDontSee('هل تريد حساب متدرب مباشرة؟');
        $response->assertDontSee('الامير مقرن');
        $response->assertSee('حائل');
        $response->assertSee(route('apply.form', ['locale' => 'ar', 'type' => 'client']), false);
        $response->assertSee(route('contact', ['locale' => 'ar']), false);
    }

    public function test_english_company_apply_page_uses_translated_copy(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'en', 'type' => 'company']));

        $response->assertOk();
        $response->assertSee('Organization training request');
        $response->assertSee('Organization details');
        $response->assertSee('Contact person');
        $response->assertSee('Representative name');
        $response->assertSee('Training need');
        $response->assertSee('Organization name');
        $response->assertSee('What we offer organizations');
        $response->assertSee('Contact the programs team');
        $response->assertDontSee('Need a learner account now?');
        $response->assertDontSee('طلب تسجيل جهة');
    }
}
