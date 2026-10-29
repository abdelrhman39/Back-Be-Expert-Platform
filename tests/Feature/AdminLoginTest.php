<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sign_in_with_seeded_example_email(): void
    {
        $this->seed([AccessControlSeeder::class, AdminUserSeeder::class]);

        Livewire::test('admin.login-page')
            ->set('email', 'admin@example.com')
            ->set('password', AdminUserSeeder::PASSWORD)
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(
            User::query()->where('email', 'admin@example.com')->firstOrFail()
        );
    }

    public function test_admin_can_sign_in_with_local_invalid_email(): void
    {
        $this->seed([AccessControlSeeder::class, AdminUserSeeder::class]);

        Livewire::test('admin.login-page')
            ->set('email', 'admin@local.invalid')
            ->set('password', AdminUserSeeder::PASSWORD)
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_page_does_not_expose_demo_credentials(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee('بيئة تجريبية')
            ->assertDontSee('Admin@123')
            ->assertDontSee('admin@local.invalid');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->seed([AccessControlSeeder::class, AdminUserSeeder::class]);

        Livewire::test('admin.login-page')
            ->set('email', 'admin@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('credentials')
            ->assertNoRedirect();

        $this->assertGuest();
    }
}
