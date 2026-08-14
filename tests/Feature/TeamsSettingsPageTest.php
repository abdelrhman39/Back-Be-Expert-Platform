<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamsSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_teams_settings_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.teams-settings'))
            ->assertOk()
            ->assertSee('إعدادات Microsoft Teams')
            ->assertSee('الحضور التلقائي')
            ->assertSee('تسجيل المحاضرات')
            ->assertSee('دليل ربط Microsoft Teams');
    }

    public function test_guest_cannot_open_teams_settings_page(): void
    {
        $this->get(route('admin.teams-settings'))->assertRedirect();
    }
}
