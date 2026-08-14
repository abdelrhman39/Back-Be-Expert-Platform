<?php

use App\Models\InstallmentContract;
use App\Models\InstallmentPayment;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Services\InstallmentContractService;
use App\Services\InstallmentPaymentService;
use App\Services\NotificationService;
use App\Support\InstallmentOptions;
use App\Support\NotificationTypes;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'تفاصيل عقد التقسيط',
    'adminPageDesc' => 'عرض العقد والأقساط وروابط السداد والإجراءات المالية',
    'adminLayout' => 'app',
])]
#[Title('عقد تقسيط | لوحة التحكم')]
class extends Component
{
    public InstallmentContract $contract;

    public ?string $flashMessage = null;

    public ?string $flashType = 'success';

    public int $flashKey = 0;

    public ?string $generatedPayUrl = null;

    public ?int $generatedScheduleId = null;

    public ?int $editingScheduleId = null;

    public string $editDueDate = '';

    public string $editLateFee = '0';

    public string $editNotes = '';

    public ?int $manualScheduleId = null;

    public string $manualPaymentRef = '';

    public string $manualNotes = '';

    public ?int $waiveScheduleId = null;

    public string $waiveNotes = '';

    public string $cancelNotes = '';

    public string $adminNotesDraft = '';

    public function mount(InstallmentContract $contract, InstallmentContractService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.view'), 403);
        $service->refreshOverdueStatuses();
        $this->reloadContract($contract->id);
        $this->adminNotesDraft = (string) ($this->contract->admin_notes ?? '');
    }

    protected function reloadContract(?int $contractId = null): void
    {
        $this->contract = InstallmentContract::query()
            ->with([
                'schedules.payments.recorder',
                'schedules.order',
                'user',
                'student',
                'template',
                'program',
                'batch',
                'creator',
            ])
            ->findOrFail($contractId ?? $this->contract->id);
    }

    protected function findSchedule(int $scheduleId): InstallmentSchedule
    {
        return InstallmentSchedule::query()
            ->where('contract_id', $this->contract->id)
            ->findOrFail($scheduleId);
    }

    protected function flash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
        $this->flashKey++;
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    protected function paymentBlockedReason(InstallmentSchedule $schedule): string
    {
        $contract = $this->contract;

        if (! $schedule->isPayable()) {
            return 'هذا القسط غير قابل للسداد حالياً (مدفوع أو ملغى أو مُعفى).';
        }

        if ($contract->requires_student_signature && ! $contract->isStudentSigned()) {
            return 'لا يمكن إرسال رابط السداد قبل اكتمال التوقيع الإلكتروني من الطالب.';
        }

        if (! in_array($contract->status, ['active', 'suspended'], true)) {
            return 'حالة العقد الحالية ('.$contract->statusLabel().') لا تسمح بإنشاء رابط سداد.';
        }

        return 'لا يمكن إرسال رابط سداد لهذا القسط حالياً. تحقق من حالة العقد والتوقيع.';
    }

    public function paymentUrlFor(InstallmentSchedule $schedule): string
    {
        $locale = $this->contract->user?->locale ?: 'ar';

        return route('installments.pay', [
            'locale' => $locale,
            'contract' => $this->contract->id,
            'schedule' => $schedule->id,
        ]);
    }

    public function generatePaymentLink(int $scheduleId, InstallmentPaymentService $payments): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $schedule = $this->findSchedule($scheduleId);
        $user = $this->contract->user;

        if (! $user) {
            $this->flash('لا يوجد حساب مستخدم مرتبط بهذا العقد.', 'warn');

            return;
        }

        if (! $payments->studentCanPay($user, $schedule)) {
            $this->flash($this->paymentBlockedReason($schedule), 'warn');

            return;
        }

        $order = $payments->createPaymentOrder($schedule, $user, 'mada');
        $this->generatedPayUrl = $this->paymentUrlFor($schedule);
        $this->generatedScheduleId = $schedule->id;
        $this->reloadContract();
        $this->flash('تم تجهيز رابط السداد — الطلب '.$order->reference);
    }

    public function sendPaymentLink(int $scheduleId, InstallmentPaymentService $payments, NotificationService $notifications): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $schedule = $this->findSchedule($scheduleId);
        $user = $this->contract->user;

        if (! $user) {
            $this->flash('لا يوجد حساب مستخدم لإرسال رابط السداد.', 'warn');

            return;
        }

        if (! $payments->studentCanPay($user, $schedule)) {
            $this->flash($this->paymentBlockedReason($schedule), 'warn');

            return;
        }

        $order = $payments->createPaymentOrder($schedule, $user, 'mada');
        $url = $this->paymentUrlFor($schedule);

        $notifications->send(
            user: $user,
            type: NotificationTypes::INSTALLMENT_PAYMENT_LINK,
            title: 'رابط سداد قسطك — '.$schedule->label,
            body: 'المبلغ المستحق '.number_format($schedule->totalDue(), 2).' '.$this->contract->currency.'. رقم الطلب: '.$order->reference,
            actionUrl: $url,
            icon: 'fa-link',
            subject: $schedule,
        );

        $this->generatedPayUrl = $url;
        $this->generatedScheduleId = $schedule->id;
        $this->reloadContract();
        $this->flash('تم إرسال رابط السداد للطالب.');
    }

    public function cancelPaymentLink(int $scheduleId, InstallmentPaymentService $payments): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $schedule = $this->findSchedule($scheduleId);
        $count = $payments->cancelPendingPaymentOrder($schedule, auth()->user());

        if ($this->generatedScheduleId === $scheduleId) {
            $this->generatedPayUrl = null;
            $this->generatedScheduleId = null;
        }

        $this->reloadContract();
        $this->flash($count > 0 ? 'تم إلغاء رابط/طلب السداد المعلّق.' : 'لا يوجد طلب سداد معلّق لهذا القسط.', $count > 0 ? 'success' : 'warn');
    }

    public function openManualPayment(int $scheduleId): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);
        $this->findSchedule($scheduleId);
        $this->manualScheduleId = $scheduleId;
        $this->manualPaymentRef = '';
        $this->manualNotes = '';
        $this->waiveScheduleId = null;
        $this->editingScheduleId = null;
    }

    public function closeManualPayment(): void
    {
        $this->manualScheduleId = null;
        $this->manualPaymentRef = '';
        $this->manualNotes = '';
    }

    public function confirmManualPayment(InstallmentPaymentService $payments): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'manualScheduleId' => ['required', 'integer'],
            'manualPaymentRef' => ['nullable', 'string', 'max:120'],
            'manualNotes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'manualPaymentRef' => 'مرجع الدفع',
            'manualNotes' => 'ملاحظات السداد',
        ]);

        $schedule = $this->findSchedule((int) $this->manualScheduleId);

        $payments->recordManualPayment(
            schedule: $schedule,
            admin: auth()->user(),
            notes: filled($this->manualNotes) ? $this->manualNotes : 'تسجيل سداد يدوي من الإدارة',
            gatewayRef: filled($this->manualPaymentRef) ? $this->manualPaymentRef : null,
        );

        $this->closeManualPayment();
        $this->reloadContract();
        $this->flash('تم تسجيل السداد اليدوي بنجاح.');
    }

    public function openWaive(int $scheduleId): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);
        $this->findSchedule($scheduleId);
        $this->waiveScheduleId = $scheduleId;
        $this->waiveNotes = '';
        $this->manualScheduleId = null;
        $this->editingScheduleId = null;
    }

    public function closeWaive(): void
    {
        $this->waiveScheduleId = null;
        $this->waiveNotes = '';
    }

    public function confirmWaive(InstallmentContractService $contracts): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'waiveScheduleId' => ['required', 'integer'],
            'waiveNotes' => ['required', 'string', 'min:3', 'max:1000'],
        ], [], [
            'waiveNotes' => 'سبب الإعفاء',
        ]);

        $schedule = $this->findSchedule((int) $this->waiveScheduleId);
        $contracts->waiveSchedule($schedule, auth()->user(), $this->waiveNotes);
        $this->closeWaive();
        $this->reloadContract();
        $this->flash('تم إعفاء القسط.');
    }

    public function openEditSchedule(int $scheduleId): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $schedule = $this->findSchedule($scheduleId);
        $this->editingScheduleId = $scheduleId;
        $this->editDueDate = $schedule->due_date->format('Y-m-d');
        $this->editLateFee = (string) $schedule->late_fee_amount;
        $this->editNotes = (string) ($schedule->admin_notes ?? '');
        $this->manualScheduleId = null;
        $this->waiveScheduleId = null;
    }

    public function closeEditSchedule(): void
    {
        $this->editingScheduleId = null;
        $this->editDueDate = '';
        $this->editLateFee = '0';
        $this->editNotes = '';
    }

    public function saveSchedule(InstallmentContractService $contracts): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'editingScheduleId' => ['required', 'integer'],
            'editDueDate' => ['required', 'date'],
            'editLateFee' => ['required', 'numeric', 'min:0', 'max:100000'],
            'editNotes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'editDueDate' => 'تاريخ الاستحقاق',
            'editLateFee' => 'رسوم التأخير',
            'editNotes' => 'ملاحظات القسط',
        ]);

        $schedule = $this->findSchedule((int) $this->editingScheduleId);

        $contracts->updateScheduleDetails(
            schedule: $schedule,
            admin: auth()->user(),
            dueDate: $this->editDueDate,
            lateFeeAmount: (float) $this->editLateFee,
            notes: $this->editNotes,
        );

        $this->closeEditSchedule();
        $this->reloadContract();
        $this->flash('تم تحديث بيانات القسط.');
    }

    public function saveAdminNotes(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'adminNotesDraft' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'adminNotesDraft' => 'ملاحظات الإدارة',
        ]);

        $this->contract->update([
            'admin_notes' => filled($this->adminNotesDraft) ? trim($this->adminNotesDraft) : null,
        ]);

        $this->reloadContract();
        $this->adminNotesDraft = (string) ($this->contract->admin_notes ?? '');
        $this->flash('تم حفظ ملاحظات الإدارة.');
    }

    public function markPaid(int $scheduleId, InstallmentPaymentService $payments): void
    {
        $this->openManualPayment($scheduleId);
    }

    public function waive(int $scheduleId): void
    {
        $this->openWaive($scheduleId);
    }

    public function cancelContract(InstallmentContractService $contracts): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'cancelNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $contracts->cancelContract(
                $this->contract,
                auth()->user(),
                filled($this->cancelNotes) ? $this->cancelNotes : 'إلغاء من لوحة الإدارة',
            );
        } catch (ValidationException $e) {
            $this->flash(collect($e->errors())->flatten()->first() ?: 'تعذر إلغاء العقد.', 'warn');

            return;
        }

        $this->cancelNotes = '';
        $this->reloadContract();
        $this->flash('تم إلغاء العقد.');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-contracts'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.installment-contracts'), 'label' => 'عقود التقسيط'],
        ['label' => $contract->contract_no],
    ],
])

@php
    $currency = $contract->currency ?: 'SAR';
    $schedules = $contract->schedules;
    $paidCount = $schedules->where('status', 'paid')->count();
    $overdueCount = $schedules->filter(fn ($s) => $s->displayStatus() === 'overdue')->count();
    $pendingCount = $schedules->filter(fn ($s) => $s->isPayable())->count();
    $payments = $schedules->flatMap->payments->sortByDesc('paid_at');
    $pendingOrders = Order::query()
        ->whereIn('installment_schedule_id', $schedules->pluck('id'))
        ->where('status', 'pending_payment')
        ->orderByDesc('created_at')
        ->get()
        ->keyBy('installment_schedule_id');
    $checkoutItems = is_array($contract->checkout_items) ? $contract->checkout_items : [];
    $canManage = auth()->user()?->canAdmin('installments.manage');
    $statusClass = match ($contract->status) {
        'active', 'completed' => 'is-success',
        'suspended', 'defaulted' => 'is-danger',
        'pending_signature' => 'is-warning',
        'cancelled' => 'is-muted',
        default => '',
    };
@endphp

<div class="icv-page">
    @include('partials.admin.toast', [
        'message' => $flashMessage,
        'type' => $flashType === 'warn' ? 'warn' : ($flashType === 'info' ? 'info' : ($flashType === 'error' ? 'error' : 'success')),
        'key' => $flashKey,
        'dismissMethod' => 'dismissFlash',
        'duration' => 7000,
    ])

    <section class="icv-hero">
        <div class="icv-hero__main">
            <span class="icv-hero__icon"><i class="fa-solid fa-file-signature"></i></span>
            <div>
                <span class="icv-hero__eyebrow"><i class="fa-solid fa-hashtag"></i> {{ $contract->contract_no }}</span>
                <h1>{{ $contract->title }}</h1>
                <p>
                    {{ $contract->student?->name_ar ?? $contract->user?->displayName() ?? '—' }}
                    @if ($contract->student?->academic_id)
                        · <span dir="ltr">{{ $contract->student->academic_id }}</span>
                    @endif
                    @if ($contract->template)
                        · {{ $contract->template->name_ar }}
                    @endif
                </p>
            </div>
        </div>
        <div class="icv-hero__side">
            <span class="icv-status {{ $statusClass }}">{{ $contract->statusLabel() }}</span>
            <div class="icv-hero__actions">
                <a href="{{ route('admin.installment-contracts') }}" class="admin-btn-secondary admin-btn-secondary--sm">
                    <i class="fa-solid fa-arrow-right"></i> العودة للعقود
                </a>
                @if ($canManage && in_array($contract->status, ['active', 'pending_signature', 'suspended'], true))
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm icv-btn-danger" onclick="document.getElementById('icv-cancel').open = true">
                        <i class="fa-solid fa-ban"></i> إلغاء العقد
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section class="icv-kpis">
        <article>
            <span>الإجمالي</span>
            <strong>{{ number_format((float) $contract->total_amount, 2) }} <small>{{ $currency }}</small></strong>
        </article>
        <article>
            <span>المسدّد</span>
            <strong class="is-success">{{ number_format((float) $contract->paid_amount, 2) }} <small>{{ $currency }}</small></strong>
        </article>
        <article>
            <span>المتبقي</span>
            <strong class="{{ (float) $contract->remaining_balance > 0 ? 'is-warning' : 'is-success' }}">{{ number_format((float) $contract->remaining_balance, 2) }} <small>{{ $currency }}</small></strong>
        </article>
        <article class="is-emphasis">
            <span>التقدم</span>
            <strong>{{ $contract->progressPercent() }}%</strong>
            <div class="icv-progress"><i style="width: {{ min(100, $contract->progressPercent()) }}%"></i></div>
        </article>
        <article>
            <span>أقساط قابلة للسداد</span>
            <strong>{{ $pendingCount }}</strong>
        </article>
        <article>
            <span>متأخرة</span>
            <strong class="{{ $overdueCount > 0 ? 'is-danger' : '' }}">{{ $overdueCount }}</strong>
        </article>
    </section>

    <div class="icv-layout">
        <div class="icv-main">
            @if ($generatedPayUrl)
                <section class="icv-panel icv-panel--link">
                    <div class="icv-panel__head">
                        <div>
                            <h2><i class="fa-solid fa-link"></i> رابط السداد جاهز</h2>
                            <p>يمكن نسخه للطالب أو فتحه مباشرة. يعمل بعد تسجيل دخول الطالب لملكية العقد.</p>
                        </div>
                        <span class="icv-panel__chip"><i class="fa-solid fa-bolt"></i> جاهز للاستخدام</span>
                    </div>
                    <div class="icv-copy">
                        <input id="icvPayUrl" type="text" readonly dir="ltr" value="{{ $generatedPayUrl }}">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('icvPayUrl').value); this.querySelector('span').textContent='تم النسخ';">
                            <i class="fa-regular fa-copy"></i> <span>نسخ</span>
                        </button>
                        <a href="{{ $generatedPayUrl }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm">فتح</a>
                    </div>
                </section>
            @endif

            <section class="icv-panel">
                <div class="icv-panel__head">
                    <div>
                        <h2><i class="fa-solid fa-list-ol"></i> جدول الأقساط</h2>
                        <p>{{ $paidCount }} مدفوع من أصل {{ $schedules->count() }} قسطًا.</p>
                    </div>
                    <span class="icv-panel__chip">{{ $schedules->count() }} أقساط</span>
                </div>

                <div class="icv-schedule-list">
                    @foreach ($schedules as $schedule)
                        @php
                            $display = $schedule->displayStatus();
                            $pendingOrder = $pendingOrders->get($schedule->id);
                            $payUrl = $this->paymentUrlFor($schedule);
                            $badge = match ($display) {
                                'paid' => 'is-success',
                                'overdue' => 'is-danger',
                                'waived' => 'is-muted',
                                'cancelled' => 'is-muted',
                                default => 'is-warning',
                            };
                        @endphp
                        <article class="icv-schedule {{ $badge }}" wire:key="sch-{{ $schedule->id }}">
                            <div class="icv-schedule__top">
                                <div>
                                    <span class="icv-schedule__seq">#{{ $schedule->sequence }}</span>
                                    <strong>{{ $schedule->label ?: 'قسط '.$schedule->sequence }}</strong>
                                    <span class="icv-badge {{ $badge }}">{{ InstallmentOptions::scheduleStatusLabel($display) }}</span>
                                </div>
                                <div class="icv-schedule__amount">
                                    <strong>{{ number_format($schedule->totalDue(), 2) }} {{ $currency }}</strong>
                                    @if ((float) $schedule->late_fee_amount > 0)
                                        <small>يشمل رسوم تأخير {{ number_format((float) $schedule->late_fee_amount, 2) }}</small>
                                    @endif
                                </div>
                            </div>

                            <div class="icv-schedule__meta">
                                <span><i class="fa-solid fa-calendar-day"></i> استحقاق <b dir="ltr">{{ $schedule->due_date->format('Y-m-d') }}</b></span>
                                <span><i class="fa-solid fa-coins"></i> أصل القسط {{ number_format((float) $schedule->amount, 2) }}</span>
                                @if ($schedule->paid_at)
                                    <span><i class="fa-solid fa-circle-check"></i> سُدّد {{ $schedule->paid_at->format('Y-m-d H:i') }}</span>
                                @endif
                                @if ($pendingOrder)
                                    <span><i class="fa-solid fa-hourglass-half"></i> طلب معلّق <b dir="ltr">{{ $pendingOrder->reference }}</b></span>
                                @endif
                            </div>

                            @if ($schedule->admin_notes)
                                <div class="icv-note">{{ $schedule->admin_notes }}</div>
                            @endif

                            @if ($canManage)
                                <div class="icv-schedule__actions">
                                    @if ($schedule->isPayable())
                                        <div class="icv-schedule__actions-label">إجراءات السداد</div>
                                        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="generatePaymentLink({{ $schedule->id }})">
                                            <i class="fa-solid fa-link"></i> إنشاء رابط سداد
                                        </button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="sendPaymentLink({{ $schedule->id }})">
                                            <i class="fa-solid fa-paper-plane"></i> إرسال للطالب
                                        </button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" onclick="navigator.clipboard.writeText(@js($payUrl))">
                                            <i class="fa-regular fa-copy"></i> نسخ الرابط
                                        </button>
                                        <a href="{{ $payUrl }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> فتح صفحة السداد
                                        </a>
                                        @if ($pendingOrder)
                                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelPaymentLink({{ $schedule->id }})" wire:confirm="إلغاء طلب السداد المعلّق؟">
                                                <i class="fa-solid fa-link-slash"></i> إلغاء الرابط
                                            </button>
                                        @endif

                                        <div class="icv-schedule__actions-label">إدارة القسط</div>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="openManualPayment({{ $schedule->id }})">
                                            <i class="fa-solid fa-hand-holding-dollar"></i> تسجيل سداد
                                        </button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="openEditSchedule({{ $schedule->id }})">
                                            <i class="fa-solid fa-pen"></i> تعديل
                                        </button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="openWaive({{ $schedule->id }})">
                                            <i class="fa-solid fa-gift"></i> إعفاء
                                        </button>
                                    @elseif ($display === 'paid')
                                        <a href="{{ $payUrl }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm">
                                            <i class="fa-solid fa-receipt"></i> عرض قسيمة القسط
                                        </a>
                                    @else
                                        <a href="{{ $payUrl }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm">
                                            <i class="fa-solid fa-eye"></i> عرض صفحة القسط
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if ($schedule->payments->isNotEmpty())
                                <div class="icv-mini-payments">
                                    @foreach ($schedule->payments as $payment)
                                        <div>
                                            <strong>{{ number_format((float) $payment->amount, 2) }} {{ $currency }}</strong>
                                            <span>{{ $payment->gateway }} · {{ $payment->gateway_ref ?: '—' }}</span>
                                            <span>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                            <span>{{ $payment->recorder?->displayName() ?? 'نظام' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="icv-panel">
                <div class="icv-panel__head">
                    <div>
                        <h2><i class="fa-solid fa-wallet"></i> سجل المدفوعات</h2>
                        <p>كل عمليات السداد المسجّلة على أقساط هذا العقد.</p>
                    </div>
                </div>
                @if ($payments->isEmpty())
                    <div class="icv-empty">لا توجد مدفوعات مسجّلة بعد.</div>
                @else
                    <div class="admin-table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>القسط</th>
                                    <th>المبلغ</th>
                                    <th>البوابة</th>
                                    <th>المرجع</th>
                                    <th>التاريخ</th>
                                    <th>بواسطة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr wire:key="pay-{{ $payment->id }}">
                                        <td>#{{ $payment->schedule?->sequence }} — {{ $payment->schedule?->label }}</td>
                                        <td>{{ number_format((float) $payment->amount, 2) }} {{ $currency }}</td>
                                        <td>{{ $payment->gateway }}</td>
                                        <td dir="ltr">{{ $payment->gateway_ref ?: '—' }}</td>
                                        <td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td>{{ $payment->recorder?->displayName() ?? 'نظام' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <aside class="icv-side">
            <section class="icv-panel">
                <h3><i class="fa-solid fa-user-graduate"></i> بيانات الطالب</h3>
                <dl class="icv-dl">
                    <div><dt>الاسم</dt><dd>{{ $contract->student?->name_ar ?? $contract->user?->displayName() ?? '—' }}</dd></div>
                    <div><dt>البريد</dt><dd dir="ltr">{{ $contract->user?->email ?? $contract->student?->email ?? '—' }}</dd></div>
                    <div><dt>الجوال</dt><dd dir="ltr">{{ $contract->user?->phone ?? '—' }}</dd></div>
                    <div><dt>الرقم الأكاديمي</dt><dd dir="ltr">{{ $contract->student?->academic_id ?? '—' }}</dd></div>
                    <div><dt>الحالة الأكاديمية</dt><dd>{{ $contract->student?->academic_status ?? '—' }}</dd></div>
                </dl>
                @if ($contract->user)
                    <a href="{{ route('admin.users.show', $contract->user) }}" class="admin-btn-secondary admin-btn-secondary--sm" style="margin-top:.75rem;">ملف المستخدم</a>
                @endif
            </section>

            <section class="icv-panel">
                <h3><i class="fa-solid fa-file-contract"></i> بيانات العقد</h3>
                <dl class="icv-dl">
                    <div><dt>الخطة</dt><dd>{{ $contract->template?->name_ar ?? '—' }}</dd></div>
                    <div><dt>البرنامج</dt><dd>{{ $contract->program?->name_ar ?? '—' }}</dd></div>
                    <div><dt>الدفعة</dt><dd>{{ $contract->batch?->name_ar ?? '—' }}</dd></div>
                    <div><dt>تاريخ البداية</dt><dd dir="ltr">{{ $contract->starts_at?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt>أُنشئ بواسطة</dt><dd>{{ $contract->creator?->displayName() ?? '—' }}</dd></div>
                    <div><dt>تاريخ الإنشاء</dt><dd>{{ $contract->created_at?->format('Y-m-d H:i') }}</dd></div>
                    @if ($contract->suspended_at)
                        <div><dt>تاريخ الإيقاف</dt><dd>{{ $contract->suspended_at->format('Y-m-d H:i') }}</dd></div>
                        <div><dt>سبب الإيقاف</dt><dd>{{ $contract->suspension_reason ?: '—' }}</dd></div>
                    @endif
                    @if ($contract->completed_at)
                        <div><dt>تاريخ الاكتمال</dt><dd>{{ $contract->completed_at->format('Y-m-d H:i') }}</dd></div>
                    @endif
                </dl>
            </section>

            <section class="icv-panel">
                <h3><i class="fa-solid fa-signature"></i> التوقيع الإلكتروني</h3>
                @if ($contract->isStudentSigned())
                    <div class="icv-state is-success">
                        <span class="icv-state__icon"><i class="fa-solid fa-check"></i></span>
                        <div>
                            <strong>تم التوقيع</strong>
                            <p>{{ $contract->student_signature_name }} · {{ $contract->student_signed_at?->format('Y-m-d H:i') }}</p>
                            @if ($contract->student_signature_ip)
                                <p dir="ltr">IP: {{ $contract->student_signature_ip }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($contract->student_signature_path)
                        <div class="icv-signed" style="margin-top:.75rem;">
                            <img src="{{ asset('storage/'.$contract->student_signature_path) }}" alt="توقيع الطالب">
                        </div>
                    @endif
                @elseif ($contract->requires_student_signature)
                    <div class="icv-state is-warning">
                        <span class="icv-state__icon"><i class="fa-solid fa-hourglass-half"></i></span>
                        <div>
                            <strong>بانتظار التوقيع</strong>
                            <p>يلزم توقيع الطالب الإلكتروني قبل متابعة السداد.</p>
                        </div>
                    </div>
                @else
                    <div class="icv-state">
                        <span class="icv-state__icon"><i class="fa-solid fa-minus"></i></span>
                        <div>
                            <strong>التوقيع غير مطلوب</strong>
                            <p>لم يُطلب التوقيع الإلكتروني لهذا العقد.</p>
                        </div>
                    </div>
                @endif
            </section>

            @if ($checkoutItems !== [])
                <section class="icv-panel">
                    <h3><i class="fa-solid fa-cart-shopping"></i> عناصر الشراء المرتبطة</h3>
                    <ul class="icv-items">
                        @foreach ($checkoutItems as $item)
                            <li>
                                <strong>{{ $item['course_title'] ?? ('دورة #'.($item['course_id'] ?? '—')) }}</strong>
                                <span>{{ $item['delivery_type'] ?? '—' }} · {{ number_format((float) ($item['price'] ?? 0), 2) }} {{ $currency }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="icv-panel">
                <h3><i class="fa-solid fa-sticky-note"></i> ملاحظات الإدارة</h3>
                @if ($canManage)
                    <textarea class="admin-control" rows="4" wire:model="adminNotesDraft" placeholder="ملاحظات داخلية عن العقد..."></textarea>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" style="margin-top:.6rem;" wire:click="saveAdminNotes">حفظ الملاحظات</button>
                @else
                    <div class="icv-note">{{ $contract->admin_notes ?: 'لا توجد ملاحظات.' }}</div>
                @endif
            </section>

            @if ($canManage && in_array($contract->status, ['active', 'pending_signature', 'suspended'], true))
                <details id="icv-cancel" class="icv-panel icv-panel--danger">
                    <summary>منطقة الخطر — إلغاء العقد</summary>
                    <p>لا يمكن إلغاء عقد يحتوي أقساطاً مدفوعة قبل معالجة الاسترداد.</p>
                    <textarea class="admin-control" rows="3" wire:model="cancelNotes" placeholder="سبب الإلغاء (اختياري)"></textarea>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" style="margin-top:.6rem;" wire:click="cancelContract" wire:confirm="تأكيد إلغاء هذا العقد؟">تأكيد الإلغاء</button>
                </details>
            @endif
        </aside>
    </div>
</div>

@if ($manualScheduleId)
    @php $manualSchedule = $schedules->firstWhere('id', $manualScheduleId); @endphp
    <div class="icv-modal" wire:click.self="closeManualPayment">
        <div class="icv-modal__card">
            <div class="icv-modal__head">
                <h3>تسجيل سداد يدوي</h3>
                <button type="button" wire:click="closeManualPayment"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p>القسط #{{ $manualSchedule?->sequence }} — المستحق {{ number_format((float) ($manualSchedule?->totalDue() ?? 0), 2) }} {{ $currency }}</p>
            <div class="admin-field">
                <label>مرجع الدفع (اختياري)</label>
                <input type="text" class="admin-control" wire:model="manualPaymentRef" dir="ltr" placeholder="BANK-REF / TRANSFER-ID">
                @error('manualPaymentRef')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>ملاحظات</label>
                <textarea class="admin-control" rows="3" wire:model="manualNotes"></textarea>
                @error('manualNotes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="icv-modal__actions">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeManualPayment">إلغاء</button>
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="confirmManualPayment" wire:confirm="تأكيد تسجيل السداد؟">تأكيد السداد</button>
            </div>
        </div>
    </div>
@endif

@if ($waiveScheduleId)
    @php $waiveSchedule = $schedules->firstWhere('id', $waiveScheduleId); @endphp
    <div class="icv-modal" wire:click.self="closeWaive">
        <div class="icv-modal__card">
            <div class="icv-modal__head">
                <h3>إعفاء قسط</h3>
                <button type="button" wire:click="closeWaive"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p>القسط #{{ $waiveSchedule?->sequence }} — {{ number_format((float) ($waiveSchedule?->amount ?? 0), 2) }} {{ $currency }}</p>
            <div class="admin-field">
                <label>سبب الإعفاء *</label>
                <textarea class="admin-control" rows="3" wire:model="waiveNotes"></textarea>
                @error('waiveNotes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="icv-modal__actions">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeWaive">إلغاء</button>
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="confirmWaive" wire:confirm="تأكيد إعفاء هذا القسط؟">تأكيد الإعفاء</button>
            </div>
        </div>
    </div>
@endif

@if ($editingScheduleId)
    @php $editSchedule = $schedules->firstWhere('id', $editingScheduleId); @endphp
    <div class="icv-modal" wire:click.self="closeEditSchedule">
        <div class="icv-modal__card">
            <div class="icv-modal__head">
                <h3>تعديل القسط #{{ $editSchedule?->sequence }}</h3>
                <button type="button" wire:click="closeEditSchedule"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="admin-field">
                <label>تاريخ الاستحقاق *</label>
                <input type="date" class="admin-control" wire:model="editDueDate">
                @error('editDueDate')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>رسوم التأخير ({{ $currency }})</label>
                <input type="number" min="0" step="0.01" class="admin-control" wire:model="editLateFee">
                @error('editLateFee')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>ملاحظات القسط</label>
                <textarea class="admin-control" rows="3" wire:model="editNotes"></textarea>
                @error('editNotes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="icv-modal__actions">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeEditSchedule">إلغاء</button>
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="saveSchedule">حفظ التعديل</button>
            </div>
        </div>
    </div>
@endif

@include('partials.admin.shell-end')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-installment-contract-view.css') }}?v=1">
@endpush
