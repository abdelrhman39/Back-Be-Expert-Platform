<?php

use App\Models\InstallmentDunningExecution;
use App\Models\InstallmentDunningPolicy;
use App\Models\InstallmentDunningStep;
use App\Models\PlatformSetting;
use App\Support\InstallmentDunningActions;
use App\Support\InstallmentSettings;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'تصعيد متأخرات الأقساط',
    'adminPageDesc' => 'بناء مسار ديناميكي للتذكير والتحذير والقفل عند تأخر السداد',
    'adminLayout' => 'app',
])]
#[Title('تصعيد المتأخرات | لوحة التحكم')]
class extends Component
{
    public bool $dunningEnabled = true;

    public string $dunningTime = '09:00';

    public ?int $policyId = null;

    public string $policyName = '';

    public string $policyDescription = '';

    public ?int $editingStepId = null;

    public string $stepName = '';

    public string $stepNotes = '';

    public bool $stepEnabled = true;

    public int $triggerOffsetDays = 1;

    public string $triggerHour = '';

    /** @var array<int, string> */
    public array $stepActions = [];

    public bool $emailEnabled = true;

    public string $emailSubject = '';

    public string $emailBody = '';

    /** @var array<int, string> */
    public array $stepChannels = ['mail', 'database'];

    public bool $showStepForm = false;

    public ?string $savedMessage = null;

    public ?string $actionMessage = null;

    public string $flashType = 'success';

    public int $flashKey = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->dunningEnabled = InstallmentSettings::dunningEnabled();
        $this->dunningTime = InstallmentSettings::dunningProcessTime();

        $policy = InstallmentDunningPolicy::defaultPolicy()
            ?? InstallmentDunningPolicy::query()->first();

        if (! $policy) {
            $policy = InstallmentDunningPolicy::query()->create([
                'name' => 'مسار التصعيد الافتراضي',
                'description' => 'مسار قابل للتخصيص بالكامل من لوحة التحكم.',
                'is_active' => true,
                'is_default' => true,
                'process_time' => '09:00',
            ]);
        }

        $this->policyId = $policy->id;
        $this->policyName = $policy->name;
        $this->policyDescription = (string) $policy->description;
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

    public function savePolicy(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'policyName' => ['required', 'string', 'max:120'],
            'policyDescription' => ['nullable', 'string', 'max:2000'],
            'dunningTime' => ['required', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        PlatformSetting::set('installment_dunning_enabled', $this->dunningEnabled ? '1' : '0', 'finance', 'تفعيل مسار تصعيد المتأخرات');
        PlatformSetting::set('installment_dunning_time', $this->dunningTime, 'finance', 'وقت تشغيل مسار التصعيد');

        InstallmentDunningPolicy::query()->whereKey($this->policyId)->update([
            'name' => $this->policyName,
            'description' => $this->policyDescription,
            'is_active' => $this->dunningEnabled,
            'process_time' => $this->dunningTime,
        ]);

        $this->flash('تم حفظ إعدادات مسار التصعيد.');
    }

    public function openCreateStep(): void
    {
        $this->resetStepForm();
        $next = ((int) InstallmentDunningStep::query()->where('policy_id', $this->policyId)->max('sort_order')) + 1;
        $this->triggerOffsetDays = max(1, $next);
        $this->stepName = 'خطوة '.$next;
        $this->stepActions = [InstallmentDunningActions::SEND_NOTIFICATION];
        $this->emailSubject = 'تنبيه بخصوص قسط متأخر — {{installment_label}}';
        $this->emailBody = "مرحباً {{student_name}},\n\nيرجى سداد القسط {{installment_label}} بمبلغ {{amount}} ر.س.\n\n{{pay_url}}";
        $this->showStepForm = true;
    }

    public function openEditStep(int $stepId): void
    {
        $step = InstallmentDunningStep::query()
            ->where('policy_id', $this->policyId)
            ->findOrFail($stepId);

        $this->editingStepId = $step->id;
        $this->stepName = $step->name;
        $this->stepNotes = (string) $step->admin_notes;
        $this->stepEnabled = $step->enabled;
        $this->triggerOffsetDays = (int) $step->trigger_offset_days;
        $this->triggerHour = $step->trigger_hour === null ? '' : (string) $step->trigger_hour;
        $this->stepActions = $step->actionList();
        $this->emailEnabled = $step->email_enabled;
        $this->emailSubject = (string) $step->email_subject;
        $this->emailBody = (string) $step->email_body;
        $this->stepChannels = $step->channelList() ?: ['mail', 'database'];
        $this->showStepForm = true;
    }

    public function cancelStepForm(): void
    {
        $this->resetStepForm();
        $this->showStepForm = false;
    }

    public function saveStep(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'stepName' => ['required', 'string', 'max:160'],
            'stepNotes' => ['nullable', 'string', 'max:2000'],
            'triggerOffsetDays' => ['required', 'integer', 'min:-30', 'max:365'],
            'triggerHour' => ['nullable', 'regex:/^(|[0-9]|1[0-9]|2[0-3])$/'],
            'stepActions' => ['array'],
            'emailSubject' => ['nullable', 'string', 'max:200'],
            'emailBody' => ['nullable', 'string', 'max:5000'],
            'stepChannels' => ['array'],
        ]);

        $actions = array_values(array_intersect($this->stepActions, InstallmentDunningActions::keys()));
        if ($this->emailEnabled && ! in_array(InstallmentDunningActions::SEND_NOTIFICATION, $actions, true)) {
            $actions[] = InstallmentDunningActions::SEND_NOTIFICATION;
        }

        $payload = [
            'policy_id' => $this->policyId,
            'name' => $this->stepName,
            'admin_notes' => $this->stepNotes !== '' ? $this->stepNotes : null,
            'enabled' => $this->stepEnabled,
            'trigger_offset_days' => $this->triggerOffsetDays,
            'trigger_hour' => $this->triggerHour === '' ? null : (int) $this->triggerHour,
            'actions' => $actions,
            'email_enabled' => $this->emailEnabled,
            'email_subject' => $this->emailSubject !== '' ? $this->emailSubject : null,
            'email_body' => $this->emailBody !== '' ? $this->emailBody : null,
            'channels' => array_values(array_intersect($this->stepChannels, ['mail', 'database'])) ?: ['mail', 'database'],
        ];

        if ($this->editingStepId) {
            InstallmentDunningStep::query()
                ->where('policy_id', $this->policyId)
                ->whereKey($this->editingStepId)
                ->update($payload);
            $this->flash('تم تحديث الخطوة.');
        } else {
            $payload['sort_order'] = ((int) InstallmentDunningStep::query()->where('policy_id', $this->policyId)->max('sort_order')) + 1;
            InstallmentDunningStep::query()->create($payload);
            $this->flash('تمت إضافة الخطوة.');
        }

        $this->resetStepForm();
        $this->showStepForm = false;
        unset($this->steps, $this->recentExecutions);
    }

    public function deleteStep(int $stepId): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        InstallmentDunningStep::query()
            ->where('policy_id', $this->policyId)
            ->whereKey($stepId)
            ->delete();

        $this->renumberSteps();
        $this->flash('تم حذف الخطوة.', 'info');
        unset($this->steps, $this->recentExecutions);
    }

    public function moveStep(int $stepId, string $direction): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $steps = InstallmentDunningStep::query()
            ->where('policy_id', $this->policyId)
            ->orderBy('sort_order')
            ->get()
            ->values();

        $index = $steps->search(fn (InstallmentDunningStep $s) => $s->id === $stepId);

        if ($index === false) {
            return;
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= $steps->count()) {
            return;
        }

        $current = $steps[$index];
        $other = $steps[$swapWith];
        $tmp = $current->sort_order;
        $current->update(['sort_order' => $other->sort_order]);
        $other->update(['sort_order' => $tmp]);
        $this->renumberSteps();
        unset($this->steps);
    }

    public function toggleStep(int $stepId): void
    {
        $step = InstallmentDunningStep::query()
            ->where('policy_id', $this->policyId)
            ->findOrFail($stepId);
        $step->update(['enabled' => ! $step->enabled]);
        unset($this->steps);
    }

    public function runDunningNow(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);
        Artisan::call('installments:process-dunning');
        $this->flash(trim(Artisan::output()) ?: 'تم تشغيل مسار التصعيد.', 'info', 'action');
        unset($this->recentExecutions);
    }

    public function seedDefaultsIfEmpty(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        if (InstallmentDunningStep::query()->where('policy_id', $this->policyId)->exists()) {
            $this->flash('المسار يحتوي خطوات بالفعل.', 'info', 'action');

            return;
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\InstallmentDunningSeeder', '--force' => true]);
        $this->flash('تم تحميل القالب الافتراضي المكوّن من 6 خطوات.', 'success', 'action');
        unset($this->steps);
    }

    #[Computed]
    public function steps()
    {
        return InstallmentDunningStep::query()
            ->where('policy_id', $this->policyId)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function recentExecutions()
    {
        return InstallmentDunningExecution::query()
            ->with(['step', 'schedule.contract.user'])
            ->where('policy_id', $this->policyId)
            ->latest('executed_at')
            ->limit(25)
            ->get();
    }

    #[Computed]
    public function actionCatalog(): array
    {
        return InstallmentDunningActions::catalog();
    }

    protected function resetStepForm(): void
    {
        $this->editingStepId = null;
        $this->stepName = '';
        $this->stepNotes = '';
        $this->stepEnabled = true;
        $this->triggerOffsetDays = 1;
        $this->triggerHour = '';
        $this->stepActions = [];
        $this->emailEnabled = true;
        $this->emailSubject = '';
        $this->emailBody = '';
        $this->stepChannels = ['mail', 'database'];
    }

    protected function renumberSteps(): void
    {
        $steps = InstallmentDunningStep::query()
            ->where('policy_id', $this->policyId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($steps as $i => $step) {
            $step->update(['sort_order' => $i + 1]);
        }
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-dunning'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.installment-settings'), 'label' => 'إعدادات التقسيط'],
        ['label' => 'تصعيد المتأخرات'],
    ],
])

@php
    $toastMessage = $savedMessage ?: $actionMessage;
    $toastType = $savedMessage ? 'success' : ($actionMessage ? $flashType : 'info');
    $placeholders = ['{{student_name}}', '{{amount}}', '{{due_date}}', '{{contract_no}}', '{{installment_label}}', '{{pay_url}}', '{{step_name}}', '{{days_overdue}}'];
@endphp

<div class="dunning-page">
    @include('partials.admin.toast', [
        'message' => $toastMessage,
        'type' => $toastType,
        'key' => $flashKey,
        'dismissMethod' => 'dismissFlash',
        'duration' => 6500,
    ])

    <section class="admin-crud-card">
        <div class="admin-crud-card__head admin-crud-card__head--split">
            <div>
                <h2>مسار تصعيد متأخرات الأقساط</h2>
                <p class="admin-crud-card__meta">خطوات ديناميكية بالكامل: رتّب، أضف، عدّل الإجراءات والبريد — دون تثبيت منطق جامد في الكود.</p>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <button type="button" class="admin-btn-secondary--sm" @click="$dispatch('open-dunning-guide', { section: 'overview' })">
                    <i class="fa-solid fa-circle-question"></i> دليل العمل
                </button>
                <button type="button" class="admin-btn-secondary--sm" wire:click="runDunningNow">تشغيل الآن</button>
                <a href="{{ route('admin.installment-settings') }}" class="admin-btn-secondary--sm">إعدادات التقسيط</a>
            </div>
        </div>

        <div class="admin-filter-grid">
            <label class="admin-field" style="display:flex;align-items:center;gap:0.75rem;">
                <input type="checkbox" wire:model.live="dunningEnabled">
                <span>تفعيل مسار التصعيد التلقائي</span>
            </label>
            <label class="admin-field">
                <span>وقت التشغيل اليومي</span>
                <input type="time" class="admin-control" wire:model="dunningTime">
            </label>
            <label class="admin-field">
                <span>اسم المسار</span>
                <input type="text" class="admin-control" wire:model="policyName">
            </label>
            <label class="admin-field" style="grid-column:1/-1;">
                <span>وصف المسار (للأدمن)</span>
                <textarea class="admin-control" rows="2" wire:model="policyDescription"></textarea>
            </label>
        </div>
        <div style="margin-top:0.85rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
            <button type="button" class="admin-btn-primary--sm" wire:click="savePolicy">حفظ إعدادات المسار</button>
            <button type="button" class="admin-btn-secondary--sm" wire:click="seedDefaultsIfEmpty">تحميل القالب الافتراضي (6 خطوات)</button>
        </div>
    </section>

    <section class="admin-crud-card">
        <div class="admin-crud-card__head admin-crud-card__head--split">
            <div>
                <h2>خطوات التصعيد</h2>
                <p class="admin-crud-card__meta">كل خطوة تُنفَّذ مرة واحدة لكل قسط عند تحقق شرط التوقيت، بشرط عدم السداد.</p>
            </div>
            <button type="button" class="admin-btn-primary--sm" wire:click="openCreateStep">
                <i class="fa-solid fa-plus"></i> إضافة خطوة
            </button>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الخطوة</th>
                        <th>التوقيت</th>
                        <th>الإجراءات</th>
                        <th>الحالة</th>
                        <th>تحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->steps as $step)
                        <tr wire:key="step-{{ $step->id }}">
                            <td>{{ $step->sort_order }}</td>
                            <td>
                                <strong>{{ $step->name }}</strong>
                                @if ($step->admin_notes)
                                    <div class="admin-crud-card__meta">{{ $step->admin_notes }}</div>
                                @endif
                            </td>
                            <td>{{ $step->triggerLabel() }}</td>
                            <td>
                                @foreach ($step->actionList() as $action)
                                    <span class="dash-status-pill">{{ \App\Support\InstallmentDunningActions::label($action) }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="dash-status-pill {{ $step->enabled ? 'is-active' : '' }}">
                                    {{ $step->enabled ? 'مفعّلة' : 'متوقفة' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                                    <button type="button" class="admin-btn-secondary--sm" wire:click="moveStep({{ $step->id }}, 'up')">↑</button>
                                    <button type="button" class="admin-btn-secondary--sm" wire:click="moveStep({{ $step->id }}, 'down')">↓</button>
                                    <button type="button" class="admin-btn-secondary--sm" wire:click="toggleStep({{ $step->id }})">{{ $step->enabled ? 'إيقاف' : 'تفعيل' }}</button>
                                    <button type="button" class="admin-btn-secondary--sm" wire:click="openEditStep({{ $step->id }})">تعديل</button>
                                    <button type="button" class="admin-btn-secondary--sm" wire:click="deleteStep({{ $step->id }})" wire:confirm="حذف هذه الخطوة؟">حذف</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">لا توجد خطوات بعد. أضف خطوة أو حمّل القالب الافتراضي.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($showStepForm)
        <section class="admin-crud-card">
            <div class="admin-crud-card__head admin-crud-card__head--split">
                <div>
                    <h2>{{ $editingStepId ? 'تعديل خطوة' : 'خطوة جديدة' }}</h2>
                    <p class="admin-crud-card__meta">حدد التوقيت والإجراءات ونص الرسالة. المتغيرات تُستبدل تلقائياً عند الإرسال.</p>
                </div>
                <button type="button" class="admin-btn-secondary--sm" wire:click="cancelStepForm">إلغاء</button>
            </div>

            <div class="admin-filter-grid">
                <label class="admin-field">
                    <span>اسم الخطوة</span>
                    <input type="text" class="admin-control" wire:model="stepName">
                    @error('stepName') <div class="admin-field-hint is-visible">{{ $message }}</div> @enderror
                </label>
                <label class="admin-field">
                    <span>أيام بعد الاستحقاق (سالب = قبل)</span>
                    <input type="number" class="admin-control" wire:model="triggerOffsetDays">
                </label>
                <label class="admin-field">
                    <span>ساعة التنفيذ (اختياري 0–23)</span>
                    <input type="number" min="0" max="23" class="admin-control" wire:model="triggerHour" placeholder="مثال: 18">
                </label>
                <label class="admin-field" style="display:flex;align-items:center;gap:0.6rem;">
                    <input type="checkbox" wire:model="stepEnabled">
                    <span>الخطوة مفعّلة</span>
                </label>
                <label class="admin-field" style="grid-column:1/-1;">
                    <span>ملاحظات للأدمن (لا تظهر للطالب)</span>
                    <textarea class="admin-control" rows="2" wire:model="stepNotes"></textarea>
                </label>
            </div>

            <h3 style="font-size:0.95rem;margin:1rem 0 0.5rem;">الإجراءات عند التنفيذ</h3>
            <div class="dunning-actions-grid">
                @foreach ($this->actionCatalog as $key => $meta)
                    <label class="dunning-action-card">
                        <input type="checkbox" value="{{ $key }}" wire:model="stepActions">
                        <div>
                            <strong>{{ $meta['label'] }}</strong>
                            <p>{{ $meta['description'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>

            <h3 style="font-size:0.95rem;margin:1rem 0 0.5rem;">الرسالة / البريد</h3>
            <div class="admin-filter-grid">
                <label class="admin-field" style="display:flex;align-items:center;gap:0.6rem;">
                    <input type="checkbox" wire:model="emailEnabled">
                    <span>إرسال رسالة عند تنفيذ الخطوة</span>
                </label>
                <label class="admin-field">
                    <span>القنوات</span>
                    <div style="display:flex;gap:1rem;">
                        <label><input type="checkbox" value="mail" wire:model="stepChannels"> بريد</label>
                        <label><input type="checkbox" value="database" wire:model="stepChannels"> إشعار المنصة</label>
                    </div>
                </label>
                <label class="admin-field" style="grid-column:1/-1;">
                    <span>عنوان الرسالة</span>
                    <input type="text" class="admin-control" wire:model="emailSubject">
                </label>
                <label class="admin-field" style="grid-column:1/-1;">
                    <span>نص الرسالة</span>
                    <textarea class="admin-control" rows="7" wire:model="emailBody"></textarea>
                </label>
            </div>
            <p class="admin-crud-card__meta" style="margin-top:0.5rem;">
                متغيرات متاحة:
                @foreach ($placeholders as $ph)
                    <code dir="ltr">{{ $ph }}</code>
                @endforeach
            </p>

            <div style="margin-top:1rem;">
                <button type="button" class="admin-btn-primary--sm" wire:click="saveStep">حفظ الخطوة</button>
            </div>
        </section>
    @endif

    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>آخر التنفيذات</h2>
            <p class="admin-crud-card__meta">سجل ما تم تطبيقه فعلياً على الأقساط المتأخرة.</p>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>الوقت</th>
                        <th>الخطوة</th>
                        <th>الطالب / العقد</th>
                        <th>الإجراءات</th>
                        <th>رسالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->recentExecutions as $row)
                        <tr>
                            <td dir="ltr">{{ $row->executed_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->step?->name }}</td>
                            <td>
                                {{ $row->schedule?->contract?->user?->displayName() }}
                                <div class="admin-crud-card__meta">{{ $row->schedule?->contract?->contract_no }}</div>
                            </td>
                            <td>
                                @foreach (($row->actions_applied ?? []) as $action)
                                    <span class="dash-status-pill">{{ \App\Support\InstallmentDunningActions::label($action) }}</span>
                                @endforeach
                            </td>
                            <td>{{ $row->message_sent ? 'أُرسلت' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">لا توجد تنفيذات بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@include('partials.admin.installment-dunning-guide')

<style>
    .dunning-page { display:flex; flex-direction:column; gap:1rem; }
    .dunning-actions-grid {
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(16rem,1fr));
        gap:0.65rem;
    }
    .dunning-action-card {
        display:flex;
        gap:0.65rem;
        align-items:flex-start;
        border:1px solid var(--sa-border,#dbe3ea);
        border-radius:12px;
        padding:0.75rem;
        background:#fff;
        cursor:pointer;
    }
    .dunning-action-card strong { display:block; margin-bottom:0.2rem; }
    .dunning-action-card p { margin:0; color:var(--sa-muted,#64748b); font-size:0.8rem; line-height:1.45; }
    .dunning-action-card input { margin-top:0.2rem; }
</style>

@include('partials.admin.shell-end')
