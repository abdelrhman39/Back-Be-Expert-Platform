<?php

use App\Support\RecordingOptions;
use App\Support\RecordingSettings;
use App\Support\TeamsSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'إعدادات Microsoft Teams',
    'adminPageDesc' => 'ربط المنصة مع Teams للمحاضرات والحضور التلقائي',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'Microsoft Teams'],
    ],
])]
#[Title('Microsoft Teams | لوحة التحكم')]
class extends Component
{
    public bool $teamsEnabled = false;

    public string $tenantId = '';

    public string $clientId = '';

    public string $clientSecret = '';

    public string $organizerUserId = '';

    public bool $autoAttendance = true;

    public int $syncIntervalMinutes = 15;

    public bool $autoRecord = true;

    public string $recordingPublishMode = 'manual';

    public int $recordingAutoPublishHours = 24;

    public int $recordingRetentionDays = 365;

    public bool $recordingAllowDownload = false;

    public string $recordingAccessPolicy = 'enrolled_only';

    public bool $hasStoredClientSecret = false;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $this->teamsEnabled = TeamsSettings::isEnabled();
        $this->tenantId = TeamsSettings::tenantId() ?? '';
        $this->clientId = TeamsSettings::clientId() ?? '';
        $this->organizerUserId = TeamsSettings::organizerUserId() ?? '';
        $this->autoAttendance = TeamsSettings::autoAttendanceEnabled();
        $this->syncIntervalMinutes = TeamsSettings::syncIntervalMinutes();
        $this->autoRecord = RecordingSettings::autoRecordEnabled();
        $this->recordingPublishMode = RecordingSettings::publishMode();
        $this->recordingAutoPublishHours = RecordingSettings::autoPublishHours();
        $this->recordingRetentionDays = RecordingSettings::retentionDays();
        $this->recordingAllowDownload = RecordingSettings::allowDownload();
        $this->recordingAccessPolicy = RecordingSettings::accessPolicy();
        $this->hasStoredClientSecret = TeamsSettings::hasStoredClientSecret();
        $this->clientSecret = '';
    }

    public function save(): void
    {
        $this->validate([
            'tenantId' => ['required_if:teamsEnabled,true', 'nullable', 'string', 'max:64'],
            'clientId' => ['required_if:teamsEnabled,true', 'nullable', 'string', 'max:64'],
            'clientSecret' => ['nullable', 'string', 'max:255'],
            'organizerUserId' => ['required_if:teamsEnabled,true', 'nullable', 'string', 'max:64'],
            'syncIntervalMinutes' => ['required', 'integer', 'min:5', 'max:120'],
            'recordingPublishMode' => ['required', 'in:manual,auto_delayed'],
            'recordingAutoPublishHours' => ['required', 'integer', 'min:1', 'max:168'],
            'recordingRetentionDays' => ['required', 'integer', 'min:30', 'max:1825'],
            'recordingAccessPolicy' => ['required', 'in:enrolled_only,attended_only'],
        ], [], [
            'tenantId' => 'Azure Tenant ID',
            'clientId' => 'Client ID (Application ID)',
            'clientSecret' => 'Client Secret',
            'organizerUserId' => 'Organizer User ID',
            'syncIntervalMinutes' => 'فترة المزامنة',
        ]);

        if ($this->teamsEnabled && ! $this->hasStoredClientSecret && blank($this->clientSecret)) {
            $this->addError('clientSecret', 'Client Secret مطلوب عند تفعيل Teams.');

            return;
        }

        TeamsSettings::setEnabled($this->teamsEnabled);
        TeamsSettings::setTenantId($this->tenantId);
        TeamsSettings::setClientId($this->clientId);
        TeamsSettings::setClientSecret($this->clientSecret);
        TeamsSettings::setOrganizerUserId($this->organizerUserId);
        TeamsSettings::setAutoAttendance($this->autoAttendance);
        TeamsSettings::setSyncIntervalMinutes($this->syncIntervalMinutes);
        RecordingSettings::setAutoRecord($this->autoRecord);
        RecordingSettings::setPublishMode($this->recordingPublishMode);
        RecordingSettings::setAutoPublishHours($this->recordingAutoPublishHours);
        RecordingSettings::setRetentionDays($this->recordingRetentionDays);
        RecordingSettings::setAllowDownload($this->recordingAllowDownload);
        RecordingSettings::setAccessPolicy($this->recordingAccessPolicy);

        $this->hasStoredClientSecret = TeamsSettings::hasStoredClientSecret();
        $this->clientSecret = '';
        $this->savedMessage = 'تم حفظ إعدادات Microsoft Teams بنجاح.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.teams-settings'),
    'shellActiveHeader' => 'settings',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'Microsoft Teams'],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible" role="status">{{ $savedMessage }}</div>
@endif

<div class="teams-settings">
    <section class="teams-hero">
        <div class="teams-hero__main">
            <div class="teams-hero__icon"><i class="fa-brands fa-microsoft"></i></div>
            <div>
                <span class="teams-hero__eyebrow">تكامل المحاضرات والحضور</span>
                <h1>إعدادات Microsoft Teams</h1>
                <p>إنشاء اجتماعات المحاضرات تلقائياً، سحب الحضور من تقارير Teams، ومزامنة التسجيلات ونشرها للطلاب وفق سياساتك.</p>
                <div class="teams-hero__badges">
                    @if (TeamsSettings::isConfigured() && $teamsEnabled)
                        <span class="teams-pill teams-pill--ok"><i class="fa-solid fa-circle-check"></i> مفعّل وجاهز</span>
                    @elseif ($teamsEnabled)
                        <span class="teams-pill teams-pill--warn"><i class="fa-solid fa-triangle-exclamation"></i> مفعّل — ينقص إكمال البيانات</span>
                    @else
                        <span class="teams-pill teams-pill--off"><i class="fa-solid fa-circle-pause"></i> معطّل</span>
                    @endif
                    <span class="teams-pill"><i class="fa-solid fa-user-check"></i> الحضور: {{ $autoAttendance ? 'تلقائي' : 'يدوي' }}</span>
                    <span class="teams-pill"><i class="fa-solid fa-circle-play"></i> التسجيل: {{ $autoRecord ? 'تلقائي' : 'معطّل' }}</span>
                </div>
            </div>
        </div>
        <div class="teams-hero__side">
            <label class="teams-switch">
                <input type="checkbox" wire:model.live="teamsEnabled">
                <span class="teams-switch__track"><span class="teams-switch__thumb"></span></span>
                <span class="teams-switch__label">تفعيل التكامل</span>
            </label>
            <button type="button" class="teams-hero__guide" onclick="document.getElementById('teams-azure-guide-anchor')?.scrollIntoView({behavior:'smooth'})">
                <i class="fa-solid fa-book-open"></i> دليل إعداد Azure
            </button>
        </div>
    </section>

    <section class="teams-workflow" aria-label="آلية عمل تكامل Teams">
        <article><span>1</span><i class="fa-solid fa-plug"></i><div><strong>ربط Azure AD</strong><small>Tenant وApplication وSecret</small></div></article>
        <i class="fa-solid fa-arrow-left"></i>
        <article><span>2</span><i class="fa-solid fa-calendar-plus"></i><div><strong>إنشاء الاجتماعات</strong><small>تلقائياً لكل محاضرة مجدولة</small></div></article>
        <i class="fa-solid fa-arrow-left"></i>
        <article><span>3</span><i class="fa-solid fa-user-check"></i><div><strong>مزامنة الحضور</strong><small>من تقارير الاجتماع دورياً</small></div></article>
        <i class="fa-solid fa-arrow-left"></i>
        <article><span>4</span><i class="fa-solid fa-circle-play"></i><div><strong>التسجيلات</strong><small>سحب ونشر وفق السياسة</small></div></article>
    </section>

    <section class="teams-card">
        <header class="teams-card__head">
            <div class="teams-card__icon is-connect"><i class="fa-solid fa-key"></i></div>
            <div>
                <h2>بيانات الاتصال بـ Azure</h2>
                <p>من Azure AD → App registrations. تُحفظ الأسرار مشفّرة ولا تُعرض بعد الحفظ.</p>
            </div>
        </header>

        <div class="teams-card__body">
            <div class="teams-form-grid">
                <div class="admin-field">
                    <label for="tenantId">Azure Tenant ID</label>
                    <input type="text" id="tenantId" class="admin-control" wire:model="tenantId" dir="ltr" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" autocomplete="off">
                    @error('tenantId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>

                <div class="admin-field">
                    <label for="clientId">Application (Client) ID</label>
                    <input type="text" id="clientId" class="admin-control" wire:model="clientId" dir="ltr" autocomplete="off">
                    @error('clientId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>

                <div class="admin-field">
                    <label for="clientSecret">Client Secret @if ($hasStoredClientSecret)<span class="teams-secret-badge"><i class="fa-solid fa-lock"></i> محفوظ ومشفّر</span>@endif</label>
                    <input type="password"
                        id="clientSecret"
                        class="admin-control"
                        wire:model="clientSecret"
                        dir="ltr"
                        placeholder="{{ $hasStoredClientSecret ? '••••••••  (اتركه فارغاً للإبقاء)' : 'Secret Value' }}"
                        autocomplete="new-password">
                    @if ($hasStoredClientSecret)
                        <div class="admin-field-hint">اترك الحقل فارغاً إن لم ترد تغييره.</div>
                    @endif
                    @error('clientSecret')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>

                <div class="admin-field">
                    <label for="organizerUserId">Organizer User ID (Object ID)</label>
                    <input type="text" id="organizerUserId" class="admin-control" wire:model="organizerUserId" dir="ltr" placeholder="معرّف المستخدم المنظم للاجتماعات">
                    <div class="admin-field-hint">حساب Microsoft 365 الذي يُنشئ الاجتماعات ويُقرأ منه تقرير الحضور.</div>
                    @error('organizerUserId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="teams-copy-block">
                <div>
                    <strong>Redirect URI</strong>
                    <small>أضفه في Azure AD → Authentication</small>
                </div>
                <div class="teams-copy-block__field">
                    <input type="text" readonly dir="ltr" value="{{ TeamsSettings::redirectUri() }}" onclick="this.select()">
                    <button type="button" class="js-teams-copy" onclick="navigator.clipboard.writeText(this.dataset.copy); this.classList.add('is-copied'); setTimeout(() => this.classList.remove('is-copied'), 1500)" data-copy="{{ TeamsSettings::redirectUri() }}"><i class="fa-regular fa-copy"></i> نسخ</button>
                </div>
            </div>
        </div>
    </section>

    <div class="teams-grid-2">
        <section class="teams-card">
            <header class="teams-card__head">
                <div class="teams-card__icon is-attendance"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <h2>الحضور التلقائي</h2>
                    <p>تُطابَق سجلات الحضور مع الطلاب عبر البريد المرتبط بحساب Microsoft.</p>
                </div>
            </header>

            <div class="teams-card__body">
                <label class="teams-toggle">
                    <input type="checkbox" wire:model="autoAttendance">
                    <span class="teams-toggle__switch"></span>
                    <span><strong>مزامنة الحضور تلقائياً</strong><small>سحب تقارير الاجتماع وتسجيل الحضور والغياب دون تدخل يدوي.</small></span>
                </label>

                <div class="admin-field teams-field-narrow">
                    <label for="syncIntervalMinutes">فترة المزامنة (دقائق)</label>
                    <input type="number" id="syncIntervalMinutes" class="admin-control" wire:model="syncIntervalMinutes" min="5" max="120">
                    @error('syncIntervalMinutes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>

                <div class="teams-copy-block">
                    <div>
                        <strong>المزامنة اليدوية</strong>
                        <small>تعمل تلقائياً كل 15 دقيقة عبر المجدول</small>
                    </div>
                    <div class="teams-copy-block__field">
                        <input type="text" readonly dir="ltr" value="php artisan teams:sync-attendance" onclick="this.select()">
                        <button type="button" class="js-teams-copy" onclick="navigator.clipboard.writeText(this.dataset.copy); this.classList.add('is-copied'); setTimeout(() => this.classList.remove('is-copied'), 1500)" data-copy="php artisan teams:sync-attendance"><i class="fa-regular fa-copy"></i> نسخ</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="teams-card">
            <header class="teams-card__head">
                <div class="teams-card__icon is-recording"><i class="fa-solid fa-circle-play"></i></div>
                <div>
                    <h2>تسجيل المحاضرات</h2>
                    <p>تسجيل سحابي تلقائي، مزامنة عبر Graph، ونشر للطلاب وفق السياسة.</p>
                </div>
            </header>

            <div class="teams-card__body">
                <label class="teams-toggle">
                    <input type="checkbox" wire:model="autoRecord">
                    <span class="teams-toggle__switch"></span>
                    <span><strong>تسجيل تلقائي</strong><small>يبدأ التسجيل عند إنشاء اجتماع المحاضرة.</small></span>
                </label>

                <div class="teams-form-grid">
                    <div class="admin-field">
                        <label for="recordingPublishMode">وضع النشر</label>
                        <select id="recordingPublishMode" class="admin-control" wire:model="recordingPublishMode">
                            @foreach (RecordingOptions::publishModes() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="recordingAutoPublishHours">تأخير النشر التلقائي (ساعات)</label>
                        <input type="number" id="recordingAutoPublishHours" class="admin-control" wire:model="recordingAutoPublishHours" min="1" max="168">
                        @error('recordingAutoPublishHours')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="recordingRetentionDays">مدة الاحتفاظ (أيام)</label>
                        <input type="number" id="recordingRetentionDays" class="admin-control" wire:model="recordingRetentionDays" min="30">
                        @error('recordingRetentionDays')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label for="recordingAccessPolicy">من يرى التسجيل؟</label>
                        <select id="recordingAccessPolicy" class="admin-control" wire:model="recordingAccessPolicy">
                            @foreach (RecordingOptions::accessPolicies() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="teams-toggle teams-toggle--warn">
                    <input type="checkbox" wire:model="recordingAllowDownload">
                    <span class="teams-toggle__switch"></span>
                    <span><strong>السماح بتحميل التسجيل</strong><small>غير موصى به — يقلل التحكم في تداول المحتوى.</small></span>
                </label>

                <div class="teams-copy-block">
                    <div>
                        <strong>مزامنة التسجيلات</strong>
                        <small>كل 30 دقيقة — تتطلب صلاحية <code>OnlineMeetingRecording.Read.All</code></small>
                    </div>
                    <div class="teams-copy-block__field">
                        <input type="text" readonly dir="ltr" value="php artisan teams:sync-recordings" onclick="this.select()">
                        <button type="button" class="js-teams-copy" onclick="navigator.clipboard.writeText(this.dataset.copy); this.classList.add('is-copied'); setTimeout(() => this.classList.remove('is-copied'), 1500)" data-copy="php artisan teams:sync-recordings"><i class="fa-regular fa-copy"></i> نسخ</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div id="teams-azure-guide-anchor"></div>
    @include('partials.admin.teams-azure-guide')

    <div class="teams-savebar">
        <div class="teams-savebar__hint">
            <i class="fa-solid fa-shield-halved"></i>
            تُحفظ الأسرار مشفّرة، ويُسجل أي تغيير في سجل التدقيق.
        </div>
        <div class="teams-savebar__actions">
            <a href="{{ route('admin.settings') }}">إعدادات المنصة</a>
            <a href="{{ route('admin.zoom-settings') }}">إعدادات Zoom</a>
            <button type="button" class="teams-savebar__save" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk"></i> حفظ الإعدادات</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
        </div>
    </div>
</div>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .teams-settings { display: flex; flex-direction: column; gap: 1.1rem; }

    /* ---- Hero ---- */
    .teams-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        flex-wrap: wrap;
        background: linear-gradient(135deg, #464eb8 0%, #7b83eb 55%, #505ac9 100%);
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        color: #fff;
        box-shadow: 0 10px 30px rgba(70, 78, 184, 0.28);
    }
    .teams-hero__main { display: flex; gap: 1.1rem; align-items: flex-start; min-width: 0; }
    .teams-hero__icon {
        flex: 0 0 auto;
        width: 3.4rem; height: 3.4rem;
        display: grid; place-items: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 14px;
        font-size: 1.5rem;
    }
    .teams-hero__eyebrow {
        display: inline-block;
        font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 0.2rem;
    }
    .teams-hero h1 { margin: 0 0 0.35rem; font-size: 1.35rem; font-weight: 800; color: #fff; }
    .teams-hero p { margin: 0 0 0.75rem; font-size: 0.88rem; color: rgba(255, 255, 255, 0.85); max-width: 46rem; line-height: 1.7; }
    .teams-hero__badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .teams-pill {
        display: inline-flex; align-items: center; gap: 0.35rem;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        padding: 0.28rem 0.75rem;
        font-size: 0.76rem; font-weight: 700;
        color: #fff;
    }
    .teams-pill--ok { background: rgba(34, 197, 94, 0.3); border-color: rgba(134, 239, 172, 0.5); }
    .teams-pill--warn { background: rgba(245, 158, 11, 0.32); border-color: rgba(253, 230, 138, 0.5); }
    .teams-pill--off { background: rgba(100, 116, 139, 0.35); border-color: rgba(203, 213, 225, 0.35); }
    .teams-hero__side { display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end; }
    .teams-switch { display: inline-flex; align-items: center; gap: 0.6rem; cursor: pointer; user-select: none; }
    .teams-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .teams-switch__track {
        width: 3rem; height: 1.6rem;
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: 999px;
        position: relative;
        transition: background 0.2s ease;
    }
    .teams-switch__thumb {
        position: absolute; top: 2px; inset-inline-start: 3px;
        width: 1.2rem; height: 1.2rem;
        background: #fff; border-radius: 50%;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
    }
    .teams-switch input:checked + .teams-switch__track { background: #22c55e; border-color: #4ade80; }
    .teams-switch input:checked + .teams-switch__track .teams-switch__thumb { transform: translateX(-1.35rem); }
    .teams-switch__label { font-size: 0.85rem; font-weight: 800; color: #fff; }
    .teams-hero__guide {
        display: inline-flex; align-items: center; gap: 0.45rem;
        background: rgba(255, 255, 255, 0.95);
        color: #464eb8;
        border: none; border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.82rem; font-weight: 800;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .teams-hero__guide:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18); }

    /* ---- Workflow strip ---- */
    .teams-workflow {
        display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;
        background: #fff;
        border: 1px solid var(--sa-border, #e2e8f0);
        border-radius: 14px;
        padding: 0.9rem 1.1rem;
    }
    .teams-workflow > i { color: #cbd5e1; font-size: 0.8rem; }
    .teams-workflow article {
        display: flex; align-items: center; gap: 0.55rem;
        flex: 1; min-width: 11rem;
        position: relative;
        padding: 0.4rem 0.5rem;
    }
    .teams-workflow article > span {
        position: absolute; top: -0.15rem; inset-inline-start: 0.1rem;
        font-size: 0.62rem; font-weight: 800; color: #94a3b8;
    }
    .teams-workflow article > i {
        width: 2.2rem; height: 2.2rem;
        display: grid; place-items: center;
        border-radius: 10px;
        background: #eef2ff; color: #4f46e5;
        font-size: 0.95rem;
        flex: 0 0 auto;
    }
    .teams-workflow article strong { display: block; font-size: 0.82rem; font-weight: 800; color: var(--sa-ink, #1a1a1a); }
    .teams-workflow article small { display: block; font-size: 0.72rem; color: #64748b; }

    /* ---- Cards ---- */
    .teams-card {
        background: #fff;
        border: 1px solid var(--sa-border, #e2e8f0);
        border-radius: 14px;
        overflow: hidden;
    }
    .teams-card__head {
        display: flex; gap: 0.85rem; align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--sa-border, #e2e8f0);
        background: #f8fafc;
    }
    .teams-card__head h2 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--sa-ink, #1a1a1a); }
    .teams-card__head p { margin: 0.15rem 0 0; font-size: 0.8rem; color: #64748b; }
    .teams-card__icon {
        flex: 0 0 auto;
        width: 2.6rem; height: 2.6rem;
        display: grid; place-items: center;
        border-radius: 12px;
        font-size: 1.05rem;
    }
    .teams-card__icon.is-connect { background: #eef2ff; color: #4f46e5; }
    .teams-card__icon.is-attendance { background: #ecfdf5; color: #059669; }
    .teams-card__icon.is-recording { background: #fef2f2; color: #dc2626; }
    .teams-card__body { padding: 1.25rem; display: flex; flex-direction: column; gap: 1.1rem; }

    .teams-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.1rem;
        align-items: start;
    }
    @media (max-width: 991.98px) {
        .teams-grid-2 { grid-template-columns: 1fr; }
    }

    .teams-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem 1rem;
    }
    @media (max-width: 767.98px) {
        .teams-form-grid { grid-template-columns: 1fr; }
    }
    .teams-field-narrow { max-width: 14rem; }

    .teams-secret-badge {
        display: inline-flex; align-items: center; gap: 0.3rem;
        margin-inline-start: 0.45rem;
        background: #ecfdf5; color: #059669;
        border-radius: 999px;
        padding: 0.12rem 0.55rem;
        font-size: 0.68rem; font-weight: 800;
    }

    /* ---- Toggle rows ---- */
    .teams-toggle {
        display: flex; align-items: flex-start; gap: 0.75rem;
        background: #f8fafc;
        border: 1px solid var(--sa-border, #e2e8f0);
        border-radius: 12px;
        padding: 0.8rem 0.95rem;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .teams-toggle:hover { border-color: #c7d2fe; background: #fbfcff; }
    .teams-toggle--warn:hover { border-color: #fde68a; background: #fffdf5; }
    .teams-toggle input { position: absolute; opacity: 0; pointer-events: none; }
    .teams-toggle__switch {
        flex: 0 0 auto;
        width: 2.6rem; height: 1.4rem;
        background: #cbd5e1;
        border-radius: 999px;
        position: relative;
        margin-top: 0.15rem;
        transition: background 0.2s ease;
    }
    .teams-toggle__switch::after {
        content: '';
        position: absolute; top: 2px; inset-inline-start: 3px;
        width: 1.05rem; height: 1.05rem;
        background: #fff; border-radius: 50%;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    }
    .teams-toggle input:checked + .teams-toggle__switch { background: #4f46e5; }
    .teams-toggle--warn input:checked + .teams-toggle__switch { background: #d97706; }
    .teams-toggle input:checked + .teams-toggle__switch::after { transform: translateX(-1.15rem); }
    .teams-toggle strong { display: block; font-size: 0.86rem; font-weight: 800; color: var(--sa-ink, #1a1a1a); }
    .teams-toggle small { display: block; font-size: 0.74rem; color: #64748b; line-height: 1.6; }

    /* ---- Copy blocks ---- */
    .teams-copy-block {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 0.8rem 0.95rem;
        display: flex; flex-direction: column; gap: 0.55rem;
    }
    .teams-copy-block strong { font-size: 0.82rem; font-weight: 800; color: var(--sa-ink, #1a1a1a); }
    .teams-copy-block small { display: block; font-size: 0.73rem; color: #64748b; }
    .teams-copy-block small code { background: #e2e8f0; padding: 0.05rem 0.3rem; border-radius: 4px; font-size: 0.7rem; }
    .teams-copy-block__field { display: flex; gap: 0.5rem; }
    .teams-copy-block__field input {
        flex: 1; min-width: 0;
        border: 1px solid var(--sa-border, #e2e8f0);
        border-radius: 8px;
        background: #fff;
        padding: 0.45rem 0.65rem;
        font-size: 0.78rem;
        font-family: ui-monospace, monospace;
        color: #334155;
    }
    .teams-copy-block__field button {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; gap: 0.35rem;
        border: 1px solid #c7d2fe;
        background: #eef2ff; color: #4f46e5;
        border-radius: 8px;
        padding: 0.45rem 0.85rem;
        font-size: 0.76rem; font-weight: 800;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .teams-copy-block__field button:hover { background: #e0e7ff; }
    .teams-copy-block__field button.is-copied { background: #dcfce7; border-color: #86efac; color: #16a34a; }

    /* ---- Save bar ---- */
    .teams-savebar {
        position: sticky; bottom: 0.75rem; z-index: 5;
        display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
        background: rgba(15, 23, 42, 0.94);
        backdrop-filter: blur(6px);
        border-radius: 14px;
        padding: 0.8rem 1.1rem;
        color: #e2e8f0;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.35);
    }
    .teams-savebar__hint { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: #94a3b8; }
    .teams-savebar__hint i { color: #4ade80; }
    .teams-savebar__actions { display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }
    .teams-savebar__actions a {
        color: #cbd5e1; font-size: 0.78rem; font-weight: 700;
        text-decoration: none;
        padding: 0.45rem 0.75rem;
        border-radius: 8px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        transition: background 0.15s ease;
    }
    .teams-savebar__actions a:hover { background: rgba(148, 163, 184, 0.15); color: #fff; }
    .teams-savebar__save {
        display: inline-flex; align-items: center; gap: 0.45rem;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        border: none; border-radius: 10px;
        padding: 0.55rem 1.35rem;
        font-size: 0.85rem; font-weight: 800;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .teams-savebar__save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45); }
    .teams-savebar__save:disabled { opacity: 0.7; cursor: wait; transform: none; }

    @media (max-width: 767.98px) {
        .teams-hero { padding: 1.15rem; }
        .teams-hero__side { align-items: flex-start; }
        .teams-workflow > i { display: none; }
        .teams-workflow article { min-width: calc(50% - 0.6rem); }
    }

    /* ---- Azure guide (included partial) ---- */
    .admin-copy-field {
        display: flex;
        gap: 0.5rem;
        align-items: stretch;
    }
    .admin-copy-field .admin-control { flex: 1; }
    .teams-setup-steps {
        margin: 0;
        padding-inline-start: 1.25rem;
        line-height: 1.7;
        color: #475569;
        font-size: 0.88rem;
    }
    .teams-setup-steps code {
        background: #f1f5f9;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        font-size: 0.82rem;
    }
    .teams-azure-guide { margin-top: 0; }
    .teams-guide-block {
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--sa-border, #e2e8f0);
    }
    .teams-guide-block:last-child { border-bottom: none; margin-bottom: 0; }
    .teams-guide-block h3 { font-size: 0.95rem; font-weight: 800; margin: 0 0 0.5rem; color: var(--sa-ink, #1a1a1a); }
    .teams-guide-block h4.teams-guide-sub { font-size: 0.85rem; font-weight: 700; margin: 0.75rem 0 0.35rem; }
    .teams-guide-block ul, .teams-guide-block ol { margin: 0; padding-inline-start: 1.25rem; line-height: 1.75; color: #475569; font-size: 0.88rem; }
    .teams-guide-block li { margin-bottom: 0.25rem; }
    .teams-guide-block--info { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 1rem; border-bottom: none; }
    .teams-guide-block--warn { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 1rem; border-bottom: none; }
    .teams-guide-note { margin: 0.5rem 0 0; font-size: 0.82rem; color: #64748b; }
    .teams-guide-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; margin: 0.5rem 0; }
    .teams-guide-table th, .teams-guide-table td { border: 1px solid #e2e8f0; padding: 0.45rem 0.65rem; text-align: start; }
    .teams-guide-table th { background: #f8fafc; font-weight: 700; }
    .teams-guide-table code { background: #f1f5f9; padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.78rem; }
</style>
@endpush
