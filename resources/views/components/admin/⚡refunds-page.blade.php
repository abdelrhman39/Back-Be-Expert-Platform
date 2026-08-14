<?php

use App\Models\RefundRequest;
use App\Services\RefundService;
use App\Support\RefundOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'طلبات الاسترداد',
    'adminPageDesc' => 'مراجعة ومعالجة طلبات استرداد الطلاب',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'طلبات الاسترداد'],
    ],
])]
#[Title('الاسترداد | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $rejectId = null;

    public string $rejectReason = '';

    public ?string $savedMessage = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->canAdmin('refunds.manage') || auth()->user()?->canAdmin('refunds.view'),
            403
        );
    }

    #[Computed]
    public function canManage(): bool
    {
        return auth()->user()?->canAdmin('refunds.manage') ?? false;
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending' => RefundRequest::query()->where('status', 'pending')->count(),
            'approved' => RefundRequest::query()->where('status', 'approved')->count(),
            'processed' => RefundRequest::query()->where('status', 'processed')->count(),
            'rejected' => RefundRequest::query()->where('status', 'rejected')->count(),
            'total_amount_pending' => (float) RefundRequest::query()->where('status', 'pending')->sum('amount'),
        ];
    }

    public function setStatus(string $status): void
    {
        if (in_array($status, ['all', 'pending', 'approved', 'processed', 'rejected'], true)) {
            $this->status = $status;
            $this->resetPage();
        }
    }

    public function approve(int $id): void
    {
        abort_unless($this->canManage, 403);
        $refund = RefundRequest::query()->findOrFail($id);
        app(RefundService::class)->approve($refund, auth()->user());
        $this->savedMessage = 'تمت الموافقة على '.$refund->reference_no;
        unset($this->stats);
    }

    public function process(int $id): void
    {
        abort_unless($this->canManage, 403);
        $refund = RefundRequest::query()->findOrFail($id);
        app(RefundService::class)->markProcessed($refund, auth()->user());
        $this->savedMessage = 'تم تنفيذ الاسترداد '.$refund->reference_no;
        unset($this->stats);
    }

    public function startReject(int $id): void
    {
        abort_unless($this->canManage, 403);
        $this->rejectId = $id;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        abort_unless($this->canManage, 403);
        $this->validate(['rejectReason' => ['required', 'string', 'min:5', 'max:500']]);
        $refund = RefundRequest::query()->findOrFail($this->rejectId);
        app(RefundService::class)->reject($refund, auth()->user(), $this->rejectReason);
        $this->rejectId = null;
        $this->rejectReason = '';
        $this->savedMessage = 'تم رفض الطلب '.$refund->reference_no;
        unset($this->stats);
    }

    public function closeReject(): void
    {
        $this->rejectId = null;
        $this->rejectReason = '';
    }

    public function getListProperty()
    {
        return app(RefundService::class)->adminList($this->status, $this->search);
    }
};
?>

@include('partials.admin.shell-start', ['shellLayout' => 'app', 'shellSidebarActive' => route('admin.refunds')])

<div class="admin-module refunds-hub">
    @if ($savedMessage)
        <div class="admin-module__flash admin-module__flash--success">
            <i class="fa-solid fa-circle-check"></i> {{ $savedMessage }}
        </div>
    @endif

    <section class="admin-module-hero admin-module-hero--finance">
        <div class="admin-module-hero__main">
            <h2>مركز طلبات الاسترداد</h2>
            <p>مراجعة طلبات الطلاب، الموافقة المالية، وتنفيذ الاسترداد مع تحديث حالة الطلب تلقائياً.</p>
            <div class="admin-module-flow" style="margin-top: 0.85rem;">
                <span class="admin-module-flow__step"><i class="fa-solid fa-inbox"></i> طلب الطالب</span>
                <i class="fa-solid fa-chevron-left admin-module-flow__arrow"></i>
                <span class="admin-module-flow__step"><i class="fa-solid fa-check"></i> موافقة</span>
                <i class="fa-solid fa-chevron-left admin-module-flow__arrow"></i>
                <span class="admin-module-flow__step"><i class="fa-solid fa-money-bill-transfer"></i> تنفيذ</span>
                <i class="fa-solid fa-chevron-left admin-module-flow__arrow"></i>
                <span class="admin-module-flow__step"><i class="fa-solid fa-receipt"></i> طلب مسترد</span>
            </div>
        </div>
        <div class="admin-module-hero__aside">
            <div class="admin-module-chip admin-module-chip--warn">
                <i class="fa-solid fa-hourglass-half"></i>
                <div>
                    <strong>بانتظار المراجعة</strong>
                    <span>{{ $this->stats['pending'] }} طلب · {{ number_format($this->stats['total_amount_pending'], 2) }} ر.س</span>
                </div>
            </div>
            @if (! $this->canManage)
                <div class="admin-module-chip">
                    <i class="fa-solid fa-eye"></i>
                    <div>
                        <strong>وضع العرض</strong>
                        <span>لديك صلاحية العرض فقط</span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <div class="admin-module-kpis">
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--amber"><i class="fa-solid fa-clock"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['pending'] }}</span>
                <span class="admin-module-kpi__label">قيد المراجعة</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--blue"><i class="fa-solid fa-thumbs-up"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['approved'] }}</span>
                <span class="admin-module-kpi__label">موافق عليها</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--green"><i class="fa-solid fa-circle-check"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['processed'] }}</span>
                <span class="admin-module-kpi__label">تم الاسترداد</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--rose"><i class="fa-solid fa-ban"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['rejected'] }}</span>
                <span class="admin-module-kpi__label">مرفوضة</span>
            </div>
        </div>
    </div>

    <div class="refunds-toolbar">
        <nav class="admin-module-pipeline">
            @foreach (['all' => 'الكل', 'pending' => 'قيد المراجعة', 'approved' => 'موافق', 'processed' => 'مسترد', 'rejected' => 'مرفوض'] as $key => $label)
                @php
                    $count = $key === 'all'
                        ? array_sum(array_intersect_key($this->stats, array_flip(['pending','approved','processed','rejected'])))
                        : ($this->stats[$key] ?? 0);
                @endphp
                <button type="button" @class(['admin-module-pipeline__btn', 'is-active' => $status === $key]) wire:click="setStatus('{{ $key }}')">
                    {{ $label }}
                    <span class="admin-module-pipeline__count">{{ $count }}</span>
                </button>
            @endforeach
        </nav>
        <div class="refunds-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="بحث بالمرجع أو الطالب أو الطلب...">
        </div>
    </div>

    @if ($this->list->isEmpty())
        <div class="refund-empty">
            <i class="fa-solid fa-inbox"></i>
            <h3>لا توجد طلبات</h3>
            <p>لم يُعثر على طلبات استرداد{{ $status !== 'all' ? ' بهذه الحالة' : '' }}.</p>
        </div>
    @else
        <div class="refunds-cards">
            @foreach ($this->list as $refund)
                <article @class(['refund-card', 'refund-card--'.$refund->status]) wire:key="rf-{{ $refund->id }}">
                    <div class="refund-card__main">
                        <span class="refund-card__ref">{{ $refund->reference_no }}</span>
                        <div class="refund-card__student">{{ $refund->user?->displayName() }}</div>
                        <div class="refund-card__meta">
                            <span><i class="fa-solid fa-receipt"></i> {{ $refund->order?->reference }}</span>
                            <span><i class="fa-solid fa-calendar"></i> {{ $refund->created_at->translatedFormat('d M Y') }}</span>
                            @if ($refund->user?->email)
                                <span dir="ltr"><i class="fa-solid fa-envelope"></i> {{ $refund->user->email }}</span>
                            @endif
                        </div>
                        <p class="refund-card__reason">{{ $refund->reason }}</p>
                        @if ($refund->admin_notes && in_array($refund->status, ['rejected', 'approved', 'processed'], true))
                            <p class="refund-card__reason" style="background:#fffbeb;border-color:#fde68a;">
                                <strong>ملاحظة الإدارة:</strong> {{ $refund->admin_notes }}
                            </p>
                        @endif
                    </div>
                    <div class="refund-card__aside">
                        <span class="refund-card__amount">{{ number_format((float) $refund->amount, 2) }} <small>ر.س</small></span>
                        <span class="admin-badge {{ RefundOptions::statusBadgeClass($refund->status) }}">{{ RefundOptions::statusLabel($refund->status) }}</span>
                        @if ($this->canManage)
                            <div class="refund-card__actions">
                                @if ($refund->status === 'pending')
                                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="approve({{ $refund->id }})">
                                        <i class="fa-solid fa-check"></i> موافقة
                                    </button>
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="startReject({{ $refund->id }})">
                                        <i class="fa-solid fa-xmark"></i> رفض
                                    </button>
                                @elseif ($refund->status === 'approved')
                                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="process({{ $refund->id }})">
                                        <i class="fa-solid fa-money-bill-transfer"></i> تنفيذ
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div style="margin-top:1rem;">{{ $this->list->links() }}</div>
    @endif

    <div style="margin-top:0.5rem;">
        <a href="{{ route('admin.audit-log', ['group' => 'refunds']) }}" class="admin-btn-secondary admin-btn-secondary--sm">
            <i class="fa-solid fa-list-check"></i> سجل تدقيق الاسترداد
        </a>
        <a href="{{ route('admin.orders') }}" class="admin-btn-secondary admin-btn-secondary--sm">طلبات الشراء</a>
    </div>
</div>

@if ($rejectId)
    <div class="admin-modal-backdrop" wire:click.self="closeReject">
        <div class="admin-modal" role="dialog">
            <div class="admin-modal__head">
                <h3><i class="fa-solid fa-xmark"></i> رفض طلب الاسترداد</h3>
            </div>
            <div class="admin-modal__body">
                <label class="admin-field" style="display:block;margin-bottom:0.35rem;font-size:0.82rem;font-weight:700;">سبب الرفض (يُرسل للطالب)</label>
                <textarea class="admin-control" rows="4" wire:model="rejectReason" placeholder="اشرح سبب الرفض بوضوح..."></textarea>
                @error('rejectReason')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-modal__foot">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeReject">إلغاء</button>
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="confirmReject">تأكيد الرفض</button>
            </div>
        </div>
    </div>
@endif

@include('partials.admin.shell-end')
