@props(['user', 'showDelete' => false])

@php
    $canManage = auth()->user()?->canAdmin('users.manage');
    $canManagePermissions = auth()->user()?->canAdmin('users.permissions');
@endphp

<div class="admin-actions-menu">
    <button
        type="button"
        class="admin-kebab"
        aria-expanded="false"
        aria-haspopup="true"
        aria-label="إجراءات {{ $user->displayName() }}"
    >
        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
            <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
        </svg>
    </button>
    <ul class="admin-actions-dropdown" hidden role="menu">
        <li role="none">
            <a href="{{ route('admin.users.show', $user) }}" class="admin-actions-item" role="menuitem">عرض الملف</a>
        </li>
        @if ($canManage)
            <li role="none">
                <a href="{{ route('admin.users.edit', $user) }}" class="admin-actions-item" role="menuitem">تعديل</a>
            </li>
            @if ($user->id !== auth()->id())
                <li role="none">
                    <button
                        type="button"
                        class="admin-actions-item admin-actions-item--btn"
                        role="menuitem"
                        wire:click="toggleStatus({{ $user->id }})"
                    >
                        {{ $user->status === 'active' ? 'إيقاف الحساب' : 'تفعيل الحساب' }}
                    </button>
                </li>
                @if ($user->isLocked() || $user->failed_login_attempts > 0)
                    <li role="none">
                        <button
                            type="button"
                            class="admin-actions-item admin-actions-item--btn"
                            role="menuitem"
                            wire:click="unlockUser({{ $user->id }})"
                        >
                            فتح القفل
                        </button>
                    </li>
                @endif
                @if ($showDelete)
                    <li role="none">
                        <button
                            type="button"
                            class="admin-actions-item admin-actions-item--btn admin-actions-item--danger"
                            role="menuitem"
                            wire:click="openDeleteUser({{ $user->id }})"
                        >
                            حذف المستخدم
                        </button>
                    </li>
                @endif
            @endif
        @endif
        @if ($canManagePermissions)
            <li role="none">
                <a href="{{ route('admin.users.access', $user) }}" class="admin-actions-item" role="menuitem">الأدوار والصلاحيات</a>
            </li>
        @endif
    </ul>
</div>
