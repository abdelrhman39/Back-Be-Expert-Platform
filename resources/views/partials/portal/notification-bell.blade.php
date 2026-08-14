@php
    use App\Services\NotificationService;

    $locale = app()->getLocale();
    $user = auth()->user();
    $notificationService = app(NotificationService::class);
    $unreadCount = $user ? $notificationService->unreadCount($user) : 0;
    $recent = $user ? $notificationService->recentFor($user, 5) : collect();
@endphp

<li class="nav-item portal-notifications-nav">
    <div class="portal-notifications-dropdown">
        <a href="{{ route('notifications', ['locale' => $locale]) }}" class="portal-notifications-bell" title="الإشعارات" aria-label="الإشعارات">
            <i class="fa-solid fa-bell"></i>
            @if ($unreadCount > 0)
                <span class="portal-notifications-bell__badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </a>
        @if ($recent->isNotEmpty())
            <div class="portal-notifications-dropdown__menu">
                <div class="portal-notifications-dropdown__head">آخر الإشعارات</div>
                @foreach ($recent as $notification)
                    @php $data = $notification->data; @endphp
                    <a href="{{ $data['action_url'] ?? route('notifications', ['locale' => $locale]) }}" @class(['portal-notifications-dropdown__item', 'portal-notifications-dropdown__item--unread' => ! $notification->read_at])>
                        <strong>{{ $data['title'] ?? 'إشعار' }}</strong>
                        <span>{{ \Illuminate\Support\Str::limit($data['body'] ?? '', 60) }}</span>
                    </a>
                @endforeach
                <a href="{{ route('notifications', ['locale' => $locale]) }}" class="portal-notifications-dropdown__all">عرض الكل</a>
            </div>
        @endif
    </div>
</li>
