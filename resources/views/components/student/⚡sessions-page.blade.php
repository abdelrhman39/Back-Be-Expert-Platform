<?php

use App\Services\AcademicSessionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('حصصي | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function sessions()
    {
        $student = auth()->user()?->academicStudent;
        $service = app(AcademicSessionService::class);

        return $service->forStudent($student)
            ->map(function ($session) use ($service) {
                $timing = $service->resolveTiming($session);
                $session->computed_state = $timing['state'];
                $session->computed_starts_at = $timing['starts_at'];
                $session->computed_ends_at = $timing['ends_at'];
                $session->join_url = $service->joinUrl($session);

                return $session;
            });
    }

    #[Computed]
    public function student()
    {
        return auth()->user()?->academicStudent?->load('section.course');
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'sessions', 'portalTitle' => 'حصصي'])

<div class="portal-dashboard portal-sessions-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">حصصي الدراسية</h1>
            <p class="portal-orders-intro__desc">
                @if ($this->student?->section)
                    شعبة {{ $this->student->section->name }} — {{ $this->student->section->course?->name_ar ?? 'المقرر' }}
                @else
                    المحاضرات والمواد المرتبطة بشعبتك الأكاديمية.
                @endif
            </p>
        </div>
    </div>

    @if (! $this->student?->section_id)
        <div class="portal-panel">
            <div class="portal-empty portal-empty--lg">
                <div class="portal-empty__icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                <p>لا توجد شعبة أكاديمية مرتبطة بحسابك</p>
                <span class="portal-empty__hint">يتم عرض الحصص بعد التسجيل في برنامج أكاديمي وربطك بشعبة.</span>
            </div>
        </div>
    @elseif ($this->sessions->isEmpty())
        <div class="portal-panel">
            <div class="portal-empty">
                <div class="portal-empty__icon"><i class="fa-solid fa-calendar-xmark"></i></div>
                <p>لا توجد حصص منشورة بعد</p>
                <span class="portal-empty__hint">ستظهر الحصص هنا عند جدولتها من الإدارة.</span>
            </div>
        </div>
    @else
        <div class="portal-session-list">
            @foreach ($this->sessions as $session)
                @php
                    $state = $session->computed_state;
                    $stateClass = match ($state) {
                        'live' => 'portal-session-card--live',
                        'upcoming' => 'portal-session-card--upcoming',
                        default => 'portal-session-card--past',
                    };
                    $stateLabel = match ($state) {
                        'live' => 'جارية الآن',
                        'upcoming' => 'قادمة',
                        'completed' => 'منتهية',
                        default => 'مجدولة',
                    };
                @endphp
                <article @class(['portal-session-card', $stateClass]) wire:key="session-{{ $session->id }}">
                    <div class="portal-session-card__head">
                        <span @class(['portal-session-card__badge', 'is-live' => $state === 'live'])>{{ $stateLabel }}</span>
                        <time class="portal-session-card__date" dir="ltr">
                            {{ $session->session_date->translatedFormat('d M Y') }}
                            @if ($session->computed_starts_at)
                                · {{ $session->computed_starts_at->format('H:i') }}
                            @endif
                        </time>
                    </div>
                    <h2 class="portal-session-card__title">{{ $session->displayTitle() }}</h2>
                    @if ($session->description)
                        <p class="portal-session-card__desc">{{ $session->description }}</p>
                    @endif
                    <div class="portal-session-card__meta">
                        @if ($session->publishedMaterials->isNotEmpty())
                            <span><i class="fa-solid fa-paperclip"></i> {{ $session->publishedMaterials->count() }} مرفق</span>
                        @endif
                    </div>
                    <div class="portal-session-card__actions">
                        @if ($state === 'live' && $session->join_url)
                            <a href="{{ $session->join_url }}" @if (! $session->zoxAgentMeeting) target="_blank" rel="noopener" @endif class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-video"></i> انضم الآن
                            </a>
                        @elseif ($state === 'upcoming' && $session->join_url)
                            <a href="{{ $session->join_url }}" @if (! $session->zoxAgentMeeting) target="_blank" rel="noopener" @endif class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-link"></i> {{ $session->zoxAgentMeeting ? 'رابط القاعة' : 'رابط Teams' }}
                            </a>
                        @endif
                        <a href="{{ route('sessions.show', ['locale' => $locale, 'session' => $session->id]) }}" class="btn btn-primary btn-sm">
                            تفاصيل الحصة
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    .portal-session-list { display: flex; flex-direction: column; gap: 0.85rem; }
    .portal-session-card {
        background: #fff;
        border: 1px solid var(--sa-border, rgba(22, 93, 49, 0.1));
        border-radius: var(--portal-radius, 12px);
        padding: 1rem 1.15rem;
        box-shadow: var(--portal-shadow);
    }
    .portal-session-card--live { border-color: rgba(220, 38, 38, 0.35); background: linear-gradient(135deg, #fff 0%, #fff5f5 100%); }
    .portal-session-card--upcoming { border-color: rgba(22, 93, 49, 0.2); }
    .portal-session-card__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.45rem; }
    .portal-session-card__badge {
        font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 999px;
        background: #f1f5f9; color: #64748b;
    }
    .portal-session-card__badge.is-live { background: #fef2f2; color: #b91c1c; animation: portal-pulse 2s infinite; }
    .portal-session-card__date { font-size: 0.78rem; color: var(--sa-muted); font-weight: 600; }
    .portal-session-card__title { margin: 0 0 0.35rem; font-size: 1.05rem; font-weight: 800; }
    .portal-session-card__desc { margin: 0 0 0.5rem; font-size: 0.82rem; color: var(--sa-muted); }
    .portal-session-card__meta { display: flex; gap: 0.75rem; font-size: 0.78rem; color: var(--sa-muted); margin-bottom: 0.75rem; }
    .portal-session-card__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    @keyframes portal-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.75; } }
</style>
@endpush
