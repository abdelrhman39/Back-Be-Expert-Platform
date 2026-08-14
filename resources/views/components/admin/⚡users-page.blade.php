<?php

use App\Models\User;
use App\Models\AccessRole;
use App\Services\UserDeletionService;
use App\Support\AdminPermissions;
use App\Support\UserOptions;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('المستخدمون | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'type')]
    public string $roleTab = 'all';

    public string $search = '';

    public string $status = '';

    #[Url]
    public string $accessRole = '';

    public ?int $deleteUserId = null;

    /** @var array<string, mixed>|null */
    public ?array $deleteImpact = null;

    public ?string $deleteBlockReason = null;

    public ?string $message = null;

    public string $messageKind = 'success';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.view'), 403);
    }

    public function setRoleTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'admin', 'instructor', 'sales', 'student'], true)) {
            return;
        }

        $this->roleTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedAccessRole(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $userId): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        if ($userId === auth()->id()) {
            return;
        }

        $user = User::query()->findOrFail($userId);
        $user->update([
            'status' => $user->status === 'active' ? 'suspended' : 'active',
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    public function unlockUser(int $userId): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        User::query()->whereKey($userId)->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'status' => 'active',
        ]);
    }

    public function openDeleteUser(int $userId, UserDeletionService $deletion): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        $user = User::query()->findOrFail($userId);
        $this->deleteUserId = $user->id;
        $this->deleteImpact = $deletion->impact($user);
        $this->deleteBlockReason = $deletion->blockedReason(auth()->user(), $user);
        $this->resetErrorBag('delete');
    }

    public function closeDeleteUser(): void
    {
        $this->deleteUserId = null;
        $this->deleteImpact = null;
        $this->deleteBlockReason = null;
        $this->resetErrorBag('delete');
    }

    public function confirmDeleteUser(UserDeletionService $deletion): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        if (! $this->deleteUserId) {
            return;
        }

        $user = User::query()->findOrFail($this->deleteUserId);

        try {
            $deletion->delete(auth()->user(), $user);
        } catch (ValidationException $e) {
            $this->deleteBlockReason = collect($e->errors())->flatten()->first();
            $this->addError('delete', $this->deleteBlockReason ?? 'تعذر حذف المستخدم.');

            return;
        }

        $name = $this->deleteImpact['user_name'] ?? $user->displayName();
        $this->closeDeleteUser();
        $this->message = 'تم حذف المستخدم «'.$name.'» نهائياً.';
        $this->messageKind = 'success';
    }

    #[Computed]
    public function roleCounts(): array
    {
        $counts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return [
            'all' => User::query()->count(),
            'admin' => (int) ($counts['admin'] ?? 0),
            'instructor' => (int) ($counts['instructor'] ?? 0),
            'sales' => (int) ($counts['sales'] ?? 0),
            'student' => (int) ($counts['student'] ?? 0),
        ];
    }

    #[Computed]
    public function accessRoles()
    {
        return AccessRole::query()->where('is_active', true)->orderBy('name_ar')->get(['id', 'name_ar']);
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->withCount('orders')
            ->with(['academicStudent', 'accessRoles'])
            ->when($this->roleTab !== 'all', fn ($q) => $q->where('role', $this->roleTab))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhere('national_id', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->accessRole, fn ($q) => $q->whereHas('accessRoles', fn ($role) => $role->where('access_roles.id', $this->accessRole)))
            ->latest()
            ->paginate(20);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.users'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'المستخدمون'],
    ],
])

@if ($message)
    <div class="admin-alert admin-alert--{{ $messageKind === 'success' ? 'info' : 'danger' }} is-visible" wire:key="users-msg-{{ md5($message) }}">
        {{ $message }}
        <button type="button" class="admin-alert__close" wire:click="$set('message', null)" aria-label="إغلاق" style="margin-inline-start:auto;border:0;background:transparent;cursor:pointer;font-size:1.1rem;">×</button>
    </div>
@endif

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-users-hub">
    <div class="admin-users-hub__head">
        <div>
            <h1 class="admin-users-hub__title">إدارة المستخدمين</h1>
            <p class="admin-users-hub__desc">نوع الحساب يحدد البوابة الأساسية، بينما الأدوار الديناميكية تحدد الوصول الفعلي بدقة.</p>
        </div>
        <div class="admin-users-hub__actions">
            @if (auth()->user()?->canAdmin('users.permissions'))
                <a href="{{ route('admin.users.permissions') }}" class="admin-btn-secondary admin-btn-secondary--sm">الأدوار والصلاحيات الديناميكية</a>
            @endif
            @if (auth()->user()?->canAdmin('users.manage'))
                <a href="{{ route('admin.users.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ مستخدم جديد</a>
            @endif
        </div>
    </div>

    <div class="admin-users-type-grid">
        @foreach (AdminPermissions::roleMeta() as $roleKey => $meta)
            <button
                type="button"
                wire:click="setRoleTab('{{ $roleKey }}')"
                @class(['admin-users-type-card', 'is-active' => $roleTab === $roleKey, 'admin-users-type-card--'.$meta['tone']])
            >
                <span class="admin-users-type-card__count">{{ $this->roleCounts[$roleKey] ?? 0 }}</span>
                <span class="admin-users-type-card__label">{{ $meta['label'] }}</span>
                <span class="admin-users-type-card__desc">{{ $meta['description'] }}</span>
            </button>
        @endforeach
        <button
            type="button"
            wire:click="setRoleTab('all')"
            @class(['admin-users-type-card', 'admin-users-type-card--all', 'is-active' => $roleTab === 'all'])
        >
            <span class="admin-users-type-card__count">{{ $this->roleCounts['all'] }}</span>
            <span class="admin-users-type-card__label">الكل</span>
            <span class="admin-users-type-card__desc">جميع أنواع الحسابات</span>
        </button>
    </div>
</section>

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-filter-grid" style="grid-template-columns: 2fr 1fr 1fr;">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="اسم، بريد، جوال، هوية...">
        </div>
        <div class="admin-field">
            <label>دور الوصول الديناميكي</label>
            <select class="admin-control" wire:model.live="accessRole">
                <option value="">كل الأدوار</option>
                @foreach ($this->accessRoles as $role)
                    <option value="{{ $role->id }}">{{ $role->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (UserOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card admin-users-table-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>
            @if ($roleTab === 'all')
                كل المستخدمين
            @else
                {{ AdminPermissions::roleMeta()[$roleTab]['label'] ?? UserOptions::roleLabel($roleTab) }}
            @endif
            <span class="admin-crud-card__meta">— {{ $this->users->total() }}</span>
        </h2>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-data-table admin-users-table">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>البريد</th>
                    <th>الجوال</th>
                    <th>نوع الحساب / أدوار الوصول</th>
                    <th>الحالة</th>
                    <th>الطلبات</th>
                    <th>آخر دخول</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users as $user)
                    <tr wire:key="user-row-{{ $user->id }}">
                        <td>
                            <div class="admin-user-cell">
                                <span class="admin-user-cell__avatar" aria-hidden="true">{{ $user->initials() }}</span>
                                <span class="admin-user-cell__body">
                                    <a href="{{ route('admin.users.show', $user) }}" class="admin-user-cell__name">{{ $user->displayName() }}</a>
                                    <span class="admin-user-cell__meta">#{{ $user->id }}
                                        @if ($user->academicStudent)
                                            · {{ $user->academicStudent->academic_id }}
                                        @endif
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td dir="ltr" class="admin-user-cell__email">{{ $user->email ?? '—' }}</td>
                        <td dir="ltr">{{ $user->phone ?? '—' }}</td>
                        <td>
                            @include('partials.admin.user-role-badge', ['role' => $user->role])
                            <span class="admin-table-sub">{{ $user->accessRoles->pluck('name_ar')->join('، ') ?: 'بدون دور وصول' }}</span>
                        </td>
                        <td>
                            @php
                                $statusBadge = match ($user->status) {
                                    'active' => 'admin-badge--success',
                                    'suspended' => 'admin-badge--danger',
                                    default => 'admin-badge--warn',
                                };
                            @endphp
                            <span @class(['admin-badge', $statusBadge])>{{ UserOptions::statusLabel($user->status) }}</span>
                            @if ($user->isLocked())
                                <span class="admin-badge admin-badge--danger admin-user-cell__lock">مقفل</span>
                            @endif
                        </td>
                        <td>{{ $user->orders_count }}</td>
                        <td>{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                        <td class="admin-table-actions">
                            <div class="admin-users-row-actions">
                                @if (auth()->user()?->canAdmin('users.manage') && $user->id !== auth()->id())
                                    <button
                                        type="button"
                                        class="admin-users-delete-btn"
                                        title="حذف المستخدم"
                                        aria-label="حذف {{ $user->displayName() }}"
                                        wire:click="openDeleteUser({{ $user->id }})"
                                    >
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
                                        </svg>
                                    </button>
                                @endif
                                @include('partials.admin.user-actions-menu', ['user' => $user, 'showDelete' => true])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-users-empty">لا يوجد مستخدمون في هذا النوع.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->users->hasPages())
        {{ $this->users->links() }}
    @endif
</section>

@if ($deleteUserId && $deleteImpact)
    <div class="admin-modal-backdrop" wire:click.self="closeDeleteUser">
        <div class="admin-modal admin-user-delete-modal" role="dialog" aria-modal="true" aria-labelledby="user-delete-title">
            <div class="admin-modal__head">
                <h3 id="user-delete-title"><i class="fa-solid fa-triangle-exclamation" style="color:#b91c1c;"></i> تأكيد حذف المستخدم</h3>
            </div>
            <div class="admin-modal__body">
                <p class="admin-user-delete-modal__lead">
                    أنت على وشك حذف
                    <strong>{{ $deleteImpact['user_name'] }}</strong>
                    @if (! empty($deleteImpact['user_email']))
                        <span dir="ltr">({{ $deleteImpact['user_email'] }})</span>
                    @endif
                    — نوع الحساب: <strong>{{ $deleteImpact['role_label'] }}</strong>.
                </p>

                <div class="admin-user-delete-modal__warn">
                    <strong>بناءً على هذا الإجراء سيحدث التالي:</strong>
                </div>

                <div class="admin-user-delete-modal__section">
                    <h4>آثار لا يمكن التراجع عنها</h4>
                    <ul>
                        @foreach ($deleteImpact['irreversible'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="admin-user-delete-modal__section">
                    <h4>ما سيتم حذفه أو إنهاؤه</h4>
                    <ul>
                        @foreach ($deleteImpact['cascade'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                @if (! empty($deleteImpact['unlink']))
                    <div class="admin-user-delete-modal__section">
                        <h4>ما سيتم فك ربطه (دون حذف السجل بالكامل)</h4>
                        <ul>
                            @foreach ($deleteImpact['unlink'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($deleteImpact['notes']))
                    <div class="admin-user-delete-modal__section admin-user-delete-modal__section--muted">
                        <h4>ملاحظات</h4>
                        <ul>
                            @foreach ($deleteImpact['notes'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($deleteBlockReason)
                    <div class="admin-alert admin-alert--danger is-visible" style="margin-top:0.75rem;">{{ $deleteBlockReason }}</div>
                @endif
                @error('delete')
                    <div class="admin-alert admin-alert--danger is-visible" style="margin-top:0.75rem;">{{ $message }}</div>
                @enderror
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeDeleteUser">إلغاء</button>
                <button
                    type="button"
                    class="admin-btn-primary admin-btn-primary--sm"
                    style="background:#b91c1c;border-color:#b91c1c;"
                    wire:click="confirmDeleteUser"
                    wire:loading.attr="disabled"
                    @disabled($deleteBlockReason)
                >
                    تأكيد الحذف النهائي
                </button>
            </div>
        </div>
    </div>
@endif

@push('styles')
<style>
    .admin-users-hub { padding: 1.25rem 1.35rem; }
    .admin-users-hub__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .admin-users-hub__title { margin: 0 0 0.25rem; font-size: 1.05rem; font-weight: 800; color: var(--sa-ink); }
    .admin-users-hub__desc { margin: 0; font-size: 0.84rem; color: var(--sa-muted); }
    .admin-users-hub__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .admin-users-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10.5rem, 1fr));
        gap: 0.65rem;
    }
    .admin-users-type-card {
        text-align: start;
        border: 1px solid var(--sa-border);
        border-radius: var(--radius-md);
        background: var(--surface-card);
        padding: 0.75rem 0.85rem;
        cursor: pointer;
        font: inherit;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }
    .admin-users-type-card:hover { border-color: var(--sa-green); transform: translateY(-1px); }
    .admin-users-type-card.is-active {
        border-color: var(--sa-green);
        box-shadow: 0 0 0 2px var(--sa-green-soft);
        background: var(--sa-green-soft);
    }
    .admin-users-type-card__count {
        display: block;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--sa-green-dark);
        line-height: 1.1;
    }
    .admin-users-type-card__label {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--sa-ink);
    }
    .admin-users-type-card__desc {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.72rem;
        color: var(--sa-muted);
        line-height: 1.35;
    }
    .admin-users-type-card--admin.is-active .admin-users-type-card__count { color: #166534; }
    .admin-users-type-card--staff.is-active .admin-users-type-card__count { color: #1d4ed8; }
    .admin-users-type-card--student.is-active .admin-users-type-card__count { color: #b45309; }
    .admin-user-cell { display: flex; align-items: center; gap: 0.65rem; min-width: 12rem; }
    .admin-user-cell__avatar {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--sa-green-dark);
        background: var(--sa-green-soft);
        border: 1px solid var(--sa-border);
    }
    .admin-user-cell__name {
        display: block;
        font-weight: 700;
        color: var(--sa-ink);
        text-decoration: none;
    }
    .admin-user-cell__name:hover { color: var(--sa-green-dark); }
    .admin-user-cell__meta { display: block; font-size: 0.72rem; color: var(--sa-muted); margin-top: 0.1rem; }
    .admin-user-cell__email { font-size: 0.82rem; }
    .admin-user-cell__lock { margin-inline-start: 0.25rem; }
    .admin-user-role {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .admin-user-role--admin { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .admin-user-role--staff { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .admin-user-role--student { background: #fef3c7; color: #b45309; border-color: #fde68a; }
    .admin-users-empty { text-align: center; padding: 2rem !important; color: var(--sa-muted); }
    .admin-actions-item--btn {
        border: none;
        background: transparent;
        width: 100%;
        text-align: inherit;
        cursor: pointer;
        font: inherit;
    }
    .admin-actions-item--btn:hover { background: var(--sa-green-soft); color: var(--sa-green-dark); }
    .admin-actions-item--danger { color: #b91c1c; }
    .admin-actions-item--danger:hover { background: #fef2f2; color: #991b1b; }
    .admin-users-table-card .admin-table-actions { min-width: 5.5rem; }
    .admin-users-row-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .admin-users-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 8px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        cursor: pointer;
    }
    .admin-users-delete-btn:hover {
        background: #fee2e2;
        border-color: #f87171;
        color: #991b1b;
    }
    .admin-users-table .admin-table-actions {
        position: sticky;
        inset-inline-start: 0;
        z-index: 1;
        background: var(--surface-card);
    }
    .admin-users-table tbody tr.is-row-actions-open .admin-table-actions {
        z-index: 12;
        background: var(--surface-card);
        box-shadow: none;
    }
    .admin-users-table tbody tr.is-row-actions-open td {
        position: relative;
        z-index: 0;
    }
    .admin-user-delete-modal { max-width: 560px; }
    .admin-user-delete-modal__lead { margin: 0 0 0.85rem; font-size: 0.9rem; line-height: 1.6; color: var(--sa-ink); }
    .admin-user-delete-modal__warn {
        margin-bottom: 0.75rem;
        padding: 0.65rem 0.8rem;
        border-radius: 10px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        font-size: 0.86rem;
    }
    .admin-user-delete-modal__section { margin-bottom: 0.85rem; }
    .admin-user-delete-modal__section h4 {
        margin: 0 0 0.35rem;
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--sa-ink);
    }
    .admin-user-delete-modal__section ul {
        margin: 0;
        padding-inline-start: 1.15rem;
        display: grid;
        gap: 0.35rem;
        font-size: 0.82rem;
        line-height: 1.5;
        color: #334155;
    }
    .admin-user-delete-modal__section--muted h4,
    .admin-user-delete-modal__section--muted ul { color: var(--sa-muted); }
    .admin-user-delete-modal .admin-modal__body {
        max-height: min(70vh, 34rem);
        overflow: auto;
    }
</style>
@endpush

@include('partials.admin.shell-end')
