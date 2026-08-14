<?php

namespace Tests\Feature;

use App\Models\AccessPermission;
use App\Models\AccessRole;
use App\Models\User;
use App\Support\AccessControl;
use App\Support\AdminPermissions;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DynamicAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_grants_and_direct_deny_has_precedence(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $permission = AccessPermission::query()->create([
            'key' => 'reports.view',
            'name_ar' => 'عرض التقارير',
            'group_key' => 'admin.reports',
            'scope' => 'admin',
        ]);
        $role = AccessRole::query()->create([
            'key' => 'reporter',
            'name_ar' => 'مسؤول التقارير',
            'scope' => 'admin',
        ]);
        $role->permissions()->attach($permission);
        $user->accessRoles()->attach($role);
        AccessControl::forget();

        $this->assertTrue(AccessControl::can($user, 'reports.view'));

        $user->directPermissions()->attach($permission, ['effect' => 'deny']);
        AccessControl::forget($user);

        $this->assertFalse(AccessControl::can($user, 'reports.view'));
    }

    public function test_direct_allow_and_super_role_are_resolved_safely(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $permission = AccessPermission::query()->create([
            'key' => 'special.action',
            'name_ar' => 'إجراء خاص',
            'group_key' => 'admin.special',
            'scope' => 'admin',
        ]);
        $user->directPermissions()->attach($permission, ['effect' => 'allow']);
        AccessControl::forget();

        $this->assertTrue(AccessControl::can($user, 'special.action'));

        $super = AccessRole::query()->create([
            'key' => 'root',
            'name_ar' => 'إدارة عليا',
            'scope' => 'admin',
            'is_super' => true,
        ]);
        $user->accessRoles()->attach($super);
        $user->directPermissions()->updateExistingPivot($permission->id, ['effect' => 'deny']);
        AccessControl::forget($user);

        $this->assertTrue(AccessControl::can($user, 'special.action'));
        $this->assertTrue(AccessControl::can($user, 'any.future.permission'));
    }

    public function test_admin_can_open_dynamic_roles_and_user_access_pages(): void
    {
        $this->seed(AccessControlSeeder::class);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $target = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $admin->accessRoles()->attach(AccessRole::query()->where('key', 'super-admin')->firstOrFail());
        $target->accessRoles()->attach(AccessRole::query()->where('key', 'student-default')->firstOrFail());
        AccessControl::forget();

        $this->actingAs($admin)
            ->get(route('admin.users.permissions'))
            ->assertOk()
            ->assertSee('الأدوار والصلاحيات الديناميكية');

        $this->actingAs($admin)
            ->get(route('admin.users.access', $target))
            ->assertOk()
            ->assertSee('الاستثناءات المباشرة');
    }

    public function test_every_protected_admin_route_has_an_explicit_permission(): void
    {
        $missing = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'admin.'))
            ->filter(fn ($route) => in_array('admin.permission', $route->gatherMiddleware(), true))
            ->filter(fn ($route) => AdminPermissions::routePermission($route->getName()) === null)
            ->map(fn ($route) => $route->getName())
            ->values()
            ->all();

        $this->assertSame([], $missing, 'Protected admin routes without permissions: '.implode(', ', $missing));
        $this->assertFalse(AdminPermissions::canAccessRoute(
            User::factory()->create(['role' => 'admin', 'status' => 'active']),
            'admin.unmapped-sensitive-route',
        ));
    }
}
