<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_guest_cart_uses_professional_layout(): void
    {
        $response = $this->get(route('cart', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('apply-hero', false);
        $response->assertSee('سلة البرامج');
        $response->assertSee('السلة فارغة');
        $response->assertSee('تصفّح البرامج');
        $response->assertSee('كيف تتم العملية؟');
        $response->assertSee(route('courses.index', ['locale' => 'ar']), false);
        $response->assertSee(route('wishlist', ['locale' => 'ar']), false);
        $response->assertDontSee('سلة التسوق فارغة حالياً');
    }

    public function test_english_guest_cart_uses_translated_copy(): void
    {
        $response = $this->get(route('cart', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Your cart');
        $response->assertSee('Your cart is empty');
        $response->assertSee('Browse programs');
        $response->assertSee('How it works');
        $response->assertDontSee('سلة البرامج');
        $response->assertDontSee('السلة فارغة');
    }
}
