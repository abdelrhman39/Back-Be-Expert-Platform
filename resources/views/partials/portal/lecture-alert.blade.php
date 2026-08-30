@php
    $lecture = app(\App\Services\UpcomingLectureService::class)->forUser(auth()->user());
@endphp

@if ($lecture)
    <div @class(['portal-lecture-alert', 'portal-lecture-alert--live' => $lecture['state'] === 'live']) role="status" aria-live="polite">
        <div class="portal-lecture-alert__icon" aria-hidden="true">
            @if ($lecture['state'] === 'live')
                <span class="portal-lecture-alert__pulse"></span>
                <i class="fa-solid fa-video"></i>
            @else
                <i class="fa-regular fa-clock"></i>
            @endif
        </div>
        <div class="portal-lecture-alert__body">
            @if ($lecture['state'] === 'live')
                <span class="portal-lecture-alert__badge"><i class="fa-solid fa-circle"></i> مباشر الآن</span>
                <strong class="portal-lecture-alert__title">{{ $lecture['title'] }}</strong>
                <span class="portal-lecture-alert__meta">
                    {{ $lecture['course_name'] }}
                    @if ($lecture['trainer']) · {{ $lecture['trainer'] }} @endif
                    · حتى {{ $lecture['ends_at_formatted'] }}
                </span>
            @else
                <span class="portal-lecture-alert__badge portal-lecture-alert__badge--upcoming">المحاضرة القادمة</span>
                <strong class="portal-lecture-alert__title">{{ $lecture['title'] }}</strong>
                <span class="portal-lecture-alert__meta">
                    {{ $lecture['starts_at_formatted'] }}
                    @if ($lecture['trainer']) · {{ $lecture['trainer'] }} @endif
                    @if ($lecture['countdown_minutes'] > 0 && $lecture['countdown_minutes'] <= 180)
                        · خلال {{ $lecture['countdown_minutes'] }} د
                    @endif
                </span>
            @endif
        </div>
        <div class="portal-lecture-alert__actions">
            @if ($lecture['meeting_url'])
                <a href="{{ $lecture['meeting_url'] }}"
                    @if (! str_contains((string) $lecture['meeting_url'], '/sessions/')) target="_blank" rel="noopener noreferrer" @endif
                    @class(['btn btn-sm', $lecture['state'] === 'live' ? 'btn-light' : 'btn-primary'])>
                    @if ($lecture['state'] === 'live')
                        <i class="fa-solid fa-right-to-bracket"></i> دخول المحاضرة
                    @else
                        <i class="fa-solid fa-link"></i> رابط الدخول
                    @endif
                </a>
            @elseif ($lecture['state'] === 'live')
                <span class="portal-lecture-alert__waiting">المحاضرة جارية — رابط الدخول قريباً</span>
            @endif
        </div>
    </div>
@endif
