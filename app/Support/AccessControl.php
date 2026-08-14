<?php

namespace App\Support;

use App\Models\AccessPermission;
use App\Models\AccessRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccessControl
{
    /** @var array<int, array{super: bool, grants: array<int, string>, allows: array<int, string>, denies: array<int, string>}> */
    private static array $resolved = [];

    private static ?bool $available = null;

    public static function available(): bool
    {
        return self::$available ??= Schema::hasTable('access_roles')
            && Schema::hasTable('access_permissions')
            && Schema::hasTable('access_role_user')
            && Schema::hasTable('access_user_permissions');
    }

    public static function can(?User $user, string $permission): bool
    {
        return static::decision($user, $permission) ?? false;
    }

    public static function decision(?User $user, string $permission): ?bool
    {
        if (! $user || ! static::available()) {
            return null;
        }

        $access = static::resolve($user);

        if ($access === null) {
            return null;
        }
        if ($access['super']) {
            return true;
        }
        if (in_array($permission, $access['denies'], true)) {
            return false;
        }
        if (in_array($permission, $access['allows'], true)) {
            return true;
        }

        return in_array($permission, $access['grants'], true);
    }

    /** @return array{super: bool, grants: array<int, string>, allows: array<int, string>, denies: array<int, string>}|null */
    public static function resolve(User $user): ?array
    {
        if (array_key_exists($user->id, self::$resolved)) {
            return self::$resolved[$user->id];
        }

        $roles = $user->accessRoles()
            ->where('access_roles.is_active', true)
            ->with(['permissions' => fn ($query) => $query->where('access_permissions.is_active', true)])
            ->get();
        $overrides = $user->directPermissions()
            ->where('access_permissions.is_active', true)
            ->get();

        if ($roles->isEmpty() && $overrides->isEmpty()) {
            return null;
        }

        return self::$resolved[$user->id] = [
            'super' => $roles->contains(fn (AccessRole $role) => $role->is_super),
            'grants' => $roles->flatMap->permissions->pluck('key')->unique()->values()->all(),
            'allows' => $overrides->where('pivot.effect', 'allow')->pluck('key')->unique()->values()->all(),
            'denies' => $overrides->where('pivot.effect', 'deny')->pluck('key')->unique()->values()->all(),
        ];
    }

    public static function forget(?User $user = null): void
    {
        if ($user) {
            unset(self::$resolved[$user->id]);
        } else {
            self::$resolved = [];
            self::$available = null;
        }
    }

    /** @param array<int, int> $roleIds */
    public static function syncUserRoles(User $user, array $roleIds, ?User $actor = null): void
    {
        $sync = collect($roleIds)
            ->mapWithKeys(fn ($id) => [(int) $id => ['assigned_by' => $actor?->id]])
            ->all();
        $user->accessRoles()->sync($sync);
        static::forget($user);
    }

    /** @param array<int, int> $allowIds
     * @param  array<int, int>  $denyIds
     */
    public static function syncUserOverrides(User $user, array $allowIds, array $denyIds, ?User $actor = null): void
    {
        DB::transaction(function () use ($user, $allowIds, $denyIds, $actor): void {
            DB::table('access_user_permissions')->where('user_id', $user->id)->delete();
            $now = now();
            $rows = collect($allowIds)->map(fn ($id) => [
                'user_id' => $user->id,
                'permission_id' => (int) $id,
                'effect' => 'allow',
                'assigned_by' => $actor?->id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->merge(collect($denyIds)->map(fn ($id) => [
                'user_id' => $user->id,
                'permission_id' => (int) $id,
                'effect' => 'deny',
                'assigned_by' => $actor?->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]))->all();

            if ($rows !== []) {
                DB::table('access_user_permissions')->insert($rows);
            }
        });
        static::forget($user);
    }

    public static function permissionByKey(string $key): ?AccessPermission
    {
        return static::available() ? AccessPermission::query()->where('key', $key)->first() : null;
    }

    /**
     * Promote the user to full admin: role, super access, all permissions,
     * and detach any academic-student portal link.
     */
    public static function grantAllPermissions(User $user): void
    {
        $user->forceFill([
            'role' => 'admin',
            'status' => 'active',
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $student = $user->academicStudent;

        if ($student) {
            $student->forceFill([
                'user_id' => null,
                'login_allowed' => false,
            ])->save();
        }

        $user->unsetRelation('academicStudent');

        if (! static::available()) {
            return;
        }

        $super = AccessRole::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('is_super', true)->orWhere('key', 'super-admin');
            })
            ->orderByDesc('is_super')
            ->first();

        if ($super) {
            static::syncUserRoles($user, [$super->id], $user);
        }

        $permissionIds = AccessPermission::query()
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        static::syncUserOverrides($user, $permissionIds, [], $user);
    }
}
