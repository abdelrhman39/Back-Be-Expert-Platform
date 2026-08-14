<?php

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use App\Services\AssignmentService;
use App\Services\SessionRecordingService;
use App\Support\AttendanceOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تفاصيل الحصة | منصة مركز التعلم المستمر')]
class extends Component
{
    public AttendanceSession $session;

    public function mount(AttendanceSession $session, AcademicSessionService $sessions): void
    {
        abort_unless($sessions->studentCanAccess(auth()->user(), $session), 404);

        $this->session = $session->load(['section.course', 'section.schedule', 'publishedMaterials', 'recording', 'zoomMeeting']);
    }

    #[Computed]
    public function timing(): array
    {
        return app(AcademicSessionService::class)->resolveTiming($this->session);
    }

    #[Computed]
    public function joinUrl(): ?string
    {
        return app(AcademicSessionService::class)->joinUrl($this->session);
    }

    #[Computed]
    public function usesZoom(): bool
    {
        return (bool) $this->session->zoomMeeting;
    }

    #[Computed]
    public function hasJoinRoute(): bool
    {
        return \Illuminate\Support\Facades\Route::has('sessions.join');
    }

    #[Computed]
    public function hasRecordingRoute(): bool
    {
        return \Illuminate\Support\Facades\Route::has('sessions.recording');
    }

    #[Computed]
    public function attendanceRecord(): ?AttendanceRecord
    {
        $student = auth()->user()?->academicStudent;

        if (! $student) {
            return null;
        }

        return AttendanceRecord::query()
            ->where('attendance_session_id', $this->session->id)
            ->where('student_id', $student->id)
            ->first();
    }

    #[Computed]
    public function assignments()
    {
        $student = auth()->user()?->academicStudent;

        return app(AssignmentService::class)
            ->forSession($this->session)
            ->map(function ($assignment) use ($student) {
                if ($student) {
                    $assignment->my_submission = app(AssignmentService::class)->latestSubmission($assignment, $student);
                }

                return $assignment;
            });
    }

    #[Computed]
    public function publishedRecording(): ?\App\Models\SessionRecording
    {
        $recording = app(SessionRecordingService::class)->publishedForSession($this->session);

        if (! $recording || ! app(SessionRecordingService::class)->studentCanView(auth()->user(), $recording)) {
            return null;
        }

        return $recording;
    }

    public function openRecording(): void
    {
        $recording = $this->publishedRecording;

        if ($recording) {
            app(SessionRecordingService::class)->recordView($recording);
        }
    }
};
?>

@php
    $locale = app()->getLocale();
    $state = $this->timing['state'];
    $startsAt = $this->timing['starts_at'];
    $endsAt = $this->timing['ends_at'];
    $durationMinutes = $startsAt && $endsAt ? $startsAt->diffInMinutes($endsAt) : null;
    $trainerName = $this->session->section?->schedule?->trainer_name;
    $stateMeta = match ($state) {
        'live' => ['label' => 'جارية الآن', 'icon' => 'fa-tower-broadcast', 'class' => 'is-live'],
        'upcoming' => ['label' => 'قادمة', 'icon' => 'fa-hourglass-half', 'class' => 'is-upcoming'],
        'completed' => ['label' => 'منتهية', 'icon' => 'fa-circle-check', 'class' => 'is-completed'],
        'cancelled' => ['label' => 'ملغاة', 'icon' => 'fa-ban', 'class' => 'is-cancelled'],
        default => ['label' => 'مجدولة', 'icon' => 'fa-calendar-check', 'class' => 'is-scheduled'],
    };
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'sessions', 'portalTitle' => $this->session->displayTitle()])

<div class="portal-dashboard portal-session-detail">
    <section class="session-hero {{ $state === 'live' ? 'session-hero--live' : '' }}">
        <div class="session-hero__top">
            <a href="{{ route('sessions', ['locale' => $locale]) }}" class="session-hero__back">
                <i class="fa-solid fa-arrow-right"></i> العودة للحصص
            </a>
            <span class="session-hero__state {{ $stateMeta['class'] }}">
                <i class="fa-solid {{ $stateMeta['icon'] }}"></i> {{ $stateMeta['label'] }}
            </span>
        </div>

        <div class="session-hero__main">
            <div class="session-hero__copy">
                @if ($this->session->session_number)
                    <span class="session-hero__eyebrow">الحصة رقم {{ $this->session->session_number }}</span>
                @endif
                <h1>{{ $this->session->displayTitle() }}</h1>
                @if ($this->session->section?->course?->name_ar || $this->session->section?->name)
                    <p>
                        {{ $this->session->section?->course?->name_ar ?? 'المقرر' }}
                        @if ($this->session->section?->name)
                            — {{ $this->session->section->name }}
                        @endif
                    </p>
                @endif
            </div>

            <div class="session-hero__actions">
                @if (
                    $this->usesZoom
                    && $this->hasJoinRoute
                    && in_array($state, ['upcoming', 'live'], true)
                    && ($state === 'live' || ! $startsAt || now()->gte($startsAt->copy()->subMinutes(\App\Support\ZoomSettings::joinWindowMinutes())))
                )
                    <a href="{{ route('sessions.join', ['locale' => $locale, 'session' => $this->session->id]) }}" class="btn {{ $state === 'live' ? 'btn-danger' : 'btn-light' }}">
                        <i class="fa-solid fa-video"></i> {{ $state === 'live' ? 'انضم للمحاضرة الآن' : 'رابط الانضمام' }}
                    </a>
                @elseif ($state === 'live' && $this->joinUrl)
                    <a href="{{ $this->joinUrl }}" target="_blank" rel="noopener" class="btn btn-danger">
                        <i class="fa-solid fa-video"></i> انضم للمحاضرة الآن
                    </a>
                @elseif ($this->joinUrl && in_array($state, ['upcoming', 'live'], true))
                    <a href="{{ $this->joinUrl }}" target="_blank" rel="noopener" class="btn btn-light">
                        <i class="fa-solid fa-link"></i> رابط الانضمام
                    </a>
                @endif
            </div>
        </div>

        <div class="session-hero__facts">
            <span class="session-hero__fact">
                <i class="fa-regular fa-calendar"></i>
                {{ $this->session->session_date->translatedFormat('l d M Y') }}
            </span>
            @if ($startsAt)
                <span class="session-hero__fact" dir="ltr">
                    <i class="fa-regular fa-clock"></i>
                    {{ $startsAt->format('H:i') }}@if($endsAt) – {{ $endsAt->format('H:i') }}@endif
                </span>
            @endif
            @if ($durationMinutes)
                <span class="session-hero__fact">
                    <i class="fa-solid fa-stopwatch"></i>
                    {{ $durationMinutes }} دقيقة
                </span>
            @endif
            @if ($trainerName)
                <span class="session-hero__fact">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    {{ $trainerName }}
                </span>
            @endif
            @if ($state === 'upcoming' && $startsAt)
                <span class="session-hero__fact session-hero__fact--accent">
                    <i class="fa-solid fa-bell"></i>
                    تبدأ {{ $startsAt->diffForHumans() }}
                </span>
            @endif
        </div>
    </section>

    @if ($state === 'live')
        <div class="session-live-strip">
            <span class="session-live-strip__dot"></span>
            <strong>المحاضرة جارية الآن</strong>
            <span>انضم قبل فوات الوقت — يُسجَّل حضورك تلقائياً عند الانضمام.</span>
        </div>
    @endif

    @if ($recording = $this->publishedRecording)
        <section class="portal-panel portal-panel--recording">
            <div class="portal-panel__body portal-panel__body--padded portal-recording-banner">
                <div class="portal-recording-banner__icon"><i class="fa-solid fa-circle-play"></i></div>
                <div class="portal-recording-banner__body">
                    <strong>تسجيل المحاضرة متاح</strong>
                    @if ($recording->formattedDuration())
                        <span class="portal-recording-banner__meta">المدة: {{ $recording->formattedDuration() }}</span>
                    @endif
                    @if ($recording->recorded_at)
                        <span class="portal-recording-banner__meta">{{ $recording->recorded_at->translatedFormat('d M Y — H:i') }}</span>
                    @endif
                    @if ($recording->recording_passcode)
                        <span class="portal-recording-banner__meta" dir="ltr">رمز التسجيل: {{ $recording->recording_passcode }}</span>
                    @endif
                </div>
                @if ($this->hasRecordingRoute)
                    <a href="{{ route('sessions.recording', ['locale' => $locale, 'recording' => $recording->id]) }}" class="btn btn-primary btn-sm" wire:click="openRecording">
                        <i class="fa-solid fa-play"></i> مشاهدة التسجيل
                    </a>
                @elseif ($recording->recording_url)
                    <a href="{{ $recording->recording_url }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm" wire:click="openRecording">
                        <i class="fa-solid fa-play"></i> مشاهدة التسجيل
                    </a>
                @endif
            </div>
        </section>
    @elseif ($this->session->recording && $this->session->recording->status === 'processing')
        <div class="portal-alert portal-alert--compact">
            <div class="portal-alert__content">التسجيل قيد المعالجة — سيُتاح قريباً بعد انتهاء المحاضرة.</div>
        </div>
    @endif

    <div class="portal-dashboard-grid portal-dashboard-grid--wide">
        <div class="portal-main-col">
            @if ($this->session->description)
                <section class="portal-panel session-about">
                    <div class="portal-panel__head">
                        <h2 class="portal-panel__title"><i class="fa-solid fa-circle-info"></i> عن هذه الحصة</h2>
                    </div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        <p class="session-about__text">{{ $this->session->description }}</p>
                    </div>
                </section>
            @endif

            <section class="portal-panel">
                <div class="portal-panel__head session-panel-head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-paperclip"></i> مرفقات الحصة</h2>
                    @if ($this->session->publishedMaterials->isNotEmpty())
                        <span class="session-panel-head__count">{{ $this->session->publishedMaterials->count() }}</span>
                    @endif
                </div>
                <div class="portal-panel__body portal-panel__body--padded">
                    @if ($this->session->publishedMaterials->isEmpty())
                        <div class="session-empty">
                            <span class="session-empty__icon"><i class="fa-regular fa-folder-open"></i></span>
                            <p>لم تُرفَع مواد لهذه الحصة بعد</p>
                            <small>عند رفع المدرب للملفات أو الروابط ستظهر هنا مباشرة.</small>
                        </div>
                    @else
                        <ul class="session-material-list">
                            @foreach ($this->session->publishedMaterials as $material)
                                @php
                                    $isLink = in_array($material->type, ['link', 'teams_recording'], true);
                                @endphp
                                <li class="session-material" wire:key="mat-{{ $material->id }}">
                                    <span class="session-material__icon {{ $isLink ? 'is-link' : 'is-file' }}">
                                        <i class="fa-solid {{ $isLink ? 'fa-link' : 'fa-file-lines' }}"></i>
                                    </span>
                                    <span class="session-material__body">
                                        <strong>{{ $material->title }}</strong>
                                        <span class="session-material__meta">
                                            {{ $isLink ? 'رابط خارجي' : 'ملف' }}
                                            @if ($material->formattedSize())
                                                · {{ $material->formattedSize() }}
                                            @endif
                                        </span>
                                    </span>
                                    @if ($url = $material->downloadUrl())
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                                            <i class="fa-solid {{ $isLink ? 'fa-arrow-up-right-from-square' : 'fa-download' }}"></i>
                                            {{ $isLink ? 'فتح' : 'تحميل' }}
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="portal-panel">
                <div class="portal-panel__head session-panel-head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-file-pen"></i> الواجبات</h2>
                    @if ($this->assignments->isNotEmpty())
                        <span class="session-panel-head__count">{{ $this->assignments->count() }}</span>
                    @endif
                </div>
                <div class="portal-panel__body portal-panel__body--padded">
                    @if ($this->assignments->isEmpty())
                        <div class="session-empty">
                            <span class="session-empty__icon"><i class="fa-regular fa-clipboard"></i></span>
                            <p>لا توجد واجبات مرتبطة بهذه الحصة</p>
                            <small>إن أضاف المدرب واجباً لهذه الحصة ستجده هنا مع موعد التسليم.</small>
                        </div>
                    @else
                        <ul class="session-material-list">
                            @foreach ($this->assignments as $assignment)
                                @php
                                    $sub = $assignment->my_submission ?? null;
                                    $isOverdue = $assignment->due_at?->isPast() && ! $sub;
                                @endphp
                                <li class="session-material" wire:key="asg-{{ $assignment->id }}">
                                    <span class="session-material__icon is-assignment"><i class="fa-solid fa-file-pen"></i></span>
                                    <span class="session-material__body">
                                        <strong>{{ $assignment->title }}</strong>
                                        <span class="session-material__meta">
                                            @if ($assignment->due_at)
                                                <span dir="ltr">{{ $assignment->due_at->format('Y-m-d H:i') }}</span> موعد التسليم
                                            @else
                                                بدون موعد نهائي
                                            @endif
                                        </span>
                                        <span class="session-material__badges">
                                            @if ($sub?->isGraded())
                                                <span class="session-badge session-badge--graded">
                                                    <i class="fa-solid fa-star"></i> {{ $sub->finalScore() }}/{{ $assignment->max_score }}
                                                </span>
                                            @elseif ($sub)
                                                <span class="session-badge session-badge--submitted">
                                                    <i class="fa-solid fa-paper-plane"></i> {{ \App\Support\AssignmentOptions::submissionStatusLabel($sub->status) }}
                                                </span>
                                            @elseif ($isOverdue)
                                                <span class="session-badge session-badge--overdue">
                                                    <i class="fa-solid fa-triangle-exclamation"></i> انتهى الموعد دون تسليم
                                                </span>
                                            @else
                                                <span class="session-badge session-badge--pending">
                                                    <i class="fa-regular fa-hourglass-half"></i> لم يُسلَّم بعد
                                                </span>
                                            @endif
                                        </span>
                                    </span>
                                    <a href="{{ route('assignments.show', ['locale' => $locale, 'assignment' => $assignment->id]) }}" class="btn btn-primary btn-sm">
                                        {{ $sub ? 'عرض التسليم' : 'حل الواجب' }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>

        <aside class="portal-side-col">
            <div class="session-side-card">
                <header class="session-side-card__head">
                    <span class="session-side-card__head-icon"><i class="fa-solid fa-user-check"></i></span>
                    <h3>حضورك</h3>
                </header>
                <div class="session-side-card__body">
                    @if ($record = $this->attendanceRecord)
                        @php
                            $attendanceClass = match (true) {
                                in_array($record->status, ['present', 'late'], true) => 'is-present',
                                $record->status === 'excused' => 'is-excused',
                                default => 'is-absent',
                            };
                            $attendanceIcon = match (true) {
                                in_array($record->status, ['present', 'late'], true) => 'fa-circle-check',
                                $record->status === 'excused' => 'fa-circle-minus',
                                default => 'fa-circle-xmark',
                            };
                            $joinedAt = $record->joined_at ?? $record->teams_joined_at;
                        @endphp
                        <div class="session-attendance {{ $attendanceClass }}">
                            <span class="session-attendance__icon"><i class="fa-solid {{ $attendanceIcon }}"></i></span>
                            <span class="session-attendance__text">
                                <strong>{{ AttendanceOptions::recordStatusLabel($record->status) }}</strong>
                                <small>حالة حضورك لهذه الحصة</small>
                            </span>
                        </div>
                        @if ($joinedAt || in_array($record->source, ['teams_sync', 'zoom_sync'], true))
                            <ul class="session-attendance__details">
                                @if ($joinedAt)
                                    <li>
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        <span>وقت الانضمام</span>
                                        <strong dir="ltr">{{ $joinedAt->format('H:i') }}</strong>
                                    </li>
                                @endif
                                @if (in_array($record->source, ['teams_sync', 'zoom_sync'], true))
                                    <li>
                                        <i class="fa-solid fa-rotate"></i>
                                        <span>حُضِّر تلقائياً من مزوّد الاجتماع</span>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    @elseif ($state === 'completed')
                        <div class="session-attendance is-unknown">
                            <span class="session-attendance__icon"><i class="fa-regular fa-circle-question"></i></span>
                            <span class="session-attendance__text">
                                <strong>لم يُسجَّل بعد</strong>
                                <small>قد تتأخر مزامنة الحضور بعد انتهاء المحاضرة.</small>
                            </span>
                        </div>
                    @else
                        <div class="session-attendance is-waiting">
                            <span class="session-attendance__icon"><i class="fa-regular fa-clock"></i></span>
                            <span class="session-attendance__text">
                                <strong>بانتظار المحاضرة</strong>
                                <small>يُحدَّث حضورك تلقائياً عند انضمامك عبر رابط المحاضرة.</small>
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="session-side-card">
                <header class="session-side-card__head">
                    <span class="session-side-card__head-icon"><i class="fa-solid fa-list-check"></i></span>
                    <h3>ملخص الحصة</h3>
                </header>
                <div class="session-side-card__body">
                    <ul class="session-info-list">
                        <li>
                            <span class="session-info-list__label"><i class="fa-solid fa-signal"></i> الحالة</span>
                            <strong class="session-info-pill {{ $stateMeta['class'] }}">{{ $stateMeta['label'] }}</strong>
                        </li>
                        @if ($this->session->session_number)
                            <li>
                                <span class="session-info-list__label"><i class="fa-solid fa-hashtag"></i> رقم الحصة</span>
                                <strong>{{ $this->session->session_number }}</strong>
                            </li>
                        @endif
                        <li>
                            <span class="session-info-list__label"><i class="fa-regular fa-calendar"></i> التاريخ</span>
                            <strong>{{ $this->session->session_date->translatedFormat('d M Y') }}</strong>
                        </li>
                        @if ($startsAt)
                            <li>
                                <span class="session-info-list__label"><i class="fa-regular fa-clock"></i> الوقت</span>
                                <strong dir="ltr">{{ $startsAt->format('H:i') }}@if($endsAt) – {{ $endsAt->format('H:i') }}@endif</strong>
                            </li>
                        @endif
                        @if ($durationMinutes)
                            <li>
                                <span class="session-info-list__label"><i class="fa-solid fa-stopwatch"></i> المدة</span>
                                <strong>{{ $durationMinutes }} دقيقة</strong>
                            </li>
                        @endif
                        <li>
                            <span class="session-info-list__label"><i class="fa-solid fa-paperclip"></i> المرفقات</span>
                            <strong class="session-info-count">{{ $this->session->publishedMaterials->count() }}</strong>
                        </li>
                        <li>
                            <span class="session-info-list__label"><i class="fa-solid fa-file-pen"></i> الواجبات</span>
                            <strong class="session-info-count">{{ $this->assignments->count() }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    .portal-session-detail{display:flex;flex-direction:column;gap:1rem}

    /* Hero */
    .session-hero{position:relative;overflow:hidden;padding:1.35rem 1.5rem;border-radius:18px;background:linear-gradient(135deg,#0f5132 0%,#1b8354 62%,#b8943f 165%);color:#fff;box-shadow:0 14px 32px rgba(15,81,50,.18)}
    .session-hero--live{background:linear-gradient(135deg,#7f1d1d 0%,#b91c1c 60%,#f59e0b 170%)}
    .session-hero__top{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.9rem}
    .session-hero__back{display:inline-flex;align-items:center;gap:.4rem;color:rgba(255,255,255,.85);font-size:.78rem;font-weight:800;text-decoration:none}
    .session-hero__back:hover{color:#fff}
    .session-hero__state{display:inline-flex;align-items:center;gap:.4rem;padding:.32rem .7rem;border-radius:999px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);font-size:.72rem;font-weight:900}
    .session-hero__state.is-live{background:rgba(255,255,255,.92);color:#b91c1c;animation:portal-pulse 2s infinite}
    .session-hero__state.is-completed{background:rgba(255,255,255,.9);color:#166534}
    .session-hero__main{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap}
    .session-hero__eyebrow{display:block;margin-bottom:.35rem;font-size:.74rem;font-weight:800;opacity:.85}
    .session-hero__copy h1{margin:0 0 .35rem;font-size:clamp(1.25rem,2.4vw,1.75rem);font-weight:900;color:#fff}
    .session-hero__copy p{margin:0;font-size:.86rem;color:rgba(255,255,255,.85)}
    .session-hero__actions{display:flex;gap:.5rem;flex-shrink:0}
    .session-hero__actions .btn{font-weight:800}
    .session-hero__actions .btn-light{background:#fff;color:#0f5132;border:0}
    .session-hero__facts{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:1rem}
    .session-hero__fact{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .7rem;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);font-size:.73rem;font-weight:700;color:#fff}
    .session-hero__fact--accent{background:rgba(255,255,255,.92);color:#0f5132}

    /* Live strip */
    .session-live-strip{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;padding:.75rem 1rem;border:1px solid #fecaca;border-radius:12px;background:#fef2f2;color:#991b1b;font-size:.82rem}
    .session-live-strip__dot{width:.6rem;height:.6rem;border-radius:50%;background:#dc2626;animation:portal-pulse 1.5s infinite}

    /* Panels */
    .session-panel-head{display:flex;align-items:center;gap:.5rem}
    .session-panel-head__count{display:inline-grid;place-items:center;min-width:1.5rem;height:1.5rem;padding:0 .4rem;border-radius:999px;background:#ecfdf5;color:#166534;font-size:.72rem;font-weight:900}
    .session-about__text{margin:0;color:#334155;font-size:.88rem;line-height:1.9}

    /* Materials & assignments */
    .session-material-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.65rem}
    .session-material{display:flex;align-items:center;gap:.8rem;padding:.85rem .9rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;transition:border-color .15s,box-shadow .15s}
    .session-material:hover{border-color:#86efac;box-shadow:0 4px 14px rgba(22,101,52,.08)}
    .session-material__icon{display:grid;place-items:center;flex:0 0 auto;width:2.4rem;height:2.4rem;border-radius:10px;font-size:.95rem}
    .session-material__icon.is-file{background:#eff6ff;color:#1d4ed8}
    .session-material__icon.is-link{background:#f0fdf4;color:#15803d}
    .session-material__icon.is-assignment{background:#fef9ec;color:#b45309}
    .session-material__body{flex:1;min-width:0;display:flex;flex-direction:column;gap:.18rem}
    .session-material__body strong{font-size:.85rem;color:#0f172a}
    .session-material__meta{font-size:.72rem;color:#64748b;font-weight:600}
    .session-material__badges{display:flex;flex-wrap:wrap;gap:.35rem;margin-top:.1rem}
    .session-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800}
    .session-badge--graded{background:#ecfdf5;color:#15803d}
    .session-badge--submitted{background:#eff6ff;color:#1d4ed8}
    .session-badge--pending{background:#f8fafc;color:#64748b}
    .session-badge--overdue{background:#fef2f2;color:#b91c1c}

    /* Empty states */
    .session-empty{display:flex;flex-direction:column;align-items:center;gap:.3rem;padding:1.75rem 1rem;text-align:center}
    .session-empty__icon{display:grid;place-items:center;width:3rem;height:3rem;margin-bottom:.35rem;border-radius:50%;background:#f1f5f9;color:#94a3b8;font-size:1.15rem}
    .session-empty p{margin:0;font-size:.85rem;font-weight:800;color:#334155}
    .session-empty small{color:#94a3b8;font-size:.73rem}

    /* Side cards (attendance + summary) */
    .session-side-card{overflow:hidden;border:1px solid #e2e8f0;border-radius:15px;background:#fff;box-shadow:0 4px 14px rgba(15,23,42,.04)}
    .session-side-card + .session-side-card{margin-top:1rem}
    .session-side-card__head{display:flex;align-items:center;gap:.6rem;padding:.85rem 1rem;background:#f8fafc;border-bottom:1px solid #e2e8f0}
    .session-side-card__head h3{margin:0;font-size:.88rem;font-weight:900;color:#0f172a}
    .session-side-card__head-icon{display:grid;place-items:center;width:2rem;height:2rem;border-radius:9px;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff;font-size:.8rem}
    .session-side-card__body{padding:1rem}

    .session-attendance{display:flex;align-items:center;gap:.7rem;padding:.85rem;border-radius:12px;border:1px solid transparent}
    .session-attendance__icon{display:grid;place-items:center;flex:0 0 auto;width:2.5rem;height:2.5rem;border-radius:50%;background:rgba(255,255,255,.7);font-size:1.05rem}
    .session-attendance__text{display:flex;flex-direction:column;gap:.1rem;min-width:0}
    .session-attendance__text strong{font-size:.88rem}
    .session-attendance__text small{font-size:.7rem;opacity:.8;line-height:1.5}
    .session-attendance.is-present{background:#ecfdf5;border-color:#bbf7d0;color:#15803d}
    .session-attendance.is-excused{background:#fefce8;border-color:#fde68a;color:#a16207}
    .session-attendance.is-absent{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
    .session-attendance.is-unknown,.session-attendance.is-waiting{background:#f8fafc;border-color:#e2e8f0;color:#475569}
    .session-attendance__details{list-style:none;margin:.75rem 0 0;padding:.15rem 0 0;display:flex;flex-direction:column;gap:.5rem;border-top:1px dashed #e2e8f0}
    .session-attendance__details li{display:flex;align-items:center;gap:.5rem;padding-top:.5rem;color:#64748b;font-size:.74rem;font-weight:600}
    .session-attendance__details li span{flex:1}
    .session-attendance__details li strong{color:#0f172a;font-size:.78rem}
    .session-attendance__details i{display:grid;place-items:center;width:1.6rem;height:1.6rem;border-radius:8px;background:#ecfdf5;color:#15803d;font-size:.68rem;flex:0 0 auto}

    .session-info-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column}
    .session-info-list li{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.6rem 0;border-bottom:1px solid #f1f5f9}
    .session-info-list li:first-child{padding-top:0}
    .session-info-list li:last-child{border-bottom:0;padding-bottom:0}
    .session-info-list__label{display:inline-flex;align-items:center;gap:.45rem;color:#64748b;font-size:.76rem;font-weight:700}
    .session-info-list__label i{display:grid;place-items:center;width:1.6rem;height:1.6rem;border-radius:8px;background:#f1f5f9;color:#1b8354;font-size:.66rem}
    .session-info-list strong{color:#0f172a;font-size:.8rem;font-weight:800}
    .session-info-pill{padding:.2rem .6rem;border-radius:999px;background:#f1f5f9;color:#475569!important;font-size:.7rem!important}
    .session-info-pill.is-live{background:#fef2f2;color:#b91c1c!important}
    .session-info-pill.is-upcoming{background:#eff6ff;color:#1d4ed8!important}
    .session-info-pill.is-completed{background:#ecfdf5;color:#15803d!important}
    .session-info-pill.is-cancelled{background:#fef2f2;color:#b91c1c!important}
    .session-info-count{display:inline-grid;place-items:center;min-width:1.7rem;height:1.7rem;padding:0 .45rem;border-radius:999px;background:#ecfdf5;color:#166534!important}

    /* Recording banner (kept) */
    .portal-recording-banner{display:flex;flex-wrap:wrap;align-items:center;gap:1rem;background:linear-gradient(135deg,#f0f4ff,#fff);border-radius:10px}
    .portal-recording-banner__icon{font-size:2rem;color:#464eb8}
    .portal-recording-banner__body{flex:1;min-width:10rem;display:flex;flex-direction:column;gap:.15rem}
    .portal-recording-banner__meta{font-size:.78rem;color:var(--sa-muted)}
    .portal-panel--recording{border-color:rgba(70,78,184,.25)}

    @keyframes portal-pulse{0%,100%{opacity:1}50%{opacity:.65}}
    @media(max-width:640px){
        .session-hero{padding:1.1rem 1.15rem}
        .session-hero__main{align-items:flex-start;flex-direction:column}
        .session-hero__actions{width:100%}
        .session-hero__actions .btn{flex:1}
        .session-material{flex-wrap:wrap}
        .session-material .btn{width:100%}
    }
</style>
@endpush
