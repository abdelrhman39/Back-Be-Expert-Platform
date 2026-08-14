@php
    use App\Support\UserOptions;

    $statusClass = match ($user->status) {
        'active' => 'admin-badge--success',
        'suspended' => 'admin-badge--danger',
        default => 'admin-badge--warn',
    };

    $roleClass = match ($user->role) {
        'admin' => 'admin-badge--success',
        'instructor' => 'admin-badge--warn',
        default => '',
    };
@endphp

<div class="admin-batch-view-head">
    <div class="admin-batch-view-head__main">
        <div class="admin-batch-view-head__title-row">
            <h1 class="admin-batch-view-title">{{ $user->displayName() }}</h1>
            <span @class(['admin-badge', $statusClass])>{{ UserOptions::statusLabel($user->status) }}</span>
            <span @class(['admin-badge', $roleClass])>{{ UserOptions::roleLabel($user->role) }}</span>
        </div>

        <p class="admin-batch-view-meta">
            <code class="admin-code">#{{ $user->id }}</code>
            <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
            <span dir="ltr">{{ $user->email }}</span>
            @if ($user->phone)
                <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
                <span dir="ltr">{{ $user->phone }}</span>
            @endif
        </p>
        @if ($user->accessRoles->isNotEmpty())
            <p class="admin-batch-view-meta">
                <strong>أدوار الوصول:</strong>
                {{ $user->accessRoles->pluck('name_ar')->join('، ') }}
            </p>
        @endif

        <ul class="admin-batch-view-facts">
            <li>
                <span class="admin-batch-view-facts__label">الهوية</span>
                <span class="admin-batch-view-facts__value" dir="ltr">{{ $user->national_id ?? '—' }}</span>
            </li>
            <li>
                <span class="admin-batch-view-facts__label">اللغة</span>
                <span class="admin-tag">{{ UserOptions::localeLabel($user->locale) }}</span>
            </li>
            <li>
                <span class="admin-batch-view-facts__label">آخر دخول</span>
                <span class="admin-batch-view-facts__value">{{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}</span>
            </li>
            @if ($user->isLocked())
                <li>
                    <span class="admin-batch-view-facts__label">القفل</span>
                    <span class="admin-badge admin-badge--danger">حتى {{ $user->locked_until->format('Y-m-d H:i') }}</span>
                </li>
            @endif
        </ul>
    </div>

    <div class="admin-batch-view-capacity admin-program-view-summary" aria-label="ملخص الحساب">
        <div class="admin-batch-view-capacity__head">
            <span class="admin-batch-view-capacity__title">الملخص</span>
        </div>
        <ul class="admin-program-view-summary__list">
            <li><span>الطلبات</span><strong>{{ $stats['orders'] }}</strong></li>
            <li><span>محاولات فاشلة</span><strong dir="ltr">{{ $user->failed_login_attempts }}</strong></li>
            <li><span>مسجل منذ</span><strong>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</strong></li>
        </ul>
        <div class="admin-filter-actions" style="margin-top:0.75rem;">
            <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-primary admin-btn-primary--sm" style="width:100%;justify-content:center;">تعديل</a>
            @if (auth()->user()?->canAdmin('users.permissions'))
                <a href="{{ route('admin.users.access', $user) }}" class="admin-btn-secondary admin-btn-secondary--sm" style="width:100%;justify-content:center;">الأدوار والصلاحيات</a>
            @endif
        </div>
    </div>
</div>
