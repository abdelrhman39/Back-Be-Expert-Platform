<?php

use App\Models\AccessPermission;
use App\Models\AccessRole;
use App\Support\AccessControl;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الأدوار والصلاحيات | لوحة التحكم')]
class extends Component
{
    public ?int $activeRoleId = null;
    public array $selectedPermissionIds = [];
    public string $permissionSearch = '';
    public string $permissionScope = '';
    public bool $showRoleForm = false;
    public ?int $editingRoleId = null;
    public string $roleKey = '';
    public string $roleName = '';
    public string $roleDescription = '';
    public string $roleScope = 'all';
    public bool $roleActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.permissions'), 403);
        $this->activeRoleId = AccessRole::query()->orderByDesc('is_super')->orderBy('sort_order')->value('id');
        $this->loadRolePermissions();
    }

    #[Computed]
    public function roles()
    {
        return AccessRole::query()
            ->withCount(['users', 'permissions'])
            ->orderByDesc('is_super')
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();
    }

    #[Computed]
    public function activeRole(): ?AccessRole
    {
        return $this->activeRoleId ? AccessRole::query()->find($this->activeRoleId) : null;
    }

    #[Computed]
    public function permissions()
    {
        return AccessPermission::query()
            ->where('is_active', true)
            ->when($this->permissionScope, fn ($query) => $query->where('scope', $this->permissionScope))
            ->when($this->permissionSearch, function ($query): void {
                $term = '%'.trim($this->permissionSearch).'%';
                $query->where(fn ($query) => $query
                    ->where('name_ar', 'like', $term)
                    ->orWhere('key', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->orderBy('scope')
            ->orderBy('group_key')
            ->orderBy('name_ar')
            ->get()
            ->groupBy('group_key');
    }

    public function setRole(int $roleId): void
    {
        abort_unless(AccessRole::query()->whereKey($roleId)->exists(), 404);
        $this->activeRoleId = $roleId;
        $this->loadRolePermissions();
    }

    public function loadRolePermissions(): void
    {
        $this->selectedPermissionIds = $this->activeRoleId
            ? AccessRole::query()->find($this->activeRoleId)?->permissions()->pluck('access_permissions.id')->map(fn ($id) => (string) $id)->all() ?? []
            : [];
    }

    public function savePermissions(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.permissions'), 403);
        $role = AccessRole::query()->findOrFail($this->activeRoleId);

        if ($role->is_super) {
            $this->addError('role', 'الدور الأعلى يمتلك جميع الصلاحيات ضمنياً.');
            return;
        }

        $role->permissions()->sync(array_map('intval', $this->selectedPermissionIds));
        AccessControl::forget();
        app(\App\Services\AuditLogService::class)->log(
            action: 'access_role.permissions.updated',
            descriptionAr: 'تحديث صلاحيات الدور: '.$role->name_ar,
            group: 'security',
            actor: auth()->user(),
            subject: $role,
            subjectLabel: $role->name_ar,
            newValues: ['permission_ids' => $this->selectedPermissionIds],
        );
        session()->flash('admin_message', 'تم حفظ صلاحيات دور «'.$role->name_ar.'».');
    }

    public function selectVisible(bool $selected): void
    {
        if ($this->activeRole?->is_super) {
            return;
        }

        $visibleIds = $this->permissions->flatten()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->selectedPermissionIds = $selected
            ? array_values(array_unique([...$this->selectedPermissionIds, ...$visibleIds]))
            : array_values(array_diff($this->selectedPermissionIds, $visibleIds));
    }

    public function createRole(): void
    {
        $this->editingRoleId = null;
        $this->roleKey = '';
        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleScope = 'all';
        $this->roleActive = true;
        $this->showRoleForm = true;
        $this->resetValidation();
    }

    public function editRole(int $roleId): void
    {
        $role = AccessRole::query()->findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->roleKey = $role->key;
        $this->roleName = $role->name_ar;
        $this->roleDescription = $role->description ?? '';
        $this->roleScope = $role->scope;
        $this->roleActive = $role->is_active;
        $this->showRoleForm = true;
        $this->resetValidation();
    }

    public function saveRole(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.permissions'), 403);
        $role = $this->editingRoleId ? AccessRole::query()->findOrFail($this->editingRoleId) : new AccessRole;
        $this->validate([
            'roleKey' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9][a-z0-9._-]*$/', Rule::unique('access_roles', 'key')->ignore($role->id)],
            'roleName' => ['required', 'string', 'max:255'],
            'roleDescription' => ['nullable', 'string', 'max:1000'],
            'roleScope' => ['required', Rule::in(['all', 'admin', 'instructor', 'portal'])],
            'roleActive' => ['boolean'],
        ]);

        $isNew = ! $role->exists;
        $role->fill([
            'key' => $role->is_system ? $role->key : $this->roleKey,
            'name_ar' => $this->roleName,
            'description' => $this->roleDescription ?: null,
            'scope' => $this->roleScope,
            'is_active' => $this->roleActive,
        ]);
        if ($isNew) {
            $role->is_system = false;
            $role->is_super = false;
        }
        $role->save();

        $this->activeRoleId = $role->id;
        $this->showRoleForm = false;
        $this->loadRolePermissions();
        AccessControl::forget();
        app(\App\Services\AuditLogService::class)->log(
            action: $isNew ? 'access_role.created' : 'access_role.updated',
            descriptionAr: ($isNew ? 'إنشاء دور: ' : 'تحديث دور: ').$role->name_ar,
            group: 'security',
            actor: auth()->user(),
            subject: $role,
            subjectLabel: $role->name_ar,
            newValues: $role->only(['key', 'name_ar', 'scope', 'is_active']),
        );
        session()->flash('admin_message', 'تم حفظ الدور بنجاح.');
    }

    public function deleteRole(int $roleId): void
    {
        abort_unless(auth()->user()?->canAdmin('users.permissions'), 403);
        $role = AccessRole::query()->withCount('users')->findOrFail($roleId);

        if ($role->is_system || $role->users_count > 0) {
            $this->addError('role', 'لا يمكن حذف دور نظام أو دور مرتبط بمستخدمين.');
            return;
        }

        app(\App\Services\AuditLogService::class)->log(
            action: 'access_role.deleted',
            descriptionAr: 'حذف دور: '.$role->name_ar,
            group: 'security',
            actor: auth()->user(),
            subjectLabel: $role->name_ar,
            oldValues: $role->only(['key', 'name_ar', 'scope']),
        );
        $role->delete();
        $this->activeRoleId = AccessRole::query()->orderByDesc('is_super')->value('id');
        $this->loadRolePermissions();
        AccessControl::forget();
    }

    public function groupLabel(string $group): string
    {
        $labels = config('admin-permissions.groups', []);
        if (str_starts_with($group, 'admin.')) {
            return $labels[substr($group, 6)] ?? $group;
        }

        return match ($group) {
            'instructor.profile' => 'المدرب — الملف الشخصي',
            'instructor.sections' => 'المدرب — الشعب والطلاب',
            'instructor.schedules' => 'المدرب — الجداول',
            'instructor.teams' => 'المدرب — اجتماعات Teams',
            'instructor.materials' => 'المدرب — المواد والملفات',
            'instructor.assignments' => 'المدرب — الواجبات',
            'instructor.attendance' => 'المدرب — الحضور',
            'instructor.grades' => 'المدرب — الدرجات',
            'instructor.exams', 'instructor.questions', 'instructor.exam_attempts',
            'instructor.exam_reports', 'instructor.exam_accommodations' => 'المدرب — الاختبارات',
            default => 'المدرب — '.str_replace(['instructor.', '_'], ['', ' '], $group),
        };
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.users.permissions'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.users'), 'label' => 'المستخدمون'],
        ['label' => 'الأدوار والصلاحيات'],
    ],
])

@if (session('admin_message'))<div class="admin-alert admin-alert--success is-visible">{{ session('admin_message') }}</div>@endif
@error('role')<div class="admin-alert admin-alert--error is-visible">{{ $message }}</div>@enderror

<section class="rbac-hero">
    <div><span>التحكم المركزي بالوصول</span><h1>الأدوار والصلاحيات الديناميكية</h1><p>أنشئ أي عدد من الأدوار، وحدد صلاحيات الإدارة والمدربين، ثم اربط أكثر من دور بالمستخدم مع استثناءات مباشرة.</p></div>
    <button type="button" class="admin-btn-primary" wire:click="createRole"><i class="fa-solid fa-plus"></i> دور جديد</button>
</section>

<div class="rbac-layout">
    <aside class="rbac-roles">
        <header><strong>الأدوار</strong><span>{{ $this->roles->count() }}</span></header>
        @foreach ($this->roles as $role)
            <button type="button" wire:click="setRole({{ $role->id }})" @class(['rbac-role', 'is-active' => $activeRoleId === $role->id]) wire:key="access-role-{{ $role->id }}">
                <span class="rbac-role__icon"><i class="fa-solid {{ $role->is_super ? 'fa-crown' : 'fa-user-shield' }}"></i></span>
                <span class="rbac-role__body"><strong>{{ $role->name_ar }}</strong><small>{{ $role->permissions_count }} صلاحية · {{ $role->users_count }} مستخدم</small></span>
                @if (! $role->is_active)<em>موقوف</em>@endif
            </button>
        @endforeach
    </aside>

    <main class="rbac-workspace">
        @if ($this->activeRole)
            <header class="rbac-workspace__head">
                <div>
                    <span>{{ $this->activeRole->scope }} · {{ $this->activeRole->key }}</span>
                    <h2>{{ $this->activeRole->name_ar }}</h2>
                    <p>{{ $this->activeRole->description }}</p>
                </div>
                <div class="admin-row-actions">
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="editRole({{ $this->activeRole->id }})">بيانات الدور</button>
                    @if (! $this->activeRole->is_system)
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteRole({{ $this->activeRole->id }})" wire:confirm="حذف هذا الدور؟">حذف</button>
                    @endif
                </div>
            </header>

            @if ($this->activeRole->is_super)
                <div class="rbac-super-note"><i class="fa-solid fa-crown"></i><div><strong>دور الإدارة العليا</strong><p>يمتلك جميع الصلاحيات الحالية والمستقبلية ضمنياً لحماية الوصول الإداري.</p></div></div>
            @else
                <div class="rbac-filters">
                    <input type="search" class="admin-control" wire:model.live.debounce.250ms="permissionSearch" placeholder="ابحث باسم الصلاحية أو رمزها">
                    <select class="admin-control" wire:model.live="permissionScope"><option value="">كل النطاقات</option><option value="admin">لوحة الإدارة</option><option value="instructor">بوابة المدرب</option><option value="portal">البوابة</option></select>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="selectVisible(true)">تحديد النتائج</button>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="selectVisible(false)">إلغاء النتائج</button>
                </div>
                <div class="rbac-permission-summary"><strong>{{ count($selectedPermissionIds) }}</strong><span>صلاحية محددة لهذا الدور</span></div>
                <div class="rbac-groups">
                    @forelse ($this->permissions as $group => $permissions)
                        <section class="rbac-group" wire:key="permission-group-{{ $group }}">
                            <header><h3>{{ $this->groupLabel($group) }}</h3><span>{{ $permissions->count() }}</span></header>
                            <div>
                                @foreach ($permissions as $permission)
                                    <label class="rbac-permission" wire:key="permission-{{ $permission->id }}">
                                        <input type="checkbox" value="{{ $permission->id }}" wire:model.live="selectedPermissionIds">
                                        <span><strong>{{ $permission->name_ar }}</strong><code>{{ $permission->key }}</code></span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <div class="rbac-empty">لا توجد صلاحيات مطابقة للبحث.</div>
                    @endforelse
                </div>
                <div class="rbac-savebar"><span>لن تطبق التغييرات حتى الحفظ.</span><button type="button" class="admin-btn-primary" wire:click="savePermissions">حفظ صلاحيات الدور</button></div>
            @endif
        @endif
    </main>
</div>

@if ($showRoleForm)
    <div class="rbac-modal" wire:click.self="$set('showRoleForm', false)">
        <section>
            <header><div><span>إعداد الدور</span><h2>{{ $editingRoleId ? 'تعديل الدور' : 'دور جديد' }}</h2></div><button type="button" wire:click="$set('showRoleForm', false)">×</button></header>
            <div class="rbac-modal__body">
                <label>الاسم العربي *<input type="text" class="admin-control" wire:model="roleName">@error('roleName')<small>{{ $message }}</small>@enderror</label>
                <label>المفتاح التقني *<input type="text" class="admin-control" wire:model="roleKey" dir="ltr" @disabled($editingRoleId && $this->activeRole?->is_system)>@error('roleKey')<small>{{ $message }}</small>@enderror</label>
                <label>النطاق<select class="admin-control" wire:model="roleScope"><option value="all">كل المنصة</option><option value="admin">الإدارة</option><option value="instructor">المدرب</option><option value="portal">البوابة</option></select></label>
                <label>الوصف<textarea class="admin-control" wire:model="roleDescription" rows="3"></textarea></label>
                <label class="rbac-role-active"><input type="checkbox" wire:model="roleActive"> الدور نشط وقابل للإسناد</label>
            </div>
            <footer><button type="button" class="admin-btn-primary" wire:click="saveRole">حفظ الدور</button><button type="button" class="admin-btn-secondary" wire:click="$set('showRoleForm', false)">إلغاء</button></footer>
        </section>
    </div>
@endif

@push('styles')
<style>
    .rbac-hero{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.4rem;margin-bottom:1rem;border-radius:18px;background:linear-gradient(135deg,#123b2a,#1b8354);color:#fff}.rbac-hero span{font-size:.68rem;font-weight:900;opacity:.75}.rbac-hero h1{margin:.25rem 0;font-size:1.35rem}.rbac-hero p{margin:0;max-width:760px;font-size:.76rem;opacity:.85}
    .rbac-layout{display:grid;grid-template-columns:260px minmax(0,1fr);gap:1rem}.rbac-roles,.rbac-workspace{border:1px solid #dbe7e0;border-radius:16px;background:#fff;overflow:hidden}.rbac-roles>header{display:flex;justify-content:space-between;padding:1rem;border-bottom:1px solid #e2e8f0}.rbac-roles>header span{color:#64748b}
    .rbac-role{display:flex;align-items:center;width:100%;gap:.65rem;padding:.75rem;border:0;border-bottom:1px solid #edf2ef;background:#fff;text-align:start;cursor:pointer}.rbac-role:hover,.rbac-role.is-active{background:#effaf4}.rbac-role.is-active{box-shadow:inset -3px 0 #1b8354}.rbac-role__icon{display:grid;place-items:center;width:2rem;height:2rem;border-radius:9px;background:#e9f6ef;color:#166534}.rbac-role__body{display:flex;flex:1;min-width:0;flex-direction:column}.rbac-role__body strong{font-size:.76rem}.rbac-role__body small{font-size:.62rem;color:#64748b}.rbac-role em{font-size:.58rem;color:#b91c1c}
    .rbac-workspace__head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.15rem;border-bottom:1px solid #e2e8f0}.rbac-workspace__head span{font:700 .62rem monospace;color:#64748b}.rbac-workspace__head h2{margin:.2rem 0;font-size:1.05rem}.rbac-workspace__head p{margin:0;color:#64748b;font-size:.7rem}
    .rbac-super-note{display:flex;align-items:flex-start;gap:.8rem;margin:1rem;padding:1rem;border:1px solid #fde68a;border-radius:13px;background:#fffbeb;color:#92400e}.rbac-super-note i{font-size:1.2rem}.rbac-super-note p{margin:.2rem 0 0;font-size:.72rem}
    .rbac-filters{display:grid;grid-template-columns:minmax(220px,1fr) 180px auto auto;gap:.55rem;padding:1rem}.rbac-permission-summary{display:flex;align-items:baseline;gap:.45rem;padding:0 1rem .75rem}.rbac-permission-summary strong{font-size:1.2rem;color:#166534}.rbac-permission-summary span{font-size:.68rem;color:#64748b}
    .rbac-groups{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;padding:0 1rem 5rem}.rbac-group{border:1px solid #dbe7e0;border-radius:12px;overflow:hidden}.rbac-group>header{display:flex;justify-content:space-between;padding:.7rem;background:#f3f8f5}.rbac-group h3{margin:0;font-size:.76rem;color:#145a38}.rbac-group header span{font-size:.65rem;color:#64748b}.rbac-group>div{display:grid}.rbac-permission{display:flex;align-items:flex-start;gap:.55rem;padding:.6rem;border-top:1px solid #edf2ef;cursor:pointer}.rbac-permission:has(input:checked){background:#f0fdf4}.rbac-permission input{margin-top:.2rem;accent-color:#1b8354}.rbac-permission span{display:flex;min-width:0;flex-direction:column}.rbac-permission strong{font-size:.7rem}.rbac-permission code{font-size:.58rem;color:#64748b;direction:ltr;text-align:left}.rbac-empty{padding:2rem;color:#64748b}
    .rbac-savebar{position:sticky;bottom:0;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 1rem;border-top:1px solid #dbe7e0;background:rgba(255,255,255,.96);box-shadow:0 -8px 25px rgba(15,81,50,.06)}.rbac-savebar span{font-size:.65rem;color:#64748b}
    .rbac-modal{position:fixed;z-index:1500;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.55)}.rbac-modal>section{width:min(540px,100%);border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(0,0,0,.25);overflow:hidden}.rbac-modal header,.rbac-modal footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:1rem;border-bottom:1px solid #e2e8f0}.rbac-modal header span{font-size:.62rem;color:#64748b}.rbac-modal header h2{margin:.15rem 0 0;font-size:1rem}.rbac-modal header>button{border:0;background:none;font-size:1.4rem;cursor:pointer}.rbac-modal__body{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;padding:1rem}.rbac-modal__body label{display:grid;gap:.3rem;font-size:.7rem;font-weight:800}.rbac-modal__body label:nth-child(4){grid-column:1/-1}.rbac-modal__body small{color:#b91c1c}.rbac-role-active{display:flex!important;align-items:center;grid-column:1/-1}.rbac-modal footer{justify-content:flex-start;border-top:1px solid #e2e8f0;border-bottom:0}
    @media(max-width:950px){.rbac-layout{grid-template-columns:1fr}.rbac-roles{max-height:300px;overflow:auto}.rbac-filters{grid-template-columns:1fr 1fr}.rbac-groups{grid-template-columns:1fr}}@media(max-width:600px){.rbac-hero,.rbac-workspace__head{align-items:flex-start;flex-direction:column}.rbac-filters,.rbac-modal__body{grid-template-columns:1fr}.rbac-modal__body label:nth-child(4){grid-column:auto}}
</style>
@endpush

@include('partials.admin.shell-end')
