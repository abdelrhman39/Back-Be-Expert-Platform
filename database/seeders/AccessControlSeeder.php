<?php

namespace Database\Seeders;

use App\Models\AccessPermission;
use App\Models\AccessRole;
use App\Models\User;
use App\Support\AdminPermissions;
use App\Support\InstructorPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $adminDefinitions = AdminPermissions::definitions();
        $instructorDefinitions = InstructorPermissions::definitions();

        foreach ($adminDefinitions as $key => $definition) {
            AccessPermission::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name_ar' => $definition['label'],
                    'description' => $definition['description'] ?? null,
                    'group_key' => 'admin.'.($definition['group'] ?? 'general'),
                    'scope' => 'admin',
                    'is_system' => true,
                    'is_active' => true,
                ],
            );
        }

        foreach ($instructorDefinitions as $key => $definition) {
            AccessPermission::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name_ar' => $definition['label'],
                    'description' => null,
                    'group_key' => $definition['group'],
                    'scope' => 'instructor',
                    'is_system' => true,
                    'is_active' => true,
                ],
            );
        }

        $roles = [
            'super-admin' => [
                'name_ar' => 'مدير النظام الكامل',
                'description' => 'وصول كامل غير قابل للتقييد إلى جميع أجزاء المنصة.',
                'scope' => 'admin',
                'is_super' => true,
                'permissions' => [],
            ],
            'platform-staff' => [
                'name_ar' => 'موظف تشغيل المنصة',
                'description' => 'الصلاحيات التشغيلية الافتراضية للكادر الإداري.',
                'scope' => 'admin',
                'is_super' => false,
                'permissions' => AdminPermissions::roleDefaults('staff'),
            ],
            'crm-sales' => [
                'name_ar' => 'موظف مبيعات CRM',
                'description' => 'الوصول إلى العملاء المسندين وتسجيل التواصل والمتابعة فقط.',
                'scope' => 'admin',
                'is_super' => false,
                'permissions' => AdminPermissions::roleDefaults('sales'),
            ],
            'student-default' => [
                'name_ar' => 'طالب',
                'description' => 'الدور الأساسي لحسابات الطلاب.',
                'scope' => 'portal',
                'is_super' => false,
                'permissions' => [],
            ],
        ];

        foreach (InstructorPermissions::presetLabels() as $preset => $label) {
            $roles[$preset] = [
                'name_ar' => $label,
                'description' => 'حزمة نظام للمدربين، ويمكن تخصيص المستخدم باستثناءات مباشرة.',
                'scope' => 'instructor',
                'is_super' => false,
                'permissions' => InstructorPermissions::presetPermissions($preset),
            ];
        }

        foreach ($roles as $key => $definition) {
            $role = AccessRole::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name_ar' => $definition['name_ar'],
                    'description' => $definition['description'],
                    'scope' => $definition['scope'],
                    'is_system' => true,
                    'is_super' => $definition['is_super'],
                    'is_active' => true,
                ],
            );

            $permissionIds = AccessPermission::query()
                ->whereIn('key', $definition['permissions'])
                ->pluck('id')
                ->all();
            $role->permissions()->sync($permissionIds);
        }

        $roleIds = AccessRole::query()->pluck('id', 'key');

        User::query()->with('academicStaff')->each(function (User $user) use ($roleIds): void {
            $roleKey = match ($user->role) {
                'admin' => 'super-admin',
                'sales' => 'crm-sales',
                'instructor' => $user->academicStaff?->permission_preset ?: 'instructor.viewer',
                'staff' => 'platform-staff',
                default => 'student-default',
            };
            $roleId = $roleIds[$roleKey] ?? null;

            if ($roleId) {
                DB::table('access_role_user')->updateOrInsert(
                    ['role_id' => $roleId, 'user_id' => $user->id],
                    ['assigned_by' => null, 'created_at' => now(), 'updated_at' => now()],
                );
            }
        });
    }
}
