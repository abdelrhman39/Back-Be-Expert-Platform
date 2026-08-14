<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoomSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reach_zoom_from_system_governance_and_settings_hub(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.system-settings'))
            ->assertOk()
            ->assertSee(route('admin.zoom-settings'), false)
            ->assertSee('Zoom')
            ->assertSee('ربط Zoom، المضيفون، الحضور والتسجيلات');

        $this->actingAs($admin)
            ->get(route('admin.zoom-settings'))
            ->assertOk()
            ->assertSee('إعدادات Zoom')
            ->assertSee('Microsoft Teams');
    }

    public function test_guest_cannot_open_zoom_settings_page(): void
    {
        $this->get(route('admin.zoom-settings'))->assertRedirect();
    }
}
