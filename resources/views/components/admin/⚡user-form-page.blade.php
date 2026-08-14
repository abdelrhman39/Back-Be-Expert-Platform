<?php

use App\Models\User;
use App\Models\AccessRole;
use App\Support\AccessControl;
use App\Support\UserOptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('مستخدم | لوحة التحكم')]
class extends Component
{
    public ?int $userId = null;

    public string $name = '';

    public string $nameAr = '';

    public string $email = '';

    public string $phone = '';

    public string $nationalId = '';

    public string $role = 'student';

    public string $status = 'active';

    public string $locale = 'ar';

    public string $password = '';

    public string $passwordConfirmation = '';

    public array $accessRoleIds = [];

    public function mount(?User $user = null): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        if (! $user) {
            $defaultRoleId = AccessRole::query()->where('key', 'student-default')->value('id');
            $this->accessRoleIds = $defaultRoleId ? [(string) $defaultRoleId] : [];
            return;
        }

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->nameAr = $user->name_ar ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->nationalId = $user->national_id ?? '';
        $this->role = $user->role ?? 'student';
        $this->status = $user->status ?? 'active';
        $this->locale = $user->locale ?? 'ar';
        $this->accessRoleIds = $user->accessRoles()->pluck('access_roles.id')->map(fn ($id) => (string) $id)->all();
    }

    #[Computed]
    public function accessRoles()
    {
        return AccessRole::query()->where('is_active', true)->orderByDesc('is_super')->orderBy('name_ar')->get();
    }

    public function updatedRole(): void
    {
        if (! auth()->user()?->canAdmin('users.permissions')) {
            return;
        }

        $key = match ($this->role) {
            'admin' => 'super-admin',
            'sales' => 'crm-sales',
            'instructor' => 'instructor.viewer',
            default => 'student-default',
        };
        $roleId = AccessRole::query()->where('key', $key)->value('id');
        $this->accessRoleIds = $roleId ? [(string) $roleId] : [];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'nameAr' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($this->userId)],
            'nationalId' => ['nullable', 'string', 'max:10', Rule::unique('users', 'national_id')->ignore($this->userId)],
            'role' => ['required', Rule::in(array_keys(UserOptions::roles()))],
            'status' => ['required', Rule::in(array_keys(UserOptions::statuses()))],
            'locale' => ['required', Rule::in(array_keys(UserOptions::locales()))],
            'accessRoleIds' => ['array'],
            'accessRoleIds.*' => ['integer', 'distinct', Rule::exists('access_roles', 'id')->where('is_active', true)],
        ];

        if ($this->userId) {
            if ($this->password !== '') {
                $rules['password'] = ['required', 'string', 'min:8', 'same:passwordConfirmation'];
            }
        } else {
            $rules['password'] = ['required', 'string', 'min:8', 'same:passwordConfirmation'];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'name_ar' => $this->nameAr ?: null,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'national_id' => $this->nationalId ?: null,
            'role' => $this->role,
            'status' => $this->status,
            'locale' => $this->locale,
        ];

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            $user = User::query()->findOrFail($this->userId);

            if ($this->status === 'active') {
                $data['failed_login_attempts'] = 0;
                $data['locked_until'] = null;
            }

            $user->update($data);
            if (auth()->user()?->canAdmin('users.permissions')) {
                $this->syncAccessRoles($user);
            }
            session()->flash('admin_message', 'تم تحديث المستخدم.');

            $this->redirectRoute('admin.users.show', $user, navigate: true);

            return;
        }

        $user = User::query()->create($data);
        if (auth()->user()?->canAdmin('users.permissions')) {
            $this->syncAccessRoles($user);
        }
        session()->flash('admin_message', 'تم إنشاء المستخدم.');

        $this->redirectRoute('admin.users.show', $user, navigate: true);
    }

    private function syncAccessRoles(User $user): void
    {
        $roles = AccessRole::query()->whereKey($this->accessRoleIds)->get();
        if ($user->is(auth()->user()) && ! $roles->contains('is_super', true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'accessRoleIds' => 'لا يمكنك إزالة دور الإدارة العليا من حسابك الحالي.',
            ]);
        }

        AccessControl::syncUserRoles($user, array_map('intval', $this->accessRoleIds), auth()->user());
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.users'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.users'), 'label' => 'المستخدمون'],
        ['label' => $userId ? 'تعديل' : 'إضافة'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $userId ? 'تعديل المستخدم' : 'إضافة مستخدم' }}</h2>
        <a href="{{ route('admin.users') }}" class="admin-btn-secondary admin-btn-secondary--sm">← القائمة</a>
    </div>

    <form wire:submit="save">
        <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label>الاسم (Latin)</label>
            <input type="text" class="admin-control" wire:model="name" required>
            @error('name') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>الاسم (عربي)</label>
            <input type="text" class="admin-control" wire:model="nameAr">
            @error('nameAr') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>البريد الإلكتروني</label>
            <input type="email" class="admin-control" wire:model="email" dir="ltr" required>
            @error('email') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>الجوال</label>
            <input type="text" class="admin-control" wire:model="phone" dir="ltr">
            @error('phone') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>رقم الهوية</label>
            <input type="text" class="admin-control" wire:model="nationalId" dir="ltr" maxlength="10">
            @error('nationalId') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>نوع الحساب الأساسي</label>
            <select class="admin-control" wire:model.live="role">
                @foreach (\App\Support\UserOptions::roles() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('role') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model="status">
                @foreach (\App\Support\UserOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>اللغة</label>
            <select class="admin-control" wire:model="locale">
                @foreach (\App\Support\UserOptions::locales() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('locale') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>{{ $userId ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور' }}</label>
            <input type="password" class="admin-control" wire:model="password" autocomplete="new-password" @if(! $userId) required @endif>
            @error('password') <span class="admin-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="admin-field">
            <label>تأكيد كلمة المرور</label>
            <input type="password" class="admin-control" wire:model="passwordConfirmation" autocomplete="new-password">
        </div>

        @canAdmin('users.permissions')
        <section class="user-form-access" style="grid-column:1/-1">
            <div class="user-form-access__head">
                <div>
                    <h3><i class="fa-solid fa-user-shield"></i> أدوار الوصول الديناميكية</h3>
                    <p>يمكن إسناد أكثر من دور. نوع الحساب يحدد البوابة، بينما هذه الأدوار تحدد ما يستطيع المستخدم عرضه أو تنفيذه.</p>
                </div>
                @if ($userId)
                    <a href="{{ route('admin.users.access', $userId) }}" class="admin-btn-secondary admin-btn-secondary--sm">التحكم الدقيق والاستثناءات</a>
                @endif
            </div>
            <div class="user-form-access__roles">
                @foreach ($this->accessRoles as $accessRole)
                    <label>
                        <input type="checkbox" value="{{ $accessRole->id }}" wire:model="accessRoleIds">
                        <span><strong>{{ $accessRole->name_ar }}</strong><small>{{ $accessRole->description }}</small></span>
                    </label>
                @endforeach
            </div>
            @error('accessRoleIds')<span class="admin-field-error">{{ $message }}</span>@enderror
            @error('accessRoleIds.*')<span class="admin-field-error">{{ $message }}</span>@enderror
        </section>
        @endcanAdmin

        <div class="admin-filter-actions" style="grid-column:1/-1;margin-top:0;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ</button>
            @if ($userId)
                <a href="{{ route('admin.users.show', $userId) }}" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</a>
            @endif
        </div>
        </div>
    </form>
</section>

@push('styles')
<style>
    .user-form-access{margin-top:.5rem;padding:1rem;border:1px solid #dbe7e0;border-radius:14px;background:#f8fbf9}.user-form-access__head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}.user-form-access h3{margin:0;color:#145a38;font-size:.9rem}.user-form-access h3 i{margin-inline-end:.35rem}.user-form-access p{margin:.25rem 0 0;color:#64748b;font-size:.68rem}.user-form-access__roles{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem;margin-top:.8rem}.user-form-access__roles label{display:flex;align-items:flex-start;gap:.5rem;padding:.65rem;border:1px solid #dbe7e0;border-radius:10px;background:#fff;cursor:pointer}.user-form-access__roles label:has(input:checked){border-color:#1b8354;background:#f0fdf4}.user-form-access__roles input{margin-top:.2rem;accent-color:#1b8354}.user-form-access__roles span{display:flex;flex-direction:column}.user-form-access__roles strong{font-size:.7rem}.user-form-access__roles small{font-size:.6rem;color:#64748b}@media(max-width:800px){.user-form-access__head{flex-direction:column}.user-form-access__roles{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.admin.shell-end')
