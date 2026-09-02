<?php

use App\Models\Order;
use App\Services\RefundService;
use App\Support\OrderOptions;
use App\Support\RefundOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('طلبات الاسترداد | مركز التعلم المستمر')]
class extends Component
{
    public ?int $selectedOrderId = null;

    public string $reason = '';

    public ?string $flashMessage = null;

    #[Computed]
    public function refunds()
    {
        return app(RefundService::class)->forUser(auth()->user());
    }

    #[Computed]
    public function eligibleOrders()
    {
        $service = app(RefundService::class);

        return Order::query()
            ->where('user_id', auth()->id())
            ->where('status', 'paid')
            ->latest()
            ->get()
            ->filter(fn (Order $order) => ! $service->openForOrder($order));
    }

    public function submit(): void
    {
        $this->validate([
            'selectedOrderId' => ['required', 'integer'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [], [
            'selectedOrderId' => 'الطلب',
            'reason' => 'سبب الاسترداد',
        ]);

        $order = Order::query()->where('user_id', auth()->id())->findOrFail($this->selectedOrderId);

        app(RefundService::class)->request(auth()->user(), $order, $this->reason);

        $this->reset(['selectedOrderId', 'reason']);
        $this->flashMessage = 'تم تقديم طلب الاسترداد وسيتم مراجعته من الإدارة المالية.';
        unset($this->refunds, $this->eligibleOrders);
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'user-requests', 'portalTitle' => 'طلبات الاسترداد'])

<div class="portal-dashboard portal-refunds-page">
    @if ($flashMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ $flashMessage }}</div>
        </div>
    @endif

    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">طلبات الاسترداد</h1>
            <p class="portal-orders-intro__desc">قدّم طلب استرداد للطلبات المدفوعة وتابع حالة المراجعة.</p>
        </div>
        <a href="{{ route('user-requests', ['locale' => $locale]) }}" class="portal-btn-secondary">مركز طلباتي</a>
    </div>

    <div class="portal-settings-grid">
        <section class="portal-panel portal-settings-form">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-rotate-left"></i> طلب استرداد جديد</h2>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                @if ($this->eligibleOrders->isEmpty())
                    <p class="text-muted small mb-0">لا توجد طلبات مدفوعة مؤهلة لطلب استرداد حالياً.</p>
                @else
                    <form wire:submit="submit">
                        <div class="mb-3">
                            <label class="form-label">اختر الطلب</label>
                            <select class="form-select @error('selectedOrderId') is-invalid @enderror" wire:model="selectedOrderId">
                                <option value="">— اختر طلباً —</option>
                                @foreach ($this->eligibleOrders as $order)
                                    <option value="{{ $order->id }}">{{ $order->reference }} — {{ number_format((float) $order->total, 2) }} ر.س</option>
                                @endforeach
                            </select>
                            @error('selectedOrderId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">سبب الاسترداد</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" rows="4" wire:model="reason" placeholder="اشرح سبب طلب الاسترداد..."></textarea>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">تقديم الطلب</button>
                    </form>
                @endif
            </div>
        </section>

        <section class="portal-panel">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-list"></i> سجل الطلبات</h2>
            </div>
            <div class="portal-panel__body">
                @if ($this->refunds->isEmpty())
                    <div class="portal-empty portal-empty--compact"><p>لا توجد طلبات استرداد</p></div>
                @else
                    <div class="portal-refunds-list">
                        @foreach ($this->refunds as $refund)
                            <article class="portal-refund-item" wire:key="rf-{{ $refund->id }}">
                                <div>
                                    <strong dir="ltr">{{ $refund->reference_no }}</strong>
                                    <div class="portal-refund-item__meta">
                                        طلب {{ $refund->order?->reference }} · {{ number_format((float) $refund->amount, 2) }} ر.س
                                    </div>
                                    <p class="portal-refund-item__reason">{{ $refund->reason }}</p>
                                    @if ($refund->admin_notes && in_array($refund->status, ['rejected', 'approved', 'processed'], true))
                                        <p class="portal-refund-item__admin"><strong>رد الإدارة:</strong> {{ $refund->admin_notes }}</p>
                                    @endif
                                </div>
                                <span class="portal-badge portal-badge--{{ $refund->status === 'processed' ? 'success' : ($refund->status === 'pending' ? 'warning' : ($refund->status === 'rejected' ? 'danger' : 'info')) }}">
                                    {{ RefundOptions::statusLabel($refund->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    .portal-refunds-list { display: flex; flex-direction: column; gap: 0.65rem; }
    .portal-refund-item { display: flex; justify-content: space-between; gap: 1rem; padding: 0.9rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; flex-wrap: wrap; }
    .portal-refund-item__meta { font-size: 0.78rem; color: #64748b; margin: 0.15rem 0; }
    .portal-refund-item__reason { margin: 0.35rem 0 0; font-size: 0.84rem; color: #475569; }
    .portal-refund-item__admin { margin: 0.35rem 0 0; font-size: 0.8rem; color: #334155; background: #f8fafc; padding: 0.45rem 0.6rem; border-radius: 6px; }
    .portal-badge--info { background: #dbeafe; color: #1d4ed8; }
    .portal-badge--danger { background: #fee2e2; color: #b91c1c; }
</style>
@endpush
