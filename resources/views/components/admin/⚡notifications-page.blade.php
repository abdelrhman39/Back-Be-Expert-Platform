<?php

use App\Services\NotificationService;
use App\Support\NotificationTypes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'الإشعارات',
    'adminPageDesc' => 'صندوق الوارد وإرسال إعلانات للمستخدمين حسب التحديثات',
    'adminLayout' => 'app',
])]
#[Title('الإشعارات | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public string $tab = 'inbox';

    public string $audience = 'students';

    public string $announceTitle = '';

    public string $announceBody = '';

    public string $announceUrl = '';

    public bool $sendMail = false;

    public ?string $flashMessage = null;

    public string $flashType = 'success';

    public int $flashKey = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('notifications.manage'), 403);

        $requested = request()->query('tab');
        if (in_array($requested, ['inbox', 'send'], true)) {
            $this->tab = $requested;
        }
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['inbox', 'send'], true)) {
            $this->tab = $tab;
            $this->resetPage();
        }
    }

    public function markRead(string $notificationId): void
    {
        $user = auth()->user();
        if ($user) {
            app(NotificationService::class)->markRead($user, $notificationId);
        }
    }

    public function markAllRead(): void
    {
        $user = auth()->user();
        if ($user) {
            app(NotificationService::class)->markAllRead($user);
        }
    }

    public function sendAnnouncement(NotificationService $notifications): void
    {
        abort_unless(auth()->user()?->canAdmin('notifications.manage'), 403);

        $this->validate([
            'audience' => ['required', 'in:all,students,instructors,admins,staff'],
            'announceTitle' => ['required', 'string', 'max:160'],
            'announceBody' => ['required', 'string', 'max:2000'],
            'announceUrl' => ['nullable', 'string', 'max:500'],
        ], [], [
            'audience' => 'الجمهور',
            'announceTitle' => 'عنوان الإشعار',
            'announceBody' => 'نص الإشعار',
            'announceUrl' => 'رابط الإجراء',
        ]);

        $channels = $this->sendMail ? ['database', 'mail'] : ['database'];
        $url = filled($this->announceUrl) ? $this->announceUrl : null;

        $count = $notifications->notifyAudience(
            audience: $this->audience,
            type: NotificationTypes::SYSTEM_ANNOUNCEMENT,
            title: $this->announceTitle,
            body: $this->announceBody,
            actionUrl: $url,
            icon: 'fa-bullhorn',
            channels: $channels,
        );

        $this->announceTitle = '';
        $this->announceBody = '';
        $this->announceUrl = '';
        $this->sendMail = false;
        $this->tab = 'inbox';
        $this->flashMessage = "تم إرسال الإشعار إلى {$count} مستخدماً.";
        $this->flashType = 'success';
        $this->flashKey++;
        $this->dispatch('notifications-updated');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.notifications'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الإشعارات'],
    ],
])

@php
    $user = auth()->user();
    $notifications = $user?->notifications()->paginate(20) ?? collect();
    $audienceLabels = NotificationTypes::audienceLabels();
    $previewCount = app(\App\Services\NotificationService::class)->usersForAudience($audience)->count();
@endphp

<div class="admin-notif-page">
    @include('partials.admin.toast', [
        'message' => $flashMessage,
        'type' => $flashType,
        'key' => $flashKey,
        'dismissMethod' => 'dismissFlash',
    ])

    <section class="admin-notif-hero">
        <div>
            <span class="admin-notif-hero__eyebrow">مركز الإشعارات</span>
            <h1>صندوق الوارد والإعلانات</h1>
            <p>راجع إشعاراتك الإدارية، وأرسل تحديثات لجميع الطلاب أو المدربين أو الإداريين.</p>
        </div>
        <div class="admin-notif-hero__actions">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm {{ $tab === 'inbox' ? 'is-active' : '' }}" wire:click="setTab('inbox')">
                <i class="fa-solid fa-inbox"></i> صندوق الوارد
            </button>
            <button type="button" class="admin-btn-primary admin-btn-primary--sm {{ $tab === 'send' ? 'is-active' : '' }}" wire:click="setTab('send')">
                <i class="fa-solid fa-paper-plane"></i> إرسال إعلان
            </button>
        </div>
    </section>

    @if ($tab === 'send')
        <section class="admin-notif-panel">
            <div class="admin-notif-panel__head">
                <h2><i class="fa-solid fa-bullhorn"></i> إرسال إشعار جماعي</h2>
                <p>يظهر الإشعار فوراً في جرس الإشعارات أعلى لوحة المستلمين.</p>
            </div>

            <div class="admin-notif-audience">
                @foreach ($audienceLabels as $key => $label)
                    <label class="admin-notif-audience__card {{ $audience === $key ? 'is-on' : '' }}">
                        <input type="radio" wire:model.live="audience" value="{{ $key }}">
                        <strong>{{ $label }}</strong>
                    </label>
                @endforeach
            </div>

            <div class="admin-notif-preview">
                <i class="fa-solid fa-users"></i>
                سيتم الإرسال إلى حوالي <strong>{{ number_format($previewCount) }}</strong> مستخدماً نشطاً
                ({{ $audienceLabels[$audience] ?? $audience }}).
            </div>

            <div class="admin-field">
                <label for="announceTitle">عنوان الإشعار *</label>
                <input id="announceTitle" type="text" class="admin-control" wire:model="announceTitle" maxlength="160" placeholder="مثال: تحديث مهم على جدول المحاضرات">
                @error('announceTitle')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>

            <div class="admin-field">
                <label for="announceBody">نص الإشعار *</label>
                <textarea id="announceBody" class="admin-control" rows="5" wire:model="announceBody" maxlength="2000" placeholder="اكتب تفاصيل التحديث أو الإعلان..."></textarea>
                @error('announceBody')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>

            <div class="admin-field">
                <label for="announceUrl">رابط الإجراء (اختياري)</label>
                <input id="announceUrl" type="url" class="admin-control" wire:model="announceUrl" dir="ltr" placeholder="https://...">
                @error('announceUrl')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>

            <label class="admin-notif-mail">
                <input type="checkbox" wire:model="sendMail">
                <span>إرسال نسخة بالبريد الإلكتروني أيضاً</span>
            </label>

            <div class="admin-notif-actions">
                <button type="button" class="admin-btn-primary" wire:click="sendAnnouncement" wire:loading.attr="disabled" wire:target="sendAnnouncement">
                    <span wire:loading.remove wire:target="sendAnnouncement"><i class="fa-solid fa-paper-plane"></i> إرسال الآن</span>
                    <span wire:loading.inline-flex wire:target="sendAnnouncement"><i class="fa-solid fa-spinner fa-spin"></i> جارٍ الإرسال...</span>
                </button>
                <a href="{{ route('admin.notification-rules') }}" class="admin-btn-secondary admin-btn-secondary--sm">قواعد الإشعارات التلقائية</a>
            </div>
        </section>
    @else
        <section class="admin-notif-panel">
            <div class="admin-notif-panel__head admin-notif-panel__head--row">
                <div>
                    <h2><i class="fa-solid fa-inbox"></i> صندوق الوارد</h2>
                    <p>الإشعارات الواردة لحسابك الإداري (طلبات جديدة، تحديثات النظام...).</p>
                </div>
                @if ($user && $user->unreadNotifications()->exists())
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="markAllRead">تعيين الكل مقروءاً</button>
                @endif
            </div>

            @if ($notifications->isEmpty())
                <div class="admin-notif-empty">
                    <i class="fa-regular fa-bell-slash"></i>
                    <p>لا توجد إشعارات في صندوقك حالياً.</p>
                </div>
            @else
                <div class="admin-notif-list">
                    @foreach ($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = $notification->read_at === null;
                        @endphp
                        <article class="admin-notif-item {{ $isUnread ? 'is-unread' : '' }}" wire:key="admin-n-{{ $notification->id }}">
                            <span class="admin-notif-item__icon"><i class="fa-solid {{ $data['icon'] ?? 'fa-bell' }}"></i></span>
                            <div class="admin-notif-item__body">
                                <h3>{{ $data['title'] ?? 'إشعار' }}</h3>
                                <p>{{ $data['body'] ?? '' }}</p>
                                <div class="admin-notif-item__meta">
                                    <time>{{ $notification->created_at?->diffForHumans() }}</time>
                                    @if (! empty($data['action_url']))
                                        <a href="{{ $data['action_url'] }}">فتح</a>
                                    @endif
                                    @if ($isUnread)
                                        <button type="button" wire:click="markRead('{{ $notification->id }}')">تعيين مقروءاً</button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if (method_exists($notifications, 'links'))
                    <div style="margin-top:1rem;">{{ $notifications->links() }}</div>
                @endif
            @endif
        </section>
    @endif
</div>

@include('partials.admin.shell-end')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-notifications.css') }}?v=1">
@endpush
