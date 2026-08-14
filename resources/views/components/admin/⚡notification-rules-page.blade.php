<?php

use App\Models\NotificationDelivery;
use App\Models\NotificationRule;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\NotificationRuleCatalog;
use App\Support\NotificationTypes;
use App\Support\RuntimeSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'محرك الإشعارات',
    'adminPageDesc' => 'قواعد التذكير · القنوات · الاختبار',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'قواعد الإشعارات'],
    ],
])]
#[Title('قواعد الإشعارات | لوحة التحكم')]
class extends Component
{
    public string $activeTab = 'lecture';

    public ?string $savedMessage = null;

    public ?string $errorMessage = null;

    public string $testEmail = '';

    /** @var array<int, array{id: int, offset_minutes: int, channels: array<int, string>, is_enabled: bool}> */
    public array $ruleForms = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('notifications.manage'), 403);
        $this->testEmail = auth()->user()?->email ?? '';
        $this->loadRuleForms();
    }

    protected function loadRuleForms(): void
    {
        $this->ruleForms = NotificationRule::query()
            ->orderBy('type')
            ->orderByDesc('offset_minutes')
            ->get()
            ->mapWithKeys(fn (NotificationRule $rule) => [
                $rule->id => [
                    'id' => $rule->id,
                    'offset_minutes' => (int) ($rule->offset_minutes ?? 0),
                    'channels' => $rule->channelList(),
                    'is_enabled' => $rule->is_enabled,
                ],
            ])
            ->all();
    }

    #[Computed]
    public function rules()
    {
        return NotificationRule::query()
            ->orderBy('type')
            ->orderByDesc('offset_minutes')
            ->get()
            ->groupBy(fn (NotificationRule $r) => NotificationRuleCatalog::categoryForType($r->type));
    }

    #[Computed]
    public function stats(): array
    {
        $rules = NotificationRule::query()->get();

        return [
            'total' => $rules->count(),
            'enabled' => $rules->where('is_enabled', true)->count(),
            'deliveries_today' => NotificationDelivery::query()->whereDate('sent_at', today())->count(),
            'deliveries_week' => NotificationDelivery::query()->where('sent_at', '>=', now()->subDays(7))->count(),
        ];
    }

    #[Computed]
    public function mailConfigured(): bool
    {
        $mailer = RuntimeSettings::get('MAIL_MAILER', config('mail.default'));

        if ($mailer === 'log' || $mailer === 'array') {
            return false;
        }

        return filled(RuntimeSettings::get('MAIL_HOST')) || $mailer === 'sendmail';
    }

    #[Computed]
    public function recentDeliveries()
    {
        return NotificationDelivery::query()
            ->with('rule')
            ->latest('sent_at')
            ->limit(6)
            ->get();
    }

    public function tabCount(string $tab): int
    {
        return $this->rules->get($tab, collect())->count();
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, NotificationRuleCatalog::categories())) {
            $this->activeTab = $tab;
        }
    }

    public function toggleRule(int $ruleId): void
    {
        abort_unless(auth()->user()?->canAdmin('notifications.manage'), 403);
        $rule = NotificationRule::query()->findOrFail($ruleId);

        if ($rule->type === NotificationTypes::LECTURE_LIVE_NOW) {
            return;
        }

        $old = $rule->is_enabled;
        $rule->update(['is_enabled' => ! $old]);
        $this->loadRuleForms();

        app(AuditLogService::class)->log(
            action: 'notification_rule.toggled',
            descriptionAr: ($rule->is_enabled ? 'تفعيل' : 'تعطيل').' قاعدة: '.$rule->name_ar,
            group: 'notifications',
            subject: $rule,
            subjectLabel: $rule->name_ar,
            oldValues: ['is_enabled' => $old],
            newValues: ['is_enabled' => $rule->is_enabled],
        );

        $this->savedMessage = 'تم تحديث حالة «'.$rule->name_ar.'».';
        $this->errorMessage = null;
    }

    public function toggleChannel(int $ruleId, string $channel): void
    {
        abort_unless(auth()->user()?->canAdmin('notifications.manage'), 403);
        if (! array_key_exists($channel, NotificationRuleCatalog::channels())) {
            return;
        }

        $form = $this->ruleForms[$ruleId] ?? null;

        if (! $form) {
            return;
        }

        $channels = $form['channels'];

        if (in_array($channel, $channels, true)) {
            $channels = array_values(array_diff($channels, [$channel]));
        } else {
            $channels[] = $channel;
        }

        $this->ruleForms[$ruleId]['channels'] = $channels ?: ['database'];
    }

    public function setPreset(int $ruleId, int $minutes): void
    {
        if (isset($this->ruleForms[$ruleId])) {
            $this->ruleForms[$ruleId]['offset_minutes'] = $minutes;
        }
    }

    public function saveRule(int $ruleId): void
    {
        abort_unless(auth()->user()?->canAdmin('notifications.manage'), 403);
        $rule = NotificationRule::query()->findOrFail($ruleId);
        $form = $this->ruleForms[$ruleId] ?? null;

        if (! $form) {
            return;
        }

        $old = [
            'offset_minutes' => $rule->offset_minutes,
            'channels' => $rule->channelList(),
        ];

        $rule->update([
            'offset_minutes' => max(0, (int) $form['offset_minutes']),
            'channels' => array_values($form['channels'] ?: ['database']),
        ]);

        app(AuditLogService::class)->log(
            action: 'notification_rule.updated',
            descriptionAr: 'تحديث قاعدة: '.$rule->name_ar,
            group: 'notifications',
            subject: $rule,
            subjectLabel: $rule->name_ar,
            oldValues: $old,
            newValues: [
                'offset_minutes' => $rule->offset_minutes,
                'channels' => $rule->channelList(),
            ],
        );

        $this->savedMessage = 'تم حفظ «'.$rule->name_ar.'» بنجاح.';
        $this->errorMessage = null;
    }

    public function sendTestNotification(): void
    {
        $this->validate(['testEmail' => ['required', 'email']]);

        $user = User::query()->where('email', $this->testEmail)->first();

        if (! $user) {
            $this->errorMessage = 'لا يوجد مستخدم بهذا البريد في المنصة.';
            $this->savedMessage = null;

            return;
        }

        try {
            RuntimeSettings::applyRuntimeConfig();

            app(NotificationService::class)->send(
                user: $user,
                type: NotificationTypes::LECTURE_REMINDER,
                title: 'إشعار تجريبي من لوحة التحكم',
                body: 'هذا إشعار اختبار للتأكد من عمل محرك الإشعارات والبريد.',
                actionUrl: route('notifications', ['locale' => 'ar']),
                icon: 'fa-flask',
            );

            $this->savedMessage = 'تم إرسال إشعار تجريبي إلى '.$user->displayName();
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = 'فشل الإرسال: '.$e->getMessage();
            $this->savedMessage = null;
        }
    }

    public function dismissFlash(): void
    {
        $this->savedMessage = null;
        $this->errorMessage = null;
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.notification-rules'),
])

<div class="admin-module notif-engine">
    @if ($savedMessage)
        <div class="admin-module__flash admin-module__flash--success" wire:click="dismissFlash">
            <i class="fa-solid fa-circle-check"></i> {{ $savedMessage }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="admin-module__flash admin-module__flash--error" wire:click="dismissFlash">
            <i class="fa-solid fa-circle-exclamation"></i> {{ $errorMessage }}
        </div>
    @endif

    <section class="admin-module-hero">
        <div class="admin-module-hero__main">
            <h2>محرك الإشعارات</h2>
            <p>إدارة تذكيرات المحاضرات، إشعارات الواجبات والتسجيلات، وقنوات الإرسال — مع منع التكرار التلقائي لكل طالب.</p>
            <div class="admin-module-flow" style="margin-top: 0.85rem;">
                <span class="admin-module-flow__step"><i class="fa-solid fa-clock"></i> مجدول</span>
                <i class="fa-solid fa-chevron-left admin-module-flow__arrow"></i>
                <span class="admin-module-flow__step"><i class="fa-solid fa-bell"></i> قواعد</span>
                <i class="fa-solid fa-chevron-left admin-module-flow__arrow"></i>
                <span class="admin-module-flow__step"><i class="fa-solid fa-paper-plane"></i> إرسال</span>
                <i class="fa-solid fa-chevron-left admin-module-flow__arrow"></i>
                <span class="admin-module-flow__step"><i class="fa-solid fa-user-graduate"></i> الطالب</span>
            </div>
        </div>
        <div class="admin-module-hero__aside">
            <div class="admin-module-chip">
                <i class="fa-solid fa-rotate"></i>
                <div>
                    <strong>المجدول</strong>
                    <code dir="ltr">notifications:dispatch-lecture-reminders</code>
                    <span>كل 5 دقائق</span>
                </div>
            </div>
            <div @class(['admin-module-chip', 'admin-module-chip--ok' => $this->mailConfigured, 'admin-module-chip--warn' => ! $this->mailConfigured])>
                <i class="fa-solid fa-envelope"></i>
                <div>
                    <strong>البريد</strong>
                    <span>{{ $this->mailConfigured ? 'SMTP مضبوط' : 'وضع التطوير (log)' }}</span>
                </div>
            </div>
        </div>
    </section>

    <div class="admin-module-kpis">
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--green"><i class="fa-solid fa-toggle-on"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['enabled'] }}/{{ $this->stats['total'] }}</span>
                <span class="admin-module-kpi__label">قواعد مفعّلة</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--blue"><i class="fa-solid fa-paper-plane"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ number_format($this->stats['deliveries_today']) }}</span>
                <span class="admin-module-kpi__label">إرسال اليوم</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--purple"><i class="fa-solid fa-chart-line"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ number_format($this->stats['deliveries_week']) }}</span>
                <span class="admin-module-kpi__label">آخر 7 أيام</span>
            </div>
        </div>
        <a href="{{ route('admin.system-settings.section', ['section' => 'mail']) }}" class="admin-module-kpi admin-module-kpi--link">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--amber"><i class="fa-solid fa-gear"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value" style="font-size:0.95rem;">إعدادات</span>
                <span class="admin-module-kpi__label">البريد والـ SMTP</span>
            </div>
        </a>
    </div>

    <nav class="notif-tabs">
        @foreach (NotificationRuleCatalog::categories() as $key => $cat)
            <button type="button" @class(['notif-tab', 'is-active' => $activeTab === $key]) wire:click="setTab('{{ $key }}')">
                <i class="fa-solid {{ $cat['icon'] }}"></i>
                {{ $cat['label'] }}
                <span class="notif-tab__badge">{{ $this->tabCount($key) }}</span>
            </button>
        @endforeach
    </nav>

    @php $category = NotificationRuleCatalog::categories()[$activeTab]; @endphp

    @php
        $tabRules = $this->rules->get($activeTab, collect());
        $liveRules = $tabRules->where('trigger_kind', 'live_window');
        $reminderRules = $tabRules->where('trigger_kind', 'before_event')->sortByDesc('offset_minutes')->values();
        $immediateRules = $tabRules->where('trigger_kind', 'immediate')->values();
    @endphp

    <div class="notif-rules-layout">
        <div class="notif-rules-main">
            <section class="notif-rules-board">
                <header class="notif-rules-board__head">
                    <div class="notif-rules-board__title">
                        <span class="notif-rules-board__icon"><i class="fa-solid {{ $category['icon'] }}"></i></span>
                        <div>
                            <h3>{{ $category['label'] }}</h3>
                            <p>{{ $category['description'] }}</p>
                        </div>
                    </div>
                    <span class="notif-rules-board__count">{{ $tabRules->count() }} قواعد</span>
                </header>

                @foreach ($liveRules as $rule)
                    @php $form = $ruleForms[$rule->id] ?? ['offset_minutes' => 0, 'channels' => ['database'], 'is_enabled' => true]; @endphp
                    <article class="notif-live-hero" wire:key="rule-live-{{ $rule->id }}">
                        <div class="notif-live-hero__glow" aria-hidden="true"></div>
                        <div class="notif-live-hero__visual">
                            <div class="notif-live-hero__ring">
                                <i class="fa-solid fa-tower-broadcast"></i>
                            </div>
                            <span class="notif-live-hero__live-pill"><span class="notif-live-hero__dot"></span> LIVE</span>
                        </div>
                        <div class="notif-live-hero__body">
                            <div class="notif-live-hero__tags">
                                <span class="notif-tag notif-tag--critical"><i class="fa-solid fa-shield-halved"></i> إشعار حرج</span>
                                <span class="notif-tag notif-tag--locked"><i class="fa-solid fa-lock"></i> لا يُعطّل</span>
                                <code class="notif-live-hero__code" dir="ltr">{{ $rule->type }}</code>
                            </div>
                            <h4>{{ $rule->name_ar }}</h4>
                            <p>يُرسل تلقائياً عند بدء المحاضرة ويظهر في بانر <strong>«جارية الآن»</strong> في بوابة الطالب. الطالب لا يستطيع إيقاف هذا الإشعار من تفضيلاته.</p>
                            <ul class="notif-live-hero__facts">
                                <li><i class="fa-solid fa-bolt"></i> فوري عند بدء الجلسة</li>
                                <li><i class="fa-solid fa-display"></i> بانر أعلى صفحة المحاضرات</li>
                                <li><i class="fa-solid fa-bell"></i> جرس الإشعارات</li>
                            </ul>
                        </div>
                        <aside class="notif-live-hero__panel">
                            <header class="notif-live-hero__panel-head">
                                <span><i class="fa-solid fa-sliders"></i> قنوات الإرسال</span>
                                <span class="notif-live-hero__always-on"><i class="fa-solid fa-circle-check"></i> مفعّل دائماً</span>
                            </header>
                            <div class="notif-channel-cards">
                                @foreach (NotificationRuleCatalog::channels() as $chKey => $chMeta)
                                    <button type="button"
                                        @class(['notif-channel-card', 'notif-channel-card--'.$chMeta['color'], 'is-on' => in_array($chKey, $form['channels'], true)])
                                        wire:click="toggleChannel({{ $rule->id }}, '{{ $chKey }}')">
                                        <span class="notif-channel-card__icon"><i class="fa-solid {{ $chMeta['icon'] }}"></i></span>
                                        <span class="notif-channel-card__label">{{ $chMeta['label'] }}</span>
                                        <span class="notif-channel-card__state">{{ in_array($chKey, $form['channels'], true) ? 'مفعّل' : 'معطّل' }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <button type="button" class="notif-save-btn notif-save-btn--block" wire:click="saveRule({{ $rule->id }})" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveRule({{ $rule->id }})"><i class="fa-solid fa-floppy-disk"></i> حفظ القنوات</span>
                                <span wire:loading wire:target="saveRule({{ $rule->id }})">جاري الحفظ...</span>
                            </button>
                        </aside>
                    </article>
                @endforeach

                @if ($reminderRules->isNotEmpty())
                    <section class="notif-reminder-board">
                        <div class="notif-reminder-board__head">
                            <div>
                                <h4><i class="fa-solid fa-clock"></i> تذكيرات قبل المحاضرة</h4>
                                <p>جدول زمني للتذكيرات — كل تذكير يُرسل مرة واحدة فقط لكل طالب.</p>
                            </div>
                            <div class="notif-timeline-visual" aria-hidden="true">
                                @foreach ($reminderRules as $r)
                                    @php $mins = (int) ($ruleForms[$r->id]['offset_minutes'] ?? $r->offset_minutes); @endphp
                                    <div @class(['notif-timeline-node', 'is-last' => $loop->last])>
                                        <span class="notif-timeline-node__bubble">{{ NotificationRuleCatalog::offsetLabel($mins) }}</span>
                                        @if (! $loop->last)<span class="notif-timeline-node__line"></span>@endif
                                    </div>
                                @endforeach
                                <div class="notif-timeline-node notif-timeline-node--live">
                                    <span class="notif-timeline-node__bubble is-live"><i class="fa-solid fa-circle-dot"></i> مباشر</span>
                                </div>
                            </div>
                        </div>

                        <div class="notif-reminder-table">
                            <div class="notif-reminder-table__thead">
                                <span>القاعدة</span>
                                <span>التوقيت قبل المحاضرة</span>
                                <span>قنوات الإرسال</span>
                                <span>الحالة</span>
                                <span></span>
                            </div>
                            <div class="notif-reminder-table__body">
                                @foreach ($reminderRules as $rule)
                                    @php
                                        $form = $ruleForms[$rule->id] ?? ['offset_minutes' => 0, 'channels' => ['database'], 'is_enabled' => true];
                                        $offset = (int) $form['offset_minutes'];
                                        $stepIcon = match(true) {
                                            $offset >= 1440 => 'fa-calendar-day',
                                            $offset >= 120 => 'fa-hourglass-half',
                                            default => 'fa-stopwatch',
                                        };
                                    @endphp
                                    <article @class(['notif-reminder-table__row', 'is-disabled' => ! $form['is_enabled']]) wire:key="rule-rem-{{ $rule->id }}">
                                        <div class="notif-reminder-table__rule">
                                            <span class="notif-reminder-table__step"><i class="fa-solid {{ $stepIcon }}"></i></span>
                                            <div>
                                                <strong>{{ $rule->name_ar }}</strong>
                                                <code dir="ltr">{{ $rule->type }}</code>
                                            </div>
                                        </div>

                                        <div class="notif-reminder-table__timing">
                                            <div class="notif-timing-box">
                                                <label>دقائق</label>
                                                <input type="number" min="0" class="admin-control" wire:model="ruleForms.{{ $rule->id }}.offset_minutes" aria-label="دقائق قبل المحاضرة">
                                            </div>
                                            <span class="notif-timing-equiv"><i class="fa-solid fa-equals"></i> {{ NotificationRuleCatalog::offsetLabel($offset) }}</span>
                                            <div class="notif-presets notif-presets--row">
                                                @foreach ([1440 => '24 ساعة', 120 => 'ساعتان', 30 => '30 دقيقة'] as $mins => $label)
                                                    <button type="button" @class(['notif-preset', 'is-active' => $offset === $mins]) wire:click="setPreset({{ $rule->id }}, {{ $mins }})">{{ $label }}</button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="notif-reminder-table__channels">
                                            @foreach (NotificationRuleCatalog::channels() as $chKey => $chMeta)
                                                <button type="button"
                                                    @class(['notif-channel-card', 'notif-channel-card--sm', 'notif-channel-card--'.$chMeta['color'], 'is-on' => in_array($chKey, $form['channels'], true)])
                                                    wire:click="toggleChannel({{ $rule->id }}, '{{ $chKey }}')">
                                                    <i class="fa-solid {{ $chMeta['icon'] }}"></i>
                                                    <span>{{ $chMeta['label'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>

                                        <div class="notif-reminder-table__status">
                                            <button type="button" class="notif-switch" wire:click="toggleRule({{ $rule->id }})" title="تفعيل/تعطيل">
                                                <span @class(['notif-switch__track', 'is-on' => $form['is_enabled']])"></span>
                                            </button>
                                            <span @class(['notif-status-label', 'is-on' => $form['is_enabled']])>{{ $form['is_enabled'] ? 'مفعّل' : 'معطّل' }}</span>
                                        </div>

                                        <div class="notif-reminder-table__save">
                                            <button type="button" class="notif-save-btn" wire:click="saveRule({{ $rule->id }})" wire:loading.attr="disabled" title="حفظ">
                                                <span wire:loading.remove wire:target="saveRule({{ $rule->id }})"><i class="fa-solid fa-check"></i> حفظ</span>
                                                <span wire:loading wire:target="saveRule({{ $rule->id }})">...</span>
                                            </button>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                @if ($immediateRules->isNotEmpty())
                    <section class="notif-immediate-board">
                        <div class="notif-immediate-board__head">
                            <h4><i class="fa-solid fa-bolt"></i> إشعارات فورية</h4>
                            <p>تُرسل لحظة النشر — واجبات جديدة وتسجيلات محاضرات.</p>
                        </div>
                        <div class="notif-immediate-list">
                            @foreach ($immediateRules as $rule)
                                @php $form = $ruleForms[$rule->id] ?? ['offset_minutes' => 0, 'channels' => ['database'], 'is_enabled' => true]; @endphp
                                <article @class(['notif-immediate-row', 'is-disabled' => ! $form['is_enabled']]) wire:key="rule-imm-{{ $rule->id }}">
                                    <div class="notif-immediate-row__identity">
                                        <span class="notif-immediate-row__icon"><i class="fa-solid fa-bolt"></i></span>
                                        <div>
                                            <strong>{{ $rule->name_ar }}</strong>
                                            <span class="notif-reminder-row__slug" dir="ltr">{{ $rule->type }}</span>
                                        </div>
                                    </div>
                                    <p class="notif-immediate-row__desc">يُرسل فوراً لطلاب الشعبة عند النشر.</p>
                                    <div class="notif-immediate-row__channels">
                                        @foreach (NotificationRuleCatalog::channels() as $chKey => $chMeta)
                                            <button type="button"
                                                @class(['notif-chip', 'notif-chip--'.$chMeta['color'], 'is-on' => in_array($chKey, $form['channels'], true)])
                                                wire:click="toggleChannel({{ $rule->id }}, '{{ $chKey }}')"
                                                title="{{ $chMeta['label'] }}">
                                                <i class="fa-solid {{ $chMeta['icon'] }}"></i>
                                                <span>{{ $chMeta['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="notif-reminder-row__actions">
                                        <button type="button" class="notif-switch" wire:click="toggleRule({{ $rule->id }})" title="تفعيل/تعطيل">
                                            <span @class(['notif-switch__track', 'is-on' => $form['is_enabled']])"></span>
                                        </button>
                                        <button type="button" class="notif-save-btn" wire:click="saveRule({{ $rule->id }})" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="saveRule({{ $rule->id }})"><i class="fa-solid fa-check"></i> حفظ</span>
                                            <span wire:loading wire:target="saveRule({{ $rule->id }})">...</span>
                                        </button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </section>
        </div>

        <aside class="notif-sidebar">
            <div class="admin-side-panel">
                <div class="admin-side-panel__head"><i class="fa-solid fa-flask"></i> إرسال تجريبي</div>
                <div class="admin-side-panel__body">
                    <p style="margin:0 0 0.65rem;font-size:0.78rem;color:#64748b;line-height:1.5;">أرسل إشعاراً لمستخدم موجود للتحقق من الجرس والبريد.</p>
                    <div class="notif-test-form">
                        <input type="email" class="admin-control" wire:model="testEmail" placeholder="email@example.com" dir="ltr">
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="sendTestNotification" wire:loading.attr="disabled">
                            <i class="fa-solid fa-paper-plane"></i> إرسال الآن
                        </button>
                    </div>
                    @error('testEmail')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="admin-side-panel">
                <div class="admin-side-panel__head"><i class="fa-solid fa-clock-rotate-left"></i> آخر الإرسالات</div>
                <div class="admin-side-panel__body">
                    @forelse ($this->recentDeliveries as $delivery)
                        <div class="admin-recent-item" wire:key="del-{{ $delivery->id }}">
                            <strong>{{ $delivery->rule?->name_ar ?? $delivery->channel }}</strong>
                            <span>{{ $delivery->sent_at?->diffForHumans() }} · {{ $delivery->channel }}</span>
                        </div>
                    @empty
                        <p style="margin:0;font-size:0.78rem;color:#94a3b8;">لا توجد إرسالات مسجّلة بعد.</p>
                    @endforelse
                </div>
            </div>

            <div class="admin-side-panel">
                <div class="admin-side-panel__head"><i class="fa-solid fa-circle-info"></i> دليل سريع</div>
                <div class="admin-side-panel__body">
                    <ul class="admin-side-panel__list">
                        <li><strong>داخل المنصة</strong> — جرس الإشعارات و<code>/ar/notifications</code></li>
                        <li><strong>البريد</strong> — يتطلب SMTP من إعدادات النظام</li>
                        <li><strong>تفضيلات الطالب</strong> — إيقاف البريد غير الحرج من الإعدادات</li>
                        <li><strong>بدون تكرار</strong> — كل تذكير يُرسل مرة واحدة لكل طالب</li>
                    </ul>
                    <a href="{{ route('admin.audit-log', ['group' => 'notifications']) }}" class="admin-btn-secondary admin-btn-secondary--sm" style="margin-top:0.75rem;">
                        <i class="fa-solid fa-list-check"></i> سجل التدقيق
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>

@include('partials.admin.shell-end')
