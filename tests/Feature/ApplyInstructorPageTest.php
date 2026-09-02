<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyInstructorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_instructor_apply_page_uses_professional_layout(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'ar', 'type' => 'instructor']));

        $response->assertOk();
        $response->assertSee('apply-hero', false);
        $response->assertSee('apply-paths', false);
        $response->assertSee('طلب انضمام كمدرب');
        $response->assertSee('البيانات الشخصية');
        $response->assertSee('الخبرة المهنية');
        $response->assertSee('المرفقات');
        $response->assertSee('السيرة الذاتية (PDF)');
        $response->assertSee('ماذا يعني الانضمام؟');
        $response->assertSee('تواصل مع الفريق الأكاديمي');
        $response->assertDontSee('هل تريد حساب متدرب مباشرة؟');
        $response->assertDontSee('الامير مقرن');
        $response->assertSee('حائل');
        $response->assertSee(route('apply.form', ['locale' => 'ar', 'type' => 'client']), false);
        $response->assertSee(route('contact', ['locale' => 'ar']), false);
    }

    public function test_english_instructor_apply_page_uses_translated_copy(): void
    {
        $response = $this->get(route('apply.form', ['locale' => 'en', 'type' => 'instructor']));

        $response->assertOk();
        $response->assertSee('Join as an instructor');
        $response->assertSee('Personal details');
        $response->assertSee('Professional experience');
        $response->assertSee('Attachments');
        $response->assertSee('First name');
        $response->assertSee('CV (PDF)');
        $response->assertSee('What joining means');
        $response->assertSee('Contact the academic team');
        $response->assertDontSee('Need a learner account now?');
        $response->assertDontSee('طلب انضمام كمدرب');
    }
}
