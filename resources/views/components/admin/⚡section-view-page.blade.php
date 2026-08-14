<?php

use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SessionMaterial;
use App\Services\AcademicSessionService;
use App\Services\Meetings\MeetingProviderManager;
use App\Services\MicrosoftTeams\TeamsAttendanceSyncService;
use App\Services\MicrosoftTeams\TeamsMeetingService;
use App\Services\MicrosoftTeams\TeamsRecordingSyncService;
use App\Services\SessionMaterialService;
use App\Services\SessionRecordingService;
use App\Services\Zoom\ZoomAttendanceSyncService;
use App\Services\Zoom\ZoomMeetingService;
use App\Services\Zoom\ZoomRecordingSyncService;
use App\Support\AcademicStudentOptions;
use App\Support\AttendanceOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تفاصيل الشعبة | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public AcademicSection $section;

    #[Url(as: 'tab')]
    public string $activeTab = 'details';

    public string $studentSearch = '';

    public int $studentsPerPage = 15;

    #[Url]
    public string $attendanceSessionId = '';

    public string $attendanceSessionDate = '';

    public string $attendanceSessionTitle = '';

    public string $materialTitle = '';

    public string $materialLink = '';

    public $materialFile = null;

    public string $recordingManualUrl = '';

    public function mount(AcademicSection $section): void
    {
        abort_unless(auth()->user()?->canAdmin('sections.view'), 403);

        $this->section = $section->load([
            'program',
            'batch.program',
            'course',
            'level',
            'schedule.staff',
        ]);

        $this->section->refreshStudentsCount();
        $this->section->refresh();

        if ($this->attendanceSessionId === '') {
            $latest = AttendanceSession::query()
                ->where('section_id', $section->id)
                ->orderByDesc('session_date')
                ->first();

            if ($latest) {
                $this->attendanceSessionId = (string) $latest->id;
            }
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['details', 'students', 'schedule', 'attendance'], true)) {
            $this->activeTab = $tab;
            if ($tab === 'students') {
                $this->resetPage('studentsPage');
            }
        }
    }

    public function updatedStudentSearch(): void
    {
        $this->resetPage('studentsPage');
    }

    public function updatedStudentsPerPage(): void
    {
        $this->resetPage('studentsPage');
    }

    #[Computed]
    public function paginatedStudents()
    {
        return $this->section->students()
            ->when($this->studentSearch, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->studentSearch.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->studentSearch.'%')
                    ->orWhere('national_id', 'like', '%'.$this->studentSearch.'%')
                    ->orWhere('mobile', 'like', '%'.$this->studentSearch.'%');
            }))
            ->latest('joined_at')
            ->paginate($this->studentsPerPage, pageName: 'studentsPage');
    }

    #[Computed]
    public function attendanceSessions()
    {
        return AttendanceSession::query()
            ->where('section_id', $this->section->id)
            ->orderByDesc('session_date')
            ->get();
    }

    #[Computed]
    public function selectedAttendanceSession(): ?AttendanceSession
    {
        if ($this->attendanceSessionId === '') {
            return null;
        }

        return AttendanceSession::query()
            ->with(['materials', 'recording', 'zoomMeeting.host', 'zoomMeeting.registrants'])
            ->where('section_id', $this->section->id)
            ->find($this->attendanceSessionId);
    }

    #[Computed]
    public function selectedSessionRecords()
    {
        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return collect();
        }

        $records = AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->with('student')
            ->get()
            ->keyBy('student_id');

        return $this->section->students()
            ->orderBy('name_ar')
            ->get()
            ->map(function (AcademicStudent $student) use ($records, $session) {
                $record = $records->get($student->id);

                if ($record) {
                    return $record;
                }

                return (new AttendanceRecord([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => 'absent',
                ]))->setRelation('student', $student);
            });
    }

    #[Computed]
    public function attendanceSectionSummary(): array
    {
        $sessionIds = AttendanceSession::query()
            ->where('section_id', $this->section->id)
            ->pluck('id');

        $records = AttendanceRecord::query()
            ->whereIn('attendance_session_id', $sessionIds)
            ->get();

        $summary = AttendanceOptions::summarizeRecords($records);

        return [
            'sessions' => $this->attendanceSessions->count(),
            'students' => $this->section->students_count,
            'avg_rate' => $summary['rate'],
            'present_total' => $summary['present'] + $summary['late'],
            'absent_total' => $summary['absent'],
        ];
    }

    public function updateAttendanceStatus(int $studentId, string $status): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        if (! array_key_exists($status, AttendanceOptions::recordStatuses())) {
            return;
        }

        $session = $this->selectedAttendanceSession;

        if (! $session || $session->section_id !== $this->section->id) {
            return;
        }

        AttendanceRecord::query()->updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'source' => 'manual',
                'recorded_by' => auth()->id(),
            ],
        );

        unset($this->selectedSessionRecords, $this->attendanceSectionSummary);
    }

    public function createAttendanceSession(): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $this->validate([
            'attendanceSessionDate' => ['required', 'date'],
            'attendanceSessionTitle' => ['nullable', 'string', 'max:255'],
        ], [], [
            'attendanceSessionDate' => 'تاريخ الجلسة',
        ]);

        try {
            $session = app(AcademicSessionService::class)->createForSection($this->section, [
                'title' => $this->attendanceSessionTitle,
                'session_date' => $this->attendanceSessionDate,
                'published' => true,
                'source' => 'manual',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('attendanceSessionDate', collect($e->errors())->flatten()->first() ?: 'تعذّر إنشاء الجلسة.');

            return;
        }

        $this->attendanceSessionId = (string) $session->id;
        $this->attendanceSessionTitle = '';
        session()->flash('admin_message', 'تم إنشاء جلسة الحضور.');

        unset($this->attendanceSessions, $this->attendanceSectionSummary, $this->selectedAttendanceSession, $this->selectedSessionRecords);
    }

    public function generateUpcomingSessions(AcademicSessionService $sessions): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $count = $sessions->generateUpcomingForSection($this->section);

        session()->flash('admin_message', $count > 0 ? "تم إنشاء {$count} حصة قادمة." : 'لا توجد حصص جديدة للإنشاء.');
        unset($this->attendanceSessions, $this->attendanceSectionSummary);
    }

    public function syncTeamsAttendance(TeamsAttendanceSyncService $sync): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        $sync->syncSession($session);
        session()->flash('admin_message', 'تمت مزامنة الحضور من Teams.');
        unset($this->selectedSessionRecords, $this->attendanceSectionSummary, $this->selectedAttendanceSession);
    }

    public function createTeamsMeeting(TeamsMeetingService $meetings): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        if ($meetings->ensureMeetingForSession($session)) {
            session()->flash('admin_message', 'تم إنشاء اجتماع Teams.');
        } else {
            session()->flash('admin_message', 'تعذّر إنشاء اجتماع Teams — تحقق من الإعدادات.');
        }

        unset($this->selectedAttendanceSession, $this->attendanceSessions);
    }

    public function createZoomMeeting(ZoomMeetingService $meetings): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        try {
            $meetings->ensureMeeting($session);
            session()->flash('admin_message', 'تم إنشاء / تحديث اجتماع Zoom.');
        } catch (\Throwable $e) {
            session()->flash('admin_message', 'تعذّر إنشاء اجتماع Zoom: '.$e->getMessage());
        }

        unset($this->selectedAttendanceSession, $this->attendanceSessions);
    }

    public function syncZoomAttendance(ZoomAttendanceSyncService $sync): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession?->loadMissing('zoomMeeting');
        $meeting = $session?->zoomMeeting;

        if (! $meeting) {
            session()->flash('admin_message', 'لا يوجد اجتماع Zoom لهذه الجلسة.');

            return;
        }

        $sync->syncMeeting($meeting);
        session()->flash('admin_message', 'تمت مزامنة الحضور من Zoom.');
        unset($this->selectedSessionRecords, $this->attendanceSectionSummary, $this->selectedAttendanceSession);
    }

    public function syncZoomRecording(ZoomRecordingSyncService $sync): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession?->loadMissing('zoomMeeting');
        $meeting = $session?->zoomMeeting;

        if (! $meeting) {
            session()->flash('admin_message', 'لا يوجد اجتماع Zoom لهذه الجلسة.');

            return;
        }

        $sync->syncMeeting($meeting);
        session()->flash('admin_message', 'تمت محاولة مزامنة التسجيل من Zoom.');
        unset($this->selectedAttendanceSession);
    }

    public function maskedPasscode(?string $passcode): string
    {
        if (! filled($passcode)) {
            return '—';
        }

        $length = mb_strlen($passcode);

        if ($length <= 2) {
            return str_repeat('•', $length);
        }

        return mb_substr($passcode, 0, 1).str_repeat('•', max(1, $length - 2)).mb_substr($passcode, -1);
    }

    public function uploadSessionMaterial(SessionMaterialService $materials): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        $this->validate([
            'materialTitle' => ['required', 'string', 'max:255'],
            'materialFile' => ['required', 'file', 'max:51200'],
        ], [], [
            'materialTitle' => 'عنوان المرفق',
            'materialFile' => 'الملف',
        ]);

        $materials->uploadFile($session, auth()->user(), $this->materialFile, $this->materialTitle);
        $this->reset(['materialTitle', 'materialFile']);
        session()->flash('admin_message', 'تم رفع المرفق.');
        unset($this->selectedAttendanceSession);
    }

    public function addSessionMaterialLink(SessionMaterialService $materials): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        $this->validate([
            'materialTitle' => ['required', 'string', 'max:255'],
            'materialLink' => ['required', 'url', 'max:500'],
        ], [], [
            'materialTitle' => 'عنوان الرابط',
            'materialLink' => 'الرابط',
        ]);

        $materials->addLink($session, auth()->user(), $this->materialTitle, $this->materialLink);
        $this->reset(['materialTitle', 'materialLink']);
        session()->flash('admin_message', 'تم إضافة الرابط.');
        unset($this->selectedAttendanceSession);
    }

    public function deleteSessionMaterial(int $materialId, SessionMaterialService $materials): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $material = SessionMaterial::query()
            ->where('id', $materialId)
            ->whereHas('session', fn ($q) => $q->where('section_id', $this->section->id))
            ->first();

        if ($material) {
            $materials->delete($material);
            session()->flash('admin_message', 'تم حذف المرفق.');
        }

        unset($this->selectedAttendanceSession);
    }

    public function syncSessionRecording(TeamsRecordingSyncService $sync): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        $sync->syncSession($session);
        session()->flash('admin_message', 'تمت محاولة مزامنة التسجيل من Teams.');
        unset($this->selectedAttendanceSession);
    }

    public function saveManualRecording(SessionRecordingService $recordings): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = $this->selectedAttendanceSession;

        if (! $session) {
            return;
        }

        $this->validate(['recordingManualUrl' => ['required', 'url', 'max:1000']]);

        $recordings->setManualUrl($session, $this->recordingManualUrl, auth()->user());
        $this->recordingManualUrl = '';
        session()->flash('admin_message', 'تم حفظ رابط التسجيل اليدوي.');
        unset($this->selectedAttendanceSession);
    }

    public function publishSessionRecording(SessionRecordingService $recordings): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $recording = $this->selectedAttendanceSession?->recording;

        if (! $recording?->recording_url) {
            session()->flash('admin_message', 'لا يوجد تسجيل جاهز للنشر.');

            return;
        }

        $recordings->publish($recording, auth()->user());
        session()->flash('admin_message', 'تم نشر التسجيل للطلاب.');
        unset($this->selectedAttendanceSession);
    }

    public function hideSessionRecording(SessionRecordingService $recordings): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $recording = $this->selectedAttendanceSession?->recording;

        if ($recording) {
            $recordings->hide($recording);
            session()->flash('admin_message', 'تم إخفاء التسجيل.');
        }

        unset($this->selectedAttendanceSession);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.sections'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.sections'), 'label' => 'الشعب الدراسية'],
        ['label' => $section->name],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-program-stats">
    <div class="admin-program-stats__grid">
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $section->students_count }}</span>
            <span class="admin-program-stat__label">مسجلون</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $section->max_capacity ?: '—' }}</span>
            <span class="admin-program-stat__label">السعة</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $section->availableSeats() ?? '—' }}</span>
            <span class="admin-program-stat__label">مقاعد متاحة</span>
        </div>
        <div class="admin-program-stat">
            <span class="admin-program-stat__value">{{ $section->schedule ? '1' : '0' }}</span>
            <span class="admin-program-stat__label">موعد جدول</span>
        </div>
    </div>
    <div class="admin-program-stats__actions">
        <a href="{{ route('admin.sections.edit', $section) }}" class="admin-btn-primary admin-btn-primary--sm">تعديل الشعبة</a>
        @if ($section->batch)
            <a href="{{ route('admin.students.create', ['batch' => $section->batch_id, 'section' => $section->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">+ طالب للشعبة</a>
            <a href="{{ route('admin.batches.show', ['batch' => $section->batch, 'tab' => 'sections']) }}" class="admin-btn-secondary admin-btn-secondary--sm">الدفعة</a>
        @endif
        @if ($section->course)
            <a href="{{ route('admin.academic-courses.show', $section->course) }}" class="admin-btn-secondary admin-btn-secondary--sm">المقرر</a>
        @endif
        <a href="{{ route('admin.sections') }}" class="admin-btn-secondary admin-btn-secondary--sm">القائمة</a>
    </div>
</section>

<section class="admin-crud-card admin-view-card admin-section-view-card">
    <header class="admin-batch-view-hero">
        @include('partials.admin.section-view-header', ['section' => $section])
    </header>

    <div class="admin-view-tabs" role="tablist" aria-label="أقسام الشعبة">
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'details']) wire:click="setTab('details')">التفاصيل</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'students']) wire:click="setTab('students')">الطلاب ({{ $section->students_count }})</button>
        <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'schedule']) wire:click="setTab('schedule')">الجدول الدراسي</button>
        @canAdmin('attendance.view')
            <button type="button" @class(['admin-view-tab', 'is-active' => $activeTab === 'attendance']) wire:click="setTab('attendance')">الحضور ({{ $this->attendanceSessions->count() }})</button>
        @endcanAdmin
    </div>

    @if ($activeTab === 'details')
        <div class="admin-view-panel is-active">
            @include('partials.admin.section-detail-sections', ['section' => $section])

            <section class="admin-course-block admin-course-block--system" style="margin-top:1rem;">
                <h2 class="admin-course-block__title">معلومات النظام</h2>
                <div class="admin-system-grid">
                    <div class="admin-system-item">
                        <span class="admin-system-item__label">أُضيف بواسطة</span>
                        <span class="admin-system-item__value">{{ $section->added_by ?: '—' }}</span>
                    </div>
                    <div class="admin-system-item">
                        <span class="admin-system-item__label">تاريخ الإضافة</span>
                        <span class="admin-system-item__value">
                            {{ $section->created_at?->format('Y-m-d H:i') ?? '—' }}
                            @if ($section->created_at)
                                <span class="admin-system-item__ago">{{ $section->created_at->diffForHumans() }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="admin-system-item">
                        <span class="admin-system-item__label">آخر تحديث</span>
                        <span class="admin-system-item__value">{{ $section->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                    </div>
                </div>
            </section>
        </div>
    @elseif ($activeTab === 'students')
        <div class="admin-view-panel is-active">
            <div class="admin-batch-students-summary">
                <p>
                    يعرض الجدول الطلاب المسجّلين في هذه الشعبة
                    — العدد: <strong>{{ $section->students_count }}</strong>
                    @if ($section->max_capacity)
                        من أصل <strong>{{ $section->max_capacity }}</strong>.
                    @endif
                    @if ($section->batch)
                        <span class="admin-batch-students-summary__meta">(دفعة: {{ $section->batch->code }})</span>
                    @endif
                </p>
            </div>

            <div class="admin-table-toolbar">
                    <div class="admin-field" style="min-width:220px;margin:0;">
                        <input type="search" class="admin-control" wire:model.live.debounce.300ms="studentSearch" placeholder="بحث بالاسم، الرقم الأكاديمي، الهوية...">
                    </div>
                    <label class="admin-table-toolbar__label">
                        عدد الصفوف
                        <select class="admin-control admin-control--inline" wire:model.live="studentsPerPage">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </label>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الرقم الأكاديمي</th>
                                <th>الاسم</th>
                                <th>رقم الهوية</th>
                                <th>الجوال</th>
                                <th>الحالة</th>
                                <th><span class="visually-hidden">إجراءات</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->paginatedStudents as $index => $student)
                                <tr wire:key="section-student-{{ $student->id }}">
                                    <td>{{ $this->paginatedStudents->firstItem() + $index }}</td>
                                    <td><code class="admin-code">{{ $student->academic_id ?? '—' }}</code></td>
                                    <td>
                                        <a href="{{ route('admin.students.show', $student) }}" class="dash-inline-link">{{ $student->name_ar }}</a>
                                    </td>
                                    <td dir="ltr">{{ $student->national_id ?? '—' }}</td>
                                    <td dir="ltr">{{ $student->mobile ?? '—' }}</td>
                                    <td>
                                        <span @class([
                                            'admin-badge',
                                            'admin-badge--success' => $student->academic_status === 'studying',
                                            'admin-badge--warn' => $student->academic_status === 'pending',
                                            'admin-badge--danger' => in_array($student->academic_status, ['withdrawn', 'deferred'], true),
                                        ])>{{ $student->study_status ?: AcademicStudentOptions::academicStatusLabel($student->academic_status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.students.show', $student) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" style="text-align:center;padding:1.5rem">لا يوجد طلاب مسجّلون في هذه الشعبة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $this->paginatedStudents->links() }}
                <div class="admin-filter-actions" style="margin-top:1rem;">
                    <a href="{{ route('admin.students.create', ['batch' => $section->batch_id, 'section' => $section->id]) }}" class="admin-btn-primary admin-btn-primary--sm">+ طالب للشعبة</a>
                    @if ($section->batch)
                        <a href="{{ route('admin.students', ['batch' => $section->batch_id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">كل طلاب الدفعة</a>
                    @endif
                </div>
        </div>
    @elseif ($activeTab === 'attendance' && auth()->user()?->canAdmin('attendance.view'))
        <div class="admin-view-panel is-active">
            @include('partials.admin.section-tab-attendance', [
                'section' => $section,
                'attendanceSectionSummary' => $this->attendanceSectionSummary,
            ])
        </div>
    @else
        <div class="admin-view-panel is-active">
            @include('partials.admin.section-tab-schedule', [
                'section' => $section,
                'schedule' => $section->schedule,
            ])
        </div>
    @endif
</section>

@include('partials.admin.view-hero-styles')

@push('styles')
<style>
    .admin-program-stats { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .admin-program-stats__grid { display:flex; flex-wrap:wrap; gap:0.75rem; }
    .admin-program-stat { min-width:5.5rem; padding:0.65rem 1rem; border-radius:var(--radius-md); background:var(--sa-mist); border:1px solid var(--sa-border); text-align:center; }
    .admin-program-stat__value { display:block; font-size:1rem; font-weight:800; color:var(--sa-green-dark); }
    .admin-program-stat__label { font-size:0.75rem; color:var(--sa-muted); }
    .admin-program-stats__actions { display:flex; flex-wrap:wrap; gap:0.5rem; }
    .admin-table-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.75rem; margin-bottom:0.75rem; }
    .admin-table-toolbar__label { display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; color:var(--sa-muted); }
    .admin-batch-students-summary { margin-bottom:0.75rem; padding:0.75rem 1rem; background:var(--sa-green-soft); border-radius:var(--radius-md); font-size:0.88rem; color:var(--sa-ink); }
    .admin-badge--warn { background:#fff7ed; color:#c2410c; }
    .admin-section-view-card .admin-view-panel { padding: 1.25rem 1.5rem; }
</style>
@endpush

@include('partials.admin.shell-end')
