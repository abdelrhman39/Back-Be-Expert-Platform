<?php

use App\Models\AcademicStaff;
use App\Models\PlatformSetting;
use App\Models\ZoomHost;
use App\Services\Zoom\ZoomApiException;
use App\Services\Zoom\ZoomHostSyncService;
use App\Support\MeetingSettings;
use App\Support\RecordingSettings;
use App\Support\ZoomSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'إعدادات Zoom',
    'adminPageDesc' => 'ربط المنصة مع Zoom للمحاضرات والحضور والتسجيل',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'Zoom'],
    ],
])]
#[Title('Zoom | لوحة التحكم')]
class extends Component
{
    public bool $zoomEnabled = false;

    public string $accountId = '';

    public string $clientId = '';

    public string $clientSecret = '';

    public string $webhookSecret = '';

    public string $defaultProvider = 'zoom';

    public string $hostStrategy = 'central';

    public string $defaultHost = '';

    public bool $registrationRequired = true;

    public bool $waitingRoom = true;

    public bool $hostVideo = true;

    public bool $participantVideo = false;

    public bool $muteUponEntry = true;

    public bool $joinBeforeHost = false;

    public bool $allowMultipleDevices = false;

    public string $audioMode = 'both';

    public int $joinWindowMinutes = 30;

    public bool $autoAttendance = true;

    public int $syncInterval = 15;

    public int $lateMinutes = 10;

    public int $minimumAttendancePercent = 0;

    public int $minimumAttendanceMinutes = 1;

    public string $recordingPolicy = 'automatic';

    public string $recordingDestination = 'zoom';

    public string $s3Disk = 's3';

    public string $googleDisk = 'google';

    public string $recordingPublishMode = 'manual';

    public int $recordingAutoPublishHours = 24;

    public int $recordingRetentionDays = 365;

    public bool $recordingAllowDownload = false;

    public string $recordingAccessPolicy = 'enrolled_only';

    public bool $hasStoredClientSecret = false;

    public bool $hasStoredWebhookSecret = false;

    public ?string $savedMessage = null;

    public ?string $actionMessage = null;

    public ?string $actionError = null;

    /** @var array<string, mixed>|null */
    public ?array $connectionResult = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoom-settings.manage'), 403);

        $this->zoomEnabled = filter_var(PlatformSetting::get('zoom_enabled') ?? config('zoom.enabled'), FILTER_VALIDATE_BOOL);
        $this->accountId = ZoomSettings::accountId() ?? '';
        $this->clientId = ZoomSettings::clientId() ?? '';
        $this->defaultHost = ZoomSettings::defaultHost() ?? '';
        $this->hostStrategy = ZoomSettings::hostStrategy();
        $this->defaultProvider = MeetingSettings::defaultProvider();
        $this->registrationRequired = ZoomSettings::registrationRequired();
        $this->waitingRoom = ZoomSettings::waitingRoom();
        $this->hostVideo = ZoomSettings::hostVideo();
        $this->participantVideo = ZoomSettings::participantVideo();
        $this->muteUponEntry = ZoomSettings::muteUponEntry();
        $this->joinBeforeHost = ZoomSettings::joinBeforeHost();
        $this->allowMultipleDevices = ZoomSettings::allowMultipleDevices();
        $this->audioMode = ZoomSettings::audioMode();
        $this->joinWindowMinutes = ZoomSettings::joinWindowMinutes();
        $this->autoAttendance = ZoomSettings::autoAttendance();
        $this->syncInterval = ZoomSettings::syncInterval();
        $this->lateMinutes = ZoomSettings::lateMinutes();
        $this->minimumAttendancePercent = ZoomSettings::minimumAttendancePercent();
        $this->minimumAttendanceMinutes = ZoomSettings::minimumAttendanceMinutes();
        $this->recordingPolicy = ZoomSettings::recordingPolicy();
        $this->recordingDestination = ZoomSettings::recordingDestination();
        $this->s3Disk = ZoomSettings::s3Disk();
        $this->googleDisk = ZoomSettings::googleDisk();
        $this->recordingPublishMode = RecordingSettings::publishMode();
        $this->recordingAutoPublishHours = RecordingSettings::autoPublishHours();
        $this->recordingRetentionDays = RecordingSettings::retentionDays();
        $this->recordingAllowDownload = RecordingSettings::allowDownload();
        $this->recordingAccessPolicy = RecordingSettings::accessPolicy();
        $this->hasStoredClientSecret = filled(PlatformSetting::get('zoom_client_secret')) || filled(config('zoom.client_secret'));
        $this->hasStoredWebhookSecret = filled(PlatformSetting::get('zoom_webhook_secret')) || filled(config('zoom.webhook_secret'));
        $this->clientSecret = '';
        $this->webhookSecret = '';
    }

    #[Computed]
    public function hosts()
    {
        return ZoomHost::query()
            ->with('academicStaff')
            ->orderByDesc('is_active')
            ->orderBy('priority')
            ->orderBy('email')
            ->get();
    }

    #[Computed]
    public function staffOptions()
    {
        return AcademicStaff::query()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en']);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoom-settings.manage'), 403);

        $this->validate([
            'accountId' => ['required_if:zoomEnabled,true', 'nullable', 'string', 'max:120'],
            'clientId' => ['required_if:zoomEnabled,true', 'nullable', 'string', 'max:120'],
            'clientSecret' => ['nullable', 'string', 'max:255'],
            'webhookSecret' => ['nullable', 'string', 'max:255'],
            'defaultProvider' => ['required', 'in:zoom,teams,zoxagent,manual'],
            'hostStrategy' => ['required', 'in:central,instructor,pool'],
            'defaultHost' => ['nullable', 'string', 'max:190'],
            'syncInterval' => ['required', 'integer', 'min:5', 'max:120'],
            'lateMinutes' => ['required', 'integer', 'min:0', 'max:180'],
            'minimumAttendancePercent' => ['required', 'integer', 'min:0', 'max:100'],
            'minimumAttendanceMinutes' => ['required', 'integer', 'min:0', 'max:600'],
            'recordingPolicy' => ['required', 'in:automatic,manual,disabled'],
            'recordingDestination' => ['required', 'in:zoom,s3,google'],
            'audioMode' => ['required', 'in:both,voip,telephony'],
            'joinWindowMinutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'recordingPublishMode' => ['required', 'in:manual,auto_delayed'],
            'recordingAutoPublishHours' => ['required', 'integer', 'min:1', 'max:168'],
            'recordingRetentionDays' => ['required', 'integer', 'min:30', 'max:3650'],
            'recordingAccessPolicy' => ['required', 'in:enrolled_only,attended_only'],
            's3Disk' => ['required_if:recordingDestination,s3', 'nullable', 'string', 'max:64'],
            'googleDisk' => ['required_if:recordingDestination,google', 'nullable', 'string', 'max:64'],
        ], [], [
            'accountId' => 'Account ID',
            'clientId' => 'Client ID',
            'clientSecret' => 'Client Secret',
            'webhookSecret' => 'Webhook Secret',
            'defaultProvider' => 'مزوّد المحاضرات',
            'hostStrategy' => 'استراتيجية المضيف',
            'defaultHost' => 'المضيف الافتراضي',
            'syncInterval' => 'فترة المزامنة',
            'lateMinutes' => 'حد التأخير',
            'minimumAttendancePercent' => 'الحد الأدنى للنسبة',
            'minimumAttendanceMinutes' => 'الحد الأدنى للدقائق',
            'recordingPolicy' => 'سياسة التسجيل',
            'recordingDestination' => 'وجهة التسجيل',
            's3Disk' => 'قرص S3',
            'googleDisk' => 'قرص Google',
        ]);

        if ($this->zoomEnabled && ! $this->hasStoredClientSecret && blank($this->clientSecret)) {
            $this->addError('clientSecret', 'Client Secret مطلوب عند تفعيل Zoom لأول مرة.');

            return;
        }

        $userId = auth()->id();

        ZoomSettings::set('enabled', $this->zoomEnabled ? '1' : '0', false, $userId);
        ZoomSettings::set('account_id', trim($this->accountId), false, $userId);
        ZoomSettings::set('client_id', trim($this->clientId), false, $userId);

        if (filled($this->clientSecret)) {
            ZoomSettings::set('client_secret', $this->clientSecret, true, $userId);
            $this->hasStoredClientSecret = true;
        }

        if (filled($this->webhookSecret)) {
            ZoomSettings::set('webhook_secret', $this->webhookSecret, true, $userId);
            $this->hasStoredWebhookSecret = true;
        }

        ZoomSettings::set('host_strategy', $this->hostStrategy, false, $userId);
        ZoomSettings::set('default_host', trim($this->defaultHost), false, $userId);
        ZoomSettings::set('registration_required', $this->registrationRequired ? '1' : '0', false, $userId);
        ZoomSettings::set('waiting_room', $this->waitingRoom ? '1' : '0', false, $userId);
        ZoomSettings::set('host_video', $this->hostVideo ? '1' : '0', false, $userId);
        ZoomSettings::set('participant_video', $this->participantVideo ? '1' : '0', false, $userId);
        ZoomSettings::set('mute_upon_entry', $this->muteUponEntry ? '1' : '0', false, $userId);
        ZoomSettings::set('join_before_host', $this->joinBeforeHost ? '1' : '0', false, $userId);
        ZoomSettings::set('allow_multiple_devices', $this->allowMultipleDevices ? '1' : '0', false, $userId);
        ZoomSettings::set('audio_mode', $this->audioMode, false, $userId);
        ZoomSettings::set('join_window_minutes', (string) $this->joinWindowMinutes, false, $userId);
        ZoomSettings::set('auto_attendance', $this->autoAttendance ? '1' : '0', false, $userId);
        ZoomSettings::set('sync_interval', (string) $this->syncInterval, false, $userId);
        ZoomSettings::set('late_minutes', (string) $this->lateMinutes, false, $userId);
        ZoomSettings::set('minimum_attendance_percent', (string) $this->minimumAttendancePercent, false, $userId);
        ZoomSettings::set('minimum_attendance_minutes', (string) $this->minimumAttendanceMinutes, false, $userId);
        ZoomSettings::set('recording_policy', $this->recordingPolicy, false, $userId);
        ZoomSettings::set('recording_destination', $this->recordingDestination, false, $userId);
        ZoomSettings::set('s3_disk', trim($this->s3Disk) ?: 's3', false, $userId);
        ZoomSettings::set('google_disk', trim($this->googleDisk) ?: 'google', false, $userId);
        RecordingSettings::setPublishMode($this->recordingPublishMode);
        RecordingSettings::setAutoPublishHours($this->recordingAutoPublishHours);
        RecordingSettings::setRetentionDays($this->recordingRetentionDays);
        RecordingSettings::setAllowDownload($this->recordingAllowDownload);
        RecordingSettings::setAccessPolicy($this->recordingAccessPolicy);

        MeetingSettings::setDefaultProvider($this->defaultProvider);

        $this->clientSecret = '';
        $this->webhookSecret = '';
        $this->actionError = null;
        $this->connectionResult = null;
        $this->savedMessage = 'تم حفظ إعدادات Zoom بنجاح. القيم المحفوظة هنا لها أولوية على .env.';
    }

    public function testConnection(ZoomHostSyncService $hosts): void
    {
        abort_unless(auth()->user()?->canAdmin('zoom-settings.manage'), 403);

        $this->actionMessage = null;
        $this->actionError = null;
        $this->connectionResult = null;

        try {
            $result = method_exists($hosts, 'testConnection')
                ? $hosts->testConnection()
                : $hosts->test();

            $this->connectionResult = is_array($result) ? $result : ['ok' => (bool) $result];
            $this->actionMessage = 'اتصال Zoom ناجح.';
        } catch (ZoomApiException $e) {
            $this->actionError = $e->getMessage();
        } catch (\Throwable $e) {
            $this->actionError = 'تعذّر اختبار الاتصال: '.$e->getMessage();
        }
    }

    public function syncHosts(ZoomHostSyncService $hosts): void
    {
        abort_unless(auth()->user()?->canAdmin('zoom-settings.manage'), 403);

        $this->actionMessage = null;
        $this->actionError = null;

        try {
            $count = method_exists($hosts, 'syncHosts')
                ? $hosts->syncHosts()
                : $hosts->sync();

            $label = is_numeric($count) ? (int) $count : null;
            $this->actionMessage = $label !== null
                ? "تمت مزامنة مستخدمي Zoom ({$label})."
                : 'تمت مزامنة مستخدمي Zoom.';
            unset($this->hosts);
        } catch (ZoomApiException $e) {
            $this->actionError = $e->getMessage();
        } catch (\Throwable $e) {
            $this->actionError = 'تعذّرت مزامنة المضيفين: '.$e->getMessage();
        }
    }

    public function updateHost(int $hostId, ?string $staffId = null, ?bool $active = null, ?int $priority = null, ?string $pool = null): void
    {
        abort_unless(auth()->user()?->canAdmin('zoom-settings.manage'), 403);

        $host = ZoomHost::query()->find($hostId);

        if (! $host) {
            return;
        }

        $payload = [];

        if ($staffId !== null) {
            $payload['academic_staff_id'] = $staffId !== '' ? (int) $staffId : null;
        }

        if ($active !== null) {
            $payload['is_active'] = $active;
        }

        if ($priority !== null) {
            $payload['priority'] = max(0, min(999, $priority));
        }

        if ($pool !== null) {
            $payload['pool'] = $pool !== '' ? $pool : null;
        }

        if ($payload !== []) {
            $host->update($payload);
            unset($this->hosts);
            $this->actionMessage = 'تم تحديث بيانات المضيف.';
            $this->actionError = null;
        }
    }

    public function webhookEndpoint(): string
    {
        return url('/webhooks/zoom');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.zoom-settings'),
    'shellActiveHeader' => 'settings',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.settings'), 'label' => 'إعدادات المنصة'],
        ['label' => 'Zoom'],
    ],
])

@php
    $zoomTabFields = [
        'overview' => ['defaultProvider', 'hostStrategy'],
        'connect' => ['accountId', 'clientId', 'clientSecret', 'webhookSecret', 'defaultHost'],
        'meeting' => ['audioMode', 'joinWindowMinutes'],
        'attendance' => ['syncInterval', 'lateMinutes', 'minimumAttendancePercent', 'minimumAttendanceMinutes'],
        'recording' => ['recordingPolicy', 'recordingDestination', 's3Disk', 'googleDisk', 'recordingPublishMode', 'recordingAutoPublishHours', 'recordingRetentionDays', 'recordingAccessPolicy'],
    ];
    $zoomTabErrors = collect($zoomTabFields)->map(fn ($fields) => collect($fields)->contains(fn ($field) => $errors->has($field)));
    $zoomInitialTab = $zoomTabErrors->filter()->keys()->first() ?? 'overview';
    $zoomConfigured = ZoomSettings::configured();
@endphp

<div class="zoom-settings" x-data="{ tab: @js($zoomInitialTab) }">

    <section class="zoom-hero">
        <div class="zoom-hero__main">
            <div class="zoom-hero__icon"><i class="fa-solid fa-video"></i></div>
            <div>
                <span class="zoom-hero__eyebrow">تكامل المحاضرات المباشرة</span>
                <h1>إعدادات Zoom</h1>
                <p>
                    تنشئ المنصة اجتماع Zoom لكل محاضرة، وتختار المضيف، وتصدر رابطاً فردياً آمناً للطالب،
                    ثم تسحب الحضور والتسجيل تلقائياً وتطبّق سياسات النشر والصلاحيات المحددة هنا.
                </p>
                <div class="zoom-hero__badges">
                    @if (ZoomSettings::enabled())
                        <span class="zoom-pill zoom-pill--ok"><i class="fa-solid fa-circle-check"></i> مفعّل وجاهز</span>
                    @elseif ($zoomEnabled)
                        <span class="zoom-pill zoom-pill--warn"><i class="fa-solid fa-triangle-exclamation"></i> مفعّل — ينقص إكمال البيانات</span>
                    @else
                        <span class="zoom-pill zoom-pill--off"><i class="fa-solid fa-circle-pause"></i> معطّل</span>
                    @endif
                    <span class="zoom-pill">
                        <i class="fa-solid fa-tower-broadcast"></i>
                        المزوّد الافتراضي: {{ MeetingSettings::providers()[$defaultProvider] ?? $defaultProvider }}
                    </span>
                    <span class="zoom-pill">
                        <i class="fa-solid fa-key"></i>
                        {{ $zoomConfigured ? 'بيانات الربط مكتملة' : 'بيانات الربط غير مكتملة' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="zoom-hero__side">
            <label class="zoom-switch">
                <input type="checkbox" wire:model.live="zoomEnabled">
                <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                <span class="zoom-switch__label">تفعيل التكامل</span>
            </label>
            <button type="button" class="zoom-hero__guide" @click="$dispatch('open-zoom-guide', { section: 'setup' })">
                <i class="fa-regular fa-circle-question"></i> شرح آلية العمل
            </button>
        </div>
    </section>

    @if ($savedMessage)
        <div class="admin-alert admin-alert--info is-visible" role="status">{{ $savedMessage }}</div>
    @endif
    @if ($actionMessage)
        <div class="admin-alert admin-alert--info is-visible" role="status">{{ $actionMessage }}</div>
    @endif
    @if ($actionError)
        <div class="admin-alert admin-alert--danger is-visible" role="alert">{{ $actionError }}</div>
    @endif

    <nav class="zoom-tabs" aria-label="أقسام إعدادات Zoom">
        <button type="button" :class="{ 'is-active': tab === 'overview' }" @click="tab = 'overview'">
            <i class="fa-solid fa-compass"></i> نظرة عامة
            @if ($zoomTabErrors['overview'])<span class="zoom-tabs__dot"></span>@endif
        </button>
        <button type="button" :class="{ 'is-active': tab === 'connect' }" @click="tab = 'connect'">
            <i class="fa-solid fa-plug"></i> الربط والاتصال
            @if ($zoomTabErrors['connect'])<span class="zoom-tabs__dot"></span>@endif
        </button>
        <button type="button" :class="{ 'is-active': tab === 'meeting' }" @click="tab = 'meeting'">
            <i class="fa-solid fa-video"></i> الاجتماع
            @if ($zoomTabErrors['meeting'])<span class="zoom-tabs__dot"></span>@endif
        </button>
        <button type="button" :class="{ 'is-active': tab === 'attendance' }" @click="tab = 'attendance'">
            <i class="fa-solid fa-user-check"></i> الحضور
            @if ($zoomTabErrors['attendance'])<span class="zoom-tabs__dot"></span>@endif
        </button>
        <button type="button" :class="{ 'is-active': tab === 'recording' }" @click="tab = 'recording'">
            <i class="fa-solid fa-record-vinyl"></i> التسجيل
            @if ($zoomTabErrors['recording'])<span class="zoom-tabs__dot"></span>@endif
        </button>
        <button type="button" :class="{ 'is-active': tab === 'hosts' }" @click="tab = 'hosts'">
            <i class="fa-solid fa-users-gear"></i> المضيفون
            <span class="zoom-tabs__count">{{ $this->hosts->count() }}</span>
        </button>
    </nav>

    <div x-show="tab === 'overview'" x-cloak>
        <section class="admin-crud-card zoom-overview">
            <div class="admin-crud-card__head admin-crud-card__head--row">
                <div>
                    <span class="zoom-overview__eyebrow">الوصف وآلية العمل</span>
                    <h2>ماذا يفعل تكامل Zoom داخل المنصة؟</h2>
                    <p class="admin-crud-card__meta">خمس مراحل تعمل تلقائياً بعد إكمال الربط، من إنشاء الاجتماع حتى نشر التسجيل.</p>
                </div>
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" @click="$dispatch('open-zoom-guide', { section: 'setup' })">
                    <i class="fa-regular fa-circle-question"></i> فتح الدليل التفصيلي
                </button>
            </div>

            <ol class="zoom-workflow" aria-label="خطوات عمل تكامل Zoom">
                <li>
                    <span>1</span>
                    <div><strong>الربط</strong><small>أدخل بيانات Server-to-Server OAuth واختبر الاتصال مرة واحدة.</small></div>
                </li>
                <li>
                    <span>2</span>
                    <div><strong>إنشاء المحاضرة</strong><small>عند إنشاء الحصة تختار المنصة المضيف وتنشئ الاجتماع وإعداداته.</small></div>
                </li>
                <li>
                    <span>3</span>
                    <div><strong>دخول آمن</strong><small>الطالب يمر عبر رابط المنصة بعد التحقق من تسجيله ووقت الإتاحة.</small></div>
                </li>
                <li>
                    <span>4</span>
                    <div><strong>الحضور</strong><small>بعد الانتهاء تُجمع مرات الدخول والخروج ويُحسب الحضور والتأخير والغياب.</small></div>
                </li>
                <li>
                    <span>5</span>
                    <div><strong>التسجيل</strong><small>يُسحب التسجيل ويُنشر يدوياً أو تلقائياً، مع إمكانية نسخه إلى تخزين خاص.</small></div>
                </li>
            </ol>

            <div class="zoom-overview__notes">
                <button type="button" @click="$dispatch('open-zoom-guide', { section: 'meeting' })"><i class="fa-solid fa-video"></i><span><b>المضيف والاجتماع</b> تعرف على الاستراتيجيات وروابط البدء.</span></button>
                <button type="button" @click="$dispatch('open-zoom-guide', { section: 'attendance' })"><i class="fa-solid fa-user-check"></i><span><b>احتساب الحضور</b> تعرف على المطابقة والمدة وحد التأخير.</span></button>
                <button type="button" @click="$dispatch('open-zoom-guide', { section: 'recording' })"><i class="fa-solid fa-record-vinyl"></i><span><b>التسجيل والنشر</b> تعرف على التخزين والحماية والصلاحيات.</span></button>
                <button type="button" @click="$dispatch('open-zoom-guide', { section: 'limits' })"><i class="fa-solid fa-triangle-exclamation"></i><span><b>قيود Zoom</b> متطلبات الترخيص وحدود Co-host.</span></button>
            </div>
        </section>

        <section class="admin-crud-card">
            <div class="admin-crud-card__head">
                <h2>الاختيارات الأساسية</h2>
                <p class="admin-crud-card__meta">تُدار كل إعدادات Zoom من لوحة التحكم. قيم `.env` تعمل كاحتياطي فقط عند غياب قيمة محفوظة.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <div class="admin-field">
                    <label for="defaultProvider">مزوّد المحاضرات الافتراضي</label>
                    <select id="defaultProvider" class="admin-control" wire:model="defaultProvider">
                        @foreach (MeetingSettings::providers() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="admin-field-hint">يُستخدم عند توليد الحصص الجديدة تلقائياً من الجداول.</div>
                    @error('defaultProvider')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label for="hostStrategy">استراتيجية المضيف</label>
                    <select id="hostStrategy" class="admin-control" wire:model="hostStrategy">
                        <option value="central">حساب مركزي (default host)</option>
                        <option value="instructor">حساب المدرب المرتبط</option>
                        <option value="pool">مجموعة مضيفين (أقل انشغالاً)</option>
                    </select>
                    <div class="admin-field-hint">تحدد أي حساب Zoom يستضيف كل محاضرة جديدة.</div>
                    @error('hostStrategy')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>
    </div>

    <div x-show="tab === 'connect'" x-cloak>
    <section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>بيانات التطبيق (Server-to-Server OAuth)</h2>
        <p class="admin-crud-card__meta">الأسرار تُحفظ مشفّرة. الحقول الفارغة تُبقي القيمة المحفوظة أو تحتفظ باحتياطي `.env`.</p>
    </div>

    <div class="admin-form-grid admin-form-grid--2">
        <div class="admin-field">
            <label for="accountId">Account ID</label>
            <input type="text" id="accountId" class="admin-control" wire:model="accountId" dir="ltr" autocomplete="off">
            @error('accountId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="clientId">Client ID</label>
            <input type="text" id="clientId" class="admin-control" wire:model="clientId" dir="ltr" autocomplete="off">
            @error('clientId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="clientSecret">Client Secret</label>
            <input type="password"
                id="clientSecret"
                class="admin-control"
                wire:model="clientSecret"
                dir="ltr"
                placeholder="{{ $hasStoredClientSecret ? '••••••••  (اتركه فارغاً للإبقاء)' : 'Secret Value' }}"
                autocomplete="new-password">
            @if ($hasStoredClientSecret)
                <div class="admin-field-hint">سر محفوظ. اترك الحقل فارغاً إن لم ترد تغييره.</div>
            @endif
            @error('clientSecret')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="webhookSecret">Webhook Secret Token</label>
            <input type="password"
                id="webhookSecret"
                class="admin-control"
                wire:model="webhookSecret"
                dir="ltr"
                placeholder="{{ $hasStoredWebhookSecret ? '••••••••  (اتركه فارغاً للإبقاء)' : 'Secret Token' }}"
                autocomplete="new-password">
            @if ($hasStoredWebhookSecret)
                <div class="admin-field-hint">رمز Webhook محفوظ مشفّراً.</div>
            @endif
            @error('webhookSecret')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="defaultHost">المضيف الافتراضي (User ID / email)</label>
            <input type="text" id="defaultHost" class="admin-control" wire:model="defaultHost" dir="ltr" placeholder="me أو user id أو البريد">
            @error('defaultHost')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field admin-field--wide">
            <label>Webhook Endpoint</label>
            <div class="admin-copy-field">
                <input type="text" class="admin-control" readonly dir="ltr" value="{{ $this->webhookEndpoint() }}">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" onclick="navigator.clipboard.writeText('{{ $this->webhookEndpoint() }}')">نسخ</button>
            </div>
            <div class="admin-field-hint">أضفه في Zoom Marketplace → Feature → Event Subscriptions.</div>
        </div>
    </div>

    <div class="admin-filter-actions" style="margin-top:1rem;">
        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="testConnection" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="testConnection">اختبار الاتصال</span>
            <span wire:loading wire:target="testConnection">جاري الاختبار…</span>
        </button>
        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncHosts" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="syncHosts">مزامنة المضيفين</span>
            <span wire:loading wire:target="syncHosts">جاري المزامنة…</span>
        </button>
    </div>

    @if ($connectionResult)
        <div class="zoom-connection-result" dir="ltr">
            <pre>{{ json_encode($connectionResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif
</section>
    </div>

    <div x-show="tab === 'meeting'" x-cloak>
<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>إعدادات الاجتماع</h2>
        <p class="admin-crud-card__meta">تُطبّق هذه الإعدادات على كل اجتماع Zoom تنشئه المنصة للمحاضرات.</p>
    </div>
    <div class="admin-form-grid admin-form-grid--2 zoom-toggle-grid">
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="registrationRequired">
                <span>طلب تسجيل فردي للمشاركين (Registration)</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="waitingRoom">
                <span>تفعيل غرفة الانتظار (Waiting Room)</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="hostVideo">
                <span>تشغيل فيديو المضيف عند البدء</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="participantVideo">
                <span>تشغيل فيديو المشاركين عند الدخول</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="muteUponEntry">
                <span>كتم المشاركين عند الدخول</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="joinBeforeHost">
                <span>السماح بالدخول قبل المضيف</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="allowMultipleDevices">
                <span>السماح للطالب بالدخول من عدة أجهزة</span>
            </label>
        </div>
        <div class="admin-field">
            <label for="audioMode">نوع الاتصال الصوتي</label>
            <select id="audioMode" class="admin-control" wire:model="audioMode">
                <option value="both">الإنترنت والهاتف</option>
                <option value="voip">صوت الإنترنت فقط</option>
                <option value="telephony">الهاتف فقط</option>
            </select>
            @error('audioMode')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="joinWindowMinutes">إظهار رابط الطالب قبل الموعد (دقائق)</label>
            <input type="number" id="joinWindowMinutes" class="admin-control" wire:model="joinWindowMinutes" min="0" max="1440">
            @error('joinWindowMinutes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
    </div>
</section>
    </div>

    <div x-show="tab === 'attendance'" x-cloak>
<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>الحضور التلقائي</h2>
        <p class="admin-crud-card__meta">بعد انتهاء الاجتماع تُجلب تقارير المشاركين وتُطابق الطلاب عبر التسجيل الفردي ثم البريد.</p>
    </div>
    <div class="admin-field">
        <label class="admin-check">
            <input type="checkbox" wire:model="autoAttendance">
            <span>تفعيل مزامنة الحضور التلقائية من Zoom</span>
        </label>
    </div>
    <div class="admin-form-grid admin-form-grid--2">
        <div class="admin-field">
            <label for="syncInterval">فترة المزامنة (دقائق)</label>
            <input type="number" id="syncInterval" class="admin-control" wire:model="syncInterval" min="5" max="120">
            @error('syncInterval')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="lateMinutes">حد التأخير (دقائق)</label>
            <input type="number" id="lateMinutes" class="admin-control" wire:model="lateMinutes" min="0" max="180">
            @error('lateMinutes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="minimumAttendancePercent">الحد الأدنى لنسبة الحضور (%)</label>
            <input type="number" id="minimumAttendancePercent" class="admin-control" wire:model="minimumAttendancePercent" min="0" max="100">
            @error('minimumAttendancePercent')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="minimumAttendanceMinutes">الحد الأدنى لمدة الحضور (دقائق)</label>
            <input type="number" id="minimumAttendanceMinutes" class="admin-control" wire:model="minimumAttendanceMinutes" min="0" max="600">
            @error('minimumAttendanceMinutes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="admin-field admin-field--wide">
        <label>أمر المزامنة اليدوية</label>
        <div class="admin-copy-field">
            <input type="text" class="admin-control" readonly dir="ltr" value="php artisan zoom:sync-attendance">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" onclick="navigator.clipboard.writeText('php artisan zoom:sync-attendance')">نسخ</button>
        </div>
    </div>
</section>
    </div>

    <div x-show="tab === 'recording'" x-cloak>
<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>سياسة التسجيل</h2>
        <p class="admin-crud-card__meta">تحكم في بدء التسجيل ووجهة تخزينه وطريقة نشره وصلاحيات مشاهدته.</p>
    </div>
    <div class="admin-form-grid admin-form-grid--2">
        <div class="admin-field">
            <label for="recordingPolicy">سياسة التسجيل</label>
            <select id="recordingPolicy" class="admin-control" wire:model.live="recordingPolicy">
                <option value="automatic">تلقائي (سحابي)</option>
                <option value="manual">يدوي من المضيف</option>
                <option value="disabled">معطّل</option>
            </select>
            @error('recordingPolicy')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="recordingDestination">وجهة التخزين</label>
            <select id="recordingDestination" class="admin-control" wire:model.live="recordingDestination">
                <option value="zoom">الإبقاء في Zoom</option>
                <option value="s3">نسخ إلى S3</option>
                <option value="google">نسخ إلى Google Drive</option>
            </select>
            @error('recordingDestination')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        @if ($recordingDestination === 's3')
            <div class="admin-field">
                <label for="s3Disk">قرص S3 (Laravel disk)</label>
                <input type="text" id="s3Disk" class="admin-control" wire:model="s3Disk" dir="ltr">
                @error('s3Disk')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endif
        @if ($recordingDestination === 'google')
            <div class="admin-field">
                <label for="googleDisk">قرص Google (Laravel disk)</label>
                <input type="text" id="googleDisk" class="admin-control" wire:model="googleDisk" dir="ltr">
                @error('googleDisk')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endif
        <div class="admin-field">
            <label for="recordingPublishMode">نشر التسجيل للطلاب</label>
            <select id="recordingPublishMode" class="admin-control" wire:model="recordingPublishMode">
                <option value="manual">نشر يدوي بعد المراجعة</option>
                <option value="auto_delayed">نشر تلقائي بعد تأخير</option>
            </select>
        </div>
        <div class="admin-field">
            <label for="recordingAutoPublishHours">تأخير النشر التلقائي (ساعات)</label>
            <input type="number" id="recordingAutoPublishHours" class="admin-control" wire:model="recordingAutoPublishHours" min="1" max="168">
        </div>
        <div class="admin-field">
            <label for="recordingRetentionDays">مدة الاحتفاظ (أيام)</label>
            <input type="number" id="recordingRetentionDays" class="admin-control" wire:model="recordingRetentionDays" min="30" max="3650">
        </div>
        <div class="admin-field">
            <label for="recordingAccessPolicy">صلاحية مشاهدة التسجيل</label>
            <select id="recordingAccessPolicy" class="admin-control" wire:model="recordingAccessPolicy">
                <option value="enrolled_only">كل طلاب الشعبة</option>
                <option value="attended_only">الحاضرون والمتأخرون فقط</option>
            </select>
        </div>
        <div class="admin-field">
            <label class="admin-check">
                <input type="checkbox" wire:model="recordingAllowDownload">
                <span>السماح للطلاب بتحميل التسجيل</span>
            </label>
        </div>
    </div>
    <div class="admin-field admin-field--wide" style="margin-top:0.75rem;">
        <label>مزامنة التسجيلات</label>
        <div class="admin-copy-field">
            <input type="text" class="admin-control" readonly dir="ltr" value="php artisan zoom:sync-recordings">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" onclick="navigator.clipboard.writeText('php artisan zoom:sync-recordings')">نسخ</button>
        </div>
    </div>
</section>
    </div>

    <div x-show="tab === 'hosts'" x-cloak>
<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>مضيفو Zoom</h2>
            <p class="admin-crud-card__meta">اربط كل مستخدم Zoom بمدرب، وفعّله ضمن المجموعة مع الأولوية.</p>
        </div>
        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncHosts">تحديث القائمة</button>
    </div>

    @if ($this->hosts->isEmpty())
        <p class="admin-detail-empty">لا يوجد مضيفون بعد. احفظ بيانات الاعتماد ثم نفّذ «مزامنة المضيفين».</p>
    @else
        <div class="admin-table-wrap">
            <table class="admin-data-table zoom-hosts-table">
                <thead>
                    <tr>
                        <th>البريد / المعرّف</th>
                        <th>الترخيص</th>
                        <th>المدرب</th>
                        <th>نشط</th>
                        <th>الأولوية</th>
                        <th>المجموعة</th>
                        <th>آخر مزامنة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->hosts as $host)
                        <tr wire:key="zoom-host-{{ $host->id }}">
                            <td>
                                <strong dir="ltr">{{ $host->email ?: '—' }}</strong>
                                <div class="admin-crud-card__meta" dir="ltr">{{ $host->zoom_user_id }}</div>
                            </td>
                            <td>{{ $host->license_type ?: '—' }}</td>
                            <td>
                                <select class="admin-control admin-control--inline"
                                    wire:change="updateHost({{ $host->id }}, $event.target.value, null, null, null)">
                                    <option value="">— غير مرتبط —</option>
                                    @foreach ($this->staffOptions as $staff)
                                        <option value="{{ $staff->id }}" @selected($host->academic_staff_id === $staff->id)>
                                            {{ $staff->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <label class="admin-check">
                                    <input type="checkbox"
                                        @checked($host->is_active)
                                        wire:change="updateHost({{ $host->id }}, null, $event.target.checked, null, null)">
                                    <span class="visually-hidden">نشط</span>
                                </label>
                            </td>
                            <td>
                                <input type="number" class="admin-control admin-control--inline" style="max-width:5rem;"
                                    value="{{ $host->priority }}"
                                    wire:change="updateHost({{ $host->id }}, null, null, Number($event.target.value), null)"
                                    min="0" max="999">
                            </td>
                            <td>
                                <input type="text" class="admin-control admin-control--inline" style="max-width:8rem;"
                                    value="{{ $host->pool }}"
                                    wire:change="updateHost({{ $host->id }}, null, null, null, $event.target.value)"
                                    placeholder="default">
                            </td>
                            <td>
                                {{ $host->last_synced_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
    </div>

    @include('partials.admin.zoom-integration-guide')

    <div class="zoom-savebar">
        <div class="zoom-savebar__hint">
            <i class="fa-solid fa-shield-halved"></i>
            الأسرار تُحفظ مشفّرة، والحفظ يشمل كل الأقسام دفعة واحدة.
        </div>
        <div class="zoom-savebar__actions">
            <a href="{{ route('admin.settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">إعدادات المنصة</a>
            <a href="{{ route('admin.teams-settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">Microsoft Teams</a>
            <button type="button" class="zoom-savebar__save" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk"></i> حفظ الإعدادات</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
        </div>
    </div>

</div>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .zoom-settings [x-cloak] { display: none !important; }
    .zoom-settings { display: flex; flex-direction: column; gap: 1rem; }
    .zoom-settings .admin-crud-card { margin: 0; }

    /* ===== Hero ===== */
    .zoom-hero {
        display: flex;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 18px;
        background:
            radial-gradient(60rem 22rem at 110% -40%, rgba(45, 212, 191, 0.28), transparent 60%),
            radial-gradient(40rem 18rem at -20% 140%, rgba(59, 130, 246, 0.22), transparent 55%),
            linear-gradient(135deg, #0c3b28 0%, #14532d 55%, #166534 100%);
        color: #fff;
        box-shadow: 0 18px 44px rgba(12, 59, 40, 0.28);
    }
    .zoom-hero__main { display: flex; gap: 1rem; align-items: flex-start; }
    .zoom-hero__icon {
        display: grid;
        place-items: center;
        width: 3.3rem;
        height: 3.3rem;
        flex: 0 0 auto;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-size: 1.3rem;
        color: #86efac;
    }
    .zoom-hero__eyebrow { display: block; color: #86efac; font-size: 0.64rem; font-weight: 900; letter-spacing: 0.02em; }
    .zoom-hero h1 { margin: 0.2rem 0 0.4rem; color: #fff; font-size: 1.35rem; font-weight: 900; }
    .zoom-hero p { margin: 0; max-width: 46rem; color: rgba(255, 255, 255, 0.82); font-size: 0.76rem; line-height: 1.9; }
    .zoom-hero__badges { display: flex; flex-wrap: wrap; gap: 0.45rem; margin-top: 0.75rem; }
    .zoom-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.32rem 0.65rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.1);
        color: #e2f7ea;
        font-size: 0.66rem;
        font-weight: 800;
    }
    .zoom-pill--ok { background: rgba(34, 197, 94, 0.25); border-color: rgba(134, 239, 172, 0.5); color: #bbf7d0; }
    .zoom-pill--warn { background: rgba(245, 158, 11, 0.22); border-color: rgba(253, 230, 138, 0.5); color: #fde68a; }
    .zoom-pill--off { background: rgba(148, 163, 184, 0.2); border-color: rgba(203, 213, 225, 0.4); color: #e2e8f0; }
    .zoom-hero__side { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; gap: 0.8rem; flex: 0 0 auto; }
    .zoom-hero__guide {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .zoom-hero__guide:hover { background: rgba(255, 255, 255, 0.22); }

    /* ===== Enable switch ===== */
    .zoom-switch { display: inline-flex; align-items: center; gap: 0.6rem; cursor: pointer; user-select: none; }
    .zoom-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .zoom-switch__track {
        position: relative;
        display: inline-block;
        width: 3.1rem;
        height: 1.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.32);
        transition: background 0.2s ease;
    }
    .zoom-switch__thumb {
        position: absolute;
        top: 50%;
        inset-inline-start: 0.22rem;
        width: 1.2rem;
        height: 1.2rem;
        border-radius: 50%;
        background: #fff;
        transform: translateY(-50%);
        transition: inset-inline-start 0.2s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }
    .zoom-switch input:checked + .zoom-switch__track { background: #22c55e; border-color: #4ade80; }
    .zoom-switch input:checked + .zoom-switch__track .zoom-switch__thumb { inset-inline-start: calc(100% - 1.42rem); }
    .zoom-switch__label { color: #fff; font-size: 0.76rem; font-weight: 900; }

    /* ===== Tabs ===== */
    .zoom-tabs {
        position: sticky;
        top: 0.5rem;
        z-index: 30;
        display: flex;
        gap: 0.35rem;
        overflow-x: auto;
        padding: 0.45rem;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(8px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
        scrollbar-width: thin;
    }
    .zoom-tabs button {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        white-space: nowrap;
        padding: 0.6rem 0.95rem;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 800;
        cursor: pointer;
        transition: background 0.18s ease, color 0.18s ease;
    }
    .zoom-tabs button:hover { background: #f1f5f9; color: #334155; }
    .zoom-tabs button.is-active { background: #14532d; color: #fff; box-shadow: 0 6px 16px rgba(20, 83, 45, 0.28); }
    .zoom-tabs button.is-active i { color: #86efac; }
    .zoom-tabs__dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        background: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
    }
    .zoom-tabs__count {
        display: inline-grid;
        place-items: center;
        min-width: 1.35rem;
        height: 1.35rem;
        padding: 0 0.3rem;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.62rem;
        font-weight: 900;
    }
    .zoom-tabs button.is-active .zoom-tabs__count { background: rgba(255, 255, 255, 0.2); color: #fff; }

    /* ===== Toggle cards ===== */
    .zoom-toggle-grid .admin-check {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        padding: 0.75rem 0.9rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fbfdfc;
        font-weight: 700;
        transition: border-color 0.18s ease, background 0.18s ease;
        cursor: pointer;
    }
    .zoom-toggle-grid .admin-check:hover { border-color: #86efac; background: #f0fdf4; }
    .zoom-toggle-grid .admin-check:has(input:checked) { border-color: #22c55e; background: #f0fdf4; }
    .zoom-toggle-grid .admin-check input { accent-color: #16a34a; width: 1.05rem; height: 1.05rem; }

    /* ===== Save bar ===== */
    .zoom-savebar {
        position: sticky;
        bottom: 0.75rem;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 1rem;
        border: 1px solid #d7e5dd;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        box-shadow: 0 -6px 28px rgba(15, 23, 42, 0.1), 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .zoom-savebar__hint { display: flex; align-items: center; gap: 0.5rem; color: #64748b; font-size: 0.68rem; font-weight: 700; }
    .zoom-savebar__hint i { color: #15803d; }
    .zoom-savebar__actions { display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
    .zoom-savebar__save {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.62rem 1.35rem;
        border: 0;
        border-radius: 10px;
        background: linear-gradient(135deg, #15803d, #16a34a);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .zoom-savebar__save:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(22, 163, 74, 0.42); }
    .zoom-savebar__save:disabled { opacity: 0.65; cursor: wait; transform: none; }

    /* ===== Shared ===== */
    .admin-form-grid--2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .admin-copy-field {
        display: flex;
        gap: 0.5rem;
        align-items: stretch;
    }
    .admin-copy-field .admin-control { flex: 1; }
    .admin-alert--danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .admin-control--inline { min-width: 7rem; padding: 0.25rem 0.5rem; font-size: 0.82rem; }
    .zoom-connection-result {
        margin-top: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        overflow: auto;
        max-height: 16rem;
    }
    .zoom-connection-result pre {
        margin: 0;
        font-size: 0.78rem;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .zoom-overview {
        border-color: #bbf7d0;
        background: linear-gradient(145deg, #ffffff 0%, #f0fdf4 100%);
    }
    .zoom-overview__eyebrow {
        display: block;
        margin-bottom: 0.25rem;
        color: #15803d;
        font-size: 0.68rem;
        font-weight: 900;
    }
    .zoom-workflow {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.65rem;
        margin: 1rem 0;
        padding: 0;
        list-style: none;
    }
    .zoom-workflow li {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.8rem;
        border: 1px solid #dcfce7;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.82);
    }
    .zoom-workflow li > span {
        display: grid;
        place-items: center;
        width: 1.7rem;
        height: 1.7rem;
        flex: 0 0 auto;
        border-radius: 50%;
        background: #166534;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 900;
    }
    .zoom-workflow strong,.zoom-workflow small { display: block; }
    .zoom-workflow strong { color: #17251f; font-size: 0.76rem; }
    .zoom-workflow small { margin-top: 0.25rem; color: #64748b; font-size: 0.65rem; line-height: 1.65; }
    .zoom-overview__notes {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }
    .zoom-overview__notes button {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.7rem;
        border: 1px solid #dbe5df;
        border-radius: 10px;
        background: #fff;
        color: #52645b;
        text-align: start;
        font: 700 0.68rem/1.65 inherit;
        cursor: pointer;
    }
    .zoom-overview__notes button:hover { border-color: #86efac; background: #f0fdf4; }
    .zoom-overview__notes i { margin-top: 0.15rem; color: #15803d; }
    .zoom-overview__notes b { display: block; color: #17251f; }
    @media (max-width: 1100px) {
        .zoom-workflow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .zoom-overview__notes { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .zoom-hero { flex-direction: column; }
        .zoom-hero__side { flex-direction: row; align-items: center; justify-content: space-between; width: 100%; }
        .admin-form-grid--2 { grid-template-columns: 1fr; }
        .zoom-hosts-table { font-size: 0.82rem; }
        .zoom-savebar { flex-direction: column; align-items: stretch; }
        .zoom-savebar__hint { justify-content: center; }
        .zoom-savebar__actions { justify-content: center; }
    }
    @media (max-width: 600px) {
        .zoom-workflow,.zoom-overview__notes { grid-template-columns: 1fr; }
    }
</style>
@endpush
