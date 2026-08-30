<?php

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\SessionMaterial;
use App\Services\AcademicSessionService;
use App\Services\AssignmentService;
use App\Services\AttendanceService;
use App\Services\InstructorService;
use App\Services\MicrosoftTeams\TeamsAttendanceSyncService;
use App\Services\MicrosoftTeams\TeamsRecordingSyncService;
use App\Services\SessionMaterialService;
use App\Services\SessionRecordingService;
use App\Services\Zoom\ZoomAttendanceSyncService;
use App\Services\Zoom\ZoomMeetingService;
use App\Services\Zoom\ZoomRecordingSyncService;
use App\Support\AssignmentOptions;
use App\Support\AttendanceOptions;
use App\Support\ZoomSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app-user')]
#[Title('مركز الحصة | لوحة المدرب')]
class extends Component
{
    use WithFileUploads;

    public AcademicSection $section;

    public AttendanceSession $session;

    #[Url]
    public string $tab = 'materials';

    public string $flashMessage = '';

    public string $materialTitle = '';

    public $materialFile = null;

    public string $materialLink = '';

    public string $recordingManualUrl = '';

    public bool $showAssignmentForm = false;

    public string $assignmentTitle = '';

    public string $assignmentInstructions = '';

    public int $assignmentMaxScore = 100;

    public string $assignmentDueAt = '';

    public bool $assignmentAllowLate = true;

    public ?int $gradingSubmissionId = null;

    public int $gradeScore = 0;

    public string $gradeFeedback = '';

    public function mount(AcademicSection $section, AttendanceSession $session, InstructorService $instructors): void
    {
        abort_unless($session->section_id === $section->id, 404);
        $instructors->authorizeSession(auth()->user(), $session);

        $this->section = $section->load(['course', 'students']);
        $this->session = $session->load(['materials', 'recording', 'records.student', 'zoomMeeting.host', 'zoxAgentMeeting']);
    }

    protected function reloadSession(): void
    {
        unset($this->attendanceRoster, $this->attendanceSummary, $this->recording, $this->assignments, $this->zoomMeeting, $this->meetingProviderLabel, $this->joinUrl);
        $this->session = AttendanceSession::query()
            ->with(['materials', 'recording', 'records.student', 'zoomMeeting.host', 'zoxAgentMeeting'])
            ->findOrFail($this->session->id);
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
    public function zoomMeeting()
    {
        return $this->session->zoomMeeting;
    }

    #[Computed]
    public function meetingProviderLabel(): string
    {
        if ($this->session->zoxAgentMeeting) {
            return 'ZoxAgent Meet';
        }

        if ($this->zoomMeeting) {
            return 'Zoom';
        }

        if ($this->session->teams_join_web_url) {
            return 'Microsoft Teams';
        }

        if ($this->session->meeting_url) {
            return 'رابط يدوي';
        }

        return 'غير محدد';
    }

    #[Computed]
    public function assignments()
    {
        return app(InstructorService::class)->assignmentsForSession($this->session);
    }

    #[Computed]
    public function recording()
    {
        return app(SessionRecordingService::class)->forSession($this->session);
    }

    #[Computed]
    public function attendanceRoster()
    {
        return app(AttendanceService::class)->rosterForSession($this->session, $this->section);
    }

    #[Computed]
    public function attendanceSummary(): array
    {
        return AttendanceOptions::summarizeRecords($this->attendanceRoster);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['meeting', 'materials', 'assignments', 'recording', 'attendance'], true)) {
            $this->tab = $tab;
        }
    }

    public function createZoxAgentMeeting(\App\Services\ZoxAgent\ZoxAgentMeetingService $meetings, InstructorService $instructors): void
    {
        $instructors->authorizeSession(auth()->user(), $this->session);

        try {
            $meetings->ensureMeeting($this->session);
            $this->flash('تم إنشاء / تحديث قاعة ZoxAgent.');
            $this->reloadSession();
        } catch (\Throwable $e) {
            $this->flash('تعذّر إنشاء قاعة ZoxAgent: '.$e->getMessage());
        }
    }

    public function syncZoxAgentAttendance(\App\Services\ZoxAgent\ZoxAgentMeetingService $meetings, InstructorService $instructors): void
    {
        $instructors->authorizeSession(auth()->user(), $this->session);

        if (! $this->session->zoxAgentMeeting) {
            $this->flash('لا توجد قاعة ZoxAgent لهذه الحصة.');

            return;
        }

        $synced = $meetings->syncAttendance($this->session);
        $this->flash('تمت مزامنة حضور ZoxAgent ('.$synced.' طالب).');
        $this->reloadSession();
    }

    public function endZoxAgentMeeting(\App\Services\ZoxAgent\ZoxAgentMeetingService $meetings, InstructorService $instructors): void
    {
        $instructors->authorizeSession(auth()->user(), $this->session);

        if (! $this->session->zoxAgentMeeting) {
            $this->flash('لا توجد قاعة ZoxAgent لهذه الحصة.');

            return;
        }

        try {
            $meetings->endRoom($this->session);
            $this->flash('تم إنهاء قاعة ZoxAgent وإيقاف التسجيل السحابي.');
            $this->reloadSession();
        } catch (\Throwable $e) {
            $this->flash('تعذّر إنهاء القاعة: '.$e->getMessage());
        }
    }

    public function syncZoxAgentRecording(\App\Services\ZoxAgent\ZoxAgentMeetingService $meetings, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.recordings.view');

        if (! $this->session->zoxAgentMeeting) {
            $this->flash('لا توجد قاعة ZoxAgent لهذه الحصة.');

            return;
        }

        try {
            $count = $meetings->pullRecordings($this->session);
            $this->flash($count > 0 ? 'تمت مزامنة تسجيل ZoxAgent.' : 'لا يوجد تسجيل جاهز بعد.');
            $this->reloadSession();
        } catch (\Throwable $e) {
            $this->flash('تعذّر مزامنة التسجيل: '.$e->getMessage());
        }
    }

    public function createZoomMeeting(ZoomMeetingService $meetings, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.zoom.meeting.create');

        try {
            $meetings->ensureMeeting($this->session);
            $this->flash('تم إنشاء / تحديث اجتماع Zoom.');
            $this->reloadSession();
        } catch (\Throwable $e) {
            $this->flash('تعذّر إنشاء اجتماع Zoom: '.$e->getMessage());
        }
    }

    public function syncZoomAttendance(ZoomAttendanceSyncService $sync, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.zoom.attendance.sync');

        $meeting = $this->session->zoomMeeting;
        if (! $meeting) {
            $this->flash('لا يوجد اجتماع Zoom لهذه الحصة.');

            return;
        }

        $sync->syncMeeting($meeting);
        $this->flash('تمت مزامنة الحضور من Zoom.');
        $this->reloadSession();
    }

    public function syncZoomRecording(ZoomRecordingSyncService $sync, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.zoom.recording.sync');

        $meeting = $this->session->zoomMeeting;
        if (! $meeting) {
            $this->flash('لا يوجد اجتماع Zoom لهذه الحصة.');

            return;
        }

        $sync->syncMeeting($meeting);
        $this->flash('تمت محاولة مزامنة التسجيل من Zoom.');
        $this->reloadSession();
    }

    public function uploadMaterial(SessionMaterialService $materials, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.materials.upload');

        $this->validate([
            'materialTitle' => ['required', 'string', 'max:255'],
            'materialFile' => ['required', 'file', 'max:51200'],
        ], [], [
            'materialTitle' => 'عنوان المرفق',
            'materialFile' => 'الملف',
        ]);

        $materials->uploadFile($this->session, auth()->user(), $this->materialFile, $this->materialTitle);
        $this->reset(['materialTitle', 'materialFile']);
        $this->flash('تم رفع المرفق.');
        unset($this->session);
        $this->session = AttendanceSession::query()->with(['materials', 'recording', 'records.student'])->findOrFail($this->session->id);
    }

    public function addMaterialLink(SessionMaterialService $materials, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.materials.upload');

        $this->validate([
            'materialTitle' => ['required', 'string', 'max:255'],
            'materialLink' => ['required', 'url', 'max:500'],
        ], [], [
            'materialTitle' => 'عنوان الرابط',
            'materialLink' => 'الرابط',
        ]);

        $materials->addLink($this->session, auth()->user(), $this->materialTitle, $this->materialLink);
        $this->reset(['materialTitle', 'materialLink']);
        $this->flash('تم إضافة الرابط.');
        unset($this->session);
        $this->session = AttendanceSession::query()->with(['materials', 'recording', 'records.student'])->findOrFail($this->session->id);
    }

    public function deleteMaterial(int $materialId, SessionMaterialService $materials, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.materials.delete');

        $material = SessionMaterial::query()
            ->where('id', $materialId)
            ->where('attendance_session_id', $this->session->id)
            ->first();

        if ($material) {
            $materials->delete($material);
            $this->flash('تم حذف المرفق.');
            unset($this->session);
            $this->session = AttendanceSession::query()->with(['materials', 'recording', 'records.student'])->findOrFail($this->session->id);
        }
    }

    public function createAssignment(AssignmentService $assignments, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.assignments.create');

        $this->validate([
            'assignmentTitle' => ['required', 'string', 'max:255'],
            'assignmentInstructions' => ['nullable', 'string', 'max:10000'],
            'assignmentMaxScore' => ['required', 'integer', 'min:1', 'max:1000'],
            'assignmentDueAt' => ['nullable', 'date'],
        ], [], [
            'assignmentTitle' => 'عنوان الواجب',
        ]);

        $assignment = Assignment::query()->create([
            'section_id' => $this->section->id,
            'attendance_session_id' => $this->session->id,
            'scope' => 'session',
            'title' => $this->assignmentTitle,
            'instructions' => $this->assignmentInstructions ?: null,
            'max_score' => $this->assignmentMaxScore,
            'due_at' => $this->assignmentDueAt ?: null,
            'allow_late_submission' => $this->assignmentAllowLate,
            'max_attempts' => 1,
            'max_files' => 5,
            'allow_text_submission' => true,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        if (auth()->user()->canInstructor('instructor.assignments.publish')) {
            $assignments->publish($assignment);
        }

        $this->reset(['assignmentTitle', 'assignmentInstructions', 'assignmentDueAt', 'showAssignmentForm']);
        $this->assignmentMaxScore = 100;
        $this->assignmentAllowLate = true;
        $this->flash(auth()->user()->canInstructor('instructor.assignments.publish') ? 'تم إنشاء ونشر الواجب.' : 'تم حفظ الواجب كمسودة.');
        unset($this->assignments);
    }

    public function publishAssignment(int $assignmentId, AssignmentService $assignments, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.assignments.publish');

        $assignment = Assignment::query()
            ->where('id', $assignmentId)
            ->where('section_id', $this->section->id)
            ->firstOrFail();

        $assignments->publish($assignment);
        $this->flash('تم نشر الواجب للطلاب.');
        unset($this->assignments);
    }

    public function openGrading(int $submissionId): void
    {
        abort_unless(auth()->user()?->canInstructor('instructor.assignments.grade'), 403);

        $submission = AssignmentSubmission::query()
            ->with('assignment')
            ->whereHas('assignment', fn ($q) => $q->where('section_id', $this->section->id))
            ->findOrFail($submissionId);

        $this->gradingSubmissionId = $submission->id;
        $this->gradeScore = (int) ($submission->score ?? 0);
        $this->gradeFeedback = $submission->feedback ?? '';
    }

    public function submitGrade(AssignmentService $assignments, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.assignments.grade');

        $submission = AssignmentSubmission::query()
            ->with('assignment')
            ->whereHas('assignment', fn ($q) => $q->where('section_id', $this->section->id))
            ->findOrFail($this->gradingSubmissionId);

        $assignments->grade($submission, $this->gradeScore, $this->gradeFeedback ?: null, auth()->user());
        $this->gradingSubmissionId = null;
        $this->flash('تم حفظ الدرجة.');
        unset($this->assignments);
    }

    public function syncRecording(TeamsRecordingSyncService $sync, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.recordings.view');
        $sync->syncSession($this->session);
        $this->flash('تمت محاولة مزامنة التسجيل من Teams.');
        unset($this->recording, $this->session);
        $this->session = AttendanceSession::query()->with(['materials', 'recording', 'records.student'])->findOrFail($this->session->id);
    }

    public function saveManualRecording(SessionRecordingService $recordings, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.recordings.view');

        $this->validate(['recordingManualUrl' => ['required', 'url', 'max:1000']]);

        $recordings->setManualUrl($this->session, $this->recordingManualUrl, auth()->user());
        $this->recordingManualUrl = '';
        $this->flash('تم حفظ رابط التسجيل.');
        unset($this->recording, $this->session);
        $this->session = AttendanceSession::query()->with(['materials', 'recording', 'records.student'])->findOrFail($this->session->id);
    }

    public function publishRecording(SessionRecordingService $recordings, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.recordings.publish');

        $recording = $this->recording;

        if (! $recording?->recording_url && ! $recording?->play_url) {
            $this->flash('لا يوجد تسجيل جاهز للنشر.');

            return;
        }

        $recordings->publish($recording, auth()->user());
        $this->flash('تم نشر التسجيل للطلاب.');
        unset($this->recording, $this->session);
        $this->session = AttendanceSession::query()->with(['materials', 'recording', 'records.student'])->findOrFail($this->session->id);
    }

    public function hideRecording(SessionRecordingService $recordings, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.recordings.publish');

        $recording = $this->recording;

        if ($recording) {
            $recordings->hide($recording);
            $this->flash('تم إخفاء التسجيل.');
            $this->reloadSession();
        }
    }

    public function updateAttendanceStatus(int $studentId, string $status, AttendanceService $attendance): void
    {
        $user = auth()->user();
        $existing = $this->attendanceRoster->firstWhere('student_id', $studentId);

        abort_unless($attendance->instructorMayEditRecord($user, $existing?->exists ? $existing : null), 403);

        $isOverride = $existing?->source === 'teams_sync' && $user->canInstructor('instructor.attendance.override');

        $attendance->setRecordStatus($this->session, $studentId, $status, $user, $isOverride);
        $this->flash('تم تحديث حالة الحضور.');
        $this->reloadSession();
    }

    public function markAllPresent(AttendanceService $attendance, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.attendance.record_manual');
        $count = $attendance->markAllPresent($this->session, auth()->user());
        $this->flash("تم تسجيل {$count} طالباً كحاضرين.");
        $this->reloadSession();
    }

    public function syncTeamsAttendance(TeamsAttendanceSyncService $sync, InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.attendance.view');
        $sync->syncSession($this->session);
        $this->flash('تمت مزامنة الحضور من Teams.');
        $this->reloadSession();
    }

    protected function flash(string $message): void
    {
        $this->flashMessage = $message;
    }
};
?>

@php
    $locale = app()->getLocale();
    $state = $this->timing['state'];
    $breadcrumb = [
        ['href' => route('instructor.sections.show', ['locale' => $locale, 'section' => $section->id]), 'label' => $section->name],
        ['label' => $session->displayTitle()],
    ];
@endphp

@include('partials.instructor.shell-start', [
    'instructorActive' => 'sections',
    'instructorTitle' => $session->displayTitle(),
    'instructorBreadcrumb' => $breadcrumb,
])

<div class="portal-dashboard portal-instructor-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ $session->displayTitle() }}</h1>
            <p class="portal-orders-intro__desc">{{ $section->name }} — {{ $session->session_date->format('Y-m-d') }}</p>
        </div>
        <div class="portal-inst-hub-actions">
            <span class="portal-inst-badge portal-inst-badge--{{ $state }}">{{ match($state) { 'live' => 'مباشر الآن', 'completed' => 'منتهية', 'upcoming' => 'قادمة', default => 'مجدولة' } }}</span>
            <span class="portal-inst-badge">{{ $this->meetingProviderLabel }}</span>
            @if ($this->session->zoxAgentMeeting && \Illuminate\Support\Facades\Route::has('sessions.join'))
                <a href="{{ route('sessions.join', ['locale' => $locale, 'session' => $session->id]) }}" class="portal-btn portal-btn--primary portal-btn--sm">
                    <i class="fa-solid fa-video"></i> بدء قاعة ZoxAgent
                </a>
            @elseif ($this->zoomMeeting && auth()->user()?->canInstructor('instructor.zoom.meeting.start') && \Illuminate\Support\Facades\Route::has('instructor.zoom.start'))
                <a href="{{ route('instructor.zoom.start', ['locale' => $locale, 'session' => $session->id]) }}" class="portal-btn portal-btn--primary portal-btn--sm">
                    <i class="fa-solid fa-video"></i> بدء اجتماع Zoom
                </a>
            @elseif ($this->zoomMeeting && auth()->user()?->canInstructor('instructor.zoom.join_link.view') && \Illuminate\Support\Facades\Route::has('sessions.join'))
                <a href="{{ route('sessions.join', ['locale' => $locale, 'session' => $session->id]) }}" class="portal-btn portal-btn--primary portal-btn--sm">
                    <i class="fa-solid fa-video"></i> انضمام للمحاضرة
                </a>
            @endif
            @canInstructor('instructor.teams.join_link.view')
                @if (! $this->zoomMeeting && $this->joinUrl)
                    <a href="{{ $this->joinUrl }}" target="_blank" rel="noopener" class="portal-btn portal-btn--primary portal-btn--sm">
                        <i class="fa-solid fa-video"></i> انضمام Teams
                    </a>
                @endif
            @endcanInstructor
        </div>
    </div>

    @if ($flashMessage)
        <div class="portal-alert portal-alert--info portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-check"></i></span>
            <div class="portal-alert__content">{{ $flashMessage }}</div>
        </div>
    @endif

    <nav class="portal-inst-tabs" aria-label="أقسام مركز الحصة">
        <button type="button" @class(['portal-inst-tab', 'is-active' => $tab === 'meeting']) wire:click="setTab('meeting')">الاجتماع</button>
        <button type="button" @class(['portal-inst-tab', 'is-active' => $tab === 'materials']) wire:click="setTab('materials')">المرفقات</button>
        <button type="button" @class(['portal-inst-tab', 'is-active' => $tab === 'assignments']) wire:click="setTab('assignments')">الواجبات</button>
        <button type="button" @class(['portal-inst-tab', 'is-active' => $tab === 'recording']) wire:click="setTab('recording')">التسجيل</button>
        @canInstructor('instructor.attendance.view')
            <button type="button" @class(['portal-inst-tab', 'is-active' => $tab === 'attendance']) wire:click="setTab('attendance')">الحضور</button>
        @endcanInstructor
    </nav>

    @if ($tab === 'meeting')
        @php $zoom = $this->zoomMeeting; @endphp
        <section class="portal-inst-panel">
            <header class="portal-inst-panel__head">
                <h2>التحكم بالاجتماع</h2>
                <p>إنشاء الاجتماع، بدء المحاضرة، ومزامنة الحضور والتسجيل حسب مزوّد الحصة.</p>
            </header>

            <dl class="portal-ur-detail-list" style="margin-bottom:1rem;">
                <div class="portal-ur-detail-row">
                    <dt>المزوّد</dt>
                    <dd>{{ $this->meetingProviderLabel }}</dd>
                </div>
                @if ($session->zoxAgentMeeting)
                    <div class="portal-ur-detail-row">
                        <dt>قاعة ZoxAgent</dt>
                        <dd dir="ltr">{{ $session->zoxAgentMeeting->room_code }}</dd>
                    </div>
                @elseif ($zoom)
                    <div class="portal-ur-detail-row">
                        <dt>حالة Zoom</dt>
                        <dd>{{ $zoom->status ?: 'مرتبط' }}</dd>
                    </div>
                    @if ($zoom->host)
                        <div class="portal-ur-detail-row">
                            <dt>المضيف</dt>
                            <dd dir="ltr">{{ $zoom->host->email ?: $zoom->host->zoom_user_id }}</dd>
                        </div>
                    @endif
                    @if ($zoom->attendance_synced_at)
                        <div class="portal-ur-detail-row">
                            <dt>آخر مزامنة حضور</dt>
                            <dd>{{ $zoom->attendance_synced_at->diffForHumans() }}</dd>
                        </div>
                    @endif
                @elseif ($session->teams_join_web_url)
                    <div class="portal-ur-detail-row">
                        <dt>Teams</dt>
                        <dd>اجتماع مرتبط</dd>
                    </div>
                @else
                    <div class="portal-ur-detail-row">
                        <dt>الاجتماع</dt>
                        <dd>لم يُنشأ بعد</dd>
                    </div>
                @endif
            </dl>

            <div class="portal-inst-hub-actions">
                @if (\App\Support\ZoxAgentSettings::enabled())
                    <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="createZoxAgentMeeting" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createZoxAgentMeeting">{{ $session->zoxAgentMeeting ? 'تحديث قاعة ZoxAgent' : 'إنشاء قاعة ZoxAgent' }}</span>
                        <span wire:loading wire:target="createZoxAgentMeeting">جاري الإنشاء…</span>
                    </button>
                    @if ($session->zoxAgentMeeting)
                        <button type="button" class="portal-btn portal-btn--ghost portal-btn--sm" wire:click="endZoxAgentMeeting" wire:confirm="إنهاء القاعة يوقف البث والتسجيل السحابي. متابعة؟">إنهاء القاعة</button>
                    @endif
                @endif
                @if (ZoomSettings::enabled() && auth()->user()?->canInstructor('instructor.zoom.meeting.create'))
                    <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="createZoomMeeting" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createZoomMeeting">{{ $zoom ? 'تحديث اجتماع Zoom' : 'إنشاء اجتماع Zoom' }}</span>
                        <span wire:loading wire:target="createZoomMeeting">جاري الإنشاء…</span>
                    </button>
                @endif
                @if ($zoom && auth()->user()?->canInstructor('instructor.zoom.meeting.start') && \Illuminate\Support\Facades\Route::has('instructor.zoom.start'))
                    <a href="{{ route('instructor.zoom.start', ['locale' => $locale, 'session' => $session->id]) }}" class="portal-btn portal-btn--primary portal-btn--sm">
                        بدء الاجتماع
                    </a>
                @endif
                @if ($zoom && auth()->user()?->canInstructor('instructor.zoom.attendance.sync'))
                    <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncZoomAttendance">مزامنة حضور Zoom</button>
                @endif
                @if ($zoom && auth()->user()?->canInstructor('instructor.zoom.recording.sync'))
                    <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncZoomRecording">مزامنة تسجيل Zoom</button>
                @endif
                @canInstructor('instructor.teams.join_link.view')
                    @if ($session->teams_join_web_url)
                        <a href="{{ $session->teams_join_web_url }}" target="_blank" rel="noopener" class="portal-btn portal-btn--ghost portal-btn--sm">فتح Teams</a>
                    @endif
                @endcanInstructor
            </div>
        </section>
    @endif

    @if ($tab === 'materials')
        <section class="portal-inst-panel">
            <header class="portal-inst-panel__head">
                <h2>مرفقات الحصة</h2>
                <p>تظهر للطلاب في صفحة تفاصيل الحصة بعد النشر.</p>
            </header>

            @if ($session->materials->isNotEmpty())
                <ul class="portal-inst-material-list">
                    @foreach ($session->materials as $material)
                        <li wire:key="mat-{{ $material->id }}" class="portal-inst-material-item">
                            <div>
                                <strong>{{ $material->title }}</strong>
                                <span>{{ $material->type === 'link' ? 'رابط' : ($material->type === 'teams_recording' ? 'تسجيل' : 'ملف') }}</span>
                            </div>
                            @canInstructor('instructor.materials.delete')
                                <button type="button" class="portal-btn portal-btn--ghost portal-btn--sm" wire:click="deleteMaterial({{ $material->id }})" wire:confirm="حذف هذا المرفق؟">حذف</button>
                            @endcanInstructor
                        </li>
                    @endforeach
                </ul>
            @endif

            @canInstructor('instructor.materials.upload')
                <div class="portal-inst-form-grid">
                    <div class="portal-field">
                        <label>عنوان المرفق</label>
                        <input type="text" class="portal-control" wire:model="materialTitle">
                        @error('materialTitle')<span class="portal-field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="portal-field">
                        <label>رفع ملف</label>
                        <input type="file" class="portal-control" wire:model="materialFile">
                        @error('materialFile')<span class="portal-field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="portal-field portal-field--actions">
                        <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="uploadMaterial" wire:loading.attr="disabled">رفع ملف</button>
                    </div>
                </div>
                <div class="portal-inst-form-grid portal-inst-form-grid--link">
                    <div class="portal-field">
                        <label>عنوان الرابط</label>
                        <input type="text" class="portal-control" wire:model="materialTitle">
                    </div>
                    <div class="portal-field">
                        <label>رابط خارجي</label>
                        <input type="url" class="portal-control" wire:model="materialLink" dir="ltr" placeholder="https://">
                        @error('materialLink')<span class="portal-field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="portal-field portal-field--actions">
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="addMaterialLink">إضافة رابط</button>
                    </div>
                </div>
            @endcanInstructor
        </section>
    @endif

    @if ($tab === 'assignments')
        <section class="portal-inst-panel">
            <header class="portal-inst-panel__head portal-inst-panel__head--split">
                <div>
                    <h2>واجبات الحصة</h2>
                    <p>إنشاء ونشر وتصحيح تسليمات الطلاب.</p>
                </div>
                @canInstructor('instructor.assignments.create')
                    <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="$toggle('showAssignmentForm')">
                        {{ $showAssignmentForm ? 'إلغاء' : 'واجب جديد' }}
                    </button>
                @endcanInstructor
            </header>

            @if ($showAssignmentForm)
                <div class="portal-inst-form-card">
                    <div class="portal-inst-form-grid portal-inst-form-grid--2">
                        <div class="portal-field portal-field--wide">
                            <label>عنوان الواجب *</label>
                            <input type="text" class="portal-control" wire:model="assignmentTitle">
                            @error('assignmentTitle')<span class="portal-field-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="portal-field portal-field--wide">
                            <label>التعليمات</label>
                            <textarea class="portal-control" rows="4" wire:model="assignmentInstructions"></textarea>
                        </div>
                        <div class="portal-field">
                            <label>الدرجة العظمى</label>
                            <input type="number" class="portal-control" wire:model="assignmentMaxScore" min="1">
                        </div>
                        <div class="portal-field">
                            <label>الموعد النهائي</label>
                            <input type="datetime-local" class="portal-control" wire:model="assignmentDueAt" dir="ltr">
                        </div>
                        <div class="portal-field portal-field--wide">
                            <label class="portal-check"><input type="checkbox" wire:model="assignmentAllowLate"> السماح بالتسليم المتأخر</label>
                        </div>
                    </div>
                    <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="createAssignment">
                        {{ auth()->user()->canInstructor('instructor.assignments.publish') ? 'إنشاء ونشر' : 'حفظ مسودة' }}
                    </button>
                </div>
            @endif

            @if ($this->assignments->isEmpty())
                <p class="portal-inst-empty">لا توجد واجبات مرتبطة بهذه الحصة.</p>
            @else
                @foreach ($this->assignments as $assignment)
                    <div class="portal-inst-assignment-card" wire:key="asg-{{ $assignment->id }}">
                        <div class="portal-inst-assignment-card__head">
                            <div>
                                <h3>{{ $assignment->title }}</h3>
                                <span class="portal-inst-badge">{{ AssignmentOptions::statusLabel($assignment->status) }}</span>
                            </div>
                            @if ($assignment->status === 'draft' && auth()->user()->canInstructor('instructor.assignments.publish'))
                                <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="publishAssignment({{ $assignment->id }})">نشر</button>
                            @endif
                        </div>
                        <p class="portal-inst-assignment-card__meta">
                            {{ $assignment->submissions_count }} تسليم · {{ $assignment->graded_count }} مُصحَّح
                            @if ($assignment->due_at) · الموعد: {{ $assignment->due_at->format('Y-m-d H:i') }} @endif
                        </p>

                        @canInstructor('instructor.assignments.grade')
                            @php
                                $pending = \App\Models\AssignmentSubmission::query()
                                    ->with('student')
                                    ->where('assignment_id', $assignment->id)
                                    ->whereIn('status', ['submitted', 'late', 'graded'])
                                    ->orderByDesc('submitted_at')
                                    ->limit(10)
                                    ->get();
                            @endphp
                            @if ($pending->isNotEmpty())
                                <ul class="portal-inst-submission-list">
                                    @foreach ($pending as $submission)
                                        <li wire:key="sub-{{ $submission->id }}">
                                            <span>{{ $submission->student?->name_ar ?? 'طالب' }}</span>
                                            <span class="portal-inst-badge">{{ AssignmentOptions::submissionStatusLabel($submission->status) }}</span>
                                            @if ($submission->score !== null)
                                                <span>{{ $submission->score }}/{{ $assignment->max_score }}</span>
                                            @endif
                                            <button type="button" class="portal-btn portal-btn--ghost portal-btn--sm" wire:click="openGrading({{ $submission->id }})">تصحيح</button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @endcanInstructor
                    </div>
                @endforeach
            @endif

            @if ($gradingSubmissionId)
                <div class="portal-inst-modal-backdrop" wire:click.self="$set('gradingSubmissionId', null)">
                    <div class="portal-inst-modal">
                        <h3>تصحيح التسليم</h3>
                        <div class="portal-field">
                            <label>الدرجة</label>
                            <input type="number" class="portal-control" wire:model="gradeScore" min="0">
                        </div>
                        <div class="portal-field">
                            <label>ملاحظات</label>
                            <textarea class="portal-control" rows="3" wire:model="gradeFeedback"></textarea>
                        </div>
                        <div class="portal-inst-modal__actions">
                            <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="submitGrade">حفظ</button>
                            <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="$set('gradingSubmissionId', null)">إلغاء</button>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @endif

    @if ($tab === 'recording')
        @php $rec = $this->recording; @endphp
        <section class="portal-inst-panel">
            <header class="portal-inst-panel__head">
                <h2>تسجيل المحاضرة</h2>
                <p>مزامنة من مزوّد الاجتماع أو إدخال رابط يدوي ثم النشر للطلاب.</p>
            </header>

            <div class="portal-inst-recording-status">
                @if ($rec)
                    <dl class="portal-ur-detail-list">
                        <div class="portal-ur-detail-row">
                            <dt>الحالة</dt>
                            <dd>{{ $rec->statusLabel() }}</dd>
                        </div>
                        @if ($rec->recording_url)
                            <div class="portal-ur-detail-row">
                                <dt>الرابط</dt>
                                <dd dir="ltr" style="word-break:break-all;">{{ Str::limit($rec->recording_url, 80) }}</dd>
                            </div>
                        @endif
                        @if ($rec->formattedDuration())
                            <div class="portal-ur-detail-row">
                                <dt>المدة</dt>
                                <dd>{{ $rec->formattedDuration() }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="portal-inst-empty">لا يوجد تسجيل مرتبط بهذه الحصة بعد.</p>
                @endif
            </div>

            @canInstructor('instructor.recordings.view')
                <div class="portal-inst-hub-actions" style="margin-bottom:1rem;">
                    @if ($this->session->zoxAgentMeeting)
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncZoxAgentRecording">مزامنة من ZoxAgent</button>
                    @endif
                    @if ($this->zoomMeeting && auth()->user()?->canInstructor('instructor.zoom.recording.sync'))
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncZoomRecording">مزامنة من Zoom</button>
                    @endif
                    <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncRecording">مزامنة من Teams</button>
                    @canInstructor('instructor.recordings.publish')
                        @if ($rec && $rec->recording_url && ! $rec->isPublished())
                            <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="publishRecording">نشر للطلاب</button>
                        @endif
                        @if ($rec?->isPublished())
                            <button type="button" class="portal-btn portal-btn--ghost portal-btn--sm" wire:click="hideRecording">إخفاء</button>
                        @endif
                    @endcanInstructor
                </div>
                <div class="portal-inst-form-grid portal-inst-form-grid--link">
                    <div class="portal-field">
                        <label>رابط تسجيل يدوي</label>
                        <input type="url" class="portal-control" wire:model="recordingManualUrl" dir="ltr" placeholder="https://...">
                        @error('recordingManualUrl')<span class="portal-field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="portal-field portal-field--actions">
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="saveManualRecording">حفظ الرابط</button>
                    </div>
                </div>
            @endcanInstructor
        </section>
    @endif

    @if ($tab === 'attendance')
        @php $summary = $this->attendanceSummary; @endphp
        <section class="portal-inst-panel">
            <header class="portal-inst-panel__head portal-inst-panel__head--split">
                <div>
                    <h2>سجل الحضور</h2>
                    <p>تعديل يدوي أو تصحيح سجل المزوّد — {{ $summary['present'] + $summary['late'] }} حاضر من {{ $summary['sessions'] }} طالب.</p>
                </div>
                <div class="portal-inst-hub-actions">
                    @if ($this->session->zoxAgentMeeting)
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncZoxAgentAttendance">مزامنة ZoxAgent</button>
                    @endif
                    @if ($this->zoomMeeting && auth()->user()?->canInstructor('instructor.zoom.attendance.sync'))
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncZoomAttendance">مزامنة Zoom</button>
                    @endif
                    @canInstructor('instructor.attendance.view')
                        <button type="button" class="portal-btn portal-btn--secondary portal-btn--sm" wire:click="syncTeamsAttendance">مزامنة Teams</button>
                    @endcanInstructor
                    @canInstructor('instructor.attendance.record_manual')
                        <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" wire:click="markAllPresent" wire:confirm="تسجيل جميع الطلاب كحاضرين؟">الكل حاضر</button>
                    @endcanInstructor
                </div>
            </header>

            <div class="portal-inst-kpis portal-inst-kpis--compact portal-inst-kpis--attendance">
                <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ $summary['present'] }}</span><span class="portal-inst-kpi__label">حاضر</span></div>
                <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ $summary['late'] }}</span><span class="portal-inst-kpi__label">متأخر</span></div>
                <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ $summary['absent'] }}</span><span class="portal-inst-kpi__label">غائب</span></div>
                <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ $summary['rate'] }}%</span><span class="portal-inst-kpi__label">نسبة الحضور</span></div>
            </div>

            <div class="portal-inst-table-wrap">
                <table class="portal-inst-table">
                    <thead>
                        <tr>
                            <th>الرقم الأكاديمي</th>
                            <th>الطالب</th>
                            <th>الحالة</th>
                            <th>المصدر</th>
                            @if (auth()->user()->canInstructor('instructor.attendance.record_manual') || auth()->user()->canInstructor('instructor.attendance.override'))
                                <th>تعديل</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->attendanceRoster as $record)
                            @php $canEdit = app(\App\Services\AttendanceService::class)->instructorMayEditRecord(auth()->user(), $record->exists ? $record : null); @endphp
                            <tr wire:key="att-{{ $record->student_id }}">
                                <td>{{ $record->student?->academic_id ?? '—' }}</td>
                                <td>{{ $record->student?->name_ar ?? '—' }}</td>
                                <td>
                                    <span class="portal-inst-badge portal-inst-badge--att-{{ $record->status }}">
                                        {{ AttendanceOptions::recordStatusLabel($record->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $sourceLabel = match ($record->source) {
                                            'zoom_sync' => 'Zoom',
                                            'teams_sync' => 'Microsoft Teams',
                                            default => $record->source ? AttendanceOptions::sourceLabel($record->source) : '—',
                                        };
                                    @endphp
                                    {{ $sourceLabel }}
                                </td>
                                @if (auth()->user()->canInstructor('instructor.attendance.record_manual') || auth()->user()->canInstructor('instructor.attendance.override'))
                                    <td>
                                        @if ($canEdit)
                                            <select class="portal-control portal-control--inline" wire:change="updateAttendanceStatus({{ $record->student_id }}, $event.target.value)">
                                                @foreach (AttendanceOptions::recordStatuses() as $value => $label)
                                                    <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span class="portal-inst-empty" title="سجل مزوّد الاجتماع — يتطلب صلاحية تصحيح">مقفل</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="5" class="portal-inst-empty">لا يوجد طلاب في هذه الشعبة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>

@include('partials.instructor.shell-end')
