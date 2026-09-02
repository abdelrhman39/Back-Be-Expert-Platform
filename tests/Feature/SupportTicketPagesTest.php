<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_search_page_renders_professional_layout(): void
    {
        $response = $this->get(route('support.ticket.search', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('support-track', false);
        $response->assertSee('support-panel', false);
        $response->assertSee('البحث عن تذكرة');
        $response->assertSee('متابعة آمنة');
        $response->assertSee('رقم التذكرة');
        $response->assertDontSee('support-aside', false);
    }

    public function test_ticket_new_page_renders_professional_layout(): void
    {
        $response = $this->get(route('support.ticket.new', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('support-compose', false);
        $response->assertSee('إنشاء تذكرة دعم');
        $response->assertSee('كيف تتم المعالجة');
        $response->assertSee('إرسال التذكرة');
        $response->assertDontSee('مرحباً');
    }

    public function test_english_ticket_search_page_renders(): void
    {
        $this->get(route('support.ticket.search', ['locale' => 'en']))
            ->assertOk()
            ->assertSee('Find a support ticket')
            ->assertSee('Secure follow-up');
    }
}
