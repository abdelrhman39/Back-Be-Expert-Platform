<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_another_user_from_users_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $target = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email' => 'to-delete@example.com',
            'name_ar' => 'مستخدم للحذف',
        ]);

        Livewire::actingAs($admin)
            ->test('admin.users-page')
            ->call('openDeleteUser', $target->id)
            ->assertSet('deleteUserId', $target->id)
            ->assertSet('deleteBlockReason', null)
            ->call('confirmDeleteUser')
            ->assertSet('deleteUserId', null)
            ->assertSee('تم حذف المستخدم', false);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test('admin.users-page')
            ->call('openDeleteUser', $admin->id)
            ->assertSet('deleteBlockReason', 'لا يمكنك حذف حسابك الحالي أثناء تسجيل الدخول.')
            ->call('confirmDeleteUser');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_cannot_delete_last_active_admin(): void
    {
        $actor = User::factory()->create(['role' => 'admin', 'status' => 'suspended']);
        $lonely = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->assertSame(
            'لا يمكن حذف آخر مسؤول نشط في النظام.',
            app(UserDeletionService::class)->blockedReason($actor, $lonely)
        );
    }
}
