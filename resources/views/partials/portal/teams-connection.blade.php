@php
    use App\Support\TeamsSettings;

    $teamsEnabled = TeamsSettings::isEnabled() && TeamsSettings::isConfigured();
    $connection = auth()->user()?->microsoftTeamsConnection;
    $locale = app()->getLocale();
@endphp

@if ($teamsEnabled)
    <div class="portal-widget portal-widget--teams {{ $connection ? 'is-connected' : 'is-disconnected' }}">
        <div class="portal-widget__head">
            <span class="portal-widget__head-icon portal-widget__head-icon--teams">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor">
                    <path d="M20.625 8.25H17.25V5.625A2.625 2.625 0 0 0 14.625 3h-5.25A2.625 2.625 0 0 0 6.75 5.625V8.25H3.375A1.125 1.125 0 0 0 2.25 9.375v9A2.625 2.625 0 0 0 4.875 21h14.25a2.625 2.625 0 0 0 2.625-2.625v-9A1.125 1.125 0 0 0 20.625 8.25ZM8.25 5.625a1.125 1.125 0 0 1 1.125-1.125h5.25A1.125 1.125 0 0 1 15.75 5.625V8.25H8.25V5.625Zm10.875 12.75a1.125 1.125 0 0 1-1.125 1.125H4.875a1.125 1.125 0 0 1-1.125-1.125v-7.5h15.375v7.5Z"/>
                </svg>
            </span>
            <h3 class="portal-widget__title">Microsoft Teams</h3>
        </div>

        @if ($connection)
            <div class="portal-teams-status portal-teams-status--connected">
                <span class="portal-teams-status__dot" aria-hidden="true"></span>
                <div class="portal-teams-status__body">
                    <strong>مرتبط</strong>
                    <span class="portal-teams-status__email" dir="ltr">{{ $connection->microsoft_email }}</span>
                    @if ($connection->display_name)
                        <span class="portal-teams-status__name">{{ $connection->display_name }}</span>
                    @endif
                    @if ($connection->connected_at)
                        <span class="portal-teams-status__meta">منذ {{ $connection->connected_at->translatedFormat('d M Y') }}</span>
                    @endif
                </div>
            </div>
            <p class="portal-teams-note">يُستخدم حسابك لتحضيرك تلقائياً عند حضور المحاضرات على Teams.</p>
            @if (isset($showDisconnect) && $showDisconnect)
                <button type="button"
                    class="btn btn-outline-danger btn-sm w-100 mt-2"
                    wire:click="disconnectTeams"
                    wire:confirm="هل تريد إلغاء ربط حساب Microsoft Teams؟"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="disconnectTeams"><i class="fa-solid fa-link-slash"></i> إلغاء الربط</span>
                    <span wire:loading wire:target="disconnectTeams">جاري الإلغاء…</span>
                </button>
            @else
                <a href="{{ route('settings', ['locale' => $locale]) }}" class="portal-widget__link">إدارة الربط ←</a>
            @endif
        @else
            <div class="portal-teams-status portal-teams-status--disconnected">
                <span class="portal-teams-status__dot" aria-hidden="true"></span>
                <div class="portal-teams-status__body">
                    <strong>غير مرتبط</strong>
                    <span class="portal-teams-status__meta">اربط حساب Teams لتفعيل التحضير التلقائي</span>
                </div>
            </div>
            <p class="portal-teams-note">استخدم نفس البريد المسجّل في Microsoft 365 / Teams الخاص بمؤسستك.</p>
            <a href="{{ route('integrations.microsoft.connect') }}" class="btn btn-primary btn-sm w-100 portal-teams-connect-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="currentColor"><path d="M20.625 8.25H17.25V5.625A2.625 2.625 0 0 0 14.625 3h-5.25A2.625 2.625 0 0 0 6.75 5.625V8.25H3.375A1.125 1.125 0 0 0 2.25 9.375v9A2.625 2.625 0 0 0 4.875 21h14.25a2.625 2.625 0 0 0 2.625-2.625v-9A1.125 1.125 0 0 0 20.625 8.25Z"/></svg>
                ربط حساب Microsoft Teams
            </a>
        @endif
    </div>
@endif
