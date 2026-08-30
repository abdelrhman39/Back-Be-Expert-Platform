<?php

use App\Models\PlatformSetting;
use App\Services\ZoxAgent\ZoxAgentApiException;
use App\Services\ZoxAgent\ZoxAgentMeetingService;
use App\Support\MeetingSettings;
use App\Support\ZoxAgentSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'إعدادات ZoxAgent Meet',
    'adminPageDesc' => 'ربط المنصة مع ZoxAgent: قاعات، حضور، تسجيلات، ويب هوك، وتكلفة LiveKit',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'ZoxAgent Meet'],
    ],
])]
#[Title('ZoxAgent Meet | لوحة التحكم')]
class extends Component
{
    public bool $enabled = false;

    public string $baseUrl = 'https://app.zoxagent.com';

    public string $apiKey = '';

    public string $embedOrigin = '';

    public string $joinMode = 'redirect';

    public string $defaultProvider = 'zoom';

    public bool $autoRecord = true;

    public bool $autoAttendance = true;

    public string $attendanceMode = 'join';

    public int $startLeadMinutes = 5;

    public int $joinWindowMinutes = 30;

    public int $lateMinutes = 10;

    public int $minimumAttendanceMinutes = 1;

    public int $minimumAttendancePercent = 0;

    public string $recordingStorageMode = 'managed';

    public string $s3Label = 'Be Expert LMS';

    public string $s3Bucket = '';

    public string $s3Region = 'eu-central-1';

    public string $s3Endpoint = '';

    public string $s3PublicBaseUrl = '';

    public string $s3AccessKey = '';

    public string $s3SecretKey = '';

    public bool $s3ForcePathStyle = false;

    public bool $hasStoredS3Credentials = false;

    public bool $allowScreenShare = true;

    public bool $allowStudentCamera = false;

    public string $screenShareQuality = '720p';

    public string $cameraQuality = '540p';

    public string $recordingQuality = '720p';

    public bool $adaptiveStream = true;

    public bool $dynacast = true;

    public int $emptyTimeoutSec = 300;

    public bool $autoDispatchAgents = false;

    public string $webhookInboundUrl = '';

    public bool $hasWebhookSecret = false;

    public bool $hasStoredApiKey = false;

    public ?string $savedMessage = null;

    public ?string $actionError = null;

    /** @var array<string, mixed>|null */
    public ?array $connectionResult = null;

    /** @var array<string, mixed>|null */
    public ?array $billing = null;

    public ?string $billingError = null;

    public bool $billingLoading = false;

    public int $periodMonths = 1;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoxagent-settings.manage'), 403);

        $this->enabled = filter_var(PlatformSetting::get('zoxagent_enabled'), FILTER_VALIDATE_BOOL);
        $this->baseUrl = ZoxAgentSettings::baseUrl() ?? 'https://app.zoxagent.com';
        $this->embedOrigin = ZoxAgentSettings::embedOrigin();
        $this->joinMode = ZoxAgentSettings::joinMode();
        $this->defaultProvider = MeetingSettings::defaultProvider();
        $this->autoRecord = ZoxAgentSettings::autoRecord();
        $this->autoAttendance = ZoxAgentSettings::autoAttendance();
        $this->attendanceMode = ZoxAgentSettings::attendanceMode();
        $this->startLeadMinutes = ZoxAgentSettings::startLeadMinutes();
        $this->joinWindowMinutes = ZoxAgentSettings::joinWindowMinutes();
        $this->lateMinutes = ZoxAgentSettings::lateMinutes();
        $this->minimumAttendanceMinutes = ZoxAgentSettings::minimumAttendanceMinutes();
        $this->minimumAttendancePercent = ZoxAgentSettings::minimumAttendancePercent();
        $this->hasStoredApiKey = ZoxAgentSettings::hasApiKey();
        $this->apiKey = '';
        $this->recordingStorageMode = ZoxAgentSettings::recordingStorageMode();
        $this->s3Label = ZoxAgentSettings::s3Label();
        $this->s3Bucket = ZoxAgentSettings::s3Bucket() ?? '';
        $this->s3Region = ZoxAgentSettings::s3Region();
        $this->s3Endpoint = ZoxAgentSettings::s3Endpoint() ?? '';
        $this->s3PublicBaseUrl = ZoxAgentSettings::s3PublicBaseUrl() ?? '';
        $this->hasStoredS3Credentials = ZoxAgentSettings::hasS3Credentials();
        $this->s3ForcePathStyle = ZoxAgentSettings::s3ForcePathStyle();
        $this->allowScreenShare = ZoxAgentSettings::allowScreenShare();
        $this->allowStudentCamera = ZoxAgentSettings::allowStudentCamera();
        $this->screenShareQuality = ZoxAgentSettings::screenShareQuality();
        $this->cameraQuality = ZoxAgentSettings::cameraQuality();
        $this->recordingQuality = ZoxAgentSettings::recordingQuality();
        $this->adaptiveStream = ZoxAgentSettings::adaptiveStream();
        $this->dynacast = ZoxAgentSettings::dynacast();
        $this->emptyTimeoutSec = ZoxAgentSettings::emptyTimeoutSec();
        $this->autoDispatchAgents = ZoxAgentSettings::autoDispatchAgents();
        $this->webhookInboundUrl = ZoxAgentSettings::inboundWebhookUrl();
        $this->hasWebhookSecret = filled(ZoxAgentSettings::webhookSecret());

        if ($this->hasStoredApiKey) {
            $this->loadBilling();
        }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoxagent-settings.manage'), 403);

        $this->validate([
            'baseUrl' => ['required_if:enabled,true', 'nullable', 'url', 'max:190'],
            'apiKey' => [$this->hasStoredApiKey ? 'nullable' : 'required_if:enabled,true', 'string', 'max:255'],
            'embedOrigin' => ['required', 'url', 'max:190'],
            'joinMode' => ['required', 'in:redirect,embed'],
            'defaultProvider' => ['required', 'in:zoom,teams,zoxagent,manual'],
            'attendanceMode' => ['required', 'in:join,duration'],
            'startLeadMinutes' => ['required', 'integer', 'min:0', 'max:180'],
            'joinWindowMinutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'lateMinutes' => ['required', 'integer', 'min:0', 'max:180'],
            'minimumAttendanceMinutes' => ['required', 'integer', 'min:0', 'max:600'],
            'minimumAttendancePercent' => ['required', 'integer', 'min:0', 'max:100'],
            'recordingStorageMode' => ['required', 'in:managed,byo'],
            'screenShareQuality' => ['required', 'in:off,720p,1080p'],
            'cameraQuality' => ['required', 'in:360p,540p,720p'],
            'recordingQuality' => ['required', 'in:off,720p,1080p'],
            'emptyTimeoutSec' => ['required', 'integer', 'min:60', 'max:3600'],
            's3Bucket' => ['required_if:recordingStorageMode,byo', 'nullable', 'string', 'max:200'],
            's3Region' => ['required_if:recordingStorageMode,byo', 'nullable', 'string', 'max:80'],
        ], [], [
            'baseUrl' => 'رابط ZoxAgent',
            'apiKey' => 'مفتاح API',
            'embedOrigin' => 'نطاق الدمج',
            'joinMode' => 'طريقة الدخول',
            'defaultProvider' => 'مزوّد المحاضرات',
            's3Bucket' => 'حاوية S3',
            's3Region' => 'منطقة S3',
        ]);

        if ($this->recordingStorageMode === 'byo' && ! $this->hasStoredS3Credentials && (blank($this->s3AccessKey) || blank($this->s3SecretKey))) {
            $this->addError('s3AccessKey', 'مفاتيح AWS مطلوبة عند اختيار تخزين المنصة.');

            return;
        }

        $userId = auth()->id();
        ZoxAgentSettings::set('enabled', $this->enabled ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('base_url', rtrim($this->baseUrl, '/'), updatedBy: $userId);
        ZoxAgentSettings::set('embed_origin', rtrim($this->embedOrigin, '/'), updatedBy: $userId);
        ZoxAgentSettings::set('join_mode', $this->joinMode, updatedBy: $userId);
        ZoxAgentSettings::set('auto_record', $this->autoRecord ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('auto_attendance', $this->autoAttendance ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('attendance_mode', $this->attendanceMode, updatedBy: $userId);
        ZoxAgentSettings::set('start_lead_minutes', (string) $this->startLeadMinutes, updatedBy: $userId);
        ZoxAgentSettings::set('join_window_minutes', (string) $this->joinWindowMinutes, updatedBy: $userId);
        ZoxAgentSettings::set('late_minutes', (string) $this->lateMinutes, updatedBy: $userId);
        ZoxAgentSettings::set('minimum_attendance_minutes', (string) $this->minimumAttendanceMinutes, updatedBy: $userId);
        ZoxAgentSettings::set('minimum_attendance_percent', (string) $this->minimumAttendancePercent, updatedBy: $userId);
        ZoxAgentSettings::set('recording_storage_mode', $this->recordingStorageMode, updatedBy: $userId);
        ZoxAgentSettings::set('s3_label', $this->s3Label, updatedBy: $userId);
        ZoxAgentSettings::set('s3_bucket', $this->s3Bucket, updatedBy: $userId);
        ZoxAgentSettings::set('s3_region', $this->s3Region, updatedBy: $userId);
        ZoxAgentSettings::set('s3_endpoint', $this->s3Endpoint, updatedBy: $userId);
        ZoxAgentSettings::set('s3_public_base_url', $this->s3PublicBaseUrl, updatedBy: $userId);
        ZoxAgentSettings::set('s3_force_path_style', $this->s3ForcePathStyle ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('livekit_allow_screen_share', $this->allowScreenShare ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('livekit_allow_student_camera', $this->allowStudentCamera ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('livekit_screen_share_quality', $this->screenShareQuality, updatedBy: $userId);
        ZoxAgentSettings::set('livekit_camera_quality', $this->cameraQuality, updatedBy: $userId);
        ZoxAgentSettings::set('livekit_recording_quality', $this->recordingQuality, updatedBy: $userId);
        ZoxAgentSettings::set('livekit_adaptive_stream', $this->adaptiveStream ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('livekit_dynacast', $this->dynacast ? '1' : '0', updatedBy: $userId);
        ZoxAgentSettings::set('livekit_empty_timeout_sec', (string) $this->emptyTimeoutSec, updatedBy: $userId);
        ZoxAgentSettings::set('livekit_auto_dispatch_agents', $this->autoDispatchAgents ? '1' : '0', updatedBy: $userId);

        if (filled($this->apiKey)) {
            ZoxAgentSettings::set('api_key', trim($this->apiKey), true, $userId);
            $this->hasStoredApiKey = true;
            $this->apiKey = '';
        }
        if (filled($this->s3AccessKey)) {
            ZoxAgentSettings::set('s3_access_key', trim($this->s3AccessKey), true, $userId);
            $this->s3AccessKey = '';
        }
        if (filled($this->s3SecretKey)) {
            ZoxAgentSettings::set('s3_secret_key', trim($this->s3SecretKey), true, $userId);
            $this->s3SecretKey = '';
        }
        $this->hasStoredS3Credentials = ZoxAgentSettings::hasS3Credentials();

        MeetingSettings::setDefaultProvider($this->defaultProvider);

        $this->actionError = null;
        $messages = ['تم حفظ إعدادات ZoxAgent.'];

        if ($this->enabled && ZoxAgentSettings::configured()) {
            $meetings = app(ZoxAgentMeetingService::class);
            try {
                $meetings->pushMediaPolicy();
                $messages[] = 'سياسة LiveKit رُفعت إلى ZoxAgent.';
            } catch (ZoxAgentApiException $e) {
                $this->actionError = $e->getMessage();
            }
            try {
                $meetings->pushRecordingStorage();
                $messages[] = 'إعدادات التخزين رُفعت.';
            } catch (ZoxAgentApiException $e) {
                $this->actionError = ($this->actionError ? $this->actionError.' — ' : '').$e->getMessage();
            }
            try {
                $meetings->pushWebhooks();
                $this->hasWebhookSecret = filled(ZoxAgentSettings::webhookSecret());
                $messages[] = 'الويب هوك مربوط.';
            } catch (ZoxAgentApiException $e) {
                $this->actionError = ($this->actionError ? $this->actionError.' — ' : '').$e->getMessage();
            }
        }

        $this->savedMessage = implode(' ', $messages);

        if (ZoxAgentSettings::configured()) {
            $this->loadBilling();
        }
    }

    public function testConnection(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoxagent-settings.manage'), 403);

        $this->actionError = null;
        $this->connectionResult = null;

        try {
            $this->connectionResult = app(ZoxAgentMeetingService::class)->testConnection();
            $this->savedMessage = 'الاتصال بـ ZoxAgent ناجح.';
            $this->loadBilling();
        } catch (ZoxAgentApiException $e) {
            $this->actionError = $e->getMessage();
        }
    }

    public function loadBilling(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoxagent-settings.manage'), 403);

        if (! ZoxAgentSettings::configured()) {
            $this->billing = null;
            $this->billingError = 'احفظ رابط ZoxAgent ومفتاح API أولاً لعرض الاشتراك والتجديد من هنا.';

            return;
        }

        $this->billingLoading = true;
        $this->billingError = null;

        try {
            $snapshot = app(ZoxAgentMeetingService::class)->billingSnapshot();
            $this->billing = $snapshot;
        } catch (ZoxAgentApiException $e) {
            $this->billing = null;
            $this->billingError = $e->getMessage();
        }

        $this->billingLoading = false;
    }

    public function checkoutPlan(string $planCode, string $kind = 'plan'): void
    {
        $this->runCheckout([
            'kind' => $kind === 'renewal' ? 'renewal' : 'plan',
            'planCode' => $planCode,
            'periodMonths' => max(1, min(12, $this->periodMonths)),
        ]);
    }

    public function checkoutAddOn(string $code, int $quantity = 1): void
    {
        $this->runCheckout([
            'kind' => 'addon',
            'addOnCode' => $code,
            'periodMonths' => max(1, min(12, $this->periodMonths)),
            'quantity' => max(1, min(20, $quantity)),
        ]);
    }

    /** @param  array<string, mixed>  $payload */
    private function runCheckout(array $payload): void
    {
        abort_unless(auth()->user()?->canAdmin('zoxagent-settings.manage'), 403);

        $this->actionError = null;

        try {
            $result = app(ZoxAgentMeetingService::class)->createBillingCheckout($payload);
            $payUrl = $result['payUrl'] ?? null;
            if (! is_string($payUrl) || $payUrl === '') {
                throw new ZoxAgentApiException('لم يُرجع ZoxAgent رابط دفع. تحقق من إعداد بوابة الدفع.');
            }
            $this->savedMessage = 'فُتحت صفحة الدفع في نافذة جديدة. أكمل السداد ثم اضغط «تحديث الحالة».';
            $this->js('window.open('.json_encode($payUrl, JSON_UNESCAPED_SLASHES).', "_blank", "noopener,noreferrer")');
            $this->loadBilling();
        } catch (ZoxAgentApiException $e) {
            $this->actionError = $e->getMessage();
        }
    }

    public function testStorage(): void
    {
        abort_unless(auth()->user()?->canAdmin('zoxagent-settings.manage'), 403);

        $this->actionError = null;
        try {
            $credentials = [];
            if (filled($this->s3AccessKey) && filled($this->s3SecretKey)) {
                $credentials = [
                    'bucket' => $this->s3Bucket,
                    'region' => $this->s3Region,
                    'endpoint' => $this->s3Endpoint ?: null,
                    'accessKey' => $this->s3AccessKey,
                    'secretKey' => $this->s3SecretKey,
                    'forcePathStyle' => $this->s3ForcePathStyle,
                ];
            }
            app(ZoxAgentMeetingService::class)->testRecordingStorage($credentials);
            $this->savedMessage = 'اختبار التخزين س3 ناجح.';
        } catch (ZoxAgentApiException $e) {
            $this->actionError = $e->getMessage();
        }
    }
};

<div class="admin-page">
    <section class="admin-crud-card">
        <div class="admin-crud-card__head admin-crud-card__head--row">
            <div>
                <h1>ZoxAgent Meet</h1>
                <p class="admin-crud-card__meta">
                    المفاتيح تُحفظ في قاعدة البيانات مشفّرة. أضف نطاق هذه المنصة في نطاقات الدمج داخل لوحة ZoxAgent،
                    واستخدم مفتاح API بصلاحيات <code>rooms:read</code> و<code>rooms:write</code> و<code>embed:write</code>.
                    تجديد الاشتراك وشراء الإضافات يتم من هذه الصفحة بنفس المفتاح، دون تسجيل دخول إلى ZoxAgent.
                </p>
            </div>
            <label class="zoom-switch">
                <input type="checkbox" wire:model.live="enabled">
                <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                <span class="zoom-switch__label">تفعيل التكامل</span>
            </label>
        </div>

        @if ($savedMessage)
            <div class="admin-alert admin-alert--info is-visible" role="status">{{ $savedMessage }}</div>
        @endif
        @if ($actionError)
            <div class="admin-alert admin-alert--danger is-visible" role="alert">{{ $actionError }}</div>
        @endif
        @if ($connectionResult)
            <div class="admin-alert admin-alert--info is-visible" role="status">
                تم التحقق من الحساب
                @if (! empty($connectionResult['organization']['name']))
                    — {{ $connectionResult['organization']['name'] }}
                @endif
            </div>
        @endif

        <style>
            .za-bill { margin: 1.25rem 0 1.5rem; }
            .za-bill-hero {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.8fr);
                gap: 1rem;
                padding: 1.15rem 1.25rem;
                border-radius: 16px;
                background: linear-gradient(135deg, #0f172a 0%, #134e4a 58%, #0f766e 100%);
                color: #f8fafc;
                position: relative;
                overflow: hidden;
            }
            .za-bill-hero::after {
                content: "";
                position: absolute;
                inset-inline-end: -40px;
                top: -30px;
                width: 180px;
                height: 180px;
                border-radius: 50%;
                background: rgba(255,255,255,.08);
            }
            .za-bill-kicker { font-size: .78rem; opacity: .8; margin-bottom: .35rem; }
            .za-bill-hero h2 { margin: 0 0 .35rem; font-size: 1.35rem; }
            .za-bill-hero p { margin: 0; opacity: .86; font-size: .9rem; line-height: 1.6; }
            .za-bill-badge {
                display: inline-flex; align-items: center; gap: .35rem;
                padding: .2rem .6rem; border-radius: 999px; font-size: .75rem; font-weight: 700;
                background: rgba(255,255,255,.14);
            }
            .za-bill-badge--ok { background: #dcfce7; color: #166534; }
            .za-bill-badge--info { background: #dbeafe; color: #1e40af; }
            .za-bill-badge--warn { background: #ffedd5; color: #9a3412; }
            .za-bill-badge--danger { background: #fee2e2; color: #991b1b; }
            .za-bill-badge--muted { background: #e2e8f0; color: #334155; }
            .za-bill-metrics { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: .6rem; margin-top: 1rem; }
            .za-bill-metric { background: rgba(255,255,255,.1); border-radius: 12px; padding: .7rem .8rem; }
            .za-bill-metric span { display: block; font-size: .72rem; opacity: .75; }
            .za-bill-metric strong { font-size: 1.05rem; }
            .za-bill-side {
                background: rgba(255,255,255,.1);
                border-radius: 14px;
                padding: 1rem;
                display: flex; flex-direction: column; gap: .7rem;
                position: relative; z-index: 1;
            }
            .za-bill-side label { font-size: .8rem; opacity: .85; }
            .za-bill-side select, .za-bill-side .admin-control { background: #fff; color: #0f172a; }
            .za-bill-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
            .za-bill-empty { padding: 1rem 1.1rem; border: 1px dashed #cbd5e1; border-radius: 14px; color: #475569; background: #f8fafc; }
            .za-bill-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: .85rem; }
            .za-bill-card {
                border: 1px solid #e2e8f0; border-radius: 14px; padding: 1rem;
                background: #fff; display: flex; flex-direction: column; gap: .55rem;
                min-height: 100%;
            }
            .za-bill-card.is-current { border-color: #0f766e; box-shadow: 0 0 0 1px #0f766e inset; }
            .za-bill-card.is-owned { border-color: #86efac; }
            .za-bill-card h4 { margin: 0; font-size: 1rem; }
            .za-bill-card .za-price { font-size: 1.2rem; font-weight: 800; color: #0f172a; }
            .za-bill-card .za-price small { font-size: .75rem; font-weight: 500; color: #64748b; }
            .za-bill-card ul { margin: 0; padding-inline-start: 1.1rem; color: #475569; font-size: .82rem; line-height: 1.55; }
            .za-bill-card .za-qty { display: flex; align-items: center; gap: .4rem; }
            .za-bill-card .za-qty input { width: 72px; }
            .za-product-block { margin-top: 1.25rem; }
            .za-product-block h3 { margin: 0 0 .35rem; font-size: 1.05rem; }
            .za-product-block > p { margin: 0 0 .85rem; color: #64748b; font-size: .88rem; }
            .za-checkouts { margin-top: 1rem; overflow-x: auto; }
            .za-checkouts table { width: 100%; border-collapse: collapse; font-size: .85rem; }
            .za-checkouts th, .za-checkouts td { padding: .55rem .4rem; border-bottom: 1px solid #e2e8f0; text-align: start; }
            @media (max-width: 900px) {
                .za-bill-hero { grid-template-columns: 1fr; }
                .za-bill-metrics { grid-template-columns: 1fr; }
            }
        </style>

        <section class="za-bill" wire:loading.class="is-loading">
            <div class="admin-crud-card__head" style="margin-bottom:.75rem;">
                <h2>الاشتراك والفوترة</h2>
                <p class="admin-crud-card__meta">جدّد Meet أو اشترِ إضافات ومنتجات ZoxAgent الأخرى بنفس الأسعار، دون مغادرة لوحة المنصة.</p>
            </div>

            @if ($billingLoading)
                <div class="za-bill-empty">جارٍ تحميل الاشتراك من ZoxAgent…</div>
            @elseif ($billingError)
                <div class="za-bill-empty">
                    {{ $billingError }}
                    @if ($hasStoredApiKey)
                        <div style="margin-top:.65rem;">
                            <button type="button" class="admin-btn-secondary" wire:click="loadBilling">إعادة المحاولة</button>
                        </div>
                    @endif
                </div>
            @elseif ($billing)
                @php
                    $sub = $billing['subscription'] ?? null;
                    $usage = $billing['usage'] ?? [];
                    $products = $billing['products'] ?? [];
                    $addOns = $billing['addOns'] ?? [];
                    $checkouts = $billing['checkouts'] ?? [];
                    $orgStatus = $billing['organization']['status'] ?? null;
                    $subStatus = is_array($sub) ? ($sub['status'] ?? '') : '';
                    $canRenew = is_array($sub) && ($sub['priceMonthlyCents'] ?? 0) > 0;
                @endphp
                <div class="za-bill-hero">
                    <div>
                        <div class="za-bill-kicker">{{ $billing['organization']['name'] ?? 'حساب ZoxAgent' }}</div>
                        <h2>{{ $sub['planNameAr'] ?? 'لا توجد خطة مدفوعة' }}</h2>
                        <p>
                            @if ($sub)
                                الحالة: {{ $sub['statusAr'] ?? $subStatus }}
                                @if (! empty($sub['currentPeriodEnd']))
                                    — ينتهي {{ \Illuminate\Support\Carbon::parse($sub['currentPeriodEnd'])->timezone(config('app.timezone'))->format('Y-m-d') }}
                                    @if (($sub['daysRemaining'] ?? null) !== null)
                                        ({{ $sub['daysRemaining'] }} يوم)
                                    @endif
                                @endif
                            @else
                                يمكنك الاشتراك في Zox Meet أو أي أداة أخرى أدناه مباشرة.
                            @endif
                        </p>
                        @if ($orgStatus === 'suspended' || in_array($subStatus, ['past_due', 'suspended'], true))
                            <p style="margin-top:.5rem; color:#fecaca;">الحساب معلّق أو متأخر السداد. أكمل التجديد لاستعادة الخدمة.</p>
                        @endif
                        <div class="za-bill-metrics">
                            <div class="za-bill-metric">
                                <span>القاعات</span>
                                <strong>{{ $usage['roomsUsed'] ?? 0 }} / {{ $usage['roomsLimit'] ?? '—' }}</strong>
                            </div>
                            <div class="za-bill-metric">
                                <span>الساعات هذا الشهر</span>
                                <strong>{{ $usage['monthlyHoursUsed'] ?? 0 }} / {{ $usage['monthlyHoursLimit'] ?? '—' }}</strong>
                            </div>
                            <div class="za-bill-metric">
                                <span>التخزين (GB)</span>
                                <strong>{{ $usage['storageGbUsed'] ?? 0 }} / {{ $usage['storageGbLimit'] ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="za-bill-side">
                        <label for="periodMonths">مدة الفاتورة</label>
                        <select id="periodMonths" class="admin-control" wire:model.live="periodMonths">
                            <option value="1">شهر واحد</option>
                            <option value="3">3 أشهر</option>
                            <option value="6">6 أشهر</option>
                            <option value="12">سنة كاملة</option>
                        </select>
                        <div class="za-bill-actions">
                            @if ($canRenew)
                                <button type="button" class="admin-btn-primary" wire:click="checkoutPlan('{{ $sub['planCode'] }}', 'renewal')" wire:loading.attr="disabled" wire:target="checkoutPlan, checkoutAddOn, loadBilling">
                                    تجديد {{ $sub['planNameAr'] }}
                                </button>
                            @endif
                            <button type="button" class="admin-btn-secondary" wire:click="loadBilling" wire:loading.attr="disabled" wire:target="loadBilling">تحديث الحالة</button>
                        </div>
                        <small style="opacity:.8;">ستُفتح صفحة الدفع في نافذة جديدة. لا حاجة لتسجيل الدخول إلى ZoxAgent.</small>
                    </div>
                </div>

                @foreach ($products as $product)
                    @php $productPlans = $product['plans'] ?? []; @endphp
                    @if ($productPlans !== [])
                    <div class="za-product-block">
                        <h3>
                            {{ $product['nameAr'] }}
                            @if (! empty($product['subscribed']))
                                <span class="za-bill-badge za-bill-badge--ok">مفعّل</span>
                            @endif
                        </h3>
                        <p>{{ $product['taglineAr'] ?? '' }}</p>
                        <div class="za-bill-grid">
                            @foreach ($productPlans as $plan)
                                @php
                                    $isCurrent = ! empty($plan['isCurrent']);
                                    $total = ((int) ($plan['priceMonthlyCents'] ?? 0)) * max(1, (int) $periodMonths);
                                    $currency = $plan['currency'] ?? 'USD';
                                @endphp
                                <article class="za-bill-card {{ $isCurrent ? 'is-current' : '' }}">
                                    <div style="display:flex; justify-content:space-between; gap:.5rem; align-items:center;">
                                        <h4>{{ $plan['nameAr'] }}</h4>
                                        @if ($isCurrent)
                                            <span class="za-bill-badge za-bill-badge--ok">الحالية</span>
                                        @endif
                                    </div>
                                    <div class="za-price">
                                        {{ number_format($total / 100, 2) }} {{ $currency }}
                                        <small> / {{ $periodMonths }} {{ $periodMonths == 1 ? 'شهر' : 'أشهر' }}</small>
                                    </div>
                                    @if (! empty($plan['highlightsAr']))
                                        <ul>
                                            @foreach (array_slice($plan['highlightsAr'], 0, 4) as $line)
                                                <li>{{ $line }}</li>
                                            @endforeach
                                        </ul>
                                    @elseif (($product['id'] ?? '') === 'meet')
                                        <ul>
                                            <li>{{ $plan['maxParticipants'] ?? '—' }} مشارك</li>
                                            <li>{{ $plan['maxRooms'] ?? '—' }} قاعة</li>
                                            <li>{{ $plan['monthlyHoursLimit'] ?? '—' }} ساعة/شهر</li>
                                        </ul>
                                    @endif
                                    <div style="margin-top:auto;">
                                        @if ($isCurrent)
                                            <button type="button" class="admin-btn-primary" style="width:100%;" wire:click="checkoutPlan('{{ $plan['code'] }}', 'renewal')" wire:loading.attr="disabled">تجديد هذه الخطة</button>
                                        @else
                                            <button type="button" class="admin-btn-secondary" style="width:100%;" wire:click="checkoutPlan('{{ $plan['code'] }}')" wire:loading.attr="disabled">
                                                {{ ! empty($product['subscribed']) ? 'ترقية / تغيير' : 'اشتراك' }} — {{ number_format(((int) ($plan['priceMonthlyCents'] ?? 0)) / 100, 2) }} {{ $currency }}/شهر
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach

                @if ($addOns !== [])
                    <div class="za-product-block">
                        <h3>إضافات السعة والمميزات</h3>
                        <p>تُحسب بنفس أسعار ZoxAgent. مناسبة لتوسيع القاعات أو الساعات أو التحليلات دون ترقية الخطة كاملة.</p>
                        <div class="za-bill-grid">
                            @foreach ($addOns as $addOn)
                                @php
                                    $code = $addOn['code'] ?? '';
                                    $owned = (int) ($addOn['ownedQuantity'] ?? 0);
                                    $monthlyCents = (int) ($addOn['priceMonthlyCents'] ?? 0);
                                @endphp
                                <article class="za-bill-card {{ $owned > 0 ? 'is-owned' : '' }}" x-data="{ qty: 1 }">
                                    <div style="display:flex; justify-content:space-between; gap:.4rem;">
                                        <h4>{{ $addOn['nameAr'] }}</h4>
                                        <span class="za-bill-badge za-bill-badge--muted">{{ $addOn['categoryAr'] ?? '' }}</span>
                                    </div>
                                    @if (! empty($addOn['description']))
                                        <p style="margin:0; color:#64748b; font-size:.85rem;">{{ $addOn['description'] }}</p>
                                    @endif
                                    @if ($owned > 0)
                                        <small>نشطة حالياً: ×{{ $owned }}</small>
                                    @endif
                                    <div class="za-price">
                                        <span x-text="(({{ $monthlyCents }} * qty * {{ (int) $periodMonths }}) / 100).toFixed(2)"></span>
                                        {{ $addOn['currency'] ?? 'USD' }}
                                        <small>للكمية والمدة المختارة</small>
                                    </div>
                                    <div class="za-qty">
                                        <label>الكمية</label>
                                        <input type="number" class="admin-control" min="1" max="20" x-model.number="qty">
                                    </div>
                                    <button type="button" class="admin-btn-secondary" @click="$wire.checkoutAddOn(@js($code), qty)">شراء الإضافة</button>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($checkouts !== [])
                    <div class="za-checkouts">
                        <h3 style="font-size:1rem; margin: 1rem 0 .4rem;">آخر عمليات الدفع</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>البند</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($checkouts as $row)
                                    <tr>
                                        <td>{{ isset($row['createdAt']) ? \Illuminate\Support\Carbon::parse($row['createdAt'])->timezone(config('app.timezone'))->format('Y-m-d H:i') : '—' }}</td>
                                        <td>{{ $row['addOn']['nameAr'] ?? $row['plan']['nameAr'] ?? $row['kind'] ?? '—' }}</td>
                                        <td>{{ number_format(((int) ($row['amountCents'] ?? 0)) / 100, 2) }} {{ $row['currency'] ?? '' }}</td>
                                        <td>
                                            <span class="za-bill-badge {{ ($row['status'] ?? '') === 'paid' ? 'za-bill-badge--ok' : (($row['status'] ?? '') === 'pending' ? 'za-bill-badge--info' : 'za-bill-badge--muted') }}">
                                                {{ $row['statusAr'] ?? $row['status'] ?? '' }}
                                            </span>
                                            @if (($row['status'] ?? '') === 'pending' && ! empty($row['payUrl']))
                                                <a href="{{ $row['payUrl'] }}" target="_blank" rel="noopener">إكمال الدفع</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <div class="za-bill-empty">احفظ مفتاح API ثم اضغط اختبار الاتصال لعرض الاشتراك وخيارات التجديد هنا.</div>
            @endif
        </section>

        <div class="admin-form-grid admin-form-grid--2">
            <div class="admin-field">
                <label for="baseUrl">رابط ZoxAgent</label>
                <input type="url" id="baseUrl" class="admin-control" wire:model="baseUrl" dir="ltr" placeholder="https://app.zoxagent.com">
                @error('baseUrl')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label for="apiKey">مفتاح API</label>
                <input type="password" id="apiKey" class="admin-control" wire:model="apiKey" dir="ltr" autocomplete="new-password" placeholder="{{ $hasStoredApiKey ? 'مفتاح محفوظ — اتركه فارغاً للإبقاء عليه' : 'zxk_live_...' }}">
                <div class="admin-field-hint">يُحفظ مشفّراً في إعدادات المنصة، وليس في ملف البيئة.</div>
                @error('apiKey')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label for="embedOrigin">نطاق الدمج (هذه المنصة)</label>
                <input type="url" id="embedOrigin" class="admin-control" wire:model="embedOrigin" dir="ltr">
                <div class="admin-field-hint">يجب أن يطابق نطاقاً مسموحاً في إعدادات Embed داخل ZoxAgent.</div>
                @error('embedOrigin')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label for="joinMode">طريقة دخول الطالب والمدرب</label>
                <select id="joinMode" class="admin-control" wire:model="joinMode">
                    <option value="redirect">تحويل إلى قاعة Zox Meet (موصى به)</option>
                    <option value="embed">تضمين القاعة داخل المنصة</option>
                </select>
            </div>
            <div class="admin-field">
                <label for="defaultProvider">مزوّد المحاضرات الافتراضي</label>
                <select id="defaultProvider" class="admin-control" wire:model="defaultProvider">
                    @foreach (MeetingSettings::providers() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="admin-field-hint">عند اختيار ZoxAgent Meet تُنشأ قاعة تلقائياً مع كل حصة جديدة.</div>
            </div>
            <div class="admin-field">
                <label for="attendanceMode">سياسة الحضور</label>
                <select id="attendanceMode" class="admin-control" wire:model="attendanceMode">
                    <option value="join">حضور بمجرد الدخول</option>
                    <option value="duration">حضور حسب مدة البقاء</option>
                </select>
            </div>
            <div class="admin-field">
                <label for="startLeadMinutes">بدء القاعة قبل الموعد (دقائق)</label>
                <input type="number" id="startLeadMinutes" class="admin-control" wire:model="startLeadMinutes" min="0" max="180">
            </div>
            <div class="admin-field">
                <label for="joinWindowMinutes">إتاحة الدخول قبل الموعد (دقائق)</label>
                <input type="number" id="joinWindowMinutes" class="admin-control" wire:model="joinWindowMinutes" min="0" max="1440">
            </div>
            <div class="admin-field">
                <label for="lateMinutes">حد التأخير (دقائق)</label>
                <input type="number" id="lateMinutes" class="admin-control" wire:model="lateMinutes" min="0" max="180">
            </div>
            <div class="admin-field">
                <label for="minimumAttendanceMinutes">الحد الأدنى للحضور (دقائق)</label>
                <input type="number" id="minimumAttendanceMinutes" class="admin-control" wire:model="minimumAttendanceMinutes" min="0" max="600">
            </div>
            <div class="admin-field">
                <label for="minimumAttendancePercent">الحد الأدنى للنسبة (%)</label>
                <input type="number" id="minimumAttendancePercent" class="admin-control" wire:model="minimumAttendancePercent" min="0" max="100">
            </div>
        </div>

        <div class="admin-form-grid admin-form-grid--2" style="margin-top: 1rem;">
            <label class="zoom-switch">
                <input type="checkbox" wire:model.live="autoRecord">
                <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                <span class="zoom-switch__label">تسجيل سحابي تلقائي (Egress — أعلى تكلفة LiveKit)</span>
            </label>
            <label class="zoom-switch">
                <input type="checkbox" wire:model.live="autoAttendance">
                <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                <span class="zoom-switch__label">مزامنة الحضور تلقائياً</span>
            </label>
        </div>

        <section class="admin-crud-card admin-crud-card--filter" style="margin-top:1.25rem;">
            <div class="admin-crud-card__head">
                <h2>تكلفة LiveKit</h2>
                <p class="admin-crud-card__meta">عطّل أو خفّض ما يرفع فاتورة البث: التسجيل المركّب، مشاركة الشاشة عالية الجودة، بقاء القاعة فارغة، والوكلاء داخل الغرفة.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <div class="admin-field">
                    <label for="recordingQuality">جودة تسجيل السحابة</label>
                    <select id="recordingQuality" class="admin-control" wire:model="recordingQuality">
                        <option value="off">إيقاف (لا Egress)</option>
                        <option value="720p">720p — توفير</option>
                        <option value="1080p">1080p — أعلى تكلفة</option>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="screenShareQuality">جودة مشاركة الشاشة</label>
                    <select id="screenShareQuality" class="admin-control" wire:model="screenShareQuality">
                        <option value="off">إيقاف</option>
                        <option value="720p">720p @ 15fps — توفير</option>
                        <option value="1080p">1080p @ 30fps — أعلى استهلاك</option>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="cameraQuality">جودة كاميرا المدرب</label>
                    <select id="cameraQuality" class="admin-control" wire:model="cameraQuality">
                        <option value="360p">360p</option>
                        <option value="540p">540p — موصى به</option>
                        <option value="720p">720p</option>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="emptyTimeoutSec">إغلاق القاعة الفارغة (ثوانٍ)</label>
                    <input type="number" id="emptyTimeoutSec" class="admin-control" wire:model="emptyTimeoutSec" min="60" max="3600">
                    <div class="admin-field-hint">الافتراضي السابق كان 1800 ثانية. 300 ثانية يوقف دقائق LiveKit بعد خروج الجميع.</div>
                </div>
            </div>
            <div class="admin-form-grid admin-form-grid--2" style="margin-top:1rem;">
                <label class="zoom-switch">
                    <input type="checkbox" wire:model.live="allowScreenShare">
                    <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                    <span class="zoom-switch__label">السماح بمشاركة الشاشة</span>
                </label>
                <label class="zoom-switch">
                    <input type="checkbox" wire:model.live="allowStudentCamera">
                    <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                    <span class="zoom-switch__label">كاميرات الطلاب (مسارات فيديو إضافية)</span>
                </label>
                <label class="zoom-switch">
                    <input type="checkbox" wire:model.live="adaptiveStream">
                    <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                    <span class="zoom-switch__label">Adaptive Stream (توفير عرض نطاق)</span>
                </label>
                <label class="zoom-switch">
                    <input type="checkbox" wire:model.live="dynacast">
                    <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                    <span class="zoom-switch__label">Dynacast (إيقاف الطبقات غير المستخدمة)</span>
                </label>
                <label class="zoom-switch">
                    <input type="checkbox" wire:model.live="autoDispatchAgents">
                    <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                    <span class="zoom-switch__label">إدخال وكلاء AI للقاعة (مشارك LiveKit إضافي)</span>
                </label>
            </div>
        </section>

        <section class="admin-crud-card admin-crud-card--filter" style="margin-top:1.25rem;">
            <div class="admin-crud-card__head">
                <h2>تخزين التسجيلات</h2>
                <p class="admin-crud-card__meta">مثل خبراء الأداء: سحابة ZoxAgent أو حاوية S3 الخاصة بالمنصة (BYO).</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <div class="admin-field">
                    <label for="recordingStorageMode">وضع التخزين</label>
                    <select id="recordingStorageMode" class="admin-control" wire:model.live="recordingStorageMode">
                        <option value="managed">سحابة ZoxAgent</option>
                        <option value="byo">كلاود المنصة (S3)</option>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="s3Label">تسمية الإعداد</label>
                    <input type="text" id="s3Label" class="admin-control" wire:model="s3Label">
                </div>
                @if ($recordingStorageMode === 'byo')
                    <div class="admin-field">
                        <label for="s3Bucket">Bucket</label>
                        <input type="text" id="s3Bucket" class="admin-control" wire:model="s3Bucket" dir="ltr">
                        @error('s3Bucket')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="s3Region">Region</label>
                        <input type="text" id="s3Region" class="admin-control" wire:model="s3Region" dir="ltr">
                    </div>
                    <div class="admin-field">
                        <label for="s3Endpoint">Endpoint (اختياري)</label>
                        <input type="text" id="s3Endpoint" class="admin-control" wire:model="s3Endpoint" dir="ltr">
                    </div>
                    <div class="admin-field">
                        <label for="s3PublicBaseUrl">رابط عام للملفات</label>
                        <input type="url" id="s3PublicBaseUrl" class="admin-control" wire:model="s3PublicBaseUrl" dir="ltr">
                    </div>
                    <div class="admin-field">
                        <label for="s3AccessKey">Access key</label>
                        <input type="password" id="s3AccessKey" class="admin-control" wire:model="s3AccessKey" dir="ltr" autocomplete="new-password" placeholder="{{ $hasStoredS3Credentials ? 'محفوظ — اتركه فارغاً للإبقاء' : '' }}">
                        @error('s3AccessKey')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="s3SecretKey">Secret key</label>
                        <input type="password" id="s3SecretKey" class="admin-control" wire:model="s3SecretKey" dir="ltr" autocomplete="new-password">
                    </div>
                    <label class="zoom-switch">
                        <input type="checkbox" wire:model.live="s3ForcePathStyle">
                        <span class="zoom-switch__track"><span class="zoom-switch__thumb"></span></span>
                        <span class="zoom-switch__label">Force path style</span>
                    </label>
                @endif
            </div>
        </section>

        <section class="admin-crud-card admin-crud-card--filter" style="margin-top:1.25rem;">
            <div class="admin-crud-card__head">
                <h2>ويب هوك المنصة</h2>
                <p class="admin-crud-card__meta">يُسجَّل تلقائياً عند الحفظ. يستقبل حضور الانضمام وتجهيز التسجيلات وإنهاء القاعة.</p>
            </div>
            <div class="admin-field">
                <label>عنوان الاستقبال</label>
                <input type="text" class="admin-control" dir="ltr" readonly value="{{ $webhookInboundUrl }}">
                <div class="admin-field-hint">{{ $hasWebhookSecret ? 'سر التوقيع محفوظ مشفّراً.' : 'سيُحفظ السر بعد أول ربط ناجح مع ZoxAgent.' }}</div>
            </div>
        </section>

        <div class="admin-crud-card__actions" style="margin-top: 1.25rem; display: flex; gap: .75rem; flex-wrap: wrap;">
            <button type="button" class="admin-btn-primary" wire:click="save">حفظ الإعدادات</button>
            <button type="button" class="admin-btn-secondary" wire:click="testConnection" wire:loading.attr="disabled">اختبار الاتصال</button>
            @if ($recordingStorageMode === 'byo')
                <button type="button" class="admin-btn-secondary" wire:click="testStorage" wire:loading.attr="disabled">اختبار S3</button>
            @endif
        </div>
    </section>
</div>
