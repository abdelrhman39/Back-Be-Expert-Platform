<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_guest_wishlist_uses_professional_layout(): void
    {
        $response = $this->get(route('wishlist', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('apply-hero', false);
        $response->assertSee('البرامج المفضلة');
        $response->assertSee('لا توجد برامج محفوظة');
        $response->assertSee('تصفّح البرامج');
        $response->assertSee('كيف تستخدم المفضلة؟');
        $response->assertSee(route('courses.index', ['locale' => 'ar']), false);
        $response->assertSee(route('cart', ['locale' => 'ar']), false);
        $response->assertDontSee('لا توجد دورات في قائمة المفضلة');
    }

    public function test_english_guest_wishlist_uses_translated_copy(): void
    {
        $response = $this->get(route('wishlist', ['locale' => 'en']));

        $response->assertOk();
        $response->assertSee('Saved programs');
        $response->assertSee('No saved programs');
        $response->assertSee('Browse programs');
        $response->assertSee('How the wishlist works');
        $response->assertDontSee('البرامج المفضلة');
        $response->assertDontSee('لا توجد برامج محفوظة');
    }
}
