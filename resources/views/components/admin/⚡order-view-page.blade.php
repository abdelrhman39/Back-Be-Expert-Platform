<?php

use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Support\OrderOptions;
use App\Support\PaymentMethods;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تفاصيل الطلب | لوحة التحكم')]
class extends Component
{
    public Order $order;

    public string $paymentRef = '';

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()?->canAdmin('orders.view'), 403);

        $this->order = $order->load(['user', 'items']);
        $this->paymentRef = $order->payment_ref ?? '';
    }

    public function markPaid(OrderPaymentService $payments): void
    {
        abort_unless(auth()->user()?->canAdmin('orders.manage'), 403);

        if (! OrderOptions::canManageStatus($this->order->status)) {
            return;
        }

        $reference = $this->paymentRef !== ''
            ? $this->paymentRef
            : 'MANUAL-ORDER-'.$this->order->id.'-'.now()->format('YmdHis');

        $this->order = $payments->markAsPaid(
            $this->order,
            'manual',
            $reference,
            $reference,
        );
        session()->flash('admin_message', 'تم تعليم الطلب كمدفوع.');
    }

    public function markPending(): void
    {
        abort_unless(auth()->user()?->canAdmin('orders.manage'), 403);

        if (! OrderOptions::canManageStatus($this->order->status)) {
            return;
        }

        $this->order->update(['status' => 'pending_payment']);
        $this->order->refresh();
        session()->flash('admin_message', 'تم إرجاع الطلب إلى «بانتظار الدفع».');
    }

    public function cancelOrder(): void
    {
        abort_unless(auth()->user()?->canAdmin('orders.manage'), 403);

        if (! OrderOptions::canManageStatus($this->order->status)) {
            return;
        }

        $this->order->update(['status' => 'cancelled']);
        $this->order->refresh();
        session()->flash('admin_message', 'تم إلغاء الطلب.');
    }

    public function savePaymentRef(): void
    {
        abort_unless(auth()->user()?->canAdmin('orders.manage'), 403);

        $this->validate([
            'paymentRef' => ['nullable', 'string', 'max:255'],
        ]);

        $this->order->update(['payment_ref' => $this->paymentRef !== '' ? $this->paymentRef : null]);
        $this->order->refresh();
        session()->flash('admin_message', 'تم حفظ مرجع الدفع.');
    }
};
?>

@php
    use App\Support\OrderOptions;
    use App\Support\PaymentMethods;

    $itemsTotal = $order->items->sum(fn ($item) => (float) $item->price);
    $canManage = auth()->user()?->canAdmin('orders.manage') && OrderOptions::canManageStatus($order->status);
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.orders'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.orders'), 'label' => 'طلبات الشراء'],
        ['label' => $order->reference],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="order-view-hero admin-crud-card">
    <div class="order-view-hero__main">
        <div class="order-view-hero__ref">
            <span class="order-view-hero__label">رقم الطلب</span>
            <code class="admin-code order-view-hero__code">{{ $order->reference }}</code>
        </div>
        <span @class(['admin-badge', 'order-view-hero__badge', OrderOptions::statusBadgeClass($order->status)])>
            {{ OrderOptions::statusLabel($order->status) }}
        </span>
    </div>
    <div class="order-view-hero__total">
        <span class="order-view-hero__label">إجمالي الطلب</span>
        <strong dir="ltr">{{ number_format((float) $order->total, 2) }} <small>ر.س</small></strong>
    </div>
    <div class="order-view-hero__meta">
        <span>{{ $order->created_at?->format('Y-m-d H:i') }}</span>
        @if ($order->created_at)
            <span class="admin-crud-card__meta">— {{ $order->created_at->diffForHumans() }}</span>
        @endif
    </div>
</section>

<div class="order-view-grid">
    <section class="student-profile-card">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <h2 class="student-profile-card__title">بيانات العميل</h2>
        </header>
        <div class="student-profile-card__body">
            @if ($order->user)
                <div class="admin-detail-fields admin-detail-fields--2">
                    @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'الاسم', 'value' => '<a href="'.route('admin.users.show', $order->user).'" class="admin-link">'.e($order->user->displayName()).'</a>'])
                    @include('partials.admin.detail-field', ['icon' => 'mail', 'label' => 'البريد', 'value' => $order->user->email ? '<span dir="ltr">'.e($order->user->email).'</span>' : '—'])
                    @include('partials.admin.detail-field', ['icon' => 'phone', 'label' => 'الجوال', 'value' => $order->user->phone ? '<span dir="ltr">'.e($order->user->phone).'</span>' : '—'])
                    @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'الهوية', 'value' => $order->user->national_id ? '<span dir="ltr">'.e($order->user->national_id).'</span>' : '—'])
                </div>
            @else
                <p class="admin-detail-empty">لا يوجد مستخدم مرتبط.</p>
            @endif
        </div>
    </section>

    <section class="student-profile-card">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </span>
            <h2 class="student-profile-card__title">الدفع</h2>
        </header>
        <div class="student-profile-card__body">
            <div class="admin-detail-fields admin-detail-fields--2">
                @include('partials.admin.detail-field', ['icon' => 'tag', 'label' => 'طريقة الدفع', 'value' => PaymentMethods::label($order->payment_method ?? '') ?: '—'])
                @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'حالة الدفع', 'value' => '<span class="admin-badge '.OrderOptions::statusBadgeClass($order->status).'">'.e(OrderOptions::statusLabel($order->status)).'</span>'])
            </div>
            @canAdmin('orders.manage')
                <form wire:submit="savePaymentRef" class="order-payment-ref-form">
                    <div class="admin-field">
                        <label>مرجع الدفع / رقم العملية</label>
                        <div class="order-payment-ref-form__row">
                            <input type="text" class="admin-control" wire:model="paymentRef" dir="ltr" placeholder="PAY-...">
                            <button type="submit" class="admin-btn-secondary admin-btn-secondary--sm">حفظ</button>
                        </div>
                    </div>
                </form>
            @else
                @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'مرجع الدفع', 'value' => $order->payment_ref ? '<code class="admin-code" dir="ltr">'.e($order->payment_ref).'</code>' : '—'])
            @endcanAdmin
        </div>
    </section>
</div>

<section class="student-profile-card">
    <header class="student-profile-card__head">
        <span class="student-profile-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
        </span>
        <h2 class="student-profile-card__title">عناصر الطلب <span class="admin-crud-card__meta">— {{ $order->items->count() }} دورة</span></h2>
    </header>
    <div class="student-profile-card__body">
        <div class="admin-table-wrap">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الدورة</th>
                        <th>نوع التدريب</th>
                        <th>السعر</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="order-item-cell">
                                    @if ($item->course_image)
                                        <img src="{{ $item->course_image }}" alt="" class="order-item-cell__img" loading="lazy">
                                    @else
                                        <span class="order-item-cell__placeholder" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
                                        </span>
                                    @endif
                                    <div>
                                        <strong>{{ $item->course_title ?? 'دورة #'.$item->course_id }}</strong>
                                        <span class="admin-table-sub">معرّف: {{ $item->course_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ OrderOptions::deliveryLabel($item->delivery_type) }}</td>
                            <td dir="ltr">{{ number_format((float) $item->price, 2) }} ر.س</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;padding:1.5rem">لا توجد عناصر في هذا الطلب.</td></tr>
                    @endforelse
                </tbody>
                @if ($order->items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:left;font-weight:700">المجموع</td>
                            <td dir="ltr" style="font-weight:800">{{ number_format($itemsTotal, 2) }} ر.س</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</section>

<section class="admin-course-block admin-course-block--system">
    <h2 class="admin-course-block__title">معلومات النظام</h2>
    <div class="admin-system-grid">
        <div class="admin-system-item">
            <span class="admin-system-item__label">تاريخ الإنشاء</span>
            <span class="admin-system-item__value">{{ $order->created_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
        </div>
        <div class="admin-system-item">
            <span class="admin-system-item__label">آخر تحديث</span>
            <span class="admin-system-item__value">{{ $order->updated_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
        </div>
        <div class="admin-system-item">
            <span class="admin-system-item__label">معرّف الطلب</span>
            <span class="admin-system-item__value"><code class="admin-code">#{{ $order->id }}</code></span>
        </div>
    </div>
</section>

@if ($canManage)
    <section class="order-view-actions admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>إجراءات الطلب</h2>
        </div>
        <div class="order-view-actions__buttons">
            @if ($order->status !== 'paid')
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="markPaid" wire:confirm="تعليم هذا الطلب كمدفوع؟">تعليم كمدفوع</button>
            @endif
            @if ($order->status === 'paid')
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="markPending" wire:confirm="إرجاع الطلب إلى بانتظار الدفع؟">إرجاع لبانتظار الدفع</button>
            @endif
            @if ($order->status !== 'cancelled')
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelOrder" wire:confirm="إلغاء هذا الطلب؟">إلغاء الطلب</button>
            @endif
        </div>
    </section>
@endif

<div class="order-view-back">
    <a href="{{ route('admin.orders') }}" class="admin-btn-secondary admin-btn-secondary--sm">← العودة للقائمة</a>
</div>

@push('styles')
<style>
    .order-view-hero {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem 1.5rem;
        align-items: center;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--sa-green-soft, #ecfdf5) 0%, #fff 55%);
    }
    .order-view-hero__main { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; }
    .order-view-hero__label { display: block; font-size: 0.72rem; color: var(--sa-muted); font-weight: 600; margin-bottom: 0.15rem; }
    .order-view-hero__code { font-size: 1.05rem; padding: 0.2rem 0.5rem; }
    .order-view-hero__badge { font-size: 0.78rem; }
    .order-view-hero__total { text-align: center; min-width: 8rem; }
    .order-view-hero__total strong { display: block; font-size: 1.5rem; color: var(--sa-green-dark); line-height: 1.1; }
    .order-view-hero__total small { font-size: 0.55em; font-weight: 600; }
    .order-view-hero__meta { grid-column: 1 / -1; font-size: 0.82rem; color: var(--sa-muted); }
    .order-view-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .order-payment-ref-form { margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--sa-border); }
    .order-payment-ref-form__row { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
    .order-payment-ref-form__row .admin-control { flex: 1; min-width: 10rem; }
    .order-item-cell { display: flex; align-items: center; gap: 0.65rem; }
    .order-item-cell__img { width: 2.5rem; height: 2.5rem; border-radius: 8px; object-fit: cover; border: 1px solid var(--sa-border); }
    .order-item-cell__placeholder {
        width: 2.5rem; height: 2.5rem; border-radius: 8px;
        display: grid; place-items: center;
        background: var(--sa-mist); border: 1px solid var(--sa-border); color: var(--sa-muted);
    }
    .order-view-actions { padding: 1rem 1.25rem; margin-top: 1rem; }
    .order-view-actions__buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .order-view-back { margin-top: 1rem; }
    .admin-badge--warn { background: #fff7ed; color: #c2410c; }
    .admin-badge--info { background: #eff6ff; color: #1d4ed8; }
    @media (max-width: 640px) {
        .order-view-hero { grid-template-columns: 1fr; }
        .order-view-hero__total { text-align: start; }
    }
</style>
@endpush

@include('partials.admin.shell-end')
