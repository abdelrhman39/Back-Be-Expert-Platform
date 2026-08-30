<?php

use App\Models\AcademicSection;
use App\Models\AttendanceSession;
use App\Services\AcademicSessionService;
use App\Support\AttendanceOptions;
use App\Support\MeetingSettings;
use App\Support\TeamsSettings;
use App\Support\ZoomSettings;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الحصص الدراسية | لوحة التحكم')]
class extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $state = '';

    #[Url]
    public string $sectionId = '';

    public bool $showForm = false;

    public ?int $editingSessionId = null;

    public string $formSectionId = '';

    public string $formTitle = '';

    public string $formSessionNumber = '';

    public string $formDescription = '';

    public string $formSessionDate = '';

    public string $formTimeStart = '';

    public string $formTimeEnd = '';

    public string $formStatus = 'scheduled';

    public string $formMeetingUrl = '';

    public string $formNotes = '';

    public bool $formPublished = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('sections.view'), 403);
    }

    public function openCreateForm(): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $this->resetForm();
        $this->showForm = true;

        if ($this->sectionId !== '') {
            $this->formSectionId = $this->sectionId;
            $this->applySectionDefaults();
        }
    }

    public function editSession(int $sessionId): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = AttendanceSession::query()->findOrFail($sessionId);

        $this->editingSessionId = $session->id;
        $this->formSectionId = (string) $session->section_id;
        $this->formTitle = (string) ($session->title ?? '');
        $this->formSessionNumber = $session->session_number !== null ? (string) $session->session_number : '';
        $this->formDescription = (string) ($session->description ?? '');
        $this->formSessionDate = $session->session_date?->format('Y-m-d') ?? '';
        $this->formTimeStart = $session->time_start ? substr((string) $session->time_start, 0, 5) : '';
        $this->formTimeEnd = $session->time_end ? substr((string) $session->time_end, 0, 5) : '';
        $this->formStatus = $session->status ?: 'scheduled';
        $this->formMeetingUrl = (string) ($session->meeting_url ?? '');
        $this->formNotes = (string) ($session->notes ?? '');
        $this->formPublished = (bool) $session->published_at;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function updatedFormSectionId(): void
    {
        if ($this->editingSessionId) {
            return;
        }

        $this->applySectionDefaults();
    }

    public function saveSession(AcademicSessionService $sessions): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $this->validate([
            'formSectionId' => ['required', 'exists:academic_sections,id'],
            'formTitle' => ['nullable', 'string', 'max:255'],
            'formSessionNumber' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'formDescription' => ['nullable', 'string', 'max:5000'],
            'formSessionDate' => ['required', 'date'],
            'formTimeStart' => ['nullable', 'date_format:H:i'],
            'formTimeEnd' => ['nullable', 'date_format:H:i'],
            'formStatus' => ['required', 'in:scheduled,completed,cancelled'],
            'formMeetingUrl' => ['nullable', 'url', 'max:500'],
            'formNotes' => ['nullable', 'string', 'max:2000'],
            'formPublished' => ['boolean'],
        ], [], [
            'formSectionId' => 'الشعبة',
            'formTitle' => 'عنوان الحصة',
            'formSessionNumber' => 'رقم الحصة',
            'formDescription' => 'الوصف',
            'formSessionDate' => 'تاريخ الحصة',
            'formTimeStart' => 'وقت البداية',
            'formTimeEnd' => 'وقت النهاية',
            'formStatus' => 'الحالة',
            'formMeetingUrl' => 'رابط الاجتماع',
            'formNotes' => 'ملاحظات',
        ]);

        $payload = [
            'title' => $this->formTitle,
            'session_number' => $this->formSessionNumber !== '' ? (int) $this->formSessionNumber : null,
            'description' => $this->formDescription,
            'session_date' => $this->formSessionDate,
            'time_start' => $this->formTimeStart !== '' ? $this->formTimeStart : null,
            'time_end' => $this->formTimeEnd !== '' ? $this->formTimeEnd : null,
            'meeting_url' => $this->formMeetingUrl,
            'status' => $this->formStatus,
            'notes' => $this->formNotes,
            'published' => $this->formPublished,
        ];

        try {
            if ($this->editingSessionId) {
                $session = AttendanceSession::query()->findOrFail($this->editingSessionId);
                $sessions->updateSession($session, [
                    ...$payload,
                    'section_id' => (int) $this->formSectionId,
                ]);
                session()->flash('admin_message', 'تم تحديث الحصة بنجاح.');
            } else {
                $section = AcademicSection::query()->findOrFail((int) $this->formSectionId);
                $sessions->createForSection($section, $payload);
                session()->flash('admin_message', 'تم إنشاء الحصة بنجاح. ستظهر للطلاب بعد النشر.');
            }
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?: 'تعذّر حفظ الحصة.';
            $this->addError('formSessionDate', $message);

            return;
        }

        $this->resetForm();
        unset($this->sessions, $this->stats);
    }

    public function deleteSession(int $sessionId, AcademicSessionService $sessions): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = AttendanceSession::query()->findOrFail($sessionId);
        $sessions->deleteSession($session);

        if ($this->editingSessionId === $sessionId) {
            $this->resetForm();
        }

        unset($this->sessions, $this->stats);
        session()->flash('admin_message', 'تم حذف الحصة.');
    }

    public function togglePublished(int $sessionId): void
    {
        abort_unless(auth()->user()?->canAdmin('attendance.manage'), 403);

        $session = AttendanceSession::query()->findOrFail($sessionId);
        $session->update([
            'published_at' => $session->published_at ? null : now(),
        ]);

        unset($this->sessions, $this->stats);
    }

    protected function applySectionDefaults(): void
    {
        if ($this->formSectionId === '') {
            return;
        }

        $section = AcademicSection::query()->with('schedule')->find((int) $this->formSectionId);

        if (! $section) {
            return;
        }

        if ($this->formTimeStart === '' && $section->schedule?->time_start) {
            $this->formTimeStart = substr((string) $section->schedule->time_start, 0, 5);
        }

        if ($this->formTimeEnd === '' && $section->schedule?->time_end) {
            $this->formTimeEnd = substr((string) $section->schedule->time_end, 0, 5);
        }

        if ($this->formSessionNumber === '') {
            $this->formSessionNumber = (string) app(AcademicSessionService::class)->nextSessionNumber($section->id);
        }
    }

    protected function resetForm(): void
    {
        $this->showForm = false;
        $this->editingSessionId = null;
        $this->formSectionId = '';
        $this->formTitle = '';
        $this->formSessionNumber = '';
        $this->formDescription = '';
        $this->formSessionDate = '';
        $this->formTimeStart = '';
        $this->formTimeEnd = '';
        $this->formStatus = 'scheduled';
        $this->formMeetingUrl = '';
        $this->formNotes = '';
        $this->formPublished = true;
        $this->resetValidation();
    }

    #[Computed]
    public function sessions()
    {
        $service = app(AcademicSessionService::class);

        return AttendanceSession::query()
            ->with(['section.course', 'zoomMeeting.host', 'zoxAgentMeeting'])
            ->withCount('materials')
            ->when($this->sectionId, fn ($q) => $q->where('section_id', (int) $this->sectionId))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('section', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%'));
            }))
            ->orderByDesc('session_date')
            ->orderByDesc('time_start')
            ->limit(100)
            ->get()
            ->map(function (AttendanceSession $session) use ($service) {
                $timing = $service->resolveTiming($session);
                $session->computed_state = $timing['state'];
                $session->join_url = $service->joinUrl($session);
                $session->meeting_provider_label = match (true) {
                    (bool) $session->zoxAgentMeeting => 'ZoxAgent',
                    (bool) $session->zoomMeeting => 'Zoom',
                    filled($session->teams_join_web_url) => 'Teams',
                    filled($session->meeting_url) => 'يدوي',
                    default => '—',
                };

                return $session;
            })
            ->when($this->state, fn ($c) => $c->filter(fn ($s) => $s->computed_state === $this->state))
            ->values();
    }

    #[Computed]
    public function stats(): array
    {
        $service = app(AcademicSessionService::class);
        $counts = ['live' => 0, 'upcoming' => 0, 'completed' => 0, 'total' => 0];

        foreach (AttendanceSession::query()->get() as $session) {
            $counts['total']++;
            $state = $service->resolveTiming($session)['state'];
            if (isset($counts[$state])) {
                $counts[$state]++;
            }
        }

        return $counts;
    }

    #[Computed]
    public function sections()
    {
        return AcademicSection::query()
            ->with(['course:id,name_ar', 'batch:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'course_id', 'batch_id']);
    }

    #[Computed]
    public function meetingProvider(): array
    {
        $provider = MeetingSettings::defaultProvider();
        $label = MeetingSettings::providers()[$provider] ?? $provider;

        $ready = match ($provider) {
            'zoom' => ZoomSettings::enabled(),
            'teams' => TeamsSettings::isEnabled() && TeamsSettings::isConfigured(),
            default => true,
        };

        $hint = match ($provider) {
            'zoom' => $ready
                ? 'عند إنشاء الحصة يُنشأ اجتماع Zoom تلقائياً ويُحفظ رابط الانضمام للطالب.'
                : 'Zoom هو المزوّد الافتراضي لكنه غير مفعّل/غير مكتمل الإعداد — فعّله من إعدادات Zoom أو أدخل رابطاً يدوياً.',
            'teams' => $ready
                ? 'عند إنشاء الحصة يُنشأ اجتماع Microsoft Teams تلقائياً ويُحفظ رابط الانضمام للطالب.'
                : 'Teams هو المزوّد الافتراضي لكنه غير مفعّل/غير مكتمل الإعداد — أكمل الإعداد أو أدخل رابطاً يدوياً.',
            default => 'المزوّد الحالي «رابط يدوي» — الصق رابط الاجتماع هنا (Zoom أو Teams أو أي رابط آخر).',
        };

        $settingsRoute = match ($provider) {
            'zoom' => route('admin.zoom-settings'),
            'teams' => route('admin.teams-settings'),
            default => route('admin.zoom-settings'),
        };

        return [
            'key' => $provider,
            'label' => $label,
            'ready' => $ready,
            'auto' => in_array($provider, ['zoom', 'teams'], true),
            'hint' => $hint,
            'settings_route' => $settingsRoute,
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.sessions'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الحصص الدراسية'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>الحصص الدراسية
            <span class="admin-crud-card__meta">— {{ $this->stats['live'] }} جارية · {{ $this->stats['upcoming'] }} قادمة</span>
        </h2>
        @canAdmin('attendance.manage')
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="openCreateForm">+ إضافة حصة</button>
        @endcanAdmin
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="عنوان، شعبة...">
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="state">
                <option value="">الكل</option>
                <option value="live">جارية الآن</option>
                <option value="upcoming">قادمة</option>
                <option value="completed">منتهية</option>
            </select>
        </div>
        <div class="admin-field">
            <label>الشعبة</label>
            <select class="admin-control" wire:model.live="sectionId">
                <option value="">كل الشعب</option>
                @foreach ($this->sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }} ({{ $section->code }})</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

@if ($showForm && auth()->user()?->canAdmin('attendance.manage'))
    <section class="admin-crud-card">
        <div class="admin-crud-card__head admin-crud-card__head--row">
            <h2>{{ $editingSessionId ? 'تعديل الحصة' : 'إضافة حصة جديدة' }}</h2>
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelForm">إلغاء</button>
        </div>

        <form wire:submit="saveSession" class="admin-filter-grid" style="padding:1rem 1.1rem 1.25rem;grid-template-columns:repeat(2,minmax(0,1fr));">
            <div class="admin-field">
                <label for="formSectionId">الشعبة *</label>
                <select id="formSectionId" class="admin-control" wire:model.live="formSectionId">
                    <option value="">— اختر الشعبة —</option>
                    @foreach ($this->sections as $section)
                        <option value="{{ $section->id }}">
                            {{ $section->name }} ({{ $section->code }})
                            @if ($section->course) — {{ $section->course->name_ar }} @endif
                        </option>
                    @endforeach
                </select>
                @error('formSectionId') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field">
                <label for="formSessionDate">تاريخ الحصة *</label>
                <input id="formSessionDate" type="date" class="admin-control" wire:model="formSessionDate">
                @error('formSessionDate') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field">
                <label for="formTitle">عنوان الحصة</label>
                <input id="formTitle" type="text" class="admin-control" wire:model="formTitle" placeholder="مثال: مقدمة في الذكاء الاصطناعي">
                @error('formTitle') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field">
                <label for="formSessionNumber">رقم الحصة</label>
                <input id="formSessionNumber" type="number" min="1" max="9999" class="admin-control" wire:model="formSessionNumber">
                @error('formSessionNumber') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field">
                <label for="formTimeStart">وقت البداية</label>
                <input id="formTimeStart" type="time" class="admin-control" wire:model="formTimeStart">
                @error('formTimeStart') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field">
                <label for="formTimeEnd">وقت النهاية</label>
                <input id="formTimeEnd" type="time" class="admin-control" wire:model="formTimeEnd">
                @error('formTimeEnd') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field">
                <label for="formStatus">حالة الحصة</label>
                <select id="formStatus" class="admin-control" wire:model="formStatus">
                    @foreach (AttendanceOptions::sessionStatuses() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="admin-field" style="grid-column:1/-1;">
                @php
                    $meeting = $this->meetingProvider;
                @endphp
                <div class="session-meeting-box">
                    <div class="session-meeting-box__head">
                        <strong>اجتماع الحصة</strong>
                        <span @class([
                            'admin-badge',
                            'admin-badge--success' => $meeting['key'] === 'zoom' && $meeting['ready'],
                            'admin-badge--info' => $meeting['key'] === 'teams' && $meeting['ready'],
                            'admin-badge--warn' => $meeting['auto'] && ! $meeting['ready'],
                            'admin-badge--muted' => $meeting['key'] === 'manual',
                        ])>{{ $meeting['label'] }}{{ $meeting['auto'] ? ($meeting['ready'] ? ' · تلقائي' : ' · غير جاهز') : '' }}</span>
                    </div>
                    <p class="session-meeting-box__hint">{{ $meeting['hint'] }}</p>
                    <p class="session-meeting-box__meta">
                        يُحدَّد المزوّد النشط من
                        <a href="{{ $meeting['settings_route'] }}">إعدادات الاجتماعات</a>
                        (Zoom / Teams / رابط يدوي).
                    </p>

                    @if ($meeting['key'] === 'manual' || ! $meeting['ready'])
                        <div class="admin-field" style="margin-top:.65rem;">
                            <label for="formMeetingUrl">رابط الاجتماع {{ $meeting['key'] === 'manual' ? '*' : '(احتياطي)' }}</label>
                            <input id="formMeetingUrl" type="url" class="admin-control" wire:model="formMeetingUrl" dir="ltr" placeholder="https://...">
                            <small style="color:#64748b;">يظهر للطالب كزر انضمام إذا لم يُنشأ اجتماع تلقائي.</small>
                            @error('formMeetingUrl') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="admin-field" style="margin-top:.65rem;">
                            <label for="formMeetingUrl">رابط يدوي بديل (اختياري)</label>
                            <input id="formMeetingUrl" type="url" class="admin-control" wire:model="formMeetingUrl" dir="ltr" placeholder="اتركه فارغاً للاعتماد على {{ $meeting['label'] }}">
                            <small style="color:#64748b;">لا حاجة لملئه — سيُنشأ رابط {{ $meeting['label'] }} تلقائياً عند الحفظ. استخدمه فقط إذا أردت رابطاً بديلاً.</small>
                            @error('formMeetingUrl') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>

            <div class="admin-field" style="grid-column:1/-1;">
                <label for="formDescription">وصف الحصة / المحتوى</label>
                <textarea id="formDescription" class="admin-control" rows="3" wire:model="formDescription" placeholder="يظهر للطالب داخل تفاصيل الحصة ومحتوى الدبلوم"></textarea>
                @error('formDescription') <span class="admin-field-error">{{ $message }}</span> @enderror
            </div>

            <div class="admin-field" style="grid-column:1/-1;">
                <label for="formNotes">ملاحظات داخلية</label>
                <textarea id="formNotes" class="admin-control" rows="2" wire:model="formNotes" placeholder="ملاحظات للإدارة فقط"></textarea>
            </div>

            <div class="admin-field" style="display:flex;align-items:center;gap:.5rem;">
                <label style="display:flex;align-items:center;gap:.5rem;font-weight:700;margin:0;">
                    <input type="checkbox" wire:model="formPublished"> منشورة للطلاب (تظهر في محتوى الدبلوم)
                </label>
            </div>

            <div class="admin-filter-actions" style="grid-column:1/-1;">
                <button type="submit" class="admin-btn-primary" wire:loading.attr="disabled" wire:target="saveSession">
                    <span wire:loading.remove wire:target="saveSession">{{ $editingSessionId ? 'حفظ التعديلات' : 'إنشاء الحصة' }}</span>
                    <span wire:loading wire:target="saveSession">جاري الحفظ...</span>
                </button>
            </div>
        </form>
    </section>
@endif

<section class="admin-crud-card">
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الحصة</th>
                    <th>الشعبة</th>
                    <th>الحالة</th>
                    <th>النشر</th>
                    <th>المزوّد</th>
                    <th>المواد</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->sessions as $session)
                    @php
                        $stateClass = match ($session->computed_state) {
                            'live' => 'admin-badge--danger',
                            'upcoming' => 'admin-badge--success',
                            'completed' => 'admin-badge--muted',
                            default => 'admin-badge--info',
                        };
                        $stateLabel = match ($session->computed_state) {
                            'live' => 'جارية',
                            'upcoming' => 'قادمة',
                            'completed' => 'منتهية',
                            'cancelled' => 'ملغاة',
                            default => AttendanceOptions::sessionStatusLabel($session->status),
                        };
                        $zoom = $session->zoomMeeting;
                        $lastSync = $zoom?->attendance_synced_at ?? $zoom?->last_synced_at ?? $session->teams_attendance_synced_at;
                    @endphp
                    <tr wire:key="admin-session-{{ $session->id }}">
                        <td dir="ltr">{{ $session->session_date->format('Y-m-d') }}</td>
                        <td>
                            <strong>
                                @if ($session->session_number)
                                    <span class="admin-crud-card__meta">#{{ $session->session_number }}</span>
                                @endif
                                {{ $session->displayTitle() }}
                            </strong>
                            @if ($session->time_start)
                                <div class="admin-crud-card__meta" dir="ltr">{{ substr((string) $session->time_start, 0, 5) }}@if($session->time_end) – {{ substr((string) $session->time_end, 0, 5) }}@endif</div>
                            @endif
                            @if ($session->description)
                                <div class="admin-crud-card__meta">{{ \Illuminate\Support\Str::limit(strip_tags($session->description), 70) }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($session->section)
                                <a href="{{ route('admin.sections.show', $session->section) }}" class="dash-inline-link">{{ $session->section->name }}</a>
                                @if ($session->section->course)
                                    <div class="admin-crud-card__meta">{{ $session->section->course->name_ar }}</div>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td><span @class(['admin-badge', $stateClass])>{{ $stateLabel }}</span></td>
                        <td>
                            <span style="display:inline-block;padding:.2rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800;{{ $session->published_at ? 'background:#dcfce7;color:#166534;' : 'background:#f1f5f9;color:#64748b;' }}">
                                {{ $session->published_at ? 'منشورة' : 'مسودة' }}
                            </span>
                        </td>
                        <td>
                            <span @class([
                                'admin-badge',
                                'admin-badge--success' => $session->meeting_provider_label === 'Zoom',
                                'admin-badge--info' => $session->meeting_provider_label === 'Teams',
                                'admin-badge--muted' => $session->meeting_provider_label === '—',
                            ])>{{ $session->meeting_provider_label }}</span>
                            @if ($zoom?->host)
                                <div class="admin-crud-card__meta">{{ $zoom->host->email ?: $zoom->host->zoom_user_id }}</div>
                            @endif
                            @if ($lastSync)
                                <div class="admin-crud-card__meta">آخر مزامنة: {{ $lastSync->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td>{{ $session->materials_count }}</td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
                                @canAdmin('attendance.manage')
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="editSession({{ $session->id }})">تعديل</button>
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="togglePublished({{ $session->id }})">
                                        {{ $session->published_at ? 'إخفاء' : 'نشر' }}
                                    </button>
                                @endcanAdmin
                                @if ($session->section)
                                    <a href="{{ route('admin.sections.show', ['section' => $session->section, 'tab' => 'attendance', 'attendanceSessionId' => $session->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">حضور ومواد</a>
                                @endif
                                @canAdmin('attendance.manage')
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" style="color:#b91c1c;border-color:#fecaca;" wire:click="deleteSession({{ $session->id }})" wire:confirm="حذف هذه الحصة نهائياً مع سجلات الحضور المرتبطة؟">حذف</button>
                                @endcanAdmin
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:1.5rem">لا توجد حصص. أضف حصة جديدة أو ولّدها من الجدول الدراسي.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-field-hint" style="margin-top:0.75rem;">
        لتوليد حصص قادمة من الجداول: <code dir="ltr">php artisan sessions:generate-upcoming</code>
        — وللمواد والاجتماعات والحضور استخدم زر «حضور ومواد».
    </div>
</section>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .admin-badge--muted { background: #f1f5f9; color: #64748b; }
    .admin-badge--danger { background: #fef2f2; color: #b91c1c; }
    .admin-badge--warn { background: #fff7ed; color: #c2410c; }
    .session-meeting-box{padding:1rem 1.05rem;border:1px solid #dbe7e0;border-radius:14px;background:linear-gradient(180deg,#f7fbf8 0%,#fff 70%)}
    .session-meeting-box__head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:.45rem}
    .session-meeting-box__head strong{font-size:.92rem;color:#0f172a}
    .session-meeting-box__hint{margin:0 0 .35rem;color:#334155;font-size:.82rem;line-height:1.7;font-weight:600}
    .session-meeting-box__meta{margin:0;color:#64748b;font-size:.74rem;line-height:1.6}
    .session-meeting-box__meta a{color:#166534;font-weight:800;text-decoration:none}
    .session-meeting-box__meta a:hover{text-decoration:underline}
</style>
@endpush
