<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyCooperativePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_cooperative_apply_page_uses_professional_layout(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'ar', 'type' => 'cooperative']));

        $response->assertOk();
        $response->assertSee('apply-hero', false);
        $response->assertSee('apply-paths', false);
        $response->assertSee('طلب التدريب التعاوني');
        $response->assertSee('بيانات المتدرب');
        $response->assertSee('البيانات الأكاديمية');
        $response->assertSee('مدة التدريب والفصل');
        $response->assertSee('المشرف الأكاديمي');
        $response->assertSee('اسم المتدرب');
        $response->assertSee('ماذا يشمل التدريب التعاوني؟');
        $response->assertSee('تواصل مع فريق التدريب');
        $response->assertDontSee('هل تريد حساب متدرب مباشرة؟');
        $response->assertDontSee('الامير مقرن');
        $response->assertSee('حائل');
        $response->assertSee(route('apply.form', ['locale' => 'ar', 'type' => 'client']), false);
        $response->assertSee(route('contact', ['locale' => 'ar']), false);
    }

    public function test_english_cooperative_apply_page_uses_translated_copy(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'en', 'type' => 'cooperative']));

        $response->assertOk();
        $response->assertSee('Cooperative training request');
        $response->assertSee('Trainee details');
        $response->assertSee('Academic details');
        $response->assertSee('Duration and term');
        $response->assertSee('Academic supervisor');
        $response->assertSee('Supervisor name');
        $response->assertSee('Trainee name');
        $response->assertSee('Academic major');
        $response->assertSee('What cooperative training includes');
        $response->assertSee('Contact the training team');
        $response->assertDontSee('Need a learner account now?');
        $response->assertDontSee('طلب التدريب التعاوني');
    }
}
