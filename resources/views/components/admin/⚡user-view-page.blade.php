<?php

use App\Models\User;
use App\Support\UserOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('ملف المستخدم | لوحة التحكم')]
class extends Component
{
    public User $user;

    #[Url(as: 'tab')]
    public string $activeTab = 'profile';

    public function mount(User $user): void
    {
        abort_unless(auth()->user()?->canAdmin('users.view'), 403);

        $this->user = $user->load(['academicStudent.batch.program', 'orders', 'accessRoles']);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['profile', 'security', 'orders', 'academic'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function toggleStatus(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        if ($this->user->id === auth()->id()) {
            return;
        }

        $this->user->update([
            'status' => $this->user->status === 'active' ? 'suspended' : 'active',
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        $this->user->refresh();
        session()->flash('admin_message', 'تم تحديث حالة المستخدم.');
    }

    public function unlockAccount(): void
    {
        abort_unless(auth()->user()?->canAdmin('users.manage'), 403);

        $this->user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'status' => 'active',
        ]);

        $this->user->refresh();
        session()->flash('admin_message', 'تم فتح الحساب.');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'orders' => $this->user->orders->count(),
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.users'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.users'), 'label' => 'المستخدمون'],
        ['label' => $user->displayName()],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-view-card">
    <header class="admin-batch-view-hero">
        @include('partials.admin.user-view-header', ['user' => $user, 'stats' => $this->stats])
    </header>

    <div class="admin-view-tabs" role="tablist" aria-label="أقسام المستخدم">
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'profile']) wire:click="setTab('profile')">الملف الشخصي</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'security']) wire:click="setTab('security')">الأمان</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'orders']) wire:click="setTab('orders')">الطلبات ({{ $user->orders->count() }})</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'academic']) wire:click="setTab('academic')">السجل الأكاديمي</button>
    </div>

    <div class="admin-view-panel is-active" role="tabpanel">
        @if ($activeTab === 'profile')
            <div class="admin-info-grid admin-info-grid--3">
                @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'الاسم (عربي)', 'value' => $user->name_ar ?: '—'])
                @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'الاسم (Latin)', 'value' => $user->name])
                @include('partials.admin.info-card', ['icon' => 'mail', 'label' => 'البريد', 'value' => '<a href="mailto:'.e($user->email).'" class="admin-link" dir="ltr">'.e($user->email).'</a>'])
                @include('partials.admin.info-card', ['icon' => 'phone', 'label' => 'الجوال', 'value' => $user->phone ? '<span dir="ltr">'.e($user->phone).'</span>' : '—'])
                @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'رقم الهوية', 'value' => $user->national_id ?: '—'])
                @include('partials.admin.info-card', ['icon' => 'flag', 'label' => 'الدور', 'value' => UserOptions::roleLabel($user->role)])
                @include('partials.admin.info-card', ['icon' => 'flag', 'label' => 'الحالة', 'value' => UserOptions::statusLabel($user->status)])
                @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'اللغة', 'value' => UserOptions::localeLabel($user->locale)])
                @include('partials.admin.info-card', ['icon' => 'calendar', 'label' => 'تاريخ التسجيل', 'value' => $user->created_at?->format('Y-m-d H:i') ?? '—'])
            </div>
        @elseif ($activeTab === 'security')
            <div class="admin-info-grid admin-info-grid--2">
                @include('partials.admin.info-card', ['icon' => 'shield', 'label' => 'حالة الحساب', 'value' => UserOptions::statusLabel($user->status)])
                @include('partials.admin.info-card', ['icon' => 'shield', 'label' => 'قفل مؤقت', 'value' => $user->isLocked() ? 'مقفل حتى '.$user->locked_until->format('Y-m-d H:i') : 'غير مقفل'])
                @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'محاولات دخول فاشلة', 'value' => (string) $user->failed_login_attempts])
                @include('partials.admin.info-card', ['icon' => 'clock', 'label' => 'آخر دخول', 'value' => $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i').' ('.$user->last_login_at->diffForHumans().')' : '—'])
                @include('partials.admin.info-card', ['icon' => 'clock', 'label' => 'طريقة آخر دخول', 'value' => $user->last_login_method ?? '—'])
                @include('partials.admin.info-card', ['icon' => 'mail', 'label' => 'تأكيد البريد', 'value' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d') : 'غير مؤكد'])
            </div>
            @if ($user->id !== auth()->id())
                <div class="admin-filter-actions" style="margin-top:1rem;">
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleStatus">
                        {{ $user->status === 'active' ? 'إيقاف الحساب' : 'تفعيل الحساب' }}
                    </button>
                    @if ($user->isLocked() || $user->failed_login_attempts > 0)
                        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="unlockAccount">فتح القفل</button>
                    @endif
                </div>
            @endif
        @elseif ($activeTab === 'orders')
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>المرجع</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>الدفع</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($user->orders as $order)
                            <tr>
                                <td><code class="admin-code">{{ $order->reference }}</code></td>
                                <td dir="ltr">{{ number_format((float) $order->total, 2) }} ر.س</td>
                                <td>{{ $order->status }}</td>
                                <td>{{ $order->payment_method ?? '—' }}</td>
                                <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;padding:1.5rem">لا توجد طلبات لهذا المستخدم.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            @if ($user->academicStudent)
                <div class="admin-info-grid admin-info-grid--2">
                    @include('partials.admin.info-card', [
                        'icon' => 'hash',
                        'label' => 'الرقم الأكاديمي',
                        'value' => '<code class="admin-code">'.e($user->academicStudent->academic_id).'</code>',
                    ])
                    @include('partials.admin.info-card', [
                        'icon' => 'book',
                        'label' => 'الدفعة',
                        'value' => $user->academicStudent->batch
                            ? '<a href="'.route('admin.batches.show', $user->academicStudent->batch).'" class="admin-link">'.e($user->academicStudent->batch->name).'</a>'
                            : '—',
                    ])
                    @include('partials.admin.info-card', [
                        'icon' => 'flag',
                        'label' => 'حالة الدراسة',
                        'value' => $user->academicStudent->study_status ?: \App\Support\AcademicStudentOptions::academicStatusLabel($user->academicStudent->academic_status),
                    ])
                </div>
                <div class="admin-filter-actions" style="margin-top:1rem;">
                    <a href="{{ route('admin.students.show', $user->academicStudent) }}" class="admin-btn-primary admin-btn-primary--sm">فتح ملف الطالب</a>
                </div>
            @else
                <p class="admin-detail-empty">لا يوجد سجل طالب مرتبط بهذا الحساب.</p>
            @endif
        @endif
    </div>
</section>

@include('partials.admin.view-hero-styles')

@include('partials.admin.shell-end')
