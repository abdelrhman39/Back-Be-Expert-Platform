<?php

use App\Models\User;
use App\Services\NotificationService;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    /** admin | instructor | portal */
    public string $panel = 'portal';

    public bool $open = false;

    public function mount(string $panel = 'portal'): void
    {
        $this->panel = in_array($panel, ['admin', 'instructor', 'portal'], true) ? $panel : 'portal';
    }

    protected function currentUser(): ?User
    {
        return match ($this->panel) {
            'portal' => portal_user(),
            default => auth()->user(),
        };
    }

    public function inboxUrl(): string
    {
        $locale = app()->getLocale();

        return match ($this->panel) {
            'admin' => route('admin.notifications'),
            'instructor' => route('instructor.notifications', ['locale' => $locale]),
            default => route('notifications', ['locale' => $locale]),
        };
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markRead(string $notificationId): void
    {
        $user = $this->currentUser();

        if ($user) {
            app(NotificationService::class)->markRead($user, $notificationId);
        }
    }

    public function markAllRead(): void
    {
        $user = $this->currentUser();

        if ($user) {
            app(NotificationService::class)->markAllRead($user);
        }

        $this->open = true;
    }

    #[On('notifications-updated')]
    public function refreshBell(): void
    {
        // Re-render on demand from parent pages.
    }
};
?>

@php
    $user = $this->currentUser();
    $service = app(NotificationService::class);
    $unreadCount = $user ? $service->unreadCount($user) : 0;
    $recent = $user ? $service->recentFor($user, 8) : collect();
    $inboxUrl = $this->inboxUrl();
@endphp

<div
    class="np-bell np-bell--{{ $panel }}"
    wire:poll.60s
    @keydown.escape.window="$wire.close()"
>
    <button
        type="button"
        class="np-bell__trigger"
        wire:click="toggle"
        aria-label="الإشعارات"
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        title="الإشعارات"
    >
        <i class="fa-solid fa-bell" aria-hidden="true"></i>
        @if ($unreadCount > 0)
            <span class="np-bell__badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    @if ($open)
        <div class="np-bell__backdrop" wire:click="close" aria-hidden="true"></div>
        <div class="np-bell__panel" role="dialog" aria-label="آخر الإشعارات">
            <div class="np-bell__head">
                <div>
                    <strong>الإشعارات</strong>
                    @if ($unreadCount > 0)
                        <span class="np-bell__count">{{ $unreadCount }} غير مقروء</span>
                    @endif
                </div>
                <div class="np-bell__head-actions">
                    @if ($unreadCount > 0)
                        <button type="button" wire:click="markAllRead">تعيين الكل مقروءاً</button>
                    @endif
                    <button type="button" class="np-bell__close" wire:click="close" aria-label="إغلاق">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="np-bell__list">
                @forelse ($recent as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = $notification->read_at === null;
                        $url = $data['action_url'] ?? $inboxUrl;
                    @endphp
                    <a
                        href="{{ $url }}"
                        class="np-bell__item {{ $isUnread ? 'is-unread' : '' }}"
                        wire:key="bell-n-{{ $notification->id }}"
                        wire:click="markRead('{{ $notification->id }}')"
                    >
                        <span class="np-bell__icon"><i class="fa-solid {{ $data['icon'] ?? 'fa-bell' }}"></i></span>
                        <span class="np-bell__body">
                            <strong>{{ $data['title'] ?? 'إشعار' }}</strong>
                            <span>{{ \Illuminate\Support\Str::limit($data['body'] ?? '', 90) }}</span>
                            <time>{{ $notification->created_at?->diffForHumans() }}</time>
                        </span>
                    </a>
                @empty
                    <div class="np-bell__empty">
                        <i class="fa-regular fa-bell-slash"></i>
                        <p>لا توجد إشعارات حالياً</p>
                    </div>
                @endforelse
            </div>

            <a href="{{ $inboxUrl }}" class="np-bell__footer" wire:click="close">عرض كل الإشعارات</a>
        </div>
    @endif
</div>
