<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\IdentityThemes;
use App\Support\ThemeSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IdentityThemesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_identity_themes_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.identity-themes'))
            ->assertOk()
            ->assertSee('قوالب الهوية')
            ->assertSee('الجامعة العربية المفتوحة')
            ->assertSee('جامعة الملك خالد')
            ->assertSee('جامعة طيبة')
            ->assertSee('هويات الجامعات')
            ->assertSee('الأخضر الوطني')
            ->assertSee('الكحلي والذهبي')
            ->assertSee('حفظ كهوية مخصصة');
    }

    public function test_guest_cannot_open_identity_themes_page(): void
    {
        $this->get(route('admin.identity-themes'))->assertRedirect();
    }

    public function test_admin_can_apply_a_preset_from_the_studio(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test('admin.identity-themes-page')
            ->call('applyTheme', 'kku-green')
            ->assertHasNoErrors()
            ->assertSee('تم تفعيل هوية');

        $this->assertSame('kku-green', IdentityThemes::activeKey());
        $this->assertSame('#006838', ThemeSettings::effective('theme_color_primary'));
        $this->assertSame('جامعة الملك خالد', \App\Models\PlatformSetting::get('platform_org_ar'));
    }

    public function test_home_page_exposes_active_identity_theme(): void
    {
        IdentityThemes::apply('charcoal-amber');

        $this->get(route('home', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee('data-identity-theme="charcoal-amber"', false)
            ->assertSee('--np-hero-overlay-rgb', false);
    }
}
