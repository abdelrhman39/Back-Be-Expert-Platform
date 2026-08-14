<?php

namespace App\Support;

use App\Models\PlatformSetting;
use App\Models\User;

class AdminPermissions
{
    public const STORAGE_KEY = 'role_permissions_json';

    /** @return array<string, string> */
    public static function groups(): array
    {
        return config('admin-permissions.groups', []);
    }

    /** @return array<string, array{label: string, group: string}> */
    public static function definitions(): array
    {
        return config('admin-permissions.permissions', []);
    }

    /** @return array<int, string> */
    public static function roleDefaults(string $role): array
    {
        return config("admin-permissions.role_defaults.{$role}", []);
    }

    /** @return array<string, array<int, string>> */
    public static function storedOverrides(): array
    {
        $raw = PlatformSetting::get(static::STORAGE_KEY);

        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<int, string> */
    public static function forRole(string $role): array
    {
        if ($role === 'admin') {
            return ['*'];
        }

        $overrides = static::storedOverrides();

        if (array_key_exists($role, $overrides)) {
            return array_values(array_unique($overrides[$role]));
        }

        return static::roleDefaults($role);
    }

    /** @param  array<int, string>  $permissions */
    public static function saveForRole(string $role, array $permissions): void
    {
        if ($role === 'admin') {
            return;
        }

        $overrides = static::storedOverrides();
        $overrides[$role] = array_values(array_unique(array_filter($permissions)));

        PlatformSetting::set(
            static::STORAGE_KEY,
            json_encode($overrides, JSON_UNESCAPED_UNICODE),
            'security',
            'صلاحيات الأدوار'
        );
    }

    public static function can(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $decision = AccessControl::decision($user, $permission);

        if ($decision !== null) {
            return $decision;
        }

        return static::legacyCan($user, $permission);
    }

    public static function legacyCan(User $user, string $permission): bool
    {
        $grants = static::forRole($user->role ?? 'student');

        if (in_array('*', $grants, true)) {
            return true;
        }

        return in_array($permission, $grants, true);
    }

    public static function canAccessAdmin(?User $user): bool
    {
        return static::can($user, 'admin.access');
    }

    /** @return string|array<int, string>|null */
    public static function routePermission(?string $routeName): string|array|null
    {
        if (! $routeName) {
            return null;
        }

        return config('admin-routes', [])[$routeName] ?? null;
    }

    public static function canAccessRoute(?User $user, ?string $routeName): bool
    {
        if (! static::canAccessAdmin($user)) {
            return false;
        }

        $permission = static::routePermission($routeName);

        if ($permission === null) {
            return false;
        }

        $permissions = is_array($permission) ? $permission : [$permission];

        foreach ($permissions as $key) {
            if (static::can($user, $key)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, array{label: string, description: string, icon: string, tone: string}> */
    public static function roleMeta(): array
    {
        return [
            'admin' => [
                'label' => 'المسؤولون',
                'description' => 'تحكم كامل في المنصة والإعدادات',
                'icon' => 'shield',
                'tone' => 'green',
            ],
            'instructor' => [
                'label' => 'المدربون',
                'description' => 'حسابات بوابة المدرب',
                'icon' => 'users',
                'tone' => 'blue',
            ],
            'sales' => [
                'label' => 'فريق المبيعات',
                'description' => 'موظفو التواصل وإدارة العملاء',
                'icon' => 'users',
                'tone' => 'blue',
            ],
            'student' => [
                'label' => 'الطلاب',
                'description' => 'حسابات بوابة المتدرب',
                'icon' => 'cap',
                'tone' => 'gold',
            ],
        ];
    }
}
