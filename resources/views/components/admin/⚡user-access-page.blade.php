<?php

use App\Models\AccessPermission;
use App\Models\AccessRole;
use App\Models\User;
use App\Support\AccessControl;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('صلاحيات المستخدم | لوحة التحكم')]
class extends Component
{
    public User $user;
    public array $roleIds = [];
    public array $permissionEffects = [];
    public string $search = '';
    public string $scope = '';

    public function mount(User $user): void
    {
        abort_unless(auth()->user()?->canAdmin('users.permissions'), 403);
        $this->user = $user;
        $this->roleIds = $user->accessRoles()->pluck('access_roles.id')->map(fn ($id) => (string) $id)->all();
        $this->permissionEffects = AccessPermission::query()->pluck('id')->mapWithKeys(fn ($id) => [(string) $id => 'inherit'])->all();

        foreach ($user->directPermissions()->get() as $permission) {
            $this->permissionEffects[(string) $permission->id] = $permission->pivot->effect;
        }
    }

    #[Computed]
    public function roles()
    {
        return AccessRole::query()
            ->where('is_active', true)
            ->withCount(['users', 'permissions'])
            ->orderByDesc('is_super')
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();
    }

    #[Computed]
    public function permissions()
    {
        return AccessPermission::query()
            ->where('is_active', true)
            ->when($this->scope, fn ($query) => $query->where('scope', $this->scope))
            ->when($this->search, function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(fn ($query) => $query
                    ->where('name_ar', 'like', $term)
                    ->orWhere('key', 'like', $term));
            })
            ->orderBy('group_key')
            ->orderBy('name_ar')
            ->get()
            ->groupBy('group_key');
    }

    #[Computed]
    public function inheritedPermissionIds(): array
    {
        $roles = AccessRole::query()
            ->whereKey($this->roleIds)
            ->with('permissions:id')
            ->get();

        if ($roles->contains('is_super', true)) {
            return AccessPermission::query()->where('is_active', true)->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $roles
            ->flatMap->permissions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.permissions'), 403);
        $selectedRoles = AccessRole::query()->whereKey($this->roleIds)->get();

        if ($this->user->is(auth()->user()) && ! $selectedRoles->contains('is_super', true)) {
            $this->addError('access', 'لا يمكنك إزالة دور الإدارة العليا من حسابك الحالي.');
            return;
        }

        $allowIds = [];
        $denyIds = [];
        foreach ($this->permissionEffects as $permissionId => $effect) {
            if ($effect === 'allow') {
                $allowIds[] = (int) $permissionId;
            } elseif ($effect === 'deny') {
                $denyIds[] = (int) $permissionId;
            }
        }

        if ($this->user->is(auth()->user())) {
            $protectedIds = AccessPermission::query()->whereIn('key', ['admin.access', 'users.permissions'])->pluck('id')->all();
            if (array_intersect($protectedIds, $denyIds)) {
                $this->addError('access', 'لا يمكنك منع دخول الإدارة أو إدارة الصلاحيات عن حسابك الحالي.');
                return;
            }
        }

        AccessControl::syncUserRoles($this->user, array_map('intval', $this->roleIds), auth()->user());
        AccessControl::syncUserOverrides($this->user, $allowIds, $denyIds, auth()->user());
        app(\App\Services\AuditLogService::class)->log(
            action: 'user.access.updated',
            descriptionAr: 'تحديث أدوار وصلاحيات المستخدم: '.$this->user->displayName(),
            group: 'security',
            actor: auth()->user(),
            subject: $this->user,
            subjectLabel: $this->user->displayName(),
            newValues: ['role_ids' => $this->roleIds, 'allow_ids' => $allowIds, 'deny_ids' => $denyIds],
        );
        session()->flash('admin_message', 'تم حفظ أدوار واستثناءات المستخدم.');
    }

    public function resetOverrides(): void
    {
        foreach ($this->permissionEffects as $permissionId => $effect) {
            $this->permissionEffects[$permissionId] = 'inherit';
        }
    }

    public function groupLabel(string $group): string
    {
        if (str_starts_with($group, 'admin.')) {
            return config('admin-permissions.groups.'.substr($group, 6), $group);
        }

        return 'صلاحيات المدرب — '.str_replace(['instructor.', '_'], ['', ' '], $group);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.users'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.users'), 'label' => 'المستخدمون'],
        ['href' => route('admin.users.show', $user), 'label' => $user->displayName()],
        ['label' => 'الأدوار والصلاحيات'],
    ],
])

@if (session('admin_message'))<div class="admin-alert admin-alert--success is-visible">{{ session('admin_message') }}</div>@endif
@error('access')<div class="admin-alert admin-alert--error is-visible">{{ $message }}</div>@enderror

<section class="user-access-hero">
    <div class="user-access-avatar">{{ $user->initials() }}</div>
    <div><span>تحكم دقيق بالمستخدم #{{ $user->id }}</span><h1>{{ $user->displayName() }}</h1><p>{{ $user->email }} · نوع الحساب الأساسي: {{ \App\Support\UserOptions::roleLabel($user->role) }}</p></div>
    <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary">ملف المستخدم</a>
</section>

<section class="admin-crud-card user-access-section">
    <header><div><h2>الأدوار المسندة</h2><p>يمكن إسناد أكثر من دور؛ يحصل المستخدم على مجموع صلاحياتها.</p></div><a href="{{ route('admin.users.permissions') }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة الأدوار</a></header>
    <div class="user-access-roles">
        @foreach ($this->roles as $role)
            <label @class(['user-access-role', 'is-super' => $role->is_super])>
                <input type="checkbox" value="{{ $role->id }}" wire:model.live="roleIds">
                <span class="user-access-role__icon"><i class="fa-solid {{ $role->is_super ? 'fa-crown' : 'fa-user-shield' }}"></i></span>
                <span><strong>{{ $role->name_ar }}</strong><small>{{ $role->description }}</small><em>{{ $role->permissions_count }} صلاحية · {{ $role->scope }}</em></span>
            </label>
        @endforeach
    </div>
</section>

<section class="admin-crud-card user-access-section">
    <header><div><h2>الاستثناءات المباشرة</h2><p>«سماح» يضيف صلاحية، و«منع» يتغلب على الأدوار، و«موروثة» تتبع الأدوار.</p></div><button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="resetOverrides">إلغاء كل الاستثناءات</button></header>
    <div class="user-access-filters">
        <input type="search" class="admin-control" wire:model.live.debounce.250ms="search" placeholder="ابحث في جميع الصلاحيات">
        <select class="admin-control" wire:model.live="scope"><option value="">كل النطاقات</option><option value="admin">الإدارة</option><option value="instructor">المدرب</option><option value="portal">البوابة</option></select>
        <div class="user-access-legend"><span class="is-inherit">موروثة</span><span class="is-allow">سماح</span><span class="is-deny">منع</span></div>
    </div>
    <div class="user-access-permissions">
        @foreach ($this->permissions as $group => $permissions)
            <section wire:key="user-permission-group-{{ $group }}">
                <header><strong>{{ $this->groupLabel($group) }}</strong><span>{{ $permissions->count() }}</span></header>
                @foreach ($permissions as $permission)
                    @php($inherited = in_array($permission->id, $this->inheritedPermissionIds, true))
                    <div class="user-access-permission" wire:key="user-permission-{{ $permission->id }}">
                        <div><strong>{{ $permission->name_ar }}</strong><code>{{ $permission->key }}</code>@if($inherited)<small>ممنوحة حالياً من أحد الأدوار</small>@endif</div>
                        <div class="user-access-effect">
                            <label class="is-inherit"><input type="radio" value="inherit" wire:model.live="permissionEffects.{{ $permission->id }}"> موروثة</label>
                            <label class="is-allow"><input type="radio" value="allow" wire:model.live="permissionEffects.{{ $permission->id }}"> سماح</label>
                            <label class="is-deny"><input type="radio" value="deny" wire:model.live="permissionEffects.{{ $permission->id }}"> منع</label>
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach
    </div>
    <div class="user-access-save"><span>الأولوية: الإدارة العليا ← المنع المباشر ← السماح المباشر ← صلاحيات الأدوار.</span><button type="button" class="admin-btn-primary" wire:click="save">حفظ صلاحيات المستخدم</button></div>
</section>

@push('styles')
<style>
    .user-access-hero{display:flex;align-items:center;gap:1rem;padding:1.2rem;margin-bottom:1rem;border-radius:18px;background:linear-gradient(135deg,#123b2a,#1b8354);color:#fff}.user-access-avatar{display:grid;place-items:center;width:3.2rem;height:3.2rem;border-radius:14px;background:rgba(255,255,255,.16);font-weight:900}.user-access-hero>div:nth-child(2){flex:1}.user-access-hero span{font-size:.65rem;opacity:.75}.user-access-hero h1{margin:.15rem 0;font-size:1.2rem}.user-access-hero p{margin:0;font-size:.7rem;opacity:.82}
    .user-access-section>header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem}.user-access-section h2{margin:0;font-size:1rem}.user-access-section header p{margin:.25rem 0 0;color:#64748b;font-size:.7rem}
    .user-access-roles{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem}.user-access-role{display:flex;align-items:flex-start;gap:.6rem;padding:.8rem;border:1px solid #dbe7e0;border-radius:12px;cursor:pointer}.user-access-role:has(input:checked){border-color:#1b8354;background:#f0fdf4}.user-access-role.is-super{border-color:#fde68a}.user-access-role input{margin-top:.25rem;accent-color:#1b8354}.user-access-role__icon{display:grid;place-items:center;width:2rem;height:2rem;flex:0 0 auto;border-radius:9px;background:#e9f6ef;color:#166534}.user-access-role>span:last-child{display:flex;min-width:0;flex-direction:column}.user-access-role strong{font-size:.75rem}.user-access-role small{margin:.2rem 0;color:#64748b;font-size:.62rem}.user-access-role em{font-style:normal;font-size:.58rem;color:#166534}
    .user-access-filters{display:grid;grid-template-columns:1fr 190px auto;gap:.65rem;margin-bottom:.8rem}.user-access-legend{display:flex;align-items:center;gap:.35rem}.user-access-legend span{padding:.35rem .5rem;border-radius:999px;font-size:.62rem;font-weight:800}.is-inherit{color:#475569}.user-access-legend .is-inherit{background:#f1f5f9}.is-allow{color:#166534}.user-access-legend .is-allow{background:#dcfce7}.is-deny{color:#b91c1c}.user-access-legend .is-deny{background:#fee2e2}
    .user-access-permissions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;padding-bottom:4rem}.user-access-permissions>section{border:1px solid #dbe7e0;border-radius:12px;overflow:hidden}.user-access-permissions>section>header{display:flex;justify-content:space-between;padding:.7rem;background:#f3f8f5;font-size:.72rem;color:#145a38}
    .user-access-permission{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.65rem;border-top:1px solid #edf2ef}.user-access-permission>div:first-child{display:flex;min-width:0;flex-direction:column}.user-access-permission strong{font-size:.68rem}.user-access-permission code{font-size:.56rem;color:#64748b;direction:ltr;text-align:left}.user-access-permission small{font-size:.56rem;color:#166534}.user-access-effect{display:flex;gap:.25rem}.user-access-effect label{display:flex;align-items:center;gap:.2rem;padding:.25rem .35rem;border:1px solid #e2e8f0;border-radius:7px;font-size:.58rem;cursor:pointer}.user-access-effect label:has(input:checked).is-allow{border-color:#86efac;background:#dcfce7}.user-access-effect label:has(input:checked).is-deny{border-color:#fca5a5;background:#fee2e2}.user-access-effect label:has(input:checked).is-inherit{background:#f1f5f9}
    .user-access-save{position:sticky;bottom:0;display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:0 -1rem -1rem;padding:.8rem 1rem;border-top:1px solid #dbe7e0;background:rgba(255,255,255,.96)}.user-access-save span{font-size:.62rem;color:#64748b}
    @media(max-width:1000px){.user-access-roles{grid-template-columns:repeat(2,1fr)}.user-access-permissions{grid-template-columns:1fr}}@media(max-width:650px){.user-access-hero,.user-access-section>header,.user-access-save{align-items:flex-start;flex-direction:column}.user-access-roles,.user-access-filters{grid-template-columns:1fr}.user-access-permission{align-items:flex-start;flex-direction:column}.user-access-effect{flex-wrap:wrap}}
</style>
@endpush

@include('partials.admin.shell-end')
