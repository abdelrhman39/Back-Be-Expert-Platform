<?php

use App\Services\NotificationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('الإشعارات | مركز التعلم المستمر')]
class extends Component
{
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
};
?>

@php
    $locale = app()->getLocale();
    $user = auth()->user();
    $notifications = $user?->notifications()->paginate(20) ?? collect();
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'notifications', 'portalTitle' => 'الإشعارات'])

<div class="portal-dashboard portal-notifications-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">الإشعارات</h1>
            <p class="portal-orders-intro__desc">تذكيرات المحاضرات والواجبات والتسجيلات.</p>
        </div>
        @if ($user && $user->unreadNotifications()->exists())
            <button type="button" class="portal-btn-secondary" wire:click="markAllRead">تعليم الكل كمقروء</button>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="portal-panel">
            <div class="portal-empty">
                <div class="portal-empty__icon"><i class="fa-solid fa-bell"></i></div>
                <p>لا توجد إشعارات حالياً</p>
            </div>
        </div>
    @else
        <div class="portal-notifications-list">
            @foreach ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                @endphp
                <article @class(['portal-notification-item', 'portal-notification-item--unread' => $isUnread]) wire:key="n-{{ $notification->id }}">
                    <div class="portal-notification-item__icon">
                        <i class="fa-solid {{ $data['icon'] ?? 'fa-bell' }}"></i>
                    </div>
                    <div class="portal-notification-item__body">
                        <h3>{{ $data['title'] ?? 'إشعار' }}</h3>
                        <p>{{ $data['body'] ?? '' }}</p>
                        <div class="portal-notification-item__meta">
                            <time>{{ $notification->created_at->diffForHumans() }}</time>
                            @if (! empty($data['action_url']))
                                <a href="{{ $data['action_url'] }}" class="portal-notification-item__link">عرض</a>
                            @endif
                            @if ($isUnread)
                                <button type="button" class="portal-notification-item__read" wire:click="markRead('{{ $notification->id }}')">تعليم كمقروء</button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if (method_exists($notifications, 'links'))
            <div class="portal-pagination">{{ $notifications->links() }}</div>
        @endif
    @endif
</div>

@include('partials.portal.shell-end')
