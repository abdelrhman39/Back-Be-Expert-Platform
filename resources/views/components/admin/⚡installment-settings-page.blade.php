<?php

use App\Models\InstallmentContract;
use App\Models\InstallmentPayment;
use App\Models\InstallmentSchedule;
use App\Models\PlatformSetting;
use App\Support\InstallmentSettings;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'إعدادات التقسيط',
    'adminPageDesc' => 'التحكم في التذكيرات والإيقاف ورسوم التأخير والتسجيل والتوقيع',
    'adminLayout' => 'app',
])]
#[Title('إعدادات التقسيط | لوحة التحكم')]
class extends Component
{
    public bool $remindersEnabled = true;

    public string $reminderDays = '7,3,1';

    public string $reminderTime = '08:00';

    public bool $suspensionEnabled = true;

    public int $graceDays = 7;

    public int $suspendAfterDays = 14;

    public string $overdueTime = '09:00';

    public bool $lateFeesEnabled = false;

    public string $lateFeeMode = 'percent';

    public float $lateFeePercent = 2;

    public float $lateFeeFixed = 50;

    public float $lateFeeMaxCap = 0;

    public int $lateFeeApplyAfterDays = 3;

    public bool $checkoutEnabled = true;

    public bool $academicRegistrationEnabled = true;

    public bool $requiresSignature = true;

    public ?string $savedMessage = null;

    public ?string $actionMessage = null;

    public string $flashType = 'success';

    public int $flashKey = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);
        $this->loadSettings();
    }

    public function dismissFlash(): void
    {
        $this->savedMessage = null;
        $this->actionMessage = null;
    }

    protected function flash(string $message, string $type = 'success', string $channel = 'saved'): void
    {
        if ($channel === 'action') {
            $this->actionMessage = $message;
            $this->savedMessage = null;
        } else {
            $this->savedMessage = $message;
            $this->actionMessage = null;
        }
        $this->flashType = $type;
        $this->flashKey++;
    }

    protected function loadSettings(): void
    {
        $this->remindersEnabled = InstallmentSettings::remindersEnabled();
        $this->reminderDays = implode(',', InstallmentSettings::reminderDaysBefore());
        $this->reminderTime = InstallmentSettings::reminderDispatchTime();
        $this->suspensionEnabled = InstallmentSettings::suspensionEnabled();
        $this->graceDays = InstallmentSettings::graceDays();
        $this->suspendAfterDays = InstallmentSettings::suspendAfterDays();
        $this->overdueTime = InstallmentSettings::overdueProcessTime();
        $this->lateFeesEnabled = InstallmentSettings::lateFeesEnabled();
        $this->lateFeeMode = InstallmentSettings::lateFeeMode();
        $this->lateFeePercent = InstallmentSettings::lateFeePercent();
        $this->lateFeeFixed = InstallmentSettings::lateFeeFixed();
        $this->lateFeeMaxCap = InstallmentSettings::lateFeeMaxCap();
        $this->lateFeeApplyAfterDays = InstallmentSettings::lateFeeApplyAfterDays();
        $this->checkoutEnabled = InstallmentSettings::checkoutEnabled();
        $this->academicRegistrationEnabled = InstallmentSettings::academicRegistrationEnabled();
        $this->requiresSignature = InstallmentSettings::requiresSignature();
    }

    /** @return array<string, int|float> */
    public function getStatsProperty(): array
    {
        return [
            'active' => InstallmentContract::query()->where('status', 'active')->count(),
            'suspended' => InstallmentContract::query()->where('status', 'suspended')->count(),
            'overdue' => InstallmentSchedule::query()->where('status', 'overdue')->count(),
            'collected_month' => (float) InstallmentPayment::query()
                ->where('status', 'success')
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'reminderDays' => ['required', 'regex:/^(\d+\s*,\s*)*\d+$/'],
            'reminderTime' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'graceDays' => ['required', 'integer', 'min:0', 'max:90'],
            'suspendAfterDays' => ['required', 'integer', 'min:1', 'max:180'],
            'overdueTime' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'lateFeeMode' => ['required', 'in:percent,fixed'],
            'lateFeePercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'lateFeeFixed' => ['required', 'numeric', 'min:0', 'max:100000'],
            'lateFeeMaxCap' => ['required', 'numeric', 'min:0', 'max:100000'],
            'lateFeeApplyAfterDays' => ['required', 'integer', 'min:0', 'max:180'],
        ], [], [
            'reminderDays' => 'أيام التذكير',
            'reminderTime' => 'وقت التذكير',
            'graceDays' => 'أيام السماح',
            'suspendAfterDays' => 'أيام الإيقاف',
            'overdueTime' => 'وقت معالجة المتأخرات',
            'lateFeeMode' => 'طريقة احتساب رسوم التأخير',
            'lateFeePercent' => 'نسبة رسوم التأخير',
            'lateFeeFixed' => 'قيمة رسوم التأخير الثابتة',
            'lateFeeMaxCap' => 'الحد الأقصى لرسوم التأخير',
            'lateFeeApplyAfterDays' => 'تطبيق الرسوم بعد',
        ]);

        $normalizedDays = collect(explode(',', $this->reminderDays))
            ->map(fn ($d) => (int) trim($d))
            ->filter(fn ($d) => $d > 0)
            ->unique()
            ->sortDesc()
            ->implode(',');

        PlatformSetting::set('installment_reminders_enabled', $this->remindersEnabled ? '1' : '0', 'finance', 'تفعيل تذكيرات الأقساط');
        PlatformSetting::set('installment_reminder_days', $normalizedDays ?: '7,3,1', 'finance', 'تذكير قبل الاستحقاق (أيام)');
        PlatformSetting::set('installment_reminder_time', $this->reminderTime, 'finance', 'وقت إرسال التذكيرات');
        PlatformSetting::set('installment_suspension_enabled', $this->suspensionEnabled ? '1' : '0', 'finance', 'تفعيل إيقاف الالتحاق');
        PlatformSetting::set('installment_grace_days', (string) $this->graceDays, 'finance', 'أيام السماح');
        PlatformSetting::set('installment_suspend_after_days', (string) $this->suspendAfterDays, 'finance', 'إيقاف الالتحاق بعد');
        PlatformSetting::set('installment_overdue_time', $this->overdueTime, 'finance', 'وقت معالجة المتأخرات');
        PlatformSetting::set('installment_late_fees_enabled', $this->lateFeesEnabled ? '1' : '0', 'finance', 'تفعيل رسوم التأخير');
        PlatformSetting::set('installment_late_fee_mode', $this->lateFeeMode, 'finance', 'طريقة احتساب رسوم التأخير');
        PlatformSetting::set('installment_late_fee_percent', (string) $this->lateFeePercent, 'finance', 'نسبة رسوم التأخير');
        PlatformSetting::set('installment_late_fee_fixed', (string) $this->lateFeeFixed, 'finance', 'قيمة رسوم التأخير الثابتة');
        PlatformSetting::set('installment_late_fee_max_cap', (string) $this->lateFeeMaxCap, 'finance', 'الحد الأقصى لرسوم التأخير');
        PlatformSetting::set('installment_late_fee_apply_after_days', (string) $this->lateFeeApplyAfterDays, 'finance', 'تطبيق رسوم التأخير بعد');
        PlatformSetting::set('installment_checkout_enabled', $this->checkoutEnabled ? '1' : '0', 'finance', 'تقسيط صفحة الدفع');
        PlatformSetting::set('installment_academic_registration_enabled', $this->academicRegistrationEnabled ? '1' : '0', 'finance', 'تقسيط التسجيل الأكاديمي');
        PlatformSetting::set('installment_requires_signature', $this->requiresSignature ? '1' : '0', 'finance', 'التوقيع الإلكتروني');

        $this->loadSettings();
        $this->flash('تم حفظ إعدادات التقسيط بنجاح.');
    }

    public function runReminders(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);
        Artisan::call('installments:dispatch-reminders');
        $this->flash(trim(Artisan::output()) ?: 'تم تشغيل أمر التذكيرات.', 'info', 'action');
    }

    public function runOverdue(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);
        Artisan::call('installments:process-overdue');
        $this->flash(trim(Artisan::output()) ?: 'تم تشغيل معالجة المتأخرات.', 'info', 'action');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-settings'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'إعدادات التقسيط'],
    ],
])

@php
    $stats = $this->stats;
    $lateFeePreview = 0.0;
    if ($lateFeesEnabled) {
        $lateFeePreview = $lateFeeMode === 'fixed'
            ? max(0.0, (float) $lateFeeFixed)
            : round(1000 * max(0.0, (float) $lateFeePercent) / 100, 2);
        $cap = max(0.0, (float) $lateFeeMaxCap);
        if ($cap > 0) {
            $lateFeePreview = min($lateFeePreview, $cap);
        }
    }
    $toastMessage = $savedMessage ?: $actionMessage;
    $toastType = $savedMessage ? 'success' : ($actionMessage ? $flashType : 'info');
@endphp

<div class="inst-settings-page">
    @include('partials.admin.toast', [
        'message' => $toastMessage,
        'type' => $toastType,
        'key' => $flashKey,
        'dismissMethod' => 'dismissFlash',
        'duration' => 6500,
    ])

    <section class="inst-hero" aria-labelledby="inst-settings-title">
        <div class="inst-hero__main">
            <span class="inst-hero__icon"><i class="fa-solid fa-layer-group"></i></span>
            <div>
                <span class="inst-hero__eyebrow">مركز التقسيط</span>
                <h1 id="inst-settings-title">إعدادات وتحكم التقسيط</h1>
                <p>كل إعداد ظاهر بشكل مستقل: التذكيرات، الإيقاف، رسوم التأخير، والقنوات والتوقيع.</p>
            </div>
        </div>
        <div class="inst-hero__stats">
            <div class="inst-stat">
                <span class="inst-stat__label">عقود نشطة</span>
                <strong class="is-success">{{ number_format($stats['active']) }}</strong>
            </div>
            <div class="inst-stat">
                <span class="inst-stat__label">أقساط متأخرة</span>
                <strong class="{{ $stats['overdue'] > 0 ? 'is-warning' : '' }}">{{ number_format($stats['overdue']) }}</strong>
            </div>
            <div class="inst-stat">
                <span class="inst-stat__label">عقود موقوفة</span>
                <strong class="{{ $stats['suspended'] > 0 ? 'is-danger' : '' }}">{{ number_format($stats['suspended']) }}</strong>
            </div>
            <div class="inst-stat">
                <span class="inst-stat__label">محصّل هذا الشهر</span>
                <strong>{{ number_format($stats['collected_month'], 0) }} <small>ر.س</small></strong>
            </div>
        </div>
    </section>

    <nav class="inst-section-nav" aria-label="أقسام إعدادات التقسيط">
        <a href="#reminders"><i class="fa-solid fa-bell"></i> التذكيرات</a>
        <a href="#suspension"><i class="fa-solid fa-ban"></i> الإيقاف عند التأخر</a>
        <a href="{{ route('admin.installment-dunning') }}"><i class="fa-solid fa-stairs"></i> تصعيد المتأخرات</a>
        <a href="#late-fees"><i class="fa-solid fa-coins"></i> رسوم التأخير</a>
        <a href="#channels"><i class="fa-solid fa-toggle-on"></i> القنوات والتوقيع</a>
        <a href="#links"><i class="fa-solid fa-arrow-up-right-from-square"></i> روابط سريعة</a>
    </nav>

    <section id="reminders" class="inst-panel">
        <div class="inst-panel__head">
            <div class="inst-panel__title">
                <span class="inst-step inst-step--num">01</span>
                <div>
                    <h2>تذكيرات الأقساط</h2>
                    <p>إشعارات تلقائية للطالب قبل موعد استحقاق كل قسط.</p>
                    <span class="inst-status-pill {{ $remindersEnabled ? 'is-on' : 'is-off' }}">
                        <i class="fa-solid {{ $remindersEnabled ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                        {{ $remindersEnabled ? 'مفعّلة' : 'متوقفة' }}
                    </span>
                </div>
            </div>
            <label class="inst-master-toggle">
                <span>
                    <strong>التذكيرات التلقائية</strong>
                    <small>{{ $remindersEnabled ? 'مجدولة يومياً' : 'متوقفة حالياً' }}</small>
                </span>
                <input type="checkbox" wire:model.live="remindersEnabled">
                <span class="inst-switch" aria-hidden="true"></span>
            </label>
        </div>

        <div class="inst-panel__body">
            <div class="inst-item-grid">
                <div class="inst-item {{ $remindersEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-clock"></i> وقت التشغيل اليومي</div>
                        <p class="inst-item__desc">الساعة التي يُرسل عندها النظام تذكيرات الأقساط كل يوم.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-clock"></i>
                            <input id="reminderTime" type="time" class="admin-control" wire:model="reminderTime" @disabled(! $remindersEnabled)>
                        </div>
                        @error('reminderTime')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="inst-item {{ $remindersEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-calendar-days"></i> أيام التذكير قبل الاستحقاق</div>
                        <p class="inst-item__desc">افصل بين الأيام بفاصلة. مثال: <code dir="ltr">7,3,1</code> يعني تذكيراً قبل أسبوع، ثم 3 أيام، ثم يوم واحد.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-calendar-days"></i>
                            <input id="reminderDays" type="text" class="admin-control" wire:model="reminderDays" placeholder="7,3,1" dir="ltr" @disabled(! $remindersEnabled)>
                        </div>
                        @error('reminderDays')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="inst-panel__foot">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="runReminders" wire:loading.attr="disabled" wire:target="runReminders">
                    <span wire:loading.remove wire:target="runReminders"><i class="fa-solid fa-paper-plane"></i> تشغيل التذكيرات الآن</span>
                    <span wire:loading.inline-flex wire:target="runReminders"><i class="fa-solid fa-spinner fa-spin"></i> جارٍ التشغيل...</span>
                </button>
            </div>
        </div>
    </section>

    <section id="suspension" class="inst-panel">
        <div class="inst-panel__head">
            <div class="inst-panel__title">
                <span class="inst-step inst-step--num">02</span>
                <div>
                    <h2>الإيقاف عند التأخر</h2>
                    <p>مسار واضح: استحقاق → تأخير بعد السماح → إيقاف الالتحاق، مع استعادة تلقائية بعد السداد.</p>
                    <span class="inst-status-pill {{ $suspensionEnabled ? 'is-on' : 'is-off' }}">
                        <i class="fa-solid {{ $suspensionEnabled ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                        {{ $suspensionEnabled ? 'مفعّل' : 'متوقف' }}
                    </span>
                </div>
            </div>
            <label class="inst-master-toggle">
                <span>
                    <strong>الإيقاف التلقائي</strong>
                    <small>{{ $suspensionEnabled ? 'يعمل وفق الجدول أدناه' : 'لن يُوقف الطلاب تلقائياً' }}</small>
                </span>
                <input type="checkbox" wire:model.live="suspensionEnabled">
                <span class="inst-switch" aria-hidden="true"></span>
            </label>
        </div>

        <div class="inst-panel__body">
            <div class="inst-timeline" aria-label="مسار الإيقاف">
                <div class="inst-timeline__step">
                    <span>الخطوة 1</span>
                    <strong>يوم الاستحقاق<br>اليوم 0</strong>
                </div>
                <div class="inst-timeline__arrow"><i class="fa-solid fa-arrow-left-long"></i></div>
                <div class="inst-timeline__step is-warn">
                    <span>الخطوة 2</span>
                    <strong>يصبح متأخراً<br>بعد {{ $graceDays }} يوم سماح</strong>
                </div>
                <div class="inst-timeline__arrow"><i class="fa-solid fa-arrow-left-long"></i></div>
                <div class="inst-timeline__step is-danger">
                    <span>الخطوة 3</span>
                    <strong>إيقاف الالتحاق<br>بعد {{ $suspendAfterDays }} يوم</strong>
                </div>
            </div>

            <div class="inst-item-grid">
                <div class="inst-item {{ $suspensionEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-hourglass-half"></i> أيام السماح بعد الاستحقاق</div>
                        <p class="inst-item__desc">عدد الأيام بعد تاريخ الاستحقاق قبل اعتبار القسط متأخراً.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-hourglass-half"></i>
                            <input id="graceDays" type="number" min="0" max="90" class="admin-control" wire:model.live="graceDays" @disabled(! $suspensionEnabled)>
                        </div>
                        @error('graceDays')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="inst-item {{ $suspensionEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-user-lock"></i> إيقاف الالتحاق بعد</div>
                        <p class="inst-item__desc">عدد الأيام من تاريخ الاستحقاق قبل إيقاف العقد وحالة الطالب الأكاديمية.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-user-lock"></i>
                            <input id="suspendAfterDays" type="number" min="1" max="180" class="admin-control" wire:model.live="suspendAfterDays" @disabled(! $suspensionEnabled)>
                        </div>
                        @error('suspendAfterDays')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="inst-item {{ $suspensionEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-clock"></i> وقت المعالجة اليومي</div>
                        <p class="inst-item__desc">الساعة اليومية التي يراجع فيها النظام الأقساط المتأخرة ويطبّق الإيقاف.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-clock"></i>
                            <input id="overdueTime" type="time" class="admin-control" wire:model="overdueTime" @disabled(! $suspensionEnabled)>
                        </div>
                        @error('overdueTime')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="inst-panel__foot" style="display:flex;gap:0.65rem;flex-wrap:wrap;align-items:center;">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="runOverdue" wire:loading.attr="disabled" wire:target="runOverdue">
                    <span wire:loading.remove wire:target="runOverdue"><i class="fa-solid fa-gears"></i> معالجة المتأخرات الآن</span>
                    <span wire:loading.inline-flex wire:target="runOverdue"><i class="fa-solid fa-spinner fa-spin"></i> جارٍ المعالجة...</span>
                </button>
                <a href="{{ route('admin.installment-dunning') }}" class="admin-btn-primary admin-btn-primary--sm">
                    <i class="fa-solid fa-stairs"></i> فتح مسار التصعيد الديناميكي
                </a>
            </div>
        </div>
    </section>

    <section id="late-fees" class="inst-panel">
        <div class="inst-panel__head">
            <div class="inst-panel__title">
                <span class="inst-step inst-step--num">03</span>
                <div>
                    <h2>رسوم التأخير</h2>
                    <p>رسم يُضاف مرة واحدة على القسط المتأخر ويُحصَّل ضمن مبلغ السداد.</p>
                    <span class="inst-status-pill {{ $lateFeesEnabled ? 'is-on' : 'is-off' }}">
                        <i class="fa-solid {{ $lateFeesEnabled ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                        {{ $lateFeesEnabled ? 'مفعّلة' : 'غير مفعّلة' }}
                    </span>
                </div>
            </div>
            <label class="inst-master-toggle">
                <span>
                    <strong>تفعيل رسوم التأخير</strong>
                    <small>{{ $lateFeesEnabled ? 'تُطبّق على المتأخرات' : 'لن تُحتسب رسوم إضافية' }}</small>
                </span>
                <input type="checkbox" wire:model.live="lateFeesEnabled">
                <span class="inst-switch" aria-hidden="true"></span>
            </label>
        </div>

        <div class="inst-panel__body">
            <div class="inst-item-grid">
                <div class="inst-item {{ $lateFeesEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-calculator"></i> طريقة الاحتساب</div>
                        <p class="inst-item__desc">اختر بين نسبة مئوية من قيمة القسط أو مبلغ ثابت بالريال.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-calculator"></i>
                            <select id="lateFeeMode" class="admin-control" wire:model.live="lateFeeMode" @disabled(! $lateFeesEnabled)>
                                <option value="percent">نسبة من القسط</option>
                                <option value="fixed">مبلغ ثابت</option>
                            </select>
                        </div>
                        @error('lateFeeMode')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if ($lateFeeMode === 'percent')
                    <div class="inst-item {{ $lateFeesEnabled ? '' : 'is-disabled' }}">
                        <div class="inst-item__meta">
                            <div class="inst-item__label"><i class="fa-solid fa-percent"></i> نسبة الرسوم (%)</div>
                            <p class="inst-item__desc">النسبة المئوية التي تُحسب من أصل مبلغ القسط عند التأخر.</p>
                        </div>
                        <div class="inst-item__control">
                            <div class="inst-input">
                                <i class="fa-solid fa-percent"></i>
                                <input id="lateFeePercent" type="number" step="0.1" min="0" max="100" class="admin-control" wire:model.live="lateFeePercent" @disabled(! $lateFeesEnabled)>
                            </div>
                            @error('lateFeePercent')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @else
                    <div class="inst-item {{ $lateFeesEnabled ? '' : 'is-disabled' }}">
                        <div class="inst-item__meta">
                            <div class="inst-item__label"><i class="fa-solid fa-money-bill"></i> المبلغ الثابت (ر.س)</div>
                            <p class="inst-item__desc">مبلغ ثابت يُضاف على أي قسط متأخر بغض النظر عن قيمته.</p>
                        </div>
                        <div class="inst-item__control">
                            <div class="inst-input">
                                <i class="fa-solid fa-money-bill"></i>
                                <input id="lateFeeFixed" type="number" step="1" min="0" class="admin-control" wire:model.live="lateFeeFixed" @disabled(! $lateFeesEnabled)>
                            </div>
                            @error('lateFeeFixed')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="inst-item {{ $lateFeesEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-gauge-high"></i> الحد الأقصى للرسم (ر.س)</div>
                        <p class="inst-item__desc">سقف أقصى لرسوم التأخير. اترك القيمة <strong>0</strong> إذا لم ترد وضع حد أعلى.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-gauge-high"></i>
                            <input id="lateFeeMaxCap" type="number" step="1" min="0" class="admin-control" wire:model.live="lateFeeMaxCap" @disabled(! $lateFeesEnabled)>
                        </div>
                        @error('lateFeeMaxCap')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="inst-item {{ $lateFeesEnabled ? '' : 'is-disabled' }}">
                    <div class="inst-item__meta">
                        <div class="inst-item__label"><i class="fa-solid fa-calendar-plus"></i> تطبيق الرسوم بعد</div>
                        <p class="inst-item__desc">عدد الأيام من تاريخ الاستحقاق قبل إضافة رسوم التأخير على القسط.</p>
                    </div>
                    <div class="inst-item__control">
                        <div class="inst-input">
                            <i class="fa-solid fa-calendar-plus"></i>
                            <input id="lateFeeApplyAfterDays" type="number" step="1" min="0" max="180" class="admin-control" wire:model="lateFeeApplyAfterDays" @disabled(! $lateFeesEnabled)>
                        </div>
                        @error('lateFeeApplyAfterDays')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            @if ($lateFeesEnabled)
                <div class="inst-preview">
                    <i class="fa-solid fa-lightbulb"></i>
                    <span>مثال توضيحي: قسط بقيمة <strong>1,000 ر.س</strong> متأخر ستُضاف عليه رسوم <strong>{{ number_format($lateFeePreview, 2) }} ر.س</strong> حسب الإعدادات الحالية.</span>
                </div>
            @endif
        </div>
    </section>

    <section id="channels" class="inst-panel">
        <div class="inst-panel__head">
            <div class="inst-panel__title">
                <span class="inst-step inst-step--num">04</span>
                <div>
                    <h2>قنوات التقسيط والتوقيع</h2>
                    <p>كل خيار مستقل: أين يظهر التقسيط للطالب، وهل يلزم التوقيع قبل السداد.</p>
                </div>
            </div>
        </div>

        <div class="inst-panel__body">
            <div class="inst-toggle-grid">
                <label class="inst-toggle-card {{ $checkoutEnabled ? 'is-on' : '' }}">
                    <div class="inst-toggle-card__top">
                        <span class="inst-toggle-card__icon"><i class="fa-solid fa-cart-shopping"></i></span>
                        <input type="checkbox" wire:model.live="checkoutEnabled">
                        <span class="inst-switch" aria-hidden="true"></span>
                    </div>
                    <div>
                        <strong>تقسيط صفحة الدفع</strong>
                        <p>يظهر خيار التقسيط داخل سلة الشراء وصفحة إتمام الدفع للدورات والدبلومات.</p>
                        <span class="inst-toggle-card__path" dir="ltr">/checkout</span>
                    </div>
                    <span class="inst-toggle-card__state">
                        <i class="fa-solid {{ $checkoutEnabled ? 'fa-circle-check' : 'fa-circle' }}"></i>
                        {{ $checkoutEnabled ? 'مفعّل الآن' : 'غير مفعّل' }}
                    </span>
                </label>

                <label class="inst-toggle-card {{ $academicRegistrationEnabled ? 'is-on' : '' }}">
                    <div class="inst-toggle-card__top">
                        <span class="inst-toggle-card__icon"><i class="fa-solid fa-graduation-cap"></i></span>
                        <input type="checkbox" wire:model.live="academicRegistrationEnabled">
                        <span class="inst-switch" aria-hidden="true"></span>
                    </div>
                    <div>
                        <strong>التسجيل الأكاديمي</strong>
                        <p>يتيح التقسيط لمسار التسجيل الأكاديمي للبرامج والدفعات الدراسية.</p>
                        <span class="inst-toggle-card__path" dir="ltr">/academic-registration</span>
                    </div>
                    <span class="inst-toggle-card__state">
                        <i class="fa-solid {{ $academicRegistrationEnabled ? 'fa-circle-check' : 'fa-circle' }}"></i>
                        {{ $academicRegistrationEnabled ? 'مفعّل الآن' : 'غير مفعّل' }}
                    </span>
                </label>

                <label class="inst-toggle-card {{ $requiresSignature ? 'is-on' : '' }}">
                    <div class="inst-toggle-card__top">
                        <span class="inst-toggle-card__icon"><i class="fa-solid fa-signature"></i></span>
                        <input type="checkbox" wire:model.live="requiresSignature">
                        <span class="inst-switch" aria-hidden="true"></span>
                    </div>
                    <div>
                        <strong>التوقيع الإلكتروني</strong>
                        <p>يلزم الطالب بالتوقيع على العقد إلكترونياً قبل إنشاء أو إرسال روابط السداد.</p>
                    </div>
                    <span class="inst-toggle-card__state">
                        <i class="fa-solid {{ $requiresSignature ? 'fa-circle-check' : 'fa-circle' }}"></i>
                        {{ $requiresSignature ? 'مطلوب قبل السداد' : 'غير مطلوب' }}
                    </span>
                </label>
            </div>
        </div>
    </section>

    <section id="links" class="inst-panel">
        <div class="inst-panel__head">
            <div class="inst-panel__title">
                <span class="inst-step inst-step--num">05</span>
                <div>
                    <h2>روابط سريعة</h2>
                    <p>انتقال مباشر لإدارة الخطط والعقود والتقارير وقواعد الإشعارات.</p>
                </div>
            </div>
        </div>
        <div class="inst-panel__body">
            <div class="inst-links-grid">
                <a href="{{ route('admin.installment-plans') }}" class="inst-link-card">
                    <i class="fa-solid fa-sliders"></i>
                    <strong>خطط التقسيط</strong>
                    <span>إنشاء وتعديل قوالب الأقساط والنسب.</span>
                </a>
                <a href="{{ route('admin.installment-contracts') }}" class="inst-link-card">
                    <i class="fa-solid fa-file-signature"></i>
                    <strong>عقود التقسيط</strong>
                    <span>متابعة عقود الطلاب وروابط السداد.</span>
                </a>
                <a href="{{ route('admin.installment-reports') }}" class="inst-link-card">
                    <i class="fa-solid fa-chart-line"></i>
                    <strong>تقارير التحصيل</strong>
                    <span>المستحق والمحصّل والمتأخر.</span>
                </a>
                <a href="{{ route('admin.notification-rules') }}" class="inst-link-card">
                    <i class="fa-solid fa-bell-concierge"></i>
                    <strong>قواعد الإشعارات</strong>
                    <span>قنوات إرسال تنبيهات الأقساط.</span>
                </a>
            </div>
        </div>
    </section>

    <div class="inst-save-bar">
        <div>
            <i class="fa-solid fa-circle-info"></i>
            <span>التغييرات لا تُطبّق على النظام حتى تضغط حفظ الإعدادات.</span>
        </div>
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk"></i> حفظ إعدادات التقسيط</span>
            <span wire:loading.inline-flex wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...</span>
        </button>
    </div>
</div>

@include('partials.admin.shell-end')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-installment-settings.css') }}?v=1">
@endpush
